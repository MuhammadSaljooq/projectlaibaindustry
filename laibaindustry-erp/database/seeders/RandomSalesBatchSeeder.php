<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\TaxSetting;
use App\Models\VatEntry;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Creates 50 random sales (invoice prefix RND-S-). Run: php artisan db:seed --class=RandomSalesBatchSeeder
 */
class RandomSalesBatchSeeder extends Seeder
{
    public function run(): void
    {
        $count = 50;

        $defaultCurrencyId = Currency::query()->where('is_default', true)->value('id');
        $taxRate = (float) (TaxSetting::query()->first()?->default_rate ?? 15.0);

        if (Product::query()->doesntExist()) {
            $this->command?->error('No products in database.');

            return;
        }

        Product::query()->increment('stock_quantity', 5000);

        $customers = Customer::query()->get();

        for ($n = 0; $n < $count; $n++) {
            DB::transaction(function () use ($defaultCurrencyId, $taxRate, $customers, $n) {
                $saleDate = Carbon::instance(fake()->dateTimeBetween('-8 months', 'now'));

                $useCustomer = $customers->isNotEmpty() && fake()->boolean(75);
                $customerCode = null;
                $customerName = null;
                if ($useCustomer) {
                    $c = $customers->random();
                    $customerCode = $c->customer_code;
                    $customerName = $c->customer_name;
                } else {
                    $customerName = fake()->boolean(40) ? fake()->name() : null;
                }

                $invoiceNumber = 'RND-S-'.now()->format('Ymd').'-'.strtoupper(bin2hex(random_bytes(3))).'-'.$n;

                $lineCount = fake()->numberBetween(1, 3);
                $lines = [];
                $pickedIds = [];

                for ($l = 0; $l < $lineCount; $l++) {
                    $product = Product::query()
                        ->where('stock_quantity', '>', 0)
                        ->when($pickedIds !== [], fn ($q) => $q->whereNotIn('id', $pickedIds))
                        ->inRandomOrder()
                        ->first();
                    if (! $product) {
                        break;
                    }
                    $pickedIds[] = $product->id;
                    $maxQty = min(12, (int) $product->stock_quantity);
                    $qty = fake()->numberBetween(1, max(1, $maxQty));
                    $price = (float) ($product->selling_price ?? $product->cost_price * 1.15);
                    $lines[] = ['product' => $product, 'qty' => $qty, 'price' => round($price, 2)];
                }

                if ($lines === []) {
                    return;
                }

                $subtotal = 0;
                foreach ($lines as $line) {
                    $subtotal += $line['price'] * $line['qty'];
                }
                $subtotal = round($subtotal, 2);
                $taxAmount = round($subtotal * ($taxRate / 100), 2);
                $totalAmount = round($subtotal + $taxAmount, 2);

                $sale = Sale::create([
                    'date' => $saleDate,
                    'customer_code' => $customerCode,
                    'customer_name' => $customerName,
                    'invoice_number' => $invoiceNumber,
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => 0,
                    'total_amount' => $totalAmount,
                    'tax_rate' => $taxRate,
                    'currency_id' => $defaultCurrencyId,
                    'exchange_rate' => null,
                    'status' => 'completed',
                ]);

                foreach ($lines as $line) {
                    $product = $line['product']->fresh();
                    $qty = $line['qty'];
                    $sellingPrice = $line['price'];
                    $costPrice = (float) ($product->cost_price ?? 0);
                    $lineAmount = $sellingPrice * $qty;
                    $lineTax = round($lineAmount * ($taxRate / 100), 2);
                    $profit = round(($sellingPrice - $costPrice) * $qty, 2);

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'cost_price' => $costPrice,
                        'selling_price' => $sellingPrice,
                        'profit' => $profit,
                        'tax_applied' => $lineTax,
                    ]);

                    $product->decrement('stock_quantity', $qty);
                }

                Receivable::create([
                    'date' => $saleDate,
                    'invoice_number' => $invoiceNumber,
                    'customer_name' => $customerName,
                    'customer_code' => $customerCode,
                    'amount' => $totalAmount,
                    'received' => 0,
                ]);

                $customerCodeTrim = trim((string) $customerCode);
                $customerNameTrim = trim((string) $customerName);
                if ($customerCodeTrim !== '') {
                    Customer::firstOrCreate(
                        ['customer_code' => $customerCodeTrim],
                        ['customer_name' => $customerNameTrim !== '' ? $customerNameTrim : $customerCodeTrim, 'phone' => null, 'email' => null, 'address' => null]
                    );
                }

                $customer = $customerCodeTrim !== '' ? Customer::where('customer_code', $customerCodeTrim)->first() : null;
                if ($customer) {
                    CustomerLedgerEntry::create([
                        'customer_id' => $customer->id,
                        'date' => $saleDate,
                        'description' => 'Sale Invoice',
                        'reference' => $invoiceNumber,
                        'debit' => $totalAmount,
                        'credit' => 0,
                        'source_type' => 'sale',
                        'source_id' => $sale->id,
                    ]);
                }

                VatEntry::create([
                    'type' => 'sale',
                    'source_type' => Sale::class,
                    'source_id' => $sale->id,
                    'date' => $saleDate,
                    'invoice_number' => $invoiceNumber,
                    'customer_name' => $customerName,
                    'customer_code' => $customerCode,
                    'subtotal' => $subtotal,
                    'vat_rate' => $taxRate,
                    'vat_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                ]);
            });
        }

        $created = Sale::query()->where('invoice_number', 'like', 'RND-S-%')->count();
        $this->command?->info("Sales with invoice RND-S-%: {$created}");
    }
}
