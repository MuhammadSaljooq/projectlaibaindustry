<?php

namespace App\Support\Schema;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Standalone DDL: database/schema/mysql/supplier_ledger_entries.sql
 */
final class SupplierLedgerSchema
{
    public static function ensureTableExists(): void
    {
        SuppliersSchema::ensureTableExists();

        if (Schema::hasTable('supplier_ledger_entries')) {
            return;
        }

        Schema::create('supplier_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->dateTime('date');
            $table->string('description', 255);
            $table->string('reference', 100)->nullable();
            $table->decimal('debit', 10, 2)->default(0);
            $table->decimal('credit', 10, 2)->default(0);
            $table->string('source_type', 40)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('supplier_id');
            $table->index('date');
            $table->index(['source_type', 'source_id']);
        });
    }
}
