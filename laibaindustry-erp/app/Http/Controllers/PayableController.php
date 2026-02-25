<?php

namespace App\Http\Controllers;

use App\Models\Payable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
                COALESCE(SUM(amount), 0)                        AS total_amount,
                COALESCE(SUM(received), 0)                      AS total_received,
                COALESCE(SUM(amount) - SUM(received), 0)        AS total_outstanding
            ')
            ->first();

        return view('payables.index', compact('payables', 'totals'));
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
        return view('payables.edit', compact('payable'));
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

        $payable->increment('received', (float) $validated['payment']);

        return redirect()->route('payables.index')
            ->with('success', 'Payment of ' . number_format($validated['payment'], 2) . ' recorded successfully.');
    }

    public function destroy(Payable $payable): RedirectResponse
    {
        // Only admins and managers may delete payable records (e.g. orphaned entries)
        if (! in_array(auth()->user()?->role, ['admin', 'manager'], true)) {
            return redirect()->route('payables.index')
                ->with('error', 'You do not have permission to delete payables.');
        }

        $payable->delete();

        return redirect()->route('payables.index')
            ->with('success', 'Payable deleted successfully.');
    }
}
