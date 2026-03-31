<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternationalPayablePayment extends Model
{
    protected $table = 'international_payable_payments';

    protected $fillable = [
        'international_purchase_id',
        'payment_date',
        'amount',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function internationalPurchase(): BelongsTo
    {
        return $this->belongsTo(InternationalPurchase::class);
    }
}
