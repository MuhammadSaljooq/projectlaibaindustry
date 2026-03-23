<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\Payable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PayableController extends Controller
{
    public function index(): View
    {
        $payables = Payable::query()
            ->orderByDesc('date')
            ->paginate(25);

        $totals = Payable::query()
            ->selectRaw('
                COALESCE(SUM(amount), 0)                   AS total_amount,
                COALESCE(SUM(received), 0)                 AS total_received,
                COALESCE(SUM(amount) - SUM(received), 0)   AS total_outstanding
            ')
            ->first();

        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return view('payables.index', compact('payables', 'totals', 'currencySymbol'));
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
        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return view('payables.edit', compact('payable', 'currencySymbol'));
    }

    public function update(Request $request, Payable $payable): RedirectResponse
    {
        $balance = (float) $payable->amount - (float) $payable->received;

        $validated = $request->validate([
            'payment' => [
                'required',
                'numeric',
                'min:0.01',
                "max:{$balance}",
            ],
        ], [
            'payment.max' => "Payment cannot exceed the remaining balance (" . number_format($balance, 2) . ").",
        ]);

        $payment = (float) $validated['payment'];

        try {
            DB::beginTransaction();

            $payable->increment('received', $payment);

            $customer = $payable->customer_code
                ? Customer::where('customer_code', $payable->customer_code)->first()
                : null;

            if ($customer) {
                CustomerLedgerEntry::create([
                    'customer_id' => $customer->id,
                    'date'        => now(),
                    'description' => 'Payment Made',
                    'reference'   => $payable->invoice_number,
                    'debit'       => $payment,
                    'credit'      => 0,
                    'source_type' => 'payment_made',
                    'source_id'   => $payable->id,
                ]);
            }

            DB::commit();

            return redirect()->route('payables.index')
                ->with('success', 'Payment of ' . number_format($payment, 2) . ' recorded successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
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
}
