<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Customer Statement - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'customers'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-[#1a2632] border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<h2 class="text-xl font-bold text-slate-800 dark:text-white hidden sm:block">Customer Statement</h2>
</div>
<div class="flex items-center gap-4">
<a class="h-9 px-4 flex items-center text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors" href="{{ route('customers.index') }}">
<span class="material-symbols-outlined text-[20px] mr-1">arrow_back</span>Back to Customers
</a>
</div>
</header>
<div class="flex-1 overflow-y-auto p-6 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-6">
<div class="sm:hidden">
<h2 class="text-2xl font-bold text-slate-800 dark:text-white">Customer Statement</h2>
</div>

<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
<div class="p-6 border-b border-slate-200 dark:border-slate-700">
<h3 class="text-base font-semibold text-slate-800 dark:text-white mb-4">Account Summary</h3>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
<div>
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Customer Name</p>
<p class="text-sm font-medium text-slate-900 dark:text-white mt-0.5">{{ $customer->customer_name }}</p>
</div>
<div>
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Customer Code</p>
<p class="text-sm font-medium text-slate-900 dark:text-white mt-0.5 font-mono">{{ $customer->customer_code }}</p>
</div>
<div>
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Contact</p>
<p class="text-sm font-medium text-slate-900 dark:text-white mt-0.5">{{ $customer->phone ?? '-' }}</p>
</div>
<div>
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email</p>
<p class="text-sm font-medium text-slate-900 dark:text-white mt-0.5">{{ $customer->email ?? '-' }}</p>
</div>
</div>
<div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Outstanding</p>
<p class="text-lg font-bold {{ $totalOutstanding > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">${{ number_format($totalOutstanding, 2) }}</p>
</div>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Invoice</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Amount</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Received</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Balance</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-200 dark:divide-slate-700">
@forelse($receivables as $receivable)
@php
    $balance = (float) $receivable->amount - (float) $receivable->received;
@endphp
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $receivable->date->format('Y-m-d H:i') }}</td>
<td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white">{{ $receivable->invoice_number ?: '-' }}</td>
<td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white font-mono text-right">${{ number_format($receivable->amount, 2) }}</td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 font-mono text-right">${{ number_format($receivable->received, 2) }}</td>
<td class="px-6 py-4 text-sm font-mono text-right {{ $balance > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">${{ number_format($balance, 2) }}</td>
</tr>
@empty
<tr>
<td colspan="5" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
No invoices or receivables for this customer yet.
</td>
</tr>
@endforelse
</tbody>
</table>
</div>
</div>

<div class="mt-4">
<a class="h-10 px-4 inline-flex items-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors whitespace-nowrap shrink-0" href="{{ route('customers.edit', $customer) }}">
<span class="material-symbols-outlined text-[20px] shrink-0">edit</span>
<span>Edit Customer</span>
</a>
</div>
</div>
<div class="mt-8 text-center text-xs text-slate-400 pb-4">© {{ date('Y') }} Nexus ERP Inc. All rights reserved.</div>
</div>
</main>
</body>
</html>
