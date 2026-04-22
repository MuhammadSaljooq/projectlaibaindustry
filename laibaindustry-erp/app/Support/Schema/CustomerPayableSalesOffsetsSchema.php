<?php

namespace App\Support\Schema;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CustomerPayableSalesOffsetsSchema
{
    public static function ensureTableExists(): void
    {
        if (Schema::hasTable('customer_payable_sales_offsets')) {
            return;
        }

        Schema::create('customer_payable_sales_offsets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('payable_id')->constrained('payables')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->dateTime('offset_date');
            $table->timestamps();

            $table->unique(['sale_id', 'payable_id']);
            $table->index(['customer_id', 'offset_date']);
            $table->index('payable_id');
            $table->index('sale_id');
        });
    }
}
