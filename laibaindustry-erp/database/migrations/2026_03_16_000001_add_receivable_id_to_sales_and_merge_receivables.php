<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('sales', 'receivable_id')) {
            return;
        }

        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('receivable_id')->nullable()->after('status');
            $table->index('receivable_id');
        });

        $this->backfillReceivableLinks();
        $this->mergeReceivablesByCustomerCode();
        $this->recalculateAllReceivableAmounts();
        $this->createReceivablesForOrphanSales();
        $this->refreshReceivableDisplayColumns();

        Schema::table('sales', function (Blueprint $table) {
            $table->foreign('receivable_id')
                ->references('id')
                ->on('receivables')
                ->restrictOnDelete();
        });
    }

    private function backfillReceivableLinks(): void
    {
        $sales = DB::table('sales')->orderBy('id')->get();

        foreach ($sales as $sale) {
            $rec = DB::table('receivables')
                ->where('invoice_number', $sale->invoice_number)
                ->whereRaw('ABS(amount - ?) < 0.02', [$sale->total_amount])
                ->orderBy('id')
                ->first();

            if (! $rec && $sale->invoice_number) {
                $rec = DB::table('receivables')
                    ->where('invoice_number', $sale->invoice_number)
                    ->orderBy('id')
                    ->first();
            }

            if ($rec) {
                DB::table('sales')->where('id', $sale->id)->update(['receivable_id' => $rec->id]);
            }
        }
    }

    private function mergeReceivablesByCustomerCode(): void
    {
        $codes = DB::table('sales')
            ->whereNotNull('customer_code')
            ->where('customer_code', '!=', '')
            ->distinct()
            ->pluck('customer_code');

        foreach ($codes as $code) {
            $recIds = DB::table('sales')
                ->where('customer_code', $code)
                ->whereNotNull('receivable_id')
                ->distinct()
                ->pluck('receivable_id')
                ->filter()
                ->unique()
                ->values();

            if ($recIds->count() <= 1) {
                continue;
            }

            $canonical = (int) $recIds->min();
            $others = $recIds->filter(fn ($id) => (int) $id !== $canonical)->all();

            $totalReceived = (float) DB::table('receivables')->whereIn('id', $recIds)->sum('received');
            $totalAmount = (float) DB::table('sales')->where('customer_code', $code)->sum('total_amount');
            $latestSale = DB::table('sales')
                ->where('customer_code', $code)
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->first();

            DB::table('receivables')->where('id', $canonical)->update([
                'amount'         => round($totalAmount, 2),
                'received'       => round($totalReceived, 2),
                'customer_code'  => $code,
                'customer_name'  => $latestSale->customer_name ?? null,
                'date'           => $latestSale->date ?? now(),
                'invoice_number' => null,
            ]);

            DB::table('sales')->where('customer_code', $code)->update(['receivable_id' => $canonical]);

            if (! empty($others)) {
                DB::table('receivables')->whereIn('id', $others)->delete();
            }
        }
    }

    private function recalculateAllReceivableAmounts(): void
    {
        $ids = DB::table('receivables')->pluck('id');

        foreach ($ids as $rid) {
            $sum = (float) DB::table('sales')->where('receivable_id', $rid)->sum('total_amount');
            DB::table('receivables')->where('id', $rid)->update(['amount' => round($sum, 2)]);
        }
    }

    private function createReceivablesForOrphanSales(): void
    {
        $orphans = DB::table('sales')->whereNull('receivable_id')->orderBy('id')->get();

        foreach ($orphans as $sale) {
            $rid = DB::table('receivables')->insertGetId([
                'date'            => $sale->date,
                'invoice_number'  => $sale->invoice_number,
                'customer_name'   => $sale->customer_name,
                'customer_code'   => $sale->customer_code,
                'amount'          => $sale->total_amount,
                'received'        => 0,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            DB::table('sales')->where('id', $sale->id)->update(['receivable_id' => $rid]);
        }
    }

    private function refreshReceivableDisplayColumns(): void
    {
        $ids = DB::table('receivables')->pluck('id');

        foreach ($ids as $rid) {
            $count = (int) DB::table('sales')->where('receivable_id', $rid)->count();
            $latest = DB::table('sales')
                ->where('receivable_id', $rid)
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->first();

            if (! $latest) {
                continue;
            }

            DB::table('receivables')->where('id', $rid)->update([
                'date'           => $latest->date,
                'customer_name'  => $latest->customer_name,
                'customer_code'  => $latest->customer_code,
                'invoice_number' => $count > 1 ? null : $latest->invoice_number,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['receivable_id']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['receivable_id']);
            $table->dropColumn('receivable_id');
        });
    }
};
