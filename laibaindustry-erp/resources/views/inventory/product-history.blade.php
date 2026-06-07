<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => e($product->name).' History - Laiba Safety'])
@include('partials.stitch-design')
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'products'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase truncate max-w-[280px]">{{ $product->name }}</h2>
</div>
<div class="flex items-center gap-2">
<a href="{{ route('inventory.history') }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
All history
</a>
<a href="{{ route('inventory.dashboard') }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[18px]">inventory_2</span>
Inventory
</a>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064]">Sales history</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none truncate">{{ $product->name }}</h1>
</div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] lg:text-right shrink-0">{{ $product->sku }} · per-product view</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<div class="sm:hidden">
<p class="st-label">Product</p>
<p class="text-xl font-black uppercase tracking-tight text-[#2B3437]">{{ $product->name }}</p>
</div>

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

<div class="grid grid-cols-2 md:grid-cols-4 gap-0 border border-[#ABB3B7] bg-white md:divide-x md:divide-[#ABB3B7]">
<div class="p-6 border-b md:border-b-0 border-r border-[#ABB3B7]">
<p class="st-label mb-2">Article no.</p>
<p class="text-lg font-bold font-mono text-[#2B3437] tabular-nums truncate">{{ $product->sku }}</p>
</div>
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Current stock</p>
<p class="text-2xl font-bold font-mono tabular-nums {{ $product->stock_quantity <= 0 ? 'text-[#9F403D]' : ($product->stock_quantity <= ($product->reorder_level ?? 10) ? 'text-[#9F403D]' : 'text-[#2B3437]') }}">{{ $product->stock_quantity }}</p>
</div>
<div class="p-6 border-b md:border-b-0 border-r border-[#ABB3B7]">
<p class="st-label mb-2">Total qty sold</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ number_format($totals->total_qty ?? 0) }}</p>
</div>
<div class="p-6 border-2 border-[#5E5E5E] max-md:m-0 -m-px">
<p class="st-label st-label--primary mb-2">Total revenue</p>
<p class="text-2xl font-black font-mono text-[#5E5E5E] tabular-nums">{{ $currencySymbol }} {{ number_format($totals->total_revenue ?? 0, 2) }}</p>
</div>
</div>

<form id="ph-form" method="GET" action="{{ route('products.history', $product) }}" class="flex flex-wrap items-end gap-4 p-5 bg-[#F8F9FA] border border-[#ABB3B7]">
<div class="min-w-[160px]">
<label class="st-label block mb-2" for="ph-customer">Customer</label>
<input class="st-input w-full h-10 px-3 text-sm" id="ph-customer" type="text" name="customer" value="{{ request('customer') }}" placeholder="Name or code…" autocomplete="off">
</div>
<div class="min-w-[130px]">
<label class="st-label block mb-2" for="ph-from">From</label>
<input class="st-input w-full h-10 px-3 text-sm font-mono" id="ph-from" type="text" name="from" value="{{ old('from', filter_date_input_value(request('from'))) }}" placeholder="dd/mm/yyyy" inputmode="numeric" autocomplete="off">
</div>
<div class="min-w-[130px]">
<label class="st-label block mb-2" for="ph-to">To</label>
<input class="st-input w-full h-10 px-3 text-sm font-mono" id="ph-to" type="text" name="to" value="{{ old('to', filter_date_input_value(request('to'))) }}" placeholder="dd/mm/yyyy" inputmode="numeric" autocomplete="off">
</div>
<div class="flex flex-wrap gap-2">
<button type="submit" class="st-btn-primary h-10 px-4 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[16px]">filter_list</span>
Filter
</button>
<a href="{{ route('products.history', $product) }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2{{ request('customer') || request('from') || request('to') ? '' : ' hidden' }}" id="ph-clear-btn">
<span class="material-symbols-outlined text-[16px]">close</span>
Clear
</a>
</div>
</form>

<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[320px]">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">Sales lines for {{ e($product->name) }}</h3>
<p class="text-[11px] text-[#586064] mt-1">One row per invoice · invoice number links to full sale</p>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[700px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Customer</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Qty</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap hidden md:table-cell">Unit price</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Line total</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Invoice</th>
</tr>
</thead>
<tbody>
@forelse($items as $item)
@php
$customer = $item->sale?->customer_name ?? $item->sale?->customer_code ?? 'Walk-in';
$lineTotal = $item->quantity * $item->selling_price;
@endphp
<tr class="st-tr">
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ format_display_datetime($item->sale?->date) }}</td>
<td class="st-td px-4 py-3 text-sm font-semibold text-[#2B3437] truncate max-w-[200px]">{{ $customer }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right tabular-nums text-[#2B3437]">{{ $item->quantity }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums text-[#586064] hidden md:table-cell">{{ $currencySymbol }} {{ number_format($item->selling_price, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right tabular-nums text-[#5E5E5E]">{{ $currencySymbol }} {{ number_format($lineTotal, 2) }}</td>
<td class="st-td px-4 py-3 text-sm">
@if($item->sale)
<a href="{{ route('sales.show', $item->sale) }}" class="font-bold text-[#5E5E5E] hover:underline underline-offset-2">
{{ $item->sale->invoice_number ?: '#'.$item->sale->id }}
</a>
@else
<span class="text-[#586064]">—</span>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="6" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No sales recorded for this product</p>
@if(request('customer') || request('from') || request('to'))
<a href="{{ route('products.history', $product) }}" class="text-[#5E5E5E] font-bold underline underline-offset-2">Clear filters</a>
@endif
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

<div class="p-4 border-t border-[#ABB3B7] bg-[#F8F9FA] flex items-center justify-between gap-4 flex-wrap">
<p class="text-xs text-[#586064] uppercase tracking-wide">
@if($items->total() > 0)
Showing <span class="font-bold text-[#2B3437] tabular-nums">{{ $items->firstItem() }}–{{ $items->lastItem() }}</span> of <span class="font-bold text-[#2B3437] tabular-nums">{{ number_format($items->total()) }}</span> lines
@else
No results
@endif
</p>
@if($items->hasPages())
<div class="text-sm">{{ $items->links() }}</div>
@endif
</div>
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-6 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
