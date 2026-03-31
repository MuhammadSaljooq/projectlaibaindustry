<?php

namespace Tests\Feature;

use App\Models\InternationalPurchase;
use App\Models\Supplier;
use App\Models\SupplierLedgerEntry;
use App\Models\User;
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
            'product_name' => 'Bolts',
            'quantity' => 10,
            'unit_price' => '5',
        ])->assertRedirect(route('international-purchases.index'));

        $this->assertDatabaseHas('supplier_ledger_entries', [
            'supplier_id' => $supplier->id,
            'source_type' => 'international_purchase',
            'credit' => 50,
            'debit' => 0,
        ]);

        $this->actingAs($user)->get(route('suppliers.ledger', $supplier))
            ->assertOk()
            ->assertSee('Bolts')
            ->assertSee('50.00');
    }

    public function test_international_payment_posts_debit_to_ledger(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $supplier = Supplier::create(['name' => 'Pay Co']);
        $purchase = InternationalPurchase::create([
            'supplier_id' => $supplier->id,
            'date' => '2026-06-01',
            'product_name' => 'Item',
            'quantity' => 1,
            'unit_price' => 100,
            'total_amount' => 100,
        ]);
        \App\Services\SupplierLedgerSync::syncInternationalPurchase($purchase);

        $this->actingAs($user)->post(route('international-payables.pay.store', $purchase), [
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
            'product_name' => 'No supplier',
            'quantity' => 1,
            'unit_price' => '10',
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
}
