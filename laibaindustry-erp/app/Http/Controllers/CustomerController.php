<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Mail\CustomerStatementMail;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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

    public function index(): View
    {
        $customers = Customer::query()
            ->orderBy('customer_name')
            ->paginate(15);

        return view('customers.index', ['customers' => $customers]);
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        Customer::create($this->prepareSaveData($request->validated()));

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
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    /**
     * Get statement data for a customer (ledger rows, balances, totals).
     */
    private function getStatementData(Customer $customer): array
    {
        $hasLedger = $this->hasLedgerColumns()
            && Schema::hasTable('customer_ledger_entries');

        $openingBalance = $hasLedger ? (float) $customer->opening_balance : 0;
        $runningBalance = $openingBalance;
        $totalDebit = 0;
        $totalCredit = 0;
        $ledgerRows = [];

        if ($hasLedger) {
            $entries = CustomerLedgerEntry::where('customer_id', $customer->id)
                ->orderBy('date')
                ->orderBy('id')
                ->get();

            foreach ($entries as $entry) {
                $debit = (float) $entry->debit;
                $credit = (float) $entry->credit;

                $runningBalance += $debit - $credit;
                $totalDebit += $debit;
                $totalCredit += $credit;

                $ledgerRows[] = [
                    'date' => $entry->date,
                    'description' => $entry->description,
                    // Statement column shows customer account code (not invoice / internal reference).
                    'reference' => $customer->customer_code,
                    'debit' => $debit,
                    'credit' => $credit,
                    'running_balance' => $runningBalance,
                    'source_type' => $entry->source_type,
                ];
            }
        }

        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return [
            'customer' => $customer,
            'openingBalance' => $openingBalance,
            'ledgerRows' => $ledgerRows,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'closingBalance' => $runningBalance,
            'currencySymbol' => $currencySymbol,
        ];
    }

    /**
     * Build the statement PDF as raw bytes (shared by download and email).
     *
     * @return array{content: string, filename: string}
     */
    private function makeStatementPdfBinary(Customer $customer): array
    {
        $fontsDir = storage_path('fonts');
        if (! is_dir($fontsDir)) {
            @mkdir($fontsDir, 0755, true);
        }

        if (! class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            throw new \RuntimeException('PDF package not installed on server.');
        }

        $data = $this->getStatementData($customer);

        $pdf = Pdf::loadView('customers.statement-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('fontDir', $fontsDir)
            ->setOption('fontCache', $fontsDir);

        $filename = sprintf(
            'statement-%s-%s.pdf',
            Str::slug($customer->customer_name),
            now()->format('Y-m-d')
        );

        return [
            'content' => $pdf->output(),
            'filename' => $filename,
        ];
    }

    public function statement(Customer $customer): View
    {
        $data = $this->getStatementData($customer);

        return view('customers.statement', $data);
    }

    public function statementPdf(Customer $customer): Response|RedirectResponse
    {
        try {
            $out = $this->makeStatementPdfBinary($customer);

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
                ->route('customers.statement', $customer)
                ->with('error', 'PDF generation failed: '.$e->getMessage());
        }
    }

    public function emailStatement(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $email = trim((string) $customer->email);
        if ($email === '') {
            return redirect()
                ->route('customers.statement', $customer)
                ->with('error', 'This customer has no email address. Add one on the customer record first.');
        }

        try {
            $out = $this->makeStatementPdfBinary($customer);

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
                ->route('customers.statement', $customer)
                ->with('error', 'Could not send email: '.$e->getMessage());
        }

        return redirect()
            ->route('customers.statement', $customer)
            ->with('success', 'Statement emailed to '.$email.'.');
    }
}
