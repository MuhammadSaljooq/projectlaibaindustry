<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'customer_code',
        'customer_name',
        'phone',
        'email',
        'address',
        'opening_balance',
        'opening_balance_date',
    ];

    protected $casts = [
        'opening_balance'      => 'decimal:2',
        'opening_balance_date' => 'date',
    ];

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(CustomerLedgerEntry::class)->orderBy('date')->orderBy('id');
    }
}
