<?php

namespace App\Support\Schema;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CustomerReceivablePurchaseOffsetsSchema
{
    public static function ensureTableExists(): void
    {
        if (Schema::hasTable('customer_receivable_purchase_offsets')) {
            return;
        }

        Schema::create('customer_receivable_purchase_offsets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('receivable_id')->constrained('receivables')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->dateTime('offset_date');
            $table->timestamps();

            $table->unique(['purchase_id', 'receivable_id']);
            $table->index(['customer_id', 'offset_date']);
            $table->index('receivable_id');
        });
    }
}
