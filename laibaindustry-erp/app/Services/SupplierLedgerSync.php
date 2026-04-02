<?php

namespace App\Services;

use App\Models\InternationalPayablePayment;
use App\Models\InternationalPurchaseOrder;
use App\Models\SupplierLedgerEntry;
use Illuminate\Support\Str;

final class SupplierLedgerSync
{
    public static function syncInternationalPurchaseOrder(InternationalPurchaseOrder $order): void
    {
        SupplierLedgerEntry::query()
            ->where('source_type', 'international_purchase_order')
            ->where('source_id', $order->id)
            ->delete();

        if (! $order->supplier_id) {
            return;
        }

        $order->loadMissing('lines');
        $firstLine = $order->lines->first();
        $desc = $firstLine
            ? 'International purchase — '.Str::limit($firstLine->product_name, 120)
            : 'International purchase';

        SupplierLedgerEntry::create([
            'supplier_id' => $order->supplier_id,
            'date' => $order->date->copy()->startOfDay(),
            'description' => $desc,
            'reference' => 'IPO-'.$order->id,
            'debit' => 0,
            'credit' => $order->total_amount,
            'source_type' => 'international_purchase_order',
            'source_id' => $order->id,
        ]);
    }

    public static function recordPayment(InternationalPayablePayment $payment, InternationalPurchaseOrder $order): void
    {
        if (! $order->supplier_id) {
            return;
        }

        SupplierLedgerEntry::create([
            'supplier_id' => $order->supplier_id,
            'date' => $payment->payment_date->copy()->startOfDay(),
            'description' => 'International payment',
            'reference' => 'IPP-'.$payment->id,
            'debit' => $payment->amount,
            'credit' => 0,
            'source_type' => 'international_payable_payment',
            'source_id' => $payment->id,
            'notes' => $payment->notes,
        ]);
    }
}
