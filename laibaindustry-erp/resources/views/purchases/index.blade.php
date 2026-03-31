<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Purchases - ERP'])
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
@include('purchases.partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'purchases'])
<main class="purchases-stitch flex-1 flex flex-col h-full overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">Purchases</h2>
</div>
</header>
<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

{{-- Technical header: section id + headline + 2px primary rule (DESIGN.md) --}}
<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Purchases</h1>
</div>
<div class="flex flex-wrap items-center gap-3 shrink-0">
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] hidden lg:block mr-2">Ledger view</p>
<a class="st-btn-secondary h-10 px-4 inline-flex items-center justify-center gap-2 whitespace-nowrap" href="{{ route('purchases.export') }}">
<span class="material-symbols-outlined text-[18px]">download</span>
CSV
</a>
@if(auth()->user()->role !== 'viewer')
<a class="st-btn-primary h-10 px-5 inline-flex items-center justify-center gap-2 whitespace-nowrap" href="{{ route('purchases.create') }}">
<span class="material-symbols-outlined text-[20px]">add</span>
New Purchase
</a>
@endif
</div>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<div class="sm:hidden">
<p class="st-label">Module</p>
<p class="text-xl font-black uppercase tracking-tight text-[#2B3437]">Purchases</p>
</div>

@if (session('success'))
<div class="border border-[#ABB3B7] bg-white px-4 py-3 text-sm text-[#2B3437]">
{{ session('success') }}
</div>
@endif
@if (session('error'))
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D]">
{{ session('error') }}
</div>
@endif
@if ($errors->any())
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D] space-y-1">
<p class="font-semibold">Could not apply date filter</p>
<ul class="list-disc list-inside text-[13px]">
@foreach ($errors->all() as $err)
<li>{{ $err }}</li>
@endforeach
</ul>
</div>
@endif

{{-- Metrics --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-0 border border-[#ABB3B7] bg-white md:grid md:divide-x md:divide-[#ABB3B7]">
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Subtotal</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_subtotal ?? 0, 2) }}</p>
</div>
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">VAT (15%)</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_vat ?? 0, 2) }}</p>
</div>
<div class="p-6 border-2 border-[#5E5E5E] max-md:m-0 -m-px">
<p class="st-label mb-2 text-[#5E5E5E]">Total Purchases</p>
<p class="text-2xl font-black font-mono text-[#5E5E5E] tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_purchases ?? 0, 2) }}</p>
</div>
</div>

{{-- Filter bar (structural, no rounded — DESIGN.md) --}}
<form method="GET" action="{{ route('purchases.index') }}" class="flex flex-wrap items-end gap-4 p-5 bg-[#F8F9FA] border border-[#ABB3B7]">
<div class="flex-1 min-w-[200px]">
<label class="st-label block mb-2" for="p-search">Search</label>
<div class="relative">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#586064] material-symbols-outlined text-[18px] pointer-events-none">search</span>
<input class="st-input w-full h-10 pl-10 pr-3 text-sm" id="p-search" type="text" name="search" value="{{ request('search') }}" placeholder="Invoice, vendor, product…">
</div>
</div>
<div class="min-w-[140px]">
<label class="st-label block mb-2" for="p-from">From</label>
<input class="st-input w-full h-10 px-3 text-sm font-mono" id="p-from" type="text" name="from" value="{{ old('from', filter_date_input_value(request('from'))) }}" placeholder="dd/mm/yyyy" inputmode="numeric" autocomplete="off">
</div>
<div class="min-w-[140px]">
<label class="st-label block mb-2" for="p-to">To</label>
<input class="st-input w-full h-10 px-3 text-sm font-mono" id="p-to" type="text" name="to" value="{{ old('to', filter_date_input_value(request('to'))) }}" placeholder="dd/mm/yyyy" inputmode="numeric" autocomplete="off">
</div>
<div class="flex flex-wrap gap-2">
<button type="submit" class="st-btn-primary h-10 px-4 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[16px]">filter_list</span>
Filter
</button>
@if(request('search') || request('from') || request('to'))
<a href="{{ route('purchases.index') }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[16px]">close</span>
Clear
</a>
@endif
</div>
</form>

