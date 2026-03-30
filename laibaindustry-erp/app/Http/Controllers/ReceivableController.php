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
        $receivable->load('paymentLedgerEntries');

        return view('receivables.edit', compact('receivable'));
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
                $this->syncReceivedFromLedger($receivable);
            } else {
                $receivable->increment('received', $payment);
                $receivable->update(['payment_received_at' => $paymentDate]);
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

    public function updatePayment(Request $request, Receivable $receivable, CustomerLedgerEntry $customerLedgerEntry): RedirectResponse
    {
        $this->assertPaymentLedgerBelongsToReceivable($receivable, $customerLedgerEntry);

        $id = $customerLedgerEntry->id;
        $validated = $request->validate([
            "date_{$id}" => ['required', 'date'],
            "credit_{$id}" => ['required', 'numeric', 'min:0.01'],
        ]);

        $credit = round((float) $validated["credit_{$id}"], 2);
        $otherSum = (float) CustomerLedgerEntry::query()
            ->where('source_type', 'payment_received')
            ->where('source_id', $receivable->id)
            ->where('id', '!=', $customerLedgerEntry->id)
            ->sum('credit');

        if ($otherSum + $credit > (float) $receivable->amount + 0.00001) {
            return redirect()->back()->withInput()
                ->with('error', 'Total payments cannot exceed the bill amount ('.number_format((float) $receivable->amount, 2).').');
        }

        try {
            DB::beginTransaction();

            $customerLedgerEntry->update([
                'date' => Carbon::parse($validated["date_{$id}"], config('app.timezone'))->startOfDay(),
                'credit' => $credit,
            ]);

            $this->syncReceivedFromLedger($receivable);

            DB::commit();

            return redirect()->route('receivables.edit', $receivable)
                ->with('success', 'Payment updated.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to update payment: '.$e->getMessage());
        }
    }

    public function destroyPayment(Receivable $receivable, CustomerLedgerEntry $customerLedgerEntry): RedirectResponse
    {
        $this->assertPaymentLedgerBelongsToReceivable($receivable, $customerLedgerEntry);

        try {
            DB::beginTransaction();

            $customerLedgerEntry->delete();
            $this->syncReceivedFromLedger($receivable);

            DB::commit();

            return redirect()->route('receivables.edit', $receivable)
                ->with('success', 'Payment removed.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to remove payment: '.$e->getMessage());
        }
    }

    public function adjustReceivedWithoutLedger(Request $request, Receivable $receivable): RedirectResponse
    {
        if ($receivable->paymentLedgerEntries()->exists()) {
            return redirect()->route('receivables.edit', $receivable)
                ->with('error', 'This receivable has ledger payments; edit those rows instead.');
        }

        $validated = $request->validate([
            'received' => ['required', 'numeric', 'min:0'],
            'payment_received_at' => ['nullable', 'date'],
        ]);

        $received = round((float) $validated['received'], 2);
        if ($received > (float) $receivable->amount + 0.00001) {
            return redirect()->back()->withInput()
                ->with('error', 'Received cannot exceed the bill amount ('.number_format((float) $receivable->amount, 2).').');
        }

        $paymentReceivedAt = null;
        if ($received > 0) {
            $paymentReceivedAt = ! empty($validated['payment_received_at'])
                ? Carbon::parse($validated['payment_received_at'], config('app.timezone'))->startOfDay()
                : $receivable->payment_received_at;
        }

        $receivable->update([
            'received' => $received,
            'payment_received_at' => $paymentReceivedAt,
        ]);

        return redirect()->route('receivables.edit', $receivable)
            ->with('success', 'Received amount updated.');
    }

    public function destroy(Receivable $receivable): RedirectResponse
    {
        return redirect()->route('receivables.index')
            ->with('error', 'Receivable deletion is not available.');
    }

    private function syncReceivedFromLedger(Receivable $receivable): void
    {
        $receivable->refresh();
        $sum = (float) $receivable->paymentLedgerEntries()->sum('credit');
        $maxDate = $receivable->paymentLedgerEntries()->max('date');

        $receivable->update([
            'received' => round($sum, 2),
            'payment_received_at' => $sum > 0 && $maxDate
                ? Carbon::parse($maxDate, config('app.timezone'))
                : null,
        ]);
    }

    private function assertPaymentLedgerBelongsToReceivable(Receivable $receivable, CustomerLedgerEntry $entry): void
    {
        if ($entry->source_type !== 'payment_received' || (int) $entry->source_id !== (int) $receivable->id) {
            abort(404);
        }
    }
}
