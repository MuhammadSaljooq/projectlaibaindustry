<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        [$redirect, $from, $to] = parse_list_date_filters();
        if ($redirect) {
            return $redirect;
        }

        $query = Expense::query();

        if ($from) {
            $query->whereDate('date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('date', '<=', $to);
        }

        $categoryFilter = $request->query('category');
        if ($categoryFilter && in_array($categoryFilter, Expense::categories(), true)) {
            $query->where('category', $categoryFilter);
        } else {
            $categoryFilter = null;
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where('description', 'like', '%'.$search.'%');
        }

        $categoryTotals = $this->categoryTotalsFromQuery(clone $query);
        $filteredGrandTotal = array_sum($categoryTotals);

        $allTimeCategoryTotals = $this->categoryTotalsFromQuery(Expense::query());
        $allTimeGrandTotal = array_sum($allTimeCategoryTotals);

        $expenses = $query
            ->orderByDesc('date')
            ->get();

        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return view('expenses.index', [
            'expenses' => $expenses,
            'categoryTotals' => $categoryTotals,
            'filteredGrandTotal' => $filteredGrandTotal,
            'allTimeCategoryTotals' => $allTimeCategoryTotals,
            'allTimeGrandTotal' => $allTimeGrandTotal,
            'categoryFilter' => $categoryFilter,
            'search' => $search,
            'currencySymbol' => $currencySymbol,
            'categoryLabels' => Expense::categoryLabels(),
        ]);
    }

    public function export(): StreamedResponse
    {
        $expenses = Expense::orderByDesc('date')->get();

        return response()->streamDownload(function () use ($expenses) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Category', 'Description', 'Amount']);
            foreach ($expenses as $expense) {
                fputcsv($handle, [
                    format_display_date($expense->date),
                    $expense->categoryLabel(),
                    $expense->description ?? '',
                    number_format($expense->amount, 2, '.', ''),
                ]);
            }
            fclose($handle);
        }, 'expenses-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function create(): View
    {
        return view('expenses.create', [
            'categoryLabels' => Expense::categoryLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'category' => ['required', 'string', Rule::in(Expense::categories())],
            'description' => ['nullable', 'string', 'max:255'],
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
        return view('expenses.edit', [
            'expense' => $expense,
            'categoryLabels' => Expense::categoryLabels(),
        ]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'category' => ['required', 'string', Rule::in(Expense::categories())],
            'description' => ['nullable', 'string', 'max:255'],
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

    /**
     * @return array<string, float>
     */
    private function categoryTotalsFromQuery($query): array
    {
        $totals = array_fill_keys(Expense::categories(), 0.0);

        $rows = (clone $query)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        foreach ($rows as $row) {
            if (isset($totals[$row->category])) {
                $totals[$row->category] = (float) $row->total;
            }
        }

        return $totals;
    }
}
