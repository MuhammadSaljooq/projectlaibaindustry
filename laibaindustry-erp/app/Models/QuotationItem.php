<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id',
        'sort_order',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'tax_amount',
        'amount',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    protected static function booted(): void
    {
        static::saving(function (QuotationItem $item) {
            $subtotal = (float) $item->quantity * (float) $item->unit_price;
            $item->tax_amount = round($subtotal * ((float) $item->tax_rate / 100), 2);
            $item->amount = round($subtotal + $item->tax_amount, 2);
        });
    }
}
