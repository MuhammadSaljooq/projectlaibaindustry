<?php

namespace App\Support\Schema;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Standalone DDL lives in database/schema/mysql/international_purchases.sql for production MySQL.
 * This mirrors that structure for SQLite/local when the table is missing (no Laravel migration).
 */
final class InternationalPurchasesSchema
{
    public static function ensureTableExists(): void
    {
        if (Schema::hasTable('international_purchases')) {
            return;
        }

        Schema::create('international_purchases', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('product_name', 255);
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->timestamps();

            $table->index('date');
            $table->index('product_name');
        });
    }
}
