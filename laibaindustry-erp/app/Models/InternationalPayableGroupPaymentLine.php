<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternationalPayableGroupPaymentLine extends Model
{
    protected $fillable = [
        'international_payable_group_payment_id',
        'international_purchase_order_id',
        'amount',
        'international_payable_payment_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function groupPayment(): BelongsTo
    {
        return $this->belongsTo(InternationalPayableGroupPayment::class, 'international_payable_group_payment_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(InternationalPurchaseOrder::class, 'international_purchase_order_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(InternationalPayablePayment::class, 'international_payable_payment_id');
    }
}
