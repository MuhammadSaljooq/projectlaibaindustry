<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_ledger_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('receivable_group_payment_id')->nullable()->after('source_id');
            $table->foreign('receivable_group_payment_id')
                ->references('id')
                ->on('receivable_group_payments')
                ->nullOnDelete();
            $table->index('receivable_group_payment_id');
        });
    }

    public function down(): void
    {
        Schema::table('customer_ledger_entries', function (Blueprint $table) {
            $table->dropForeign(['receivable_group_payment_id']);
            $table->dropColumn('receivable_group_payment_id');
        });
    }
};
