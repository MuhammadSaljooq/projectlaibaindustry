<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payables') || Schema::hasColumn('payables', 'purchase_id')) {
            return;
        }

        Schema::table('payables', function (Blueprint $table) {
            // Nullable so existing orphaned rows don't break; SET NULL on purchase delete
            // so the payable stays visible even after a purchase is force-deleted externally.
            $table->foreignId('purchase_id')
                ->nullable()
                ->after('id')
                ->constrained('purchases')
                ->nullOnDelete();

            $table->index('purchase_id');
        });
    }

    public function down(): void
    {
        Schema::table('payables', function (Blueprint $table) {
            $table->dropForeign(['purchase_id']);
            $table->dropIndex(['purchase_id']);
            $table->dropColumn('purchase_id');
        });
    }
};
