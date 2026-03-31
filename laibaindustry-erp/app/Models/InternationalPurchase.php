<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternationalPurchase extends Model
{
    protected $table = 'international_purchases';

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
