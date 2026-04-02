<?php

namespace Tests\Feature;

use App\Models\InternationalPurchase;
use App\Models\InternationalPurchaseOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternationalPayableTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrderWithLine(string $productName, float $total): InternationalPurchaseOrder
    {
        $order = InternationalPurchaseOrder::create([
            'supplier_id' => null,
            'date' => '2026-05-01',
            'invoice_number' => null,
            'total_amount' => $total,
        ]);
        InternationalPurchase::create([
            'international_purchase_order_id' => $order->id,
            'product_name' => $productName,
            'quantity' => 1,
            'unit_price' => $total,
            'total_amount' => $total,
        ]);

        return $order->fresh(['lines']);
    }

    public function test_manager_can_record_payment_against_international_purchase_order(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $order = $this->makeOrderWithLine('Import batch A', 500);

        $this->actingAs($user)->post(
            route('international-payables.pay.store', $order),
            [
                'payment_date' => '2026-05-10',
                'amount' => '150.50',
                'notes' => 'Wire ref 001',
            ]
        )->assertRedirect(route('international-payables.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('international_payable_payments', [
            'international_purchase_order_id' => $order->id,
            'amount' => 150.5,
        ]);

        $this->actingAs($user)->get(route('international-payables.index'))
            ->assertOk()
            ->assertSee('Import batch A')
            ->assertSee('349.50');
    }

    public function test_payment_cannot_exceed_balance(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $order = $this->makeOrderWithLine('Small', 10);

        $this->actingAs($user)->from(route('international-payables.pay', $order))
            ->post(route('international-payables.pay.store', $order), [
                'payment_date' => '2026-05-01',
                'amount' => '50',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('amount');
    }

    public function test_viewer_cannot_post_payment(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);
        $order = $this->makeOrderWithLine('X', 5);

        $this->actingAs($user)->post(route('international-payables.pay.store', $order), [
            'payment_date' => '2026-05-01',
            'amount' => '5',
        ])->assertForbidden();
    }

    public function test_payables_index_shows_one_row_for_multi_line_invoice(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $order = InternationalPurchaseOrder::create([
            'supplier_id' => null,
            'date' => '2026-05-01',
            'invoice_number' => 'PO-777',
            'total_amount' => 30,
        ]);
        InternationalPurchase::create([
            'international_purchase_order_id' => $order->id,
            'product_name' => 'Alpha',
            'quantity' => 1,
            'unit_price' => 10,
            'total_amount' => 10,
        ]);
        InternationalPurchase::create([
            'international_purchase_order_id' => $order->id,
            'product_name' => 'Beta',
            'quantity' => 1,
            'unit_price' => 20,
            'total_amount' => 20,
        ]);

        $response = $this->actingAs($user)->get(route('international-payables.index'));
        $response->assertOk()
            ->assertSee('PO-777')
            ->assertSee('30.00');
        $this->assertSame(1, InternationalPurchaseOrder::query()->where('invoice_number', 'PO-777')->count());
    }
}
