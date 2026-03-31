<?php

namespace Tests\Feature;

use App\Models\InternationalPurchase;
use App\Models\Supplier;
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
            'product_name' => 'Safety gloves bulk',
            'quantity' => 4,
            'unit_price' => '12.50',
        ])->assertRedirect(route('international-purchases.index'))
            ->assertSessionHas('success');

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

    public function test_viewer_cannot_post_store(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($user)->post(route('international-purchases.store'), [
            'date' => '2026-01-01',
            'product_name' => 'X',
            'quantity' => 1,
            'unit_price' => '10',
        ])->assertForbidden();
    }

    public function test_total_amount_is_rounded_from_quantity_and_unit_price(): void
    {
        $user = User::factory()->create(['role' => 'manager']);

        $this->actingAs($user)->post(route('international-purchases.store'), [
            'date' => '2026-02-01',
            'product_name' => 'Widget',
            'quantity' => 3,
            'unit_price' => '10.333',
        ])->assertRedirect(route('international-purchases.index'));

        $row = InternationalPurchase::query()->where('product_name', 'Widget')->first();
        $this->assertNotNull($row);
        $this->assertEquals(31.0, (float) $row->total_amount);
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
            'product_name' => 'Helmets',
            'quantity' => 2,
            'unit_price' => '25',
        ])->assertRedirect(route('international-purchases.index'));

        $this->assertDatabaseHas('international_purchases', [
            'supplier_id' => $supplier->id,
            'product_name' => 'Helmets',
            'total_amount' => 50.0,
        ]);

        $this->actingAs($user)->get(route('international-purchases.index'))
            ->assertOk()
            ->assertSee('Overseas Vendor Co')
            ->assertSee('Helmets');
    }
}
