<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Mail\CustomerStatementMail;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\CustomerReceivablePurchaseOffset;
use App\Models\Payable;
use App\Models\Purchase;
use App\Models\Receivable;
use App\Models\Sale;
use App\Services\PurchaseDeletionService;
use App\Services\PurchaseReceivableOffsetService;
use App\Services\SaleDeletionService;
use App\Support\CustomerStatementPeriod;
use App\Support\StatementCompany;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Whether the customers table has the ledger-related columns.
     * Checked once per request and cached to avoid repeated DB schema queries.
     */
    private function hasLedgerColumns(): bool
    {
        static $result = null;
        if ($result === null) {
            $result = Schema::hasColumn('customers', 'opening_balance');
        }

        return $result;
    }

    /**
     * Strip ledger fields from the data array if the live DB
     * doesn't have those columns yet.
     */
    private function prepareSaveData(array $data): array
    {
        if (! $this->hasLedgerColumns()) {
            unset($data['opening_balance'], $data['opening_balance_date']);
        } else {
            $data['opening_balance'] = $data['opening_balance'] ?? 0;
        }

        return $data;
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $customers = Customer::query()->orderBy('customer_name')->get();
        $totalCustomers = Customer::query()->count();

        return view('customers.index', [
            'customers' => $customers,
            'search' => $search,
            'totalCustomers' => $totalCustomers,
        ]);
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = Customer::create($this->prepareSaveData($request->validated()));
        if ($this->hasLedgerColumns()) {
            $customer->syncOpeningBalanceReceivable();
        }

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer added successfully.');
    }

    public function show(Customer $customer): RedirectResponse
    {
        return redirect()->route('customers.edit', $customer);
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', ['customer' => $customer]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $oldCode = $customer->customer_code;
        $oldName = $customer->customer_name;
        $newCode = $request->validated()['customer_code'];
        $newName = $request->validated()['customer_name'];
        $newOpening = (float) ($request->validated()['opening_balance'] ?? 0);

        if ($this->hasLedgerColumns() && Schema::hasColumn('receivables', 'is_opening_balance')) {
            $openingReceivable = Receivable::query()
                ->where('customer_code', $oldCode)
                ->where('is_opening_balance', true)
                ->first();
            if ($openingReceivable && $newOpening > 0) {
                $paid = (float) CustomerLedgerEntry::query()
                    ->where('source_type', 'payment_received')
                    ->where('source_id', $openingReceivable->id)
                    ->sum('credit');
                $paid += (float) CustomerReceivablePurchaseOffset::query()
                    ->where('receivable_id', $openingReceivable->id)
                    ->sum('amount');
                if ($newOpening + 0.00001 < $paid) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', 'Opening balance cannot be less than payments and purchase offsets already applied to it ('.number_format($paid, 2).').');
                }
            }
        }

        try {
            DB::beginTransaction();

            $customer->update($this->prepareSaveData($request->validated()));

            $cascade = [];
            if ($newCode !== $oldCode) {
                $cascade['customer_code'] = $newCode;
            }
            if ($newName !== $oldName) {
                $cascade['customer_name'] = $newName;
            }

            if (! empty($cascade) && $oldCode !== null) {
                foreach (['sales', 'receivables', 'purchases', 'payables'] as $table) {
                    DB::table($table)
                        ->where('customer_code', $oldCode)
                        ->update($cascade);
                }
            }

            $customer->refresh();
            if ($this->hasLedgerColumns()) {
                $customer->syncOpeningBalanceReceivable();
            }

            DB::commit();

            return redirect()
                ->route('customers.index')
                ->with('success', 'Customer updated successfully across all records.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Update failed: '.$e->getMessage());
        }
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if (Schema::hasColumn('receivables', 'is_opening_balance')) {
            $openingIds = Receivable::query()
                ->where('customer_code', $customer->customer_code)
                ->where('is_opening_balance', true)
                ->pluck('id');
            foreach ($openingIds as $id) {
                CustomerLedgerEntry::query()
                    ->where('source_type', 'payment_received')
                    ->where('source_id', (int) $id)
                    ->delete();
            }
            if ($openingIds->isNotEmpty()) {
                Receivable::query()->whereIn('id', $openingIds)->delete();
            }
        }

        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    /**
     * Statement line order: transaction {@see CustomerLedgerEntry::$date} first, then posting
     * time ({@see CustomerLedgerEntry::$created_at}, nulls last), then id.
     *
     * @param  Builder<CustomerLedgerEntry>  $query
     * @return Builder<CustomerLedgerEntry>
     */
    private function applyStatementLedgerOrdering(Builder $query): Builder
    {
        return $query
            ->orderBy('date')
            ->orderByRaw('CASE WHEN created_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('created_at')
            ->orderBy('id');
    }

    /**
     * Combined receivable payments are stored as one ledger row per invoice (FIFO allocation).
     * On the customer statement, show a single line for the full payment amount.
     *
     * @param  Collection<int, CustomerLedgerEntry>  $entries
     * @return Collection<int, CustomerLedgerEntry|\stdClass>
     */
    private function ledgerEntriesForStatementDisplay(Collection $entries): Collection
    {
        if ($entries->isEmpty()) {
            return $entries;
        }

        $useLink = Schema::hasTable('customer_ledger_receivable_group_payments');
        $useColumn = Schema::hasColumn('customer_ledger_entries', 'receivable_group_payment_id');
        if (! $useLink && ! $useColumn) {
            return $entries;
        }

        /** @var array<int, list<CustomerLedgerEntry>> $buckets */
        $buckets = [];
        $standalone = [];

        foreach ($entries as $entry) {
            $gid = null;
            if ($useLink && ($link = $entry->receivableGroupPaymentLink) !== null) {
                $gid = (int) $link->receivable_group_payment_id;
            }
            if ($gid === null && $useColumn && $entry->receivable_group_payment_id) {
                $gid = (int) $entry->receivable_group_payment_id;
            }
            if ($gid !== null) {
                if (! isset($buckets[$gid])) {
                    $buckets[$gid] = [];
                }
                $buckets[$gid][] = $entry;
            } else {
                $standalone[] = $entry;
            }
        }

        $merged = [];
        foreach ($buckets as $sliceEntries) {
            $merged[] = $this->mergeGroupPaymentLedgerSlicesForStatement($sliceEntries);
        }

        return collect($standalone)
            ->merge($merged)
            ->sort(fn ($a, $b) => $this->ledgerStatementSortKey($a) <=> $this->ledgerStatementSortKey($b))
            ->values();
    }

    /**
     * Match {@see applyStatementLedgerOrdering}: date, non-null created_at first, created_at, id.
     *
     * @param  CustomerLedgerEntry|\stdClass  $e
     * @return array{0: int, 1: int, 2: int, 3: int}
     */
    private function ledgerStatementSortKey(object $e): array
    {
        $dateTs = 0;
        if (isset($e->date) && $e->date instanceof \DateTimeInterface) {
            $dateTs = $e->date->getTimestamp();
        }

        $createdNull = empty($e->created_at) ? 1 : 0;
        $createdTs = 0;
        if (! empty($e->created_at) && $e->created_at instanceof \DateTimeInterface) {
            $createdTs = $e->created_at->getTimestamp();
        }

        return [$dateTs, $createdNull, $createdTs, (int) ($e->id ?? 0)];
    }

    /**
     * @param  list<CustomerLedgerEntry>  $sliceEntries
     */
    private function mergeGroupPaymentLedgerSlicesForStatement(array $sliceEntries): \stdClass
    {
        usort(
            $sliceEntries,
            fn (CustomerLedgerEntry $a, CustomerLedgerEntry $b): int => $this->ledgerStatementSortKey($a) <=> $this->ledgerStatementSortKey($b)
        );

        $first = $sliceEntries[0];
        $sumDebit = 0.0;
        $sumCredit = 0.0;
        foreach ($sliceEntries as $e) {
            $sumDebit += (float) $e->debit;
            $sumCredit += (float) $e->credit;
        }

        $desc = trim((string) ($first->description ?? ''));

        return (object) [
            'date' => $first->date,
            'description' => $desc !== '' ? $desc : 'Payment received',
            'debit' => $sumDebit,
            'credit' => $sumCredit,
            'source_type' => $first->source_type,
            'created_at' => $first->created_at,
            'id' => $first->id,
            'invoice_number' => null,
        ];
    }

    /**
     * Invoice / document number for the statement column (from ledger reference).
     * Payment-received lines omit invoice numbers on the statement.
     */
    private function statementInvoiceNumberForEntry(CustomerLedgerEntry|\stdClass $entry): ?string
    {
        if ($entry instanceof \stdClass) {
            $s = trim((string) ($entry->invoice_number ?? ''));

            return $s !== '' ? $s : null;
        }

        if ($entry->source_type === 'payment_received') {
            return null;
        }

        $s = trim((string) ($entry->reference ?? ''));

        return $s !== '' ? $s : null;
    }

    /**
     * Receivable rows for this customer in chronological order (oldest first) for FIFO payment display.
     *
     * @return Collection<int, Receivable>
     */
    private function receivableLinesForFifo(Customer $customer, ?CustomerStatementPeriod $period = null): Collection
    {
        $code = trim((string) $customer->customer_code);
        $query = Receivable::query()
            ->orderBy('date')
            ->orderBy('id');

        if ($code !== '') {
            $query->where('customer_code', $code);
        } else {
            $name = trim((string) $customer->customer_name);
            if ($name === '') {
                return collect();
            }
            $query->whereRaw('LOWER(TRIM(COALESCE(customer_name, ?))) = LOWER(?)', ['', $name]);
        }

        if ($period !== null) {
            $query->where('date', '<=', $period->toEnd);
        }

        return $query->get();
    }

    /**
     * Invoice numbers that are fully settled when customer payments are applied oldest-invoice first
     * (FIFO) against each receivable's billed amount, ignoring how {@see Receivable::$received} is split per row.
     *
     * @return array<string, true>  invoice_number => true
     */
    private function fifoFullyPaidInvoiceNumbersForCustomer(Customer $customer, ?CustomerStatementPeriod $period = null): array
    {
        $eps = 0.009;

        $poolQuery = CustomerLedgerEntry::query()
            ->where('customer_id', $customer->id)
            ->where('source_type', 'payment_received');

        if ($period !== null) {
            $poolQuery->where('date', '<=', $period->toEnd);
        }

        $remaining = round((float) ($poolQuery->sum('credit') ?? 0), 2);
        $paid = [];

        foreach ($this->receivableLinesForFifo($customer, $period) as $r) {
            $amt = round((float) $r->amount, 2);
            if ($amt <= $eps) {
                continue;
            }

            if ($remaining + $eps >= $amt) {
                $key = trim((string) ($r->invoice_number ?? ''));
                if ($key !== '') {
                    $paid[$key] = true;
                }
                $remaining = round($remaining - $amt, 2);
            } else {
                break;
            }
        }

        return $paid;
    }

    /**
     * Get statement data for a customer (ledger rows, balances, totals).
     *
     * @param  CustomerStatementPeriod|null  $period  When set, statement is limited to [fromStart, toEnd] inclusive; opening row is balance brought forward.
     */
    private function getStatementData(Customer $customer, ?CustomerStatementPeriod $period = null): array
    {
        $hasLedger = $this->hasLedgerColumns()
            && Schema::hasTable('customer_ledger_entries');

        $customerOpening = $hasLedger ? (float) $customer->opening_balance : 0;
        $runningBalance = $customerOpening;
        $totalDebit = 0;
        $totalCredit = 0;
        $ledgerRows = [];
        $statementFiltered = $period !== null && $hasLedger;
        $openingBalanceForStatementRow = $customerOpening;

        if ($hasLedger) {
            if ($period === null) {
                $ledgerQuery = CustomerLedgerEntry::query()->where('customer_id', $customer->id);
                if (Schema::hasTable('customer_ledger_receivable_group_payments')) {
                    $ledgerQuery->with('receivableGroupPaymentLink');
                }
                $entries = $this->ledgerEntriesForStatementDisplay(
                    $this->applyStatementLedgerOrdering($ledgerQuery)->get()
                );

                foreach ($entries as $entry) {
                    $debit = (float) $entry->debit;
                    $credit = (float) $entry->credit;

                    $runningBalance += $debit - $credit;
                    $totalDebit += $debit;
                    $totalCredit += $credit;

                    $ledgerRows[] = [
                        'date' => $entry->date,
                        'description' => $entry->description,
                        'invoice_number' => $this->statementInvoiceNumberForEntry($entry),
                        'debit' => $debit,
                        'credit' => $credit,
                        'running_balance' => $runningBalance,
                        'source_type' => $entry->source_type,
                        'ledger_entry_id' => $entry instanceof CustomerLedgerEntry ? $entry->id : null,
                        'is_merged_statement_row' => ! ($entry instanceof CustomerLedgerEntry),
                    ];
                }
            } else {
                $fromStart = $period->fromStart;
                $toEnd = $period->toEnd;

                $preNet = (float) (CustomerLedgerEntry::query()
                    ->where('customer_id', $customer->id)
                    ->where('date', '<', $fromStart)
                    ->selectRaw('COALESCE(SUM(debit - credit), 0) as net')
                    ->value('net') ?? 0);

                $periodOpening = $customerOpening + $preNet;
                $runningBalance = $periodOpening;
                $openingBalanceForStatementRow = $periodOpening;

                $periodLedgerQuery = CustomerLedgerEntry::query()
                    ->where('customer_id', $customer->id)
                    ->where('date', '>=', $fromStart)
                    ->where('date', '<=', $toEnd);
                if (Schema::hasTable('customer_ledger_receivable_group_payments')) {
                    $periodLedgerQuery->with('receivableGroupPaymentLink');
                }
                $entries = $this->ledgerEntriesForStatementDisplay(
                    $this->applyStatementLedgerOrdering($periodLedgerQuery)->get()
                );

                foreach ($entries as $entry) {
                    $debit = (float) $entry->debit;
                    $credit = (float) $entry->credit;

                    $runningBalance += $debit - $credit;
                    $totalDebit += $debit;
                    $totalCredit += $credit;

                    $ledgerRows[] = [
                        'date' => $entry->date,
                        'description' => $entry->description,
                        'invoice_number' => $this->statementInvoiceNumberForEntry($entry),
                        'debit' => $debit,
                        'credit' => $credit,
                        'running_balance' => $runningBalance,
                        'source_type' => $entry->source_type,
                        'ledger_entry_id' => $entry instanceof CustomerLedgerEntry ? $entry->id : null,
                        'is_merged_statement_row' => ! ($entry instanceof CustomerLedgerEntry),
                    ];
                }
            }
        }

        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        $outstandingReceivables = Receivable::query()
            ->where('customer_code', $customer->customer_code)
            ->whereRaw('COALESCE(amount, 0) - COALESCE(received, 0) > 0.009')
            ->where('is_opening_balance', false)
            ->orderBy('date')
            ->get()
            ->map(fn ($r) => [
                'invoice_number' => $r->invoice_number,
                'date'           => $r->date,
                'outstanding'    => round((float) $r->amount - (float) $r->received, 2),
            ]);

        return [
            'customer' => $customer,
            'company' => StatementCompany::normalize(config('company')),
            'openingBalance' => $openingBalanceForStatementRow,
            'ledgerRows' => $ledgerRows,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'closingBalance' => $runningBalance,
            'currencySymbol' => $currencySymbol,
            'statementFiltered' => $statementFiltered,
            'periodFrom' => $period?->fromStart,
            'periodTo' => $period?->toEnd,
            'outstandingReceivables' => $outstandingReceivables,
            'fifoPaidInvoiceNumbers' => $this->fifoFullyPaidInvoiceNumbersForCustomer($customer, $period),
        ];
    }

    private function redirectStatementWithValidation(Customer $customer, ValidationException $e): RedirectResponse
    {
        return redirect()
            ->route('customers.statement', ['customer' => $customer])
            ->withErrors($e->validator)
            ->withInput();
    }

    /**
     * @return array{content: string, filename: string}
     */
    private function makeStatementPdfBinary(Customer $customer, ?CustomerStatementPeriod $period = null): array
    {
        $fontsDir = storage_path('fonts');
        if (! is_dir($fontsDir)) {
            @mkdir($fontsDir, 0755, true);
        }

        if (! class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            throw new \RuntimeException('PDF package not installed on server.');
        }

        $data = $this->getStatementData($customer, $period);

        $pdf = Pdf::loadView('customers.statement-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('fontDir', $fontsDir)
            ->setOption('fontCache', $fontsDir);

        $slug = Str::slug($customer->customer_name);
        if ($period !== null) {
            $filename = sprintf(
                'statement-%s-%s-%s.pdf',
                $slug,
                str_replace('/', '-', format_display_date($period->fromStart)),
                str_replace('/', '-', format_display_date($period->toEnd))
            );
        } else {
            $filename = sprintf(
                'statement-%s-all-%s.pdf',
                $slug,
                str_replace('/', '-', format_display_date(now()))
            );
        }

        return [
            'content' => $pdf->output(),
            'filename' => $filename,
        ];
    }

    public function statement(Request $request, Customer $customer): View|RedirectResponse
    {
        try {
            [, $period] = CustomerStatementPeriod::validateAndParse($request);
        } catch (ValidationException $e) {
            return $this->redirectStatementWithValidation($customer, $e);
        }

        $data = $this->getStatementData($customer, $period);

        return view('customers.statement', $data);
    }

    public function destroyLedgerEntry(Customer $customer, CustomerLedgerEntry $customerLedgerEntry): RedirectResponse
    {
        if ((int) $customerLedgerEntry->customer_id !== (int) $customer->id) {
            abort(404);
        }

        if (Schema::hasTable('customer_ledger_receivable_group_payments')) {
            $customerLedgerEntry->loadMissing('receivableGroupPaymentLink');
            if ($customerLedgerEntry->receivableGroupPaymentLink !== null) {
                return redirect()->back()
                    ->with('error', 'This line is part of a combined receivable payment. Remove or edit it from Receivables.');
            }
        }

        if (Schema::hasColumn('customer_ledger_entries', 'receivable_group_payment_id')
            && $customerLedgerEntry->receivable_group_payment_id) {
            return redirect()->back()
                ->with('error', 'This line is part of a combined receivable payment. Remove or edit it from Receivables.');
        }

        $sourceType = (string) ($customerLedgerEntry->source_type ?? '');

        try {
            DB::beginTransaction();

            $success = 'Ledger entry removed.';

            if ($sourceType === 'sale') {
                $saleId = (int) ($customerLedgerEntry->source_id ?? 0);
                if ($saleId > 0) {
                    $sale = Sale::query()->find($saleId);
                    if ($sale) {
                        app(SaleDeletionService::class)->delete($sale);
                        $success = 'Sale and related records (receivable, ledger, VAT) were removed.';
                    } else {
                        $customerLedgerEntry->delete();
                    }
                } else {
                    $customerLedgerEntry->delete();
                }
            } elseif ($sourceType === 'purchase') {
                $purchaseId = (int) ($customerLedgerEntry->source_id ?? 0);
                if ($purchaseId > 0) {
                    $purchase = Purchase::query()->find($purchaseId);
                    if ($purchase) {
                        app(PurchaseDeletionService::class)->delete($purchase);
                        $success = 'Purchase and related records (payable, ledger, VAT, purchase offsets) were removed.';
                    } else {
                        $customerLedgerEntry->delete();
                    }
                } else {
                    $customerLedgerEntry->delete();
                }
            } elseif ($sourceType === 'payment_made') {
                $payableId = (int) ($customerLedgerEntry->source_id ?? 0);
                $debit = (float) $customerLedgerEntry->debit;
                if ($payableId > 0 && $debit > 0.00001) {
                    $payable = Payable::query()->find($payableId);
                    if ($payable) {
                        $newReceived = max(0, round((float) $payable->received - $debit, 2));
                        $payable->update(['received' => $newReceived]);
                    }
                }
                $customerLedgerEntry->delete();
                $success = 'Payment removed from the ledger; payable received amount was adjusted.';
            } elseif ($sourceType === 'payment_received') {
                $receivableId = $customerLedgerEntry->source_id ? (int) $customerLedgerEntry->source_id : null;
                $customerLedgerEntry->delete();
                if ($receivableId && Receivable::query()->whereKey($receivableId)->exists()) {
                    app(PurchaseReceivableOffsetService::class)->syncReceivable(
                        Receivable::query()->findOrFail($receivableId)
                    );
                }
                $success = 'Payment removed from the ledger; receivable totals were updated.';
            } else {
                $customerLedgerEntry->delete();
            }

            DB::commit();

            return redirect()->back()->with('success', $success);
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Could not remove ledger entry: '.$e->getMessage());
        }
    }

    public function statementPdf(Request $request, Customer $customer): Response|RedirectResponse
    {
        try {
            [, $period] = CustomerStatementPeriod::validateAndParse($request);
        } catch (ValidationException $e) {
            return $this->redirectStatementWithValidation($customer, $e);
        }

        try {
            $out = $this->makeStatementPdfBinary($customer, $period);

            return response($out['content'], 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$out['filename'].'"',
            ]);
        } catch (\Throwable $e) {
            Log::error('PDF generation failed', [
                'customer' => $customer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('customers.statement', array_filter([
                    'customer' => $customer,
                    'from' => $period?->fromStart->format('Y-m-d'),
                    'to' => $period?->toEnd->format('Y-m-d'),
                ]))
                ->with('error', 'PDF generation failed: '.$e->getMessage());
        }
    }

    public function emailStatement(Request $request, Customer $customer): RedirectResponse
    {
        try {
            [, $period] = CustomerStatementPeriod::validateAndParse($request);
        } catch (ValidationException $e) {
            return $this->redirectStatementWithValidation($customer, $e);
        }

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $email = trim((string) $customer->email);
        if ($email === '') {
            return redirect()
                ->route('customers.statement', array_filter([
                    'customer' => $customer,
                    'from' => $period?->fromStart->format('Y-m-d'),
                    'to' => $period?->toEnd->format('Y-m-d'),
                ]))
                ->with('error', 'This customer has no email address. Add one on the customer record first.');
        }

        try {
            $out = $this->makeStatementPdfBinary($customer, $period);

            Mail::to($email)->send(new CustomerStatementMail(
                customer: $customer,
                pdfContent: $out['content'],
                pdfFilename: $out['filename'],
                note: $validated['message'] ?? null,
            ));
        } catch (\Throwable $e) {
            Log::error('Statement email failed', [
                'customer' => $customer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('customers.statement', array_filter([
                    'customer' => $customer,
                    'from' => $period?->fromStart->format('Y-m-d'),
                    'to' => $period?->toEnd->format('Y-m-d'),
                ]))
                ->with('error', 'Could not send email: '.$e->getMessage());
        }

        return redirect()
            ->route('customers.statement', array_filter([
                'customer' => $customer,
                'from' => $period?->fromStart->format('Y-m-d'),
                'to' => $period?->toEnd->format('Y-m-d'),
            ]))
            ->with('success', 'Statement emailed to '.$email.'.');
    }
}
