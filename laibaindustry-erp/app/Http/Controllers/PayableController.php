<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\Payable;
use Carbon\Carbon;
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
            ->get();

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
        $payable->load('paymentLedgerEntries');
        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return view('payables.edit', compact('payable', 'currencySymbol'));
    }

    public function update(Request $request, Payable $payable): RedirectResponse
    {
        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'received' => ['required', 'numeric', 'min:0.01'],
        ]);

        $paymentDate = Carbon::parse($validated['payment_date'], config('app.timezone'))->startOfDay();
        $payment = (float) $validated['received'];
        $balance = (float) $payable->amount - (float) $payable->received;
        if ($payment > $balance) {
            return redirect()->back()->withInput()
                ->with('error', 'Amount cannot exceed remaining balance ('.number_format($balance, 2).').');
        }

        try {
            DB::beginTransaction();

            $customer = $payable->customer_code
                ? Customer::where('customer_code', $payable->customer_code)->first()
                : null;

            if ($customer) {
                CustomerLedgerEntry::create([
                    'customer_id' => $customer->id,
                    'date' => $paymentDate,
                    'description' => 'Payment Made',
                    'reference' => $payable->invoice_number,
                    'debit' => $payment,
                    'credit' => 0,
                    'source_type' => 'payment_made',
                    'source_id' => $payable->id,
                ]);
                $this->syncReceivedFromLedger($payable);
            } else {
                $payable->increment('received', $payment);
                $payable->update(['received_date' => $paymentDate]);
            }

            DB::commit();

            return redirect()->route('payables.index')
                ->with('success', 'Payment of '.number_format($payment, 2).' recorded successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to record payment: '.$e->getMessage());
        }
    }

    public function updatePayment(Request $request, Payable $payable, CustomerLedgerEntry $customerLedgerEntry): RedirectResponse
    {
        $this->assertPaymentLedgerBelongsToPayable($payable, $customerLedgerEntry);

        $id = $customerLedgerEntry->id;
        $validated = $request->validate([
            "date_{$id}" => ['required', 'date'],
            "debit_{$id}" => ['required', 'numeric', 'min:0.01'],
        ]);

        $debit = round((float) $validated["debit_{$id}"], 2);
        $otherSum = (float) CustomerLedgerEntry::query()
            ->where('source_type', 'payment_made')
            ->where('source_id', $payable->id)
            ->where('id', '!=', $customerLedgerEntry->id)
            ->sum('debit');

        if ($otherSum + $debit > (float) $payable->amount + 0.00001) {
            return redirect()->back()->withInput()
                ->with('error', 'Total payments cannot exceed the bill amount ('.number_format((float) $payable->amount, 2).').');
        }

        try {
            DB::beginTransaction();

            $customerLedgerEntry->update([
                'date' => Carbon::parse($validated["date_{$id}"], config('app.timezone'))->startOfDay(),
                'debit' => $debit,
            ]);

            $this->syncReceivedFromLedger($payable);

            DB::commit();

            return redirect()->route('payables.edit', $payable)
                ->with('success', 'Payment updated.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to update payment: '.$e->getMessage());
        }
    }

    public function destroyPayment(Payable $payable, CustomerLedgerEntry $customerLedgerEntry): RedirectResponse
    {
        $this->assertPaymentLedgerBelongsToPayable($payable, $customerLedgerEntry);

        try {
            DB::beginTransaction();

            $customerLedgerEntry->delete();
            $this->syncReceivedFromLedger($payable);

            DB::commit();

            return redirect()->route('payables.edit', $payable)
                ->with('success', 'Payment removed.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to remove payment: '.$e->getMessage());
        }
    }

    public function adjustReceivedWithoutLedger(Request $request, Payable $payable): RedirectResponse
    {
        if ($payable->paymentLedgerEntries()->exists()) {
            return redirect()->route('payables.edit', $payable)
                ->with('error', 'This payable has ledger payments; edit those rows instead.');
        }

        $validated = $request->validate([
            'received' => ['required', 'numeric', 'min:0'],
            'received_date' => ['nullable', 'date'],
        ]);

        $received = round((float) $validated['received'], 2);
        if ($received > (float) $payable->amount + 0.00001) {
            return redirect()->back()->withInput()
                ->with('error', 'Paid cannot exceed the bill amount ('.number_format((float) $payable->amount, 2).').');
        }

        $receivedDate = null;
        if ($received > 0) {
            $receivedDate = ! empty($validated['received_date'])
                ? Carbon::parse($validated['received_date'], config('app.timezone'))->startOfDay()
                : $payable->received_date;
        }

        $payable->update([
            'received' => $received,
            'received_date' => $receivedDate,
        ]);

        return redirect()->route('payables.edit', $payable)
            ->with('success', 'Paid amount updated.');
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

    private function syncReceivedFromLedger(Payable $payable): void
    {
        $paid = (float) CustomerLedgerEntry::query()
            ->where('source_type', 'payment_made')
            ->where('source_id', $payable->id)
            ->sum('debit');
        $latestPaidAt = CustomerLedgerEntry::query()
            ->where('source_type', 'payment_made')
            ->where('source_id', $payable->id)
            ->max('date');

        $payable->update([
            'received' => $paid,
            'received_date' => $latestPaidAt
                ? Carbon::parse($latestPaidAt, config('app.timezone'))->startOfDay()
                : null,
        ]);
    }

    private function assertPaymentLedgerBelongsToPayable(Payable $payable, CustomerLedgerEntry $entry): void
    {
        if ($entry->source_type !== 'payment_made' || (int) $entry->source_id !== (int) $payable->id) {
            abort(404);
        }
    }
}
