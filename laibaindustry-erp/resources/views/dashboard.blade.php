<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Dashboard - Laiba Safety'])
@include('partials.stitch-design')
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'dashboard'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">Dashboard</h2>
</div>
<div class="flex items-center gap-2 flex-wrap justify-end">
<a href="{{ route('sales.export') }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[18px]">download</span>
CSV export
</a>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">SYS_OVERVIEW_01</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Financial overview</h1>
</div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] lg:text-right shrink-0">Live metrics · last updated {{ now()->format('Y-m-d H:i') }}</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-0 border border-[#ABB3B7] bg-white md:divide-x md:divide-[#ABB3B7]">
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Total revenue</p>
<p class="text-2xl font-black font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol }}{{ number_format($totalRevenue ?? 0, 0) }}</p>
@if(isset($trend) && $trend !== null)
<p class="text-xs font-bold mt-2 tabular-nums {{ $trend >= 0 ? 'text-[#5E5E5E]' : 'text-[#9F403D]' }}">{{ $trend >= 0 ? '+' : '' }}{{ $trend }}% vs prior</p>
@endif
</div>
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Total expenses</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol }}{{ number_format($totalExpenses ?? 0, 0) }}</p>
<p class="text-xs text-[#586064] mt-2 font-medium">Operating costs</p>
</div>
<div class="p-6 border-2 border-[#5E5E5E] -m-px">
<p class="st-label st-label--primary mb-2">Net profit</p>
<p class="text-2xl font-black font-mono text-[#5E5E5E] tabular-nums">{{ $currencySymbol }}{{ number_format($netProfit ?? 0, 0) }}</p>
<p class="text-xs text-[#586064] mt-2">Margin: {{ $profitMargin ?? 0 }}%</p>
</div>
</div>

@php
$vatMonths = [];
for ($i = 3; $i >= 0; $i--) {
    $m = \Carbon\Carbon::now()->subMonths($i);
    $monthVat = \App\Models\VatEntry::whereYear('date', $m->year)->whereMonth('date', $m->month)->sum('vat_amount');
    $vatMonths[] = ['label' => strtoupper($m->format('M')), 'amount' => (float) $monthVat];
}
$vatMax = max(1, max(array_column($vatMonths, 'amount')));
@endphp

<div class="grid grid-cols-1 lg:grid-cols-12 gap-0 border border-[#ABB3B7] bg-white">
<div class="lg:col-span-8 p-6 border-b lg:border-b-0 lg:border-r border-[#ABB3B7]">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
<div>
<p class="st-label mb-1">Tax obligations</p>
<h3 class="text-lg font-bold uppercase tracking-tight text-[#2B3437]">Total VAT</h3>
</div>
<div class="text-left sm:text-right">
<p class="text-2xl font-black font-mono tabular-nums text-[#5E5E5E]">{{ $currencySymbol }}{{ number_format($netVat ?? 0, 0) }}</p>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] mt-1">Net {{ ($netVat ?? 0) >= 0 ? 'payable' : 'refundable' }}</p>
</div>
</div>
<div class="grid grid-cols-4 gap-3">
@foreach($vatMonths as $vm)
<div class="flex flex-col gap-2">
<div class="h-2 w-full border border-[#ABB3B7] bg-[#F1F4F6]">
<div class="h-full bg-[#5E5E5E]" style="width:{{ $vatMax > 0 ? round(($vm['amount'] / $vatMax) * 100) : 0 }}%;"></div>
</div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064]">{{ $vm['label'] }}</p>
</div>
@endforeach
</div>
</div>
<div class="lg:col-span-4 p-6 flex flex-col justify-center border-t lg:border-t-0 border-[#ABB3B7] bg-[#F8F9FA]">
<span class="material-symbols-outlined text-[#5E5E5E] text-[2rem] mb-3">analytics</span>
<p class="text-sm font-bold text-[#2B3437] uppercase tracking-tight">VAT report</p>
<p class="text-xs text-[#586064] mt-2 leading-relaxed">Automated tax reconciliation export.</p>
<a href="{{ route('vat.export') }}" class="st-btn-primary h-10 px-4 inline-flex items-center gap-2 mt-4 w-fit">Review export</a>
</div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
<div class="lg:col-span-2 st-paper border border-[#ABB3B7] bg-white p-6 flex flex-col">
<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
<div>
<h3 class="text-sm font-bold uppercase tracking-widest text-[#586064]">Sales overview</h3>
<p class="text-xs text-[#586064] mt-1">Last 6 months</p>
</div>
<div class="flex items-center gap-2 flex-wrap">
<span class="text-xl font-black font-mono tabular-nums text-[#2B3437]">{{ $currencySymbol }}{{ number_format($salesOverviewTotal ?? 0, 0) }}</span>
@if(isset($trend) && $trend !== null)
<span class="text-[10px] font-bold uppercase px-2 py-1 border tabular-nums {{ $trend >= 0 ? 'border-[#ABB3B7] text-[#5E5E5E]' : 'border-[#9F403D] text-[#9F403D]' }}">{{ $trend >= 0 ? '+' : '' }}{{ $trend }}%</span>
@endif
</div>
</div>
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
<div class="w-full relative flex-1 min-h-[16rem] border border-[#ABB3B7] bg-[#F8F9FA]">
<div class="absolute inset-0 flex flex-col justify-between text-[10px] font-medium text-[#586064] tabular-nums p-3 pointer-events-none">
@for($i = 5; $i >= 1; $i--)
<div class="flex w-full pb-2 border-b border-dashed border-[#ABB3B7]">{{ $currencySymbol }}{{ number_format($cMax * ($i / 5), 0) }}</div>
@endfor
<div class="flex w-full">{{ $currencySymbol }}0</div>
</div>
<svg class="absolute inset-0 h-full w-full pt-6 pb-6" preserveAspectRatio="none" viewBox="0 0 100 50" aria-hidden="true">
<path d="{{ $pD }}" fill="#EAEFF1"></path>
<path d="{{ $lD }}" fill="none" stroke="#5E5E5E" stroke-width="0.8"></path>
@foreach($pts as $p)
<circle cx="{{ $p[0] }}" cy="{{ $p[1] }}" fill="#FFFFFF" r="1.4" stroke="#5E5E5E" stroke-width="0.5"></circle>
@endforeach
</svg>
</div>
<div class="flex justify-between mt-3 px-1 text-[10px] font-bold uppercase tracking-widest text-[#586064]">
@foreach($cLabels as $label)
<span>{{ $label }}</span>
@endforeach
</div>
</div>

