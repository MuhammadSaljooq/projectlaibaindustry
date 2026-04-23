<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierLedgerEntry;
use App\Models\InternationalPurchaseOrder;
use App\Support\StatementCompany;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $search  = trim((string) $request->query('search', ''));
        $country = trim((string) $request->query('country', ''));

        $query = Supplier::query()->orderBy('name');

        if ($country !== '') {
            $query->where('country', $country);
        }

        $suppliers = $query->get();

        $countries = Supplier::query()
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');

        $balances = SupplierLedgerEntry::query()
            ->selectRaw('supplier_id, COALESCE(SUM(credit), 0) - COALESCE(SUM(debit), 0) as balance')
            ->groupBy('supplier_id')
            ->pluck('balance', 'supplier_id');

        $currencySymbol = $this->vendorInternationalCurrency();

        return view('suppliers.index', [
            'suppliers'          => $suppliers,
            'totalSuppliersCount' => Supplier::query()->count(),
            'balances'           => $balances,
            'currencySymbol'     => $currencySymbol,
            'search'             => $search,
            'country'            => $country,
            'countries'          => $countries,
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

    public function ledgerPdf(Supplier $supplier): Response|RedirectResponse
    {
        $fontsDir = storage_path('fonts');
        if (! is_dir($fontsDir)) {
            @mkdir($fontsDir, 0755, true);
        }

        try {
            $payload = $this->supplierLedgerPayload($supplier);
            $pdf = Pdf::loadView('suppliers.ledger-pdf', array_merge(
                [
                    'supplier' => $supplier,
                    'company' => StatementCompany::normalize(config('company')),
                ],
                $payload,
                ['currencySymbol' => 'USD']
            ))
                ->setPaper('a4', 'portrait')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isRemoteEnabled', false)
                ->setOption('fontDir', $fontsDir)
                ->setOption('fontCache', $fontsDir);

            $filename = sprintf(
                'vendor-statement-%s-%s.pdf',
                Str::slug($supplier->name),
                now()->format('d-m-Y')
            );

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        } catch (\Throwable $e) {
            Log::error('Vendor statement PDF generation failed', [
                'supplier' => $supplier->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('suppliers.ledger', $supplier)
                ->with('error', 'PDF generation failed: '.$e->getMessage());
        }
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', ['supplier' => $supplier]);
    }

    /**
     * @return array{
     *     ledgerEntries: \Illuminate\Support\Collection,
     *     ledgerBalance: float,
     *     ledgerTotalCredit: float,
     *     ledgerTotalPaid: float,
     *     currencySymbol: string,
     *     outstandingPayables: \Illuminate\Support\Collection
     * }
     */
    private function supplierLedgerPayload(Supplier $supplier): array
    {
        $ledgerEntries = SupplierLedgerEntry::query()
            ->where('supplier_id', $supplier->id)
            ->orderByRaw('CASE WHEN created_at IS NULL THEN 1 ELSE 0 END')
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

        $outstandingPayables = InternationalPurchaseOrder::query()
            ->where('supplier_id', $supplier->id)
            ->withSum('payablePayments', 'amount')
            ->whereRaw('COALESCE(total_amount, 0) - COALESCE((SELECT SUM(amount) FROM international_payable_payments WHERE international_payable_payments.international_purchase_order_id = international_purchase_orders.id), 0) > 0.009')
            ->orderBy('date')
            ->orderBy('id')
            ->get()
            ->map(fn (InternationalPurchaseOrder $order) => [
                'order_id' => (int) $order->id,
                'invoice_number' => $order->invoice_number,
                'date' => $order->date,
                'outstanding' => round((float) $order->total_amount - (float) ($order->payable_payments_sum_amount ?? 0), 2),
            ]);

        $currencySymbol = $this->vendorInternationalCurrency();

        return [
            'ledgerEntries' => $ledgerEntries,
            'ledgerBalance' => $running,
            'ledgerTotalCredit' => $totalCredit,
            'ledgerTotalPaid' => $totalPaid,
            'currencySymbol' => $currencySymbol,
            'outstandingPayables' => $outstandingPayables,
        ];
    }

    private function vendorInternationalCurrency(): string
    {
        return 'USD';
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
