<?php

namespace App\Services;

use App\Models\CustomerLedgerEntry;
use App\Models\Payable;
use App\Models\Purchase;
use App\Models\VatEntry;

class PurchaseDeletionService
{
    public function __construct(
        private readonly PurchaseReceivableOffsetService $offsetService,
        private readonly SalePayableOffsetService $salePayableOffsetService
    ) {}

    public function delete(Purchase $purchase): void
    {
        $deleted = Payable::query()->where('purchase_id', $purchase->id)->delete();

        if (! $deleted && $purchase->invoice_number) {
            Payable::query()->where('invoice_number', $purchase->invoice_number)->delete();
        }

        CustomerLedgerEntry::query()
            ->where('source_type', 'purchase')
            ->where('source_id', $purchase->id)
            ->delete();

        $this->offsetService->clearPurchaseOffsets($purchase);
        $this->salePayableOffsetService->syncPayablesByCustomerCode($purchase->customer_code);

        VatEntry::query()
            ->where('source_type', Purchase::class)
            ->where('source_id', $purchase->id)
            ->delete();

        $purchase->items()->delete();
        $purchase->delete();
    }
}
