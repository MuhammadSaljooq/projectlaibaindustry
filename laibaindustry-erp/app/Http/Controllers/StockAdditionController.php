<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Product;
use App\Models\StockAddition;
use App\Support\StatementCompany;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StockAdditionController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        [$redirect, $from, $to] = parse_list_date_filters();
        if ($redirect) {
            return $redirect;
        }

        $query = StockAddition::query()->with(['product', 'product.category']);

        $this->applyFilters($query, $from, $to, $request);

        $totals = (clone $query)->selectRaw('
            COUNT(*) as total_lines,
            COALESCE(SUM(quantity), 0) as total_qty,
            COALESCE(SUM(total_cost), 0) as total_cost_value
        ')->first();

        $items = $query->orderByDesc('date')->orderByDesc('id')->paginate(25)->withQueryString();

        return view('stock-additions.index', [
            'items'          => $items,
            'totals'         => $totals,
            'products'       => Product::orderBy('name')->get(),
            'currencySymbol' => Currency::where('is_default', true)->value('symbol') ?? '$',
        ]);
    }

    public function create(): View
    {
        return view('stock-additions.create', [
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'date'       => ['required', 'date'],
            'quantity'   => ['required', 'integer', 'min:1'],
            'unit_cost'  => ['nullable', 'numeric', 'min:0'],
            'reference'  => ['nullable', 'string', 'max:255'],
            'notes'      => ['nullable', 'string', 'max:1000'],
        ]);

        $unitCost  = isset($validated['unit_cost']) ? (float) $validated['unit_cost'] : null;
        $totalCost = $unitCost !== null ? round($unitCost * (int) $validated['quantity'], 2) : null;

        StockAddition::create(array_merge($validated, ['total_cost' => $totalCost]));

        Product::where('id', $validated['product_id'])
            ->increment('stock_quantity', (int) $validated['quantity']);

        return redirect()->route('stock-additions.index')
            ->with('success', 'Stock addition recorded and inventory updated.');
    }

    public function show(StockAddition $stockAddition): RedirectResponse
    {
        return redirect()->route('stock-additions.edit', $stockAddition);
    }

    public function edit(StockAddition $stockAddition): View
    {
        return view('stock-additions.edit', [
            'addition' => $stockAddition,
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, StockAddition $stockAddition): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'date'       => ['required', 'date'],
            'quantity'   => ['required', 'integer', 'min:1'],
            'unit_cost'  => ['nullable', 'numeric', 'min:0'],
            'reference'  => ['nullable', 'string', 'max:255'],
            'notes'      => ['nullable', 'string', 'max:1000'],
        ]);

        $oldProductId = $stockAddition->product_id;
        $oldQty       = $stockAddition->quantity;
        $newQty       = (int) $validated['quantity'];
        $newProductId = (int) $validated['product_id'];

        $unitCost  = isset($validated['unit_cost']) ? (float) $validated['unit_cost'] : null;
        $totalCost = $unitCost !== null ? round($unitCost * $newQty, 2) : null;

        $stockAddition->update(array_merge($validated, ['total_cost' => $totalCost]));

        // Reverse old stock adjustment then apply new one
        if ($oldProductId === $newProductId) {
            $delta = $newQty - $oldQty;
            if ($delta > 0) {
                Product::where('id', $newProductId)->increment('stock_quantity', $delta);
            } elseif ($delta < 0) {
                Product::where('id', $newProductId)->decrement('stock_quantity', abs($delta));
            }
        } else {
            // Product changed — reverse from old, apply to new
            Product::where('id', $oldProductId)->decrement('stock_quantity', $oldQty);
            Product::where('id', $newProductId)->increment('stock_quantity', $newQty);
        }

        return redirect()->route('stock-additions.index')
            ->with('success', 'Stock addition updated and inventory adjusted.');
    }

    public function destroy(StockAddition $stockAddition): RedirectResponse
    {
        $productId = $stockAddition->product_id;
        $qty       = $stockAddition->quantity;

        $stockAddition->delete();

        Product::where('id', $productId)->decrement('stock_quantity', $qty);

        return redirect()->route('stock-additions.index')
            ->with('success', 'Stock addition deleted and inventory reversed.');
    }

    public function export(Request $request): StreamedResponse
    {
        [$redirect, $from, $to] = parse_list_date_filters();

        $query = StockAddition::query()->with(['product']);
        $this->applyFilters($query, $from, $to, $request);
        $items = $query->orderByDesc('date')->orderByDesc('id')->get();

        $symbol = Currency::where('is_default', true)->value('symbol') ?? '$';

        return response()->streamDownload(function () use ($items, $symbol) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Product', 'Article No.', 'Qty Added', 'Unit Cost', 'Total Cost', 'Reference', 'Notes']);
            foreach ($items as $item) {
                fputcsv($handle, [
                    $item->date?->format('d/m/Y'),
                    $item->product?->name ?? 'Product #'.$item->product_id,
                    $item->product?->sku ?? '—',
                    $item->quantity,
                    $item->unit_cost !== null ? number_format($item->unit_cost, 2, '.', '') : '',
                    $item->total_cost !== null ? number_format($item->total_cost, 2, '.', '') : '',
                    $item->reference ?? '',
                    $item->notes ?? '',
                ]);
            }
            fclose($handle);
        }, 'stock-additions-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function pdf(Request $request): Response|RedirectResponse
    {
        [$redirect, $from, $to] = parse_list_date_filters();
        if ($redirect) {
            return $redirect;
        }

        $query = StockAddition::query()->with(['product', 'product.category']);
        $this->applyFilters($query, $from, $to, $request);

        $totals = (clone $query)->selectRaw('
            COUNT(*) as total_lines,
            COALESCE(SUM(quantity), 0) as total_qty,
            COALESCE(SUM(total_cost), 0) as total_cost_value
        ')->first();

        $items          = $query->orderByDesc('date')->orderByDesc('id')->get();
        $company        = StatementCompany::normalize(config('company'));
        $currencySymbol = Currency::where('is_default', true)->value('symbol') ?? '$';
        $search         = trim((string) $request->input('search', ''));
        $productId      = $request->integer('product_id') ?: null;
        $productName    = $productId ? (Product::find($productId)?->name) : null;

        $pdf = Pdf::loadView('stock-additions.pdf', compact(
            'items', 'totals', 'company', 'currencySymbol', 'from', 'to', 'search', 'productName'
        ))
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans');

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="stock-additions-'.now()->format('Y-m-d').'.pdf"',
        ]);
    }

    private function applyFilters($query, ?string $from, ?string $to, Request $request): void
    {
        if ($from) {
            $query->whereDate('date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('date', '<=', $to);
        }

        if ($pid = $request->integer('product_id')) {
            $query->where('product_id', $pid);
        }

        if ($search = trim((string) $request->input('search', ''))) {
            $query->search($search);
        }
    }
}
