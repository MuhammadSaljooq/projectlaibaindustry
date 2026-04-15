<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerLedgerPayableGroupPayment extends Model
{
    protected $table = 'customer_ledger_payable_group_payments';

    protected $fillable = [
        'customer_ledger_entry_id',
        'payable_group_payment_id',
    ];

    public function customerLedgerEntry(): BelongsTo
    {
        return $this->belongsTo(CustomerLedgerEntry::class);
    }

    public function payableGroupPayment(): BelongsTo
    {
        return $this->belongsTo(PayableGroupPayment::class);
    }
}
