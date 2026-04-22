<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\CustomerPayableSaleOffset;
use App\Models\Payable;
use App\Models\Sale;
use App\Models\User;
use App\Services\SalePayableOffsetService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalePayableOffsetTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        return User::factory()->create(['role' => 'manager']);
    }

    public function test_sale_store_auto_offsets_latest_payables_first(): void
    {
        $this->manager();
        Customer::create(['customer_code' => 'C-100', 'customer_name' => 'Offset Customer']);

        $older = Payable::create([
            'date' => '2026-01-01 00:00:00',
            'invoice_number' => 'AP-OLD',
            'customer_name' => 'Offset Customer',
            'customer_code' => 'C-100',
            'amount' => 80,
            'received' => 0,
        ]);
        $latest = Payable::create([
            'date' => '2026-02-01 00:00:00',
            'invoice_number' => 'AP-NEW',
            'customer_name' => 'Offset Customer',
            'customer_code' => 'C-100',
            'amount' => 70,
            'received' => 0,
        ]);

        $sale = Sale::create([
            'date' => '2026-03-01 00:00:00',
            'customer_code' => 'C-100',
            'customer_name' => 'Offset Customer',
            'invoice_number' => 'SAL-100',
            'subtotal' => 100,
            'tax_amount' => 15,
            'discount_amount' => 0,
            'total_amount' => 115,
            'tax_rate' => 15,
            'currency_id' => null,
            'exchange_rate' => null,
            'status' => 'completed',
        ]);

        app(SalePayableOffsetService::class)->syncSaleOffsets($sale, 'C-100', Carbon::parse('2026-03-01'));

        $latest->refresh();
        $older->refresh();
        $this->assertEqualsWithDelta(70.0, (float) $latest->received, 0.001);
        $this->assertEqualsWithDelta(45.0, (float) $older->received, 0.001);

        $this->assertDatabaseHas('customer_payable_sales_offsets', [
            'sale_id' => $sale->id,
            'payable_id' => $latest->id,
            'amount' => '70.00',
        ]);
        $this->assertDatabaseHas('customer_payable_sales_offsets', [
            'sale_id' => $sale->id,
            'payable_id' => $older->id,
            'amount' => '45.00',
        ]);
    }

    public function test_sale_update_rebuilds_offsets_idempotently(): void
    {
        $this->manager();
        Customer::create(['customer_code' => 'C-200', 'customer_name' => 'Rebalance Customer']);

        $older = Payable::create([
            'date' => '2026-01-01 00:00:00',
            'invoice_number' => 'AP-A',
            'customer_name' => 'Rebalance Customer',
            'customer_code' => 'C-200',
            'amount' => 120,
            'received' => 0,
        ]);
        $latest = Payable::create([
            'date' => '2026-02-01 00:00:00',
            'invoice_number' => 'AP-B',
            'customer_name' => 'Rebalance Customer',
            'customer_code' => 'C-200',
            'amount' => 120,
            'received' => 0,
        ]);

        $sale = Sale::create([
            'date' => '2026-03-01 00:00:00',
            'customer_code' => 'C-200',
            'customer_name' => 'Rebalance Customer',
            'invoice_number' => 'SAL-200',
            'subtotal' => 100,
            'tax_amount' => 15,
            'discount_amount' => 0,
            'total_amount' => 115,
            'tax_rate' => 15,
            'currency_id' => null,
            'exchange_rate' => null,
            'status' => 'completed',
        ]);

        $service = app(SalePayableOffsetService::class);
        $service->syncSaleOffsets($sale, 'C-200', Carbon::parse('2026-03-01'));

        $sale->update([
            'date' => '2026-03-05 00:00:00',
            'subtotal' => 200,
            'tax_amount' => 30,
            'total_amount' => 230,
        ]);
        $service->syncSaleOffsets($sale, 'C-200', Carbon::parse('2026-03-05'));

        $latest->refresh();
        $older->refresh();
        $this->assertEqualsWithDelta(120.0, (float) $latest->received, 0.001);
        $this->assertEqualsWithDelta(110.0, (float) $older->received, 0.001);
        $this->assertSame(2, CustomerPayableSaleOffset::query()->where('sale_id', $sale->id)->count());
    }

    public function test_sale_store_without_customer_code_creates_no_offsets(): void
    {
        $this->manager();
        $sale = Sale::create([
            'date' => '2026-03-01 00:00:00',
            'customer_code' => null,
            'customer_name' => 'Walk-in',
            'invoice_number' => 'SAL-NOCODE',
            'subtotal' => 40,
            'tax_amount' => 6,
            'discount_amount' => 0,
            'total_amount' => 46,
            'tax_rate' => 15,
            'currency_id' => null,
            'exchange_rate' => null,
            'status' => 'completed',
        ]);

        app(SalePayableOffsetService::class)->syncSaleOffsets($sale, '', Carbon::parse('2026-03-01'));
        $this->assertSame(0, CustomerPayableSaleOffset::query()->where('sale_id', $sale->id)->count());
    }

    public function test_offsets_respect_existing_payable_payments(): void
    {
        $this->manager();
        $customer = Customer::create(['customer_code' => 'C-300', 'customer_name' => 'Mixed Customer']);

        $payable = Payable::create([
            'date' => '2026-01-01 00:00:00',
            'invoice_number' => 'AP-MIX',
            'customer_name' => 'Mixed Customer',
            'customer_code' => 'C-300',
            'amount' => 100,
            'received' => 0,
        ]);

        CustomerLedgerEntry::create([
            'customer_id' => $customer->id,
            'date' => '2026-01-10 00:00:00',
            'description' => 'Payment Made',
            'reference' => 'AP-MIX',
            'debit' => 30,
            'credit' => 0,
            'source_type' => 'payment_made',
            'source_id' => $payable->id,
        ]);

        $sale = Sale::create([
            'date' => '2026-03-01 00:00:00',
            'customer_code' => 'C-300',
            'customer_name' => 'Mixed Customer',
            'invoice_number' => 'SAL-MIX',
            'subtotal' => 80,
            'tax_amount' => 12,
            'discount_amount' => 0,
            'total_amount' => 92,
            'tax_rate' => 15,
            'currency_id' => null,
            'exchange_rate' => null,
            'status' => 'completed',
        ]);

        app(SalePayableOffsetService::class)->syncSaleOffsets($sale, 'C-300', Carbon::parse('2026-03-01'));

        $payable->refresh();
        $this->assertEqualsWithDelta(100.0, (float) $payable->received, 0.001);
        $this->assertDatabaseHas('customer_payable_sales_offsets', [
            'payable_id' => $payable->id,
            'amount' => '70.00',
        ]);
    }

}
