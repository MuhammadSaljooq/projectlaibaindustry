<?php

namespace App\Http\Controllers;

use App\Models\InternationalPayableGroupPayment;
use App\Models\InternationalPayableGroupPaymentLine;
use App\Models\InternationalPayablePayment;
use App\Models\InternationalPurchaseOrder;
use App\Services\SupplierLedgerSync;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InternationalPayableController extends Controller
{
    public function index(): View
    {
        $orders = InternationalPurchaseOrder::query()
            ->with(['supplier', 'lines'])
            ->withSum('payablePayments', 'amount')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();
        $orderGroups = $orders->groupBy(fn (InternationalPurchaseOrder $order) => $this->orderGroupKey($order))
            ->map(function (Collection $slice, string $key): array {
                /** @var InternationalPurchaseOrder $first */
                $first = $slice->first();
                $latest = $slice->sortByDesc(fn (InternationalPurchaseOrder $o) => $o->date?->timestamp ?? 0)->first();
                $latestDate = $latest?->date;
                $bill = (float) $slice->sum(fn (InternationalPurchaseOrder $o) => (float) $o->total_amount);
                $paid = (float) $slice->sum(fn (InternationalPurchaseOrder $o) => (float) ($o->payable_payments_sum_amount ?? 0));

                return [
                    'group_key_encoded' => $this->encodeGroupKeyForRoute($key),
                    'display_name' => $first->supplier?->name ?: 'Unknown vendor',
                    'invoice_count' => $slice->count(),
                    'latest_invoice_date' => $latestDate,
                    'total_bill' => $bill,
                    'total_paid' => $paid,
                    'total_balance' => max(0, $bill - $paid),
                ];
            })
            ->sortByDesc(fn (array $g) => $g['latest_invoice_date']?->timestamp ?? 0)
            ->values();

        $billTotal = (float) InternationalPurchaseOrder::query()->sum('total_amount');
        $paidTotal = (float) InternationalPayablePayment::query()->sum('amount');
        $outstanding = max(0, $billTotal - $paidTotal);

        $currencySymbol = $this->internationalPayableCurrency();

        return view('international-payables.index', [
            'orderGroups' => $orderGroups,
            'billTotal' => $billTotal,
            'paidTotal' => $paidTotal,
            'outstanding' => $outstanding,
            'currencySymbol' => $currencySymbol,
            'totalInvoiceCount' => $orders->count(),
        ]);
    }

    public function showGroup(string $groupKey): View
    {
        $decoded = $this->decodeGroupKeyFromRoute($groupKey);
        if ($decoded === null || ! $this->isValidGroupKey($decoded)) {
            throw new NotFoundHttpException;
        }

        $query = InternationalPurchaseOrder::query()
            ->with(['supplier', 'lines', 'payablePayments' => fn ($q) => $q->orderByDesc('payment_date')->orderByDesc('id')])
            ->withSum('payablePayments', 'amount')
            ->orderByDesc('date')
            ->orderByDesc('id');

        if (str_starts_with($decoded, 'id:')) {
            $query->where('supplier_id', (int) substr($decoded, strlen('id:')));
        } elseif (str_starts_with($decoded, 'name:')) {
            $name = substr($decoded, strlen('name:'));
            $query->whereHas('supplier', fn ($q) => $q->whereRaw('LOWER(TRIM(name)) = ?', [$name]));
        } elseif (str_starts_with($decoded, 'order:')) {
            $query->where('id', (int) substr($decoded, strlen('order:')));
        }

        $orders = $query->get();
        if ($orders->isEmpty()) {
            throw new NotFoundHttpException;
        }

        $billTotal = (float) $orders->sum(fn (InternationalPurchaseOrder $o) => (float) $o->total_amount);
        $paidTotal = (float) $orders->sum(fn (InternationalPurchaseOrder $o) => (float) ($o->payable_payments_sum_amount ?? 0));
        $outstanding = max(0, $billTotal - $paidTotal);
        foreach ($orders as $order) {
            $paid = round((float) ($order->payable_payments_sum_amount ?? 0), 2);
            $order->setAttribute('direct_payment_total', $paid);
            $order->setAttribute('remaining_balance', round((float) $order->total_amount - $paid, 2));
        }
        $openOrders = $orders->filter(fn (InternationalPurchaseOrder $o) => (float) $o->remaining_balance > 0.00001)->values();
        $settledOrders = $orders->filter(fn (InternationalPurchaseOrder $o) => (float) $o->remaining_balance <= 0.00001)->values();
        $groupTotals = [
            'total_bill' => round($billTotal, 2),
            'total_received' => round($paidTotal, 2),
            'total_direct_payments' => round($paidTotal, 2),
            'total_remaining' => round($outstanding, 2),
        ];
        $groupPayments = Schema::hasTable('international_payable_group_payments')
            ? InternationalPayableGroupPayment::query()
                ->where('group_key', $decoded)
                ->orderByDesc('payment_date')
                ->orderByDesc('id')
                ->get()
            : collect();

        return view('international-payables.group', [
            'orders' => $orders,
            'openOrders' => $openOrders,
            'settledOrders' => $settledOrders,
            'groupPayments' => $groupPayments,
            'groupTotals' => $groupTotals,
            'displayName' => $orders->first()->supplier?->name ?: 'Unknown vendor',
            'billTotal' => $billTotal,
            'paidTotal' => $paidTotal,
            'outstanding' => $outstanding,
            'currencySymbol' => $this->internationalPayableCurrency(),
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
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $fifoList = $this->ordersFifoForGroup($decoded);
        if ($fifoList->isEmpty()) {
            throw new NotFoundHttpException;
        }

        $remaining = $this->totalGroupRemaining($fifoList);
        $amount = round((float) $validated['amount'], 2);
        if ($amount > $remaining + 0.00001) {
            return redirect()->back()->withInput()
                ->with('error', 'Amount cannot exceed combined remaining balance ('.number_format($remaining, 2).').');
        }

        $allocations = $this->allocateProRata($fifoList, $amount);
        $sumAlloc = round(array_sum($allocations), 2);
        if (abs($sumAlloc - $amount) > 0.02 || $sumAlloc < 0.01) {
            return redirect()->back()->withInput()
                ->with('error', 'Unable to allocate payment across invoices.');
        }

        $paymentDate = Carbon::parse($validated['payment_date'], config('app.timezone'))->startOfDay();
        $notes = $validated['notes'] ?? null;

        try {
            DB::beginTransaction();

            $groupPayment = Schema::hasTable('international_payable_group_payments')
                ? InternationalPayableGroupPayment::create([
                    'group_key' => $decoded,
                    'payment_date' => $paymentDate,
                    'amount' => $amount,
                    'notes' => $notes,
                ])
                : null;
            $this->applyGroupPaymentSlices($groupPayment, $paymentDate, $allocations, $notes);

            DB::commit();

            return redirect()->route('international-payables.group', ['groupKey' => $this->encodeGroupKeyForRoute($decoded)])
                ->with('success', 'Combined payment recorded.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to record combined payment: '.$e->getMessage());
        }
    }

    public function editGroupPayment(string $groupKey, InternationalPayableGroupPayment $internationalPayableGroupPayment): View
    {
        $this->assertGroupPaymentMatchesRouteKey($groupKey, $internationalPayableGroupPayment);

        $decoded = $internationalPayableGroupPayment->group_key;
        $fifoList = $this->ordersFifoForGroup($decoded);
        if ($fifoList->isEmpty()) {
            throw new NotFoundHttpException;
        }

        $remaining = $this->totalGroupRemaining($fifoList);
        $maxAllowed = round($remaining + (float) $internationalPayableGroupPayment->amount, 2);
        $groupKeyEncoded = $this->encodeGroupKeyForRoute($decoded);

        return view('international-payables.group-payment-edit', [
            'groupPayment' => $internationalPayableGroupPayment,
            'groupKeyEncoded' => $groupKeyEncoded,
            'displayName' => $fifoList->first()->supplier?->name ?: 'Unknown vendor',
            'maxAllowed' => $maxAllowed,
            'currencySymbol' => $this->internationalPayableCurrency(),
        ]);
    }

    public function updateGroupPayment(Request $request, string $groupKey, InternationalPayableGroupPayment $internationalPayableGroupPayment): RedirectResponse
    {
        $this->assertGroupPaymentMatchesRouteKey($groupKey, $internationalPayableGroupPayment);
        $internationalPayableGroupPayment->load('lines');

        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $decoded = $internationalPayableGroupPayment->group_key;
        $amount = round((float) $validated['amount'], 2);
        $oldAmount = (float) $internationalPayableGroupPayment->amount;

        $fifoList = $this->ordersFifoForGroup($decoded);
        $remaining = $this->totalGroupRemaining($fifoList);
        $maxAllowed = round($remaining + $oldAmount, 2);
        if ($amount > $maxAllowed + 0.00001) {
            return redirect()->back()->withInput()
                ->with('error', 'Amount cannot exceed combined remaining balance plus this payment ('.number_format($maxAllowed, 2).').');
        }

        $paymentDate = Carbon::parse($validated['payment_date'], config('app.timezone'))->startOfDay();
        $notes = $validated['notes'] ?? null;

        try {
            DB::beginTransaction();

            $this->reverseGroupPayment($internationalPayableGroupPayment);
            $internationalPayableGroupPayment->update([
                'amount' => $amount,
                'payment_date' => $paymentDate,
                'notes' => $notes,
            ]);
            $fifoList = $this->ordersFifoForGroup($decoded);
            $allocations = $this->allocateProRata($fifoList, $amount);
            $sumAlloc = round(array_sum($allocations), 2);
            if (abs($sumAlloc - $amount) > 0.02 || $sumAlloc < 0.01) {
                DB::rollBack();

                return redirect()->back()->withInput()
                    ->with('error', 'Unable to allocate payment across invoices.');
            }
            $this->applyGroupPaymentSlices($internationalPayableGroupPayment, $paymentDate, $allocations, $notes);

            DB::commit();

            return redirect()->route('international-payables.group', ['groupKey' => $this->encodeGroupKeyForRoute($decoded)])
                ->with('success', 'Combined payment updated.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to update combined payment: '.$e->getMessage());
        }
    }

    public function destroyGroupPayment(string $groupKey, InternationalPayableGroupPayment $internationalPayableGroupPayment): RedirectResponse
    {
        $this->assertGroupPaymentMatchesRouteKey($groupKey, $internationalPayableGroupPayment);
        $internationalPayableGroupPayment->load('lines');
        $decoded = $internationalPayableGroupPayment->group_key;

        try {
            DB::beginTransaction();

            $this->reverseGroupPayment($internationalPayableGroupPayment);
            $internationalPayableGroupPayment->delete();

            DB::commit();

            return redirect()->route('international-payables.group', ['groupKey' => $this->encodeGroupKeyForRoute($decoded)])
                ->with('success', 'Combined payment removed.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to remove combined payment: '.$e->getMessage());
        }
    }

    public function pay(InternationalPurchaseOrder $international_purchase): View
    {
        $international_purchase->load(['supplier', 'lines', 'payablePayments' => fn ($q) => $q->orderByDesc('payment_date')->orderByDesc('id')]);

        $paid = (float) $international_purchase->payablePayments()->sum('amount');
        $bill = (float) $international_purchase->total_amount;
        $balance = max(0, $bill - $paid);

        $currencySymbol = $this->internationalPayableCurrency();

        return view('international-payables.pay', [
            'order' => $international_purchase,
            'paid' => $paid,
            'balance' => $balance,
            'payments' => $international_purchase->payablePayments,
            'currencySymbol' => $currencySymbol,
        ]);
    }

    public function storePayment(Request $request, InternationalPurchaseOrder $international_purchase): RedirectResponse
    {
        $paid = (float) $international_purchase->payablePayments()->sum('amount');
        $bill = (float) $international_purchase->total_amount;
        $balance = max(0, $bill - $paid);

        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:'.$balance,
            ],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'amount.max' => 'Payment cannot exceed the remaining balance ('.number_format($balance, 2).').',
        ]);

        try {
            DB::beginTransaction();

            $payment = InternationalPayablePayment::create([
                'international_purchase_order_id' => $international_purchase->id,
                'payment_date' => Carbon::parse($validated['payment_date'], config('app.timezone'))->startOfDay(),
                'amount' => $validated['amount'],
                'notes' => $validated['notes'] ?? null,
            ]);

            SupplierLedgerSync::recordPayment($payment, $international_purchase);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to record payment: '.$e->getMessage());
        }

        return redirect()->route('international-payables.index')
            ->with('success', 'Payment of '.number_format((float) $validated['amount'], 2).' recorded successfully.');
    }

    public function updatePayment(Request $request, InternationalPurchaseOrder $international_purchase, InternationalPayablePayment $internationalPayablePayment): RedirectResponse
    {
        $this->assertPaymentBelongsToOrder($international_purchase, $internationalPayablePayment);
        if (Schema::hasColumn('international_payable_payments', 'international_payable_group_payment_id')
            && $internationalPayablePayment->international_payable_group_payment_id) {
            return redirect()->back()
                ->with('error', 'This payment belongs to a combined batch. Edit it from International Payables group page.');
        }

        $id = $internationalPayablePayment->id;
        $validated = $request->validate([
            "payment_date_{$id}" => ['required', 'date'],
            "amount_{$id}" => ['required', 'numeric', 'min:0.01'],
            "notes_{$id}" => ['nullable', 'string', 'max:500'],
        ]);

        $nextAmount = round((float) $validated["amount_{$id}"], 2);
        $bill = (float) $international_purchase->total_amount;
        $otherSum = (float) InternationalPayablePayment::query()
            ->where('international_purchase_order_id', $international_purchase->id)
            ->where('id', '!=', $internationalPayablePayment->id)
            ->sum('amount');

        if ($otherSum + $nextAmount > $bill + 0.00001) {
            return redirect()->back()->withInput()
                ->with('error', 'Total payments cannot exceed the bill amount ('.number_format($bill, 2).').');
        }

        try {
            DB::beginTransaction();

            $internationalPayablePayment->update([
                'payment_date' => Carbon::parse($validated["payment_date_{$id}"], config('app.timezone'))->startOfDay(),
                'amount' => $nextAmount,
                'notes' => $validated["notes_{$id}"] ?? null,
            ]);

            DB::table('supplier_ledger_entries')
                ->where('source_type', 'international_payable_payment')
                ->where('source_id', $internationalPayablePayment->id)
                ->update([
                    'supplier_id' => $international_purchase->supplier_id,
                    'date' => $internationalPayablePayment->payment_date,
                    'debit' => $nextAmount,
                    'notes' => $validated["notes_{$id}"] ?? null,
                    'updated_at' => now(),
                ]);

            DB::commit();

            return redirect()->route('international-payables.pay', $international_purchase)
                ->with('success', 'Payment updated.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to update payment: '.$e->getMessage());
        }
    }

    public function destroyPayment(InternationalPurchaseOrder $international_purchase, InternationalPayablePayment $internationalPayablePayment): RedirectResponse
    {
        $this->assertPaymentBelongsToOrder($international_purchase, $internationalPayablePayment);
        $returnGroupKey = (string) request()->query('groupKey', '');
        $groupPaymentId = (int) ($internationalPayablePayment->international_payable_group_payment_id ?? 0);

        try {
            DB::beginTransaction();
            if ($groupPaymentId > 0 && Schema::hasTable('international_payable_group_payments')) {
                $groupPayment = InternationalPayableGroupPayment::query()->find($groupPaymentId);
                if ($groupPayment) {
                    $this->reverseGroupPayment($groupPayment);
                    $groupPayment->delete();
                    DB::commit();

                    if ($returnGroupKey !== '' && $this->decodeGroupKeyFromRoute($returnGroupKey) !== null) {
                        return redirect()->route('international-payables.group', ['groupKey' => $returnGroupKey])
                            ->with('success', 'Combined payment batch removed.');
                    }

                    return redirect()->route('international-payables.pay', $international_purchase)
                        ->with('success', 'Combined payment batch removed.');
                }
            }

            DB::table('supplier_ledger_entries')
                ->where('source_type', 'international_payable_payment')
                ->where('source_id', $internationalPayablePayment->id)
                ->delete();

            $internationalPayablePayment->delete();

            DB::commit();

            if ($returnGroupKey !== '' && $this->decodeGroupKeyFromRoute($returnGroupKey) !== null) {
                return redirect()->route('international-payables.group', ['groupKey' => $returnGroupKey])
                    ->with('success', 'Payment removed.');
            }

            return redirect()->route('international-payables.pay', $international_purchase)
                ->with('success', 'Payment removed.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to remove payment: '.$e->getMessage());
        }
    }

    private function assertPaymentBelongsToOrder(InternationalPurchaseOrder $order, InternationalPayablePayment $payment): void
    {
        if ((int) $payment->international_purchase_order_id !== (int) $order->id) {
            abort(404);
        }
    }

    private function orderGroupKey(InternationalPurchaseOrder $order): string
    {
        $supplierName = trim((string) ($order->supplier?->name ?? ''));
        if ($supplierName !== '') {
            return 'name:'.mb_strtolower($supplierName);
        }
        if ($order->supplier_id) {
            return 'id:'.$order->supplier_id;
        }

        return 'order:'.$order->id;
    }

    private function isValidGroupKey(string $key): bool
    {
        if (str_starts_with($key, 'id:')) {
            return (bool) preg_match('/^id:\d+$/', $key);
        }
        if (str_starts_with($key, 'order:')) {
            return (bool) preg_match('/^order:\d+$/', $key);
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

    private function ordersFifoForGroup(string $decoded): Collection
    {
        $query = InternationalPurchaseOrder::query()
            ->withSum('payablePayments', 'amount')
            ->orderBy('date')
            ->orderBy('id');

        if (str_starts_with($decoded, 'id:')) {
            $query->where('supplier_id', (int) substr($decoded, strlen('id:')));
        } elseif (str_starts_with($decoded, 'name:')) {
            $name = substr($decoded, strlen('name:'));
            $query->whereHas('supplier', fn ($q) => $q->whereRaw('LOWER(TRIM(name)) = ?', [$name]));
        } elseif (str_starts_with($decoded, 'order:')) {
            $query->where('id', (int) substr($decoded, strlen('order:')));
        }

        return $query->get();
    }

    private function totalGroupRemaining(Collection $ordersOldestFirst): float
    {
        $sum = 0.0;
        foreach ($ordersOldestFirst as $order) {
            $paid = (float) ($order->payable_payments_sum_amount ?? 0);
            $sum += (float) $order->total_amount - $paid;
        }

        return round($sum, 2);
    }

    /**
     * @return array<int, float> order_id => slice amount
     */
    private function allocateProRata(Collection $ordersOldestFirst, float $paymentAmount): array
    {
        $amount = round($paymentAmount, 2);
        if ($amount <= 0) {
            return [];
        }

        $remainingByOrder = [];
        $totalRemaining = 0.0;
        foreach ($ordersOldestFirst as $order) {
            $paid = round((float) ($order->payable_payments_sum_amount ?? 0), 2);
            $remaining = round((float) $order->total_amount - $paid, 2);
            if ($remaining <= 0) {
                continue;
            }
            $remainingByOrder[(int) $order->id] = $remaining;
            $totalRemaining += $remaining;
        }

        if ($totalRemaining <= 0) {
            return [];
        }

        $totalRemaining = round($totalRemaining, 2);
        $out = [];
        $allocated = 0.0;
        foreach ($remainingByOrder as $orderId => $remaining) {
            $share = round(($remaining / $totalRemaining) * $amount, 2);
            $slice = min($share, $remaining);
            if ($slice > 0) {
                $out[$orderId] = $slice;
                $allocated = round($allocated + $slice, 2);
            }
        }

        $left = round($amount - $allocated, 2);
        if ($left > 0) {
            foreach ($remainingByOrder as $orderId => $remaining) {
                if ($left <= 0) {
                    break;
                }
                $current = (float) ($out[$orderId] ?? 0);
                $capacity = round($remaining - $current, 2);
                if ($capacity <= 0) {
                    continue;
                }
                $extra = min($capacity, $left);
                if ($extra > 0) {
                    $out[$orderId] = round($current + $extra, 2);
                    $left = round($left - $extra, 2);
                }
            }
        }

        return $out;
    }

    private function internationalPayableCurrency(): string
    {
        return 'USD';
    }

    /**
     * @param  array<int, float>  $allocations
     */
    private function applyGroupPaymentSlices(?InternationalPayableGroupPayment $groupPayment, Carbon $paymentDate, array $allocations, ?string $notes): void
    {
        foreach ($allocations as $orderId => $slice) {
            $slice = round((float) $slice, 2);
            if ($slice <= 0) {
                continue;
            }
            $order = InternationalPurchaseOrder::query()->findOrFail($orderId);
            $paymentAttrs = [
                'international_purchase_order_id' => $order->id,
                'payment_date' => $paymentDate,
                'amount' => $slice,
                'notes' => $notes,
            ];
            if ($groupPayment && Schema::hasColumn('international_payable_payments', 'international_payable_group_payment_id')) {
                $paymentAttrs['international_payable_group_payment_id'] = $groupPayment->id;
            }
            $payment = InternationalPayablePayment::create($paymentAttrs);
            SupplierLedgerSync::recordPayment($payment, $order);

            if ($groupPayment && Schema::hasTable('international_payable_group_payment_lines')) {
                InternationalPayableGroupPaymentLine::create([
                    'international_payable_group_payment_id' => $groupPayment->id,
                    'international_purchase_order_id' => $order->id,
                    'amount' => $slice,
                    'international_payable_payment_id' => $payment->id,
                ]);
            }
        }
    }

    private function reverseGroupPayment(InternationalPayableGroupPayment $payment): void
    {
        $payment->loadMissing('lines');
        foreach ($payment->lines as $line) {
            $this->reverseGroupPaymentLine($line);
            $line->delete();
        }
    }

    private function reverseGroupPaymentLine(InternationalPayableGroupPaymentLine $line): void
    {
        $payment = $line->international_payable_payment_id
            ? InternationalPayablePayment::query()->find($line->international_payable_payment_id)
            : null;
        if (! $payment) {
            return;
        }
        DB::table('supplier_ledger_entries')
            ->where('source_type', 'international_payable_payment')
            ->where('source_id', $payment->id)
            ->delete();
        $payment->delete();
    }

    private function assertGroupPaymentMatchesRouteKey(string $groupKey, InternationalPayableGroupPayment $payment): void
    {
        $decoded = $this->decodeGroupKeyFromRoute($groupKey);
        if ($decoded === null || $payment->group_key !== $decoded) {
            abort(404);
        }
    }
}
