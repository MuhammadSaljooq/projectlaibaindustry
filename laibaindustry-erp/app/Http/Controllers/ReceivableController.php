<?php

namespace App\Http\Controllers;

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
            'received' => ['required', 'numeric', 'min:0'],
        ]);

        $received = (float) $validated['received'];
        $maxReceived = $receivable->amount - $receivable->received;

        if ($received > $maxReceived) {
            return redirect()->back()->withInput()->with('error', "Amount cannot exceed remaining balance ({$maxReceived}).");
        }

        $receivable->increment('received', $received);

        return redirect()->route('receivables.index')->with('success', 'Payment recorded successfully.');
    }

    public function destroy(Receivable $receivable): RedirectResponse
    {
        return redirect()->route('receivables.index')->with('error', 'Receivable deletion is not available.');
    }
}
