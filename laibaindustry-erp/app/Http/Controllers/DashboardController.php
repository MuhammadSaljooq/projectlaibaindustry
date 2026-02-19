<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $currencySymbol = config('app.currency_symbol', '$');

        $totalRevenue = Sale::sum('total_amount');
        $openInvoicesCount = Receivable::whereRaw('amount > received')->count();
        $totalCustomers = Customer::count();
        $netProfit = SaleItem::sum('profit');

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

        $recentSales = Sale::with('items')
            ->orderByDesc('date')
            ->take(5)
            ->get();

        $recentCustomers = Customer::orderByDesc('created_at')->take(3)->get();

        $activities = collect();
        foreach ($recentSales as $sale) {
            $activities->push((object) [
                'type' => 'sale',
                'icon' => 'receipt_long',
                'iconBg' => 'bg-green-100 text-green-600 dark:bg-green-900/20 dark:text-green-400',
                'message' => "Sale #{$sale->invoice_number} for " . ($sale->customer_name ?: 'Walk-in') . ' - ' . $currencySymbol . number_format($sale->total_amount, 2),
                'time' => $sale->date->diffForHumans(),
            ]);
        }
        foreach ($recentCustomers as $customer) {
            $activities->push((object) [
                'type' => 'customer',
                'icon' => 'person_add',
                'iconBg' => 'bg-blue-100 text-primary dark:bg-blue-900/20 dark:text-blue-400',
                'message' => 'New customer: ' . $customer->customer_name,
                'time' => $customer->created_at->diffForHumans(),
            ]);
        }
        foreach ($lowStockProducts as $product) {
            $activities->push((object) [
                'type' => 'low_stock',
                'icon' => 'inventory',
                'iconBg' => 'bg-orange-100 text-orange-600 dark:bg-orange-900/20 dark:text-orange-400',
                'message' => 'Low stock: ' . $product->name . ' (' . $product->stock_quantity . ' left)',
                'time' => 'Stock alert',
            ]);
        }
        $activities = $activities->take(5)->values();

        return view('dashboard', [
            'currencySymbol' => $currencySymbol,
            'totalRevenue' => $totalRevenue,
            'openInvoicesCount' => $openInvoicesCount,
            'totalCustomers' => $totalCustomers,
            'netProfit' => $netProfit,
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
