<?php

namespace App\Support\Schema;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Standalone DDL: database/schema/mysql/international_purchases.sql
 * Existing MySQL DBs may use international_purchases_add_supplier_id.sql instead.
 */
final class InternationalPurchasesSchema
{
    public static function ensureTableExists(): void
    {
        SuppliersSchema::ensureTableExists();

        if (! Schema::hasTable('international_purchases')) {
            Schema::create('international_purchases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
                $table->date('date');
                $table->string('product_name', 255);
                $table->unsignedInteger('quantity');
                $table->decimal('unit_price', 10, 2);
                $table->decimal('total_amount', 10, 2);
                $table->timestamps();

                $table->index('date');
                $table->index('product_name');
            });

            return;
        }

        if (! Schema::hasColumn('international_purchases', 'supplier_id')) {
            Schema::table('international_purchases', function (Blueprint $table) {
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            });
        }
    }
}
