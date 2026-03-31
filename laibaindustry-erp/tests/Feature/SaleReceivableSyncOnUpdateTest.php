<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\TaxSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleReceivableSyncOnUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        return User::factory()->create(['role' => 'manager']);
    }

    public function test_sale_update_syncs_receivable_when_receivable_amount_differs_from_sale_total(): void
    {
        $user = $this->manager();

        $category = Category::create([
            'name' => 'General',
            'description' => 'Test',
        ]);
        $currency = Currency::create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'is_default' => true,
            'is_active' => true,
            'decimal_places' => 2,
        ]);
        TaxSetting::create([
            'default_rate' => 15.00,
            'description' => 'VAT',
        ]);

        $product = Product::create([
            'name' => 'Widget',
            'sku' => 'W-SYNC-1',
            'category_id' => $category->id,
            'cost_price' => 10.00,
            'selling_price' => 100.00,
            'currency_id' => $currency->id,
            'description' => null,
            'stock_quantity' => 50,
            'reorder_level' => 10,
        ]);

        $sale = Sale::create([
            'date' => '2026-03-01 10:00:00',
            'customer_code' => 'SYNC1',
            'customer_name' => 'Sync Customer',
            'invoice_number' => 'INV-SYNC-1',
            'subtotal' => 100.00,
            'tax_amount' => 15.00,
            'discount_amount' => 0,
            'total_amount' => 115.00,
            'tax_rate' => 15.00,
            'currency_id' => $currency->id,
            'exchange_rate' => null,
            'status' => 'completed',
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'cost_price' => 10.00,
            'selling_price' => 100.00,
            'profit' => 90.00,
            'tax_applied' => 15.00,
        ]);

        Receivable::create([
            'date' => '2026-03-01 10:00:00',
            'invoice_number' => 'INV-SYNC-1',
            'customer_name' => 'Sync Customer',
            'customer_code' => 'SYNC1',
            'amount' => 130.00,
            'received' => 50.00,
            'payment_received_at' => null,
        ]);

        $response = $this->actingAs($user)->put(route('sales.update', $sale), [
            'date' => '2026-03-01',
            'customer_code' => 'SYNC1',
            'customer_name' => 'Sync Customer',
            'invoice_number' => 'INV-SYNC-1',
            'items' => [
                [
                    'product_id' => (string) $product->id,
                    'quantity' => '1',
                    'selling_price' => '60.00',
                ],
            ],
        ]);

        $response->assertRedirect();

        $receivable = Receivable::query()
            ->where('invoice_number', 'INV-SYNC-1')
            ->where('customer_code', 'SYNC1')
            ->first();

        $this->assertNotNull($receivable);
        $this->assertEqualsWithDelta(69.00, (float) $receivable->amount, 0.001,
            'Receivable amount must follow the updated sale total (max with received), even when the row no longer matched the old sale total.');
        $this->assertEqualsWithDelta(50.00, (float) $receivable->received, 0.001);
    }
}
