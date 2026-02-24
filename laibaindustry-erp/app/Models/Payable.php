<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function scopeSearch(Builder $query, string $term): void
    {
        $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $term);
        $pattern = '%' . $escaped . '%';
        $query->where(function (Builder $q) use ($pattern) {
            $q->where('invoice_number', 'like', $pattern)
                ->orWhere('customer_name', 'like', $pattern)
                ->orWhere('customer_code', 'like', $pattern);
        });
    }
}