<div class="st-paper border border-[#ABB3B7] bg-white p-6 flex flex-col min-h-[16rem]">
<div class="flex items-center justify-between gap-4 border-b border-[#ABB3B7] pb-4 mb-4">
<h3 class="text-sm font-bold uppercase tracking-widest text-[#586064]">Low stock</h3>
<a class="text-[10px] font-bold uppercase tracking-widest text-[#5E5E5E] underline underline-offset-4" href="{{ route('inventory.dashboard', absolute: false) }}">View all</a>
</div>
<div class="flex flex-col flex-1 min-h-0 overflow-y-auto gap-0">
@forelse($lowStockProducts ?? [] as $product)
<div class="flex items-center justify-between py-3 border-b border-[#ABB3B7] last:border-0">
<div class="flex items-center gap-3 min-w-0">
<div class="w-8 h-8 shrink-0 border border-[#ABB3B7] bg-[#EAEFF1] flex items-center justify-center">
<span class="material-symbols-outlined text-[16px] {{ $product->stock_quantity <= 0 ? 'text-[#9F403D]' : 'text-[#586064]' }}">inventory</span>
</div>
<span class="text-sm font-semibold text-[#2B3437] truncate">{{ $product->name }}</span>
</div>
<span class="text-xs font-black font-mono tabular-nums shrink-0 {{ $product->stock_quantity <= 0 ? 'text-[#9F403D]' : 'text-[#586064]' }}">{{ $product->stock_quantity }}</span>
</div>
@empty
<p class="text-sm text-[#586064] py-6 text-center">All stock levels healthy.</p>
@endforelse
</div>
</div>
</div>

<section>
<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-4">
<h3 class="text-lg font-black uppercase tracking-tight text-[#2B3437]">Recent activity</h3>
<a class="text-[10px] font-bold uppercase tracking-widest text-[#5E5E5E] underline underline-offset-4" href="{{ route('sales.index', absolute: false) }}">View ledger</a>
</div>
<div class="border border-[#ABB3B7] bg-white">
@forelse($transactions ?? [] as $tx)
<div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-[#ABB3B7] last:border-b-0 hover:bg-[#F1F4F6] transition-colors">
<div class="flex items-center gap-4 min-w-0">
<div class="w-10 h-10 shrink-0 border border-[#ABB3B7] bg-[#EAEFF1] flex items-center justify-center">
<span class="material-symbols-outlined text-[20px] {{ $tx->type === 'sale' ? 'text-[#5E5E5E]' : 'text-[#586064]' }}">{{ $tx->icon }}</span>
</div>
<div class="min-w-0">
<p class="text-sm font-bold text-[#2B3437] truncate">{{ $tx->label }}</p>
<p class="text-xs text-[#586064] truncate mt-0.5">{{ $tx->detail }}</p>
</div>
</div>
<div class="text-right shrink-0">
<p class="text-sm font-black font-mono tabular-nums whitespace-nowrap text-[#2B3437]">{{ $tx->amount }}</p>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] mt-1">{{ $tx->status }}</p>
</div>
</div>
@empty
<div class="px-5 py-10 text-center text-sm text-[#586064]">No recent transactions.</div>
@endforelse
</div>
</section>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-4 pb-2">© {{ date('Y') }} Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>

@if(auth()->user()->role !== 'viewer')
<a href="{{ route('sales.create') }}" class="fixed z-50 bottom-8 right-8 h-14 w-14 st-btn-primary inline-flex items-center justify-center" title="New sale">
<span class="material-symbols-outlined text-[28px]">add</span>
</a>
@endif

</body>
</html>
