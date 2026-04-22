<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPayableSaleOffset extends Model
{
    protected $table = 'customer_payable_sales_offsets';

    protected $fillable = [
        'customer_id',
        'sale_id',
        'payable_id',
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

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function payable(): BelongsTo
    {
        return $this->belongsTo(Payable::class);
    }
}
