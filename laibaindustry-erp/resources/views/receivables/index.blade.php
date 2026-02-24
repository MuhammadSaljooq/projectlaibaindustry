<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Receivables - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'receivables'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-black border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<h2 class="text-xl font-bold text-slate-800 dark:text-white hidden sm:block">Receivables</h2>
</div>
</header>
<div class="flex-1 overflow-y-auto p-6 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-6">
<div class="sm:hidden">
<h2 class="text-2xl font-bold text-slate-800 dark:text-white">Receivables</h2>
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

<div class="bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col flex-1 min-h-[400px]">
<form method="GET" action="{{ route('receivables.index') }}" class="border-b border-slate-200 dark:border-slate-700">
<div class="p-5 flex flex-col gap-4">
<div class="flex flex-wrap items-center justify-between gap-3">
<div>
<h3 class="text-base font-semibold text-slate-800 dark:text-white">Receivables</h3>
<p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Track amounts owed by customers. Record payments via Edit.</p>
</div>
</div>
<div class="flex flex-col gap-3">
<div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-3">
<div class="relative flex-1 min-w-0 w-full sm:max-w-md">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 material-symbols-outlined text-[20px] pointer-events-none">search</span>
<input class="h-10 w-full min-w-0 pl-10 pr-4 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary placeholder-slate-400 text-slate-700 dark:text-slate-200 outline-none" name="search" type="text" placeholder="Search by invoice, customer name or code..." value="{{ request('search') }}"/>
</div>
<div class="flex flex-wrap gap-2">
<button type="submit" class="h-10 px-4 bg-black hover:bg-gray-800 text-white text-sm font-bold rounded-lg inline-flex items-center justify-center gap-2 whitespace-nowrap transition-colors shrink-0">Search</button>
<a href="{{ route('receivables.index') }}" class="h-10 px-4 inline-flex items-center justify-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 whitespace-nowrap shrink-0">Clear</a>
</div>
</div>
</div>
</div>
</form>

<div class="overflow-x-auto w-full -mx-4 sm:mx-0">
<table class="w-full text-left border-collapse min-w-[560px]">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Invoice</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Customer</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Amount</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Received</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Remaining</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24"></th>
</tr>
</thead>
<tbody class="divide-y divide-slate-200 dark:divide-slate-700">
@forelse($receivables as $r)
@php $remaining = (float)$r->amount - (float)$r->received; @endphp
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $r->date->format('Y-m-d') }}</td>
<td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white">{{ $r->invoice_number ?: '-' }}</td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $r->customer_name ?: $r->customer_code ?: '-' }}</td>
<td class="px-6 py-4 text-sm font-mono text-right text-slate-900 dark:text-white whitespace-nowrap"><span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($r->amount, 2) }}</span></td>
<td class="px-6 py-4 text-sm font-mono text-right text-slate-600 dark:text-slate-300 whitespace-nowrap"><span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($r->received, 2) }}</span></td>
<td class="px-6 py-4 text-sm font-mono font-bold text-right {{ $remaining > 0 ? 'text-black dark:text-white' : 'text-gray-600 dark:text-gray-400' }} whitespace-nowrap"><span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($remaining, 2) }}</span></td>
<td class="px-6 py-4">
@if ($remaining > 0)
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('receivables.edit', $r) }}" class="h-8 px-3 inline-flex items-center gap-1.5 text-xs font-medium text-black dark:text-white hover:bg-black/5 dark:hover:bg-white/5 rounded-lg transition-colors whitespace-nowrap">Record payment</a>
@else
<span class="text-xs text-slate-500 dark:text-slate-400">—</span>
@endif
@else
<span class="text-xs text-gray-600 dark:text-gray-400 font-medium">Paid</span>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
No receivables yet. Receivables are created automatically when you create a sale.
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

<div class="p-4 border-t border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-4">
<p class="text-sm text-slate-500 dark:text-slate-400">
@if($receivables->total() > 0)
Showing <span class="font-medium text-slate-900 dark:text-white">{{ $receivables->firstItem() }}</span> to <span class="font-medium text-slate-900 dark:text-white">{{ $receivables->lastItem() }}</span> of <span class="font-medium text-slate-900 dark:text-white">{{ $receivables->total() }}</span> results
@else
No results
@endif
</p>
@if($receivables->hasPages())
<nav class="flex items-center gap-1" aria-label="Pagination">
@if (!$receivables->onFirstPage())
<a class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors" href="{{ $receivables->previousPageUrl() }}"><span class="material-symbols-outlined text-[20px]">chevron_left</span></a>
@endif
@foreach ($receivables->getUrlRange(max(1, $receivables->currentPage() - 2), min($receivables->lastPage(), $receivables->currentPage() + 2)) ?: [1 => $receivables->url(1)] as $page => $url)
@if ($page == $receivables->currentPage())
<span class="px-3 py-1.5 text-sm font-medium rounded-lg bg-black text-white dark:bg-white dark:text-black">{{ $page }}</span>
@else
<a class="px-3 py-1.5 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" href="{{ $url }}">{{ $page }}</a>
@endif
@endforeach
@if ($receivables->hasMorePages())
<a class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors" href="{{ $receivables->nextPageUrl() }}"><span class="material-symbols-outlined text-[20px]">chevron_right</span></a>
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
