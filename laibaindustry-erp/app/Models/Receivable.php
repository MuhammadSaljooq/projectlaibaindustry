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
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Set amount from sum of linked sale totals.
     */
    public function recalculateAmountFromSales(): void
    {
        $sum = (float) $this->sales()->sum('total_amount');
        $this->update(['amount' => round($sum, 2)]);
    }

    /**
     * Refresh date, customer display fields, and invoice hint from linked sales.
     */
    public function syncDisplayFromLinkedSales(): void
    {
        $count = $this->sales()->count();
        $latest = $this->sales()->orderByDesc('date')->orderByDesc('id')->first();

        if (! $latest) {
            return;
        }

        $this->update([
            'date'           => $latest->date,
            'customer_name'  => $latest->customer_name,
            'customer_code'  => $latest->customer_code,
            'invoice_number' => $count > 1 ? null : $latest->invoice_number,
        ]);
    }

    /**
     * True when aggregate bill is at least payments received.
     */
    public function hasValidBalance(): bool
    {
        return (float) $this->amount >= (float) $this->received;
    }
}
