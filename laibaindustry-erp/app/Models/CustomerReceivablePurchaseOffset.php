<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerReceivablePurchaseOffset extends Model
{
    protected $fillable = [
        'customer_id',
        'purchase_id',
        'receivable_id',
        'amount',
        'offset_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'offset_date' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function receivable(): BelongsTo
    {
        return $this->belongsTo(Receivable::class);
    }
}
