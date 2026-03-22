<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customer_ledger_entries')) {
            return;
        }

        Schema::create('customer_ledger_entries', function (Blueprint $table) {
            $table->increments('id');
            // unsignedInteger matches customers.id which is INT AUTO_INCREMENT
            $table->unsignedInteger('customer_id');
            $table->dateTime('date');
            $table->string('description');
            $table->string('reference', 100)->nullable();
            $table->decimal('debit', 10, 2)->default(0);
            $table->decimal('credit', 10, 2)->default(0);
            $table->string('source_type', 30)->nullable();
            $table->unsignedInteger('source_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->cascadeOnDelete();

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
