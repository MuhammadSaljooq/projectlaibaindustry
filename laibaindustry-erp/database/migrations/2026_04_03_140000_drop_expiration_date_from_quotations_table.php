<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('quotations')) {
            return;
        }
        if (! Schema::hasColumn('quotations', 'expiration_date')) {
            return;
        }
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('expiration_date');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->date('expiration_date')->nullable()->after('quotation_date');
        });
    }
};
