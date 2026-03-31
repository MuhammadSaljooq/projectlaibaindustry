<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierLedgerEntry extends Model
{
    protected $table = 'supplier_ledger_entries';

    protected $fillable = [
        'supplier_id',
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
        'date' => 'datetime',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
