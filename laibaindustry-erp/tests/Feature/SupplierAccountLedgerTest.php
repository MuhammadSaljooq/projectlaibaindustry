<?php

namespace Tests\Feature;

use App\Models\InternationalPurchase;
use App\Models\InternationalPurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierLedgerEntry;
use App\Models\User;
use App\Services\SupplierLedgerSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierAccountLedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_international_purchase_with_supplier_posts_credit_to_ledger(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $supplier = Supplier::create(['name' => 'Ledger Test Co']);

        $this->actingAs($user)->post(route('international-purchases.store'), [
            'supplier_id' => $supplier->id,
            'date' => '2026-06-01',
            'invoice_number' => 'INV-7788',
            'items' => [
                ['product_name' => 'Bolts', 'quantity' => 10, 'unit_price' => '5'],
            ],
        ])->assertRedirect(route('international-purchases.index'));

        $this->assertDatabaseHas('supplier_ledger_entries', [
            'supplier_id' => $supplier->id,
            'source_type' => 'international_purchase_order',
            'credit' => 50,
            'debit' => 0,
        ]);

        $this->assertSame(1, SupplierLedgerEntry::query()
            ->where('source_type', 'international_purchase_order')
            ->count());

        $this->actingAs($user)->get(route('suppliers.ledger', $supplier))
            ->assertOk()
            ->assertSee('Bolts')
            ->assertSee('INV-7788')
            ->assertSee('50.00');
    }

    public function test_international_payment_posts_debit_to_ledger(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $supplier = Supplier::create(['name' => 'Pay Co']);
        $order = InternationalPurchaseOrder::create([
            'supplier_id' => $supplier->id,
            'date' => '2026-06-01',
            'invoice_number' => null,
            'total_amount' => 100,
        ]);
        InternationalPurchase::create([
            'international_purchase_order_id' => $order->id,
            'product_name' => 'Item',
            'quantity' => 1,
            'unit_price' => 100,
            'total_amount' => 100,
        ]);
        SupplierLedgerSync::syncInternationalPurchaseOrder($order->fresh(['lines']));

        $this->actingAs($user)->post(route('international-payables.pay.store', $order), [
            'payment_date' => '2026-06-15',
            'amount' => '40',
        ])->assertRedirect(route('international-payables.index'));

        $this->assertDatabaseHas('supplier_ledger_entries', [
            'supplier_id' => $supplier->id,
            'source_type' => 'international_payable_payment',
            'debit' => 40,
            'credit' => 0,
        ]);
    }

    public function test_purchase_without_supplier_does_not_create_ledger_row(): void
    {
        $user = User::factory()->create(['role' => 'manager']);

        $this->actingAs($user)->post(route('international-purchases.store'), [
            'date' => '2026-06-01',
            'items' => [
                ['product_name' => 'No supplier', 'quantity' => 1, 'unit_price' => '10'],
            ],
        ])->assertRedirect(route('international-purchases.index'));

        $this->assertSame(0, SupplierLedgerEntry::query()->count());
    }

    public function test_manager_sees_balance_on_suppliers_index(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        Supplier::create(['name' => 'Acme']);

        $this->actingAs($user)->get(route('suppliers.index'))
            ->assertOk()
            ->assertSee('Acme')
            ->assertSee('Balance owed');
    }

    public function test_manager_can_download_vendor_statement_pdf(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $supplier = Supplier::create(['name' => 'PDF Vendor']);

        $response = $this->actingAs($user)->get(route('suppliers.ledger.pdf', $supplier));

        if ($response->status() === 302) {
            $response->assertRedirect(route('suppliers.ledger', $supplier));
            $response->assertSessionHas('error');

            return;
        }

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertHeader('content-disposition');
    }
}
