<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'customer_code',
        'customer_name',
        'phone',
        'email',
        'address',
    ];

    public function scopeSearch(Builder $query, string $term): void
    {
        $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $term);
        $pattern = '%' . $escaped . '%';
        $query->where(function (Builder $q) use ($pattern) {
            $q->where('customer_name', 'like', $pattern)
                ->orWhere('customer_code', 'like', $pattern)
                ->orWhere('email', 'like', $pattern)
                ->orWhere('phone', 'like', $pattern)
                ->orWhere('address', 'like', $pattern);
        });
    }
}
