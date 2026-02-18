<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('name');
        });

        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->string('symbol', 10);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('decimal_places')->default(2);
            $table->timestamps();

            $table->index('code');
        });

        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->foreignId('to_currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->decimal('rate', 10, 6);
            $table->dateTime('effective_date')->useCurrent();
            $table->timestamps();

            $table->unique(['from_currency_id', 'to_currency_id', 'effective_date']);
            $table->index('from_currency_id');
            $table->index('to_currency_id');
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku', 100)->unique();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->decimal('cost_price', 10, 2);
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->text('description')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('reorder_level')->default(10);
            $table->timestamps();

            $table->index('name');
            $table->index('sku');
            $table->index('category_id');
            $table->index('stock_quantity');
        });

        Schema::create('tax_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('default_rate', 5, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date')->useCurrent();
            $table->string('customer_code', 100)->nullable();
            $table->string('customer_name')->nullable();
            $table->string('invoice_number', 100)->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('tax_rate', 5, 2);
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->decimal('exchange_rate', 10, 6)->nullable();
            $table->string('status', 50)->default('completed');
            $table->timestamps();

            $table->index('date');
            $table->index('customer_code');
            $table->index('customer_name');
            $table->index('invoice_number');
            $table->index(['date', 'customer_code']);
        });

        Schema::create('sales_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->integer('quantity');
            $table->decimal('cost_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->decimal('profit', 10, 2);
            $table->decimal('tax_applied', 10, 2)->default(0);

            $table->index('sale_id');
            $table->index('product_id');
        });

        Schema::create('receivables', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date')->useCurrent();
            $table->string('invoice_number', 100)->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_code', 100)->nullable();
            $table->decimal('amount', 10, 2);
            $table->decimal('received', 10, 2)->default(0);
            $table->timestamps();

            $table->index('date');
            $table->index('invoice_number');
            $table->index('customer_name');
            $table->index('customer_code');
        });

        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date')->useCurrent();
            $table->string('customer_code', 100)->nullable();
            $table->string('customer_name')->nullable();
            $table->string('invoice_number', 100)->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->timestamps();

            $table->index('date');
            $table->index('customer_code');
            $table->index('invoice_number');
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->string('product_name')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('quantity')->default(0);
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);

            $table->index('purchase_id');
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code', 100)->unique();
            $table->string('customer_name');
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();

            $table->index('customer_code');
            $table->index('customer_name');
        });

        Schema::create('payables', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date')->useCurrent();
            $table->string('invoice_number', 100)->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_code', 100)->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->date('received_date')->nullable();
            $table->date('remaining_date')->nullable();
            $table->timestamps();

            $table->index('date');
            $table->index('invoice_number');
            $table->index('customer_name');
            $table->index('customer_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payables');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('receivables');
        Schema::dropIfExists('sales_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('tax_settings');
        Schema::dropIfExists('products');
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('categories');
    }
};
