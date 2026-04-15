<?php

namespace Tests\Feature;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseGroupingTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_groups_same_customer_into_one_row(): void
    {
        $user = User::factory()->create(['role' => 'manager']);

        $p1 = Purchase::create([
            'date' => '2026-08-01 00:00:00',
            'customer_name' => 'Acme',
            'customer_code' => 'AC-1',
            'invoice_number' => 'PI-1',
            'subtotal' => 100,
            'vat_amount' => 15,
            'total_amount' => 115,
        ]);
        PurchaseItem::create([
            'purchase_id' => $p1->id,
            'product_name' => 'Mask A',
            'price' => 100,
            'quantity' => 1,
            'amount' => 100,
            'vat_amount' => 15,
            'subtotal' => 115,
        ]);

        $p2 = Purchase::create([
            'date' => '2026-08-02 00:00:00',
            'customer_name' => 'Acme',
            'customer_code' => 'AC-1',
            'invoice_number' => 'PI-2',
            'subtotal' => 50,
            'vat_amount' => 7.5,
            'total_amount' => 57.5,
        ]);
        PurchaseItem::create([
            'purchase_id' => $p2->id,
            'product_name' => 'Mask B',
            'price' => 50,
            'quantity' => 1,
            'amount' => 50,
            'vat_amount' => 7.5,
            'subtotal' => 57.5,
        ]);

        $response = $this->actingAs($user)->get(route('purchases.index'));
        $response->assertOk()
            ->assertSee('Acme')
            ->assertSee('2')
            ->assertSee('172.50');
    }

    public function test_group_page_shows_all_customer_invoices(): void
    {
        $user = User::factory()->create(['role' => 'manager']);

        $p1 = Purchase::create([
            'date' => '2026-08-01 00:00:00',
            'customer_name' => 'Grouped Co',
            'customer_code' => null,
            'invoice_number' => 'G-1',
            'subtotal' => 100,
            'vat_amount' => 15,
            'total_amount' => 115,
        ]);
        $p2 = Purchase::create([
            'date' => '2026-08-02 00:00:00',
            'customer_name' => 'Grouped Co',
            'customer_code' => null,
            'invoice_number' => 'G-2',
            'subtotal' => 10,
            'vat_amount' => 1.5,
            'total_amount' => 11.5,
        ]);

        $groupKey = rtrim(strtr(base64_encode('name:grouped co'), '+/', '-_'), '=');
        $response = $this->actingAs($user)->get(route('purchases.group', ['groupKey' => $groupKey]));
        $response->assertOk()
            ->assertSee('G-1')
            ->assertSee('G-2')
            ->assertSee('126.50');
    }
}
