<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quotation_number',
        'quotation_date',
        'expiration_date',
        'salesperson',
        'customer_name',
        'customer_vat_number',
        'customer_cr_number',
        'customer_phone',
        'customer_email',
        'customer_address',
        'untaxed_amount',
        'vat_amount',
        'total_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'expiration_date' => 'date',
        'untaxed_amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    public function recalculateTotals(): void
    {
        $this->load('items');
        $untaxed = $this->items->sum(fn ($i) => (float) $i->quantity * (float) $i->unit_price);
        $vat = $this->items->sum('tax_amount');

        $this->update([
            'untaxed_amount' => round($untaxed, 2),
            'vat_amount' => round((float) $vat, 2),
            'total_amount' => round($untaxed + (float) $vat, 2),
        ]);
    }

    public function totalInWords(): string
    {
        $amount = number_format((float) $this->total_amount, 2);
        [$riyals, $halalas] = explode('.', $amount);

        $words = $this->numberToWords((int) $riyals).' Saudi Riyals';
        if ((int) $halalas > 0) {
            $words .= ' and '.$this->numberToWords((int) $halalas).' Halalas';
        }

        return $words.' Only';
    }

    private function numberToWords(int $n): string
    {
        if ($n === 0) {
            return 'Zero';
        }

        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
            'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        if ($n < 20) {
            return $ones[$n];
        }
        if ($n < 100) {
            return $tens[intdiv($n, 10)].($n % 10 ? ' '.$ones[$n % 10] : '');
        }
        if ($n < 1000) {
            return $ones[intdiv($n, 100)].' Hundred'.($n % 100 ? ' '.$this->numberToWords($n % 100) : '');
        }
        if ($n < 1_000_000) {
            return $this->numberToWords(intdiv($n, 1000)).' Thousand'.($n % 1000 ? ' '.$this->numberToWords($n % 1000) : '');
        }

        return $this->numberToWords(intdiv($n, 1_000_000)).' Million'.($n % 1_000_000 ? ' '.$this->numberToWords($n % 1_000_000) : '');
    }
}
