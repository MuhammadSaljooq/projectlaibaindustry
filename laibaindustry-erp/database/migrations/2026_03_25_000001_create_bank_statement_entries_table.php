<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_statement_entries', function (Blueprint $table) {
            $table->id();
            $table->string('flow_type', 16);
            $table->date('transaction_date');
            $table->string('company_name', 255);
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->index(['flow_type', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_statement_entries');
    }
};
