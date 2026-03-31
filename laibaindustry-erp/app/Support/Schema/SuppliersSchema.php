<?php

namespace App\Support\Schema;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Standalone DDL: database/schema/mysql/suppliers.sql
 */
final class SuppliersSchema
{
    public static function ensureTableExists(): void
    {
        if (Schema::hasTable('suppliers')) {
            return;
        }

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('contact_name', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone', 64)->nullable();
            $table->string('country', 128)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('name');
        });
    }
}
