<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
