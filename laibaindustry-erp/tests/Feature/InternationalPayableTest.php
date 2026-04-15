<?php

namespace Tests\Feature;

use App\Models\InternationalPayableGroupPayment;
use App\Models\InternationalPayablePayment;
use App\Models\InternationalPurchase;
use App\Models\InternationalPurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierLedgerEntry;
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
        $supplier = Supplier::create(['name' => 'Import Vendor']);
        $order->update(['supplier_id' => $supplier->id]);

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
            ->assertSee('Import Vendor')
            ->assertSee('349.50');

        $groupKey = rtrim(strtr(base64_encode('name:import vendor'), '+/', '-_'), '=');
        $this->actingAs($user)->get(route('international-payables.group', ['groupKey' => $groupKey]))
            ->assertOk()
            ->assertSee('Import batch A');
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

    public function test_manager_can_update_and_delete_partial_payments(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $order = $this->makeOrderWithLine('Import batch B', 500);
        $supplier = Supplier::create(['name' => 'Ocean Trade']);
        $order->update(['supplier_id' => $supplier->id]);

        $this->actingAs($user)->post(route('international-payables.pay.store', $order), [
            'payment_date' => '2026-05-05',
            'amount' => '120.00',
            'notes' => 'First part',
        ])->assertRedirect(route('international-payables.index'));

        $this->actingAs($user)->post(route('international-payables.pay.store', $order), [
            'payment_date' => '2026-05-08',
            'amount' => '80.00',
            'notes' => 'Second part',
        ])->assertRedirect(route('international-payables.index'));

        $first = InternationalPayablePayment::query()
            ->where('international_purchase_order_id', $order->id)
            ->orderBy('id')
            ->firstOrFail();

        $this->actingAs($user)->patch(route('international-payables.payments.update', [$order, $first]), [
            "payment_date_{$first->id}" => '2026-05-06',
            "amount_{$first->id}" => '150.00',
            "notes_{$first->id}" => 'Updated part',
        ])->assertRedirect(route('international-payables.pay', $order))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('international_payable_payments', [
            'id' => $first->id,
            'amount' => 150,
            'notes' => 'Updated part',
        ]);
        $this->assertDatabaseHas('supplier_ledger_entries', [
            'source_type' => 'international_payable_payment',
            'source_id' => $first->id,
            'debit' => 150,
            'notes' => 'Updated part',
        ]);

        $second = InternationalPayablePayment::query()
            ->where('international_purchase_order_id', $order->id)
            ->where('id', '!=', $first->id)
            ->firstOrFail();

        $this->actingAs($user)->delete(route('international-payables.payments.destroy', [$order, $second]))
            ->assertRedirect(route('international-payables.pay', $order))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('international_payable_payments', [
            'id' => $second->id,
        ]);
        $this->assertDatabaseMissing('supplier_ledger_entries', [
            'source_type' => 'international_payable_payment',
            'source_id' => $second->id,
        ]);

        $this->assertSame(1, InternationalPayablePayment::query()->where('international_purchase_order_id', $order->id)->count());
        $this->assertSame(1, SupplierLedgerEntry::query()->where('source_type', 'international_payable_payment')->count());
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
            ->assertSee('30.00');
        $groupKey = rtrim(strtr(base64_encode('order:'.$order->id), '+/', '-_'), '=');
        $this->actingAs($user)->get(route('international-payables.group', ['groupKey' => $groupKey]))
            ->assertOk()
            ->assertSee('PO-777');
        $this->assertSame(1, InternationalPurchaseOrder::query()->where('invoice_number', 'PO-777')->count());
    }

    public function test_payables_index_groups_same_vendor_name_into_single_entry(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $supplierA = Supplier::create(['name' => 'Global Source']);
        $supplierB = Supplier::create(['name' => 'Global Source']);

        $orderA = InternationalPurchaseOrder::create([
            'supplier_id' => $supplierA->id,
            'date' => '2026-06-01',
            'invoice_number' => 'G-1',
            'total_amount' => 30,
        ]);
        InternationalPurchase::create([
            'international_purchase_order_id' => $orderA->id,
            'product_name' => 'A',
            'quantity' => 1,
            'unit_price' => 30,
            'total_amount' => 30,
        ]);

        $orderB = InternationalPurchaseOrder::create([
            'supplier_id' => $supplierB->id,
            'date' => '2026-06-02',
            'invoice_number' => 'G-2',
            'total_amount' => 70,
        ]);
        InternationalPurchase::create([
            'international_purchase_order_id' => $orderB->id,
            'product_name' => 'B',
            'quantity' => 1,
            'unit_price' => 70,
            'total_amount' => 70,
        ]);

        $index = $this->actingAs($user)->get(route('international-payables.index'));
        $index->assertOk()
            ->assertSee('Global Source')
            ->assertSee('2')
            ->assertSee('100.00');

        $groupKey = rtrim(strtr(base64_encode('name:global source'), '+/', '-_'), '=');
        $group = $this->actingAs($user)->get(route('international-payables.group', ['groupKey' => $groupKey]));
        $group->assertOk()
            ->assertSee('G-1')
            ->assertSee('G-2')
            ->assertSee('100.00');
    }

    public function test_group_combined_payment_allocates_fifo_across_vendor_invoices(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $supplier = Supplier::create(['name' => 'Merge Vendor']);

        $orderA = InternationalPurchaseOrder::create([
            'supplier_id' => $supplier->id,
            'date' => '2026-06-01',
            'invoice_number' => 'M-1',
            'total_amount' => 100,
        ]);
        InternationalPurchase::create([
            'international_purchase_order_id' => $orderA->id,
            'product_name' => 'A',
            'quantity' => 1,
            'unit_price' => 100,
            'total_amount' => 100,
        ]);

        $orderB = InternationalPurchaseOrder::create([
            'supplier_id' => $supplier->id,
            'date' => '2026-06-02',
            'invoice_number' => 'M-2',
            'total_amount' => 100,
        ]);
        InternationalPurchase::create([
            'international_purchase_order_id' => $orderB->id,
            'product_name' => 'B',
            'quantity' => 1,
            'unit_price' => 100,
            'total_amount' => 100,
        ]);

        $groupKey = rtrim(strtr(base64_encode('name:merge vendor'), '+/', '-_'), '=');
        $this->actingAs($user)->post(route('international-payables.group.payments.store', ['groupKey' => $groupKey]), [
            'payment_date' => '2026-06-10',
            'amount' => '120.00',
            'notes' => 'Combined',
        ])->assertRedirect(route('international-payables.group', ['groupKey' => $groupKey]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('international_payable_payments', [
            'international_purchase_order_id' => $orderA->id,
            'amount' => 100,
            'notes' => 'Combined',
        ]);
        $this->assertDatabaseHas('international_payable_payments', [
            'international_purchase_order_id' => $orderB->id,
            'amount' => 20,
            'notes' => 'Combined',
        ]);
        $this->assertDatabaseHas('international_payable_group_payments', [
            'group_key' => 'name:merge vendor',
            'amount' => 120,
        ]);
    }

    public function test_group_combined_payment_can_be_updated_and_removed(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $supplier = Supplier::create(['name' => 'Merge Vendor']);
        $order = InternationalPurchaseOrder::create([
            'supplier_id' => $supplier->id,
            'date' => '2026-06-01',
            'invoice_number' => 'M-1',
            'total_amount' => 100,
        ]);
        InternationalPurchase::create([
            'international_purchase_order_id' => $order->id,
            'product_name' => 'A',
            'quantity' => 1,
            'unit_price' => 100,
            'total_amount' => 100,
        ]);

        $groupKey = rtrim(strtr(base64_encode('name:merge vendor'), '+/', '-_'), '=');
        $this->actingAs($user)->post(route('international-payables.group.payments.store', ['groupKey' => $groupKey]), [
            'payment_date' => '2026-06-10',
            'amount' => '60.00',
            'notes' => 'Combined',
        ])->assertRedirect(route('international-payables.group', ['groupKey' => $groupKey]));

        $groupPayment = InternationalPayableGroupPayment::query()->firstOrFail();
        $this->actingAs($user)->patch(route('international-payables.group.payments.update', ['groupKey' => $groupKey, 'internationalPayableGroupPayment' => $groupPayment]), [
            'payment_date' => '2026-06-11',
            'amount' => '80.00',
            'notes' => 'Updated',
        ])->assertRedirect(route('international-payables.group', ['groupKey' => $groupKey]));

        $this->assertDatabaseHas('international_payable_group_payments', [
            'id' => $groupPayment->id,
            'amount' => 80,
            'notes' => 'Updated',
        ]);

        $this->actingAs($user)->delete(route('international-payables.group.payments.destroy', ['groupKey' => $groupKey, 'internationalPayableGroupPayment' => $groupPayment]))
            ->assertRedirect(route('international-payables.group', ['groupKey' => $groupKey]));

        $this->assertDatabaseMissing('international_payable_group_payments', [
            'id' => $groupPayment->id,
        ]);
    }

    public function test_delete_grouped_payment_from_pay_page_removes_full_batch(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $supplier = Supplier::create(['name' => 'Merge Vendor']);
        $order = InternationalPurchaseOrder::create([
            'supplier_id' => $supplier->id,
            'date' => '2026-06-01',
            'invoice_number' => 'M-1',
            'total_amount' => 100,
        ]);
        InternationalPurchase::create([
            'international_purchase_order_id' => $order->id,
            'product_name' => 'A',
            'quantity' => 1,
            'unit_price' => 100,
            'total_amount' => 100,
        ]);

        $groupKey = rtrim(strtr(base64_encode('name:merge vendor'), '+/', '-_'), '=');
        $this->actingAs($user)->post(route('international-payables.group.payments.store', ['groupKey' => $groupKey]), [
            'payment_date' => '2026-06-10',
            'amount' => '60.00',
            'notes' => 'Combined',
        ])->assertRedirect(route('international-payables.group', ['groupKey' => $groupKey]));

        $payment = InternationalPayablePayment::query()->firstOrFail();
        $this->actingAs($user)->delete(route('international-payables.payments.destroy', [$order, $payment]))
            ->assertRedirect(route('international-payables.pay', $order))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('international_payable_group_payments', 0);
        $this->assertDatabaseCount('international_payable_payments', 0);
    }

    public function test_delete_payment_with_group_key_query_redirects_back_to_group_page(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $supplier = Supplier::create(['name' => 'Merge Vendor']);
        $order = InternationalPurchaseOrder::create([
            'supplier_id' => $supplier->id,
            'date' => '2026-06-01',
            'invoice_number' => 'M-1',
            'total_amount' => 100,
        ]);
        InternationalPurchase::create([
            'international_purchase_order_id' => $order->id,
            'product_name' => 'A',
            'quantity' => 1,
            'unit_price' => 100,
            'total_amount' => 100,
        ]);

        $this->actingAs($user)->post(route('international-payables.pay.store', $order), [
            'payment_date' => '2026-06-10',
            'amount' => '20.00',
        ])->assertRedirect(route('international-payables.index'));

        $payment = InternationalPayablePayment::query()->firstOrFail();
        $groupKey = rtrim(strtr(base64_encode('name:merge vendor'), '+/', '-_'), '=');
        $this->actingAs($user)->delete(route('international-payables.payments.destroy', [
            'international_purchase' => $order,
            'internationalPayablePayment' => $payment,
            'groupKey' => $groupKey,
        ]))
            ->assertRedirect(route('international-payables.group', ['groupKey' => $groupKey]))
            ->assertSessionHas('success');
    }
}
