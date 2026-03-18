<?php

namespace App\Http\Controllers;

use App\Models\VatEntry;
use Illuminate\View\View;

class VatController extends Controller
{
    public function index(): View
    {
        $entries = VatEntry::query()
            ->orderByDesc('date')
            ->paginate(25);

        $totals = VatEntry::query()
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'sale'     THEN vat_amount ELSE 0 END), 0) AS sales_vat,
                COALESCE(SUM(CASE WHEN type = 'purchase' THEN vat_amount ELSE 0 END), 0) AS purchase_vat
            ")
            ->first();

        $totals->net_vat = (float) $totals->sales_vat - (float) $totals->purchase_vat;

        return view('vat.index', compact('entries', 'totals'));
    }
}
