<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\CustomerLedgerPayableGroupPayment;
use App\Models\Payable;
use App\Models\PayableGroupPayment;
use App\Models\PayableGroupPaymentLine;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PayableController extends Controller
{
    public function index(): View
    {
        $payables = Payable::query()
            ->orderByDesc('date')
            ->get();
        $payableGroups = $payables->groupBy(fn (Payable $payable) => $this->payableGroupKey($payable))
            ->map(function (Collection $slice, string $key): array {
                /** @var Payable $first */
                $first = $slice->first();
                $latest = $slice->sortByDesc(fn (Payable $p) => $p->date?->timestamp ?? 0)->first();
                $latestDate = $latest?->date;
                $bill = (float) $slice->sum(fn (Payable $p) => (float) $p->amount);
                $paid = (float) $slice->sum(fn (Payable $p) => (float) $p->received);

                return [
                    'group_key_encoded' => $this->encodeGroupKeyForRoute($key),
                    'display_name' => $first->customer_name ?: ($first->customer_code ?: 'Unknown customer'),
                    'display_code' => trim((string) ($first->customer_code ?? '')),
                    'invoice_count' => $slice->count(),
                    'latest_invoice_date' => $latestDate,
                    'total_bill' => $bill,
                    'total_paid' => $paid,
                    'total_balance' => max(0, $bill - $paid),
                ];
            })
            ->sortByDesc(fn (array $g) => $g['latest_invoice_date']?->timestamp ?? 0)
            ->values();

        $totals = Payable::query()
            ->selectRaw('
                COALESCE(SUM(amount), 0)                   AS total_amount,
                COALESCE(SUM(received), 0)                 AS total_received,
                COALESCE(SUM(amount) - SUM(received), 0)   AS total_outstanding
            ')
            ->first();

        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return view('payables.index', [
            'payableGroups' => $payableGroups,
            'totals' => $totals,
            'currencySymbol' => $currencySymbol,
            'totalInvoiceCount' => $payables->count(),
        ]);
    }

    public function showGroup(string $groupKey): View
    {
        $decoded = $this->decodeGroupKeyFromRoute($groupKey);
        if ($decoded === null || ! $this->isValidGroupKey($decoded)) {
            throw new NotFoundHttpException;
        }

        $query = Payable::query()
            ->orderByDesc('date')
            ->orderByDesc('id');

        if (str_starts_with($decoded, 'code:')) {
            $code = substr($decoded, strlen('code:'));
            $query->whereRaw('LOWER(TRIM(COALESCE(customer_code, ?))) = ?', ['', $code]);
        } elseif (str_starts_with($decoded, 'name:')) {
            $name = substr($decoded, strlen('name:'));
            $query->whereRaw('LOWER(TRIM(COALESCE(customer_name, ?))) = ?', ['', $name]);
        } elseif (str_starts_with($decoded, 'id:')) {
            $query->where('id', (int) substr($decoded, strlen('id:')));
        }

        $payables = $query->get();
        if ($payables->isEmpty()) {
            throw new NotFoundHttpException;
        }

        foreach ($payables as $payable) {
            $received = round((float) $payable->received, 2);
            $payable->setAttribute('direct_payment_total', $received);
            $payable->setAttribute('remaining_balance', round((float) $payable->amount - $received, 2));
        }

        $openPayables = $payables->filter(fn (Payable $p) => (float) $p->remaining_balance > 0.00001)->values();
        $settledPayables = $payables->filter(fn (Payable $p) => (float) $p->remaining_balance <= 0.00001)->values();

        $billTotal = (float) $payables->sum(fn (Payable $p) => (float) $p->amount);
        $paidTotal = (float) $payables->sum(fn (Payable $p) => (float) $p->received);
        $outstanding = max(0, $billTotal - $paidTotal);
        $groupTotals = [
            'total_bill' => round($billTotal, 2),
            'total_received' => round($paidTotal, 2),
            'total_direct_payments' => round($paidTotal, 2),
            'total_remaining' => round($outstanding, 2),
        ];
        $groupPayments = Schema::hasTable('payable_group_payments')
            ? PayableGroupPayment::query()
                ->where('group_key', $decoded)
                ->orderByDesc('payment_date')
                ->orderByDesc('id')
                ->get()
            : collect();

        return view('payables.group', [
            'payables' => $payables,
            'openPayables' => $openPayables,
            'settledPayables' => $settledPayables,
            'groupPayments' => $groupPayments,
            'groupTotals' => $groupTotals,
            'displayName' => $payables->first()->customer_name ?: ($payables->first()->customer_code ?: 'Unknown customer'),
            'currencySymbol' => Currency::query()->where('is_default', true)->value('symbol') ?? '$',
            'billTotal' => $billTotal,
            'paidTotal' => $paidTotal,
            'outstanding' => $outstanding,
            'groupKeyEncoded' => $this->encodeGroupKeyForRoute($decoded),
        ]);
    }

    public function storeGroupPayment(Request $request, string $groupKey): RedirectResponse
    {
        $decoded = $this->decodeGroupKeyFromRoute($groupKey);
        if ($decoded === null || ! $this->isValidGroupKey($decoded)) {
            throw new NotFoundHttpException;
        }

        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $fifoList = $this->payablesFifoForGroup($decoded);
        if ($fifoList->isEmpty()) {
            throw new NotFoundHttpException;
        }

        $remaining = $this->totalGroupRemaining($fifoList);
        $amount = round((float) $validated['amount'], 2);
        if ($amount > $remaining + 0.00001) {
            return redirect()->back()->withInput()
                ->with('error', 'Amount cannot exceed combined remaining balance ('.number_format($remaining, 2).').');
        }

        $allocations = $this->allocateFifo($fifoList, $amount);
        $sumAlloc = round(array_sum($allocations), 2);
        if (abs($sumAlloc - $amount) > 0.02 || $sumAlloc < 0.01) {
            return redirect()->back()->withInput()
                ->with('error', 'Unable to allocate payment across invoices.');
        }

        $paymentDate = Carbon::parse($validated['payment_date'], config('app.timezone'))->startOfDay();

        try {
            DB::beginTransaction();

            $groupPayment = Schema::hasTable('payable_group_payments')
                ? PayableGroupPayment::create([
                    'group_key' => $decoded,
                    'payment_date' => $paymentDate,
                    'amount' => $amount,
                ])
                : null;
            $this->applyGroupPaymentSlices($groupPayment, $paymentDate, $allocations);

            DB::commit();

            return redirect()->route('payables.group', ['groupKey' => $this->encodeGroupKeyForRoute($decoded)])
                ->with('success', 'Combined payment recorded.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to record combined payment: '.$e->getMessage());
        }
    }

    public function editGroupPayment(string $groupKey, PayableGroupPayment $payableGroupPayment): View
    {
        $this->assertGroupPaymentMatchesRouteKey($groupKey, $payableGroupPayment);

        $decoded = $payableGroupPayment->group_key;
        $fifoList = $this->payablesFifoForGroup($decoded);
        if ($fifoList->isEmpty()) {
            throw new NotFoundHttpException;
        }

        $remaining = $this->totalGroupRemaining($fifoList);
        $maxAllowed = round($remaining + (float) $payableGroupPayment->amount, 2);
        $groupKeyEncoded = $this->encodeGroupKeyForRoute($decoded);

        return view('payables.group-payment-edit', [
            'groupPayment' => $payableGroupPayment,
            'groupKeyEncoded' => $groupKeyEncoded,
            'displayName' => $fifoList->first()->customer_name
                ?: $fifoList->first()->customer_code
                ?: ('#'.$fifoList->first()->id),
            'maxAllowed' => $maxAllowed,
            'currencySymbol' => Currency::query()->where('is_default', true)->value('symbol') ?? '$',
        ]);
    }

    public function updateGroupPayment(Request $request, string $groupKey, PayableGroupPayment $payableGroupPayment): RedirectResponse
    {
        $this->assertGroupPaymentMatchesRouteKey($groupKey, $payableGroupPayment);
        $payableGroupPayment->load('lines');

        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $decoded = $payableGroupPayment->group_key;
        $amount = round((float) $validated['amount'], 2);
        $oldAmount = (float) $payableGroupPayment->amount;

        $fifoList = $this->payablesFifoForGroup($decoded);
        $remaining = $this->totalGroupRemaining($fifoList);
        $maxAllowed = round($remaining + $oldAmount, 2);
        if ($amount > $maxAllowed + 0.00001) {
            return redirect()->back()->withInput()
                ->with('error', 'Amount cannot exceed combined remaining balance plus this payment ('.number_format($maxAllowed, 2).').');
        }

        $paymentDate = Carbon::parse($validated['payment_date'], config('app.timezone'))->startOfDay();

        try {
            DB::beginTransaction();

            $this->reverseGroupPayment($payableGroupPayment);
            $payableGroupPayment->update([
                'amount' => $amount,
                'payment_date' => $paymentDate,
            ]);
            $fifoList = $this->payablesFifoForGroup($decoded);
            $allocations = $this->allocateFifo($fifoList, $amount);
            $sumAlloc = round(array_sum($allocations), 2);
            if (abs($sumAlloc - $amount) > 0.02 || $sumAlloc < 0.01) {
                DB::rollBack();

                return redirect()->back()->withInput()
                    ->with('error', 'Unable to allocate payment across invoices.');
            }
            $this->applyGroupPaymentSlices($payableGroupPayment, $paymentDate, $allocations);

            DB::commit();

            return redirect()->route('payables.group', ['groupKey' => $this->encodeGroupKeyForRoute($decoded)])
                ->with('success', 'Combined payment updated.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to update combined payment: '.$e->getMessage());
        }
    }

    public function destroyGroupPayment(string $groupKey, PayableGroupPayment $payableGroupPayment): RedirectResponse
    {
        $this->assertGroupPaymentMatchesRouteKey($groupKey, $payableGroupPayment);
        $payableGroupPayment->load('lines');
        $decoded = $payableGroupPayment->group_key;

        try {
            DB::beginTransaction();

            $this->reverseGroupPayment($payableGroupPayment);
            $payableGroupPayment->delete();

            DB::commit();

            return redirect()->route('payables.group', ['groupKey' => $this->encodeGroupKeyForRoute($decoded)])
                ->with('success', 'Combined payment removed.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to remove combined payment: '.$e->getMessage());
        }
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('payables.index')
            ->with('error', 'Payables are created automatically when a purchase is saved.');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('payables.index')
            ->with('error', 'Payables are created automatically when a purchase is saved.');
    }

    public function show(Payable $payable): RedirectResponse
    {
        return redirect()->route('payables.index');
    }

    public function edit(Payable $payable): View
    {
        $payable->load('paymentLedgerEntries');
        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return view('payables.edit', compact('payable', 'currencySymbol'));
    }

    public function update(Request $request, Payable $payable): RedirectResponse
    {
        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'received' => ['required', 'numeric', 'min:0.01'],
        ]);

        $paymentDate = Carbon::parse($validated['payment_date'], config('app.timezone'))->startOfDay();
        $payment = (float) $validated['received'];
        $balance = (float) $payable->amount - (float) $payable->received;
        if ($payment > $balance) {
            return redirect()->back()->withInput()
                ->with('error', 'Amount cannot exceed remaining balance ('.number_format($balance, 2).').');
        }

        try {
            DB::beginTransaction();

            $customer = $payable->customer_code
                ? Customer::where('customer_code', $payable->customer_code)->first()
                : null;

            if ($customer) {
                CustomerLedgerEntry::create([
                    'customer_id' => $customer->id,
                    'date' => $paymentDate,
                    'description' => 'Payment Made',
                    'reference' => $payable->invoice_number,
                    'debit' => $payment,
                    'credit' => 0,
                    'source_type' => 'payment_made',
                    'source_id' => $payable->id,
                ]);
                $this->syncReceivedFromLedger($payable);
            } else {
                $payable->increment('received', $payment);
                $payable->update(['received_date' => $paymentDate]);
            }

            DB::commit();

            return redirect()->route('payables.index')
                ->with('success', 'Payment of '.number_format($payment, 2).' recorded successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to record payment: '.$e->getMessage());
        }
    }

    public function updatePayment(Request $request, Payable $payable, CustomerLedgerEntry $customerLedgerEntry): RedirectResponse
    {
        $this->assertPaymentLedgerBelongsToPayable($payable, $customerLedgerEntry);

        $id = $customerLedgerEntry->id;
        $validated = $request->validate([
            "date_{$id}" => ['required', 'date'],
            "debit_{$id}" => ['required', 'numeric', 'min:0.01'],
        ]);

        $debit = round((float) $validated["debit_{$id}"], 2);
        $otherSum = (float) CustomerLedgerEntry::query()
            ->where('source_type', 'payment_made')
            ->where('source_id', $payable->id)
            ->where('id', '!=', $customerLedgerEntry->id)
            ->sum('debit');

        if ($otherSum + $debit > (float) $payable->amount + 0.00001) {
            return redirect()->back()->withInput()
                ->with('error', 'Total payments cannot exceed the bill amount ('.number_format((float) $payable->amount, 2).').');
        }

        try {
            DB::beginTransaction();

            $customerLedgerEntry->update([
                'date' => Carbon::parse($validated["date_{$id}"], config('app.timezone'))->startOfDay(),
                'debit' => $debit,
            ]);

            $this->syncReceivedFromLedger($payable);

            DB::commit();

            return redirect()->route('payables.edit', $payable)
                ->with('success', 'Payment updated.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to update payment: '.$e->getMessage());
        }
    }

    public function destroyPayment(Payable $payable, CustomerLedgerEntry $customerLedgerEntry): RedirectResponse
    {
        $this->assertPaymentLedgerBelongsToPayable($payable, $customerLedgerEntry);

        try {
            DB::beginTransaction();

            $customerLedgerEntry->delete();
            $this->syncReceivedFromLedger($payable);

            DB::commit();

            return redirect()->route('payables.edit', $payable)
                ->with('success', 'Payment removed.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to remove payment: '.$e->getMessage());
        }
    }

    public function adjustReceivedWithoutLedger(Request $request, Payable $payable): RedirectResponse
    {
        if ($payable->paymentLedgerEntries()->exists()) {
            return redirect()->route('payables.edit', $payable)
                ->with('error', 'This payable has ledger payments; edit those rows instead.');
        }

        $validated = $request->validate([
            'received' => ['required', 'numeric', 'min:0'],
            'received_date' => ['nullable', 'date'],
        ]);

        $received = round((float) $validated['received'], 2);
        if ($received > (float) $payable->amount + 0.00001) {
            return redirect()->back()->withInput()
                ->with('error', 'Paid cannot exceed the bill amount ('.number_format((float) $payable->amount, 2).').');
        }

        $receivedDate = null;
        if ($received > 0) {
            $receivedDate = ! empty($validated['received_date'])
                ? Carbon::parse($validated['received_date'], config('app.timezone'))->startOfDay()
                : $payable->received_date;
        }

        $payable->update([
            'received' => $received,
            'received_date' => $receivedDate,
        ]);

        return redirect()->route('payables.edit', $payable)
            ->with('success', 'Paid amount updated.');
    }

    public function destroy(Payable $payable): RedirectResponse
    {
        if (! in_array(auth()->user()?->role, ['admin', 'manager'], true)) {
            return redirect()->route('payables.index')
                ->with('error', 'You do not have permission to delete payables.');
        }

        $payable->delete();

        return redirect()->route('payables.index')
            ->with('success', 'Payable deleted successfully.');
    }

    private function syncReceivedFromLedger(Payable $payable): void
    {
        $paid = (float) CustomerLedgerEntry::query()
            ->where('source_type', 'payment_made')
            ->where('source_id', $payable->id)
            ->sum('debit');
        $latestPaidAt = CustomerLedgerEntry::query()
            ->where('source_type', 'payment_made')
            ->where('source_id', $payable->id)
            ->max('date');

        $payable->update([
            'received' => $paid,
            'received_date' => $latestPaidAt
                ? Carbon::parse($latestPaidAt, config('app.timezone'))->startOfDay()
                : null,
        ]);
    }

    private function assertPaymentLedgerBelongsToPayable(Payable $payable, CustomerLedgerEntry $entry): void
    {
        if ($entry->source_type !== 'payment_made' || (int) $entry->source_id !== (int) $payable->id) {
            abort(404);
        }
    }

    private function payableGroupKey(Payable $payable): string
    {
        $code = trim((string) ($payable->customer_code ?? ''));
        if ($code !== '') {
            return 'code:'.mb_strtolower($code);
        }

        $name = trim((string) ($payable->customer_name ?? ''));
        if ($name !== '') {
            return 'name:'.mb_strtolower($name);
        }

        return 'id:'.$payable->id;
    }

    private function isValidGroupKey(string $key): bool
    {
        if (str_starts_with($key, 'id:')) {
            return (bool) preg_match('/^id:\d+$/', $key);
        }
        if (str_starts_with($key, 'code:')) {
            return strlen($key) > strlen('code:');
        }
        if (str_starts_with($key, 'name:')) {
            return strlen($key) > strlen('name:');
        }

        return false;
    }

    private function encodeGroupKeyForRoute(string $key): string
    {
        return rtrim(strtr(base64_encode($key), '+/', '-_'), '=');
    }

    private function decodeGroupKeyFromRoute(string $value): ?string
    {
        $normalized = strtr($value, '-_', '+/');
        $pad = strlen($normalized) % 4;
        if ($pad !== 0) {
            $normalized .= str_repeat('=', 4 - $pad);
        }
        $decoded = base64_decode($normalized, true);

        return $decoded === false ? null : $decoded;
    }

    private function payablesFifoForGroup(string $decoded): Collection
    {
        $query = Payable::query()->orderBy('date')->orderBy('id');
        if (str_starts_with($decoded, 'code:')) {
            $code = substr($decoded, strlen('code:'));
            $query->whereRaw('LOWER(TRIM(COALESCE(customer_code, ?))) = ?', ['', $code]);
        } elseif (str_starts_with($decoded, 'name:')) {
            $name = substr($decoded, strlen('name:'));
            $query->whereRaw('LOWER(TRIM(COALESCE(customer_name, ?))) = ?', ['', $name]);
        } elseif (str_starts_with($decoded, 'id:')) {
            $query->where('id', (int) substr($decoded, strlen('id:')));
        }

        return $query->get();
    }

    private function totalGroupRemaining(Collection $payablesOldestFirst): float
    {
        $sum = 0.0;
        foreach ($payablesOldestFirst as $p) {
            $sum += (float) $p->amount - (float) $p->received;
        }

        return round($sum, 2);
    }

    /**
     * @return array<int, float> payable_id => slice amount
     */
    private function allocateFifo(Collection $payablesOldestFirst, float $paymentAmount): array
    {
        $left = round($paymentAmount, 2);
        $out = [];
        foreach ($payablesOldestFirst as $p) {
            if ($left <= 0) {
                break;
            }
            $remaining = round((float) $p->amount - (float) $p->received, 2);
            if ($remaining <= 0) {
                continue;
            }
            $slice = min($left, $remaining);
            if ($slice > 0) {
                $out[(int) $p->id] = $slice;
                $left = round($left - $slice, 2);
            }
        }

        return $out;
    }

    /**
     * @param  array<int, float>  $allocations
     */
    private function applyGroupPaymentSlices(?PayableGroupPayment $groupPayment, Carbon $paymentDate, array $allocations): void
    {
        foreach ($allocations as $payableId => $slice) {
            $slice = round((float) $slice, 2);
            if ($slice <= 0) {
                continue;
            }
            $payable = Payable::query()->findOrFail($payableId);
            $customer = $payable->customer_code
                ? Customer::where('customer_code', $payable->customer_code)->first()
                : null;

            if ($customer) {
                $ledgerAttrs = [
                    'customer_id' => $customer->id,
                    'date' => $paymentDate,
                    'description' => 'Payment Made',
                    'reference' => $payable->invoice_number,
                    'debit' => $slice,
                    'credit' => 0,
                    'source_type' => 'payment_made',
                    'source_id' => $payable->id,
                ];
                if ($groupPayment && Schema::hasColumn('customer_ledger_entries', 'payable_group_payment_id')) {
                    $ledgerAttrs['payable_group_payment_id'] = $groupPayment->id;
                }
                $entry = CustomerLedgerEntry::create($ledgerAttrs);
                if ($groupPayment && Schema::hasTable('customer_ledger_payable_group_payments')) {
                    CustomerLedgerPayableGroupPayment::create([
                        'customer_ledger_entry_id' => $entry->id,
                        'payable_group_payment_id' => $groupPayment->id,
                    ]);
                }
                if ($groupPayment && Schema::hasTable('payable_group_payment_lines')) {
                    PayableGroupPaymentLine::create([
                        'payable_group_payment_id' => $groupPayment->id,
                        'payable_id' => $payable->id,
                        'amount' => $slice,
                        'customer_ledger_entry_id' => $entry->id,
                    ]);
                }
                $this->syncReceivedFromLedger($payable);
            } else {
                $payable->increment('received', $slice);
                $payable->refresh();
                $currentAt = $payable->received_date
                    ? Carbon::parse($payable->received_date, config('app.timezone'))->startOfDay()
                    : null;
                $nextAt = $paymentDate;
                if ($currentAt && $currentAt->gt($paymentDate)) {
                    $nextAt = $currentAt;
                }
                $payable->update(['received_date' => $nextAt]);
                if ($groupPayment && Schema::hasTable('payable_group_payment_lines')) {
                    PayableGroupPaymentLine::create([
                        'payable_group_payment_id' => $groupPayment->id,
                        'payable_id' => $payable->id,
                        'amount' => $slice,
                        'customer_ledger_entry_id' => null,
                    ]);
                }
            }
        }
    }

    private function reverseGroupPayment(PayableGroupPayment $payment): void
    {
        $payment->loadMissing('lines');
        $batchId = (int) $payment->id;
        foreach ($payment->lines as $line) {
            $this->reverseGroupPaymentLine($line, $batchId);
            $line->delete();
        }
    }

    private function reverseGroupPaymentLine(PayableGroupPaymentLine $line, int $excludeGroupPaymentId): void
    {
        $payable = $line->payable_id
            ? Payable::query()->find($line->payable_id)
            : null;

        if ($line->customer_ledger_entry_id) {
            $entry = CustomerLedgerEntry::query()->find($line->customer_ledger_entry_id);
            if ($entry) {
                $entry->delete();
            }
            if ($payable) {
                $this->syncReceivedFromLedger($payable);
            }

            return;
        }

        if (! $payable) {
            return;
        }

        $slice = (float) $line->amount;
        $payable->decrement('received', $slice);
        $payable->refresh();
        $paid = round((float) $payable->received, 2);
        if ($paid <= 0) {
            $payable->update([
                'received' => 0,
                'received_date' => null,
            ]);
        } else {
            $maxOrphan = $this->maxOrphanGroupPaymentDateForPayable($payable, $excludeGroupPaymentId);
            if ($maxOrphan) {
                $payable->update(['received_date' => $maxOrphan]);
            }
        }
    }

    private function maxOrphanGroupPaymentDateForPayable(Payable $payable, int $excludeGroupPaymentId): ?Carbon
    {
        if (! Schema::hasTable('payable_group_payment_lines') || ! Schema::hasTable('payable_group_payments')) {
            return null;
        }
        $max = DB::table('payable_group_payment_lines')
            ->join('payable_group_payments as gp', 'gp.id', '=', 'payable_group_payment_lines.payable_group_payment_id')
            ->where('payable_group_payment_lines.payable_id', $payable->id)
            ->whereNull('payable_group_payment_lines.customer_ledger_entry_id')
            ->where('payable_group_payment_lines.payable_group_payment_id', '!=', $excludeGroupPaymentId)
            ->max('gp.payment_date');

        return $max ? Carbon::parse($max, config('app.timezone'))->startOfDay() : null;
    }

    private function assertGroupPaymentMatchesRouteKey(string $groupKey, PayableGroupPayment $payment): void
    {
        $decoded = $this->decodeGroupKeyFromRoute($groupKey);
        if ($decoded === null || $payment->group_key !== $decoded) {
            abort(404);
        }
    }
}
