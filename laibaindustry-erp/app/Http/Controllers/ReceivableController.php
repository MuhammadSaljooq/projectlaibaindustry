<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\Receivable;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReceivableController extends Controller
{
    public function index(): View
    {
        $receivables = Receivable::query()
            ->orderByDesc('date')
            ->paginate(15);

        $totals = Receivable::query()
            ->selectRaw('
                COALESCE(SUM(amount), 0)                   AS total_amount,
                COALESCE(SUM(received), 0)                 AS total_received,
                COALESCE(SUM(amount) - SUM(received), 0)   AS total_remaining
            ')
            ->first();

        return view('receivables.index', compact('receivables', 'totals'));
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

            $receivable->increment('received', $payment);
            $receivable->update(['payment_received_at' => $paymentDate]);

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

    public function destroy(Receivable $receivable): RedirectResponse
    {
        return redirect()->route('receivables.index')
            ->with('error', 'Receivable deletion is not available.');
    }
}
