<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\User;
use App\Models\VatEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VatEntryDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        return User::factory()->create(['role' => 'manager']);
    }

    public function test_manager_can_delete_vat_entry_and_sees_success_flash(): void
    {
        $user = $this->manager();

        $entry = VatEntry::create([
            'type' => 'sale',
            'source_type' => Sale::class,
            'source_id' => 1,
            'date' => '2026-01-15 10:00:00',
            'invoice_number' => 'VAT-DEL-1',
            'customer_name' => 'Test Co',
            'customer_code' => null,
            'subtotal' => 100.00,
            'vat_rate' => 15.00,
            'vat_amount' => 15.00,
            'total_amount' => 115.00,
        ]);

        $response = $this->actingAs($user)->from(route('vat.index', ['search' => 'VAT-DEL', 'from' => '2026-01-01', 'to' => '2026-01-31']))
            ->delete(route('vat.destroy', $entry), [
                'search' => 'VAT-DEL',
                'from' => '2026-01-01',
                'to' => '2026-01-31',
            ]);

        $response->assertRedirect(route('vat.index', [
            'search' => 'VAT-DEL',
            'from' => '2026-01-01',
            'to' => '2026-01-31',
        ]));
        $response->assertSessionHas('success');
        $this->assertNull(VatEntry::query()->find($entry->id));
    }

    public function test_viewer_cannot_delete_vat_entry(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $entry = VatEntry::create([
            'type' => 'purchase',
            'source_type' => 'App\\Models\\Purchase',
            'source_id' => 1,
            'date' => '2026-02-01 10:00:00',
            'invoice_number' => 'P-1',
            'customer_name' => null,
            'customer_code' => 'SUP',
            'subtotal' => 50.00,
            'vat_rate' => 15.00,
            'vat_amount' => 7.50,
            'total_amount' => 57.50,
        ]);

        $this->actingAs($viewer)
            ->delete(route('vat.destroy', $entry))
            ->assertForbidden();

        $this->assertNotNull(VatEntry::query()->find($entry->id));
    }
}
