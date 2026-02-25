<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Payables - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'payables'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-[#1a2632] border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<h2 class="text-xl font-bold text-slate-800 dark:text-white hidden sm:block">Payables</h2>
</div>
<div class="flex items-center gap-4">
<div class="relative hidden sm:block">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 material-symbols-outlined text-[20px]">search</span>
<input class="h-9 pl-10 pr-4 text-sm bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary/50 w-64 placeholder-slate-400 text-slate-700 dark:text-slate-200 transition-all" placeholder="Global search..." type="text" disabled>
</div>
<button class="p-2 text-slate-500 hover:text-primary hover:bg-primary/5 rounded-full relative transition-colors" type="button">
<span class="material-symbols-outlined">notifications</span>
<span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border border-white dark:border-[#1a2632]"></span>
</button>
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-6">
<div class="sm:hidden">
<h2 class="text-2xl font-bold text-slate-800 dark:text-white">Payables</h2>
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

{{-- Summary cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Payable</p>
<p class="text-2xl font-bold text-slate-900 dark:text-white font-mono mt-1">
<span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_amount ?? 0, 2) }}</span>
</p>
</div>
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Amount Paid</p>
<p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 font-mono mt-1">
<span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_received ?? 0, 2) }}</span>
</p>
</div>
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Outstanding Balance</p>
<p class="text-2xl font-bold text-primary font-mono mt-1">
<span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_outstanding ?? 0, 2) }}</span>
</p>
</div>
</div>

<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col flex-1 min-h-[400px]">
<div class="p-5 border-b border-slate-200 dark:border-slate-700">
<h3 class="text-base font-semibold text-slate-800 dark:text-white">Payables</h3>
<p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Automatically created from purchases. Record payments via the action button.</p>
</div>

<div class="overflow-x-auto w-full -mx-4 sm:mx-0">
<table class="w-full text-left border-collapse min-w-[860px]">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
<th class="px-5 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Date</th>
<th class="px-5 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Invoice Number</th>
<th class="px-5 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Customer Name</th>
<th class="px-5 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Customer Code</th>
<th class="px-5 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right whitespace-nowrap">Amount</th>
<th class="px-5 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right whitespace-nowrap">Received Amount</th>
<th class="px-5 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right whitespace-nowrap">Balance</th>
<th class="px-5 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-36"></th>
</tr>
</thead>
<tbody class="divide-y divide-slate-200 dark:divide-slate-700">
@forelse($payables as $p)
@php $balance = (float)$p->amount - (float)$p->received; @endphp
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
<td class="px-5 py-3.5 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $p->date->format('Y-m-d') }}</td>
<td class="px-5 py-3.5 text-sm font-medium text-slate-900 dark:text-white">{{ $p->invoice_number ?: '-' }}</td>
<td class="px-5 py-3.5 text-sm text-slate-600 dark:text-slate-300">{{ $p->customer_name ?: '-' }}</td>
<td class="px-5 py-3.5 text-sm text-slate-600 dark:text-slate-300">{{ $p->customer_code ?: '-' }}</td>
<td class="px-5 py-3.5 text-sm font-mono text-right text-slate-900 dark:text-white whitespace-nowrap">
<span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($p->amount, 2) }}</span>
</td>
<td class="px-5 py-3.5 text-sm font-mono text-right whitespace-nowrap">
<span class="tabular-nums {{ (float)$p->received > 0 ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-slate-500 dark:text-slate-400' }}">
{{ $currencySymbol ?? '$' }} {{ number_format($p->received, 2) }}
</span>
</td>
<td class="px-5 py-3.5 text-sm font-mono font-bold text-right whitespace-nowrap">
<span class="tabular-nums {{ $balance > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
{{ $currencySymbol ?? '$' }} {{ number_format($balance, 2) }}
</span>
</td>
            <td class="px-5 py-3.5 text-right">
<div class="inline-flex items-center justify-end gap-2">
@if($balance > 0)
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('payables.edit', $p) }}"
   class="h-8 px-3 inline-flex items-center gap-1.5 text-xs font-semibold text-white bg-primary hover:bg-blue-600 rounded-lg transition-colors whitespace-nowrap">
<span class="material-symbols-outlined text-[15px]">payments</span>
Record payment
</a>
@else
<span class="text-xs text-slate-500 dark:text-slate-400">—</span>
@endif
@else
<span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
<span class="material-symbols-outlined text-[15px]">check_circle</span>
Paid
</span>
@endif
@if(in_array(auth()->user()->role, ['admin', 'manager']))
<form method="POST" action="{{ route('payables.destroy', $p) }}"
      onsubmit="return confirm('Delete this payable entry? This cannot be undone.');">
@csrf
@method('DELETE')
<button type="submit"
    class="h-8 w-8 inline-flex items-center justify-center text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
    title="Delete payable">
<span class="material-symbols-outlined text-[18px]">delete</span>
</button>
</form>
@endif
</div>
</td>
</tr>
@empty
<tr>
<td colspan="8" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
No payables yet. Payables are created automatically when you save a purchase.
<a href="{{ route('purchases.create') }}" class="text-primary font-medium hover:underline ml-1">Create a purchase</a>
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

<div class="p-4 border-t border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-4">
<p class="text-sm text-slate-500 dark:text-slate-400">
@if($payables->total() > 0)
Showing <span class="font-medium text-slate-900 dark:text-white">{{ $payables->firstItem() }}</span>
to <span class="font-medium text-slate-900 dark:text-white">{{ $payables->lastItem() }}</span>
of <span class="font-medium text-slate-900 dark:text-white">{{ $payables->total() }}</span> results
@else
No results
@endif
</p>
@if($payables->hasPages())
<nav class="flex items-center gap-1" aria-label="Pagination">
@if (!$payables->onFirstPage())
<a class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors" href="{{ $payables->previousPageUrl() }}">
<span class="material-symbols-outlined text-[20px]">chevron_left</span>
</a>
@endif
@foreach ($payables->getUrlRange(max(1, $payables->currentPage() - 2), min($payables->lastPage(), $payables->currentPage() + 2)) ?: [1 => $payables->url(1)] as $page => $url)
@if ($page == $payables->currentPage())
<span class="px-3 py-1.5 text-sm font-medium rounded-lg bg-primary text-white">{{ $page }}</span>
@else
<a class="px-3 py-1.5 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" href="{{ $url }}">{{ $page }}</a>
@endif
@endforeach
@if ($payables->hasMorePages())
<a class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors" href="{{ $payables->nextPageUrl() }}">
<span class="material-symbols-outlined text-[20px]">chevron_right</span>
</a>
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
