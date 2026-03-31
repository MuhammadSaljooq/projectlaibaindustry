<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\CustomerLedgerReceivableGroupPayment;
use App\Models\Receivable;
use App\Models\ReceivableGroupPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceivableGroupPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        return User::factory()->create(['role' => 'manager']);
    }

    public function test_group_page_exposes_group_totals(): void
    {
        $user = $this->manager();

        Receivable::create([
            'date' => '2026-01-01 10:00:00',
            'invoice_number' => 'T-1',
            'customer_name' => 'Totals Co',
            'customer_code' => 'TOT',
            'amount' => 100,
            'received' => 25,
            'payment_received_at' => '2026-01-10 00:00:00',
        ]);
        Receivable::create([
            'date' => '2026-02-01 10:00:00',
            'invoice_number' => 'T-2',
            'customer_name' => 'Totals Co',
            'customer_code' => 'TOT',
            'amount' => 50,
            'received' => 0,
            'payment_received_at' => null,
        ]);

        $key = Receivable::encodeGroupKeyForRoute('code:TOT');
        $response = $this->actingAs($user)->get(route('receivables.group', ['groupKey' => $key]));

        $response->assertOk();
        $totals = $response->viewData('groupTotals');
        $this->assertEqualsWithDelta(150.0, (float) $totals['total_bill'], 0.001);
        $this->assertEqualsWithDelta(25.0, (float) $totals['total_received'], 0.001);
        $this->assertEqualsWithDelta(125.0, (float) $totals['total_remaining'], 0.001);
    }

    public function test_store_combined_payment_allocates_fifo_and_sets_ledger_group_id(): void
    {
        $user = $this->manager();

        Customer::create([
            'customer_code' => 'FIFO-1',
            'customer_name' => 'FIFO Customer',
            'phone' => null,
            'email' => null,
            'address' => null,
            'opening_balance' => 0,
            'opening_balance_date' => null,
        ]);

        $r1 = Receivable::create([
            'date' => '2026-01-01 10:00:00',
            'invoice_number' => 'F-1',
            'customer_name' => 'FIFO Customer',
            'customer_code' => 'FIFO-1',
            'amount' => 40,
            'received' => 0,
            'payment_received_at' => null,
        ]);
        $r2 = Receivable::create([
            'date' => '2026-02-01 10:00:00',
            'invoice_number' => 'F-2',
            'customer_name' => 'FIFO Customer',
            'customer_code' => 'FIFO-1',
            'amount' => 60,
            'received' => 0,
            'payment_received_at' => null,
        ]);

        $key = Receivable::encodeGroupKeyForRoute('code:FIFO-1');
        $response = $this->actingAs($user)->post(
            route('receivables.group.payments.store', ['groupKey' => $key]),
            ['payment_date' => '2026-03-01', 'amount' => '50.00']
        );

        $response->assertRedirect(route('receivables.group', ['groupKey' => $key]));
        $r1->refresh();
        $r2->refresh();
        $this->assertEqualsWithDelta(40.0, (float) $r1->received, 0.001);
        $this->assertEqualsWithDelta(10.0, (float) $r2->received, 0.001);

        $gp = ReceivableGroupPayment::query()->first();
        $this->assertNotNull($gp);
        $this->assertEqualsWithDelta(50.0, (float) $gp->amount, 0.001);

        $this->assertSame(2, CustomerLedgerEntry::query()
            ->where('receivable_group_payment_id', $gp->id)
            ->where('source_type', 'payment_received')
            ->count());
        $this->assertSame(2, CustomerLedgerReceivableGroupPayment::query()
            ->where('receivable_group_payment_id', $gp->id)
            ->count());

        $customer = Customer::where('customer_code', 'FIFO-1')->first();
        $this->assertNotNull($customer);
        $stmt = $this->actingAs($user)->get(route('customers.statement', $customer));
        $stmt->assertOk();
        $paymentRows = array_values(array_filter(
            $stmt->viewData('ledgerRows'),
            fn (array $r) => ($r['source_type'] ?? '') === 'payment_received'
        ));
        $this->assertCount(1, $paymentRows);
        $this->assertEqualsWithDelta(50.0, (float) $paymentRows[0]['credit'], 0.001);
    }

    public function test_update_combined_payment_reallocates(): void
    {
        $user = $this->manager();

        Customer::create([
            'customer_code' => 'UP-1',
            'customer_name' => 'Update Customer',
            'phone' => null,
            'email' => null,
            'address' => null,
            'opening_balance' => 0,
            'opening_balance_date' => null,
        ]);

        $r1 = Receivable::create([
            'date' => '2026-01-01 10:00:00',
            'invoice_number' => 'U-1',
            'customer_name' => 'Update Customer',
            'customer_code' => 'UP-1',
            'amount' => 40,
            'received' => 0,
            'payment_received_at' => null,
        ]);
        Receivable::create([
            'date' => '2026-02-01 10:00:00',
            'invoice_number' => 'U-2',
            'customer_name' => 'Update Customer',
            'customer_code' => 'UP-1',
            'amount' => 60,
            'received' => 0,
            'payment_received_at' => null,
        ]);

        $key = Receivable::encodeGroupKeyForRoute('code:UP-1');
        $this->actingAs($user)->post(
            route('receivables.group.payments.store', ['groupKey' => $key]),
            ['payment_date' => '2026-03-01', 'amount' => '30.00']
        );

        $gp = ReceivableGroupPayment::query()->first();
        $this->assertNotNull($gp);

        $patch = $this->actingAs($user)->patch(
            route('receivables.group.payments.update', ['groupKey' => $key, 'receivableGroupPayment' => $gp]),
            ['payment_date' => '2026-03-15', 'amount' => '45.00']
        );
        $patch->assertRedirect(route('receivables.group', ['groupKey' => $key]));

        $r1->refresh();
        $r2 = Receivable::query()->where('invoice_number', 'U-2')->first();
        $this->assertEqualsWithDelta(40.0, (float) $r1->received, 0.001);
        $this->assertEqualsWithDelta(5.0, (float) $r2->received, 0.001);
    }

    public function test_destroy_combined_payment_restores_balances(): void
    {
        $user = $this->manager();

        Customer::create([
            'customer_code' => 'DL-1',
            'customer_name' => 'Delete Customer',
            'phone' => null,
            'email' => null,
            'address' => null,
            'opening_balance' => 0,
            'opening_balance_date' => null,
        ]);

        Receivable::create([
            'date' => '2026-01-01 10:00:00',
            'invoice_number' => 'D-1',
            'customer_name' => 'Delete Customer',
            'customer_code' => 'DL-1',
            'amount' => 40,
            'received' => 0,
            'payment_received_at' => null,
        ]);

        $key = Receivable::encodeGroupKeyForRoute('code:DL-1');
        $this->actingAs($user)->post(
            route('receivables.group.payments.store', ['groupKey' => $key]),
            ['payment_date' => '2026-03-01', 'amount' => '25.00']
        );

        $gp = ReceivableGroupPayment::query()->first();
        $this->assertNotNull($gp);

        $del = $this->actingAs($user)->delete(
            route('receivables.group.payments.destroy', ['groupKey' => $key, 'receivableGroupPayment' => $gp])
        );
        $del->assertRedirect(route('receivables.group', ['groupKey' => $key]));

        $r = Receivable::query()->where('invoice_number', 'D-1')->first();
        $this->assertEqualsWithDelta(0.0, (float) $r->received, 0.001);
        $this->assertNull($r->payment_received_at);
        $this->assertSame(0, ReceivableGroupPayment::query()->count());
    }

    public function test_destroy_combined_payment_succeeds_when_line_references_deleted_receivable(): void
    {
        $user = $this->manager();

        Customer::create([
            'customer_code' => 'OR-1',
            'customer_name' => 'Orphan Customer',
            'phone' => null,
            'email' => null,
            'address' => null,
            'opening_balance' => 0,
            'opening_balance_date' => null,
        ]);

        Receivable::create([
            'date' => '2026-01-01 10:00:00',
            'invoice_number' => 'O-1',
            'customer_name' => 'Orphan Customer',
            'customer_code' => 'OR-1',
            'amount' => 40,
            'received' => 0,
            'payment_received_at' => null,
        ]);
        Receivable::create([
            'date' => '2026-02-01 10:00:00',
            'invoice_number' => 'O-2',
            'customer_name' => 'Orphan Customer',
            'customer_code' => 'OR-1',
            'amount' => 10,
            'received' => 0,
            'payment_received_at' => null,
        ]);

        $key = Receivable::encodeGroupKeyForRoute('code:OR-1');
        $this->actingAs($user)->post(
            route('receivables.group.payments.store', ['groupKey' => $key]),
            ['payment_date' => '2026-03-01', 'amount' => '25.00']
        );

        $gp = ReceivableGroupPayment::query()->first();
        $this->assertNotNull($gp);

        Receivable::query()->where('invoice_number', 'O-1')->delete();

        $del = $this->actingAs($user)->delete(
            route('receivables.group.payments.destroy', ['groupKey' => $key, 'receivableGroupPayment' => $gp])
        );

        $del->assertRedirect(route('receivables.group', ['groupKey' => $key]));
        $del->assertSessionHas('success');
        $this->assertSame(0, ReceivableGroupPayment::query()->count());
    }
}
