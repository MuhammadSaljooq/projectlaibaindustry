<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Currency;
use App\Models\CustomerLedgerEntry;
use App\Models\Payable;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource (purchase line items with purchase header).
     */
    public function index(Request $request): View
    {
        $query = PurchaseItem::query()
            ->with('purchase')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->select('purchase_items.*')
            ->orderByDesc('purchases.date')
            ->orderBy('purchases.id')
            ->orderBy('purchase_items.id');

        if ($search = $request->query('search')) {
            $term = str_replace(['%', '_'], ['\\%', '\\_'], trim($search));
            $pattern = '%' . $term . '%';
            $query->where(function ($q) use ($pattern) {
                $q->where('purchases.customer_name', 'like', $pattern)
                    ->orWhere('purchases.customer_code', 'like', $pattern)
                    ->orWhere('purchases.invoice_number', 'like', $pattern)
                    ->orWhere('purchase_items.product_name', 'like', $pattern);
            });
        }

        $items = $query->paginate(25)->withQueryString();

        $totals = Purchase::query()
            ->selectRaw('COALESCE(SUM(subtotal), 0) as total_subtotal, COALESCE(SUM(vat_amount), 0) as total_vat, COALESCE(SUM(total_amount), 0) as total_purchases')
            ->first();

        return view('purchases.index', [
            'items' => $items,
            'totals' => $totals,
        ]);
    }

    public function create(): View
    {
        $customers = Customer::query()->orderBy('customer_name')->get(['id', 'customer_code', 'customer_name']);
        return view('purchases.create', ['customers' => $customers]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'customer_code' => ['nullable', 'string', 'max:100'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $items = array_values(array_filter($request->items ?? [], fn ($i) => ! empty(trim($i['product_name'] ?? ''))));
        if (empty($items)) {
            return redirect()->back()->withInput()->with('error', 'Please add at least one line item with a product name.');
        }

        $defaultCurrencyId = Currency::query()->where('is_default', true)->value('id');
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

            $subtotalSum = 0;
            $vatSum = 0;
            foreach ($items as $item) {
                $qty = (int) $item['quantity'];
                $price = (float) $item['price'];
                $amount = round($price * $qty, 2);
                $vat = round($amount * ($taxRate / 100), 2);
                $lineSubtotal = round($amount + $vat, 2);
                $subtotalSum += $amount;
                $vatSum += $vat;
            }
            $totalAmount = round($subtotalSum + $vatSum, 2);

            $purchase = Purchase::create([
                'date' => $request->date,
                'customer_code' => $customerCode !== '' ? $customerCode : null,
                'customer_name' => $customerName ?? $request->customer_name,
                'invoice_number' => $request->invoice_number ?: null,
                'subtotal' => round($subtotalSum, 2),
                'vat_amount' => round($vatSum, 2),
                'total_amount' => $totalAmount,
                'currency_id' => $defaultCurrencyId,
            ]);

            foreach ($items as $item) {
                $qty = (int) $item['quantity'];
                $price = (float) $item['price'];
                $amount = round($price * $qty, 2);
                $vat = round($amount * ($taxRate / 100), 2);
                $lineSubtotal = round($amount + $vat, 2);
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_name' => trim($item['product_name']),
                    'price' => $price,
                    'quantity' => $qty,
                    'amount' => $amount,
                    'vat_amount' => $vat,
                    'subtotal' => $lineSubtotal,
                ]);
            }

            $payable = Payable::create([
                'purchase_id' => $purchase->id,
                'date' => $purchase->date,
                'invoice_number' => $purchase->invoice_number,
                'customer_name' => $purchase->customer_name,
                'customer_code' => $purchase->customer_code,
                'amount' => $purchase->total_amount,
                'received' => 0,
            ]);

            CustomerLedgerEntry::create([
                'customer_code' => $payable->customer_code ?? '',
                'customer_name' => $payable->customer_name,
                'entry_date' => $payable->date,
                'type' => 'payable',
                'reference' => ($payable->customer_code !== null && $payable->customer_code !== '') ? $payable->customer_code : ($payable->invoice_number ?: 'Bill'),
                'debit' => 0,
                'credit' => $payable->amount,
                'payable_id' => $payable->id,
            ]);

            DB::commit();
            return redirect()->route('purchases.index')->with('success', 'Purchase recorded successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to save purchase: ' . $e->getMessage());
        }
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load('items');
        return view('purchases.show', ['purchase' => $purchase]);
    }

    public function edit(Purchase $purchase): View
    {
        $purchase->load('items');
        $customers = Customer::query()->orderBy('customer_name')->get(['id', 'customer_code', 'customer_name']);
        return view('purchases.edit', ['purchase' => $purchase, 'customers' => $customers]);
    }

    public function update(Request $request, Purchase $purchase): RedirectResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'customer_code' => ['nullable', 'string', 'max:100'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $items = array_values(array_filter($request->items ?? [], fn ($i) => ! empty(trim($i['product_name'] ?? ''))));
        if (empty($items)) {
            return redirect()->back()->withInput()->with('error', 'Please add at least one line item with a product name.');
        }

        $taxRate = 15.0;

        try {
            DB::beginTransaction();

            $subtotalSum = 0;
            $vatSum = 0;
            foreach ($items as $item) {
                $qty = (int) $item['quantity'];
                $price = (float) $item['price'];
                $amount = round($price * $qty, 2);
                $vat = round($amount * ($taxRate / 100), 2);
                $subtotalSum += $amount;
                $vatSum += $vat;
            }
            $totalAmount = round($subtotalSum + $vatSum, 2);

            $purchase->update([
                'date' => $request->date,
                'customer_code' => $request->customer_code ?: null,
                'customer_name' => $request->customer_name ?: null,
                'invoice_number' => $request->invoice_number ?: null,
                'subtotal' => round($subtotalSum, 2),
                'vat_amount' => round($vatSum, 2),
                'total_amount' => $totalAmount,
            ]);

            $purchase->items()->delete();
            foreach ($items as $item) {
                $qty = (int) $item['quantity'];
                $price = (float) $item['price'];
                $amount = round($price * $qty, 2);
                $vat = round($amount * ($taxRate / 100), 2);
                $lineSubtotal = round($amount + $vat, 2);
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_name' => trim($item['product_name']),
                    'price' => $price,
                    'quantity' => $qty,
                    'amount' => $amount,
                    'vat_amount' => $vat,
                    'subtotal' => $lineSubtotal,
                ]);
            }

            $payable = $purchase->payable()->updateOrCreate(
                ['purchase_id' => $purchase->id],
                [
                    'date' => $purchase->date,
                    'invoice_number' => $purchase->invoice_number,
                    'customer_name' => $purchase->customer_name,
                    'customer_code' => $purchase->customer_code,
                    'amount' => $purchase->total_amount,
                ]
            );

            $payableRef = ($payable->customer_code !== null && $payable->customer_code !== '') ? $payable->customer_code : ($payable->invoice_number ?: 'Bill');
            CustomerLedgerEntry::query()
                ->where('payable_id', $payable->id)
                ->where('type', 'payable')
                ->update([
                    'entry_date' => $payable->date,
                    'reference' => $payableRef,
                    'credit' => $payable->amount,
                    'customer_code' => $payable->customer_code ?? '',
                    'customer_name' => $payable->customer_name,
                ]);

            if (CustomerLedgerEntry::query()->where('payable_id', $payable->id)->where('type', 'payable')->doesntExist()) {
                CustomerLedgerEntry::create([
                    'customer_code' => $payable->customer_code ?? '',
                    'customer_name' => $payable->customer_name,
                    'entry_date' => $payable->date,
                    'type' => 'payable',
                    'reference' => $payableRef,
                    'debit' => 0,
                    'credit' => $payable->amount,
                    'payable_id' => $payable->id,
                ]);
            }

            DB::commit();
            return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to update purchase: ' . $e->getMessage());
        }
    }

    public function destroy(Purchase $purchase): RedirectResponse
    {
        $purchase->delete();
        return redirect()->route('purchases.index')->with('success', 'Purchase deleted successfully.');
    }
}
