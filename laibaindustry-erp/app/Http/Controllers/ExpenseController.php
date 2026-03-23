<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseController extends Controller
{
    public function index(): View
    {
        $query = Expense::query();

        if ($search = request('search')) {
            $query->where('type', 'like', "%{$search}%");
        }
        if ($from = request('from')) {
            $query->whereDate('date', '>=', $from);
        }
        if ($to = request('to')) {
            $query->whereDate('date', '<=', $to);
        }

        $filteredTotal = (clone $query)->sum('amount');

        $expenses = $query
            ->orderByDesc('date')
            ->paginate(25)
            ->appends(request()->query());

        $totalAmount = Expense::query()->sum('amount');
        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return view('expenses.index', [
            'expenses' => $expenses,
            'totalAmount' => $totalAmount,
            'filteredTotal' => $filteredTotal,
            'currencySymbol' => $currencySymbol,
        ]);
    }

    public function export(): StreamedResponse
    {
        $expenses = Expense::orderByDesc('date')->get();

        return response()->streamDownload(function () use ($expenses) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Type', 'Amount']);
            foreach ($expenses as $expense) {
                fputcsv($handle, [
                    $expense->date->format('Y-m-d'),
                    $expense->type,
                    number_format($expense->amount, 2, '.', ''),
                ]);
            }
            fclose($handle);
        }, 'expenses-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function create(): View
    {
        return view('expenses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date'   => ['required', 'date'],
            'type'   => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        Expense::create($validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense added successfully.');
    }

    public function show(Expense $expense): RedirectResponse
    {
        return redirect()->route('expenses.edit', $expense);
    }

    public function edit(Expense $expense): View
    {
        return view('expenses.edit', ['expense' => $expense]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'date'   => ['required', 'date'],
            'type'   => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }
}
