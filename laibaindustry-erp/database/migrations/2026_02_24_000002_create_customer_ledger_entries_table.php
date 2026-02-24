<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code', 100)->index();
            $table->string('customer_name')->nullable();
            $table->dateTime('entry_date');
            $table->string('type', 20); // invoice, payment, adjustment
            $table->string('reference', 100)->nullable();
            $table->decimal('debit', 10, 2)->default(0);
            $table->decimal('credit', 10, 2)->default(0);
            $table->string('payment_type', 50)->nullable();
            $table->foreignId('receivable_id')->nullable()->constrained('receivables')->nullOnDelete();
            $table->timestamps();

            $table->index('entry_date');
            $table->index(['customer_code', 'entry_date']);
        });

        $this->backfillFromReceivables();
    }

    protected function backfillFromReceivables(): void
    {
        $receivables = DB::table('receivables')->orderBy('id')->get();

        foreach ($receivables as $r) {
            DB::table('customer_ledger_entries')->insert([
                'customer_code' => $r->customer_code ?? '',
                'customer_name' => $r->customer_name,
                'entry_date' => $r->date,
                'type' => 'invoice',
                'reference' => $r->invoice_number,
                'debit' => $r->amount,
                'credit' => 0,
                'payment_type' => null,
                'receivable_id' => $r->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $received = (float) ($r->received ?? 0);
            if ($received > 0) {
                $paymentDate = $r->updated_at ?? $r->date;
                DB::table('customer_ledger_entries')->insert([
                    'customer_code' => $r->customer_code ?? '',
                    'customer_name' => $r->customer_name,
                    'entry_date' => $paymentDate,
                    'type' => 'payment',
                    'reference' => $r->invoice_number ?: 'Payment',
                    'debit' => 0,
                    'credit' => $received,
                    'payment_type' => 'Historical',
                    'receivable_id' => $r->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_ledger_entries');
    }
};
