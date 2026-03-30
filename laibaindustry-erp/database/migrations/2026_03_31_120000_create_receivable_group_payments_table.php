<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receivable_group_payments', function (Blueprint $table) {
            $table->id();
            $table->string('group_key', 512);
            $table->date('payment_date');
            $table->decimal('amount', 10, 2);
            $table->timestamps();

            $table->index('group_key');
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receivable_group_payments');
    }
};
