<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternationalPurchase extends Model
{
    protected $table = 'international_purchases';

    protected $fillable = [
        'international_purchase_order_id',
        'product_name',
        'quantity',
        'unit_price',
        'total_amount',
    ];

    protected $casts = [
        'international_purchase_order_id' => 'integer',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(InternationalPurchaseOrder::class, 'international_purchase_order_id');
    }
}
