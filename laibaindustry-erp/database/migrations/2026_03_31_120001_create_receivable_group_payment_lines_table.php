<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receivable_group_payment_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receivable_group_payment_id');
            $table->unsignedBigInteger('receivable_id');
            $table->decimal('amount', 10, 2);
            $table->unsignedInteger('customer_ledger_entry_id')->nullable();
            $table->timestamps();

            // Only FK to receivable_group_payments: guaranteed same engine/type as this migration.
            // No DB FK to receivables / customer_ledger_entries — live DBs often use INT id vs BIGINT,
            // MyISAM, or other drift; errno 150 otherwise. Indexes keep lookups fast; app enforces links.
            $table->foreign('receivable_group_payment_id', 'rgpl_ar_group_pay_fk')
                ->references('id')
                ->on('receivable_group_payments')
                ->cascadeOnDelete();

            $table->index('receivable_id', 'rgpl_receivable_id_idx');
            $table->index('customer_ledger_entry_id', 'rgpl_cust_ledger_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receivable_group_payment_lines');
    }
};
