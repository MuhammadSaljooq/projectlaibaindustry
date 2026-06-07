<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAddition extends Model
{
    protected $fillable = [
        'product_id',
        'date',
        'quantity',
        'unit_cost',
        'total_cost',
        'reference',
        'notes',
    ];

    protected $casts = [
        'date'       => 'date',
        'unit_cost'  => 'float',
        'total_cost' => 'float',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeSearch(Builder $query, string $term): void
    {
        $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $term);
        $pattern = '%' . $escaped . '%';
        $query->where(function (Builder $q) use ($pattern) {
            $q->where('reference', 'like', $pattern)
              ->orWhere('notes', 'like', $pattern)
              ->orWhereHas('product', fn (Builder $p) =>
                  $p->where('name', 'like', $pattern)
                    ->orWhere('sku', 'like', $pattern)
              );
        });
    }
}
