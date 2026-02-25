<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Models\Customer;
use App\Models\Payable;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    private const VAT_RATE = 0.15;

    public function index(): View
    {
        $items = PurchaseItem::query()
            ->with('purchase')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->select('purchase_items.*')
            ->orderByDesc('purchases.date')
            ->orderBy('purchases.id')
            ->orderBy('purchase_items.id')
            ->paginate(25);

        $totals = Purchase::query()
            ->selectRaw('
                COALESCE(SUM(subtotal), 0)    AS total_subtotal,
                COALESCE(SUM(vat_amount), 0)  AS total_vat,
                COALESCE(SUM(total_amount), 0) AS total_purchases
            ')
            ->first();

        return view('purchases.index', compact('items', 'totals'));
    }

    public function create(): View
    {
        $customers = Customer::query()->orderBy('customer_name')->get();

        return view('purchases.create', compact('customers'));
    }

    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        $lines = array_values(
            array_filter($request->items ?? [], fn ($i) => ! empty(trim($i['product_name'] ?? '')))
        );

        if (empty($lines)) {
            return redirect()->back()->withInput()
                ->with('error', 'Please add at least one item to the purchase.');
        }

        $defaultCurrencyId = \App\Models\Currency::query()->where('is_default', true)->value('id');

        try {
            DB::beginTransaction();

            $purchaseSubtotal = 0;
            $purchaseVat      = 0;

            foreach ($lines as $line) {
                $price    = (float) ($line['price']    ?? 0);
                $qty      = (int)   ($line['quantity'] ?? 1);
                $amount   = round($price * $qty, 2);
                $vat      = round($amount * self::VAT_RATE, 2);
                $purchaseSubtotal += $amount;
                $purchaseVat      += $vat;
            }

            $purchaseTotal = round($purchaseSubtotal + $purchaseVat, 2);

            $purchase = Purchase::create([
                'date'           => $request->date,
                'customer_code'  => $request->customer_code  ?: null,
                'customer_name'  => $request->customer_name  ?: null,
                'invoice_number' => $request->invoice_number,
                'subtotal'       => round($purchaseSubtotal, 2),
                'vat_amount'     => round($purchaseVat, 2),
                'total_amount'   => $purchaseTotal,
                'currency_id'    => $defaultCurrencyId,
            ]);

            foreach ($lines as $line) {
                $price    = (float) ($line['price']    ?? 0);
                $qty      = (int)   ($line['quantity'] ?? 1);
                $amount   = round($price * $qty, 2);
                $vat      = round($amount * self::VAT_RATE, 2);
                $subtotal = round($amount + $vat, 2);

                PurchaseItem::create([
                    'purchase_id'  => $purchase->id,
                    'product_name' => trim($line['product_name']),
                    'price'        => $price,
                    'quantity'     => $qty,
                    'amount'       => $amount,
                    'vat_amount'   => $vat,
                    'subtotal'     => $subtotal,
                ]);
            }

            Payable::create([
                'purchase_id'    => $purchase->id,
                'date'           => $request->date,
                'invoice_number' => $request->invoice_number,
                'customer_name'  => $request->customer_name  ?: null,
                'customer_code'  => $request->customer_code  ?: null,
                'amount'         => $purchaseTotal,
            ]);

            // Only upsert a customer record when a customer_code is explicitly provided
            $customerCode = trim($request->customer_code ?? '');
            $customerName = trim($request->customer_name ?? '');
            if ($customerCode !== '') {
                Customer::firstOrCreate(
                    ['customer_code' => $customerCode],
                    ['customer_name' => $customerName ?: $customerCode]
                );
            }

            DB::commit();

            return redirect()->route('purchases.index')
                ->with('success', 'Purchase created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to create purchase: ' . $e->getMessage());
        }
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['items', 'currency']);

        return view('purchases.show', compact('purchase'));
    }

    public function destroy(Purchase $purchase): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Delete the linked payable — primary lookup via purchase_id (reliable),
            // fallback to invoice_number for legacy rows created before the FK was added.
            $deleted = Payable::where('purchase_id', $purchase->id)->delete();

            if (! $deleted && $purchase->invoice_number) {
                Payable::where('invoice_number', $purchase->invoice_number)->delete();
            }

            $purchase->items()->delete();
            $purchase->delete();

            DB::commit();

            return redirect()->route('purchases.index')
                ->with('success', 'Purchase deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->route('purchases.index')
                ->with('error', 'Failed to delete purchase: ' . $e->getMessage());
        }
    }
}
