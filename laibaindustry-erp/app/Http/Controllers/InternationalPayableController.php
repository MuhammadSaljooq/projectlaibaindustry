<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\InternationalPayablePayment;
use App\Models\InternationalPurchase;
use App\Services\SupplierLedgerSync;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InternationalPayableController extends Controller
{
    public function index(): View
    {
        $purchases = InternationalPurchase::query()
            ->with('supplier')
            ->withSum('payablePayments', 'amount')
            ->orderByDesc('date')
            ->paginate(25);

        $billTotal = (float) InternationalPurchase::query()->sum('total_amount');
        $paidTotal = (float) InternationalPayablePayment::query()->sum('amount');
        $outstanding = max(0, $billTotal - $paidTotal);

        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return view('international-payables.index', [
            'purchases' => $purchases,
            'billTotal' => $billTotal,
            'paidTotal' => $paidTotal,
            'outstanding' => $outstanding,
            'currencySymbol' => $currencySymbol,
        ]);
    }

    public function pay(InternationalPurchase $international_purchase): View
    {
        $international_purchase->load('supplier');
        $paid = (float) $international_purchase->payablePayments()->sum('amount');
        $bill = (float) $international_purchase->total_amount;
        $balance = max(0, $bill - $paid);

        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return view('international-payables.pay', [
            'purchase' => $international_purchase,
            'paid' => $paid,
            'balance' => $balance,
            'currencySymbol' => $currencySymbol,
        ]);
    }

    public function storePayment(Request $request, InternationalPurchase $international_purchase): RedirectResponse
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
                'max:' . $balance,
            ],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'amount.max' => 'Payment cannot exceed the remaining balance (' . number_format($balance, 2) . ').',
        ]);

        try {
            DB::beginTransaction();

            $payment = InternationalPayablePayment::create([
                'international_purchase_id' => $international_purchase->id,
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'notes' => $validated['notes'] ?? null,
            ]);

            SupplierLedgerSync::recordPayment($payment, $international_purchase);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to record payment: ' . $e->getMessage());
        }

        return redirect()->route('international-payables.index')
            ->with('success', 'Payment of ' . number_format((float) $validated['amount'], 2) . ' recorded successfully.');
    }
}
