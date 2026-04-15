<?php

namespace App\Http\Controllers;

use App\Models\InternationalPurchase;
use App\Models\InternationalPurchaseOrder;
use App\Models\Supplier;
use App\Services\SupplierLedgerSync;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InternationalPurchaseController extends Controller
{
    public function index(): View
    {
        $orders = InternationalPurchaseOrder::query()
            ->with(['supplier', 'lines'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();
        $orderGroups = $orders->groupBy(fn (InternationalPurchaseOrder $order) => $this->orderGroupKey($order))
            ->map(function (Collection $slice, string $key): array {
                /** @var InternationalPurchaseOrder $first */
                $first = $slice->first();
                $latest = $slice->sortByDesc(fn (InternationalPurchaseOrder $o) => $o->date?->timestamp ?? 0)->first();
                $latestDate = $latest?->date;

                return [
                    'group_key' => $key,
                    'group_key_encoded' => $this->encodeGroupKeyForRoute($key),
                    'display_name' => $first->supplier?->name ?: 'Unknown vendor',
                    'invoice_count' => $slice->count(),
                    'latest_invoice_date' => $latestDate,
                    'total_amount' => (float) $slice->sum(fn (InternationalPurchaseOrder $o) => (float) $o->total_amount),
                ];
            })
            ->sortByDesc(fn (array $g) => $g['latest_invoice_date']?->timestamp ?? 0)
            ->values();

        $totalAmount = (float) InternationalPurchaseOrder::query()->sum('total_amount');
        $currencySymbol = $this->internationalPurchaseCurrency();

        return view('international-purchases.index', [
            'orderGroups' => $orderGroups,
            'totalAmount' => $totalAmount,
            'currencySymbol' => $currencySymbol,
            'totalInvoiceCount' => $orders->count(),
        ]);
    }

    public function showGroup(string $groupKey): View
    {
        $decoded = $this->decodeGroupKeyFromRoute($groupKey);
        if ($decoded === null || ! $this->isValidGroupKey($decoded)) {
            throw new NotFoundHttpException;
        }

        $query = InternationalPurchaseOrder::query()
            ->with(['supplier', 'lines'])
            ->orderByDesc('date')
            ->orderByDesc('id');

        if (str_starts_with($decoded, 'id:')) {
            $supplierId = (int) substr($decoded, strlen('id:'));
            $query->where('supplier_id', $supplierId);
        } elseif (str_starts_with($decoded, 'name:')) {
            $name = substr($decoded, strlen('name:'));
            $query->whereHas('supplier', fn ($q) => $q->whereRaw('LOWER(TRIM(name)) = ?', [$name]));
        } elseif (str_starts_with($decoded, 'order:')) {
            $orderId = (int) substr($decoded, strlen('order:'));
            $query->where('id', $orderId);
        }

        $orders = $query->get();
        if ($orders->isEmpty()) {
            throw new NotFoundHttpException;
        }

        $displayName = $orders->first()->supplier?->name ?: 'Unknown vendor';
        $totalAmount = (float) $orders->sum(fn (InternationalPurchaseOrder $o) => (float) $o->total_amount);
        $currencySymbol = $this->internationalPurchaseCurrency();

        return view('international-purchases.group', [
            'orders' => $orders,
            'displayName' => $displayName,
            'currencySymbol' => $currencySymbol,
            'totalAmount' => $totalAmount,
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
            'currencySymbol' => $this->internationalPurchaseCurrency(),
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

        $currencySymbol = $this->internationalPurchaseCurrency();

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
            'currencySymbol' => $this->internationalPurchaseCurrency(),
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

    private function orderGroupKey(InternationalPurchaseOrder $order): string
    {
        $supplierName = trim((string) ($order->supplier?->name ?? ''));
        if ($supplierName !== '') {
            return 'name:'.mb_strtolower($supplierName);
        }

        if ($order->supplier_id) {
            return 'id:'.$order->supplier_id;
        }

        return 'order:'.$order->id;
    }

    private function isValidGroupKey(string $key): bool
    {
        if (str_starts_with($key, 'id:')) {
            return (bool) preg_match('/^id:\d+$/', $key);
        }
        if (str_starts_with($key, 'order:')) {
            return (bool) preg_match('/^order:\d+$/', $key);
        }
        if (str_starts_with($key, 'name:')) {
            return strlen($key) > strlen('name:');
        }

        return false;
    }

    private function encodeGroupKeyForRoute(string $key): string
    {
        return rtrim(strtr(base64_encode($key), '+/', '-_'), '=');
    }

    private function decodeGroupKeyFromRoute(string $value): ?string
    {
        $normalized = strtr($value, '-_', '+/');
        $pad = strlen($normalized) % 4;
        if ($pad !== 0) {
            $normalized .= str_repeat('=', 4 - $pad);
        }
        $decoded = base64_decode($normalized, true);

        return $decoded === false ? null : $decoded;
    }

    private function internationalPurchaseCurrency(): string
    {
        return 'USD';
    }
}
