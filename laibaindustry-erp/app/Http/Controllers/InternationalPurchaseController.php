<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\InternationalPurchase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InternationalPurchaseController extends Controller
{
    public function index(): View
    {
        $query = InternationalPurchase::query();

        if ($search = request('search')) {
            $query->where('product_name', 'like', "%{$search}%");
        }
        if ($from = request('from')) {
            $query->whereDate('date', '>=', $from);
        }
        if ($to = request('to')) {
            $query->whereDate('date', '<=', $to);
        }

        $filteredTotal = (clone $query)->sum('total_amount');

        $purchases = $query
            ->orderByDesc('date')
            ->paginate(25)
            ->appends(request()->query());

        $totalAmount = InternationalPurchase::query()->sum('total_amount');
        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return view('international-purchases.index', [
            'purchases' => $purchases,
            'totalAmount' => $totalAmount,
            'filteredTotal' => $filteredTotal,
            'currencySymbol' => $currencySymbol,
        ]);
    }

    public function export(): StreamedResponse
    {
        $rows = InternationalPurchase::orderByDesc('date')->get();

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Product', 'Qty', 'Unit price', 'Total']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->date->format('Y-m-d'),
                    $row->product_name,
                    (string) $row->quantity,
                    number_format((float) $row->unit_price, 2, '.', ''),
                    number_format((float) $row->total_amount, 2, '.', ''),
                ]);
            }
            fclose($handle);
        }, 'international-purchases-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function create(): View
    {
        return view('international-purchases.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'product_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $totalAmount = round((int) $validated['quantity'] * (float) $validated['unit_price'], 2);

        InternationalPurchase::create([
            'date' => $validated['date'],
            'product_name' => $validated['product_name'],
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['unit_price'],
            'total_amount' => $totalAmount,
        ]);

        return redirect()->route('international-purchases.index')
            ->with('success', 'International purchase added successfully.');
    }

    public function show(InternationalPurchase $international_purchase): RedirectResponse
    {
        return redirect()->route('international-purchases.edit', $international_purchase);
    }

    public function edit(InternationalPurchase $international_purchase): View
    {
        return view('international-purchases.edit', [
            'internationalPurchase' => $international_purchase,
        ]);
    }

    public function update(Request $request, InternationalPurchase $international_purchase): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'product_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $totalAmount = round((int) $validated['quantity'] * (float) $validated['unit_price'], 2);

        $international_purchase->update([
            'date' => $validated['date'],
            'product_name' => $validated['product_name'],
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['unit_price'],
            'total_amount' => $totalAmount,
        ]);

        return redirect()->route('international-purchases.index')
            ->with('success', 'International purchase updated successfully.');
    }

    public function destroy(InternationalPurchase $international_purchase): RedirectResponse
    {
        $international_purchase->delete();

        return redirect()->route('international-purchases.index')
            ->with('success', 'International purchase deleted successfully.');
    }
}
