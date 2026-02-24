<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Customer Statement - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'customers'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-black border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
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

<div class="bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
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
<div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700 grid grid-cols-1 sm:grid-cols-3 gap-4">
<div>
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Closing balance</p>
<p class="text-lg font-bold {{ ($closing_balance ?? 0) > 0 ? 'text-black dark:text-white' : (($closing_balance ?? 0) < 0 ? 'text-gray-600 dark:text-gray-400' : 'text-slate-600 dark:text-slate-400') }} tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($closing_balance ?? 0, 2) }}</p>
</div>
<div>
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total debit</p>
<p class="text-lg font-bold text-slate-900 dark:text-white tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($total_debit ?? 0, 2) }}</p>
</div>
<div>
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total credit</p>
<p class="text-lg font-bold text-slate-900 dark:text-white tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($total_credit ?? 0, 2) }}</p>
</div>
</div>
</div>

<form method="GET" action="{{ route('customers.statement', $customer) }}" class="p-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/30 flex flex-wrap items-end gap-4">
<div>
<label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1" for="from_date">From date</label>
<input class="h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 text-slate-900 dark:text-white text-sm" id="from_date" name="from_date" type="date" value="{{ $from_date ?? '' }}">
</div>
<div>
<label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1" for="to_date">To date</label>
<input class="h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 text-slate-900 dark:text-white text-sm" id="to_date" name="to_date" type="date" value="{{ $to_date ?? '' }}">
</div>
<button type="submit" class="h-10 px-4 bg-black hover:bg-gray-800 text-white text-sm font-medium rounded-lg">Filter</button>
<a href="{{ route('customers.statement', $customer) }}" class="h-10 px-4 inline-flex items-center text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600">Clear</a>
</form>

<div class="p-4 border-b border-slate-200 dark:border-slate-700">
<p class="text-sm text-slate-600 dark:text-slate-400">
@if(isset($from_date) || isset($to_date))
Opening balance (brought forward): <span class="font-mono font-semibold text-slate-900 dark:text-white tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($opening_balance ?? 0, 2) }}</span>
@else
Opening balance: <span class="font-mono font-semibold text-slate-900 dark:text-white tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($opening_balance ?? 0, 2) }}</span> (from inception)
@endif
</p>
</div>

@php
$statementTypeLabels = [
    'invoice' => 'Invoice',
    'payment' => 'Payment',
    'payable' => 'Bill',
    'payable_payment' => 'Payment (to supplier)',
    'adjustment' => 'Adjustment',
];
@endphp
<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[700px]">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Type</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Reference</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Debit</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Credit</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Running balance</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Payment type</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-200 dark:divide-slate-700">
@forelse($lines ?? [] as $line)
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $line['date'] }}</td>
<td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white">{{ $statementTypeLabels[$line['type']] ?? ucfirst($line['type']) }}</td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $line['reference'] }}</td>
<td class="px-6 py-4 text-sm font-mono text-right text-slate-900 dark:text-white whitespace-nowrap">
@if((float)($line['debit'] ?? 0) > 0)
<span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($line['debit'], 2) }}</span>
@else
<span class="text-slate-400">—</span>
@endif
</td>
<td class="px-6 py-4 text-sm font-mono text-right text-slate-900 dark:text-white whitespace-nowrap">
@if((float)($line['credit'] ?? 0) > 0)
<span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($line['credit'], 2) }}</span>
@else
<span class="text-slate-400">—</span>
@endif
</td>
<td class="px-6 py-4 text-sm font-mono font-medium text-right whitespace-nowrap {{ ($line['running_balance'] ?? 0) > 0 ? 'text-black dark:text-white' : (($line['running_balance'] ?? 0) < 0 ? 'text-gray-600 dark:text-gray-400' : 'text-slate-600 dark:text-slate-400') }}"><span class="tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($line['running_balance'] ?? 0, 2) }}</span></td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $line['payment_type'] ?? '—' }}</td>
</tr>
@empty
<tr>
<td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
No transactions in this period.
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

<div class="p-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/30 flex flex-wrap items-center gap-6">
<p class="text-sm font-semibold text-slate-800 dark:text-white">Closing balance: <span class="font-mono tabular-nums {{ ($closing_balance ?? 0) > 0 ? 'text-black dark:text-white' : (($closing_balance ?? 0) < 0 ? 'text-gray-600 dark:text-gray-400' : '') }}">{{ $currencySymbol ?? '$' }} {{ number_format($closing_balance ?? 0, 2) }}</span></p>
<p class="text-sm font-semibold text-slate-800 dark:text-white">Total debit: <span class="font-mono tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($total_debit ?? 0, 2) }}</span></p>
<p class="text-sm font-semibold text-slate-800 dark:text-white">Total credit: <span class="font-mono tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($total_credit ?? 0, 2) }}</span></p>
</div>
</div>

@if(auth()->user()->role !== 'viewer')
<div class="mt-4">
<a class="h-10 px-4 inline-flex items-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors whitespace-nowrap shrink-0" href="{{ route('customers.edit', $customer) }}">
<span class="material-symbols-outlined text-[20px] shrink-0">edit</span>
<span>Edit Customer</span>
</a>
</div>
@endif
</div>
<div class="mt-8 text-center text-xs text-slate-400 pb-4">© {{ date('Y') }} Nexus ERP Inc. All rights reserved.</div>
</div>
</main>
</body>
</html>
