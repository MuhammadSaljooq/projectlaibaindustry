<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Http\Requests\StoreSaleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $customerCode = $request->query('customer');

        $applyFilters = function ($query) use ($search, $dateFrom, $dateTo, $customerCode) {
            if ($search !== null && trim($search) !== '') {
                $term = str_replace(['%', '_'], ['\\%', '\\_'], trim($search));
                $pattern = '%' . $term . '%';
                $query->where(function ($q) use ($pattern) {
                    $q->where('sales.customer_name', 'like', $pattern)
                        ->orWhere('sales.customer_code', 'like', $pattern)
                        ->orWhere('sales.invoice_number', 'like', $pattern)
                        ->orWhere('products.name', 'like', $pattern);
                });
            }
            if ($dateFrom) {
                $query->where('sales.date', '>=', $dateFrom . ' 00:00:00');
            }
            if ($dateTo) {
                $query->where('sales.date', '<=', $dateTo . ' 23:59:59');
            }
            if ($customerCode !== null && $customerCode !== '') {
                $query->where('sales.customer_code', $customerCode);
            }
        };

        $baseQuery = SaleItem::query()
            ->join('sales', 'sales_items.sale_id', '=', 'sales.id')
            ->leftJoin('products', 'sales_items.product_id', '=', 'products.id');
        $applyFilters($baseQuery);
        $saleIds = (clone $baseQuery)->select('sales.id')->distinct()->pluck('id');

        $totals = Sale::query()
            ->when($saleIds->isNotEmpty(), fn ($q) => $q->whereIn('id', $saleIds))
            ->selectRaw('COALESCE(SUM(subtotal), 0) as total_subtotal, COALESCE(SUM(tax_amount), 0) as total_vat, COALESCE(SUM(total_amount), 0) as total_sales')
            ->first();

        $itemsQuery = SaleItem::query()
            ->with(['sale', 'product'])
            ->join('sales', 'sales_items.sale_id', '=', 'sales.id')
            ->leftJoin('products', 'sales_items.product_id', '=', 'products.id')
            ->select('sales_items.*');
        $applyFilters($itemsQuery);
        $items = $itemsQuery
            ->orderByDesc('sales.date')
            ->orderBy('sales.id')
            ->orderBy('sales_items.id')
            ->paginate(25)
            ->withQueryString();

        $customers = Customer::query()->orderBy('customer_name')->get(['customer_code', 'customer_name']);

        return view('sales.index', [
            'items' => $items,
            'totals' => $totals,
            'customers' => $customers,
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

            // Resolve customer_code so statement shows under the right customer
            $customerCode = trim($request->customer_code ?? '');
            $customerName = trim($request->customer_name ?? '') ?: null;
            if ($customerCode === '' && $customerName !== '') {
                $customer = Customer::where('customer_name', $customerName)->first();
                if ($customer) {
                    $customerCode = $customer->customer_code;
                } else {
                    $newCode = 'CUST-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $customerName), 0, 6)) . '-' . substr((string) time(), -4);
                    Customer::create([
                        'customer_code' => $newCode,
                        'customer_name' => $customerName,
                        'phone' => null,
                        'email' => null,
                        'address' => null,
                    ]);
                    $customerCode = $newCode;
                }
            }
            if ($customerCode !== '') {
                Customer::firstOrCreate(
                    ['customer_code' => $customerCode],
                    ['customer_name' => $customerName ?: $customerCode, 'phone' => null, 'email' => null, 'address' => null]
                );
            }

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
                'customer_code' => $customerCode !== '' ? $customerCode : null,
                'customer_name' => $customerName ?: ($request->customer_name ?: null),
                'invoice_number' => $request->invoice_number ?: null,
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

            $invoiceNumber = $request->invoice_number ?: "SALE-{$sale->id}";
            $receivable = Receivable::create([
                'date' => $request->date,
                'invoice_number' => $invoiceNumber,
                'customer_name' => $customerName ?: 'Walk-in',
                'customer_code' => $customerCode !== '' ? $customerCode : null,
                'amount' => round($totalAmount, 2),
                'received' => 0,
            ]);

            CustomerLedgerEntry::create([
                'customer_code' => $customerCode !== '' ? $customerCode : '',
                'customer_name' => $customerName ?: 'Walk-in',
                'entry_date' => $receivable->date,
                'type' => 'invoice',
                'reference' => $invoiceNumber,
                'debit' => $receivable->amount,
                'credit' => 0,
                'receivable_id' => $receivable->id,
            ]);

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

            // Resolve customer_code so statement stays correct
            $customerCode = trim($request->customer_code ?? '');
            $customerName = trim($request->customer_name ?? '') ?: null;
            if ($customerCode === '' && $customerName !== '') {
                $customer = Customer::where('customer_name', $customerName)->first();
                if ($customer) {
                    $customerCode = $customer->customer_code;
                } else {
                    $newCode = 'CUST-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $customerName), 0, 6)) . '-' . substr((string) time(), -4);
                    Customer::create([
                        'customer_code' => $newCode,
                        'customer_name' => $customerName,
                        'phone' => null,
                        'email' => null,
                        'address' => null,
                    ]);
                    $customerCode = $newCode;
                }
            }

            $sale->load('items.product');
            $oldTotal = (float) $sale->total_amount;
            $oldInvoiceRef = $sale->invoice_number ?: "SALE-{$sale->id}";

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
                'customer_code' => $customerCode !== '' ? $customerCode : null,
                'customer_name' => $customerName ?: $request->customer_name,
                'invoice_number' => $request->invoice_number ?: null,
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

            // Update the receivable for this sale (match by old invoice ref and old total)
            $receivable = Receivable::query()
                ->where('invoice_number', $oldInvoiceRef)
                ->where('amount', $oldTotal)
                ->first();
            $received = $receivable ? (float) ($receivable->received ?? 0) : 0.0;
            $newInvoiceRef = $request->invoice_number ?: "SALE-{$sale->id}";
            $newAmount = max(round($totalAmount, 2), $received);

            if ($receivable) {
                $receivable->update([
                    'date' => $request->date,
                    'invoice_number' => $newInvoiceRef,
                    'customer_name' => $customerName ?: 'Walk-in',
                    'customer_code' => $customerCode !== '' ? $customerCode : null,
                    'amount' => $newAmount,
                ]);

                CustomerLedgerEntry::query()
                    ->where('receivable_id', $receivable->id)
                    ->where('type', 'invoice')
                    ->update([
                        'entry_date' => $request->date,
                        'reference' => $newInvoiceRef,
                        'debit' => $newAmount,
                        'customer_code' => $customerCode !== '' ? $customerCode : '',
                        'customer_name' => $customerName ?: 'Walk-in',
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

            $invoiceRef = $sale->invoice_number ?: "SALE-{$sale->id}";
            $q = Receivable::query()
                ->where('invoice_number', $invoiceRef)
                ->where('amount', $sale->total_amount);
            if ($sale->customer_code !== null) {
                $q->where('customer_code', $sale->customer_code);
            } else {
                $q->where('customer_name', $sale->customer_name ?: 'Walk-in');
            }
            $q->delete();

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
