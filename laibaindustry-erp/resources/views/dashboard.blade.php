<!DOCTYPE html>
<html lang="en" class="dark">
<head>
@include('partials.frontend-head', ['title' => 'Dashboard - Laiba Safety'])
<style>
body { background-color: #131313; color: #e2e2e2; font-family: 'Inter', sans-serif; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'dashboard'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#131313;">

{{-- Header --}}
<header class="h-16 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="background:#1B1B1B;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#8e9192] hover:text-white rounded-md" style="background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
</div>
<div class="flex items-center gap-3">
<a href="{{ route('sales.export') }}" class="h-9 px-5 text-[11px] font-bold uppercase tracking-[0.05em] flex items-center gap-2 active:scale-[0.98] transition-all" style="background:#FFFFFF;color:#2F3131;border-radius:0.375rem;">
<span class="material-symbols-outlined" style="font-size:14px;">download</span>
EXPORTS
</a>
<div class="w-10 h-10 flex items-center justify-center cursor-pointer hover:opacity-80 transition-opacity" style="border:1px solid rgba(68,71,72,0.4);border-radius:0.375rem;">
<span class="material-symbols-outlined" style="color:#C4C7C8;">calendar_today</span>
</div>
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col" style="gap:2rem;">

{{-- Page Heading --}}
<div>
<span class="text-[11px] font-medium uppercase text-[#8e9192] block mb-2" style="letter-spacing:0.2em;">System Status: Optimal</span>
<h2 class="text-white font-black" style="font-size:2.5rem;letter-spacing:-0.02em;line-height:1.1;">Financial Overview</h2>
</div>

{{-- Bento Grid --}}
<div class="grid grid-cols-12" style="gap:1.5rem;">

{{-- Total Revenue --}}
<div class="col-span-12 md:col-span-4 relative overflow-hidden group" style="background:#1B1B1B;border-radius:0.5rem;padding:2rem;">
<div class="absolute top-0 right-0 p-4 opacity-[0.05] group-hover:opacity-[0.1] transition-opacity">
<span class="material-symbols-outlined" style="font-size:4rem;">trending_up</span>
</div>
<p class="text-[10px] font-semibold uppercase text-[#C4C7C8]" style="letter-spacing:0.15em;margin-bottom:1.5rem;">Total Revenue</p>
<h3 class="text-white font-black tabular-nums" style="font-size:clamp(2rem,5vw,3.5rem);letter-spacing:-0.03em;margin-bottom:0.5rem;">{{ $currencySymbol }}{{ number_format($totalRevenue ?? 0, 0) }}</h3>
@if(isset($trend) && $trend !== null)
<div class="flex items-center gap-1.5 font-bold text-sm" style="color:{{ $trend >= 0 ? '#FFFFFF' : '#FFB4AB' }};">
<span class="material-symbols-outlined" style="font-size:14px;">{{ $trend >= 0 ? 'north_east' : 'south_east' }}</span>
<span>{{ $trend >= 0 ? '+' : '' }}{{ $trend }}% vs last period</span>
</div>
@endif
</div>

{{-- Total Expenses --}}
<div class="col-span-12 md:col-span-4 relative overflow-hidden group" style="background:#1B1B1B;border-radius:0.5rem;padding:2rem;">
<div class="absolute top-0 right-0 p-4 opacity-[0.05] group-hover:opacity-[0.1] transition-opacity">
<span class="material-symbols-outlined" style="font-size:4rem;">payments</span>
</div>
<p class="text-[10px] font-semibold uppercase text-[#C4C7C8]" style="letter-spacing:0.15em;margin-bottom:1.5rem;">Total Expenses</p>
<h3 class="text-white font-black tabular-nums" style="font-size:clamp(2rem,5vw,3.5rem);letter-spacing:-0.03em;margin-bottom:0.5rem;">{{ $currencySymbol }}{{ number_format($totalExpenses ?? 0, 0) }}</h3>
<div class="flex items-center gap-1.5 font-bold text-sm" style="color:#FFB4AB;">
<span class="material-symbols-outlined" style="font-size:14px;">south_east</span>
<span>Operating costs</span>
</div>
</div>

{{-- Net Profit (Hero Inverted White) --}}
<div class="col-span-12 md:col-span-4 relative overflow-hidden group" style="background:linear-gradient(135deg,#FFFFFF,#C6C6C7);border-radius:0.5rem;padding:2rem;">
<p class="text-[10px] font-semibold uppercase" style="letter-spacing:0.15em;margin-bottom:1.5rem;color:rgba(42,49,49,0.5);">Net Profit</p>
<h3 class="font-black tabular-nums" style="font-size:clamp(2rem,5vw,3.5rem);letter-spacing:-0.03em;margin-bottom:0.5rem;color:#1a1c1c;">{{ $currencySymbol }}{{ number_format($netProfit ?? 0, 0) }}</h3>
<p class="text-sm font-medium" style="color:rgba(42,49,49,0.45);">Margin: {{ $profitMargin ?? 0 }}%</p>
<div class="absolute bottom-4 right-4">
<span class="material-symbols-outlined" style="font-size:2.5rem;color:rgba(42,49,49,0.08);">{{ ($netProfit ?? 0) >= 0 ? 'check_circle' : 'warning' }}</span>
</div>
</div>

{{-- Total VAT (Wide) --}}
<div class="col-span-12 md:col-span-8" style="background:#1B1B1B;border-radius:0.5rem;padding:2rem;">
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4" style="margin-bottom:2.5rem;">
<div>
<p class="text-[10px] font-semibold uppercase text-[#C4C7C8]" style="letter-spacing:0.15em;">Tax Obligations</p>
<h4 class="text-white font-bold" style="font-size:1.5rem;margin-top:0.25rem;">Total VAT</h4>
</div>
<div class="text-left sm:text-right">
<span class="text-white font-black tabular-nums" style="font-size:2rem;">{{ $currencySymbol }}{{ number_format($netVat ?? 0, 0) }}</span>
<p class="text-[10px] uppercase font-medium text-[#C4C7C8]" style="margin-top:0.25rem;">Net {{ ($netVat ?? 0) >= 0 ? 'Payable' : 'Refundable' }}</p>
</div>
</div>
@php
$vatMonths = [];
for ($i = 3; $i >= 0; $i--) {
    $m = \Carbon\Carbon::now()->subMonths($i);
    $key = $m->format('Y-m');
    $monthVat = \App\Models\VatEntry::whereYear('date', $m->year)->whereMonth('date', $m->month)->sum('vat_amount');
    $vatMonths[] = ['label' => strtoupper($m->format('M')), 'amount' => (float) $monthVat];
}
$vatMax = max(1, max(array_column($vatMonths, 'amount')));
@endphp
<div class="grid grid-cols-4 gap-4">
@foreach($vatMonths as $vm)
<div class="h-1 overflow-hidden" style="background:#353535;border-radius:9999px;">
<div class="h-full" style="background:#FFFFFF;border-radius:9999px;width:{{ $vatMax > 0 ? round(($vm['amount'] / $vatMax) * 100) : 0 }}%;"></div>
</div>
@endforeach
</div>
<div class="flex justify-between mt-4 text-[10px] font-bold uppercase text-[#C4C7C8]" style="letter-spacing:0.15em;">
@foreach($vatMonths as $vm)
<span>{{ $vm['label'] }}</span>
@endforeach
</div>
</div>

{{-- Generate VAT Report Widget --}}
<div class="col-span-12 md:col-span-4 flex flex-col justify-center items-center text-center" style="background:#2A2A2A;border-radius:0.5rem;padding:2rem;">
<span class="material-symbols-outlined" style="font-size:2.5rem;color:#FFFFFF;margin-bottom:1rem;">analytics</span>
<p class="text-sm font-medium text-white" style="margin-bottom:0.5rem;">Generate VAT Report</p>
<p class="text-xs text-[#C4C7C8]" style="line-height:1.6;">System has prepared the automated tax reconciliation.</p>
<a href="{{ route('vat.export') }}" class="inline-block text-[11px] font-bold uppercase text-white" style="margin-top:1.5rem;letter-spacing:-0.02em;border-bottom:1px solid #FFFFFF;padding-bottom:0.25rem;">Review Now</a>
</div>

</div>

{{-- Sales Chart + Low Stock --}}
<div class="grid grid-cols-1 lg:grid-cols-3" style="gap:1.5rem;">

{{-- Sales Chart --}}
<div class="lg:col-span-2" style="background:#1B1B1B;border-radius:0.5rem;padding:2rem;">
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3" style="margin-bottom:1.5rem;">
<div>
<h3 class="text-lg font-bold text-white">Sales Overview</h3>
<p class="text-xs text-[#8e9192]" style="margin-top:0.25rem;">Last 6 months performance</p>
</div>
<div class="flex items-center gap-2">
<span class="font-black text-white tabular-nums" style="font-size:1.5rem;">{{ $currencySymbol }}{{ number_format($salesOverviewTotal ?? 0, 0) }}</span>
@if(isset($trend) && $trend !== null)
<span class="text-[11px] font-bold px-2 py-1 flex items-center gap-0.5" style="color:{{ $trend >= 0 ? '#FFFFFF' : '#FFB4AB' }};background:{{ $trend >= 0 ? 'rgba(255,255,255,0.08)' : 'rgba(255,180,171,0.08)' }};border-radius:0.25rem;">
<span class="material-symbols-outlined" style="font-size:12px;">{{ $trend >= 0 ? 'arrow_upward' : 'arrow_downward' }}</span>
{{ $trend >= 0 ? '+' : '' }}{{ $trend }}%
</span>
@endif
</div>
</div>
<div class="w-full relative" style="height:16rem;">
@php
$cMax = $chartMax ?? 1;
$cValues = $chartValues ?? array_fill(0, 6, 0);
$cLabels = $chartLabels ?? ['Jan','Feb','Mar','Apr','May','Jun'];
$pts = [];
$cnt = count($cValues);
foreach ($cValues as $i => $v) {
    $ratio = $cMax > 0 ? ($v / $cMax) : 0;
    $y = 50 - ($ratio * 45);
    $y = max(5, min(50, $y));
    $x = $cnt > 1 ? ($i / ($cnt - 1)) * 100 : 50;
    $pts[] = [round($x, 1), round($y, 1)];
}
$pD = 'M0 50';
$lD = '';
foreach ($pts as $p) {
    $pD .= ' L' . $p[0] . ' ' . $p[1];
    $lD .= ($lD ? ' L' : 'M') . $p[0] . ' ' . $p[1];
}
$pD .= ' L100 50 Z';
@endphp
<div class="absolute inset-0 flex flex-col justify-between text-[10px] font-medium text-[#8e9192] tabular-nums">
@for($i = 5; $i >= 1; $i--)
<div class="flex w-full pb-2" style="border-bottom:1px dashed #2A2A2A;">{{ $currencySymbol }}{{ number_format($cMax * ($i / 5), 0) }}</div>
@endfor
<div class="flex w-full">{{ $currencySymbol }}0</div>
</div>
<svg class="absolute inset-0 h-full w-full" style="padding-top:1.5rem;padding-bottom:1.5rem;" preserveAspectRatio="none" viewBox="0 0 100 50">
<defs>
<linearGradient id="chartGrad" x1="0" x2="0" y1="0" y2="1">
<stop offset="0%" stop-color="#ffffff" stop-opacity="0.12"></stop>
<stop offset="100%" stop-color="#ffffff" stop-opacity="0"></stop>
</linearGradient>
</defs>
<path d="{{ $pD }}" fill="url(#chartGrad)"></path>
<path d="{{ $lD }}" fill="none" stroke="#ffffff" stroke-width="0.7"></path>
@foreach($pts as $p)
<circle cx="{{ $p[0] }}" cy="{{ $p[1] }}" fill="#131313" r="1.6" stroke="#ffffff" stroke-width="0.5"></circle>
@endforeach
</svg>
</div>
<div class="flex justify-between mt-2 px-1 text-[10px] font-bold uppercase text-[#8e9192]" style="letter-spacing:0.15em;">
@foreach($cLabels as $label)
<span>{{ $label }}</span>
@endforeach
</div>
</div>

{{-- Low Stock --}}
<div class="flex flex-col" style="background:#1B1B1B;border-radius:0.5rem;padding:2rem;">
<div class="flex items-center justify-between" style="margin-bottom:1.25rem;">
<h3 class="text-lg font-bold text-white">Low Stock</h3>
<a class="text-[10px] font-bold uppercase text-[#8e9192] hover:text-white transition-colors" style="letter-spacing:0.05em;text-decoration:underline;text-underline-offset:4px;text-decoration-color:#444748;" href="{{ route('products.index', absolute: false) }}">View All</a>
</div>
<div class="flex flex-col flex-1">
@forelse($lowStockProducts ?? [] as $product)
<div class="flex items-center justify-between" style="padding:0.75rem 0;">
<div class="flex items-center gap-3">
<div class="flex items-center justify-center shrink-0" style="width:2rem;height:2rem;background:#353535;border-radius:0.25rem;">
<span class="material-symbols-outlined" style="font-size:14px;color:{{ $product->stock_quantity <= 0 ? '#FFB4AB' : '#C4C7C8' }};">inventory</span>
</div>
<span class="text-sm font-medium text-white">{{ $product->name }}</span>
</div>
<span class="text-xs font-black tabular-nums" style="color:{{ $product->stock_quantity <= 0 ? '#FFB4AB' : '#C4C7C8' }};">{{ $product->stock_quantity }}</span>
</div>
@empty
<p class="text-sm text-[#8e9192] py-4 text-center">All stock levels healthy.</p>
@endforelse
</div>
</div>
</div>

{{-- Recent Activity --}}
<section>
<div class="flex justify-between items-end" style="margin-bottom:1.5rem;">
<h3 class="text-xl font-bold tracking-tight text-white">Recent Activity</h3>
<a class="text-[10px] font-bold uppercase text-[#C4C7C8] hover:text-white transition-colors" style="letter-spacing:0.05em;text-decoration:underline;text-underline-offset:4px;text-decoration-color:#444748;" href="{{ route('sales.index', absolute: false) }}">View Ledger</a>
</div>
<div class="flex flex-col" style="gap:0.75rem;">
@forelse($transactions ?? [] as $tx)
<div class="flex items-center justify-between gap-4 transition-colors" style="background:#0E0E0E;padding:1.25rem;border-radius:0.5rem;" onmouseover="this.style.background='#1B1B1B'" onmouseout="this.style.background='#0E0E0E'">
<div class="flex items-center gap-5 min-w-0">
<div class="flex items-center justify-center shrink-0" style="width:3rem;height:3rem;background:#2A2A2A;border-radius:0.25rem;">
<span class="material-symbols-outlined" style="color:{{ $tx->type === 'sale' ? '#FFFFFF' : '#C4C7C8' }};">{{ $tx->icon }}</span>
</div>
<div class="min-w-0">
<p class="text-sm font-bold text-white truncate">{{ $tx->label }}</p>
<p class="text-xs text-[#C4C7C8] truncate" style="margin-top:0.25rem;">{{ $tx->detail }}</p>
</div>
</div>
<div class="text-right shrink-0">
<p class="text-sm font-black tabular-nums whitespace-nowrap" style="color:{{ $tx->type === 'sale' ? '#FFFFFF' : '#e2e2e2' }};">{{ $tx->amount }}</p>
<p class="text-[10px] uppercase font-bold whitespace-nowrap" style="margin-top:0.25rem;color:{{ $tx->type === 'sale' ? 'rgba(255,255,255,0.5)' : '#8e9192' }};">{{ $tx->status }}</p>
</div>
</div>
@empty
<div class="text-center" style="background:#0E0E0E;padding:2rem;border-radius:0.5rem;">
<p class="text-sm text-[#8e9192]">No recent transactions.</p>
</div>
@endforelse
</div>
</section>

<div class="text-center text-[10px] uppercase font-medium text-[#8e9192] pb-4" style="margin-top:2rem;letter-spacing:0.15em;">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</div>
</div>
</div>
</main>

{{-- Floating Action Button --}}
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('sales.create') }}" class="fixed z-50 flex items-center justify-center hover:scale-110 active:scale-95 transition-all" style="bottom:2rem;right:2rem;width:4rem;height:4rem;background:#FFFFFF;color:#2F3131;border-radius:50%;box-shadow:0 10px 30px rgba(0,0,0,0.4);" title="New Sale">
<span class="material-symbols-outlined" style="font-size:1.75rem;">add</span>
</a>
@endif

</body>
</html>
