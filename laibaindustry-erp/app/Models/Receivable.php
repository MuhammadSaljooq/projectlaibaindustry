<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Receivable extends Model
{
    protected $fillable = [
        'date',
        'invoice_number',
        'customer_name',
        'customer_code',
        'amount',
        'received',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

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
