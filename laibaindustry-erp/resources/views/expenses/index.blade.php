<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Expenses - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'expenses'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-[#1a2632] border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<h2 class="text-xl font-bold text-slate-800 dark:text-white hidden sm:block">Expenses</h2>
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-6">
<div class="sm:hidden">
<h2 class="text-2xl font-bold text-slate-800 dark:text-white">Expenses</h2>
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

<div class="grid grid-cols-1 sm:grid-cols-1 gap-4">
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Expenses</p>
<p class="text-2xl font-bold text-slate-900 dark:text-white font-mono mt-1">
<span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totalAmount ?? 0, 2) }}</span>
</p>
</div>
</div>

<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col flex-1 min-h-[400px]">
<div class="p-5 border-b border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-3">
<div>
<h3 class="text-base font-semibold text-slate-800 dark:text-white">Expenses</h3>
<p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Track your business expenses by date, type, and amount.</p>
</div>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('expenses.create') }}" class="h-10 px-4 inline-flex items-center gap-2 text-sm font-semibold text-white bg-primary hover:bg-blue-600 rounded-lg transition-colors shrink-0">
<span class="material-symbols-outlined text-[20px]">add</span>
<span>Add Expense</span>
</a>
@endif
</div>

<div class="overflow-x-auto w-full -mx-4 sm:mx-0">
<table class="w-full text-left border-collapse min-w-[500px]">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
<th class="px-5 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Date</th>
<th class="px-5 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider whitespace-nowrap">Type</th>
<th class="px-5 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right whitespace-nowrap">Amount</th>
@if(auth()->user()->role !== 'viewer')
<th class="px-5 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-32"></th>
@endif
</tr>
</thead>
<tbody class="divide-y divide-slate-200 dark:divide-slate-700">
@forelse($expenses as $expense)
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
<td class="px-5 py-3.5 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $expense->date->format('Y-m-d') }}</td>
<td class="px-5 py-3.5 text-sm font-medium text-slate-900 dark:text-white">{{ $expense->type }}</td>
<td class="px-5 py-3.5 text-sm font-mono text-right text-slate-900 dark:text-white whitespace-nowrap">
<span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($expense->amount, 2) }}</span>
</td>
@if(auth()->user()->role !== 'viewer')
<td class="px-5 py-3.5">
<div class="flex items-center gap-2">
<a href="{{ route('expenses.edit', $expense) }}" class="h-8 px-3 inline-flex items-center gap-1.5 text-xs font-medium text-primary hover:bg-primary/5 rounded-lg transition-colors whitespace-nowrap">Edit</a>
<form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="inline" onsubmit="return confirm('Delete this expense?');">
@csrf
@method('DELETE')
<button type="submit" class="h-8 px-3 inline-flex items-center gap-1.5 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors whitespace-nowrap">Delete</button>
</form>
</div>
</td>
@endif
</tr>
@empty
<tr>
<td colspan="{{ auth()->user()->role === 'viewer' ? 3 : 4 }}" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
No expenses yet. @if(auth()->user()->role !== 'viewer')<a href="{{ route('expenses.create') }}" class="text-primary font-medium hover:underline">Add your first expense</a>@else<span>No expenses recorded yet.</span>@endif
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

<div class="p-4 border-t border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-4">
<p class="text-sm text-slate-500 dark:text-slate-400">
@if($expenses->total() > 0)
Showing <span class="font-medium text-slate-900 dark:text-white">{{ $expenses->firstItem() }}</span> to <span class="font-medium text-slate-900 dark:text-white">{{ $expenses->lastItem() }}</span> of <span class="font-medium text-slate-900 dark:text-white">{{ $expenses->total() }}</span> results
@else
No results
@endif
</p>
@if($expenses->hasPages())
<nav class="flex items-center gap-1" aria-label="Pagination">
@if (!$expenses->onFirstPage())
<a class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors" href="{{ $expenses->previousPageUrl() }}"><span class="material-symbols-outlined text-[20px]">chevron_left</span></a>
@endif
@foreach ($expenses->getUrlRange(max(1, $expenses->currentPage() - 2), min($expenses->lastPage(), $expenses->currentPage() + 2)) ?: [1 => $expenses->url(1)] as $page => $url)
@if ($page == $expenses->currentPage())
<span class="px-3 py-1.5 text-sm font-medium rounded-lg bg-primary text-white">{{ $page }}</span>
@else
<a class="px-3 py-1.5 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" href="{{ $url }}">{{ $page }}</a>
@endif
@endforeach
@if ($expenses->hasMorePages())
<a class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors" href="{{ $expenses->nextPageUrl() }}"><span class="material-symbols-outlined text-[20px]">chevron_right</span></a>
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
