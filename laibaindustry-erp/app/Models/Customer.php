<?php

namespace App\Models;

use App\Services\PurchaseReceivableOffsetService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

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
        'opening_balance' => 'decimal:2',
        'opening_balance_date' => 'date',
    ];

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(CustomerLedgerEntry::class)->orderBy('date')->orderBy('id');
    }

    /**
     * Create or update the single synthetic receivable for this customer's opening balance
     * so they appear on the Receivables page even with no sales.
     */
    public function syncOpeningBalanceReceivable(): void
    {
        if (! Schema::hasColumn('customers', 'opening_balance')
            || ! Schema::hasColumn('receivables', 'is_opening_balance')) {
            return;
        }

        $code = trim((string) $this->customer_code);
        if ($code === '') {
            return;
        }

        $opening = (float) ($this->opening_balance ?? 0);
        $offsetService = app(PurchaseReceivableOffsetService::class);

        $receivable = Receivable::query()
            ->where('customer_code', $code)
            ->where('is_opening_balance', true)
            ->first();

        if ($opening <= 0) {
            if ($receivable) {
                $offsetService->syncReceivable($receivable);
                $receivable->refresh();
                if ((float) $receivable->received <= 0.00001) {
                    $receivable->delete();
                } else {
                    $receivable->update([
                        'amount' => (float) $receivable->received,
                        'customer_name' => $this->customer_name,
                    ]);
                    $offsetService->syncReceivable($receivable->fresh());
                }
            }

            return;
        }

        $at = $this->opening_balance_date
            ? Carbon::parse($this->opening_balance_date)->startOfDay()
            : now()->startOfDay();

        if (! $receivable) {
            Receivable::create([
                'date' => $at,
                'invoice_number' => 'Opening balance',
                'customer_name' => $this->customer_name,
                'customer_code' => $code,
                'amount' => $opening,
                'received' => 0,
                'is_opening_balance' => true,
            ]);

            return;
        }

        $receivable->update([
            'date' => $at,
            'invoice_number' => 'Opening balance',
            'customer_name' => $this->customer_name,
            'customer_code' => $code,
            'amount' => $opening,
        ]);
        $offsetService->syncReceivable($receivable->fresh());
    }
}
