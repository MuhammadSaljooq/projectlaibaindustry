<?php

namespace Tests\Feature;

use App\Models\InternationalPurchase;
use App\Models\InternationalPurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierLedgerEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternationalPurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_and_list_international_purchase(): void
    {
        $user = User::factory()->create(['role' => 'manager']);

        $this->actingAs($user)->post(route('international-purchases.store'), [
            'date' => '2026-03-15',
            'items' => [
                [
                    'product_name' => 'Safety gloves bulk',
                    'quantity' => 4,
                    'unit_price' => '12.50',
                ],
            ],
        ])->assertRedirect(route('international-purchases.index'))
            ->assertSessionHas('success');

        $this->assertSame(1, InternationalPurchaseOrder::query()->count());
        $this->assertDatabaseHas('international_purchases', [
            'product_name' => 'Safety gloves bulk',
            'quantity' => 4,
            'total_amount' => 50.0,
        ]);

        $this->actingAs($user)->get(route('international-purchases.index'))
            ->assertOk()
            ->assertSee('Safety gloves bulk')
            ->assertSee('50.00');
    }

    public function test_manager_can_create_multiple_lines_in_one_submit_produces_one_invoice_row(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $supplier = Supplier::create([
            'name' => 'Overseas Vendor Co',
            'country' => 'Germany',
        ]);

        $this->actingAs($user)->post(route('international-purchases.store'), [
            'supplier_id' => $supplier->id,
            'date' => '2026-05-10',
            'invoice_number' => 'INV-INT-99',
            'items' => [
                ['product_name' => 'Line A', 'quantity' => 2, 'unit_price' => '10'],
                ['product_name' => 'Line B', 'quantity' => 1, 'unit_price' => '25.50'],
            ],
        ])->assertRedirect(route('international-purchases.index'))
            ->assertSessionHas('success');

        $this->assertSame(1, InternationalPurchaseOrder::query()->count());
        $this->assertSame(2, InternationalPurchase::query()->count());

        $this->assertDatabaseHas('international_purchase_orders', [
            'supplier_id' => $supplier->id,
            'invoice_number' => 'INV-INT-99',
            'total_amount' => 45.5,
        ]);

        $this->assertDatabaseHas('international_purchases', [
            'product_name' => 'Line A',
            'quantity' => 2,
            'total_amount' => 20.0,
        ]);
        $this->assertDatabaseHas('international_purchases', [
            'product_name' => 'Line B',
            'quantity' => 1,
            'total_amount' => 25.5,
        ]);

        $response = $this->actingAs($user)->get(route('international-purchases.index'));
        $response->assertOk()
            ->assertSee('Line A')
            ->assertSee('45.50')
            ->assertSee('INV-INT-99');
        $this->assertSame(
            1,
            substr_count($response->getContent(), 'Overseas Vendor Co'),
            'Vendor name should appear once (one invoice row)'
        );
        $this->assertSame(1, SupplierLedgerEntry::query()
            ->where('source_type', 'international_purchase_order')
            ->where('supplier_id', $supplier->id)
            ->count());
    }

    public function test_viewer_cannot_post_store(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($user)->post(route('international-purchases.store'), [
            'date' => '2026-01-01',
            'items' => [
                ['product_name' => 'X', 'quantity' => 1, 'unit_price' => '10'],
            ],
        ])->assertForbidden();
    }

    public function test_total_amount_is_rounded_from_quantity_and_unit_price(): void
    {
        $user = User::factory()->create(['role' => 'manager']);

        $this->actingAs($user)->post(route('international-purchases.store'), [
            'date' => '2026-02-01',
            'items' => [
                ['product_name' => 'Widget', 'quantity' => 3, 'unit_price' => '10.333'],
            ],
        ])->assertRedirect(route('international-purchases.index'));

        $row = InternationalPurchase::query()->where('product_name', 'Widget')->first();
        $this->assertNotNull($row);
        $this->assertEquals(31.0, (float) $row->total_amount);
        $order = $row->order;
        $this->assertNotNull($order);
        $this->assertEquals(31.0, (float) $order->total_amount);
    }

    public function test_manager_can_attach_supplier_to_international_purchase(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $supplier = Supplier::create([
            'name' => 'Overseas Vendor Co',
            'country' => 'Germany',
        ]);

        $this->actingAs($user)->post(route('international-purchases.store'), [
            'supplier_id' => $supplier->id,
            'date' => '2026-04-01',
            'items' => [
                ['product_name' => 'Helmets', 'quantity' => 2, 'unit_price' => '25'],
            ],
        ])->assertRedirect(route('international-purchases.index'));

        $this->assertDatabaseHas('international_purchase_orders', [
            'supplier_id' => $supplier->id,
            'total_amount' => 50.0,
        ]);
        $this->assertDatabaseHas('international_purchases', [
            'product_name' => 'Helmets',
            'total_amount' => 50.0,
        ]);

        $this->actingAs($user)->get(route('international-purchases.index'))
            ->assertOk()
            ->assertSee('Overseas Vendor Co')
            ->assertSee('Helmets');
    }
}
