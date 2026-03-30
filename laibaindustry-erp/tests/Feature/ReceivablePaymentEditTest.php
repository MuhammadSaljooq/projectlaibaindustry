<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\Receivable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceivablePaymentEditTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        return User::factory()->create(['role' => 'manager']);
    }

    /** @return array{0: Customer, 1: Receivable, 2: CustomerLedgerEntry, 3: CustomerLedgerEntry} */
    private function receivableWithTwoPayments(): array
    {
        $customer = Customer::create([
            'customer_code' => 'RCP-001',
            'customer_name' => 'RCP Customer',
            'phone' => null,
            'email' => null,
            'address' => null,
            'opening_balance' => 0,
            'opening_balance_date' => null,
        ]);

        $receivable = Receivable::create([
            'date' => '2026-01-10 12:00:00',
            'invoice_number' => 'RCP-INV-1',
            'customer_name' => 'RCP Customer',
            'customer_code' => 'RCP-001',
            'amount' => 100.00,
            'received' => 60.00,
            'payment_received_at' => '2026-02-01 00:00:00',
        ]);

        $e1 = CustomerLedgerEntry::create([
            'customer_id' => $customer->id,
            'date' => '2026-01-15 00:00:00',
            'description' => 'Payment Received',
            'reference' => 'RCP-INV-1',
            'debit' => 0,
            'credit' => 25.00,
            'source_type' => 'payment_received',
            'source_id' => $receivable->id,
            'notes' => null,
        ]);

        $e2 = CustomerLedgerEntry::create([
            'customer_id' => $customer->id,
            'date' => '2026-02-01 00:00:00',
            'description' => 'Payment Received',
            'reference' => 'RCP-INV-1',
            'debit' => 0,
            'credit' => 35.00,
            'source_type' => 'payment_received',
            'source_id' => $receivable->id,
            'notes' => null,
        ]);

        return [$customer, $receivable, $e1, $e2];
    }

    public function test_patch_payment_updates_ledger_and_syncs_receivable_received(): void
    {
        $user = $this->manager();
        [, $receivable, $e1] = $this->receivableWithTwoPayments();

        $response = $this->actingAs($user)->patch(
            route('receivables.payments.update', [$receivable, $e1]),
            [
                "date_{$e1->id}" => '2026-01-20',
                "credit_{$e1->id}" => '30.00',
            ]
        );

        $response->assertRedirect(route('receivables.edit', $receivable));
        $response->assertSessionHas('success');

        $e1->refresh();
        $this->assertSame('30.00', $e1->credit);
        $this->assertStringStartsWith('2026-01-20', $e1->date->format('Y-m-d'));

        $receivable->refresh();
        $this->assertEqualsWithDelta(65.00, (float) $receivable->received, 0.001);
        $this->assertStringStartsWith('2026-02-01', $receivable->payment_received_at->format('Y-m-d'));
    }

    public function test_delete_payment_syncs_receivable(): void
    {
        $user = $this->manager();
        [, $receivable, $e1] = $this->receivableWithTwoPayments();

        $response = $this->actingAs($user)->delete(
            route('receivables.payments.destroy', [$receivable, $e1])
        );

        $response->assertRedirect(route('receivables.edit', $receivable));
        $this->assertDatabaseMissing('customer_ledger_entries', ['id' => $e1->id]);

        $receivable->refresh();
        $this->assertEqualsWithDelta(35.00, (float) $receivable->received, 0.001);
    }

    public function test_patch_fails_when_total_exceeds_bill(): void
    {
        $user = $this->manager();
        [, $receivable, $e1] = $this->receivableWithTwoPayments();

        $response = $this->actingAs($user)->patch(
            route('receivables.payments.update', [$receivable, $e1]),
            [
                "date_{$e1->id}" => '2026-01-15',
                "credit_{$e1->id}" => '90.00',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $receivable->refresh();
        $this->assertEqualsWithDelta(60.00, (float) $receivable->received, 0.001);
    }

    public function test_patch_returns_404_when_ledger_entry_belongs_to_other_receivable(): void
    {
        $user = $this->manager();
        [, $receivableA, $e1] = $this->receivableWithTwoPayments();

        $receivableB = Receivable::create([
            'date' => '2026-03-01 12:00:00',
            'invoice_number' => 'RCP-INV-2',
            'customer_name' => 'RCP Customer',
            'customer_code' => 'RCP-001',
            'amount' => 50.00,
            'received' => 0,
            'payment_received_at' => null,
        ]);

        $response = $this->actingAs($user)->patch(
            route('receivables.payments.update', [$receivableB, $e1]),
            [
                "date_{$e1->id}" => '2026-01-15',
                "credit_{$e1->id}" => '10.00',
            ]
        );

        $response->assertNotFound();
    }

    public function test_adjust_received_without_ledger_updates_receivable_only(): void
    {
        $user = $this->manager();

        $receivable = Receivable::create([
            'date' => '2026-01-10 12:00:00',
            'invoice_number' => 'ORPH-1',
            'customer_name' => 'Walk-in',
            'customer_code' => null,
            'amount' => 80.00,
            'received' => 40.00,
            'payment_received_at' => '2026-01-11 00:00:00',
        ]);

        $response = $this->actingAs($user)->put(
            route('receivables.adjust-received', $receivable),
            [
                'received' => '50.00',
                'payment_received_at' => '2026-01-12',
            ]
        );

        $response->assertRedirect(route('receivables.edit', $receivable));
        $receivable->refresh();
        $this->assertEqualsWithDelta(50.00, (float) $receivable->received, 0.001);
        $this->assertStringStartsWith('2026-01-12', $receivable->payment_received_at->format('Y-m-d'));
    }
}
