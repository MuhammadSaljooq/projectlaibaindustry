<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        return User::factory()->create(['role' => 'manager']);
    }

    private function seedFixtures(): array
    {
        $category = Category::create(['name' => 'Safety Gear', 'description' => null]);
        $currency = Currency::create([
            'code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$',
            'is_default' => true, 'is_active' => true, 'decimal_places' => 2,
        ]);

        $productA = Product::create([
            'name' => 'Hard Hat', 'sku' => 'HH-001', 'category_id' => $category->id,
            'cost_price' => 10.00, 'selling_price' => 25.00, 'currency_id' => $currency->id,
            'stock_quantity' => 50, 'reorder_level' => 10,
        ]);
        $productB = Product::create([
            'name' => 'Safety Vest', 'sku' => 'SV-002', 'category_id' => $category->id,
            'cost_price' => 5.00, 'selling_price' => 15.00, 'currency_id' => $currency->id,
            'stock_quantity' => 100, 'reorder_level' => 10,
        ]);

        $saleA = Sale::create([
            'date' => '2026-03-15 10:00:00',
            'customer_code' => 'CUST-01', 'customer_name' => 'Acme Corp',
            'invoice_number' => 'INV-001',
            'subtotal' => 50.00, 'tax_amount' => 7.50, 'discount_amount' => 0,
            'total_amount' => 57.50, 'tax_rate' => 15, 'currency_id' => $currency->id,
            'exchange_rate' => null, 'status' => 'completed',
        ]);
        SaleItem::create([
            'sale_id' => $saleA->id, 'product_id' => $productA->id,
            'quantity' => 2, 'cost_price' => 10.00, 'selling_price' => 25.00,
            'profit' => 30.00, 'tax_applied' => 7.50,
        ]);

        $saleB = Sale::create([
            'date' => '2026-04-01 09:00:00',
            'customer_code' => 'CUST-02', 'customer_name' => 'Beta Ltd',
            'invoice_number' => 'INV-002',
            'subtotal' => 30.00, 'tax_amount' => 4.50, 'discount_amount' => 0,
            'total_amount' => 34.50, 'tax_rate' => 15, 'currency_id' => $currency->id,
            'exchange_rate' => null, 'status' => 'completed',
        ]);
        SaleItem::create([
            'sale_id' => $saleB->id, 'product_id' => $productB->id,
            'quantity' => 2, 'cost_price' => 5.00, 'selling_price' => 15.00,
            'profit' => 20.00, 'tax_applied' => 4.50,
        ]);

        return compact('productA', 'productB', 'saleA', 'saleB');
    }

    public function test_global_history_lists_customer_product_qty_and_line_total(): void
    {
        $user = $this->manager();
        $this->seedFixtures();

        $response = $this->actingAs($user)->get(route('inventory.history'));

        $response->assertOk();
        $response->assertSee('Acme Corp');
        $response->assertSee('Hard Hat');
        $response->assertSee('INV-001');
        $response->assertSee('Beta Ltd');
        $response->assertSee('Safety Vest');
        $response->assertSee('INV-002');
    }

    public function test_product_filter_shows_only_that_products_rows(): void
    {
        $user = $this->manager();
        ['productA' => $productA] = $this->seedFixtures();

        $response = $this->actingAs($user)->get(route('inventory.history', ['product_id' => $productA->id]));

        $response->assertOk();
        // Acme Corp / INV-001 belong to productA — must appear
        $response->assertSee('Acme Corp');
        $response->assertSee('INV-001');
        // Beta Ltd / INV-002 belong to productB — must not appear in table rows
        $response->assertDontSee('Beta Ltd');
        $response->assertDontSee('INV-002');
    }

    public function test_date_filter_excludes_out_of_range_sales(): void
    {
        $user = $this->manager();
        $this->seedFixtures();

        // Only request sales from April onwards — should exclude the March sale (Acme/INV-001)
        $response = $this->actingAs($user)->get(route('inventory.history', ['from' => '01/04/2026']));

        $response->assertOk();
        $response->assertSee('Beta Ltd');
        $response->assertSee('INV-002');
        $response->assertDontSee('Acme Corp');
        $response->assertDontSee('INV-001');
    }

    public function test_customer_filter_matches_by_name_and_code(): void
    {
        $user = $this->manager();
        $this->seedFixtures();

        // Match by name
        $response = $this->actingAs($user)->get(route('inventory.history', ['customer' => 'Acme']));
        $response->assertOk();
        $response->assertSee('Acme Corp');
        $response->assertDontSee('Beta Ltd');

        // Match by customer_code
        $response = $this->actingAs($user)->get(route('inventory.history', ['customer' => 'CUST-02']));
        $response->assertOk();
        $response->assertSee('Beta Ltd');
        $response->assertDontSee('Acme Corp');
    }

    public function test_product_history_page_is_scoped_to_single_product(): void
    {
        $user = $this->manager();
        ['productA' => $productA] = $this->seedFixtures();

        $response = $this->actingAs($user)->get(route('products.history', $productA));

        $response->assertOk();
        $response->assertSee('Hard Hat');
        $response->assertSee('Acme Corp');
        $response->assertSee('INV-001');
        $response->assertDontSee('Safety Vest');
        $response->assertDontSee('Beta Ltd');
    }

    public function test_export_returns_csv_with_correct_headers(): void
    {
        $user = $this->manager();
        $this->seedFixtures();

        $response = $this->actingAs($user)->get(route('inventory.history.export'));

        $response->assertOk();
        $this->assertStringStartsWith('text/csv', $response->headers->get('Content-Type'));
        $content = $response->streamedContent();
        $this->assertStringContainsString('Date', $content);
        $this->assertStringContainsString('Hard Hat', $content);
        $this->assertStringContainsString('Safety Vest', $content);
    }
}
