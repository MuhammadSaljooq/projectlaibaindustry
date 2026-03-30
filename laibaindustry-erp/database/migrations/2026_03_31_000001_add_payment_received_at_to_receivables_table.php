<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receivables', function (Blueprint $table) {
            $table->dateTime('payment_received_at')->nullable()->after('received');
        });

        if (Schema::hasTable('customer_ledger_entries')) {
            DB::statement("
                UPDATE receivables
                SET payment_received_at = (
                    SELECT MAX(cle.date) FROM customer_ledger_entries AS cle
                    WHERE cle.source_type = 'payment_received' AND cle.source_id = receivables.id
                )
                WHERE received > 0
            ");
        }
    }

    public function down(): void
    {
        Schema::table('receivables', function (Blueprint $table) {
            $table->dropColumn('payment_received_at');
        });
    }
};
