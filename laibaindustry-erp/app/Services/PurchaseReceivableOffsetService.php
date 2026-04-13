<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\CustomerReceivablePurchaseOffset;
use App\Models\Purchase;
use App\Models\Receivable;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class PurchaseReceivableOffsetService
{
    public function syncPurchaseOffsets(Purchase $purchase, ?string $customerCode, CarbonInterface $offsetDate): void
    {
        $affectedReceivableIds = CustomerReceivablePurchaseOffset::query()
            ->where('purchase_id', $purchase->id)
            ->pluck('receivable_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        CustomerReceivablePurchaseOffset::query()
            ->where('purchase_id', $purchase->id)
            ->delete();

        $customerCode = trim((string) $customerCode);
        $remaining = round((float) $purchase->total_amount, 2);

        if ($customerCode === '' || $remaining <= 0) {
            $this->syncReceivablesByIds($affectedReceivableIds);

            return;
        }

        $customer = Customer::query()->where('customer_code', $customerCode)->first();
        if (! $customer) {
            $this->syncReceivablesByIds($affectedReceivableIds);

            return;
        }

        $receivables = Receivable::query()
            ->where('customer_code', $customerCode)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        foreach ($receivables as $receivable) {
            if ($remaining <= 0) {
                break;
            }

            $available = round((float) $receivable->amount - $this->currentReceivedTotal($receivable), 2);
            if ($available <= 0) {
                continue;
            }

            $slice = min($remaining, $available);
            if ($slice <= 0) {
                continue;
            }

            CustomerReceivablePurchaseOffset::query()->create([
                'customer_id' => $customer->id,
                'purchase_id' => $purchase->id,
                'receivable_id' => $receivable->id,
                'amount' => $slice,
                'offset_date' => Carbon::parse($offsetDate, config('app.timezone'))->startOfDay(),
            ]);

            $affectedReceivableIds[] = (int) $receivable->id;
            $remaining = round($remaining - $slice, 2);
        }

        $this->syncReceivablesByIds($affectedReceivableIds);
    }

    public function clearPurchaseOffsets(Purchase $purchase): void
    {
        $affectedReceivableIds = CustomerReceivablePurchaseOffset::query()
            ->where('purchase_id', $purchase->id)
            ->pluck('receivable_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        CustomerReceivablePurchaseOffset::query()
            ->where('purchase_id', $purchase->id)
            ->delete();

        $this->syncReceivablesByIds($affectedReceivableIds);
    }

    public function syncReceivable(Receivable $receivable): void
    {
        $paymentSum = (float) CustomerLedgerEntry::query()
            ->where('source_type', 'payment_received')
            ->where('source_id', $receivable->id)
            ->sum('credit');

        $offsetSum = (float) CustomerReceivablePurchaseOffset::query()
            ->where('receivable_id', $receivable->id)
            ->sum('amount');

        $received = round(min((float) $receivable->amount, $paymentSum + $offsetSum), 2);

        $maxPaymentDate = CustomerLedgerEntry::query()
            ->where('source_type', 'payment_received')
            ->where('source_id', $receivable->id)
            ->max('date');
        $maxOffsetDate = CustomerReceivablePurchaseOffset::query()
            ->where('receivable_id', $receivable->id)
            ->max('offset_date');

        $maxDate = null;
        if ($maxPaymentDate && $maxOffsetDate) {
            $maxDate = Carbon::parse(max($maxPaymentDate, $maxOffsetDate), config('app.timezone'));
        } elseif ($maxPaymentDate) {
            $maxDate = Carbon::parse($maxPaymentDate, config('app.timezone'));
        } elseif ($maxOffsetDate) {
            $maxDate = Carbon::parse($maxOffsetDate, config('app.timezone'));
        }

        $receivable->update([
            'received' => $received,
            'payment_received_at' => $received > 0 && $maxDate ? $maxDate : null,
        ]);
    }

    private function syncReceivablesByIds(array $ids): void
    {
        if ($ids === []) {
            return;
        }

        /** @var Collection<int, Receivable> $receivables */
        $receivables = Receivable::query()
            ->whereIn('id', array_values(array_unique($ids)))
            ->get();

        foreach ($receivables as $receivable) {
            $this->syncReceivable($receivable);
        }
    }

    private function currentReceivedTotal(Receivable $receivable): float
    {
        $paymentSum = (float) CustomerLedgerEntry::query()
            ->where('source_type', 'payment_received')
            ->where('source_id', $receivable->id)
            ->sum('credit');

        $offsetSum = (float) CustomerReceivablePurchaseOffset::query()
            ->where('receivable_id', $receivable->id)
            ->sum('amount');

        return round(min((float) $receivable->amount, $paymentSum + $offsetSum), 2);
    }
}
