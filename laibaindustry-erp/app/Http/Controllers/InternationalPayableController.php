<?php

namespace App\Http\Controllers;

use App\Models\InternationalPayablePayment;
use App\Models\InternationalPurchaseOrder;
use App\Services\SupplierLedgerSync;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
            ->with(['supplier', 'lines'])
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

        return view('international-payables.group', [
            'orders' => $orders,
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

        $allocations = $this->allocateFifo($fifoList, $amount);
        $sumAlloc = round(array_sum($allocations), 2);
        if (abs($sumAlloc - $amount) > 0.02 || $sumAlloc < 0.01) {
            return redirect()->back()->withInput()
                ->with('error', 'Unable to allocate payment across invoices.');
        }

        $paymentDate = Carbon::parse($validated['payment_date'], config('app.timezone'))->startOfDay();
        $notes = $validated['notes'] ?? null;

        try {
            DB::beginTransaction();

            foreach ($allocations as $orderId => $slice) {
                $slice = round((float) $slice, 2);
                if ($slice <= 0) {
                    continue;
                }
                /** @var InternationalPurchaseOrder $order */
                $order = $fifoList->firstWhere('id', $orderId);
                if (! $order) {
                    $order = InternationalPurchaseOrder::query()->findOrFail($orderId);
                }

                $payment = InternationalPayablePayment::create([
                    'international_purchase_order_id' => $order->id,
                    'payment_date' => $paymentDate,
                    'amount' => $slice,
                    'notes' => $notes,
                ]);
                SupplierLedgerSync::recordPayment($payment, $order);
            }

            DB::commit();

            return redirect()->route('international-payables.group', ['groupKey' => $this->encodeGroupKeyForRoute($decoded)])
                ->with('success', 'Combined payment recorded.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to record combined payment: '.$e->getMessage());
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

        try {
            DB::beginTransaction();

            DB::table('supplier_ledger_entries')
                ->where('source_type', 'international_payable_payment')
                ->where('source_id', $internationalPayablePayment->id)
                ->delete();

            $internationalPayablePayment->delete();

            DB::commit();

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
    private function allocateFifo(Collection $ordersOldestFirst, float $paymentAmount): array
    {
        $left = round($paymentAmount, 2);
        $out = [];
        foreach ($ordersOldestFirst as $order) {
            if ($left <= 0) {
                break;
            }
            $paid = round((float) ($order->payable_payments_sum_amount ?? 0), 2);
            $remaining = round((float) $order->total_amount - $paid, 2);
            if ($remaining <= 0) {
                continue;
            }
            $slice = min($left, $remaining);
            if ($slice > 0) {
                $out[(int) $order->id] = $slice;
                $left = round($left - $slice, 2);
            }
        }

        return $out;
    }

    private function internationalPayableCurrency(): string
    {
        return 'USD';
    }
}
