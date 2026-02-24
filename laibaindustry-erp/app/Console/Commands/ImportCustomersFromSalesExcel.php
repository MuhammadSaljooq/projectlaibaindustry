<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class ImportCustomersFromSalesExcel extends Command
{
    protected $signature = 'sales:import-customers-from-excel
                            {file : Path to SALES.xlsx}
                            {--dry-run : Only list unique names}
                            {--customer-col=D : Column for company/customer name}
                            {--start-row=6 : First row}
                            {--end-row=219 : Last row}';

    protected $description = 'Import unique company names from SALES.xlsx as customers';

    public function handle(): int
    {
        $path = $this->argument('file');
        $dryRun = $this->option('dry-run');
        $col = strtoupper($this->option('customer-col') ?: 'D');
        $startRow = (int) $this->option('start-row');
        $endRow = (int) $this->option('end-row');

        if (! is_readable($path)) {
            $this->error("File not found or not readable: {$path}");
            return self::FAILURE;
        }

        $maxRows = $endRow + 5;
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadFilter(new class($maxRows) implements IReadFilter {
            private int $maxRows;
            public function __construct(int $maxRows) { $this->maxRows = $maxRows; }
            public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool { return $row <= $this->maxRows; }
        });
        $sheet = $reader->load($path)->getActiveSheet();

        $names = [];
        for ($row = $startRow; $row <= $endRow; $row++) {
            $value = trim((string) $sheet->getCell($col . $row)->getValue());
            if ($value !== '') {
                $names[$value] = true;
            }
        }
        $unique = array_keys($names);
        $count = count($unique);
        $this->info("Found {$count} unique customer name(s).");

        if ($dryRun) {
            foreach ($unique as $i => $n) {
                $this->line('  ' . ($i + 1) . '. ' . $n);
            }
            return self::SUCCESS;
        }

        $created = 0;
        $skipped = 0;
        foreach ($unique as $name) {
            if (Customer::query()->where('customer_name', $name)->exists()) {
                $skipped++;
                continue;
            }
            $code = $this->uniqueCode($name);
            Customer::create([
                'customer_code' => $code,
                'customer_name' => $name,
                'phone' => null,
                'email' => null,
                'address' => null,
            ]);
            $created++;
        }
        $this->info("Created {$created} customer(s), skipped {$skipped}.");
        return self::SUCCESS;
    }

    private function uniqueCode(string $name): string
    {
        $base = 'CUST-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 8)) ?: 'CUST';
        $base = substr($base, 0, 95);
        $code = $base;
        $n = 0;
        while (Customer::query()->where('customer_code', $code)->exists()) {
            $n++;
            $code = substr($base, 0, 92) . '-' . $n;
        }
        return $code;
    }
}
