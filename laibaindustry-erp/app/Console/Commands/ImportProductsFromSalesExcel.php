<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Currency;
use App\Models\Product;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class ImportProductsFromSalesExcel extends Command
{
    protected $signature = 'sales:import-products-from-excel
                            {file : Path to SALES.xlsx}
                            {--dry-run : Only list unique item descriptions}
                            {--max-rows=250 : Max rows to read}
                            {--start-row=6 : First data row}
                            {--end-row=219 : Last data row}
                            {--item-col=E : Column for item description}
                            {--qty-col=F : Column for quantity}
                            {--price-col=G : Column for unit price}
                            {--update-existing : Update selling_price and stock_quantity for existing products}';

    protected $description = 'Import unique item descriptions from SALES.xlsx as products (with price and qty from file)';

    public function handle(): int
    {
        $path = $this->argument('file');
        $dryRun = $this->option('dry-run');
        $maxRows = (int) $this->option('max-rows');
        $startRow = (int) $this->option('start-row');
        $endRow = (int) $this->option('end-row');
        $itemCol = strtoupper($this->option('item-col') ?: 'E');
        $qtyCol = strtoupper($this->option('qty-col') ?: 'F');
        $priceCol = strtoupper($this->option('price-col') ?: 'G');
        $updateExisting = $this->option('update-existing');

        if (! is_readable($path)) {
            $this->error("File not found or not readable: {$path}");
            return self::FAILURE;
        }

        $maxRows = max($maxRows, $endRow);
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadFilter(new class($maxRows) implements IReadFilter {
            private int $maxRows;
            public function __construct(int $maxRows) { $this->maxRows = $maxRows; }
            public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool { return $row <= $this->maxRows; }
        });
        $sheet = $reader->load($path)->getActiveSheet();

        // For each product name: collect prices and sum of quantities
        $products = [];
        for ($row = $startRow; $row <= $endRow; $row++) {
            $name = trim((string) $sheet->getCell($itemCol . $row)->getValue());
            if ($name === '') {
                continue;
            }
            $qty = (int) $sheet->getCell($qtyCol . $row)->getValue() ?: 0;
            $price = (float) $sheet->getCell($priceCol . $row)->getValue() ?: 0.0;
            if (! isset($products[$name])) {
                $products[$name] = ['prices' => [], 'qty_sum' => 0];
            }
            if ($price > 0) {
                $products[$name]['prices'][] = $price;
            }
            $products[$name]['qty_sum'] += $qty;
        }

        $count = count($products);
        $this->info("Found {$count} unique product(s) with price/qty from columns {$priceCol} and {$qtyCol}.");

        if ($dryRun) {
            foreach ($products as $name => $data) {
                $avgPrice = count($data['prices']) > 0 ? round(array_sum($data['prices']) / count($data['prices']), 2) : 0;
                $this->line('  ' . $name . ' → price: ' . $avgPrice . ', qty sum: ' . $data['qty_sum']);
            }
            return self::SUCCESS;
        }

        $category = Category::query()->where('name', 'General')->first();
        if (! $category) {
            $this->error('Category "General" not found.');
            return self::FAILURE;
        }
        $defaultCurrencyId = Currency::query()->where('is_default', true)->value('id');

        $created = 0;
        $updated = 0;
        $skipped = 0;
        foreach ($products as $name => $data) {
            $sellingPrice = count($data['prices']) > 0
                ? round(array_sum($data['prices']) / count($data['prices']), 2)
                : null;
            $stockQty = max(0, $data['qty_sum']);

            $existing = Product::query()->where('name', $name)->first();
            if ($existing) {
                if ($updateExisting) {
                    $existing->update([
                        'selling_price' => $sellingPrice ?? $existing->selling_price,
                        'stock_quantity' => $stockQty,
                    ]);
                    $updated++;
                } else {
                    $skipped++;
                }
                continue;
            }

            $sku = $this->uniqueSku($name);
            Product::create([
                'name' => $name,
                'sku' => $sku,
                'category_id' => $category->id,
                'cost_price' => 0,
                'selling_price' => $sellingPrice,
                'currency_id' => $defaultCurrencyId,
                'description' => null,
                'stock_quantity' => $stockQty,
                'reorder_level' => 10,
            ]);
            $created++;
        }
        $this->info("Created {$created}, updated {$updated}, skipped {$skipped}.");
        return self::SUCCESS;
    }

    private function uniqueSku(string $name): string
    {
        $base = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 12)) ?: 'ITEM';
        $base = substr($base, 0, 90);
        $sku = $base;
        $n = 0;
        while (Product::query()->where('sku', $sku)->exists()) {
            $n++;
            $sku = substr($base, 0, 88) . '-' . $n;
        }
        return $sku;
    }
}
