<?php

namespace App\Http\Controllers;

use App\Models\Customer;
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
    public function index(): View
    {
        $sales = Sale::query()
            ->withCount('items')
            ->orderByDesc('date')
            ->paginate(15);

        return view('sales.index', ['sales' => $sales]);
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

            Receivable::create([
                'date' => $request->date,
                'invoice_number' => $request->invoice_number ?: "SALE-{$sale->id}",
                'customer_name' => $request->customer_name ?: 'Walk-in',
                'customer_code' => $request->customer_code ?: null,
                'amount' => round($totalAmount, 2),
                'received' => 0,
            ]);

            $customerCode = trim($request->customer_code ?? '');
            $customerName = trim($request->customer_name ?? '');
            if ($customerCode !== '' || $customerName !== '') {
                if ($customerCode !== '') {
                    Customer::firstOrCreate(
                        ['customer_code' => $customerCode],
                        ['customer_name' => $customerName ?: $customerCode, 'phone' => null, 'email' => null, 'address' => null]
                    );
                } else {
                    $existing = Customer::where('customer_name', $customerName)->first();
                    if (!$existing) {
                        $code = 'CUST-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $customerName), 0, 6)) . '-' . substr((string) time(), -4);
                        Customer::create(['customer_code' => $code, 'customer_name' => $customerName, 'phone' => null, 'email' => null, 'address' => null]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('sales.index')->with('success', 'Sale created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to create sale: ' . $e->getMessage());
        }
    }

    public function show(Sale $sale): RedirectResponse
    {
        return redirect()->route('sales.index');
    }

    public function edit(Sale $sale): RedirectResponse
    {
        return redirect()->route('sales.index');
    }

    public function update(Request $request, Sale $sale): RedirectResponse
    {
        return redirect()->route('sales.index')->with('error', 'Sale editing is not available.');
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        return redirect()->route('sales.index')->with('error', 'Sale deletion is not available.');
    }
}
