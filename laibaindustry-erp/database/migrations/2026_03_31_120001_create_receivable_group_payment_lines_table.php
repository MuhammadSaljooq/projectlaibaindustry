<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receivable_group_payment_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receivable_group_payment_id')
                ->constrained('receivable_group_payments')
                ->cascadeOnDelete();
            $table->foreignId('receivable_id')
                ->constrained('receivables')
                ->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->unsignedInteger('customer_ledger_entry_id')->nullable();
            $table->timestamps();

            $table->foreign('customer_ledger_entry_id')
                ->references('id')
                ->on('customer_ledger_entries')
                ->nullOnDelete();

            $table->index('receivable_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receivable_group_payment_lines');
    }
};
