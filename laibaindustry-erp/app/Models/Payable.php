<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payable extends Model
{
    protected $fillable = [
        'purchase_id',
        'date',
        'invoice_number',
        'customer_name',
        'customer_code',
        'amount',
        'received',
        'received_date',
        'remaining_date',
    ];

    protected $casts = [
        'date' => 'datetime',
        'received_date' => 'date',
        'remaining_date' => 'date',
        'amount' => 'decimal:2',
        'received' => 'decimal:2',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function paymentLedgerEntries(): HasMany
    {
        return $this->hasMany(CustomerLedgerEntry::class, 'source_id')
            ->where('source_type', 'payment_made')
            ->orderByDesc('date')
            ->orderByDesc('id');
    }

    public function salesOffsets(): HasMany
    {
        return $this->hasMany(CustomerPayableSaleOffset::class)
            ->orderByDesc('offset_date')
            ->orderByDesc('id');
    }

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->received);
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->balance <= 0;
    }
}
