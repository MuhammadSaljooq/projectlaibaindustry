<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_ledger_payable_group_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_ledger_entry_id');
            $table->unsignedBigInteger('payable_group_payment_id');
            $table->timestamps();

            $table->unique(['customer_ledger_entry_id', 'payable_group_payment_id'], 'cle_pg_unique');
            $table->index('payable_group_payment_id', 'cle_pg_group_idx');
            $table->index('customer_ledger_entry_id', 'cle_pg_ledger_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_ledger_payable_group_payments');
    }
};
