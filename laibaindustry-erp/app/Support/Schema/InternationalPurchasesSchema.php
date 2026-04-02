<?php

namespace App\Support\Schema;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Standalone DDL: database/schema/mysql/international_purchase_orders.sql,
 * international_purchases.sql (line items).
 */
final class InternationalPurchasesSchema
{
    public static function ensureTableExists(): void
    {
        SuppliersSchema::ensureTableExists();

        if (! Schema::hasTable('international_purchase_orders')) {
            Schema::create('international_purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
                $table->date('date');
                $table->string('invoice_number', 191)->nullable();
                $table->decimal('total_amount', 10, 2);
                $table->timestamps();

                $table->index(['supplier_id', 'invoice_number']);
                $table->index('date');
            });
        }

        if (! Schema::hasTable('international_purchases')) {
            Schema::create('international_purchases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('international_purchase_order_id')->constrained('international_purchase_orders')->cascadeOnDelete();
                $table->string('product_name', 255);
                $table->unsignedInteger('quantity');
                $table->decimal('unit_price', 10, 2);
                $table->decimal('total_amount', 10, 2);
                $table->timestamps();

                $table->index('product_name');
            });
        }
    }
}
