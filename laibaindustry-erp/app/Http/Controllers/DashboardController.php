<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Currency;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\VatEntry;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $currencySymbol = Currency::query()->where('is_default', true)->value('symbol') ?? '$';

        $totalRevenue = Sale::sum('total_amount');
        $openInvoicesCount = Receivable::whereRaw('amount > received')->count();
        $totalCustomers = Customer::count();
        $totalExpenses = Expense::sum('amount');
        $netProfit = SaleItem::sum('profit') - $totalExpenses;

        $vatTotals = VatEntry::query()
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'sale'     THEN vat_amount ELSE 0 END), 0) AS sales_vat,
                COALESCE(SUM(CASE WHEN type = 'purchase' THEN vat_amount ELSE 0 END), 0) AS purchase_vat
            ")
            ->first();
        $netVat = (float) $vatTotals->sales_vat - (float) $vatTotals->purchase_vat;

        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
        $salesByMonth = Sale::query()
            ->where('date', '>=', $sixMonthsAgo)
            ->get()
            ->groupBy(fn ($s) => $s->date->format('Y-m'))
            ->map(fn ($group) => $group->sum('total_amount'))
            ->toArray();

        $chartLabels = [];
        $chartValues = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $key = $m->format('Y-m');
            $chartLabels[] = $m->format('M');
            $chartValues[] = (float) ($salesByMonth[$key] ?? 0);
        }

        $chartMax = max(1, max($chartValues));
        $trend = $this->computeTrend($chartValues);
        $salesOverviewTotal = array_sum($chartValues);

        $lowStockProducts = Product::lowStock()->orderBy('stock_quantity')->take(5)->get();

        $profitMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;

        $recentSales = Sale::with('items')
            ->orderByDesc('date')
            ->take(5)
            ->get();

        $recentPurchases = Purchase::orderByDesc('date')->take(3)->get();
        $recentExpenses = Expense::orderByDesc('date')->take(3)->get();

        $transactions = collect();
        foreach ($recentSales as $sale) {
            $transactions->push((object) [
                'type' => 'sale',
                'icon' => 'receipt',
                'label' => "Sale #{$sale->invoice_number} - " . ($sale->customer_name ?: 'Walk-in'),
                'detail' => $sale->date->format('M d, Y') . ' • Sale',
                'amount' => '+' . $currencySymbol . ' ' . number_format($sale->total_amount, 2),
                'amountClass' => 'text-emerald-400',
                'status' => 'Completed',
                'statusClass' => 'text-emerald-400/70',
                'sortDate' => $sale->date,
            ]);
        }
        foreach ($recentPurchases as $purchase) {
            $transactions->push((object) [
                'type' => 'purchase',
                'icon' => 'shopping_bag',
                'label' => "Purchase #{$purchase->invoice_number} - " . ($purchase->customer_name ?: 'Supplier'),
                'detail' => $purchase->date->format('M d, Y') . ' • Purchase',
                'amount' => '-' . $currencySymbol . ' ' . number_format($purchase->total_amount, 2),
                'amountClass' => 'text-white',
                'status' => 'Completed',
                'statusClass' => 'text-[#8e9192]',
                'sortDate' => $purchase->date,
            ]);
        }
        foreach ($recentExpenses as $expense) {
            $transactions->push((object) [
                'type' => 'expense',
                'icon' => 'account_balance_wallet',
                'label' => $expense->type,
                'detail' => $expense->date->format('M d, Y') . ' • Operating Expense',
                'amount' => '-' . $currencySymbol . ' ' . number_format($expense->amount, 2),
                'amountClass' => 'text-white',
                'status' => 'Completed',
                'statusClass' => 'text-[#8e9192]',
                'sortDate' => $expense->date,
            ]);
        }
        $transactions = $transactions->sortByDesc('sortDate')->take(6)->values();

        $activities = collect();
        foreach ($recentSales as $sale) {
            $activities->push((object) [
                'type' => 'sale',
                'icon' => 'receipt_long',
                'iconBg' => 'bg-green-100 text-green-600 dark:bg-green-900/20 dark:text-green-400',
                'message' => "Sale #{$sale->invoice_number} for " . ($sale->customer_name ?: 'Walk-in') . ' - ' . $currencySymbol . ' ' . number_format($sale->total_amount, 2),
                'time' => $sale->date->diffForHumans(),
            ]);
        }
        $activities = $activities->take(5)->values();

        return view('dashboard', [
            'currencySymbol' => $currencySymbol,
            'totalRevenue' => $totalRevenue,
            'openInvoicesCount' => $openInvoicesCount,
            'totalCustomers' => $totalCustomers,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
            'profitMargin' => $profitMargin,
            'salesVat' => (float) $vatTotals->sales_vat,
            'purchaseVat' => (float) $vatTotals->purchase_vat,
            'netVat' => $netVat,
            'transactions' => $transactions,
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
            'chartMax' => $chartMax,
            'trend' => $trend,
            'salesOverviewTotal' => $salesOverviewTotal,
            'lowStockProducts' => $lowStockProducts,
            'activities' => $activities,
        ]);
    }

    private function computeTrend(array $values): ?float
    {
        if (count($values) < 2) {
            return null;
        }
        $first = $values[0];
        $last = $values[count($values) - 1];
        if ($first == 0) {
            return $last > 0 ? 100 : null;
        }
        return round((($last - $first) / $first) * 100, 1);
    }
}
