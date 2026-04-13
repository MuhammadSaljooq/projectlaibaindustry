<?php

use App\Models\CustomerReceivablePurchaseOffset;
use App\Models\Purchase;
use App\Services\PurchaseReceivableOffsetService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('offsets:backfill {--customer_code=} {--purchase_id=*} {--dry-run}', function () {
    if (! Schema::hasTable('customer_receivable_purchase_offsets')) {
        $this->error('Table customer_receivable_purchase_offsets does not exist. Run migrations first.');

        return 1;
    }

    /** @var PurchaseReceivableOffsetService $service */
    $service = app(PurchaseReceivableOffsetService::class);

    $query = Purchase::query()->orderBy('date')->orderBy('id');
    $customerCode = trim((string) $this->option('customer_code'));
    if ($customerCode !== '') {
        $query->where('customer_code', $customerCode);
    }

    $purchaseIds = array_values(array_filter((array) $this->option('purchase_id'), fn ($v) => (string) $v !== ''));
    if ($purchaseIds !== []) {
        $query->whereIn('id', $purchaseIds);
    }

    $purchases = $query->get();
    if ($purchases->isEmpty()) {
        $this->warn('No purchases matched filters. Nothing to process.');

        return 0;
    }

    $isDryRun = (bool) $this->option('dry-run');
    $processed = 0;
    $skipped = 0;
    $offsetRows = 0;
    $offsetAmount = 0.0;

    $this->info(sprintf(
        'Backfill start: %d purchase(s)%s',
        $purchases->count(),
        $isDryRun ? ' [DRY RUN]' : ''
    ));

    $runner = function () use (
        $purchases,
        $service,
        &$processed,
        &$skipped,
        &$offsetRows,
        &$offsetAmount
    ): void {
        foreach ($purchases as $purchase) {
            $code = trim((string) $purchase->customer_code);
            if ($code === '') {
                $skipped++;
                continue;
            }

            DB::transaction(function () use ($service, $purchase): void {
                $service->syncPurchaseOffsets(
                    $purchase,
                    $purchase->customer_code,
                    $purchase->date ?? now()
                );
            });

            $processed++;
            $offsetRows += (int) CustomerReceivablePurchaseOffset::query()
                ->where('purchase_id', $purchase->id)
                ->count();
            $offsetAmount += (float) CustomerReceivablePurchaseOffset::query()
                ->where('purchase_id', $purchase->id)
                ->sum('amount');
        }
    };

    if ($isDryRun) {
        DB::beginTransaction();
        try {
            $runner();
        } finally {
            DB::rollBack();
        }
    } else {
        $runner();
    }

    $this->newLine();
    $this->line('Backfill summary');
    $this->line('---------------');
    $this->line('Processed purchases: '.$processed);
    $this->line('Skipped purchases:   '.$skipped);
    $this->line('Offset rows:         '.$offsetRows);
    $this->line('Offset amount:       '.number_format($offsetAmount, 2));

    if ($isDryRun) {
        $this->warn('Dry run complete. No data was persisted.');
    } else {
        $this->info('Backfill complete. Data persisted.');
    }

    return 0;
})->purpose('Backfill AP-AR purchase offsets for historical purchases');
