<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceivableGroupPaymentLine extends Model
{
    protected $fillable = [
        'receivable_group_payment_id',
        'receivable_id',
        'amount',
        'customer_ledger_entry_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function groupPayment(): BelongsTo
    {
        return $this->belongsTo(ReceivableGroupPayment::class, 'receivable_group_payment_id');
    }

    public function receivable(): BelongsTo
    {
        return $this->belongsTo(Receivable::class);
    }

    public function customerLedgerEntry(): BelongsTo
    {
        return $this->belongsTo(CustomerLedgerEntry::class);
    }
}
