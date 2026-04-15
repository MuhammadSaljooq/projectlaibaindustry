<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayableGroupPaymentLine extends Model
{
    protected $fillable = [
        'payable_group_payment_id',
        'payable_id',
        'amount',
        'customer_ledger_entry_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function groupPayment(): BelongsTo
    {
        return $this->belongsTo(PayableGroupPayment::class, 'payable_group_payment_id');
    }

    public function payable(): BelongsTo
    {
        return $this->belongsTo(Payable::class);
    }

    public function customerLedgerEntry(): BelongsTo
    {
        return $this->belongsTo(CustomerLedgerEntry::class);
    }
}
