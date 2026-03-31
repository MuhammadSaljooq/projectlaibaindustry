<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_ledger_receivable_group_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_ledger_entry_id');
            $table->unsignedBigInteger('receivable_group_payment_id');
            $table->timestamps();

            $table->unique('customer_ledger_entry_id', 'cl_rgp_ledger_unique');
            $table->index('receivable_group_payment_id', 'cl_rgp_group_idx');

            $table->foreign('customer_ledger_entry_id', 'cl_rgp_ledger_fk')
                ->references('id')
                ->on('customer_ledger_entries')
                ->cascadeOnDelete();

            $table->foreign('receivable_group_payment_id', 'cl_rgp_group_fk')
                ->references('id')
                ->on('receivable_group_payments')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_ledger_receivable_group_payments');
    }
};
