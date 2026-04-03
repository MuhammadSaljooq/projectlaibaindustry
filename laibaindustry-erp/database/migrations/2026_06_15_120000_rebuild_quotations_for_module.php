<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replaces legacy quotation tables with the quotation_module schema.
     * Destructive: all existing quotation data is dropped.
     */
    public function up(): void
    {
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');

        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number')->unique();
            $table->date('quotation_date');
            $table->date('expiration_date')->nullable();
            $table->string('salesperson')->nullable();
            $table->string('customer_name');
            $table->string('customer_vat_number')->nullable();
            $table->string('customer_cr_number')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->text('customer_address')->nullable();
            $table->decimal('untaxed_amount', 12, 2)->default(0);
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->text('description');
            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(15.00);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['quotation_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');

        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number', 40)->unique();
            $table->date('quotation_date');
            $table->string('salesperson', 191)->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('receiver_name')->nullable();
            $table->text('receiver_address')->nullable();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->string('amount_in_words', 512)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('quotation_date');
            $table->index('customer_id');
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->text('description');
            $table->decimal('quantity', 12, 4)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('untaxed_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->index(['quotation_id', 'sort_order']);
        });
    }
};
