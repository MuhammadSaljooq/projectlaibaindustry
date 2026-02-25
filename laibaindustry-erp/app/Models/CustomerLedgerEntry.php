<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerLedgerEntry extends Model
{
    protected $table = 'customer_ledger_entries';

    protected $fillable = [
        'customer_id',
        'date',
        'description',
        'reference',
        'debit',
        'credit',
        'source_type',
        'source_id',
        'notes',
    ];

    protected $casts = [
        'date'   => 'datetime',
        'debit'  => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
