<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('receivables', 'is_opening_balance')) {
            return;
        }

        Schema::table('receivables', function (Blueprint $table) {
            $table->boolean('is_opening_balance')->default(false);
            $table->index(['customer_code', 'is_opening_balance']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('receivables', 'is_opening_balance')) {
            return;
        }

        Schema::table('receivables', function (Blueprint $table) {
            $table->dropIndex(['customer_code', 'is_opening_balance']);
            $table->dropColumn('is_opening_balance');
        });
    }
};
