<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class VatEntry extends Model
{
    protected $fillable = [
        'type',
        'source_type',
        'source_id',
        'date',
        'invoice_number',
        'customer_name',
        'customer_code',
        'subtotal',
        'vat_rate',
        'vat_amount',
        'total_amount',
    ];

    protected $casts = [
        'date'         => 'datetime',
        'subtotal'     => 'decimal:2',
        'vat_rate'     => 'decimal:2',
        'vat_amount'   => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
