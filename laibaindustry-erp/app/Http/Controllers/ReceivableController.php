<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\CustomerLedgerReceivableGroupPayment;
use App\Models\CustomerReceivablePurchaseOffset;
use App\Models\Receivable;
use App\Models\ReceivableGroupPayment;
use App\Models\ReceivableGroupPaymentLine;
use App\Services\PurchaseReceivableOffsetService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReceivableController extends Controller
{
    public function index(Request $request): View
    {
        $table = (new Receivable)->getTable();
        $expr = trim(Receivable::groupKeySqlExpression());
        $maxDateSql = DB::connection()->getDriverName() === 'sqlite'
            ? 'MAX(date)'
            : 'MAX(`date`)';

        $receivableGroups = DB::table($table)
            ->selectRaw("{$expr} as ar_group_key")
            ->selectRaw('MAX(customer_name) as agg_customer_name')
            ->selectRaw('MAX(TRIM(COALESCE(customer_code, ?))) as agg_customer_code', [''])
            ->selectRaw('COUNT(*) as invoice_count')
            ->selectRaw('SUM(amount) as total_amount')
            ->selectRaw('SUM(received) as total_received')
            ->selectRaw("{$maxDateSql} as latest_invoice_date")
            ->selectRaw('MAX(payment_received_at) as latest_payment_at')
            ->groupByRaw($expr)
            ->orderByDesc(DB::raw($maxDateSql))
            ->get();

        $totalGroupsCount = $receivableGroups->count();

        $search = trim((string) $request->input('search', ''));

        $totals = Receivable::query()
            ->selectRaw('
                COALESCE(SUM(amount), 0)                   AS total_amount,
                COALESCE(SUM(received), 0)                 AS total_received,
                COALESCE(SUM(amount) - SUM(received), 0)   AS total_remaining
            ')
            ->first();
        $totals->total_purchase_offsets = Schema::hasTable('customer_receivable_purchase_offsets')
            ? (float) (CustomerReceivablePurchaseOffset::query()->sum('amount') ?? 0)
            : 0.0;

        return view('receivables.index', compact(
            'receivableGroups',
            'totals',
            'search',
            'totalGroupsCount',
        ));
    }

    public function showGroup(string $groupKey): View
    {
        $decoded = Receivable::decodeGroupKeyFromRoute($groupKey);
        if ($decoded === null || ! $this->isValidGroupKeyString($decoded)) {
            throw new NotFoundHttpException;
        }

        $expr = trim(Receivable::groupKeySqlExpression());
        $receivables = Receivable::query()
            ->whereRaw("({$expr}) = ?", [$decoded])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $offsetTotals = CustomerReceivablePurchaseOffset::query()
            ->whereIn('receivable_id', $receivables->pluck('id'))
            ->selectRaw('receivable_id, SUM(amount) as total_amount')
            ->groupBy('receivable_id')
            ->pluck('total_amount', 'receivable_id');
        $paymentTotals = CustomerLedgerEntry::query()
            ->where('source_type', 'payment_received')
            ->whereIn('source_id', $receivables->pluck('id'))
            ->selectRaw('source_id, SUM(credit) as total_amount')
            ->groupBy('source_id')
            ->pluck('total_amount', 'source_id');

        foreach ($receivables as $receivable) {
            $offset = round((float) ($offsetTotals[$receivable->id] ?? 0), 2);
            $payment = round((float) ($paymentTotals[$receivable->id] ?? 0), 2);
            $received = round((float) $receivable->received, 2);
            $remaining = round((float) $receivable->amount - $received, 2);
            $receivable->setAttribute(
                'purchase_offset_total',
                $offset
            );
            $receivable->setAttribute('direct_payment_total', $payment);
            $receivable->setAttribute('remaining_balance', $remaining);
        }
        $totalDirectPayments = round((float) $receivables->sum(fn (Receivable $r) => (float) ($r->direct_payment_total ?? 0)), 2);
        $totalPurchaseOffsets = round((float) $receivables->sum(fn (Receivable $r) => (float) ($r->purchase_offset_total ?? 0)), 2);
        $openReceivables = $receivables->filter(fn (Receivable $r) => (float) $r->remaining_balance > 0.00001)->values();
        $settledReceivables = $receivables->filter(fn (Receivable $r) => (float) $r->remaining_balance <= 0.00001)->values();

        if ($receivables->isEmpty()) {
            throw new NotFoundHttpException;
        }

        $displayName = $receivables->first()->customer_name
            ?: $receivables->first()->customer_code
            ?: ('#'.$receivables->first()->id);

        $groupTotals = [
            'total_bill' => round((float) $receivables->sum('amount'), 2),
            'total_received' => round((float) $receivables->sum('received'), 2),
            'total_direct_payments' => $totalDirectPayments,
            'total_purchase_offsets' => $totalPurchaseOffsets,
            'total_remaining' => round((float) $receivables->sum('amount') - (float) $receivables->sum('received'), 2),
        ];

        $groupPayments = ReceivableGroupPayment::query()
            ->where('group_key', $decoded)
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get();

        $groupKeyEncoded = Receivable::encodeGroupKeyForRoute($decoded);

        return view('receivables.group', [
            'groupKey' => $decoded,
            'groupKeyEncoded' => $groupKeyEncoded,
            'displayName' => $displayName,
            'receivables' => $receivables,
            'openReceivables' => $openReceivables,
            'settledReceivables' => $settledReceivables,
            'groupTotals' => $groupTotals,
            'groupPayments' => $groupPayments,
        ]);
    }

    public function storeGroupPayment(Request $request, string $groupKey): RedirectResponse
    {
        $decoded = Receivable::decodeGroupKeyFromRoute($groupKey);
        if ($decoded === null || ! $this->isValidGroupKeyString($decoded)) {
            throw new NotFoundHttpException;
        }

        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $fifoList = $this->receivablesFifoForGroup($decoded);
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

            $gp = ReceivableGroupPayment::create([
                'group_key' => $decoded,
                'payment_date' => $paymentDate,
                'amount' => $amount,
            ]);
            $this->applyGroupPaymentSlices($gp, $paymentDate, $allocations);

            DB::commit();

            return redirect()->route('receivables.group', ['groupKey' => Receivable::encodeGroupKeyForRoute($decoded)])
                ->with('success', 'Combined payment recorded.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to record combined payment: '.$e->getMessage());
        }
    }

    public function editGroupPayment(string $groupKey, ReceivableGroupPayment $receivableGroupPayment): View
    {
        $this->assertGroupPaymentMatchesRouteKey($groupKey, $receivableGroupPayment);

        $decoded = $receivableGroupPayment->group_key;
        $fifoList = $this->receivablesFifoForGroup($decoded);
        if ($fifoList->isEmpty()) {
            throw new NotFoundHttpException;
        }

        $remaining = $this->totalGroupRemaining($fifoList);
        $maxAllowed = round($remaining + (float) $receivableGroupPayment->amount, 2);
        $groupKeyEncoded = Receivable::encodeGroupKeyForRoute($decoded);

        return view('receivables.group-payment-edit', [
            'groupPayment' => $receivableGroupPayment,
            'groupKeyEncoded' => $groupKeyEncoded,
            'displayName' => $fifoList->first()->customer_name
                ?: $fifoList->first()->customer_code
                ?: ('#'.$fifoList->first()->id),
            'maxAllowed' => $maxAllowed,
        ]);
    }

    public function updateGroupPayment(Request $request, string $groupKey, ReceivableGroupPayment $receivableGroupPayment): RedirectResponse
    {
        $this->assertGroupPaymentMatchesRouteKey($groupKey, $receivableGroupPayment);
        $receivableGroupPayment->load('lines');

        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $decoded = $receivableGroupPayment->group_key;
        $amount = round((float) $validated['amount'], 2);
        $oldAmount = (float) $receivableGroupPayment->amount;

        $fifoList = $this->receivablesFifoForGroup($decoded);
        $remaining = $this->totalGroupRemaining($fifoList);
        $maxAllowed = round($remaining + $oldAmount, 2);
        if ($amount > $maxAllowed + 0.00001) {
            return redirect()->back()->withInput()
                ->with('error', 'Amount cannot exceed combined remaining balance plus this payment ('.number_format($maxAllowed, 2).').');
        }

        $paymentDate = Carbon::parse($validated['payment_date'], config('app.timezone'))->startOfDay();

        try {
            DB::beginTransaction();

            $this->reverseGroupPayment($receivableGroupPayment);
            $receivableGroupPayment->update([
                'amount' => $amount,
                'payment_date' => $paymentDate,
            ]);
            $fifoList = $this->receivablesFifoForGroup($decoded);
            $allocations = $this->allocateFifo($fifoList, $amount);
            $sumAlloc = round(array_sum($allocations), 2);
            if (abs($sumAlloc - $amount) > 0.02 || $sumAlloc < 0.01) {
                DB::rollBack();

                return redirect()->back()->withInput()
                    ->with('error', 'Unable to allocate payment across invoices.');
            }
            $this->applyGroupPaymentSlices($receivableGroupPayment, $paymentDate, $allocations);

            DB::commit();

            return redirect()->route('receivables.group', ['groupKey' => Receivable::encodeGroupKeyForRoute($decoded)])
                ->with('success', 'Combined payment updated.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to update combined payment: '.$e->getMessage());
        }
    }

    public function destroyGroupPayment(string $groupKey, ReceivableGroupPayment $receivableGroupPayment): RedirectResponse
    {
        $this->assertGroupPaymentMatchesRouteKey($groupKey, $receivableGroupPayment);
        $receivableGroupPayment->load('lines');
        $decoded = $receivableGroupPayment->group_key;

        try {
            DB::beginTransaction();

            $this->reverseGroupPayment($receivableGroupPayment);
            $receivableGroupPayment->delete();

            DB::commit();

            return redirect()->route('receivables.group', ['groupKey' => Receivable::encodeGroupKeyForRoute($decoded)])
                ->with('success', 'Combined payment removed.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to remove combined payment: '.$e->getMessage());
        }
    }

    private function isValidGroupKeyString(string $key): bool
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

    public function create(): RedirectResponse
    {
        return redirect()->route('receivables.index')
            ->with('error', 'Receivables are created automatically from sales.');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('receivables.index')
            ->with('error', 'Receivables are created automatically from sales.');
    }

    public function show(Receivable $receivable): RedirectResponse
    {
        return redirect()->route('receivables.index');
    }

    public function edit(Receivable $receivable): View
    {
        $receivable->load('paymentLedgerEntries');

        return view('receivables.edit', compact('receivable'));
    }

    public function update(Request $request, Receivable $receivable): RedirectResponse
    {
        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'received' => ['required', 'numeric', 'min:0'],
        ]);

        $paymentDate = Carbon::parse($validated['payment_date'], config('app.timezone'))->startOfDay();

        $payment = (float) $validated['received'];
        $maxReceivable = (float) $receivable->amount - (float) $receivable->received;

        if ($payment > $maxReceivable) {
            return redirect()->back()->withInput()
                ->with('error', 'Amount cannot exceed remaining balance ('.number_format($maxReceivable, 2).').');
        }

        try {
            DB::beginTransaction();

            $customer = $receivable->customer_code
                ? Customer::where('customer_code', $receivable->customer_code)->first()
                : null;

            if ($customer) {
                CustomerLedgerEntry::create([
                    'customer_id' => $customer->id,
                    'date' => $paymentDate,
                    'description' => 'Payment Received',
                    'reference' => $receivable->invoice_number,
                    'debit' => 0,
                    'credit' => $payment,
                    'source_type' => 'payment_received',
                    'source_id' => $receivable->id,
                ]);
                $this->syncReceivedFromLedger($receivable);
            } else {
                $receivable->increment('received', $payment);
                $receivable->update(['payment_received_at' => $paymentDate]);
            }

            DB::commit();

            return redirect()->route('receivables.index')
                ->with('success', 'Payment recorded successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to record payment: '.$e->getMessage());
        }
    }

    public function updatePayment(Request $request, Receivable $receivable, CustomerLedgerEntry $customerLedgerEntry): RedirectResponse
    {
        $this->assertPaymentLedgerBelongsToReceivable($receivable, $customerLedgerEntry);

        $id = $customerLedgerEntry->id;
        $validated = $request->validate([
            "date_{$id}" => ['required', 'date'],
            "credit_{$id}" => ['required', 'numeric', 'min:0.01'],
        ]);

        $credit = round((float) $validated["credit_{$id}"], 2);
        $otherSum = (float) CustomerLedgerEntry::query()
            ->where('source_type', 'payment_received')
            ->where('source_id', $receivable->id)
            ->where('id', '!=', $customerLedgerEntry->id)
            ->sum('credit');

        if ($otherSum + $credit > (float) $receivable->amount + 0.00001) {
            return redirect()->back()->withInput()
                ->with('error', 'Total payments cannot exceed the bill amount ('.number_format((float) $receivable->amount, 2).').');
        }

        try {
            DB::beginTransaction();

            $customerLedgerEntry->update([
                'date' => Carbon::parse($validated["date_{$id}"], config('app.timezone'))->startOfDay(),
                'credit' => $credit,
            ]);

            $this->syncReceivedFromLedger($receivable);

            DB::commit();

            return redirect()->route('receivables.edit', $receivable)
                ->with('success', 'Payment updated.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to update payment: '.$e->getMessage());
        }
    }

    public function destroyPayment(Receivable $receivable, CustomerLedgerEntry $customerLedgerEntry): RedirectResponse
    {
        $this->assertPaymentLedgerBelongsToReceivable($receivable, $customerLedgerEntry);

        try {
            DB::beginTransaction();

            $customerLedgerEntry->delete();
            $this->syncReceivedFromLedger($receivable);

            DB::commit();

            return redirect()->route('receivables.edit', $receivable)
                ->with('success', 'Payment removed.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to remove payment: '.$e->getMessage());
        }
    }

    public function adjustReceivedWithoutLedger(Request $request, Receivable $receivable): RedirectResponse
    {
        if ($receivable->paymentLedgerEntries()->exists()) {
            return redirect()->route('receivables.edit', $receivable)
                ->with('error', 'This receivable has ledger payments; edit those rows instead.');
        }

        $validated = $request->validate([
            'received' => ['required', 'numeric', 'min:0'],
            'payment_received_at' => ['nullable', 'date'],
        ]);

        $received = round((float) $validated['received'], 2);
        if ($received > (float) $receivable->amount + 0.00001) {
            return redirect()->back()->withInput()
                ->with('error', 'Received cannot exceed the bill amount ('.number_format((float) $receivable->amount, 2).').');
        }

        $paymentReceivedAt = null;
        if ($received > 0) {
            $paymentReceivedAt = ! empty($validated['payment_received_at'])
                ? Carbon::parse($validated['payment_received_at'], config('app.timezone'))->startOfDay()
                : $receivable->payment_received_at;
        }

        $receivable->update([
            'received' => $received,
            'payment_received_at' => $paymentReceivedAt,
        ]);

        return redirect()->route('receivables.edit', $receivable)
            ->with('success', 'Received amount updated.');
    }

    public function destroy(Receivable $receivable): RedirectResponse
    {
        return redirect()->route('receivables.index')
            ->with('error', 'Receivable deletion is not available.');
    }

    private function syncReceivedFromLedger(Receivable $receivable): void
    {
        $receivable->refresh();
        app(PurchaseReceivableOffsetService::class)->syncReceivable($receivable);
    }

    private function receivablesFifoForGroup(string $decoded): Collection
    {
        $expr = trim(Receivable::groupKeySqlExpression());

        return Receivable::query()
            ->whereRaw("({$expr}) = ?", [$decoded])
            ->orderBy('date')
            ->orderBy('id')
            ->get();
    }

    private function totalGroupRemaining(Collection $receivablesOldestFirst): float
    {
        $sum = 0.0;
        foreach ($receivablesOldestFirst as $r) {
            $sum += (float) $r->amount - (float) $r->received;
        }

        return round($sum, 2);
    }

    /**
     * @return array<int, float> receivable_id => slice amount
     */
    private function allocateFifo(Collection $receivablesOldestFirst, float $paymentAmount): array
    {
        $left = round($paymentAmount, 2);
        $out = [];
        foreach ($receivablesOldestFirst as $r) {
            if ($left <= 0) {
                break;
            }
            $remaining = round((float) $r->amount - (float) $r->received, 2);
            if ($remaining <= 0) {
                continue;
            }
            $slice = min($left, $remaining);
            if ($slice > 0) {
                $out[(int) $r->id] = $slice;
                $left = round($left - $slice, 2);
            }
        }

        return $out;
    }

    /**
     * @param  array<int, float>  $allocations
     */
    private function applyGroupPaymentSlices(ReceivableGroupPayment $groupPayment, Carbon $paymentDate, array $allocations): void
    {
        foreach ($allocations as $receivableId => $slice) {
            $slice = round((float) $slice, 2);
            if ($slice <= 0) {
                continue;
            }
            $receivable = Receivable::query()->findOrFail($receivableId);
            $customer = $receivable->customer_code
                ? Customer::where('customer_code', $receivable->customer_code)->first()
                : null;

            if ($customer) {
                $ledgerAttrs = [
                    'customer_id' => $customer->id,
                    'date' => $paymentDate,
                    'description' => 'Payment Received',
                    'reference' => $receivable->invoice_number,
                    'debit' => 0,
                    'credit' => $slice,
                    'source_type' => 'payment_received',
                    'source_id' => $receivable->id,
                ];
                if (Schema::hasColumn('customer_ledger_entries', 'receivable_group_payment_id')) {
                    $ledgerAttrs['receivable_group_payment_id'] = $groupPayment->id;
                }
                $entry = CustomerLedgerEntry::create($ledgerAttrs);
                if (Schema::hasTable('customer_ledger_receivable_group_payments')) {
                    CustomerLedgerReceivableGroupPayment::create([
                        'customer_ledger_entry_id' => $entry->id,
                        'receivable_group_payment_id' => $groupPayment->id,
                    ]);
                }
                ReceivableGroupPaymentLine::create([
                    'receivable_group_payment_id' => $groupPayment->id,
                    'receivable_id' => $receivable->id,
                    'amount' => $slice,
                    'customer_ledger_entry_id' => $entry->id,
                ]);
                $this->syncReceivedFromLedger($receivable);
            } else {
                $receivable->increment('received', $slice);
                $receivable->refresh();
                $currentAt = $receivable->payment_received_at
                    ? Carbon::parse($receivable->payment_received_at, config('app.timezone'))->startOfDay()
                    : null;
                $nextAt = $paymentDate;
                if ($currentAt && $currentAt->gt($paymentDate)) {
                    $nextAt = $currentAt;
                }
                $receivable->update(['payment_received_at' => $nextAt]);
                ReceivableGroupPaymentLine::create([
                    'receivable_group_payment_id' => $groupPayment->id,
                    'receivable_id' => $receivable->id,
                    'amount' => $slice,
                    'customer_ledger_entry_id' => null,
                ]);
            }
        }
    }

    private function reverseGroupPayment(ReceivableGroupPayment $payment): void
    {
        $payment->loadMissing('lines');
        $batchId = (int) $payment->id;
        foreach ($payment->lines as $line) {
            $this->reverseGroupPaymentLine($line, $batchId);
            $line->delete();
        }
    }

    private function reverseGroupPaymentLine(ReceivableGroupPaymentLine $line, int $excludeBatchId): void
    {
        $receivable = $line->receivable_id
            ? Receivable::query()->find($line->receivable_id)
            : null;

        if ($line->customer_ledger_entry_id) {
            $entry = CustomerLedgerEntry::query()->find($line->customer_ledger_entry_id);
            if ($entry) {
                $entry->delete();
            }
            if ($receivable) {
                $this->syncReceivedFromLedger($receivable);
            }

            return;
        }

        if (! $receivable) {
            return;
        }

        $slice = (float) $line->amount;
        $receivable->decrement('received', $slice);
        $receivable->refresh();
        $recv = round((float) $receivable->received, 2);
        if ($recv <= 0) {
            $receivable->update([
                'received' => 0,
                'payment_received_at' => null,
            ]);
        } else {
            $maxOrphan = $this->maxOrphanGroupPaymentDateForReceivable($receivable, $excludeBatchId);
            if ($maxOrphan) {
                $receivable->update(['payment_received_at' => $maxOrphan]);
            }
        }
    }

    private function maxOrphanGroupPaymentDateForReceivable(Receivable $receivable, int $excludeGroupPaymentId): ?Carbon
    {
        $max = DB::table('receivable_group_payment_lines')
            ->join('receivable_group_payments as gp', 'gp.id', '=', 'receivable_group_payment_lines.receivable_group_payment_id')
            ->where('receivable_group_payment_lines.receivable_id', $receivable->id)
            ->whereNull('receivable_group_payment_lines.customer_ledger_entry_id')
            ->where('receivable_group_payment_lines.receivable_group_payment_id', '!=', $excludeGroupPaymentId)
            ->max('gp.payment_date');

        return $max ? Carbon::parse($max, config('app.timezone'))->startOfDay() : null;
    }

    private function assertGroupPaymentMatchesRouteKey(string $groupKey, ReceivableGroupPayment $payment): void
    {
        $decoded = Receivable::decodeGroupKeyFromRoute($groupKey);
        if ($decoded === null || $payment->group_key !== $decoded) {
            abort(404);
        }
    }

    private function assertPaymentLedgerBelongsToReceivable(Receivable $receivable, CustomerLedgerEntry $entry): void
    {
        if ($entry->source_type !== 'payment_received' || (int) $entry->source_id !== (int) $receivable->id) {
            abort(404);
        }
    }
}
