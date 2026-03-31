<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Supplier;
use App\Models\SupplierLedgerEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        $suppliers = Supplier::query()->orderBy('name')->paginate(25);

        $balances = SupplierLedgerEntry::query()
            ->selectRaw('supplier_id, COALESCE(SUM(credit), 0) - COALESCE(SUM(debit), 0) as balance')
            ->groupBy('supplier_id')
            ->pluck('balance', 'supplier_id');

        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return view('suppliers.index', [
            'suppliers' => $suppliers,
            'totalSuppliersCount' => Supplier::query()->count(),
            'balances' => $balances,
            'currencySymbol' => $currencySymbol,
        ]);
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:64'],
            'country' => ['nullable', 'string', 'max:128'],
            'notes' => ['nullable', 'string'],
        ]);

        Supplier::create($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Vendor added successfully.');
    }

    public function show(Supplier $supplier): RedirectResponse
    {
        return redirect()->route('suppliers.edit', $supplier);
    }

    public function ledger(Supplier $supplier): View
    {
        return view('suppliers.ledger', array_merge(
            ['supplier' => $supplier],
            $this->supplierLedgerPayload($supplier)
        ));
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', ['supplier' => $supplier]);
    }

    /**
     * @return array{ledgerEntries: \Illuminate\Support\Collection, ledgerBalance: float, ledgerTotalCredit: float, ledgerTotalPaid: float, currencySymbol: string}
     */
    private function supplierLedgerPayload(Supplier $supplier): array
    {
        $ledgerEntries = SupplierLedgerEntry::query()
            ->where('supplier_id', $supplier->id)
            ->orderBy('date')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $totalCredit = 0.0;
        $totalPaid = 0.0;
        $running = 0.0;
        foreach ($ledgerEntries as $entry) {
            $totalCredit += (float) $entry->credit;
            $totalPaid += (float) $entry->debit;
            $running += (float) $entry->credit - (float) $entry->debit;
            $entry->setAttribute('running_balance', $running);
        }

        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        return [
            'ledgerEntries' => $ledgerEntries,
            'ledgerBalance' => $running,
            'ledgerTotalCredit' => $totalCredit,
            'ledgerTotalPaid' => $totalPaid,
            'currencySymbol' => $currencySymbol,
        ];
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:64'],
            'country' => ['nullable', 'string', 'max:128'],
            'notes' => ['nullable', 'string'],
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Vendor removed successfully.');
    }
}
