<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'VAT - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'vat'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-[#1a2632] border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<h2 class="text-xl font-bold text-slate-800 dark:text-white hidden sm:block">VAT</h2>
</div>
</header>
<div class="flex-1 overflow-y-auto p-6 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-6">
<div class="sm:hidden">
<h2 class="text-2xl font-bold text-slate-800 dark:text-white">VAT</h2>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Sales VAT (Output)</p>
<p class="text-2xl font-bold text-primary font-mono mt-1">
<span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->sales_vat ?? 0, 2) }}</span>
</p>
</div>
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Purchase VAT (Input)</p>
<p class="text-2xl font-bold text-amber-600 dark:text-amber-400 font-mono mt-1">
<span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->purchase_vat ?? 0, 2) }}</span>
</p>
</div>
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Net VAT Payable</p>
<p class="text-2xl font-bold {{ ($totals->net_vat ?? 0) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }} font-mono mt-1">
<span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->net_vat ?? 0, 2) }}</span>
</p>
</div>
</div>

@include('partials.search-filter-bar', ['action' => route('vat.index'), 'searchPlaceholder' => 'Search invoice, customer...'])

<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col flex-1 min-h-[400px]">
<div class="p-5 border-b border-slate-200 dark:border-slate-700">
<div class="flex flex-wrap items-center justify-between gap-3 w-full">
<div>
<h3 class="text-base font-semibold text-slate-800 dark:text-white">VAT Entries</h3>
<p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Automatically recorded from Sales and Purchases.</p>
</div>
<a class="h-9 px-3 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white border border-slate-200 dark:border-slate-600 rounded-lg transition-colors inline-flex items-center gap-1.5 whitespace-nowrap" href="{{ route('vat.export') }}">
<span class="material-symbols-outlined text-[18px]">download</span>
CSV
</a>
</div>
</div>

<div class="overflow-x-auto w-full -mx-4 sm:mx-0">
<table class="w-full text-left border-collapse min-w-[700px]">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Type</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Invoice #</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Customer / Supplier</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Subtotal</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">VAT Rate</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">VAT Amount</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-200 dark:divide-slate-700">
@forelse($entries as $entry)
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $entry->date->format('Y-m-d') }}</td>
<td class="px-6 py-4">
@if($entry->type === 'sale')
<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
<span class="material-symbols-outlined text-[14px]">shopping_cart</span>
Sale
</span>
@else
<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
<span class="material-symbols-outlined text-[14px]">shopping_bag</span>
Purchase
</span>
@endif
</td>
<td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white">{{ $entry->invoice_number ?: '-' }}</td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $entry->customer_name ?: $entry->customer_code ?: '-' }}</td>
<td class="px-6 py-4 text-sm font-mono text-right text-slate-900 dark:text-white whitespace-nowrap"><span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($entry->subtotal, 2) }}</span></td>
<td class="px-6 py-4 text-sm font-mono text-right text-slate-600 dark:text-slate-300 whitespace-nowrap"><span class="tabular-nums">{{ number_format($entry->vat_rate, 2) }}%</span></td>
<td class="px-6 py-4 text-sm font-mono font-bold text-right {{ $entry->type === 'sale' ? 'text-primary' : 'text-amber-600 dark:text-amber-400' }} whitespace-nowrap"><span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($entry->vat_amount, 2) }}</span></td>
</tr>
@empty
<tr>
<td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
No VAT entries yet. VAT entries are created automatically when you create a sale or purchase.
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

<div class="p-4 border-t border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-4">
<p class="text-sm text-slate-500 dark:text-slate-400">
@if($entries->total() > 0)
Showing <span class="font-medium text-slate-900 dark:text-white">{{ $entries->firstItem() }}</span> to <span class="font-medium text-slate-900 dark:text-white">{{ $entries->lastItem() }}</span> of <span class="font-medium text-slate-900 dark:text-white">{{ $entries->total() }}</span> results
@else
No results
@endif
</p>
@if($entries->hasPages())
<nav class="flex items-center gap-1" aria-label="Pagination">
@if (!$entries->onFirstPage())
<a class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors" href="{{ $entries->previousPageUrl() }}"><span class="material-symbols-outlined text-[20px]">chevron_left</span></a>
@endif
@foreach ($entries->getUrlRange(max(1, $entries->currentPage() - 2), min($entries->lastPage(), $entries->currentPage() + 2)) ?: [1 => $entries->url(1)] as $page => $url)
@if ($page == $entries->currentPage())
<span class="px-3 py-1.5 text-sm font-medium rounded-lg bg-primary text-white">{{ $page }}</span>
@else
<a class="px-3 py-1.5 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" href="{{ $url }}">{{ $page }}</a>
@endif
@endforeach
@if ($entries->hasMorePages())
<a class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors" href="{{ $entries->nextPageUrl() }}"><span class="material-symbols-outlined text-[20px]">chevron_right</span></a>
@endif
</nav>
@endif
</div>
</div>
</div>
<div class="mt-8 text-center text-xs text-slate-400 pb-4">&copy; {{ date('Y') }} Nexus ERP Inc. All rights reserved.</div>
</div>
</main>
</body>
</html>
