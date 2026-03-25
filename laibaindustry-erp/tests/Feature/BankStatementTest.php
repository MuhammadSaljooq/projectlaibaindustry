<?php

namespace Tests\Feature;

use App\Models\BankStatementEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankStatementTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_view_index_and_create_inflow_and_outflow(): void
    {
        $user = User::factory()->create(['role' => 'manager']);

        $this->actingAs($user)->get(route('bank-statement.index'))
            ->assertOk()
            ->assertSee('Cash inflow')
            ->assertSee('Cash outflow');

        $this->actingAs($user)->post(route('bank-statement.store'), [
            'flow_type' => 'inflow',
            'transaction_date' => '2025-06-01',
            'company_name' => 'Acme Ltd',
            'amount' => '150.50',
        ])->assertRedirect(route('bank-statement.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bank_statement_entries', [
            'flow_type' => 'inflow',
            'company_name' => 'Acme Ltd',
            'amount' => 150.5,
        ]);

        $this->actingAs($user)->post(route('bank-statement.store'), [
            'flow_type' => 'outflow',
            'transaction_date' => '2025-06-02',
            'company_name' => 'Supplier Co',
            'amount' => '75.25',
        ])->assertRedirect(route('bank-statement.index'));

        $this->assertDatabaseHas('bank_statement_entries', [
            'flow_type' => 'outflow',
            'company_name' => 'Supplier Co',
        ]);

        $this->actingAs($user)->get(route('bank-statement.index'))
            ->assertOk()
            ->assertSee('Acme Ltd')
            ->assertSee('Supplier Co');
    }

    public function test_store_validation_puts_errors_in_named_bag_for_inflow(): void
    {
        $user = User::factory()->create(['role' => 'manager']);

        $response = $this->actingAs($user)->post(route('bank-statement.store'), [
            'flow_type' => 'inflow',
            'transaction_date' => '',
            'company_name' => '',
            'amount' => '0',
        ]);

        $response->assertRedirect(route('bank-statement.index'));
        $response->assertSessionHasErrors(['transaction_date', 'company_name', 'amount'], errorBag: 'storeInflow');
    }

    public function test_manager_can_update_and_delete(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $entry = BankStatementEntry::create([
            'flow_type' => BankStatementEntry::FLOW_INFLOW,
            'transaction_date' => '2025-01-10',
            'company_name' => 'Old Co',
            'amount' => 10,
        ]);

        $this->actingAs($user)->put(route('bank-statement.update', $entry), [
            'transaction_date' => '2025-02-10',
            'company_name' => 'New Co',
            'amount' => '99.99',
        ])->assertRedirect(route('bank-statement.index'));

        $entry->refresh();
        $this->assertSame('New Co', $entry->company_name);
        $this->assertEquals(99.99, (float) $entry->amount);

        $this->actingAs($user)->delete(route('bank-statement.destroy', $entry))
            ->assertRedirect(route('bank-statement.index'));

        $this->assertDatabaseMissing('bank_statement_entries', ['id' => $entry->id]);
    }

    public function test_viewer_cannot_post_store(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($user)->post(route('bank-statement.store'), [
            'flow_type' => 'inflow',
            'transaction_date' => '2025-06-01',
            'company_name' => 'X',
            'amount' => '10',
        ])->assertForbidden();
    }
}
