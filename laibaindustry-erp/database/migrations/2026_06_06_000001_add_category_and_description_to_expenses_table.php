<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('category')->nullable()->after('date');
            $table->string('description')->nullable()->after('category');
        });

        DB::table('expenses')->update([
            'description' => DB::raw('type'),
            'category' => 'personal',
        ]);

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn('type');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('type')->nullable()->after('date');
        });

        DB::table('expenses')->update([
            'type' => DB::raw('description'),
        ]);

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn(['category', 'description']);
            $table->index('type');
        });
    }
};
