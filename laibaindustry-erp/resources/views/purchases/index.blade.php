<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Purchases - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'purchases'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-[#1a2632] border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<h2 class="text-xl font-bold text-slate-800 dark:text-white hidden sm:block">Purchases</h2>
</div>
</header>
<div class="flex-1 overflow-y-auto p-6 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-6">
<div class="sm:hidden">
<h2 class="text-2xl font-bold text-slate-800 dark:text-white">Purchases</h2>
</div>

@if (session('success'))
<div class="rounded-lg border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/20 dark:border-emerald-800 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-400">
{{ session('success') }}
</div>
@endif
@if (session('error'))
<div class="rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-400">
{{ session('error') }}
</div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Subtotal</p>
<p class="text-2xl font-bold text-slate-900 dark:text-white font-mono mt-1"><span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_subtotal ?? 0, 2) }}</span></p>
</div>
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">VAT (15%)</p>
<p class="text-2xl font-bold text-slate-900 dark:text-white font-mono mt-1"><span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_vat ?? 0, 2) }}</span></p>
</div>
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Purchases</p>
<p class="text-2xl font-bold text-primary font-mono mt-1"><span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_purchases ?? 0, 2) }}</span></p>
</div>
</div>

@include('partials.search-filter-bar', ['action' => route('purchases.index'), 'searchPlaceholder' => 'Search invoice, supplier, product...'])

<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col flex-1 min-h-[400px]">
<div class="p-5 border-b border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-3">
<h3 class="text-base font-semibold text-slate-800 dark:text-white">Purchases</h3>
<div class="flex items-center gap-2">
<a class="h-9 px-3 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white border border-slate-200 dark:border-slate-600 rounded-lg transition-colors inline-flex items-center gap-1.5 whitespace-nowrap" href="{{ route('purchases.export') }}">
<span class="material-symbols-outlined text-[18px]">download</span>
CSV
</a>
@if(auth()->user()->role !== 'viewer')
<a class="h-10 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg inline-flex items-center justify-center gap-2 whitespace-nowrap transition-colors shadow-sm hover:shadow active:scale-[0.98] shrink-0" href="{{ route('purchases.create') }}">
<span class="material-symbols-outlined text-[20px] shrink-0">add</span>
<span>New Purchase</span>
</a>
@endif
</div>
</div>

<div class="overflow-x-auto w-full -mx-4 sm:mx-0">
<table class="w-full text-left border-collapse min-w-[1000px]">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Date</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Customer Code</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Customer Name</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Invoice Number</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Product Name</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right whitespace-nowrap">Price</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right whitespace-nowrap">Qty</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right whitespace-nowrap">Amount</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right whitespace-nowrap">VAT 15%</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right whitespace-nowrap">Subtotal</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24"></th>
</tr>
</thead>
<tbody class="divide-y divide-slate-200 dark:divide-slate-700">
@forelse($items as $item)
@php $purchase = $item->purchase; @endphp
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors {{ $purchase ? 'cursor-pointer' : '' }}"
    @if($purchase) onclick="window.location.href='{{ route('purchases.show', $purchase) }}';" @endif>
<td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $purchase ? $purchase->date->format('Y-m-d H:i') : '-' }}</td>
<td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $purchase && $purchase->customer_code ? $purchase->customer_code : '-' }}</td>
<td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $purchase && $purchase->customer_name ? $purchase->customer_name : '-' }}</td>
<td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-white">
@if($purchase)
<a href="{{ route('purchases.show', $purchase) }}" class="text-primary hover:underline" onclick="event.stopPropagation()">{{ $purchase->invoice_number ?: '#' . $purchase->id }}</a>
@else
-
@endif
</td>
<td class="px-4 py-3 text-sm text-slate-900 dark:text-white">{{ $item->product_name ?: '-' }}</td>
<td class="px-4 py-3 text-sm font-mono text-right text-slate-900 dark:text-white whitespace-nowrap"><span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($item->price, 2) }}</span></td>
<td class="px-4 py-3 text-sm font-mono text-right text-slate-600 dark:text-slate-300">{{ number_format($item->quantity) }}</td>
<td class="px-4 py-3 text-sm font-mono text-right text-slate-900 dark:text-white whitespace-nowrap"><span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($item->amount, 2) }}</span></td>
<td class="px-4 py-3 text-sm font-mono text-right text-slate-600 dark:text-slate-300 whitespace-nowrap"><span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($item->vat_amount, 2) }}</span></td>
<td class="px-4 py-3 text-sm font-mono font-medium text-right text-slate-900 dark:text-white whitespace-nowrap"><span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($item->subtotal, 2) }}</span></td>
<td class="px-4 py-3">
@if($purchase)
<a href="{{ route('purchases.show', $purchase) }}" class="inline-flex items-center gap-1 text-xs font-medium text-primary hover:bg-primary/5 rounded-lg px-2 py-1 transition-colors whitespace-nowrap" onclick="event.stopPropagation()">View</a>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="11" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
No purchases yet.
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('purchases.create') }}" class="text-primary font-medium hover:underline">Create your first purchase</a>
@else
<span>No purchases recorded yet.</span>
@endif
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

<div class="p-4 border-t border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-4">
<p class="text-sm text-slate-500 dark:text-slate-400">
@if($items->total() > 0)
Showing <span class="font-medium text-slate-900 dark:text-white">{{ $items->firstItem() }}</span> to <span class="font-medium text-slate-900 dark:text-white">{{ $items->lastItem() }}</span> of <span class="font-medium text-slate-900 dark:text-white">{{ $items->total() }}</span> line items
@else
No results
@endif
</p>
@if($items->hasPages())
<nav class="flex items-center gap-1" aria-label="Pagination">
@if (!$items->onFirstPage())
<a class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors" href="{{ $items->previousPageUrl() }}"><span class="material-symbols-outlined text-[20px]">chevron_left</span></a>
@endif
@foreach ($items->getUrlRange(max(1, $items->currentPage() - 2), min($items->lastPage(), $items->currentPage() + 2)) ?: [1 => $items->url(1)] as $page => $url)
@if ($page == $items->currentPage())
<span class="px-3 py-1.5 text-sm font-medium rounded-lg bg-primary text-white">{{ $page }}</span>
@else
<a class="px-3 py-1.5 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" href="{{ $url }}">{{ $page }}</a>
@endif
@endforeach
@if ($items->hasMorePages())
<a class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors" href="{{ $items->nextPageUrl() }}"><span class="material-symbols-outlined text-[20px]">chevron_right</span></a>
@endif
</nav>
@endif
</div>
</div>
</div>
<div class="mt-8 text-center text-xs text-slate-400 pb-4">© {{ date('Y') }} Nexus ERP Inc. All rights reserved.</div>
</div>
</main>
</body>
</html>
