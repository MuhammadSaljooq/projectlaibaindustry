<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Purchases - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'purchases'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-black border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
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
<div class="rounded-lg border border-gray-300 bg-gray-100 dark:bg-gray-800/50 dark:border-gray-700 px-4 py-3 text-sm text-gray-800 dark:text-gray-300">
{{ session('success') }}
</div>
@endif
@if (session('error'))
<div class="rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-400">
{{ session('error') }}
</div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
<div class="bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Subtotal</p>
<p class="text-2xl font-bold text-slate-900 dark:text-white font-mono mt-1"><span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_subtotal ?? 0, 2) }}</span></p>
</div>
<div class="bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">VAT (15%)</p>
<p class="text-2xl font-bold text-slate-900 dark:text-white font-mono mt-1"><span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_vat ?? 0, 2) }}</span></p>
</div>
<div class="bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Purchases</p>
<p class="text-2xl font-bold text-black dark:text-white font-mono mt-1"><span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_purchases ?? 0, 2) }}</span></p>
</div>
</div>

<div class="bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col flex-1 min-h-[400px]">
<form method="GET" action="{{ route('purchases.index') }}" class="border-b border-slate-200 dark:border-slate-700">
<div class="p-5 flex flex-col gap-4">
<div class="flex flex-wrap items-center justify-between gap-3">
<h3 class="text-base font-semibold text-slate-800 dark:text-white">Purchase history</h3>
@if(auth()->user()->role !== 'viewer')
<a class="h-10 px-4 bg-black hover:bg-gray-800 text-white text-sm font-bold rounded-lg inline-flex items-center justify-center gap-2 whitespace-nowrap transition-colors shadow-sm hover:shadow active:scale-[0.98] shrink-0" href="{{ route('purchases.create') }}">
<span class="material-symbols-outlined text-[20px] shrink-0">add</span>
<span>New Purchase</span>
</a>
@endif
</div>
<div class="flex flex-col gap-3">
<div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-3">
<div class="relative flex-1 min-w-0 w-full sm:max-w-md">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 material-symbols-outlined text-[20px] pointer-events-none">search</span>
<input class="h-10 w-full min-w-0 pl-10 pr-4 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary placeholder-slate-400 text-slate-700 dark:text-slate-200 outline-none" name="search" type="text" placeholder="Search by customer, invoice, product..." value="{{ request('search') }}"/>
</div>
<div class="flex flex-wrap gap-2">
<button type="submit" class="h-10 px-4 bg-black hover:bg-gray-800 text-white text-sm font-bold rounded-lg inline-flex items-center justify-center gap-2 whitespace-nowrap transition-colors shrink-0">Search</button>
<a href="{{ route('purchases.index') }}" class="h-10 px-4 inline-flex items-center justify-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 whitespace-nowrap shrink-0">Clear</a>
</div>
</div>
</div>
</div>
</form>

<div class="overflow-x-auto w-full -mx-4 sm:mx-0">
<table class="w-full text-left border-collapse min-w-[900px]">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Date</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Customer code</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Customer name</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Invoice number</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Product name</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right whitespace-nowrap">Price</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right whitespace-nowrap">Qty</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right whitespace-nowrap">Amount</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right whitespace-nowrap">VAT 15%</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right whitespace-nowrap">Subtotal</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right whitespace-nowrap w-28">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-200 dark:divide-slate-700">
@forelse($items as $item)
@php
$purchase = $item->purchase;
$amount = (float) ($item->amount ?: $item->price * $item->quantity);
$vat = (float) ($item->vat_amount ?? 0);
$subtotal = (float) ($item->subtotal ?: $amount + $vat);
@endphp
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors cursor-pointer" @if($purchase) data-href="{{ route('purchases.show', $purchase) }}" @endif role="button" tabindex="0">
<td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $purchase ? $purchase->date->format('Y-m-d H:i') : '-' }}</td>
<td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $purchase && $purchase->customer_code ? $purchase->customer_code : '-' }}</td>
<td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $purchase && $purchase->customer_name ? $purchase->customer_name : '-' }}</td>
<td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-white">
@if($purchase)
<span class="text-black dark:text-white">{{ $purchase->invoice_number ?: '#' . $purchase->id }}</span>
@else
-
@endif
</td>
<td class="px-4 py-3 text-sm text-slate-900 dark:text-white">{{ $item->product_name ?: '-' }}</td>
<td class="px-4 py-3 text-sm font-mono text-right text-slate-900 dark:text-white whitespace-nowrap"><span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($item->price ?? 0, 2) }}</span></td>
<td class="px-4 py-3 text-sm font-mono text-right text-slate-600 dark:text-slate-300">{{ number_format($item->quantity ?? 0) }}</td>
<td class="px-4 py-3 text-sm font-mono text-right text-slate-900 dark:text-white whitespace-nowrap"><span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($amount, 2) }}</span></td>
<td class="px-4 py-3 text-sm font-mono text-right text-slate-600 dark:text-slate-300 whitespace-nowrap"><span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($vat, 2) }}</span></td>
<td class="px-4 py-3 text-sm font-mono font-medium text-right text-slate-900 dark:text-white whitespace-nowrap"><span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($subtotal, 2) }}</span></td>
<td class="px-4 py-3 text-right" onclick="event.stopPropagation()">
@if($purchase)
<a href="{{ route('purchases.show', $purchase) }}" class="inline-flex items-center gap-1 text-xs font-medium text-black dark:text-white hover:bg-black/5 dark:hover:bg-white/5 rounded-lg px-2 py-1 transition-colors whitespace-nowrap">View</a>
<a href="{{ route('purchases.edit', $purchase) }}" class="inline-flex items-center gap-1 text-xs font-medium text-black dark:text-white hover:bg-black/5 dark:hover:bg-white/5 rounded-lg px-2 py-1 transition-colors whitespace-nowrap">Edit</a>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="11" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
No purchases yet.
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
<span class="px-3 py-1.5 text-sm font-medium rounded-lg bg-black text-white dark:bg-white dark:text-black">{{ $page }}</span>
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
<script>
document.querySelectorAll('tr[data-href]').forEach(function(row) {
  row.addEventListener('click', function(e) {
    if (e.target.closest('a')) return;
    window.location.href = row.getAttribute('data-href');
  });
  row.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.target.closest('a')) {
      e.preventDefault();
      window.location.href = row.getAttribute('data-href');
    }
  });
});
</script>
</body>
</html>
