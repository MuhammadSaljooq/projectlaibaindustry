<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Receivable extends Model
{
    protected $fillable = [
        'date',
        'invoice_number',
        'customer_name',
        'customer_code',
        'amount',
        'received',
        'payment_received_at',
    ];

    protected $casts = [
        'date' => 'datetime',
        'payment_received_at' => 'datetime',
    ];

    /** @return HasMany<CustomerLedgerEntry, $this> */
    public function paymentLedgerEntries(): HasMany
    {
        return $this->hasMany(CustomerLedgerEntry::class, 'source_id')
            ->where('source_type', 'payment_received')
            ->orderBy('date')
            ->orderBy('id');
    }
}
