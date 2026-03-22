<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Dashboard - Laiba Safety'])
<style>
body { background-color: #FFFFFF; color: #2B3437; font-family: 'Inter', sans-serif; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #2B3437; color: #FFFFFF; }
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'dashboard'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#FFFFFF;">

<header class="h-14 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 rounded-none" style="color:#5E5E5E;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
</div>
<div class="flex items-center gap-3">
<a href="{{ route('sales.export') }}" class="h-9 px-5 text-[11px] font-bold uppercase flex items-center gap-2 active:scale-[0.98] transition-all" style="background:#5E5E5E;color:#F8F8F8;border-radius:0;letter-spacing:0.05em;">
<span class="material-symbols-outlined" style="font-size:14px;">download</span>
EXPORTS
</a>
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col" style="gap:3rem;">

{{-- Technical Header --}}
<div>
<div class="flex items-end justify-between" style="padding-bottom:0.75rem;border-bottom:2px solid #5E5E5E;">
<h2 class="font-bold" style="font-size:1.5rem;letter-spacing:-0.02em;color:#2B3437;">Financial Overview</h2>
<span class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Last Updated: {{ now()->format('M d, Y') }}</span>
</div>
</div>

{{-- Stat Modules --}}
<div class="grid grid-cols-1 md:grid-cols-3" style="gap:1px;background:#D3D8DE;border:1px solid #D3D8DE;">

<div style="background:#FFFFFF;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">Total Revenue</p>
<p class="font-bold tabular-nums" style="font-size:2rem;letter-spacing:-0.02em;color:#2B3437;">{{ $currencySymbol }}{{ number_format($totalRevenue ?? 0, 0) }}</p>
@if(isset($trend) && $trend !== null)
<p class="text-xs font-bold" style="margin-top:0.5rem;color:{{ $trend >= 0 ? '#2B3437' : '#9F403D' }};">
{{ $trend >= 0 ? '+' : '' }}{{ $trend }}% vs last period
</p>
@endif
</div>

<div style="background:#FFFFFF;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">Total Expenses</p>
<p class="font-bold tabular-nums" style="font-size:2rem;letter-spacing:-0.02em;color:#2B3437;">{{ $currencySymbol }}{{ number_format($totalExpenses ?? 0, 0) }}</p>
<p class="text-xs font-bold" style="margin-top:0.5rem;color:#5E5E5E;">Operating costs</p>
</div>

<div style="background:#F8F9FA;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">Net Profit</p>
<p class="font-bold tabular-nums" style="font-size:2rem;letter-spacing:-0.02em;color:#2B3437;">{{ $currencySymbol }}{{ number_format($netProfit ?? 0, 0) }}</p>
<p class="text-xs font-bold" style="margin-top:0.5rem;color:#5E5E5E;">Margin: {{ $profitMargin ?? 0 }}%</p>
</div>

</div>

{{-- Tax + VAT Report --}}
<div class="grid grid-cols-1 lg:grid-cols-3" style="gap:1.5rem;">

<div class="lg:col-span-2" style="border:1px solid #D3D8DE;padding:2rem;">
<div class="flex items-end justify-between" style="padding-bottom:0.75rem;border-bottom:2px solid #5E5E5E;margin-bottom:2rem;">
<h3 class="font-bold" style="font-size:1.125rem;letter-spacing:-0.02em;color:#2B3437;">Tax Obligations</h3>
<span class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Section T-01</span>
</div>
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4" style="margin-bottom:2rem;">
<div>
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Total VAT</p>
</div>
<div class="text-left sm:text-right">
<span class="font-bold tabular-nums" style="font-size:1.75rem;color:#2B3437;">{{ $currencySymbol }}{{ number_format($netVat ?? 0, 0) }}</span>
<p class="text-[10px] uppercase font-bold" style="margin-top:0.25rem;color:#5E5E5E;">Net {{ ($netVat ?? 0) >= 0 ? 'Payable' : 'Refundable' }}</p>
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
<div>
<div class="h-1 overflow-hidden" style="background:#EAECEE;">
<div class="h-full" style="background:#5E5E5E;width:{{ $vatMax > 0 ? round(($vm['amount'] / $vatMax) * 100) : 0 }}%;"></div>
</div>
<p class="text-[10px] font-bold uppercase text-center" style="letter-spacing:0.05em;color:#5E5E5E;margin-top:0.5rem;">{{ $vm['label'] }}</p>
</div>
@endforeach
</div>
</div>

<div class="flex flex-col justify-center items-center text-center" style="border:1px solid #D3D8DE;padding:2rem;">
<span class="material-symbols-outlined" style="font-size:2.5rem;color:#5E5E5E;margin-bottom:1rem;">analytics</span>
<p class="text-sm font-bold" style="color:#2B3437;margin-bottom:0.5rem;">Generate VAT Report</p>
<p class="text-xs" style="color:#5E5E5E;line-height:1.6;">System has prepared the automated tax reconciliation.</p>
<a href="{{ route('vat.export') }}" class="inline-block text-[11px] font-bold uppercase" style="margin-top:1.5rem;color:#2B3437;border-bottom:2px solid #5E5E5E;padding-bottom:0.25rem;letter-spacing:0.05em;">Review Now</a>
</div>

</div>

{{-- Sales Chart + Low Stock --}}
<div class="grid grid-cols-1 lg:grid-cols-3" style="gap:1.5rem;">

<div class="lg:col-span-2" style="border:1px solid #D3D8DE;padding:2rem;">
<div class="flex items-end justify-between" style="padding-bottom:0.75rem;border-bottom:2px solid #5E5E5E;margin-bottom:1.5rem;">
<div>
<h3 class="font-bold" style="font-size:1.125rem;letter-spacing:-0.02em;color:#2B3437;">Sales Overview</h3>
<p class="text-xs" style="margin-top:0.25rem;color:#5E5E5E;">Last 6 months performance</p>
</div>
<div class="flex items-center gap-2">
<span class="font-bold tabular-nums" style="font-size:1.25rem;color:#2B3437;">{{ $currencySymbol }}{{ number_format($salesOverviewTotal ?? 0, 0) }}</span>
@if(isset($trend) && $trend !== null)
<span class="text-[10px] font-bold uppercase px-2 py-1" style="color:{{ $trend >= 0 ? '#2B3437' : '#9F403D' }};border:1px solid {{ $trend >= 0 ? '#D3D8DE' : '#9F403D' }};letter-spacing:0.05em;">
{{ $trend >= 0 ? '+' : '' }}{{ $trend }}%
</span>
@endif
</div>
</div>
<div class="w-full relative" style="height:14rem;">
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
<div class="absolute inset-0 flex flex-col justify-between text-[10px] font-bold tabular-nums" style="color:#5E5E5E;">
@for($i = 5; $i >= 1; $i--)
<div class="flex w-full pb-2" style="border-bottom:1px solid #EAECEE;">{{ $currencySymbol }}{{ number_format($cMax * ($i / 5), 0) }}</div>
@endfor
<div class="flex w-full">{{ $currencySymbol }}0</div>
</div>
<svg class="absolute inset-0 h-full w-full" style="padding-top:1.5rem;padding-bottom:1.5rem;" preserveAspectRatio="none" viewBox="0 0 100 50">
<defs>
<linearGradient id="chartGrad" x1="0" x2="0" y1="0" y2="1">
<stop offset="0%" stop-color="#5E5E5E" stop-opacity="0.1"></stop>
<stop offset="100%" stop-color="#5E5E5E" stop-opacity="0"></stop>
</linearGradient>
</defs>
<path d="{{ $pD }}" fill="url(#chartGrad)"></path>
<path d="{{ $lD }}" fill="none" stroke="#2B3437" stroke-width="0.7"></path>
@foreach($pts as $p)
<circle cx="{{ $p[0] }}" cy="{{ $p[1] }}" fill="#FFFFFF" r="1.6" stroke="#2B3437" stroke-width="0.5"></circle>
@endforeach
</svg>
</div>
<div class="flex justify-between mt-2 px-1 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">
@foreach($cLabels as $label)
<span>{{ $label }}</span>
@endforeach
</div>
</div>

<div style="border:1px solid #D3D8DE;">
<div class="flex items-center justify-between" style="padding:1.25rem 1.5rem;border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<h3 class="font-bold" style="font-size:0.875rem;color:#2B3437;">Low Stock</h3>
<a class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;text-decoration:underline;text-underline-offset:3px;" href="{{ route('products.index', absolute: false) }}">View All</a>
</div>
<div class="flex flex-col" style="padding:0 1.5rem;">
@forelse($lowStockProducts ?? [] as $product)
<div class="flex items-center justify-between" style="padding:0.875rem 0;{{ !$loop->last ? 'border-bottom:1px solid #EAECEE;' : '' }}">
<div class="flex items-center gap-3">
<span class="material-symbols-outlined" style="font-size:16px;color:{{ $product->stock_quantity <= 0 ? '#9F403D' : '#5E5E5E' }};">inventory</span>
<span class="text-sm font-bold" style="color:#2B3437;">{{ $product->name }}</span>
</div>
<span class="text-xs font-black tabular-nums" style="color:{{ $product->stock_quantity <= 0 ? '#9F403D' : '#5E5E5E' }};">{{ $product->stock_quantity }}</span>
</div>
@empty
<p class="text-sm py-6 text-center" style="color:#5E5E5E;">All stock levels healthy.</p>
@endforelse
</div>
</div>

</div>

{{-- Recent Activity --}}
<section>
<div class="flex items-end justify-between" style="padding-bottom:0.75rem;border-bottom:2px solid #5E5E5E;margin-bottom:1.5rem;">
<h3 class="font-bold" style="font-size:1.125rem;letter-spacing:-0.02em;color:#2B3437;">Recent Activity</h3>
<a class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;text-decoration:underline;text-underline-offset:3px;" href="{{ route('sales.index', absolute: false) }}">View Ledger</a>
</div>
<div style="border:1px solid #D3D8DE;">
@forelse($transactions ?? [] as $tx)
<div class="flex items-center justify-between gap-4 transition-colors" style="padding:1rem 1.5rem;{{ !$loop->last ? 'border-bottom:1px solid #EAECEE;' : '' }}" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">
<div class="flex items-center gap-4 min-w-0">
<span class="material-symbols-outlined shrink-0" style="color:#5E5E5E;">{{ $tx->icon }}</span>
<div class="min-w-0">
<p class="text-sm font-bold truncate" style="color:#2B3437;">{{ $tx->label }}</p>
<p class="text-xs truncate" style="color:#5E5E5E;margin-top:0.125rem;">{{ $tx->detail }}</p>
</div>
</div>
<div class="text-right shrink-0">
<p class="text-sm font-black tabular-nums whitespace-nowrap" style="color:#2B3437;">{{ $tx->amount }}</p>
<p class="text-[10px] uppercase font-bold whitespace-nowrap" style="margin-top:0.125rem;color:#5E5E5E;">{{ $tx->status }}</p>
</div>
</div>
@empty
<div class="text-center" style="padding:2rem;">
<p class="text-sm" style="color:#5E5E5E;">No recent transactions.</p>
</div>
@endforelse
</div>
</section>

<div class="text-center text-[10px] uppercase font-bold pb-4" style="margin-top:2rem;letter-spacing:0.05em;color:#5E5E5E;">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</div>
</div>
</div>
</main>

@if(auth()->user()->role !== 'viewer')
<a href="{{ route('sales.create') }}" class="fixed z-50 flex items-center justify-center hover:opacity-80 active:scale-95 transition-all" style="bottom:2rem;right:2rem;width:3.5rem;height:3.5rem;background:#5E5E5E;color:#F8F8F8;border-radius:0;" title="New Sale">
<span class="material-symbols-outlined" style="font-size:1.5rem;">add</span>
</a>
@endif

</body>
</html>
