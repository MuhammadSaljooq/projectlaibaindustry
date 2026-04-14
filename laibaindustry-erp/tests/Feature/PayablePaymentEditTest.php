<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\Payable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayablePaymentEditTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: Payable, 1: CustomerLedgerEntry, 2: CustomerLedgerEntry} */
    private function seedPayableWithLedgerPayments(): array
    {
        $customer = Customer::create([
            'customer_code' => 'C-100',
            'customer_name' => 'Acme Textiles',
        ]);

        $payable = Payable::create([
            'date' => '2026-05-01 00:00:00',
            'invoice_number' => 'PINV-1',
            'customer_name' => $customer->customer_name,
            'customer_code' => $customer->customer_code,
            'amount' => 500,
            'received' => 0,
        ]);

        $p1 = CustomerLedgerEntry::create([
            'customer_id' => $customer->id,
            'date' => '2026-05-02 00:00:00',
            'description' => 'Payment Made',
            'reference' => 'PINV-1',
            'debit' => 120,
            'credit' => 0,
            'source_type' => 'payment_made',
            'source_id' => $payable->id,
        ]);
        $p2 = CustomerLedgerEntry::create([
            'customer_id' => $customer->id,
            'date' => '2026-05-04 00:00:00',
            'description' => 'Payment Made',
            'reference' => 'PINV-1',
            'debit' => 80,
            'credit' => 0,
            'source_type' => 'payment_made',
            'source_id' => $payable->id,
        ]);

        $payable->update(['received' => 200]);

        return [$payable->fresh(), $p1, $p2];
    }

    public function test_put_records_dated_partial_payment_and_posts_ledger(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        [$payable] = $this->seedPayableWithLedgerPayments();

        $this->actingAs($user)->put(route('payables.update', $payable), [
            'payment_date' => '2026-05-10',
            'received' => '150.00',
        ])->assertRedirect(route('payables.index'))
            ->assertSessionHas('success');

        $payable->refresh();
        $this->assertSame(350.0, (float) $payable->received);
        $this->assertDatabaseHas('customer_ledger_entries', [
            'source_type' => 'payment_made',
            'source_id' => $payable->id,
            'debit' => 150,
        ]);
    }

    public function test_patch_updates_ledger_payment_and_syncs_payable_received(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        [$payable, $p1] = $this->seedPayableWithLedgerPayments();

        $this->actingAs($user)->patch(route('payables.payments.update', [$payable, $p1]), [
            "date_{$p1->id}" => '2026-05-03',
            "debit_{$p1->id}" => '160.00',
        ])->assertRedirect(route('payables.edit', $payable))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('customer_ledger_entries', [
            'id' => $p1->id,
            'debit' => 160,
        ]);
        $this->assertSame(240.0, (float) $payable->fresh()->received);
    }

    public function test_delete_removes_ledger_payment_and_syncs_payable_received(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        [$payable, $p1] = $this->seedPayableWithLedgerPayments();

        $this->actingAs($user)->delete(route('payables.payments.destroy', [$payable, $p1]))
            ->assertRedirect(route('payables.edit', $payable))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('customer_ledger_entries', ['id' => $p1->id]);
        $this->assertSame(80.0, (float) $payable->fresh()->received);
    }
}
