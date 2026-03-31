<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_and_list_supplier(): void
    {
        $user = User::factory()->create(['role' => 'manager']);

        $this->actingAs($user)->post(route('suppliers.store'), [
            'name' => 'Global Safety Ltd',
            'country' => 'China',
            'contact_name' => 'Wei Chen',
            'email' => 'wei@example.com',
            'phone' => '+86 1',
        ])->assertRedirect(route('suppliers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('suppliers', [
            'name' => 'Global Safety Ltd',
            'country' => 'China',
        ]);

        $this->actingAs($user)->get(route('suppliers.index'))
            ->assertOk()
            ->assertSee('Global Safety Ltd')
            ->assertSee('China');
    }

    public function test_viewer_cannot_post_supplier_store(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($user)->post(route('suppliers.store'), [
            'name' => 'X',
        ])->assertForbidden();
    }
}
