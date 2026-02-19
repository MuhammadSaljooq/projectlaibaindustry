<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Dashboard - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden font-display">
@include('products.partials.sidebar', ['activeNav' => 'dashboard'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-[#1a2632] border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<h2 class="text-xl font-bold text-slate-800 dark:text-white hidden sm:block">Dashboard Overview</h2>
</div>
</header>
<div class="flex-1 overflow-y-auto p-6 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-6">
<div class="sm:hidden">
<h2 class="text-2xl font-bold text-[#111418] dark:text-white">Welcome back, {{ auth()->user()->name }}!</h2>
<p class="text-[#637588] dark:text-slate-400">Here's what's happening with your business today.</p>
</div>
<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
<div class="rounded-xl border border-[#e5e7eb] dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm hover:shadow-md transition-shadow">
<div class="flex items-center justify-between mb-4">
<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400">
<span class="material-symbols-outlined">attach_money</span>
</div>
@if(isset($trend) && $trend !== null && $trend >= 0)
<span class="flex items-center gap-1 text-xs font-bold text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-2 py-1 rounded-full">
<span class="material-symbols-outlined text-sm">trending_up</span>
+{{ $trend }}%
</span>
@elseif(isset($trend) && $trend !== null)
<span class="flex items-center gap-1 text-xs font-bold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 px-2 py-1 rounded-full">
<span class="material-symbols-outlined text-sm">trending_down</span>
{{ $trend }}%
</span>
@endif
</div>
<p class="text-sm font-medium text-[#637588] dark:text-slate-400">Total Revenue</p>
<h3 class="text-2xl font-bold text-[#111418] dark:text-white mt-1">{{ ($currencySymbol ?? '$') }}{{ number_format($totalRevenue ?? 0, 2) }}</h3>
</div>
<div class="rounded-xl border border-[#e5e7eb] dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm hover:shadow-md transition-shadow">
<div class="flex items-center justify-between mb-4">
<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-primary dark:bg-blue-900/20 dark:text-blue-400">
<span class="material-symbols-outlined">receipt_long</span>
</div>
</div>
<p class="text-sm font-medium text-[#637588] dark:text-slate-400">Open Invoices</p>
<h3 class="text-2xl font-bold text-[#111418] dark:text-white mt-1">{{ number_format($openInvoicesCount ?? 0) }}</h3>
</div>
<div class="rounded-xl border border-[#e5e7eb] dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm hover:shadow-md transition-shadow">
<div class="flex items-center justify-between mb-4">
<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400">
<span class="material-symbols-outlined">person_add</span>
</div>
</div>
<p class="text-sm font-medium text-[#637588] dark:text-slate-400">Total Customers</p>
<h3 class="text-2xl font-bold text-[#111418] dark:text-white mt-1">{{ number_format($totalCustomers ?? 0) }}</h3>
</div>
<div class="rounded-xl border border-[#e5e7eb] dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm hover:shadow-md transition-shadow hidden xl:block">
<div class="flex items-center justify-between mb-4">
<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-50 text-orange-600 dark:bg-orange-900/20 dark:text-orange-400">
<span class="material-symbols-outlined">account_balance_wallet</span>
</div>
</div>
<p class="text-sm font-medium text-[#637588] dark:text-slate-400">Net Profit</p>
<h3 class="text-2xl font-bold text-[#111418] dark:text-white mt-1">{{ ($currencySymbol ?? '$') }}{{ number_format($netProfit ?? 0, 2) }}</h3>
</div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
<div class="lg:col-span-2 rounded-xl border border-[#e5e7eb] dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
<div class="flex items-center justify-between mb-6">
<div>
<h3 class="text-lg font-bold text-[#111418] dark:text-white">Sales Overview</h3>
<p class="text-sm text-[#637588] dark:text-slate-400">Performance over last 6 months</p>
</div>
<div class="flex items-center gap-2">
<span class="text-2xl font-bold text-[#111418] dark:text-white">{{ ($currencySymbol ?? '$') }}{{ number_format($salesOverviewTotal ?? 0, 2) }}</span>
@if(isset($trend) && $trend !== null)
<span class="text-sm font-medium {{ $trend >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} flex items-center {{ $trend >= 0 ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }} px-2 py-1 rounded">
<span class="material-symbols-outlined text-sm mr-1">{{ $trend >= 0 ? 'arrow_upward' : 'arrow_downward' }}</span>
{{ $trend >= 0 ? '+' : '' }}{{ $trend }}%
</span>
@endif
</div>
</div>
<div class="w-full h-72 relative">
@php
$chartMax = $chartMax ?? 1;
$chartValues = $chartValues ?? array_fill(0, 6, 0);
$chartLabels = $chartLabels ?? ['Jan','Feb','Mar','Apr','May','Jun'];
$points = [];
$count = count($chartValues);
foreach ($chartValues as $i => $v) {
    $ratio = $chartMax > 0 ? ($v / $chartMax) : 0;
    $y = 50 - ($ratio * 45);
    $y = max(5, min(50, $y));
    $x = $count > 1 ? ($i / ($count - 1)) * 100 : 50;
    $points[] = [round($x, 1), round($y, 1)];
}
$pathD = 'M0 50';
$lineD = '';
foreach ($points as $p) {
    $pathD .= ' L' . $p[0] . ' ' . $p[1];
    $lineD .= ($lineD ? ' L' : 'M') . $p[0] . ' ' . $p[1];
}
$pathD .= ' L100 50 Z';
@endphp
<div class="absolute inset-0 flex flex-col justify-between text-xs text-[#637588] dark:text-slate-500">
@for($i = 5; $i >= 1; $i--)
<div class="flex w-full border-b border-dashed border-[#e5e7eb] dark:border-slate-800 pb-2"><span>{{ ($currencySymbol ?? '$') }}{{ number_format(($chartMax ?? 1) * ($i / 5), 0) }}</span></div>
@endfor
<div class="flex w-full"><span>{{ ($currencySymbol ?? '$') }}0</span></div>
</div>
<svg class="absolute inset-0 h-full w-full pt-6 pb-6" preserveAspectRatio="none" viewBox="0 0 100 50">
<defs>
<linearGradient id="chartGradient" x1="0" x2="0" y1="0" y2="1">
<stop offset="0%" stop-color="#137fec" stop-opacity="0.2"></stop>
<stop offset="100%" stop-color="#137fec" stop-opacity="0"></stop>
</linearGradient>
</defs>
<path d="{{ $pathD }}" fill="url(#chartGradient)"></path>
<path d="{{ $lineD }}" fill="none" stroke="#137fec" stroke-width="0.8"></path>
@foreach($points as $p)
<circle cx="{{ $p[0] }}" cy="{{ $p[1] }}" fill="#fff" r="1.5" stroke="#137fec" stroke-width="0.5"></circle>
@endforeach
</svg>
</div>
<div class="flex justify-between mt-2 px-2 text-xs font-medium text-[#637588] dark:text-slate-400">
@foreach($chartLabels as $label)
<span>{{ $label }}</span>
@endforeach
</div>
</div>
<div class="flex flex-col gap-6">
<div class="rounded-xl border border-[#e5e7eb] dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm flex flex-col">
<div class="flex items-center justify-between mb-4">
<h3 class="text-lg font-bold text-[#111418] dark:text-white">Low Stock Alerts</h3>
<a class="text-primary text-sm font-bold hover:underline" href="{{ route('products.index', absolute: false) }}">View Products</a>
</div>
<div class="flex flex-col gap-1">
@forelse($lowStockProducts ?? [] as $product)
<a class="group flex items-start gap-3 py-3 border-b border-[#f3f4f6] dark:border-slate-800 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors" href="{{ route('products.edit', $product) }}">
<div class="flex flex-col flex-1">
<span class="text-sm font-medium text-[#111418] dark:text-white group-hover:text-primary transition-colors">{{ $product->name }}</span>
<span class="text-xs font-bold {{ $product->stock_quantity <= 0 ? 'text-red-500' : 'text-orange-500' }} bg-red-50 dark:bg-red-900/20 px-2 py-0.5 rounded w-fit mt-1">
{{ $product->stock_quantity <= 0 ? 'Out of Stock' : 'Low Stock' }} ({{ $product->stock_quantity }} left)
</span>
</div>
</a>
@empty
<p class="text-sm text-[#637588] dark:text-slate-400 py-4">No low stock alerts.</p>
@endforelse
</div>
</div>
<div class="rounded-xl border border-[#e5e7eb] dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm flex flex-col flex-1">
<div class="flex items-center justify-between mb-4">
<h3 class="text-lg font-bold text-[#111418] dark:text-white">Recent Activity</h3>
</div>
<div class="relative pl-2">
<div class="absolute left-[19px] top-2 bottom-4 w-[2px] bg-[#f3f4f6] dark:bg-slate-800"></div>
<div class="flex flex-col gap-6">
@forelse($activities ?? [] as $activity)
<div class="relative flex gap-4 items-start">
<div class="z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 border-white dark:border-slate-900 {{ $activity->iconBg ?? 'bg-slate-100 text-slate-600' }}">
<span class="material-symbols-outlined text-[18px]">{{ $activity->icon ?? 'circle' }}</span>
</div>
<div class="pt-1">
<p class="text-sm font-medium text-[#111418] dark:text-white">{{ $activity->message }}</p>
<p class="text-xs text-[#637588] dark:text-slate-400 mt-0.5">{{ $activity->time ?? '' }}</p>
</div>
</div>
@empty
<p class="text-sm text-[#637588] dark:text-slate-400 py-4">No recent activity.</p>
@endforelse
</div>
</div>
</div>
</div>
</div>
<div class="mt-8 text-center text-xs text-slate-400 pb-4">© {{ date('Y') }} Nexus ERP Inc. All rights reserved.</div>
</div>
</div>
</main>
</body>
</html>
