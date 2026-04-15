<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_ledger_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('customer_ledger_entries', 'payable_group_payment_id')) {
                $table->unsignedBigInteger('payable_group_payment_id')->nullable()->after('receivable_group_payment_id');
                $table->foreign('payable_group_payment_id', 'cle_ap_group_pay_fk')
                    ->references('id')
                    ->on('payable_group_payments')
                    ->nullOnDelete();
                $table->index('payable_group_payment_id', 'cle_ap_group_pay_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_ledger_entries', function (Blueprint $table) {
            if (Schema::hasColumn('customer_ledger_entries', 'payable_group_payment_id')) {
                $table->dropForeign('cle_ap_group_pay_fk');
                $table->dropIndex('cle_ap_group_pay_idx');
                $table->dropColumn('payable_group_payment_id');
            }
        });
    }
};
