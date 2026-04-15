<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternationalPayablePayment extends Model
{
    protected $table = 'international_payable_payments';

    protected $fillable = [
        'international_purchase_order_id',
        'international_payable_group_payment_id',
        'payment_date',
        'amount',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function internationalPurchaseOrder(): BelongsTo
    {
        return $this->belongsTo(InternationalPurchaseOrder::class, 'international_purchase_order_id');
    }

    public function groupPayment(): BelongsTo
    {
        return $this->belongsTo(InternationalPayableGroupPayment::class, 'international_payable_group_payment_id');
    }
}
