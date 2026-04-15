<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternationalPayableGroupPayment extends Model
{
    protected $fillable = [
        'group_key',
        'payment_date',
        'amount',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /** @return HasMany<InternationalPayableGroupPaymentLine, $this> */
    public function lines(): HasMany
    {
        return $this->hasMany(InternationalPayableGroupPaymentLine::class);
    }
}
