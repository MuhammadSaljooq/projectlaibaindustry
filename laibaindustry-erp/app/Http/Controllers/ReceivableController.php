<?php

namespace App\Http\Controllers;

use App\Models\CustomerLedgerEntry;
use App\Models\Receivable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReceivableController extends Controller
{
    public function index(Request $request): View
    {
        $query = Receivable::query();
        if ($search = $request->query('search')) {
            $query->search($search);
        }
        $receivables = $query->orderByDesc('date')->paginate(15)->withQueryString();

        return view('receivables.index', ['receivables' => $receivables]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('receivables.index')->with('error', 'Receivables are created automatically from sales.');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('receivables.index')->with('error', 'Receivables are created automatically from sales.');
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
            'received' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['nullable', 'date'],
            'payment_type' => ['nullable', 'string', 'max:50'],
        ]);

        $received = (float) $validated['received'];
        $maxReceived = (float) $receivable->amount - (float) $receivable->received;

        if ($received > $maxReceived) {
            return redirect()->back()->withInput()->with('error', "Amount cannot exceed remaining balance (" . number_format($maxReceived, 2) . ").");
        }

        $paymentDate = isset($validated['payment_date'])
            ? \Carbon\Carbon::parse($validated['payment_date'])
            : now();

        $receivable->increment('received', $received);

        CustomerLedgerEntry::create([
            'customer_code' => $receivable->customer_code ?? '',
            'customer_name' => $receivable->customer_name,
            'entry_date' => $paymentDate,
            'type' => 'payment',
            'reference' => $receivable->invoice_number ?: 'Payment',
            'debit' => 0,
            'credit' => $received,
            'payment_type' => $validated['payment_type'] ?? null,
            'receivable_id' => $receivable->id,
        ]);

        return redirect()->route('receivables.index')->with('success', 'Payment recorded successfully.');
    }

    public function destroy(Receivable $receivable): RedirectResponse
    {
        return redirect()->route('receivables.index')->with('error', 'Receivable deletion is not available.');
    }
}
