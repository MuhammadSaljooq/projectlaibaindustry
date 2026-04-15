<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('international_payable_group_payment_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('international_payable_group_payment_id');
            $table->unsignedBigInteger('international_purchase_order_id');
            $table->decimal('amount', 10, 2);
            $table->unsignedBigInteger('international_payable_payment_id')->nullable();
            $table->timestamps();

            $table->foreign('international_payable_group_payment_id', 'ipgpl_group_fk')
                ->references('id')
                ->on('international_payable_group_payments')
                ->cascadeOnDelete();

            $table->index('international_purchase_order_id', 'ipgpl_order_idx');
            $table->index('international_payable_payment_id', 'ipgpl_payment_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('international_payable_group_payment_lines');
    }
};
