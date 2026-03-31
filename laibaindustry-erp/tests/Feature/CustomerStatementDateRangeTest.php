<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomerStatementDateRangeTest extends TestCase
{
    use RefreshDatabase;

    private function actingManager(): User
    {
        return User::factory()->create(['role' => 'manager']);
    }

    private function customerWithLedger(): Customer
    {
        $customer = Customer::create([
            'customer_code' => 'STMT-001',
            'customer_name' => 'Statement Test Co',
            'phone' => null,
            'email' => 'stmt@test.example',
            'address' => null,
            'opening_balance' => 100,
            'opening_balance_date' => '2025-01-01',
        ]);

        CustomerLedgerEntry::create([
            'customer_id' => $customer->id,
            'date' => '2025-01-05 10:00:00',
            'description' => 'January sale',
            'reference' => null,
            'debit' => 10,
            'credit' => 0,
            'source_type' => 'sale',
            'source_id' => null,
            'notes' => null,
        ]);

        CustomerLedgerEntry::create([
            'customer_id' => $customer->id,
            'date' => '2025-03-01 11:00:00',
            'description' => 'March payment',
            'reference' => null,
            'debit' => 0,
            'credit' => 40,
            'source_type' => 'payment_received',
            'source_id' => null,
            'notes' => null,
        ]);

        return $customer;
    }

    public function test_unfiltered_statement_matches_full_ledger(): void
    {
        $user = $this->actingManager();
        $customer = $this->customerWithLedger();

        $response = $this->actingAs($user)->get(route('customers.statement', $customer));

        $response->assertOk();
        $response->assertViewHas('statementFiltered', false);
        $response->assertViewHas('openingBalance', 100.0);
        $response->assertViewHas('closingBalance', 70.0);
        $rows = $response->viewData('ledgerRows');
        $this->assertCount(2, $rows);
    }

    public function test_statement_hides_invoice_for_payment_received_not_for_sale(): void
    {
        $user = $this->actingManager();
        $customer = Customer::create([
            'customer_code' => 'STMT-INV',
            'customer_name' => 'Invoice Display Co',
            'phone' => null,
            'email' => null,
            'address' => null,
            'opening_balance' => 0,
            'opening_balance_date' => '2025-01-01',
        ]);

        CustomerLedgerEntry::create([
            'customer_id' => $customer->id,
            'date' => '2025-02-01 10:00:00',
            'description' => 'Sale invoice line',
            'reference' => 'INV-S-100',
            'debit' => 100,
            'credit' => 0,
            'source_type' => 'sale',
            'source_id' => null,
            'notes' => null,
        ]);

        CustomerLedgerEntry::create([
            'customer_id' => $customer->id,
            'date' => '2025-02-15 10:00:00',
            'description' => 'Payment Received',
            'reference' => 'INV-ALLOCATED-TO',
            'debit' => 0,
            'credit' => 25,
            'source_type' => 'payment_received',
            'source_id' => null,
            'notes' => null,
        ]);

        $response = $this->actingAs($user)->get(route('customers.statement', $customer));

        $response->assertOk();
        $rows = $response->viewData('ledgerRows');
        $this->assertCount(2, $rows);

        $saleRow = collect($rows)->firstWhere('description', 'Sale invoice line');
        $paymentRow = collect($rows)->firstWhere('source_type', 'payment_received');

        $this->assertNotNull($saleRow);
        $this->assertNotNull($paymentRow);
        $this->assertSame('INV-S-100', $saleRow['invoice_number']);
        $this->assertNull($paymentRow['invoice_number']);
    }

    public function test_statement_orders_same_date_by_created_at_not_id(): void
    {
        $user = $this->actingManager();
        $customer = Customer::create([
            'customer_code' => 'STMT-ORD',
            'customer_name' => 'Order Test Co',
            'phone' => null,
            'email' => null,
            'address' => null,
            'opening_balance' => 0,
            'opening_balance_date' => '2025-01-01',
        ]);

        $firstPosted = CustomerLedgerEntry::create([
            'customer_id' => $customer->id,
            'date' => '2025-06-15 00:00:00',
            'description' => 'Posted first',
            'reference' => null,
            'debit' => 10,
            'credit' => 0,
            'source_type' => 'sale',
            'source_id' => null,
            'notes' => null,
        ]);

        $secondPosted = CustomerLedgerEntry::create([
            'customer_id' => $customer->id,
            'date' => '2025-06-15 00:00:00',
            'description' => 'Posted second',
            'reference' => null,
            'debit' => 5,
            'credit' => 0,
            'source_type' => 'sale',
            'source_id' => null,
            'notes' => null,
        ]);

        $this->assertLessThan($secondPosted->id, $firstPosted->id);

        DB::table('customer_ledger_entries')->where('id', $firstPosted->id)->update([
            'created_at' => '2025-06-16 12:00:00',
            'updated_at' => '2025-06-16 12:00:00',
        ]);
        DB::table('customer_ledger_entries')->where('id', $secondPosted->id)->update([
            'created_at' => '2025-06-15 08:00:00',
            'updated_at' => '2025-06-15 08:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('customers.statement', $customer));

        $response->assertOk();
        $rows = $response->viewData('ledgerRows');
        $this->assertCount(2, $rows);
        $this->assertSame('Posted second', $rows[0]['description']);
        $this->assertSame('Posted first', $rows[1]['description']);
    }

    public function test_filtered_statement_uses_balance_brought_forward_and_period_totals(): void
    {
        $user = $this->actingManager();
        $customer = $this->customerWithLedger();

        $response = $this->actingAs($user)->get(route('customers.statement', [
            'customer' => $customer,
            'from' => '2025-02-01',
            'to' => '2025-12-31',
        ]));

        $response->assertOk();
        $response->assertViewHas('statementFiltered', true);
        $response->assertViewHas('openingBalance', 110.0);
        $response->assertViewHas('closingBalance', 70.0);
        $response->assertViewHas('totalDebit', 0.0);
        $response->assertViewHas('totalCredit', 40.0);

        $rows = $response->viewData('ledgerRows');
        $this->assertCount(1, $rows);
        $this->assertSame('March payment', $rows[0]['description']);
    }

    public function test_validation_redirects_when_end_before_start(): void
    {
        $user = $this->actingManager();
        $customer = $this->customerWithLedger();

        $response = $this->actingAs($user)->get(route('customers.statement', [
            'customer' => $customer,
            'from' => '2025-06-01',
            'to' => '2025-01-01',
        ]));

        $response->assertRedirect(route('customers.statement', $customer));
        $response->assertSessionHasErrors(['to']);
    }

    public function test_validation_requires_both_dates_when_one_is_present(): void
    {
        $user = $this->actingManager();
        $customer = $this->customerWithLedger();

        $response = $this->actingAs($user)->get(route('customers.statement', [
            'customer' => $customer,
            'from' => '2025-01-01',
        ]));

        $response->assertRedirect(route('customers.statement', $customer));
        $response->assertSessionHasErrors(['to']);
    }

    public function test_pdf_route_redirects_to_statement_with_errors_on_invalid_range(): void
    {
        $user = $this->actingManager();
        $customer = $this->customerWithLedger();

        $response = $this->actingAs($user)->get(route('customers.statement.pdf', [
            'customer' => $customer,
            'from' => '2025-06-01',
            'to' => '2025-01-01',
        ]));

        $response->assertRedirect(route('customers.statement', $customer));
        $response->assertSessionHasErrors(['to']);
    }

    public function test_validation_rejects_range_exceeding_max_months(): void
    {
        $user = $this->actingManager();
        $customer = $this->customerWithLedger();

        $response = $this->actingAs($user)->get(route('customers.statement', [
            'customer' => $customer,
            'from' => '2020-01-01',
            'to' => '2024-06-01',
        ]));

        $response->assertRedirect(route('customers.statement', $customer));
        $response->assertSessionHasErrors(['to']);
    }
}
