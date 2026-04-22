<?php

use App\Support\Schema\CustomerPayableSalesOffsetsSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        CustomerPayableSalesOffsetsSchema::ensureTableExists();
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_payable_sales_offsets');
    }
};
