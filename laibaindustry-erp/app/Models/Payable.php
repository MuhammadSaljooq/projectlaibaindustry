<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payable extends Model
{
    protected $fillable = [
        'date',
        'invoice_number',
        'customer_name',
        'customer_code',
        'amount',
        'received_date',
        'remaining_date',
    ];

    protected $casts = [
        'date' => 'datetime',
        'received_date' => 'date',
        'remaining_date' => 'date',
    ];
}
