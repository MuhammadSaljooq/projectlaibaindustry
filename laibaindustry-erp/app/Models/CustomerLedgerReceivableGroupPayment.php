<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerLedgerReceivableGroupPayment extends Model
{
    protected $table = 'customer_ledger_receivable_group_payments';

    protected $fillable = [
        'customer_ledger_entry_id',
        'receivable_group_payment_id',
    ];

    public function customerLedgerEntry(): BelongsTo
    {
        return $this->belongsTo(CustomerLedgerEntry::class);
    }

    public function receivableGroupPayment(): BelongsTo
    {
        return $this->belongsTo(ReceivableGroupPayment::class);
    }
}
