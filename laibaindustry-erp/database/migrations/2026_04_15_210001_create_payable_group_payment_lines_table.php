<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payable_group_payment_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payable_group_payment_id');
            $table->unsignedBigInteger('payable_id');
            $table->decimal('amount', 10, 2);
            $table->unsignedInteger('customer_ledger_entry_id')->nullable();
            $table->timestamps();

            $table->foreign('payable_group_payment_id', 'pgpl_ap_group_pay_fk')
                ->references('id')
                ->on('payable_group_payments')
                ->cascadeOnDelete();

            $table->index('payable_id', 'pgpl_payable_id_idx');
            $table->index('customer_ledger_entry_id', 'pgpl_cust_ledger_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payable_group_payment_lines');
    }
};
