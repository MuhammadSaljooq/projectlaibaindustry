<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\InternationalPurchase;
use App\Models\InternationalPurchaseOrder;
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
        $orders = InternationalPurchaseOrder::query()
            ->with(['supplier', 'lines'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $totalAmount = (float) InternationalPurchaseOrder::query()->sum('total_amount');
        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return view('international-purchases.index', [
            'orders' => $orders,
            'totalAmount' => $totalAmount,
            'currencySymbol' => $currencySymbol,
        ]);
    }

    public function export(): StreamedResponse
    {
        $orders = InternationalPurchaseOrder::query()->with(['supplier', 'lines'])->orderByDesc('date')->get();

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Invoice #', 'Vendor', 'Product', 'Qty', 'Unit price', 'Line total']);
            foreach ($orders as $order) {
                foreach ($order->lines as $line) {
                    fputcsv($handle, [
                        format_display_date($order->date),
                        $order->invoice_number ?? '',
                        $order->supplier?->name ?? '',
                        $line->product_name,
                        (string) $line->quantity,
                        number_format((float) $line->unit_price, 2, '.', ''),
                        number_format((float) $line->total_amount, 2, '.', ''),
                    ]);
                }
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
            'invoice_number' => ['nullable', 'string', 'max:191'],
            'date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $supplierId = $validated['supplier_id'] ?? null;
        $date = $validated['date'];
        $invoiceNumber = filled($validated['invoice_number'] ?? null) ? $validated['invoice_number'] : null;

        $lineTotals = [];
        foreach ($validated['items'] as $item) {
            $lineTotals[] = round((int) $item['quantity'] * (float) $item['unit_price'], 2);
        }
        $orderTotal = round(array_sum($lineTotals), 2);

        try {
            DB::beginTransaction();

            $order = InternationalPurchaseOrder::create([
                'supplier_id' => $supplierId,
                'date' => $date,
                'invoice_number' => $invoiceNumber,
                'total_amount' => $orderTotal,
            ]);

            foreach ($validated['items'] as $i => $item) {
                InternationalPurchase::create([
                    'international_purchase_order_id' => $order->id,
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_amount' => $lineTotals[$i],
                ]);
            }

            SupplierLedgerSync::syncInternationalPurchaseOrder($order->fresh(['lines']));

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to save: '.$e->getMessage());
        }

        $count = count($validated['items']);
        $message = $count === 1
            ? 'International purchase added successfully.'
            : 'International purchase with '.$count.' lines added successfully.';

        return redirect()->route('international-purchases.index')
            ->with('success', $message);
    }

    public function show(InternationalPurchaseOrder $international_purchase): View
    {
        $international_purchase->load(['supplier', 'lines']);

        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return view('international-purchases.show', [
            'order' => $international_purchase,
            'currencySymbol' => $currencySymbol,
        ]);
    }

    public function edit(InternationalPurchaseOrder $international_purchase): View
    {
        $international_purchase->load('lines');

        return view('international-purchases.edit', [
            'order' => $international_purchase,
            'suppliers' => Supplier::query()->orderBy('name')->get(),
            'currencySymbol' => Currency::query()->where('is_default', true)->value('symbol') ?? '$',
        ]);
    }

    public function update(Request $request, InternationalPurchaseOrder $international_purchase): RedirectResponse
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
            'invoice_number' => ['nullable', 'string', 'max:191'],
            'date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        if (empty($validated['items'])) {
            return redirect()->back()->withInput()->with('error', 'Please add at least one line item.');
        }

        $supplierId = $validated['supplier_id'] ?? null;
        $lineTotals = [];
        foreach ($validated['items'] as $item) {
            $lineTotals[] = round((int) $item['quantity'] * (float) $item['unit_price'], 2);
        }
        $orderTotal = round(array_sum($lineTotals), 2);

        try {
            DB::beginTransaction();

            $international_purchase->update([
                'supplier_id' => $supplierId,
                'date' => $validated['date'],
                'invoice_number' => filled($validated['invoice_number'] ?? null) ? $validated['invoice_number'] : null,
                'total_amount' => $orderTotal,
            ]);

            $international_purchase->lines()->delete();

            foreach ($validated['items'] as $i => $item) {
                InternationalPurchase::create([
                    'international_purchase_order_id' => $international_purchase->id,
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_amount' => $lineTotals[$i],
                ]);
            }

            SupplierLedgerSync::syncInternationalPurchaseOrder($international_purchase->fresh(['lines']));

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to update: '.$e->getMessage());
        }

        return redirect()->route('international-purchases.show', $international_purchase)
            ->with('success', 'International purchase updated successfully.');
    }

    public function destroy(InternationalPurchaseOrder $international_purchase): RedirectResponse
    {
        $international_purchase->delete();

        return redirect()->route('international-purchases.index')
            ->with('success', 'International purchase deleted successfully.');
    }
}
