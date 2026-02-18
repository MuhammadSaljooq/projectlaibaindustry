<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'purchase_id',
        'product_name',
        'price',
        'quantity',
        'amount',
        'vat_amount',
        'subtotal',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }
}
