<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportSalesFromExcel extends Command
{
    protected $signature = 'sales:import-from-excel
                            {file : Path to SALES.xlsx}
                            {--dry-run : Only show what would be imported}
                            {--start-row=6 : First data row}
                            {--end-row=219 : Last data row}
                            {--date-col=A : Column for sale date}
                            {--invoice-col=B : Column for invoice number}
                            {--customer-col=D : Column for company/customer name (empty = same customer)}
                            {--item-col=E : Column for item description (product)}
                            {--qty-col=F : Column for quantity}
                            {--price-col=G : Column for unit price}
                            {--no-stock : Do not decrement product stock}';

    protected $description = 'Import sales from SALES.xlsx: group by customer (empty D = same customer), one sale per customer block with multiple line items';

    private float $taxRate = 15.0;

    public function handle(): int
    {
        $path = $this->argument('file');
        $dryRun = $this->option('dry-run');
        $startRow = (int) $this->option('start-row');
        $endRow = (int) $this->option('end-row');
        $dateCol = strtoupper($this->option('date-col') ?: 'A');
        $invoiceCol = strtoupper($this->option('invoice-col') ?: 'B');
        $customerCol = strtoupper($this->option('customer-col') ?: 'D');
        $itemCol = strtoupper($this->option('item-col') ?: 'E');
        $qtyCol = strtoupper($this->option('qty-col') ?: 'F');
        $priceCol = strtoupper($this->option('price-col') ?: 'G');
        $noStock = $this->option('no-stock');

        if (! is_readable($path)) {
            $this->error("File not found or not readable: {$path}");
            return self::FAILURE;
        }

        $maxRows = $endRow + 5;
        $this->info('Reading rows ' . $startRow . '–' . $endRow . ' (customer in ' . $customerCol . ', item in ' . $itemCol . '; empty customer = same as above)...');
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadFilter(new class($maxRows) implements IReadFilter {
            private int $maxRows;
            public function __construct(int $maxRows) { $this->maxRows = $maxRows; }
            public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool { return $row <= $this->maxRows; }
        });
        $sheet = $reader->load($path)->getActiveSheet();

        // Build list of line items with customer carried forward (empty D = same customer as previous row)
        $lines = [];
        $currentCustomer = null;
        for ($row = $startRow; $row <= $endRow; $row++) {
            $customerCell = trim((string) $sheet->getCell($customerCol . $row)->getValue());
            if ($customerCell !== '') {
                $currentCustomer = $customerCell;
            }
            $itemName = trim((string) $sheet->getCell($itemCol . $row)->getValue());
            if ($itemName === '') {
                continue;
            }
            if ($currentCustomer === null) {
                continue;
            }
            $dateVal = $sheet->getCell($dateCol . $row)->getValue();
            $invoiceVal = trim((string) $sheet->getCell($invoiceCol . $row)->getValue());
            $qty = (int) $sheet->getCell($qtyCol . $row)->getValue() ?: 1;
            $price = (float) $sheet->getCell($priceCol . $row)->getValue() ?: 0.0;
            if ($qty < 1) {
                $qty = 1;
            }
            $lines[] = [
                'row' => $row,
                'customer_name' => $currentCustomer,
                'date_val' => $dateVal,
                'invoice' => $invoiceVal,
                'item_name' => $itemName,
                'qty' => $qty,
                'price' => $price,
            ];
        }

        // Group consecutive lines by same customer => each group = one sale with multiple line items
        $groups = [];
        $currentGroup = null;
        foreach ($lines as $line) {
            if ($currentGroup === null || $currentGroup['customer_name'] !== $line['customer_name']) {
                $currentGroup = [
                    'customer_name' => $line['customer_name'],
                    'date_val' => $line['date_val'],
                    'invoice' => $line['invoice'],
                    'lines' => [],
                ];
                $groups[] = &$currentGroup;
            }
            $currentGroup['lines'][] = $line;
        }

        $this->info('Grouped into ' . count($groups) . ' sale(s) (one per customer block).');

        $defaultCurrencyId = Currency::query()->where('is_default', true)->value('id');
        if (! $defaultCurrencyId) {
            $this->error('No default currency found.');
            return self::FAILURE;
        }

        $created = 0;
        $skipped = 0;
        $errors = [];
        $bar = $this->output->createProgressBar(count($groups));
        $bar->start();

        foreach ($groups as $group) {
            $customerName = $group['customer_name'];
            $customer = Customer::query()->where('customer_name', $customerName)->first();
            if (! $customer) {
                $errors[] = "Customer not found: \"{$customerName}\" (block with " . count($group['lines']) . ' lines)';
                $skipped++;
                $bar->advance();
                continue;
            }

            $saleDate = $this->parseDate($group['date_val']);
            $invoiceNumber = $group['invoice'] !== '' ? $group['invoice'] : ('IMP-' . $created . '-' . $saleDate->format('Ymd'));

            $itemsForSale = [];
            foreach ($group['lines'] as $line) {
                $product = Product::query()->where('name', $line['item_name'])->first();
                if (! $product) {
                    $errors[] = "Row {$line['row']}: Product not found: \"{$line['item_name']}\"";
                    continue;
                }
                if (! $noStock && $product->stock_quantity < $line['qty']) {
                    $errors[] = "Row {$line['row']}: Insufficient stock for \"{$line['item_name']}\" (need {$line['qty']}, have {$product->stock_quantity})";
                    continue;
                }
                $itemsForSale[] = [
                    'product' => $product,
                    'qty' => $line['qty'],
                    'price' => $line['price'],
                ];
            }

            if (empty($itemsForSale)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            if ($dryRun) {
                $created++;
                $bar->advance();
                continue;
            }

            try {
                DB::beginTransaction();
                $subtotal = 0;
                foreach ($itemsForSale as $item) {
                    $subtotal += $item['price'] * $item['qty'];
                }
                $taxAmount = round($subtotal * ($this->taxRate / 100), 2);
                $totalAmount = round($subtotal + $taxAmount, 2);

                $sale = Sale::create([
                    'date' => $saleDate,
                    'customer_code' => $customer->customer_code,
                    'customer_name' => $customer->customer_name,
                    'invoice_number' => $invoiceNumber,
                    'subtotal' => round($subtotal, 2),
                    'tax_amount' => $taxAmount,
                    'discount_amount' => 0,
                    'total_amount' => $totalAmount,
                    'tax_rate' => $this->taxRate,
                    'currency_id' => $defaultCurrencyId,
                    'exchange_rate' => null,
                    'status' => 'completed',
                ]);

                foreach ($itemsForSale as $item) {
                    $product = $item['product'];
                    $qty = $item['qty'];
                    $price = $item['price'];
                    $costPrice = (float) $product->cost_price;
                    $profit = round(($price - $costPrice) * $qty, 2);
                    $lineTax = round($price * $qty * ($this->taxRate / 100), 2);

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'cost_price' => $costPrice,
                        'selling_price' => $price,
                        'profit' => $profit,
                        'tax_applied' => $lineTax,
                    ]);
                    if (! $noStock) {
                        $product->decrement('stock_quantity', $qty);
                    }
                }

                Receivable::create([
                    'date' => $saleDate,
                    'invoice_number' => $invoiceNumber,
                    'customer_name' => $customer->customer_name,
                    'customer_code' => $customer->customer_code,
                    'amount' => $totalAmount,
                    'received' => 0,
                ]);

                DB::commit();
                $created++;
            } catch (\Throwable $e) {
                DB::rollBack();
                $errors[] = 'Block ' . $customerName . ': ' . $e->getMessage();
                $skipped++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. Created {$created} sale(s), skipped {$skipped}.");
        if (count($errors) > 0) {
            $this->warn('Errors:');
            foreach (array_slice($errors, 0, 25) as $err) {
                $this->line('  ' . $err);
            }
            if (count($errors) > 25) {
                $this->line('  ... and ' . (count($errors) - 25) . ' more.');
            }
        }
        return self::SUCCESS;
    }

    private function parseDate($value): Carbon
    {
        if (is_numeric($value)) {
            try {
                $dt = ExcelDate::excelToDateTimeObject((float) $value);
                return Carbon::instance($dt);
            } catch (\Throwable $e) {
            }
        }
        $str = trim((string) $value);
        if ($str !== '') {
            try {
                return Carbon::parse($str);
            } catch (\Throwable $e) {
            }
        }
        return Carbon::today();
    }
}
