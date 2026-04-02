<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternationalPurchaseOrder extends Model
{
    protected $table = 'international_purchase_orders';

    protected static function booted(): void
    {
        static::deleting(function (InternationalPurchaseOrder $order) {
            SupplierLedgerEntry::query()
                ->where('source_type', 'international_purchase_order')
                ->where('source_id', $order->id)
                ->delete();

            $paymentIds = $order->payablePayments()->pluck('id');
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
        'invoice_number',
        'total_amount',
    ];

    protected $casts = [
        'supplier_id' => 'integer',
        'date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InternationalPurchase::class, 'international_purchase_order_id');
    }

    public function payablePayments(): HasMany
    {
        return $this->hasMany(InternationalPayablePayment::class, 'international_purchase_order_id');
    }
}
