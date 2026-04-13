<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\CustomerReceivablePurchaseOffset;
use App\Models\Purchase;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseReceivableOffsetTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        return User::factory()->create(['role' => 'manager']);
    }

    public function test_purchase_store_auto_offsets_latest_receivables_first(): void
    {
        $user = $this->manager();
        Customer::create(['customer_code' => 'C-100', 'customer_name' => 'Offset Customer']);

        $older = Receivable::create([
            'date' => '2026-01-01 00:00:00',
            'invoice_number' => 'AR-OLD',
            'customer_name' => 'Offset Customer',
            'customer_code' => 'C-100',
            'amount' => 80,
            'received' => 0,
        ]);
        $latest = Receivable::create([
            'date' => '2026-02-01 00:00:00',
            'invoice_number' => 'AR-NEW',
            'customer_name' => 'Offset Customer',
            'customer_code' => 'C-100',
            'amount' => 70,
            'received' => 0,
        ]);

        $response = $this->actingAs($user)->post(route('purchases.store'), $this->purchasePayload(
            date: '2026-03-01',
            code: 'C-100',
            name: 'Offset Customer',
            invoice: 'PUR-100',
            price: '100.00'
        ));

        $response->assertRedirect(route('purchases.index'));

        $purchase = Purchase::query()->where('invoice_number', 'PUR-100')->first();
        $this->assertNotNull($purchase);
        $this->assertEqualsWithDelta(115.0, (float) $purchase->total_amount, 0.001);

        $latest->refresh();
        $older->refresh();
        $this->assertEqualsWithDelta(70.0, (float) $latest->received, 0.001);
        $this->assertEqualsWithDelta(45.0, (float) $older->received, 0.001);

        $this->assertDatabaseHas('customer_receivable_purchase_offsets', [
            'purchase_id' => $purchase->id,
            'receivable_id' => $latest->id,
            'amount' => '70.00',
        ]);
        $this->assertDatabaseHas('customer_receivable_purchase_offsets', [
            'purchase_id' => $purchase->id,
            'receivable_id' => $older->id,
            'amount' => '45.00',
        ]);
    }

    public function test_purchase_update_rebuilds_offsets_idempotently(): void
    {
        $user = $this->manager();
        Customer::create(['customer_code' => 'C-200', 'customer_name' => 'Rebalance Customer']);

        $older = Receivable::create([
            'date' => '2026-01-01 00:00:00',
            'invoice_number' => 'AR-A',
            'customer_name' => 'Rebalance Customer',
            'customer_code' => 'C-200',
            'amount' => 60,
            'received' => 0,
        ]);
        $latest = Receivable::create([
            'date' => '2026-02-01 00:00:00',
            'invoice_number' => 'AR-B',
            'customer_name' => 'Rebalance Customer',
            'customer_code' => 'C-200',
            'amount' => 60,
            'received' => 0,
        ]);

        $this->actingAs($user)->post(route('purchases.store'), $this->purchasePayload(
            date: '2026-03-01',
            code: 'C-200',
            name: 'Rebalance Customer',
            invoice: 'PUR-200',
            price: '50.00'
        ));

        $purchase = Purchase::query()->where('invoice_number', 'PUR-200')->firstOrFail();

        $patch = $this->actingAs($user)->put(route('purchases.update', $purchase), $this->purchasePayload(
            date: '2026-03-05',
            code: 'C-200',
            name: 'Rebalance Customer',
            invoice: 'PUR-200',
            price: '100.00'
        ));
        $patch->assertRedirect(route('purchases.show', $purchase));

        $latest->refresh();
        $older->refresh();
        $this->assertEqualsWithDelta(60.0, (float) $latest->received, 0.001);
        $this->assertEqualsWithDelta(55.0, (float) $older->received, 0.001);
        $this->assertSame(2, CustomerReceivablePurchaseOffset::query()->where('purchase_id', $purchase->id)->count());
    }

    public function test_purchase_store_without_customer_code_creates_no_offsets(): void
    {
        $user = $this->manager();

        $response = $this->actingAs($user)->post(route('purchases.store'), $this->purchasePayload(
            date: '2026-03-01',
            code: '',
            name: 'Walk-in',
            invoice: 'PUR-NOCODE',
            price: '40.00'
        ));

        $response->assertRedirect(route('purchases.index'));
        $purchase = Purchase::query()->where('invoice_number', 'PUR-NOCODE')->firstOrFail();
        $this->assertSame(0, CustomerReceivablePurchaseOffset::query()->where('purchase_id', $purchase->id)->count());
    }

    public function test_offsets_respect_existing_receivable_payments(): void
    {
        $user = $this->manager();
        $customer = Customer::create(['customer_code' => 'C-300', 'customer_name' => 'Mixed Customer']);

        $receivable = Receivable::create([
            'date' => '2026-01-01 00:00:00',
            'invoice_number' => 'AR-MIX',
            'customer_name' => 'Mixed Customer',
            'customer_code' => 'C-300',
            'amount' => 100,
            'received' => 0,
        ]);

        CustomerLedgerEntry::create([
            'customer_id' => $customer->id,
            'date' => '2026-01-10 00:00:00',
            'description' => 'Payment Received',
            'reference' => 'AR-MIX',
            'debit' => 0,
            'credit' => 30,
            'source_type' => 'payment_received',
            'source_id' => $receivable->id,
        ]);

        $response = $this->actingAs($user)->post(route('purchases.store'), $this->purchasePayload(
            date: '2026-03-01',
            code: 'C-300',
            name: 'Mixed Customer',
            invoice: 'PUR-MIX',
            price: '80.00'
        ));

        $response->assertRedirect(route('purchases.index'));

        $receivable->refresh();
        $this->assertEqualsWithDelta(100.0, (float) $receivable->received, 0.001);
        $this->assertDatabaseHas('customer_receivable_purchase_offsets', [
            'receivable_id' => $receivable->id,
            'amount' => '70.00',
        ]);
    }

    private function purchasePayload(string $date, string $code, string $name, string $invoice, string $price): array
    {
        return [
            'date' => $date,
            'customer_code' => $code !== '' ? $code : null,
            'customer_name' => $name,
            'invoice_number' => $invoice,
            'items' => [
                [
                    'product_name' => 'Service',
                    'price' => $price,
                    'quantity' => '1',
                ],
            ],
        ];
    }
}
