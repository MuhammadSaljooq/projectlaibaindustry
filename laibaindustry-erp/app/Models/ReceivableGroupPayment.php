<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReceivableGroupPayment extends Model
{
    protected $fillable = [
        'group_key',
        'payment_date',
        'amount',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /** @return HasMany<ReceivableGroupPaymentLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(ReceivableGroupPaymentLine::class);
    }
}
