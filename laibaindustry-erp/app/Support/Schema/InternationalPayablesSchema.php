<?php

namespace App\Support\Schema;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Standalone DDL: database/schema/mysql/international_payable_payments.sql
 */
final class InternationalPayablesSchema
{
    public static function ensureTableExists(): void
    {
        InternationalPurchasesSchema::ensureTableExists();

        if (! Schema::hasTable('international_payable_payments')) {
            Schema::create('international_payable_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('international_purchase_id')
                    ->constrained('international_purchases')
                    ->cascadeOnDelete();
                $table->date('payment_date');
                $table->decimal('amount', 10, 2);
                $table->string('notes', 500)->nullable();
                $table->timestamps();

                $table->index('payment_date');
            });
        }

        SupplierLedgerSchema::ensureTableExists();
    }
}
