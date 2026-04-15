<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('international_payable_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('international_payable_payments', 'international_payable_group_payment_id')) {
                $table->unsignedBigInteger('international_payable_group_payment_id')->nullable()->after('international_purchase_order_id');
                $table->foreign('international_payable_group_payment_id', 'int_pay_pay_group_fk')
                    ->references('id')
                    ->on('international_payable_group_payments')
                    ->nullOnDelete();
                $table->index('international_payable_group_payment_id', 'int_pay_pay_group_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('international_payable_payments', function (Blueprint $table) {
            if (Schema::hasColumn('international_payable_payments', 'international_payable_group_payment_id')) {
                $table->dropForeign('int_pay_pay_group_fk');
                $table->dropIndex('int_pay_pay_group_idx');
                $table->dropColumn('international_payable_group_payment_id');
            }
        });
    }
};
