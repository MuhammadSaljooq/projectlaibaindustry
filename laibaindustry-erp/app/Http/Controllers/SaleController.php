<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\TaxSetting;
use App\Models\VatEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SaleController extends Controller
{
    public function index(): View|RedirectResponse
    {
        [$redirect, $from, $to] = parse_list_date_filters();
        if ($redirect) {
            return $redirect;
        }

        $query = Sale::query()
            ->with(['items.product', 'currency']);

        if ($search = request('search')) {
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like) {
                $q->where('invoice_number', 'like', $like)
                    ->orWhere('customer_name', 'like', $like)
                    ->orWhere('customer_code', 'like', $like)
                    ->orWhereHas('items.product', fn ($pq) => $pq->where('name', 'like', $like));
            });
        }
        if ($from) {
            $query->whereDate('date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('date', '<=', $to);
        }

        $sales = $query
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(25)
            ->appends(request()->query());

        $totals = Sale::query()
            ->selectRaw('COALESCE(SUM(subtotal), 0) as total_subtotal, COALESCE(SUM(tax_amount), 0) as total_vat, COALESCE(SUM(total_amount), 0) as total_sales')
            ->first();

        return view('sales.index', [
            'sales' => $sales,
            'totals' => $totals,
        ]);
    }

    public function export(): StreamedResponse
    {
        $sales = Sale::with('items.product')->orderByDesc('date')->get();

        return response()->streamDownload(function () use ($sales) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Invoice #', 'Customer Code', 'Customer Name', 'Product', 'Qty', 'Price', 'Amount', 'VAT', 'Total']);
            foreach ($sales as $sale) {
                foreach ($sale->items as $item) {
                    $amount = $item->selling_price * $item->quantity;
                    fputcsv($handle, [
                        format_display_datetime($sale->date),
                        $sale->invoice_number,
                        $sale->customer_code ?? '',
                        $sale->customer_name ?? '',
                        $item->product?->name ?? 'Product #'.$item->product_id,
                        $item->quantity,
                        number_format($item->selling_price, 2, '.', ''),
                        number_format($amount, 2, '.', ''),
                        number_format($item->tax_applied ?? 0, 2, '.', ''),
                        number_format($amount + ($item->tax_applied ?? 0), 2, '.', ''),
                    ]);
                }
            }
            fclose($handle);
        }, 'sales-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function create(): View
    {
        $products = Product::query()->orderBy('name')->get();
        $customers = Customer::query()->orderBy('customer_name')->get();

        return view('sales.create', ['products' => $products, 'customers' => $customers]);
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        $items = array_values(array_filter($request->items ?? [], fn ($i) => ! empty($i['product_id'] ?? null)));
        if (empty($items)) {
            return redirect()->back()->withInput()->with('error', 'Please add at least one product to the sale.');
        }

        $defaultCurrencyId = \App\Models\Currency::query()->where('is_default', true)->value('id');
        $taxRate = (float) (TaxSetting::first()?->default_rate ?? 15.0);

        try {
            DB::beginTransaction();

            $subtotal = 0;
            foreach ($items as $item) {
                $qty = (int) ($item['quantity'] ?? 1);
                $price = (float) ($item['selling_price'] ?? 0);
                $subtotal += $price * $qty;
            }
            $taxAmount = $subtotal * ($taxRate / 100);
            $totalAmount = $subtotal + $taxAmount;

            $sale = Sale::create([
                'date' => $request->date,
                'customer_code' => $request->customer_code ?: null,
                'customer_name' => $request->customer_name ?: null,
                'invoice_number' => $request->invoice_number,
                'subtotal' => round($subtotal, 2),
                'tax_amount' => round($taxAmount, 2),
                'discount_amount' => 0,
                'total_amount' => round($totalAmount, 2),
                'tax_rate' => $taxRate,
                'currency_id' => $defaultCurrencyId,
                'exchange_rate' => null,
                'status' => 'completed',
            ]);

            $stockWarning = false;

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = (int) ($item['quantity'] ?? 1);
                $sellingPrice = (float) ($item['selling_price'] ?? 0);
                $costPrice = (float) ($product->cost_price ?? 0);
                $stockBefore = (int) $product->stock_quantity;
                $reorderLevel = (int) ($product->reorder_level ?? 10);

                $lineAmount = $sellingPrice * $qty;
                $lineTax = $lineAmount * ($taxRate / 100);
                $profit = ($sellingPrice - $costPrice) * $qty;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'cost_price' => $costPrice,
                    'selling_price' => $sellingPrice,
                    'profit' => round($profit, 2),
                    'tax_applied' => round($lineTax, 2),
                ]);

                $product->decrement('stock_quantity', $qty);
                $product->refresh();

                if ($stockBefore < $qty || $product->stock_quantity < 0 || $product->stock_quantity <= $reorderLevel) {
                    $stockWarning = true;
                }
            }

            Receivable::create([
                'date' => $request->date,
                'invoice_number' => $request->invoice_number,
                'customer_name' => $request->customer_name ?: null,
                'customer_code' => $request->customer_code ?: null,
                'amount' => round($totalAmount, 2),
                'received' => 0,
            ]);

            // Only upsert a customer record when a customer_code is explicitly provided
            $customerCode = trim($request->customer_code ?? '');
            $customerName = trim($request->customer_name ?? '');
            if ($customerCode !== '') {
                Customer::firstOrCreate(
                    ['customer_code' => $customerCode],
                    ['customer_name' => $customerName ?: $customerCode, 'phone' => null, 'email' => null, 'address' => null]
                );
            }

            // Ledger: Debit entry — customer owes us for this sale
            $customer = $customerCode !== '' ? Customer::where('customer_code', $customerCode)->first() : null;
            if ($customer) {
                CustomerLedgerEntry::create([
                    'customer_id' => $customer->id,
                    'date' => $request->date,
                    'description' => 'Sale Invoice',
                    'reference' => $request->invoice_number,
                    'debit' => round($totalAmount, 2),
                    'credit' => 0,
                    'source_type' => 'sale',
                    'source_id' => $sale->id,
                ]);
            }

            VatEntry::create([
                'type' => 'sale',
                'source_type' => Sale::class,
                'source_id' => $sale->id,
                'date' => $request->date,
                'invoice_number' => $request->invoice_number,
                'customer_name' => $request->customer_name ?: null,
                'customer_code' => $request->customer_code ?: null,
                'subtotal' => round($subtotal, 2),
                'vat_rate' => $taxRate,
                'vat_amount' => round($taxAmount, 2),
                'total_amount' => round($totalAmount, 2),
            ]);

            DB::commit();

            $redirect = redirect()->route('sales.index')->with('success', 'Sale created successfully.');
            if ($stockWarning) {
                $redirect->with('warning', 'Sale saved. Some products are low or negative on stock—review inventory.');
            }

            return $redirect;
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()->with('error', 'Failed to create sale: '.$e->getMessage());
        }
    }

    public function show(Sale $sale): View
    {
        $sale->load(['items.product', 'currency']);

        return view('sales.show', ['sale' => $sale]);
    }

    public function edit(Sale $sale): View
    {
        $sale->load('items.product');
        $products = Product::query()->orderBy('name')->get();
        $customers = Customer::query()->orderBy('customer_name')->get();

        return view('sales.edit', [
            'sale' => $sale,
            'products' => $products,
            'customers' => $customers,
        ]);
    }

    public function update(StoreSaleRequest $request, Sale $sale): RedirectResponse
    {
        $items = array_values(array_filter($request->items ?? [], fn ($i) => ! empty($i['product_id'] ?? null)));
        if (empty($items)) {
            return redirect()->back()->withInput()->with('error', 'Please add at least one product to the sale.');
        }

        $defaultCurrencyId = \App\Models\Currency::query()->where('is_default', true)->value('id');
        $taxRate = (float) ($sale->tax_rate ?? 15.0);

        try {
            DB::beginTransaction();

            $sale->load('items.product');
            $oldInvoiceRef = $sale->invoice_number;
            $oldCustomerCode = $sale->customer_code;

            foreach ($sale->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock_quantity', $item->quantity);
                }
            }

            $subtotal = 0;
            foreach ($items as $item) {
                $qty = (int) ($item['quantity'] ?? 1);
                $price = (float) ($item['selling_price'] ?? 0);
                $subtotal += $price * $qty;
            }
            $taxAmount = $subtotal * ($taxRate / 100);
            $totalAmount = $subtotal + $taxAmount;

            $sale->update([
                'date' => $request->date,
                'customer_code' => $request->customer_code ?: null,
                'customer_name' => $request->customer_name ?: null,
                'invoice_number' => $request->invoice_number,
                'subtotal' => round($subtotal, 2),
                'tax_amount' => round($taxAmount, 2),
                'total_amount' => round($totalAmount, 2),
            ]);

            $sale->items()->delete();

            $stockWarning = false;

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = (int) ($item['quantity'] ?? 1);
                $sellingPrice = (float) ($item['selling_price'] ?? 0);
                $costPrice = (float) ($product->cost_price ?? 0);
                $stockBefore = (int) $product->stock_quantity;
                $reorderLevel = (int) ($product->reorder_level ?? 10);

                $lineAmount = $sellingPrice * $qty;
                $lineTax = $lineAmount * ($taxRate / 100);
                $profit = ($sellingPrice - $costPrice) * $qty;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'cost_price' => $costPrice,
                    'selling_price' => $sellingPrice,
                    'profit' => round($profit, 2),
                    'tax_applied' => round($lineTax, 2),
                ]);

                $product->decrement('stock_quantity', $qty);
                $product->refresh();

                if ($stockBefore < $qty || $product->stock_quantity < 0 || $product->stock_quantity <= $reorderLevel) {
                    $stockWarning = true;
                }
            }

            if ($oldInvoiceRef) {
                $receivable = $this->receivablesLinkedToSale($oldInvoiceRef, $oldCustomerCode)->first();
                if ($receivable) {
                    $received = (float) $receivable->received;
                    $receivable->update([
                        'date' => $request->date,
                        'invoice_number' => $request->invoice_number,
                        'customer_name' => $request->customer_name ?: null,
                        'customer_code' => $request->customer_code ?: null,
                        'amount' => max(round($totalAmount, 2), $received),
                    ]);
                }
            }

            // Ledger: update the existing Debit entry for this sale
            CustomerLedgerEntry::where('source_type', 'sale')
                ->where('source_id', $sale->id)
                ->update([
                    'date' => $request->date,
                    'reference' => $request->invoice_number,
                    'debit' => round($totalAmount, 2),
                ]);

            VatEntry::where('source_type', Sale::class)
                ->where('source_id', $sale->id)
                ->update([
                    'date' => $request->date,
                    'invoice_number' => $request->invoice_number,
                    'customer_name' => $request->customer_name ?: null,
                    'customer_code' => $request->customer_code ?: null,
                    'subtotal' => round($subtotal, 2),
                    'vat_rate' => $taxRate,
                    'vat_amount' => round($taxAmount, 2),
                    'total_amount' => round($totalAmount, 2),
                ]);

            DB::commit();

            $redirect = redirect()->route('sales.show', $sale)->with('success', 'Sale updated successfully.');
            if ($stockWarning) {
                $redirect->with('warning', 'Sale saved. Some products are low or negative on stock—review inventory.');
            }

            return $redirect;
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()->with('error', 'Failed to update sale: '.$e->getMessage());
        }
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $sale->load('items.product');

            foreach ($sale->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock_quantity', $item->quantity);
                }
            }

            if ($sale->invoice_number) {
                $this->receivablesLinkedToSale($sale->invoice_number, $sale->customer_code)->delete();
            }

            // Ledger: remove the Debit entry for this sale
            CustomerLedgerEntry::where('source_type', 'sale')
                ->where('source_id', $sale->id)
                ->delete();

            VatEntry::where('source_type', Sale::class)
                ->where('source_id', $sale->id)
                ->delete();

            $sale->items()->delete();
            $sale->delete();

            DB::commit();

            return redirect()->route('sales.index')->with('success', 'Sale deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->route('sales.index')->with('error', 'Failed to delete sale: '.$e->getMessage());
        }
    }

    /**
     * Receivable rows created from a sale are keyed by invoice (and customer code when set).
     * Never require amount to match: after a prior edit, amount may have been raised to max(sale total, received),
     * so an equality check leaves a stale row and breaks receivable totals / remaining.
     *
     * @return Builder<Receivable>
     */
    private function receivablesLinkedToSale(?string $invoiceNumber, ?string $customerCode): Builder
    {
        $query = Receivable::query();
        if (! $invoiceNumber) {
            return $query->whereRaw('0 = 1');
        }

        $query->where('invoice_number', $invoiceNumber);
        $trimCode = trim((string) ($customerCode ?? ''));
        if ($trimCode !== '') {
            $query->where('customer_code', $trimCode);
        } else {
            $query->where(function (Builder $q) {
                $q->whereNull('customer_code')->orWhere('customer_code', '');
            });
        }

        return $query->orderBy('id');
    }
}
