<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'VAT - ERP'])
@include('partials.stitch-design')
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'vat'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">VAT</h2>
</div>
<div class="flex items-center gap-2 flex-wrap justify-end">
<a href="{{ route('vat.export') }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2 whitespace-nowrap">
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
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Value added tax</h1>
</div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] lg:text-right shrink-0">Output · input · net · current filters</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<div class="sm:hidden">
<p class="st-label">Module</p>
<p class="text-xl font-black uppercase tracking-tight text-[#2B3437]">VAT</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-0 border border-[#ABB3B7] bg-white md:divide-x md:divide-[#ABB3B7]">
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Output VAT <span class="font-normal normal-case text-[#586064]">(filtered)</span></p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol }} {{ number_format($filteredSalesVat ?? 0, 2) }}</p>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] mt-2">Sales</p>
</div>
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Input VAT <span class="font-normal normal-case text-[#586064]">(filtered)</span></p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol }} {{ number_format($filteredPurchaseVat ?? 0, 2) }}</p>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] mt-2">Purchases</p>
</div>
<div class="p-6 border-2 border-[#5E5E5E] max-md:m-0 -m-px">
<p class="st-label st-label--primary mb-2">Net VAT <span class="font-normal normal-case text-[#586064]">(filtered)</span></p>
<p class="text-2xl font-black font-mono tabular-nums {{ ($filteredNetVat ?? 0) < 0 ? 'text-[#9F403D]' : 'text-[#5E5E5E]' }}">{{ $currencySymbol }} {{ number_format($filteredNetVat ?? 0, 2) }}</p>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] mt-2">{{ ($filteredNetVat ?? 0) >= 0 ? 'Payable' : 'Refundable' }}</p>
</div>
</div>

<p class="text-[11px] text-[#586064] border border-[#ABB3B7] bg-[#F8F9FA] px-4 py-3">
<span class="font-bold text-[#2B3437] uppercase tracking-wide">All-time ·</span>
Output {{ $currencySymbol }} {{ number_format($totals->sales_vat ?? 0, 2) }}
· Input {{ $currencySymbol }} {{ number_format($totals->purchase_vat ?? 0, 2) }}
· Net <span class="font-mono font-bold {{ ($totals->net_vat ?? 0) < 0 ? 'text-[#9F403D]' : 'text-[#2B3437]' }}">{{ $currencySymbol }} {{ number_format($totals->net_vat ?? 0, 2) }}</span>
</p>

<form method="GET" action="{{ route('vat.index') }}" class="flex flex-wrap items-end gap-4 p-5 bg-[#F8F9FA] border border-[#ABB3B7]">
<div class="flex-1 min-w-[200px]">
<label class="st-label block mb-2" for="v-search">Search</label>
<div class="relative">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#586064] material-symbols-outlined text-[18px] pointer-events-none">search</span>
<input class="st-input w-full h-10 pl-10 pr-3 text-sm" id="v-search" type="text" name="search" value="{{ request('search') }}" placeholder="Invoice, customer, code…">
</div>
</div>
<div class="min-w-[140px]">
<label class="st-label block mb-2" for="v-from">From</label>
<input class="st-input w-full h-10 px-3 text-sm" id="v-from" type="date" name="from" value="{{ request('from') }}">
</div>
<div class="min-w-[140px]">
<label class="st-label block mb-2" for="v-to">To</label>
<input class="st-input w-full h-10 px-3 text-sm" id="v-to" type="date" name="to" value="{{ request('to') }}">
</div>
<div class="flex flex-wrap gap-2">
<button type="submit" class="st-btn-primary h-10 px-4 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[16px]">filter_list</span>
Filter
</button>
@if(request('search') || request('from') || request('to'))
<a href="{{ route('vat.index') }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[16px]">close</span>
Clear
</a>
@endif
</div>
</form>

