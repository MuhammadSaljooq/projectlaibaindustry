<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\Receivable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReceivableController extends Controller
{
    public function index(): View
    {
        $receivables = Receivable::query()
            ->withCount('sales')
            ->orderByDesc('date')
            ->paginate(15);

        $totals = Receivable::query()
            ->selectRaw('
                COALESCE(SUM(amount), 0)                   AS total_amount,
                COALESCE(SUM(received), 0)                 AS total_received,
                COALESCE(SUM(amount) - SUM(received), 0)   AS total_remaining
            ')
            ->first();

        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return view('receivables.index', [
            'receivables'    => $receivables,
            'totals'         => $totals,
            'currencySymbol' => $currencySymbol,
        ]);
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
        $receivable->load(['sales' => fn ($q) => $q->orderByDesc('date')->orderByDesc('id')]);
        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return view('receivables.edit', [
            'receivable'     => $receivable,
            'currencySymbol' => $currencySymbol,
        ]);
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

        try {
            DB::beginTransaction();

            $receivable->increment('received', $payment);

            $customer = $receivable->customer_code
                ? Customer::where('customer_code', $receivable->customer_code)->first()
                : null;

            if ($customer) {
                $salesCount = $receivable->sales()->count();
                $reference  = $receivable->customer_code
                    ? ($salesCount > 1
                        ? 'AR / ' . $receivable->customer_code . ' (multiple invoices)'
                        : ($receivable->invoice_number
                            ? $receivable->invoice_number
                            : 'AR / ' . $receivable->customer_code))
                    : ($receivable->invoice_number ?: 'Walk-in / payment received');

                CustomerLedgerEntry::create([
                    'customer_id' => $customer->id,
                    'date'        => now(),
                    'description' => 'Payment Received',
                    'reference'   => $reference,
                    'debit'       => 0,
                    'credit'      => $payment,
                    'source_type' => 'payment_received',
                    'source_id'   => $receivable->id,
                ]);
            }

            DB::commit();

            return redirect()->route('receivables.index')
                ->with('success', 'Payment recorded successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }

    public function destroy(Receivable $receivable): RedirectResponse
    {
        return redirect()->route('receivables.index')
            ->with('error', 'Receivable deletion is not available.');
    }
}
