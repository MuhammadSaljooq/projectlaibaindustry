<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $table = 'suppliers';

    protected $fillable = [
        'name',
        'contact_name',
        'email',
        'phone',
        'country',
        'notes',
    ];

    public function internationalPurchases(): HasMany
    {
        return $this->hasMany(InternationalPurchase::class, 'supplier_id');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(SupplierLedgerEntry::class, 'supplier_id');
    }
}
