<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\TaxSetting;
use App\Models\VatEntry;
use App\Http\Requests\StoreSaleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SaleController extends Controller
{
    public function index(): View
    {
        $query = SaleItem::query()
            ->with(['sale', 'product'])
            ->join('sales', 'sales_items.sale_id', '=', 'sales.id')
            ->join('products', 'sales_items.product_id', '=', 'products.id')
            ->select('sales_items.*');

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sales.invoice_number', 'like', "%{$search}%")
                  ->orWhere('sales.customer_name', 'like', "%{$search}%")
                  ->orWhere('sales.customer_code', 'like', "%{$search}%")
                  ->orWhere('products.name', 'like', "%{$search}%");
            });
        }
        if ($from = request('from')) {
            $query->whereDate('sales.date', '>=', $from);
        }
        if ($to = request('to')) {
            $query->whereDate('sales.date', '<=', $to);
        }

        $items = $query
            ->orderByDesc('sales.date')
            ->orderBy('sales.id')
            ->orderBy('sales_items.id')
            ->paginate(25)
            ->appends(request()->query());

        $totals = Sale::query()
            ->selectRaw('COALESCE(SUM(subtotal), 0) as total_subtotal, COALESCE(SUM(tax_amount), 0) as total_vat, COALESCE(SUM(total_amount), 0) as total_sales')
            ->first();

        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return view('sales.index', [
            'items'            => $items,
            'totals'           => $totals,
            'currencySymbol'   => $currencySymbol,
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
                        $sale->date->format('Y-m-d H:i'),
                        $sale->invoice_number,
                        $sale->customer_code ?? '',
                        $sale->customer_name ?? '',
                        $item->product?->name ?? 'Product #' . $item->product_id,
                        $item->quantity,
                        number_format($item->selling_price, 2, '.', ''),
                        number_format($amount, 2, '.', ''),
                        number_format($item->tax_applied ?? 0, 2, '.', ''),
                        number_format($amount + ($item->tax_applied ?? 0), 2, '.', ''),
                    ]);
                }
            }
            fclose($handle);
        }, 'sales-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function create(): View
    {
        $products  = Product::query()->orderBy('name')->get();
        $customers = Customer::query()->orderBy('customer_name')->get();
        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return view('sales.create', [
            'products'         => $products,
            'customers'        => $customers,
            'currencySymbol'   => $currencySymbol,
        ]);
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
                $qty   = (int) ($item['quantity']      ?? 1);
                $price = (float) ($item['selling_price'] ?? 0);
                $subtotal += $price * $qty;
            }
            $taxAmount   = $subtotal * ($taxRate / 100);
            $totalAmount = $subtotal + $taxAmount;

            $sale = Sale::create([
                'date'            => $request->date,
                'customer_code'   => $request->customer_code ?: null,
                'customer_name'   => $request->customer_name ?: null,
                'invoice_number'  => $request->invoice_number,
                'subtotal'        => round($subtotal, 2),
                'tax_amount'      => round($taxAmount, 2),
                'discount_amount' => 0,
                'total_amount'    => round($totalAmount, 2),
                'tax_rate'        => $taxRate,
                'currency_id'     => $defaultCurrencyId,
                'exchange_rate'   => null,
                'status'          => 'completed',
            ]);

            foreach ($items as $item) {
                $product      = Product::findOrFail($item['product_id']);
                $qty          = (int) ($item['quantity']      ?? 1);
                $sellingPrice = (float) ($item['selling_price'] ?? 0);
                $costPrice    = (float) ($product->cost_price    ?? 0);

                if ($product->stock_quantity < $qty) {
                    DB::rollBack();
                    return redirect()->back()->withInput()->with('error', "Insufficient stock for '{$product->name}'. Available: {$product->stock_quantity}, required: {$qty}.");
                }

                $lineAmount = $sellingPrice * $qty;
                $lineTax    = $lineAmount * ($taxRate / 100);
                $profit     = ($sellingPrice - $costPrice) * $qty;

                SaleItem::create([
                    'sale_id'       => $sale->id,
                    'product_id'    => $product->id,
                    'quantity'      => $qty,
                    'cost_price'    => $costPrice,
                    'selling_price' => $sellingPrice,
                    'profit'        => round($profit, 2),
                    'tax_applied'   => round($lineTax, 2),
                ]);

                $product->decrement('stock_quantity', $qty);
            }

            $receivable = $this->createReceivableForNewSale($sale);
            $sale->update(['receivable_id' => $receivable->id]);
            $receivable->recalculateAmountFromSales();
            $receivable->syncDisplayFromLinkedSales();
            $receivable->refresh();
            if (! $receivable->hasValidBalance()) {
                DB::rollBack();

                return redirect()->back()->withInput()->with('error', 'Receivable total is below payments already recorded for this customer balance.');
            }

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
                    'date'        => $request->date,
                    'description' => 'Sale Invoice',
                    'reference'   => $request->invoice_number,
                    'debit'       => round($totalAmount, 2),
                    'credit'      => 0,
                    'source_type' => 'sale',
                    'source_id'   => $sale->id,
                ]);
            }

            VatEntry::create([
                'type'           => 'sale',
                'source_type'    => Sale::class,
                'source_id'      => $sale->id,
                'date'           => $request->date,
                'invoice_number' => $request->invoice_number,
                'customer_name'  => $request->customer_name ?: null,
                'customer_code'  => $request->customer_code ?: null,
                'subtotal'       => round($subtotal, 2),
                'vat_rate'       => $taxRate,
                'vat_amount'     => round($taxAmount, 2),
                'total_amount'   => round($totalAmount, 2),
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
        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return view('sales.show', [
            'sale'           => $sale,
            'currencySymbol' => $currencySymbol,
        ]);
    }

    public function edit(Sale $sale): View
    {
        $sale->load('items.product');
        $products  = Product::query()->orderBy('name')->get();
        $customers = Customer::query()->orderBy('customer_name')->get();

        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return view('sales.edit', [
            'sale'           => $sale,
            'products'       => $products,
            'customers'      => $customers,
            'currencySymbol' => $currencySymbol,
        ]);
    }

    public function update(StoreSaleRequest $request, Sale $sale): RedirectResponse
    {
        $items = array_values(array_filter($request->items ?? [], fn ($i) => ! empty($i['product_id'] ?? null)));
        if (empty($items)) {
            return redirect()->back()->withInput()->with('error', 'Please add at least one product to the sale.');
        }

        $defaultCurrencyId = \App\Models\Currency::query()->where('is_default', true)->value('id');
        $taxRate           = (float) ($sale->tax_rate ?? 15.0);

        try {
            DB::beginTransaction();

            $sale->load('items.product');
            $oldReceivableId = $sale->receivable_id;

            foreach ($sale->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock_quantity', $item->quantity);
                }
            }

            $subtotal = 0;
            foreach ($items as $item) {
                $qty   = (int) ($item['quantity']      ?? 1);
                $price = (float) ($item['selling_price'] ?? 0);
                $subtotal += $price * $qty;
            }
            $taxAmount   = $subtotal * ($taxRate / 100);
            $totalAmount = $subtotal + $taxAmount;

            $sale->update([
                'date'           => $request->date,
                'customer_code'  => $request->customer_code ?: null,
                'customer_name'  => $request->customer_name ?: null,
                'invoice_number' => $request->invoice_number,
                'subtotal'       => round($subtotal, 2),
                'tax_amount'     => round($taxAmount, 2),
                'total_amount'   => round($totalAmount, 2),
            ]);

            $sale->items()->delete();

            foreach ($items as $item) {
                $product      = Product::findOrFail($item['product_id']);
                $qty          = (int) ($item['quantity']      ?? 1);
                $sellingPrice = (float) ($item['selling_price'] ?? 0);
                $costPrice    = (float) ($product->cost_price    ?? 0);

                if ($product->stock_quantity < $qty) {
                    DB::rollBack();
                    return redirect()->back()->withInput()->with('error', "Insufficient stock for '{$product->name}'. Available: {$product->stock_quantity}, required: {$qty}.");
                }

                $lineAmount = $sellingPrice * $qty;
                $lineTax    = $lineAmount * ($taxRate / 100);
                $profit     = ($sellingPrice - $costPrice) * $qty;

                SaleItem::create([
                    'sale_id'       => $sale->id,
                    'product_id'    => $product->id,
                    'quantity'      => $qty,
                    'cost_price'    => $costPrice,
                    'selling_price' => $sellingPrice,
                    'profit'        => round($profit, 2),
                    'tax_applied'   => round($lineTax, 2),
                ]);

                $product->decrement('stock_quantity', $qty);
            }

            $sale->refresh();
            $customerCodeRaw = trim((string) ($request->customer_code ?? ''));

            $targetReceivable = $this->resolveTargetReceivableForSale(
                $sale,
                $request->invoice_number,
                $customerCodeRaw,
                $request->customer_name ?: null,
                $request->date
            );

            $sale->update(['receivable_id' => $targetReceivable->id]);

            if ($oldReceivableId && (int) $oldReceivableId !== (int) $targetReceivable->id) {
                $oldReceivable = Receivable::find($oldReceivableId);
                if ($oldReceivable) {
                    $oldReceivable->recalculateAmountFromSales();
                    $oldReceivable->syncDisplayFromLinkedSales();
                    $this->cleanupReceivableIfUnused($oldReceivable);
                }
            }

            $targetReceivable->recalculateAmountFromSales();
            $targetReceivable->syncDisplayFromLinkedSales();
            $targetReceivable->refresh();
            if (! $targetReceivable->hasValidBalance()) {
                DB::rollBack();

                return redirect()->back()->withInput()->with('error', 'The updated sale total would be less than payments already recorded on this customer balance.');
            }

            // Ledger: update the existing Debit entry for this sale
            CustomerLedgerEntry::where('source_type', 'sale')
                ->where('source_id', $sale->id)
                ->update([
                    'date'        => $request->date,
                    'reference'   => $request->invoice_number,
                    'debit'       => round($totalAmount, 2),
                ]);

            VatEntry::where('source_type', Sale::class)
                ->where('source_id', $sale->id)
                ->update([
                    'date'           => $request->date,
                    'invoice_number' => $request->invoice_number,
                    'customer_name'  => $request->customer_name ?: null,
                    'customer_code'  => $request->customer_code ?: null,
                    'subtotal'       => round($subtotal, 2),
                    'vat_rate'       => $taxRate,
                    'vat_amount'     => round($taxAmount, 2),
                    'total_amount'   => round($totalAmount, 2),
                ]);

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
            $receivableId = $sale->receivable_id;

            foreach ($sale->items as $item) {
                if ($item->product) {
                    $item->product->increment('stock_quantity', $item->quantity);
                }
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

            if ($receivableId) {
                $receivable = Receivable::find($receivableId);
                if ($receivable) {
                    $receivable->recalculateAmountFromSales();
                    $receivable->syncDisplayFromLinkedSales();
                    $this->cleanupReceivableIfUnused($receivable);
                }
            }

            DB::commit();

            return redirect()->route('sales.index')->with('success', 'Sale deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('sales.index')->with('error', 'Failed to delete sale: ' . $e->getMessage());
        }
    }

    /**
     * New sale: one receivable bucket per customer_code (non-empty), else one receivable per walk-in sale.
     */
    private function createReceivableForNewSale(Sale $sale): Receivable
    {
        $code = $sale->customer_code !== null ? trim((string) $sale->customer_code) : '';

        if ($code !== '') {
            return Receivable::firstOrCreate(
                ['customer_code' => $code],
                [
                    'date'           => $sale->date,
                    'customer_name'  => $sale->customer_name,
                    'invoice_number' => null,
                    'amount'         => 0,
                    'received'       => 0,
                ]
            );
        }

        return Receivable::create([
            'date'           => $sale->date,
            'invoice_number' => $sale->invoice_number,
            'customer_name'  => $sale->customer_name,
            'customer_code'  => null,
            'amount'         => 0,
            'received'       => 0,
        ]);
    }

    /**
     * After sale header changes: resolve which receivable row this sale should belong to.
     */
    private function resolveTargetReceivableForSale(
        Sale $sale,
        string $invoiceNumber,
        string $customerCodeRaw,
        ?string $customerName,
        mixed $date
    ): Receivable {
        $code = trim($customerCodeRaw);

        if ($code !== '') {
            return Receivable::firstOrCreate(
                ['customer_code' => $code],
                [
                    'date'           => $date,
                    'customer_name'  => $customerName,
                    'invoice_number' => null,
                    'amount'         => 0,
                    'received'       => 0,
                ]
            );
        }

        if ($sale->receivable_id) {
            $current = Receivable::find($sale->receivable_id);
            // Only reuse a dedicated walk-in row (no customer_code on receivable), not a merged customer bucket.
            if ($current
                && $current->customer_code === null
                && $current->sales()->count() === 1
                && (int) $current->sales()->first()->id === (int) $sale->id) {
                return $current;
            }
        }

        return Receivable::create([
            'date'           => $date,
            'invoice_number' => $invoiceNumber,
            'customer_name'  => $customerName,
            'customer_code'  => null,
            'amount'         => 0,
            'received'       => 0,
        ]);
    }

    /**
     * Remove receivable when it has no linked sales and no payments recorded.
     */
    private function cleanupReceivableIfUnused(?Receivable $receivable): void
    {
        if (! $receivable) {
            return;
        }

        $receivable->refresh();

        if ($receivable->sales()->count() === 0 && (float) $receivable->received === 0.0) {
            $receivable->delete();
        }
    }
}
