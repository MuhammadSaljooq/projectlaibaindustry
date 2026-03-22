<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payables') || Schema::hasColumn('payables', 'received')) {
            return;
        }

        Schema::table('payables', function (Blueprint $table) {
            $table->decimal('received', 10, 2)->default(0)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('payables', function (Blueprint $table) {
            $table->dropColumn('received');
        });
    }
};
