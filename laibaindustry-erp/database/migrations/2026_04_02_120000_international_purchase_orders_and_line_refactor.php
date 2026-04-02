<?php

use App\Support\Schema\SupplierLedgerSchema;
use App\Support\Schema\SuppliersSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        SuppliersSchema::ensureTableExists();

        if (! Schema::hasTable('international_purchases')) {
            $this->createNewInternationalPurchaseTables();

            return;
        }

        if (Schema::hasColumn('international_purchases', 'international_purchase_order_id')
            && ! Schema::hasColumn('international_purchases', 'supplier_id')) {
            return;
        }

        Schema::create('international_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->date('date');
            $table->string('invoice_number', 191)->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->timestamps();

            $table->index(['supplier_id', 'invoice_number']);
            $table->index('date');
        });

        Schema::table('international_purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('international_purchase_order_id')->nullable()->after('id');
        });

        $now = now();
        $lines = DB::table('international_purchases')->orderBy('id')->get();

        foreach ($lines as $line) {
            $orderId = DB::table('international_purchase_orders')->insertGetId([
                'supplier_id' => $line->supplier_id,
                'date' => $line->date,
                'invoice_number' => null,
                'total_amount' => $line->total_amount,
                'created_at' => $line->created_at ?? $now,
                'updated_at' => $line->updated_at ?? $now,
            ]);

            DB::table('international_purchases')->where('id', $line->id)->update([
                'international_purchase_order_id' => $orderId,
            ]);
        }

        if (Schema::hasTable('supplier_ledger_entries')) {
            foreach ($lines as $line) {
                $orderId = DB::table('international_purchases')->where('id', $line->id)->value('international_purchase_order_id');
                DB::table('supplier_ledger_entries')
                    ->where('source_type', 'international_purchase')
                    ->where('source_id', $line->id)
                    ->update([
                        'source_type' => 'international_purchase_order',
                        'source_id' => $orderId,
                        'reference' => 'IPO-'.$orderId,
                    ]);
            }
        }

        if (Schema::hasTable('international_payable_payments')) {
            Schema::table('international_payable_payments', function (Blueprint $table) {
                $table->unsignedBigInteger('international_purchase_order_id')->nullable()->after('id');
            });

            $payments = DB::table('international_payable_payments')->orderBy('id')->get();
            foreach ($payments as $payment) {
                $orderId = DB::table('international_purchases')
                    ->where('id', $payment->international_purchase_id)
                    ->value('international_purchase_order_id');
                DB::table('international_payable_payments')->where('id', $payment->id)->update([
                    'international_purchase_order_id' => $orderId,
                ]);
            }

            Schema::table('international_payable_payments', function (Blueprint $table) {
                $table->dropForeign(['international_purchase_id']);
                $table->dropColumn('international_purchase_id');
            });

            Schema::table('international_payable_payments', function (Blueprint $table) {
                $table->foreign('international_purchase_order_id')
                    ->references('id')
                    ->on('international_purchase_orders')
                    ->cascadeOnDelete();
            });
        }

        Schema::table('international_purchases', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });

        Schema::table('international_purchases', function (Blueprint $table) {
            $table->dropColumn(['supplier_id', 'date']);
        });

        Schema::table('international_purchases', function (Blueprint $table) {
            $table->foreign('international_purchase_order_id')
                ->references('id')
                ->on('international_purchase_orders')
                ->cascadeOnDelete();
        });
    }

    private function createNewInternationalPurchaseTables(): void
    {
        Schema::create('international_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->date('date');
            $table->string('invoice_number', 191)->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->timestamps();

            $table->index(['supplier_id', 'invoice_number']);
            $table->index('date');
        });

        Schema::create('international_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('international_purchase_order_id')->constrained('international_purchase_orders')->cascadeOnDelete();
            $table->string('product_name', 255);
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->timestamps();

            $table->index('product_name');
        });

        SupplierLedgerSchema::ensureTableExists();

        if (! Schema::hasTable('international_payable_payments')) {
            Schema::create('international_payable_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('international_purchase_order_id')->constrained('international_purchase_orders')->cascadeOnDelete();
                $table->date('payment_date');
                $table->decimal('amount', 10, 2);
                $table->string('notes', 500)->nullable();
                $table->timestamps();

                $table->index('payment_date');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('international_purchase_orders')) {
            return;
        }

        if (! Schema::hasTable('international_purchases')) {
            Schema::dropIfExists('international_purchase_orders');

            return;
        }

        if (Schema::hasColumn('international_purchases', 'supplier_id')) {
            return;
        }

        Schema::table('international_purchases', function (Blueprint $table) {
            $table->dropForeign(['international_purchase_order_id']);
        });

        Schema::table('international_purchases', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('id')->constrained('suppliers')->nullOnDelete();
            $table->date('date')->after('supplier_id');
        });

        $orders = DB::table('international_purchase_orders')->orderBy('id')->get();
        foreach ($orders as $order) {
            $line = DB::table('international_purchases')
                ->where('international_purchase_order_id', $order->id)
                ->orderBy('id')
                ->first();
            if ($line) {
                DB::table('international_purchases')->where('id', $line->id)->update([
                    'supplier_id' => $order->supplier_id,
                    'date' => $order->date,
                ]);
            }
        }

        if (Schema::hasTable('international_payable_payments')
            && Schema::hasColumn('international_payable_payments', 'international_purchase_order_id')) {
            Schema::table('international_payable_payments', function (Blueprint $table) {
                $table->dropForeign(['international_purchase_order_id']);
            });
            Schema::table('international_payable_payments', function (Blueprint $table) {
                $table->foreignId('international_purchase_id')->nullable()->after('id');
            });
            $payments = DB::table('international_payable_payments')->orderBy('id')->get();
            foreach ($payments as $payment) {
                $lineId = DB::table('international_purchases')
                    ->where('international_purchase_order_id', $payment->international_purchase_order_id)
                    ->orderBy('id')
                    ->value('id');
                if ($lineId) {
                    DB::table('international_payable_payments')->where('id', $payment->id)->update([
                        'international_purchase_id' => $lineId,
                    ]);
                }
            }
            Schema::table('international_payable_payments', function (Blueprint $table) {
                $table->dropColumn('international_purchase_order_id');
            });
            Schema::table('international_payable_payments', function (Blueprint $table) {
                $table->foreign('international_purchase_id')
                    ->references('id')
                    ->on('international_purchases')
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('supplier_ledger_entries')) {
            foreach ($orders as $order) {
                $lineId = DB::table('international_purchases')
                    ->where('international_purchase_order_id', $order->id)
                    ->orderBy('id')
                    ->value('id');
                if ($lineId) {
                    DB::table('supplier_ledger_entries')
                        ->where('source_type', 'international_purchase_order')
                        ->where('source_id', $order->id)
                        ->update([
                            'source_type' => 'international_purchase',
                            'source_id' => $lineId,
                            'reference' => 'IP-'.$lineId,
                        ]);
                }
            }
        }

        Schema::table('international_purchases', function (Blueprint $table) {
            $table->dropColumn('international_purchase_order_id');
        });

        Schema::dropIfExists('international_purchase_orders');
    }
};