{{-- Ledger table --}}
<div class="st-paper flex flex-col flex-1 min-h-[400px] border border-[#ABB3B7] bg-white">
<div class="px-5 py-4 border-b border-[#ABB3B7] flex flex-wrap items-center justify-between gap-3 bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">Line items ledger</h3>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064]">Read-only row · click to open</p>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[1000px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Customer Code</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Customer Name</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Invoice Number</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Product Name</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Price</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Qty</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Amount</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">VAT 15%</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Subtotal</th>
<th class="st-th px-4 py-3 w-24"></th>
</tr>
</thead>
<tbody>
@forelse($items as $item)
@php $purchase = $item->purchase; @endphp
<tr class="st-tr {{ $purchase ? 'cursor-pointer' : '' }}"
    @if($purchase) onclick="window.location.href='{{ route('purchases.show', $purchase) }}';" @endif>
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ $purchase ? format_display_datetime($purchase->date) : '-' }}</td>
<td class="st-td px-4 py-3 text-sm text-[#2B3437]">{{ $purchase && $purchase->customer_code ? $purchase->customer_code : '-' }}</td>
<td class="st-td px-4 py-3 text-sm text-[#2B3437]">{{ $purchase && $purchase->customer_name ? $purchase->customer_name : '-' }}</td>
<td class="st-td px-4 py-3 text-sm font-semibold text-[#2B3437]">
@if($purchase)
<a href="{{ route('purchases.show', $purchase) }}" class="text-[#5E5E5E] hover:underline font-bold" onclick="event.stopPropagation()">{{ $purchase->invoice_number ?: '#' . $purchase->id }}</a>
@else
-
@endif
</td>
<td class="st-td px-4 py-3 text-sm text-[#2B3437]">{{ $item->product_name ?: '-' }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#2B3437]">{{ $currencySymbol ?? '$' }} {{ number_format($item->price, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right text-[#586064]">{{ number_format($item->quantity) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#2B3437]">{{ $currencySymbol ?? '$' }} {{ number_format($item->amount, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#586064]">{{ $currencySymbol ?? '$' }} {{ number_format($item->vat_amount, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums text-[#5E5E5E]">{{ $currencySymbol ?? '$' }} {{ number_format($item->subtotal, 2) }}</td>
<td class="st-td px-4 py-3">
@if($purchase)
<a href="{{ route('purchases.show', $purchase) }}" class="text-[11px] font-bold uppercase tracking-wider text-[#5E5E5E] border border-[#5E5E5E] px-2 py-1 inline-flex hover:bg-[#F1F4F6]" onclick="event.stopPropagation()">View</a>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="11" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
No purchases yet.
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('purchases.create') }}" class="text-[#5E5E5E] font-bold underline underline-offset-2">Create your first purchase</a>
@else
<span>No purchases recorded yet.</span>
@endif
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

<div class="p-4 border-t border-[#ABB3B7] flex flex-col sm:flex-row items-center justify-between gap-4 bg-[#F8F9FA]">
<p class="text-xs text-[#586064] uppercase tracking-wide">
@if($items->total() > 0)
Showing <span class="font-bold text-[#2B3437] tabular-nums">{{ $items->firstItem() }}</span>–<span class="font-bold text-[#2B3437] tabular-nums">{{ $items->lastItem() }}</span> of <span class="font-bold text-[#2B3437] tabular-nums">{{ $items->total() }}</span> line items
@else
No results
@endif
</p>
@if($items->hasPages())
<nav class="flex items-stretch border border-[#ABB3B7] bg-white divide-x divide-[#ABB3B7]" aria-label="Pagination">
@if (!$items->onFirstPage())
<a class="p-2 text-[#586064] hover:bg-[#F1F4F6] inline-flex items-center justify-center" href="{{ $items->previousPageUrl() }}" aria-label="Previous"><span class="material-symbols-outlined text-[20px]">chevron_left</span></a>
@endif
@foreach ($items->getUrlRange(max(1, $items->currentPage() - 2), min($items->lastPage(), $items->currentPage() + 2)) ?: [1 => $items->url(1)] as $page => $url)
@if ($page == $items->currentPage())
<span class="px-3 py-2 text-xs font-bold uppercase tracking-wider bg-[#5E5E5E] text-[#F8F8F8] inline-flex items-center justify-center min-w-[2.5rem]">{{ $page }}</span>
@else
<a class="px-3 py-2 text-xs font-bold text-[#586064] hover:bg-[#F1F4F6] inline-flex items-center justify-center min-w-[2.5rem]" href="{{ $url }}">{{ $page }}</a>
@endif
@endforeach
@if ($items->hasMorePages())
<a class="p-2 text-[#586064] hover:bg-[#F1F4F6] inline-flex items-center justify-center" href="{{ $items->nextPageUrl() }}" aria-label="Next"><span class="material-symbols-outlined text-[20px]">chevron_right</span></a>
@endif
</nav>
@endif
</div>
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-6 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
