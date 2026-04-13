<?php

use App\Support\Schema\CustomerReceivablePurchaseOffsetsSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        CustomerReceivablePurchaseOffsetsSchema::ensureTableExists();
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_receivable_purchase_offsets');
    }
};
