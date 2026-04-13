<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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
        'is_opening_balance',
    ];

    protected $casts = [
        'date' => 'datetime',
        'payment_received_at' => 'datetime',
        'is_opening_balance' => 'boolean',
    ];

    /** @return HasMany<CustomerLedgerEntry, $this> */
    public function paymentLedgerEntries(): HasMany
    {
        return $this->hasMany(CustomerLedgerEntry::class, 'source_id')
            ->where('source_type', 'payment_received')
            ->orderBy('date')
            ->orderBy('id');
    }

    /** @return HasMany<CustomerReceivablePurchaseOffset, $this> */
    public function purchaseOffsets(): HasMany
    {
        return $this->hasMany(CustomerReceivablePurchaseOffset::class);
    }

    /**
     * Canonical AR group key (must match {@see static::groupKeySqlExpression()}).
     */
    public static function canonicalGroupKey(?string $customerCode, ?string $customerName, int $id): string
    {
        $code = trim((string) $customerCode);
        if ($code !== '') {
            return 'code:'.$code;
        }

        $name = trim((string) $customerName);
        if ($name !== '') {
            return 'name:'.mb_strtolower($name, 'UTF-8');
        }

        return 'id:'.$id;
    }

    public function groupKey(): string
    {
        return static::canonicalGroupKey($this->customer_code, $this->customer_name, (int) $this->id);
    }

    /**
     * SQL expression (single table `receivables`) producing the same string as {@see canonicalGroupKey()}.
     */
    public static function groupKeySqlExpression(): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return trim(<<<'SQL'
(CASE
    WHEN TRIM(COALESCE(customer_code, '')) != '' THEN ('code:' || TRIM(customer_code))
    WHEN TRIM(COALESCE(customer_name, '')) != '' THEN ('name:' || LOWER(TRIM(customer_name)))
    ELSE ('id:' || CAST(id AS TEXT))
END)
SQL);
        }

        return trim(<<<'SQL'
(CASE
    WHEN TRIM(COALESCE(customer_code, '')) != '' THEN CONCAT('code:', TRIM(customer_code))
    WHEN TRIM(COALESCE(customer_name, '')) != '' THEN CONCAT('name:', LOWER(TRIM(customer_name)))
    ELSE CONCAT('id:', id)
END)
SQL);
    }

    public static function encodeGroupKeyForRoute(string $key): string
    {
        return rtrim(strtr(base64_encode($key), '+/', '-_'), '=');
    }

    public static function decodeGroupKeyFromRoute(string $encoded): ?string
    {
        $b64 = strtr($encoded, '-_', '+/');
        $pad = strlen($b64) % 4;
        if ($pad > 0) {
            $b64 .= str_repeat('=', 4 - $pad);
        }

        $raw = base64_decode($b64, true);

        return $raw === false ? null : $raw;
    }
}
