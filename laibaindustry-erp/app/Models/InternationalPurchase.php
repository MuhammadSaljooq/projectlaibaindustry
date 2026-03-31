<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternationalPurchase extends Model
{
    protected $table = 'international_purchases';

    protected $fillable = [
        'date',
        'product_name',
        'quantity',
        'unit_price',
        'total_amount',
    ];

    protected $casts = [
        'date' => 'date',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];
}
