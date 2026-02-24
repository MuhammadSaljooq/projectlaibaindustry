<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\Payable;
use App\Models\Purchase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayableController extends Controller
{
    public function index(Request $request): View
    {
        $this->syncPayablesFromPurchases();

        $query = Payable::query();
        if ($search = $request->query('search')) {
            $query->search($search);
        }
        $payables = $query->orderByDesc('date')->paginate(15)->withQueryString();

        return view('payables.index', ['payables' => $payables]);
    }

    protected function syncPayablesFromPurchases(): void
    {
        $purchasesWithoutPayable = Purchase::query()->whereDoesntHave('payable')->get();
        foreach ($purchasesWithoutPayable as $purchase) {
            $customerCode = $purchase->customer_code ?? '';
            $customerName = $purchase->customer_name ?? null;
            if ($customerCode === '' && $customerName !== '') {
                $customer = Customer::where('customer_name', $customerName)->first();
                $customerCode = $customer ? $customer->customer_code : '';
            }

            $payable = Payable::create([
                'purchase_id' => $purchase->id,
                'date' => $purchase->date,
                'invoice_number' => $purchase->invoice_number,
                'customer_name' => $customerName,
                'customer_code' => $customerCode !== '' ? $customerCode : null,
                'amount' => $purchase->total_amount,
                'received' => 0,
            ]);

            CustomerLedgerEntry::create([
                'customer_code' => $customerCode !== '' ? $customerCode : '',
                'customer_name' => $payable->customer_name,
                'entry_date' => $payable->date,
                'type' => 'payable',
                'reference' => $customerCode !== '' ? $customerCode : ($payable->invoice_number ?: 'Bill'),
                'debit' => 0,
                'credit' => $payable->amount,
                'payable_id' => $payable->id,
            ]);
        }
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('payables.index')->with('error', 'Payables are created automatically from purchases.');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('payables.index')->with('error', 'Payables are created automatically from purchases.');
    }

    public function show(Payable $payable): View
    {
        $payable->load('purchase');
        return view('payables.show', ['payable' => $payable]);
    }

    public function edit(Payable $payable): RedirectResponse
    {
        return redirect()->route('payables.show', $payable);
    }

    public function update(Request $request, Payable $payable): RedirectResponse
    {
        $request->validate([
            'received' => ['required', 'numeric', 'min:0.01'],
        ]);

        $received = (float) $request->received;
        $remaining = (float) $payable->amount - (float) ($payable->received ?? 0);

        if ($received > $remaining) {
            return redirect()->back()->withInput()->with('error', 'Payment amount cannot exceed remaining balance (' . number_format($remaining, 2) . ').');
        }

        $payable->increment('received', $received);
        $payable->update(['received_date' => now()]);

        CustomerLedgerEntry::create([
            'customer_code' => $payable->customer_code ?? '',
            'customer_name' => $payable->customer_name,
            'entry_date' => now(),
            'type' => 'payable_payment',
            'reference' => ($payable->customer_code !== null && $payable->customer_code !== '') ? $payable->customer_code : ($payable->invoice_number ?: 'Payment'),
            'debit' => $received,
            'credit' => 0,
            'payment_type' => $request->input('payment_type'),
            'payable_id' => $payable->id,
        ]);

        return redirect()->route('payables.show', $payable)->with('success', 'Payment recorded successfully.');
    }

    public function destroy(Payable $payable): RedirectResponse
    {
        $payable->delete();
        return redirect()->route('payables.index')->with('success', 'Payable deleted successfully.');
    }
}
