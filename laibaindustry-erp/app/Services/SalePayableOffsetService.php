<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\CustomerPayableSaleOffset;
use App\Models\Payable;
use App\Models\Receivable;
use App\Models\Sale;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class SalePayableOffsetService
{
    public function syncSaleOffsets(Sale $sale, ?string $customerCode, CarbonInterface $offsetDate): void
    {
        $affectedPayableIds = CustomerPayableSaleOffset::query()
            ->where('sale_id', $sale->id)
            ->pluck('payable_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        CustomerPayableSaleOffset::query()
            ->where('sale_id', $sale->id)
            ->delete();

        $customerCode = trim((string) $customerCode);
        $remaining = $this->saleOffsetCapacity($sale, $customerCode);

        if ($customerCode === '' || $remaining <= 0) {
            $this->syncPayablesByIds($affectedPayableIds);

            return;
        }

        $customer = Customer::query()->where('customer_code', $customerCode)->first();
        if (! $customer) {
            $this->syncPayablesByIds($affectedPayableIds);

            return;
        }

        $payables = Payable::query()
            ->where('customer_code', $customerCode)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        foreach ($payables as $payable) {
            if ($remaining <= 0) {
                break;
            }

            $available = round((float) $payable->amount - $this->currentReceivedTotal($payable), 2);
            if ($available <= 0) {
                continue;
            }

            $slice = min($remaining, $available);
            if ($slice <= 0) {
                continue;
            }

            CustomerPayableSaleOffset::query()->create([
                'customer_id' => $customer->id,
                'sale_id' => $sale->id,
                'payable_id' => $payable->id,
                'amount' => $slice,
                'offset_date' => Carbon::parse($offsetDate, config('app.timezone'))->startOfDay(),
            ]);

            $affectedPayableIds[] = (int) $payable->id;
            $remaining = round($remaining - $slice, 2);
        }

        $this->syncPayablesByIds($affectedPayableIds);
    }

    public function clearSaleOffsets(Sale $sale): void
    {
        $affectedPayableIds = CustomerPayableSaleOffset::query()
            ->where('sale_id', $sale->id)
            ->pluck('payable_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        CustomerPayableSaleOffset::query()
            ->where('sale_id', $sale->id)
            ->delete();

        $this->syncPayablesByIds($affectedPayableIds);
    }

    public function syncPayable(Payable $payable): void
    {
        $paymentSum = (float) CustomerLedgerEntry::query()
            ->where('source_type', 'payment_made')
            ->where('source_id', $payable->id)
            ->sum('debit');

        $offsetSum = (float) CustomerPayableSaleOffset::query()
            ->where('payable_id', $payable->id)
            ->sum('amount');

        $received = round(min((float) $payable->amount, $paymentSum + $offsetSum), 2);

        $maxPaymentDate = CustomerLedgerEntry::query()
            ->where('source_type', 'payment_made')
            ->where('source_id', $payable->id)
            ->max('date');
        $maxOffsetDate = CustomerPayableSaleOffset::query()
            ->where('payable_id', $payable->id)
            ->max('offset_date');

        $maxDate = null;
        if ($maxPaymentDate && $maxOffsetDate) {
            $maxDate = Carbon::parse(max($maxPaymentDate, $maxOffsetDate), config('app.timezone'));
        } elseif ($maxPaymentDate) {
            $maxDate = Carbon::parse($maxPaymentDate, config('app.timezone'));
        } elseif ($maxOffsetDate) {
            $maxDate = Carbon::parse($maxOffsetDate, config('app.timezone'));
        }

        $payable->update([
            'received' => $received,
            'received_date' => $received > 0 && $maxDate ? $maxDate : null,
        ]);
    }

    public function syncPayablesByCustomerCode(?string $customerCode): void
    {
        $code = trim((string) $customerCode);
        if ($code === '') {
            return;
        }

        $this->syncSalesByCustomerCode($code);

        $ids = Payable::query()
            ->where('customer_code', $code)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->syncPayablesByIds($ids);
    }

    public function syncSalesByCustomerCode(?string $customerCode): void
    {
        $code = trim((string) $customerCode);
        if ($code === '') {
            return;
        }

        $sales = Sale::query()
            ->where('customer_code', $code)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        foreach ($sales as $sale) {
            $offsetDate = $sale->date instanceof CarbonInterface
                ? $sale->date
                : Carbon::parse((string) $sale->date, config('app.timezone'));

            $this->syncSaleOffsets($sale, $code, $offsetDate);
        }
    }

    private function syncPayablesByIds(array $ids): void
    {
        if ($ids === []) {
            return;
        }

        /** @var Collection<int, Payable> $payables */
        $payables = Payable::query()
            ->whereIn('id', array_values(array_unique($ids)))
            ->get();

        foreach ($payables as $payable) {
            $this->syncPayable($payable);
        }
    }

    private function currentReceivedTotal(Payable $payable): float
    {
        $paymentSum = (float) CustomerLedgerEntry::query()
            ->where('source_type', 'payment_made')
            ->where('source_id', $payable->id)
            ->sum('debit');

        $offsetSum = (float) CustomerPayableSaleOffset::query()
            ->where('payable_id', $payable->id)
            ->sum('amount');

        return round(min((float) $payable->amount, $paymentSum + $offsetSum), 2);
    }

    private function saleOffsetCapacity(Sale $sale, string $customerCode): float
    {
        if ($customerCode === '') {
            return 0.0;
        }

        $receivable = Receivable::query()
            ->where('customer_code', $customerCode)
            ->where('invoice_number', $sale->invoice_number)
            ->orderByDesc('id')
            ->first();

        if (! $receivable) {
            return round((float) $sale->total_amount, 2);
        }

        $cashReceived = (float) CustomerLedgerEntry::query()
            ->where('source_type', 'payment_received')
            ->where('source_id', $receivable->id)
            ->sum('credit');

        return round(max(0, (float) $sale->total_amount - $cashReceived), 2);
    }
}
