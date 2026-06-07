<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Product;
use App\Models\SaleItem;
use App\Support\StatementCompany;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryHistoryController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        [$redirect, $from, $to] = parse_list_date_filters();
        if ($redirect) {
            return $redirect;
        }

        $query = $this->baseQuery();
        $this->applyFilters($query, $from, $to, $request);

        $totals = (clone $query)->reorder()->selectRaw('
            COUNT(*) as total_lines,
            COALESCE(SUM(sales_items.quantity), 0) as total_qty,
            COALESCE(SUM(sales_items.quantity * sales_items.selling_price), 0) as total_revenue
        ')->first();

        $items = $query->select('sales_items.*')->paginate(25)->withQueryString();

        return view('inventory.history', [
            'items'          => $items,
            'totals'         => $totals,
            'products'       => Product::orderBy('name')->get(),
            'currencySymbol' => Currency::where('is_default', true)->value('symbol') ?? '$',
        ]);
    }

    public function productHistory(Request $request, Product $product): View|RedirectResponse
    {
        [$redirect, $from, $to] = parse_list_date_filters();
        if ($redirect) {
            return $redirect;
        }

        $query = $this->baseQuery()->where('sales_items.product_id', $product->id);
        $this->applyFilters($query, $from, $to, $request);

        $totals = (clone $query)->reorder()->selectRaw('
            COUNT(*) as total_lines,
            COALESCE(SUM(sales_items.quantity), 0) as total_qty,
            COALESCE(SUM(sales_items.quantity * sales_items.selling_price), 0) as total_revenue
        ')->first();

        $items = $query->select('sales_items.*')->paginate(25)->withQueryString();

        return view('inventory.product-history', [
            'product'        => $product,
            'items'          => $items,
            'totals'         => $totals,
            'currencySymbol' => Currency::where('is_default', true)->value('symbol') ?? '$',
        ]);
    }

    public function pdf(Request $request): Response|RedirectResponse
    {
        [$redirect, $from, $to] = parse_list_date_filters();
        if ($redirect) {
            return $redirect;
        }

        $query = $this->baseQuery();
        $this->applyFilters($query, $from, $to, $request);

        $totals = (clone $query)->reorder()->selectRaw('
            COUNT(*) as total_lines,
            COALESCE(SUM(sales_items.quantity), 0) as total_qty,
            COALESCE(SUM(sales_items.quantity * sales_items.selling_price), 0) as total_revenue
        ')->first();

        $items = $query->select('sales_items.*')->get();
        $company = StatementCompany::normalize(config('company'));
        $currencySymbol = Currency::where('is_default', true)->value('symbol') ?? '$';
        $search = trim((string) $request->input('search', ''));
        $productId = $request->integer('product_id') ?: null;
        $productName = $productId ? (Product::find($productId)?->name) : null;

        // Compute running stock balance per item.
        // Items arrive newest-first. We walk forward: the stock shown for each
        // row is what remained AFTER that sale. We start from each product's
        // current stock_quantity and add back quantities as we go older.
        $runningStock  = [];   // product_id → running balance
        $itemStockMap  = [];   // item_id    → remaining stock shown on that row

        foreach ($items as $item) {
            $pid = $item->product_id;
            if (!array_key_exists($pid, $runningStock)) {
                $runningStock[$pid] = (int) ($item->product?->stock_quantity ?? 0);
            }
            $itemStockMap[$item->id] = $runningStock[$pid];
            $runningStock[$pid] += (int) $item->quantity;
        }

        $pdf = Pdf::loadView('inventory.history-pdf', compact(
            'items', 'totals', 'company', 'currencySymbol', 'from', 'to', 'search', 'productName', 'itemStockMap'
        ))
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="inventory-history-'.now()->format('Y-m-d').'.pdf"',
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$redirect, $from, $to] = parse_list_date_filters();

        $query = $this->baseQuery();
        $this->applyFilters($query, $from, $to, $request);
        $items = $query->select('sales_items.*')->get();

        return response()->streamDownload(function () use ($items) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Product', 'Article No.', 'Customer', 'Qty', 'Unit Price', 'Line Total', 'Invoice']);
            foreach ($items as $item) {
                fputcsv($handle, [
                    format_display_datetime($item->sale?->date),
                    $item->product?->name ?? 'Product #'.$item->product_id,
                    $item->product?->sku ?? '—',
                    $item->sale?->customer_name ?? $item->sale?->customer_code ?? 'Walk-in',
                    $item->quantity,
                    number_format($item->selling_price, 2, '.', ''),
                    number_format($item->quantity * $item->selling_price, 2, '.', ''),
                    $item->sale?->invoice_number ?? '—',
                ]);
            }
            fclose($handle);
        }, 'inventory-history-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function baseQuery(): Builder
    {
        return SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sales_items.sale_id')
            ->with(['sale', 'sale.currency', 'product', 'product.category'])
            ->orderByDesc('sales.date')
            ->orderByDesc('sales.id');
    }

    private function applyFilters(Builder $query, ?string $from, ?string $to, Request $request): void
    {
        if ($from) {
            $query->whereDate('sales.date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('sales.date', '<=', $to);
        }

        if ($pid = $request->integer('product_id')) {
            $query->where('sales_items.product_id', $pid);
        }

        if ($customer = trim((string) $request->input('customer', ''))) {
            $query->where(function (Builder $q) use ($customer) {
                $q->where('sales.customer_name', 'like', '%'.$customer.'%')
                  ->orWhere('sales.customer_code', 'like', '%'.$customer.'%');
            });
        }

        if ($search = trim((string) $request->input('search', ''))) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->join('products as p_search', 'p_search.id', '=', 'sales_items.product_id')
                  ->where(function (Builder $q) use ($escaped) {
                      $q->where('p_search.name', 'like', '%'.$escaped.'%')
                        ->orWhere('p_search.sku', 'like', '%'.$escaped.'%')
                        ->orWhere('sales.customer_name', 'like', '%'.$escaped.'%')
                        ->orWhere('sales.customer_code', 'like', '%'.$escaped.'%');
                  });
        }
    }
}
