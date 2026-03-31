<?php

namespace App\Services;

use App\Models\InternationalPayablePayment;
use App\Models\InternationalPurchase;
use App\Models\SupplierLedgerEntry;
use Illuminate\Support\Str;

final class SupplierLedgerSync
{
    public static function syncInternationalPurchase(InternationalPurchase $purchase): void
    {
        SupplierLedgerEntry::query()
            ->where('source_type', 'international_purchase')
            ->where('source_id', $purchase->id)
            ->delete();

        if (! $purchase->supplier_id) {
            return;
        }

        SupplierLedgerEntry::create([
            'supplier_id' => $purchase->supplier_id,
            'date' => $purchase->date->copy()->startOfDay(),
            'description' => 'International purchase — ' . Str::limit($purchase->product_name, 200),
            'reference' => 'IP-' . $purchase->id,
            'debit' => 0,
            'credit' => $purchase->total_amount,
            'source_type' => 'international_purchase',
            'source_id' => $purchase->id,
        ]);
    }

    public static function recordPayment(InternationalPayablePayment $payment, InternationalPurchase $purchase): void
    {
        if (! $purchase->supplier_id) {
            return;
        }

        SupplierLedgerEntry::create([
            'supplier_id' => $purchase->supplier_id,
            'date' => $payment->payment_date->copy()->startOfDay(),
            'description' => 'International payment',
            'reference' => 'IPP-' . $payment->id,
            'debit' => $payment->amount,
            'credit' => 0,
            'source_type' => 'international_payable_payment',
            'source_id' => $payment->id,
            'notes' => $payment->notes,
        ]);
    }
}
