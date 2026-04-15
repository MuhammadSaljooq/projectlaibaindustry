<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Payable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayableGroupingTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_groups_same_customer_name_rows_into_one_entry(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        Customer::create(['customer_code' => 'C-1', 'customer_name' => 'Acme']);

        Payable::create([
            'date' => '2026-07-01 00:00:00',
            'invoice_number' => 'P-1',
            'customer_name' => 'Acme',
            'customer_code' => 'C-1',
            'amount' => 100,
            'received' => 10,
        ]);
        Payable::create([
            'date' => '2026-07-02 00:00:00',
            'invoice_number' => 'P-2',
            'customer_name' => 'Acme',
            'customer_code' => 'C-1',
            'amount' => 50,
            'received' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('payables.index'));
        $response->assertOk()
            ->assertSee('Acme')
            ->assertSee('2')
            ->assertSee('150.00')
            ->assertSee('140.00');
    }

    public function test_group_combined_payment_allocates_fifo_across_invoices(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        Customer::create(['customer_code' => 'C-1', 'customer_name' => 'Acme']);

        $older = Payable::create([
            'date' => '2026-07-01 00:00:00',
            'invoice_number' => 'P-1',
            'customer_name' => 'Acme',
            'customer_code' => 'C-1',
            'amount' => 100,
            'received' => 0,
        ]);
        $newer = Payable::create([
            'date' => '2026-07-02 00:00:00',
            'invoice_number' => 'P-2',
            'customer_name' => 'Acme',
            'customer_code' => 'C-1',
            'amount' => 100,
            'received' => 0,
        ]);

        $groupKey = rtrim(strtr(base64_encode('code:c-1'), '+/', '-_'), '=');
        $this->actingAs($user)->post(route('payables.group.payments.store', ['groupKey' => $groupKey]), [
            'payment_date' => '2026-07-10',
            'amount' => '120.00',
        ])->assertRedirect(route('payables.group', ['groupKey' => $groupKey]))
            ->assertSessionHas('success');

        $older->refresh();
        $newer->refresh();
        $this->assertSame(100.0, (float) $older->received);
        $this->assertSame(20.0, (float) $newer->received);
        $this->assertDatabaseHas('customer_ledger_entries', [
            'source_type' => 'payment_made',
            'source_id' => $older->id,
            'debit' => 100,
        ]);
        $this->assertDatabaseHas('customer_ledger_entries', [
            'source_type' => 'payment_made',
            'source_id' => $newer->id,
            'debit' => 20,
        ]);
    }
}
