<?php

namespace Tests\Feature;

use App\Models\InternationalPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternationalPayableTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_record_payment_against_international_purchase(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $purchase = InternationalPurchase::create([
            'date' => '2026-05-01',
            'product_name' => 'Import batch A',
            'quantity' => 1,
            'unit_price' => 500,
            'total_amount' => 500,
        ]);

        $this->actingAs($user)->post(
            route('international-payables.pay.store', $purchase),
            [
                'payment_date' => '2026-05-10',
                'amount' => '150.50',
                'notes' => 'Wire ref 001',
            ]
        )->assertRedirect(route('international-payables.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('international_payable_payments', [
            'international_purchase_id' => $purchase->id,
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
        $purchase = InternationalPurchase::create([
            'date' => '2026-05-01',
            'product_name' => 'Small',
            'quantity' => 1,
            'unit_price' => 10,
            'total_amount' => 10,
        ]);

        $this->actingAs($user)->from(route('international-payables.pay', $purchase))
            ->post(route('international-payables.pay.store', $purchase), [
                'payment_date' => '2026-05-01',
                'amount' => '50',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('amount');
    }

    public function test_viewer_cannot_post_payment(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);
        $purchase = InternationalPurchase::create([
            'date' => '2026-05-01',
            'product_name' => 'X',
            'quantity' => 1,
            'unit_price' => 5,
            'total_amount' => 5,
        ]);

        $this->actingAs($user)->post(route('international-payables.pay.store', $purchase), [
            'payment_date' => '2026-05-01',
            'amount' => '5',
        ])->assertForbidden();
    }
}
