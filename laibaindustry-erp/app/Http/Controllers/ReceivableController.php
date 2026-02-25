<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\Receivable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReceivableController extends Controller
{
    public function index(): View
    {
        $receivables = Receivable::query()
            ->orderByDesc('date')
            ->paginate(15);

        return view('receivables.index', ['receivables' => $receivables]);
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
        return view('receivables.edit', ['receivable' => $receivable]);
    }

    public function update(Request $request, Receivable $receivable): RedirectResponse
    {
        $validated = $request->validate([
            'received' => ['required', 'numeric', 'min:0'],
        ]);

        $payment      = (float) $validated['received'];
        $maxReceivable = (float) $receivable->amount - (float) $receivable->received;

        if ($payment > $maxReceivable) {
            return redirect()->back()->withInput()
                ->with('error', "Amount cannot exceed remaining balance (" . number_format($maxReceivable, 2) . ").");
        }

        $receivable->increment('received', $payment);

        // Ledger: Credit entry — customer paid us
        $customer = $receivable->customer_code
            ? Customer::where('customer_code', $receivable->customer_code)->first()
            : null;

        if ($customer) {
            CustomerLedgerEntry::create([
                'customer_id' => $customer->id,
                'date'        => now(),
                'description' => 'Payment Received',
                'reference'   => $receivable->invoice_number,
                'debit'       => 0,
                'credit'      => $payment,
                'source_type' => 'payment_received',
                'source_id'   => $receivable->id,
            ]);
        }

        return redirect()->route('receivables.index')
            ->with('success', 'Payment recorded successfully.');
    }

    public function destroy(Receivable $receivable): RedirectResponse
    {
        return redirect()->route('receivables.index')
            ->with('error', 'Receivable deletion is not available.');
    }
}
