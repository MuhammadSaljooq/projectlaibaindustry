<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternationalPurchase extends Model
{
    protected $table = 'international_purchases';

    protected static function booted(): void
    {
        static::deleting(function (InternationalPurchase $purchase) {
            SupplierLedgerEntry::query()
                ->where('source_type', 'international_purchase')
                ->where('source_id', $purchase->id)
                ->delete();

            $paymentIds = $purchase->payablePayments()->pluck('id');
            if ($paymentIds->isNotEmpty()) {
                SupplierLedgerEntry::query()
                    ->where('source_type', 'international_payable_payment')
                    ->whereIn('source_id', $paymentIds)
                    ->delete();
            }
        });
    }

    protected $fillable = [
        'supplier_id',
        'date',
        'product_name',
        'quantity',
        'unit_price',
        'total_amount',
    ];

    protected $casts = [
        'supplier_id' => 'integer',
        'date' => 'date',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function payablePayments(): HasMany
    {
        return $this->hasMany(InternationalPayablePayment::class, 'international_purchase_id');
    }
}
