<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerLedgerEntry extends Model
{
    protected $fillable = [
        'customer_code',
        'customer_name',
        'entry_date',
        'type',
        'reference',
        'debit',
        'credit',
        'payment_type',
        'receivable_id',
        'payable_id',
    ];

    protected $casts = [
        'entry_date' => 'datetime',
        'debit' => 'float',
        'credit' => 'float',
    ];

    public function receivable(): BelongsTo
    {
        return $this->belongsTo(Receivable::class);
    }

    public function payable(): BelongsTo
    {
        return $this->belongsTo(Payable::class);
    }
}
