<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vat_entries')) {
            return;
        }

        Schema::create('vat_entries', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->string('source_type');
            $table->unsignedBigInteger('source_id');
            $table->dateTime('date')->useCurrent();
            $table->string('invoice_number', 100)->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_code', 100)->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(0);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamps();

            $table->index('type');
            $table->index('date');
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vat_entries');
    }
};
