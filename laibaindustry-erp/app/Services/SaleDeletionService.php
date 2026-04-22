<?php

namespace App\Services;

use App\Models\CustomerLedgerEntry;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\VatEntry;
use Illuminate\Database\Eloquent\Builder;

class SaleDeletionService
{
    public function __construct(
        private readonly SalePayableOffsetService $salePayableOffsetService
    ) {}

    public function delete(Sale $sale): void
    {
        $sale->load('items.product');

        foreach ($sale->items as $item) {
            if ($item->product) {
                $item->product->increment('stock_quantity', $item->quantity);
            }
        }

        if ($sale->invoice_number) {
            $this->receivablesLinkedToSale($sale->invoice_number, $sale->customer_code)->delete();
        }

        CustomerLedgerEntry::query()
            ->where('source_type', 'sale')
            ->where('source_id', $sale->id)
            ->delete();

        $this->salePayableOffsetService->clearSaleOffsets($sale);

        VatEntry::query()
            ->where('source_type', Sale::class)
            ->where('source_id', $sale->id)
            ->delete();

        $sale->items()->delete();
        $sale->delete();
    }

    /**
     * Receivable rows created from a sale are keyed by invoice (and customer code when set).
     *
     * @return Builder<Receivable>
     */
    public function receivablesLinkedToSale(?string $invoiceNumber, ?string $customerCode): Builder
    {
        $query = Receivable::query();
        if (! $invoiceNumber) {
            return $query->whereRaw('0 = 1');
        }

        $query->where('invoice_number', $invoiceNumber);
        $trimCode = trim((string) ($customerCode ?? ''));
        if ($trimCode !== '') {
            $query->where('customer_code', $trimCode);
        } else {
            $query->where(function (Builder $q) {
                $q->whereNull('customer_code')->orWhere('customer_code', '');
            });
        }

        return $query->orderBy('id');
    }
}
