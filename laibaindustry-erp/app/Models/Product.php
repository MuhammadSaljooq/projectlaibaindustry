<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'category_id',
        'cost_price',
        'selling_price',
        'currency_id',
        'description',
        'stock_quantity',
        'reorder_level',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function scopeSearch(Builder $query, string $term): void
    {
        $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $term);
        $pattern = '%' . $escaped . '%';
        $query->where(function ($q) use ($pattern) {
            $q->where('name', 'like', $pattern)
                ->orWhere('sku', 'like', $pattern);
        });
    }

    public function scopePriceBetween(Builder $query, ?float $min, ?float $max): void
    {
        $query->when($min !== null, fn ($q) => $q->whereNotNull('selling_price')->where('selling_price', '>=', $min))
            ->when($max !== null, fn ($q) => $q->whereNotNull('selling_price')->where('selling_price', '<=', $max));
    }

    public function scopeLowStock(Builder $query): void
    {
        $query->whereRaw('stock_quantity <= reorder_level OR stock_quantity = 0');
    }
}
