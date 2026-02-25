<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->dateTime('date');
            $table->string('description');
            $table->string('reference', 100)->nullable();
            $table->decimal('debit', 10, 2)->default(0);
            $table->decimal('credit', 10, 2)->default(0);
            $table->string('source_type', 30)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('customer_id');
            $table->index('date');
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_ledger_entries');
    }
};
