<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\InternationalPayablePayment;
use App\Models\InternationalPurchaseOrder;
use App\Services\SupplierLedgerSync;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

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

        $billTotal = (float) InternationalPurchaseOrder::query()->sum('total_amount');
        $paidTotal = (float) InternationalPayablePayment::query()->sum('amount');
        $outstanding = max(0, $billTotal - $paidTotal);

        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return view('international-payables.index', [
            'orders' => $orders,
            'billTotal' => $billTotal,
            'paidTotal' => $paidTotal,
            'outstanding' => $outstanding,
            'currencySymbol' => $currencySymbol,
        ]);
    }

    public function pay(InternationalPurchaseOrder $international_purchase): View
    {
        $international_purchase->load(['supplier', 'lines', 'payablePayments' => fn ($q) => $q->orderByDesc('payment_date')->orderByDesc('id')]);

        $paid = (float) $international_purchase->payablePayments()->sum('amount');
        $bill = (float) $international_purchase->total_amount;
        $balance = max(0, $bill - $paid);

        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

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
}
