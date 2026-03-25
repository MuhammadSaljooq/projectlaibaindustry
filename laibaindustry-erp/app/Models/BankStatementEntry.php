<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankStatementEntry extends Model
{
    public const FLOW_INFLOW = 'inflow';

    public const FLOW_OUTFLOW = 'outflow';

    protected $fillable = [
        'flow_type',
        'transaction_date',
        'company_name',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public static function flowTypes(): array
    {
        return [self::FLOW_INFLOW, self::FLOW_OUTFLOW];
    }
}
