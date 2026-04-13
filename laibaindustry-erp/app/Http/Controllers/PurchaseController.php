<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\Payable;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\TaxSetting;
use App\Models\VatEntry;
use App\Services\PurchaseReceivableOffsetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PurchaseController extends Controller
{
    public function index(): View|RedirectResponse
    {
        [$redirect, $from, $to] = parse_list_date_filters();
        if ($redirect) {
            return $redirect;
        }

        $query = Purchase::query()->with(['items', 'currency']);

        if ($search = request('search')) {
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like) {
                $q->where('invoice_number', 'like', $like)
                    ->orWhere('customer_name', 'like', $like)
                    ->orWhere('customer_code', 'like', $like)
                    ->orWhereHas('items', fn ($iq) => $iq->where('product_name', 'like', $like));
            });
        }
        if ($from) {
            $query->whereDate('date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('date', '<=', $to);
        }

        $purchases = $query
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $totals = Purchase::query()
            ->selectRaw('
                COALESCE(SUM(subtotal), 0)     AS total_subtotal,
                COALESCE(SUM(vat_amount), 0)   AS total_vat,
                COALESCE(SUM(total_amount), 0) AS total_purchases
            ')
            ->first();

        return view('purchases.index', compact('purchases', 'totals'));
    }

    public function export(): StreamedResponse
    {
        $purchases = Purchase::with('items')->orderByDesc('date')->get();

        return response()->streamDownload(function () use ($purchases) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Invoice #', 'Customer Code', 'Customer Name', 'Product', 'Qty', 'Price', 'Amount', 'VAT', 'Subtotal']);
            foreach ($purchases as $purchase) {
                foreach ($purchase->items as $item) {
                    fputcsv($handle, [
                        format_display_datetime($purchase->date),
                        $purchase->invoice_number,
                        $purchase->customer_code ?? '',
                        $purchase->customer_name ?? '',
                        $item->product_name,
                        $item->quantity,
                        number_format($item->price, 2, '.', ''),
                        number_format($item->amount, 2, '.', ''),
                        number_format($item->vat_amount, 2, '.', ''),
                        number_format($item->subtotal, 2, '.', ''),
                    ]);
                }
            }
            fclose($handle);
        }, 'purchases-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
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
        $vatRatePercent = (float) (TaxSetting::first()?->default_rate ?? 15.0);
        $vatRateDecimal = $vatRatePercent / 100;

        try {
            DB::beginTransaction();

            $purchaseSubtotal = 0;
            $purchaseVat = 0;

            foreach ($lines as $line) {
                $price = (float) ($line['price'] ?? 0);
                $qty = (int) ($line['quantity'] ?? 1);
                $amount = round($price * $qty, 2);
                $vat = round($amount * $vatRateDecimal, 2);
                $purchaseSubtotal += $amount;
                $purchaseVat += $vat;
            }

            $purchaseTotal = round($purchaseSubtotal + $purchaseVat, 2);

            $purchase = Purchase::create([
                'date' => $request->date,
                'customer_code' => $request->customer_code ?: null,
                'customer_name' => $request->customer_name ?: null,
                'invoice_number' => $request->invoice_number,
                'subtotal' => round($purchaseSubtotal, 2),
                'vat_amount' => round($purchaseVat, 2),
                'total_amount' => $purchaseTotal,
                'currency_id' => $defaultCurrencyId,
            ]);

            foreach ($lines as $line) {
                $price = (float) ($line['price'] ?? 0);
                $qty = (int) ($line['quantity'] ?? 1);
                $amount = round($price * $qty, 2);
                $vat = round($amount * $vatRateDecimal, 2);
                $subtotal = round($amount + $vat, 2);

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_name' => trim($line['product_name']),
                    'price' => $price,
                    'quantity' => $qty,
                    'amount' => $amount,
                    'vat_amount' => $vat,
                    'subtotal' => $subtotal,
                ]);
            }

            Payable::create([
                'purchase_id' => $purchase->id,
                'date' => $request->date,
                'invoice_number' => $request->invoice_number,
                'customer_name' => $request->customer_name ?: null,
                'customer_code' => $request->customer_code ?: null,
                'amount' => $purchaseTotal,
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

            // Ledger: Credit entry — we owe this party for the purchase
            $customer = $customerCode !== '' ? Customer::where('customer_code', $customerCode)->first() : null;
            if ($customer) {
                CustomerLedgerEntry::create([
                    'customer_id' => $customer->id,
                    'date' => $request->date,
                    'description' => 'Purchase',
                    'reference' => $request->invoice_number,
                    'debit' => 0,
                    'credit' => $purchaseTotal,
                    'source_type' => 'purchase',
                    'source_id' => $purchase->id,
                ]);
            }

            app(PurchaseReceivableOffsetService::class)->syncPurchaseOffsets(
                $purchase,
                $request->customer_code,
                \Carbon\Carbon::parse($request->date, config('app.timezone'))->startOfDay()
            );

            VatEntry::create([
                'type' => 'purchase',
                'source_type' => Purchase::class,
                'source_id' => $purchase->id,
                'date' => $request->date,
                'invoice_number' => $request->invoice_number,
                'customer_name' => $request->customer_name ?: null,
                'customer_code' => $request->customer_code ?: null,
                'subtotal' => round($purchaseSubtotal, 2),
                'vat_rate' => $vatRatePercent,
                'vat_amount' => round($purchaseVat, 2),
                'total_amount' => $purchaseTotal,
            ]);

            DB::commit();

            return redirect()->route('purchases.index')
                ->with('success', 'Purchase created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to create purchase: '.$e->getMessage());
        }
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['items', 'currency', 'receivableOffsets.receivable']);

        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase): View
    {
        $purchase->load('items');
        $customers = Customer::query()->orderBy('customer_name')->get();

        return view('purchases.edit', compact('purchase', 'customers'));
    }

    public function update(StorePurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        $lines = array_values(
            array_filter($request->items ?? [], fn ($i) => ! empty(trim($i['product_name'] ?? '')))
        );

        if (empty($lines)) {
            return redirect()->back()->withInput()
                ->with('error', 'Please add at least one item to the purchase.');
        }

        $defaultCurrencyId = \App\Models\Currency::query()->where('is_default', true)->value('id');
        $vatRatePercent = (float) (TaxSetting::first()?->default_rate ?? 15.0);
        $vatRateDecimal = $vatRatePercent / 100;

        try {
            DB::beginTransaction();

            $purchaseSubtotal = 0;
            $purchaseVat = 0;

            foreach ($lines as $line) {
                $price = (float) ($line['price'] ?? 0);
                $qty = (int) ($line['quantity'] ?? 1);
                $amount = round($price * $qty, 2);
                $vat = round($amount * $vatRateDecimal, 2);
                $purchaseSubtotal += $amount;
                $purchaseVat += $vat;
            }

            $purchaseTotal = round($purchaseSubtotal + $purchaseVat, 2);
            $purchase->update([
                'date' => $request->date,
                'customer_code' => $request->customer_code ?: null,
                'customer_name' => $request->customer_name ?: null,
                'invoice_number' => $request->invoice_number,
                'subtotal' => round($purchaseSubtotal, 2),
                'vat_amount' => round($purchaseVat, 2),
                'total_amount' => $purchaseTotal,
                'currency_id' => $defaultCurrencyId,
            ]);

            $purchase->items()->delete();

            foreach ($lines as $line) {
                $price = (float) ($line['price'] ?? 0);
                $qty = (int) ($line['quantity'] ?? 1);
                $amount = round($price * $qty, 2);
                $vat = round($amount * $vatRateDecimal, 2);
                $subtotal = round($amount + $vat, 2);

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_name' => trim($line['product_name']),
                    'price' => $price,
                    'quantity' => $qty,
                    'amount' => $amount,
                    'vat_amount' => $vat,
                    'subtotal' => $subtotal,
                ]);
            }

            Payable::where('purchase_id', $purchase->id)->update([
                'date' => $request->date,
                'invoice_number' => $request->invoice_number,
                'customer_name' => $request->customer_name ?: null,
                'customer_code' => $request->customer_code ?: null,
                'amount' => max($purchaseTotal, (float) (Payable::where('purchase_id', $purchase->id)->value('received') ?? 0)),
            ]);

            $customerCode = trim($request->customer_code ?? '');
            $customerName = trim($request->customer_name ?? '');
            $customer = null;
            if ($customerCode !== '') {
                $customer = Customer::firstOrCreate(
                    ['customer_code' => $customerCode],
                    ['customer_name' => $customerName ?: $customerCode]
                );
            }

            CustomerLedgerEntry::where('source_type', 'purchase')
                ->where('source_id', $purchase->id)
                ->update([
                    'customer_id' => $customer?->id,
                    'date' => $request->date,
                    'reference' => $request->invoice_number,
                    'credit' => $purchaseTotal,
                ]);

            if ($customer && ! CustomerLedgerEntry::where('source_type', 'purchase')->where('source_id', $purchase->id)->exists()) {
                CustomerLedgerEntry::create([
                    'customer_id' => $customer->id,
                    'date' => $request->date,
                    'description' => 'Purchase',
                    'reference' => $request->invoice_number,
                    'debit' => 0,
                    'credit' => $purchaseTotal,
                    'source_type' => 'purchase',
                    'source_id' => $purchase->id,
                ]);
            }

            if (! $customer) {
                CustomerLedgerEntry::where('source_type', 'purchase')
                    ->where('source_id', $purchase->id)
                    ->delete();
            }

            VatEntry::where('source_type', Purchase::class)
                ->where('source_id', $purchase->id)
                ->update([
                    'date' => $request->date,
                    'invoice_number' => $request->invoice_number,
                    'customer_name' => $request->customer_name ?: null,
                    'customer_code' => $request->customer_code ?: null,
                    'subtotal' => round($purchaseSubtotal, 2),
                    'vat_rate' => $vatRatePercent,
                    'vat_amount' => round($purchaseVat, 2),
                    'total_amount' => $purchaseTotal,
                ]);

            app(PurchaseReceivableOffsetService::class)->syncPurchaseOffsets(
                $purchase,
                $request->customer_code,
                \Carbon\Carbon::parse($request->date, config('app.timezone'))->startOfDay()
            );

            DB::commit();

            return redirect()->route('purchases.show', $purchase)
                ->with('success', 'Purchase updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->with('error', 'Failed to update purchase: '.$e->getMessage());
        }
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

            // Ledger: remove the Credit entry for this purchase
            CustomerLedgerEntry::where('source_type', 'purchase')
                ->where('source_id', $purchase->id)
                ->delete();

            app(PurchaseReceivableOffsetService::class)->clearPurchaseOffsets($purchase);

            VatEntry::where('source_type', Purchase::class)
                ->where('source_id', $purchase->id)
                ->delete();

            $purchase->items()->delete();
            $purchase->delete();

            DB::commit();

            return redirect()->route('purchases.index')
                ->with('success', 'Purchase deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->route('purchases.index')
                ->with('error', 'Failed to delete purchase: '.$e->getMessage());
        }
    }
}
