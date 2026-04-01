<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\InternationalPurchase;
use App\Models\Supplier;
use App\Services\SupplierLedgerSync;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InternationalPurchaseController extends Controller
{
    public function index(): View
    {
        $purchases = InternationalPurchase::query()
            ->with('supplier')
            ->orderByDesc('date')
            ->get();

        $totalAmount = InternationalPurchase::query()->sum('total_amount');
        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return view('international-purchases.index', [
            'purchases' => $purchases,
            'totalAmount' => $totalAmount,
            'currencySymbol' => $currencySymbol,
        ]);
    }

    public function export(): StreamedResponse
    {
        $rows = InternationalPurchase::query()->with('supplier')->orderByDesc('date')->get();

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Vendor', 'Product', 'Qty', 'Unit price', 'Total']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    format_display_date($row->date),
                    $row->supplier?->name ?? '',
                    $row->product_name,
                    (string) $row->quantity,
                    number_format((float) $row->unit_price, 2, '.', ''),
                    number_format((float) $row->total_amount, 2, '.', ''),
                ]);
            }
            fclose($handle);
        }, 'international-purchases-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function create(): View
    {
        return view('international-purchases.create', [
            'suppliers' => Supplier::query()->orderBy('name')->get(),
            'currencySymbol' => Currency::query()->where('is_default', true)->value('symbol') ?? '$',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $rawItems = $request->input('items', []);
        if (! is_array($rawItems)) {
            $rawItems = [];
        }

        $items = array_values(array_filter($rawItems, function ($row) {
            if (! is_array($row)) {
                return false;
            }

            return filled(trim((string) ($row['product_name'] ?? '')));
        }));

        $request->merge(['items' => $items]);

        $validated = $request->validate([
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $supplierId = $validated['supplier_id'] ?? null;
        $date = $validated['date'];
        $count = 0;

        DB::transaction(function () use ($validated, $supplierId, $date, &$count): void {
            foreach ($validated['items'] as $item) {
                $totalAmount = round((int) $item['quantity'] * (float) $item['unit_price'], 2);
                $purchase = InternationalPurchase::create([
                    'supplier_id' => $supplierId,
                    'date' => $date,
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_amount' => $totalAmount,
                ]);
                SupplierLedgerSync::syncInternationalPurchase($purchase->fresh());
                $count++;
            }
        });

        $message = $count === 1
            ? 'International purchase added successfully.'
            : "{$count} international purchase lines added successfully.";

        return redirect()->route('international-purchases.index')
            ->with('success', $message);
    }

    public function show(InternationalPurchase $international_purchase): RedirectResponse
    {
        return redirect()->route('international-purchases.edit', $international_purchase);
    }

    public function edit(InternationalPurchase $international_purchase): View
    {
        return view('international-purchases.edit', [
            'internationalPurchase' => $international_purchase,
            'suppliers' => Supplier::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, InternationalPurchase $international_purchase): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'date' => ['required', 'date'],
            'product_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $totalAmount = round((int) $validated['quantity'] * (float) $validated['unit_price'], 2);

        $international_purchase->update([
            'supplier_id' => $validated['supplier_id'] ?? null,
            'date' => $validated['date'],
            'product_name' => $validated['product_name'],
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['unit_price'],
            'total_amount' => $totalAmount,
        ]);

        SupplierLedgerSync::syncInternationalPurchase($international_purchase->fresh());

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
