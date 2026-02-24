<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_ledger_entries', function (Blueprint $table) {
            $table->foreignId('payable_id')->nullable()->after('receivable_id')->constrained('payables')->nullOnDelete();
        });

        $this->backfillPayables();
    }

    protected function backfillPayables(): void
    {
        $payables = DB::table('payables')->orderBy('id')->get();

        foreach ($payables as $p) {
            $customerCode = $p->customer_code ?? '';
            $customerName = $p->customer_name ?? null;
            $amount = (float) $p->amount;
            $received = (float) ($p->received ?? 0);

            DB::table('customer_ledger_entries')->insert([
                'customer_code' => $customerCode,
                'customer_name' => $customerName,
                'entry_date' => $p->date,
                'type' => 'payable',
                'reference' => $p->invoice_number ?: 'Bill',
                'debit' => 0,
                'credit' => $amount,
                'payment_type' => null,
                'receivable_id' => null,
                'payable_id' => $p->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($received > 0) {
                $paymentDate = $p->received_date ?? $p->updated_at ?? $p->date;
                DB::table('customer_ledger_entries')->insert([
                    'customer_code' => $customerCode,
                    'customer_name' => $customerName,
                    'entry_date' => $paymentDate,
                    'type' => 'payable_payment',
                    'reference' => $p->invoice_number ?: 'Payment',
                    'debit' => $received,
                    'credit' => 0,
                    'payment_type' => 'Historical',
                    'receivable_id' => null,
                    'payable_id' => $p->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('customer_ledger_entries', function (Blueprint $table) {
            $table->dropForeign(['payable_id']);
        });
    }
};