<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[320px]">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">VAT entry ledger</h3>
<p class="text-[11px] text-[#586064] mt-1">Mirrored from sales and purchases · export CSV from header</p>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[900px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Type</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Invoice</th>
<th class="st-th px-4 py-3">Customer / supplier</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Subtotal</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">VAT %</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">VAT amount</th>
</tr>
</thead>
<tbody>
@forelse($entries as $entry)
<tr class="st-tr">
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ $entry->date->format('Y-m-d') }}</td>
<td class="st-td px-4 py-3">
@if($entry->type === 'sale')
<span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wide px-2 py-1 border border-[#5E5E5E] text-[#5E5E5E]">
<span class="material-symbols-outlined text-[14px]">payments</span>
Sale
</span>
@else
<span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wide px-2 py-1 border border-[#ABB3B7] text-[#586064]">
<span class="material-symbols-outlined text-[14px]">shopping_cart</span>
Purchase
</span>
@endif
</td>
<td class="st-td px-4 py-3 text-sm font-semibold text-[#2B3437]">{{ $entry->invoice_number ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm text-[#586064]">{{ $entry->customer_name ?: $entry->customer_code ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#2B3437]">{{ $currencySymbol }} {{ number_format($entry->subtotal, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#586064]">{{ number_format($entry->vat_rate, 2) }}%</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums {{ $entry->type === 'sale' ? 'text-[#5E5E5E]' : 'text-[#586064]' }}">{{ $currencySymbol }} {{ number_format($entry->vat_amount, 2) }}</td>
</tr>
@empty
<tr>
<td colspan="7" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No VAT entries</p>
<p class="max-w-md mx-auto">Entries are created when you record sales and purchases.</p>
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

@if($entries->hasPages())
<div class="p-4 border-t border-[#ABB3B7] flex flex-col sm:flex-row items-center justify-between gap-4 bg-[#F8F9FA]">
<p class="text-xs text-[#586064] uppercase tracking-wide">
Showing <span class="font-bold text-[#2B3437] tabular-nums">{{ $entries->firstItem() }}</span>–<span class="font-bold text-[#2B3437] tabular-nums">{{ $entries->lastItem() }}</span> of <span class="font-bold text-[#2B3437] tabular-nums">{{ $entries->total() }}</span>
</p>
<nav class="flex items-stretch border border-[#ABB3B7] bg-white divide-x divide-[#ABB3B7]" aria-label="Pagination">
@if (!$entries->onFirstPage())
<a class="p-2 text-[#586064] hover:bg-[#F1F4F6] inline-flex items-center justify-center" href="{{ $entries->previousPageUrl() }}" aria-label="Previous"><span class="material-symbols-outlined text-[20px]">chevron_left</span></a>
@endif
@foreach ($entries->getUrlRange(max(1, $entries->currentPage() - 2), min($entries->lastPage(), $entries->currentPage() + 2)) ?: [1 => $entries->url(1)] as $page => $url)
@if ($page == $entries->currentPage())
<span class="px-3 py-2 text-xs font-bold uppercase tracking-wider bg-[#5E5E5E] text-[#F8F8F8] inline-flex items-center justify-center min-w-[2.5rem]">{{ $page }}</span>
@else
<a class="px-3 py-2 text-xs font-bold text-[#586064] hover:bg-[#F1F4F6] inline-flex items-center justify-center min-w-[2.5rem]" href="{{ $url }}">{{ $page }}</a>
@endif
@endforeach
@if ($entries->hasMorePages())
<a class="p-2 text-[#586064] hover:bg-[#F1F4F6] inline-flex items-center justify-center" href="{{ $entries->nextPageUrl() }}" aria-label="Next"><span class="material-symbols-outlined text-[20px]">chevron_right</span></a>
@endif
</nav>
</div>
@else
<div class="p-4 border-t border-[#ABB3B7] bg-[#F8F9FA]">
<p class="text-xs text-[#586064] uppercase tracking-wide">
@if($entries->total() > 0)
Showing all <span class="font-bold text-[#2B3437] tabular-nums">{{ $entries->total() }}</span> results
@else
No results
@endif
</p>
</div>
@endif
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-6 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
