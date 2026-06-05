<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    public const CATEGORY_PERSONAL = 'personal';

    public const CATEGORY_TRANSPORT = 'transport';

    public const CATEGORY_CONTAINER = 'container';

    protected $fillable = [
        'date',
        'category',
        'description',
        'amount',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public static function categories(): array
    {
        return [
            self::CATEGORY_PERSONAL,
            self::CATEGORY_TRANSPORT,
            self::CATEGORY_CONTAINER,
        ];
    }

    public static function categoryLabels(): array
    {
        return [
            self::CATEGORY_PERSONAL => 'Personal expenses',
            self::CATEGORY_TRANSPORT => 'Warehouse/Transport Expenses',
            self::CATEGORY_CONTAINER => 'Container expenses',
        ];
    }

    public function categoryLabel(): string
    {
        return self::categoryLabels()[$this->category] ?? $this->category;
    }
}
