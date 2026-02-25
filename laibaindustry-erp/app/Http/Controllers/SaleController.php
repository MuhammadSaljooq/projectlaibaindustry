<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Http\Requests\StoreSaleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(): View
    {
        $items = SaleItem::query()
            ->with(['sale', 'product'])
            ->join('sales', 'sales_items.sale_id', '=', 'sales.id')
            ->select('sales_items.*')
            ->orderByDesc('sales.date')
            ->orderBy('sales.id')
            ->orderBy('sales_items.id')
            ->paginate(25);

        $totals = Sale::query()
            ->selectRaw('COALESCE(SUM(subtotal), 0) as total_subtotal, COALESCE(SUM(tax_amount), 0) as total_vat, COALESCE(SUM(total_amount), 0) as total_sales')
            ->first();

        return view('sales.index', [
            'items' => $items,
            'totals' => $totals,
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
        $taxRate = 15.0;

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

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = (int) ($item['quantity'] ?? 1);
                $sellingPrice = (float) ($item['selling_price'] ?? 0);
                $costPrice = (float) ($product->cost_price ?? 0);

                if ($product->stock_quantity < $qty) {
                    DB::rollBack();
                    return redirect()->back()->withInput()->with('error', "Insufficient stock for '{$product->name}'. Available: {$product->stock_quantity}, required: {$qty}.");
                }

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

            DB::commit();

            return redirect()->route('sales.index')->with('success', 'Sale created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to create sale: ' . $e->getMessage());
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
            $oldTotal = (float) $sale->total_amount;
            $oldInvoiceRef = $sale->invoice_number;

            // Restore stock for all current line items
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

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = (int) ($item['quantity'] ?? 1);
                $sellingPrice = (float) ($item['selling_price'] ?? 0);
                $costPrice = (float) ($product->cost_price ?? 0);

                if ($product->stock_quantity < $qty) {
                    DB::rollBack();
                    return redirect()->back()->withInput()->with('error', "Insufficient stock for '{$product->name}'. Available: {$product->stock_quantity}, required: {$qty}.");
                }

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
            }

            // Update the linked receivable to stay in sync with the sale
            if ($oldInvoiceRef) {
                $received = (float) (Receivable::query()
                    ->where('invoice_number', $oldInvoiceRef)
                    ->where('amount', $oldTotal)
                    ->value('received') ?? 0);

                Receivable::query()
                    ->where('invoice_number', $oldInvoiceRef)
                    ->where('amount', $oldTotal)
                    ->update([
                        'date'           => $request->date,
                        'invoice_number' => $request->invoice_number,
                        'customer_name'  => $request->customer_name ?: null,
                        'customer_code'  => $request->customer_code ?: null,
                        'amount'         => max(round($totalAmount, 2), $received),
                    ]);
            }

            DB::commit();

            return redirect()->route('sales.show', $sale)->with('success', 'Sale updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to update sale: ' . $e->getMessage());
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
                Receivable::query()
                    ->where('invoice_number', $sale->invoice_number)
                    ->where('amount', $sale->total_amount)
                    ->delete();
            }

            $sale->items()->delete();
            $sale->delete();

            DB::commit();

            return redirect()->route('sales.index')->with('success', 'Sale deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('sales.index')->with('error', 'Failed to delete sale: ' . $e->getMessage());
        }
    }
}
