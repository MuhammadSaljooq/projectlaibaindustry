<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\VatEntry;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VatController extends Controller
{
    public function index(): View
    {
        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        $query = VatEntry::query();

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%");
            });
        }
        if ($from = request('from')) {
            $query->whereDate('date', '>=', $from);
        }
        if ($to = request('to')) {
            $query->whereDate('date', '<=', $to);
        }

        $filteredSalesVat = (clone $query)->where('type', 'sale')->sum('vat_amount');
        $filteredPurchaseVat = (clone $query)->where('type', 'purchase')->sum('vat_amount');
        $filteredNetVat = (float) $filteredSalesVat - (float) $filteredPurchaseVat;

        $entries = $query
            ->orderByDesc('date')
            ->paginate(25)
            ->appends(request()->query());

        $totals = VatEntry::query()
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'sale'     THEN vat_amount ELSE 0 END), 0) AS sales_vat,
                COALESCE(SUM(CASE WHEN type = 'purchase' THEN vat_amount ELSE 0 END), 0) AS purchase_vat
            ")
            ->first();

        $totals->net_vat = (float) $totals->sales_vat - (float) $totals->purchase_vat;

        return view('vat.index', compact(
            'entries',
            'totals',
            'currencySymbol',
            'filteredSalesVat',
            'filteredPurchaseVat',
            'filteredNetVat'
        ));
    }

    public function export(): StreamedResponse
    {
        $entries = VatEntry::orderByDesc('date')->get();

        return response()->streamDownload(function () use ($entries) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Type', 'Invoice #', 'Customer/Supplier', 'Subtotal', 'VAT Rate %', 'VAT Amount', 'Total']);
            foreach ($entries as $entry) {
                fputcsv($handle, [
                    $entry->date->format('Y-m-d'),
                    ucfirst($entry->type),
                    $entry->invoice_number ?? '',
                    $entry->customer_name ?? $entry->customer_code ?? '',
                    number_format($entry->subtotal, 2, '.', ''),
                    number_format($entry->vat_rate, 2, '.', ''),
                    number_format($entry->vat_amount, 2, '.', ''),
                    number_format($entry->total_amount, 2, '.', ''),
                ]);
            }
            fclose($handle);
        }, 'vat-entries-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
