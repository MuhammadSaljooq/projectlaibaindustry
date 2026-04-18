<?php

namespace App\Http\Controllers;

use App\Models\BankStatementEntry;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\InternationalPayablePayment;
use App\Models\InternationalPurchaseOrder;
use App\Models\Payable;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\VatEntry;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        $totalSales = (float) Sale::query()->sum('total_amount');
        $salesCount = Sale::query()->count();
        $totalPurchases = (float) Purchase::query()->sum('total_amount');
        $purchasesCount = Purchase::query()->count();
        $totalExpenses = (float) Expense::query()->sum('amount');
        $totalCustomers = Customer::query()->count();
        $productCount = Product::query()->count();
        $lowStockCount = Product::query()->lowStock()->count();

        $receivableTotals = Receivable::query()
            ->selectRaw('
                COALESCE(SUM(amount), 0)                   AS total_amount,
                COALESCE(SUM(received), 0)                 AS total_received,
                COALESCE(SUM(amount) - SUM(received), 0)   AS total_remaining
            ')
            ->first();

        $payableTotals = Payable::query()
            ->selectRaw('
                COALESCE(SUM(amount), 0)                   AS total_amount,
                COALESCE(SUM(received), 0)                 AS total_received,
                COALESCE(SUM(amount) - SUM(received), 0)   AS total_outstanding
            ')
            ->first();

        // International Purchases
        $intlPurchaseTotal  = (float) InternationalPurchaseOrder::query()->sum('total_amount');
        $intlPurchaseCount  = InternationalPurchaseOrder::query()->count();

        // International Payables
        $intlPaidTotal      = (float) InternationalPayablePayment::query()->sum('amount');
        $intlOutstanding    = max(0, $intlPurchaseTotal - $intlPaidTotal);

        // Bank Statement
        $bankInflow  = (float) BankStatementEntry::query()->where('flow_type', BankStatementEntry::FLOW_INFLOW)->sum('amount');
        $bankOutflow = (float) BankStatementEntry::query()->where('flow_type', BankStatementEntry::FLOW_OUTFLOW)->sum('amount');
        $bankNet     = $bankInflow - $bankOutflow;

        $vatTotals = VatEntry::query()
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'sale'     THEN vat_amount ELSE 0 END), 0) AS sales_vat,
                COALESCE(SUM(CASE WHEN type = 'purchase' THEN vat_amount ELSE 0 END), 0) AS purchase_vat
            ")
            ->first();
        $salesVat = (float) $vatTotals->sales_vat;
        $purchaseVat = (float) $vatTotals->purchase_vat;
        $netVat = $salesVat - $purchaseVat;

        $m = fn (float $n, int $d = 2): string => $currencySymbol.number_format($n, $d);
        $m0 = fn (float $n): string => $currencySymbol.number_format($n, 0);

        $sections = [
            [
                'title' => 'Inventory',
                'icon' => 'inventory_2',
                'route' => route('inventory.dashboard', absolute: false),
                'link_label' => 'View inventory',
                'metrics' => [
                    ['label' => 'Products', 'value' => (string) $productCount],
                    ['label' => 'Low stock items', 'value' => (string) $lowStockCount],
                ],
                'actions' => [],
            ],
            [
                'title' => 'Sales',
                'icon' => 'payments',
                'route' => route('sales.index', absolute: false),
                'link_label' => 'View sales',
                'metrics' => [
                    ['label' => 'Total sales', 'value' => $m($totalSales)],
                    ['label' => 'Sale records', 'value' => (string) $salesCount],
                ],
                'actions' => [],
            ],
            [
                'title' => 'Customers',
                'icon' => 'group',
                'route' => route('customers.index', absolute: false),
                'link_label' => 'View customers',
                'metrics' => [
                    ['label' => 'Total customers', 'value' => (string) $totalCustomers],
                ],
                'actions' => [],
            ],
            [
                'title' => 'Receivable',
                'icon' => 'account_balance_wallet',
                'route' => route('receivables.index', absolute: false),
                'link_label' => 'View receivables',
                'metrics' => [
                    ['label' => 'Total invoiced', 'value' => $m((float) $receivableTotals->total_amount)],
                    ['label' => 'Total received', 'value' => $m((float) $receivableTotals->total_received)],
                    ['label' => 'Outstanding', 'value' => $m((float) $receivableTotals->total_remaining)],
                ],
                'actions' => [],
            ],
            [
                'title' => 'Purchases',
                'icon' => 'shopping_cart',
                'route' => route('purchases.index', absolute: false),
                'link_label' => 'View purchases',
                'metrics' => [
                    ['label' => 'Total purchases', 'value' => $m($totalPurchases)],
                    ['label' => 'Purchase records', 'value' => (string) $purchasesCount],
                ],
                'actions' => [],
            ],
            [
                'title' => 'Payables',
                'icon' => 'account_balance',
                'route' => route('payables.index', absolute: false),
                'link_label' => 'View payables',
                'metrics' => [
                    ['label' => 'Total payable', 'value' => $m((float) $payableTotals->total_amount)],
                    ['label' => 'Amount paid', 'value' => $m((float) $payableTotals->total_received)],
                    ['label' => 'Outstanding', 'value' => $m((float) $payableTotals->total_outstanding)],
                ],
                'actions' => [],
            ],
            [
                'title' => 'Expenses',
                'icon' => 'receipt_long',
                'route' => route('expenses.index', absolute: false),
                'link_label' => 'View expenses',
                'metrics' => [
                    ['label' => 'Total expenses', 'value' => $m($totalExpenses)],
                ],
                'actions' => [],
            ],
            [
                'title' => 'VAT',
                'icon' => 'percent',
                'route' => route('vat.index', absolute: false),
                'link_label' => 'View VAT',
                'metrics' => [
                    ['label' => 'Sales VAT', 'value' => $m0($salesVat)],
                    ['label' => 'Purchase VAT', 'value' => $m0($purchaseVat)],
                    ['label' => 'Net VAT', 'value' => $m0($netVat)],
                ],
                'actions' => [],
            ],
            [
                'title' => 'International Purchases',
                'icon' => 'flight_takeoff',
                'route' => route('international-purchases.index', absolute: false),
                'link_label' => 'View international purchases',
                'metrics' => [
                    ['label' => 'Total invoiced', 'value' => 'USD '.number_format($intlPurchaseTotal, 2)],
                    ['label' => 'Invoice records', 'value' => (string) $intlPurchaseCount],
                ],
                'actions' => [],
            ],
            [
                'title' => 'International Payables',
                'icon' => 'public',
                'route' => route('international-payables.index', absolute: false),
                'link_label' => 'View international payables',
                'metrics' => [
                    ['label' => 'Total billed', 'value' => 'USD '.number_format($intlPurchaseTotal, 2)],
                    ['label' => 'Amount paid', 'value' => 'USD '.number_format($intlPaidTotal, 2)],
                    ['label' => 'Outstanding', 'value' => 'USD '.number_format($intlOutstanding, 2)],
                ],
                'actions' => [],
            ],
            [
                'title' => 'Bank Statement',
                'icon' => 'account_balance',
                'route' => route('bank-statement.index', absolute: false),
                'link_label' => 'View bank statement',
                'metrics' => [
                    ['label' => 'Total inflow', 'value' => $m($bankInflow)],
                    ['label' => 'Total outflow', 'value' => $m($bankOutflow)],
                    ['label' => 'Net balance', 'value' => $m($bankNet)],
                ],
                'actions' => [],
            ],
        ];

        return view('dashboard', [
            'currencySymbol' => $currencySymbol,
            'sections' => $sections,
        ]);
    }
}
