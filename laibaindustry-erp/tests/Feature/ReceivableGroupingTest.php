<?php

namespace Tests\Feature;

use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceivableGroupingTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        return User::factory()->create(['role' => 'manager']);
    }

    public function test_index_groups_multiple_invoices_by_customer_code(): void
    {
        $user = $this->manager();

        Receivable::create([
            'date' => '2026-01-01 10:00:00',
            'invoice_number' => 'A-1',
            'customer_name' => 'Acme Co',
            'customer_code' => 'ACME',
            'amount' => 100,
            'received' => 0,
            'payment_received_at' => null,
        ]);
        Receivable::create([
            'date' => '2026-02-01 10:00:00',
            'invoice_number' => 'A-2',
            'customer_name' => 'Acme Co',
            'customer_code' => 'ACME',
            'amount' => 50,
            'received' => 10,
            'payment_received_at' => '2026-02-05 00:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('receivables.index'));

        $response->assertOk();
        $groups = $response->viewData('receivableGroups');
        $this->assertSame(1, $groups->count());
        $row = $groups->first();
        $this->assertSame(2, (int) $row->invoice_count);
        $this->assertEqualsWithDelta(150.0, (float) $row->total_amount, 0.001);
        $this->assertEqualsWithDelta(10.0, (float) $row->total_received, 0.001);
        $this->assertSame('code:ACME', $row->ar_group_key);
    }

    public function test_index_groups_by_normalized_name_when_code_empty(): void
    {
        $user = $this->manager();

        Receivable::create([
            'date' => '2026-01-01 10:00:00',
            'invoice_number' => 'W-1',
            'customer_name' => '  WalkIn LLC ',
            'customer_code' => null,
            'amount' => 30,
            'received' => 0,
            'payment_received_at' => null,
        ]);
        Receivable::create([
            'date' => '2026-03-01 10:00:00',
            'invoice_number' => 'W-2',
            'customer_name' => 'walkin llc',
            'customer_code' => '',
            'amount' => 20,
            'received' => 0,
            'payment_received_at' => null,
        ]);

        $response = $this->actingAs($user)->get(route('receivables.index'));

        $response->assertOk();
        $groups = $response->viewData('receivableGroups');
        $this->assertSame(1, $groups->count());
        $row = $groups->first();
        $this->assertSame(2, (int) $row->invoice_count);
        $this->assertStringStartsWith('name:', $row->ar_group_key);
    }

    public function test_group_page_lists_all_invoices_for_code(): void
    {
        $user = $this->manager();

        Receivable::create([
            'date' => '2026-01-01 10:00:00',
            'invoice_number' => 'X-1',
            'customer_name' => 'X Corp',
            'customer_code' => 'XCODE',
            'amount' => 10,
            'received' => 0,
            'payment_received_at' => null,
        ]);
        Receivable::create([
            'date' => '2026-02-01 10:00:00',
            'invoice_number' => 'X-2',
            'customer_name' => 'X Corp',
            'customer_code' => 'XCODE',
            'amount' => 20,
            'received' => 0,
            'payment_received_at' => null,
        ]);

        $key = Receivable::encodeGroupKeyForRoute('code:XCODE');
        $response = $this->actingAs($user)->get(route('receivables.group', ['groupKey' => $key]));

        $response->assertOk();
        $lines = $response->viewData('receivables');
        $this->assertCount(2, $lines);
        $this->assertTrue($lines->pluck('invoice_number')->contains('X-1'));
        $this->assertTrue($lines->pluck('invoice_number')->contains('X-2'));
    }

    public function test_viewer_can_view_group_page(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);

        $r = Receivable::create([
            'date' => '2026-01-01 10:00:00',
            'invoice_number' => 'V-1',
            'customer_name' => 'Viewer Co',
            'customer_code' => 'VIEWCO',
            'amount' => 5,
            'received' => 0,
            'payment_received_at' => null,
        ]);

        $key = Receivable::encodeGroupKeyForRoute(Receivable::canonicalGroupKey($r->customer_code, $r->customer_name, (int) $r->id));
        $response = $this->actingAs($user)->get(route('receivables.group', ['groupKey' => $key]));

        $response->assertOk();
    }
}
