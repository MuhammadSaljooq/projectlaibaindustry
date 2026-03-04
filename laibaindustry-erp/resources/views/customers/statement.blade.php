<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Statement — ' . $customer->customer_name . ' - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'customers'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">

<header class="h-16 bg-white dark:bg-[#1a2632] border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-3">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<a href="{{ route('customers.index') }}" class="p-2 text-slate-500 hover:text-primary hover:bg-primary/5 rounded-lg transition-colors hidden sm:flex items-center gap-1">
<span class="material-symbols-outlined text-[20px]">arrow_back</span>
</a>
<h2 class="text-xl font-bold text-slate-800 dark:text-white hidden sm:block">Customer Statement</h2>
</div>
<div class="flex items-center gap-2">
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('customers.edit', $customer) }}"
   class="h-9 px-3 inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
<span class="material-symbols-outlined text-[17px]">edit</span>
<span class="hidden sm:inline">Edit Customer</span>
</a>
@endif
<a href="{{ route('customers.statement.pdf', $customer) }}"
   target="_blank"
   class="h-9 px-3 inline-flex items-center gap-1.5 text-xs font-semibold text-primary hover:bg-primary/5 rounded-lg border border-primary/30 transition-colors">
<span class="material-symbols-outlined text-[17px]">download</span>
<span class="hidden sm:inline">Download PDF</span>
</a>
<button onclick="window.print()"
    class="h-9 px-3 inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-600 transition-colors">
<span class="material-symbols-outlined text-[17px]">print</span>
<span class="hidden sm:inline">Print</span>
</button>
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 scroll-smooth">
<div class="max-w-[1100px] mx-auto flex flex-col gap-6">

@if (session('success'))
<div class="rounded-lg border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/20 dark:border-emerald-800 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-400">
{{ session('success') }}
</div>
@endif

{{-- Customer header card --}}
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
<div class="flex flex-wrap items-start justify-between gap-4">
<div>
<h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $customer->customer_name }}</h3>
<p class="text-sm text-slate-500 dark:text-slate-400 font-mono mt-0.5">{{ $customer->customer_code }}</p>
</div>
<div class="text-right">
<p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Closing Balance</p>
<p class="text-2xl font-bold font-mono tabular-nums mt-0.5 {{ $closingBalance > 0 ? 'text-amber-600 dark:text-amber-400' : ($closingBalance < 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-400') }}">
{{ $currencySymbol ?? '$' }} {{ number_format(abs($closingBalance), 2) }}
@if($closingBalance > 0)
<span class="text-xs font-medium ml-1">DR</span>
@elseif($closingBalance < 0)
<span class="text-xs font-medium ml-1">CR</span>
@endif
</p>
</div>
</div>
<div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm pt-4 border-t border-slate-200 dark:border-slate-700">
<div>
<p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Phone</p>
<p class="text-slate-900 dark:text-white">{{ $customer->phone ?: '—' }}</p>
</div>
<div>
<p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Email</p>
<p class="text-slate-900 dark:text-white">{{ $customer->email ?: '—' }}</p>
</div>
<div>
<p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Address</p>
<p class="text-slate-900 dark:text-white">{{ $customer->address ?: '—' }}</p>
</div>
<div>
<p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Opening Balance</p>
<p class="font-mono font-medium text-slate-900 dark:text-white tabular-nums">
{{ $currencySymbol ?? '$' }} {{ number_format($openingBalance, 2) }}
@if($customer->opening_balance_date)
<span class="text-xs text-slate-400 font-sans ml-1">as of {{ $customer->opening_balance_date->format('Y-m-d') }}</span>
@endif
</p>
</div>
</div>
</div>

{{-- Summary cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Debit</p>
<p class="text-xl font-bold text-slate-900 dark:text-white font-mono tabular-nums mt-1">
{{ $currencySymbol ?? '$' }} {{ number_format($totalDebit, 2) }}
</p>
<p class="text-xs text-slate-400 mt-1">Sales + payments you made to them</p>
</div>
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Credit</p>
<p class="text-xl font-bold text-slate-900 dark:text-white font-mono tabular-nums mt-1">
{{ $currencySymbol ?? '$' }} {{ number_format($totalCredit, 2) }}
</p>
<p class="text-xs text-slate-400 mt-1">Payments received + purchases from them</p>
</div>
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Net Balance</p>
<p class="text-xl font-bold font-mono tabular-nums mt-1 {{ $closingBalance > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
{{ $currencySymbol ?? '$' }} {{ number_format(abs($closingBalance), 2) }}
{{ $closingBalance > 0 ? '(Receivable)' : ($closingBalance < 0 ? '(Payable)' : '') }}
</p>
<p class="text-xs text-slate-400 mt-1">Opening {{ number_format($openingBalance, 2) }} + Debit − Credit</p>
</div>
</div>

{{-- Ledger table --}}
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
<div class="p-5 border-b border-slate-200 dark:border-slate-700">
<h3 class="text-base font-semibold text-slate-800 dark:text-white">Account Ledger</h3>
<p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">All transactions in chronological order with running balance</p>
</div>

<div class="overflow-x-auto">
<table class="w-full text-left border-collapse min-w-[780px]">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
<th class="px-5 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-32">Date</th>
<th class="px-5 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Description</th>
<th class="px-5 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Reference</th>
<th class="px-5 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right w-32">Debit</th>
<th class="px-5 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right w-32">Credit</th>
<th class="px-5 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right w-36">Balance</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-200 dark:divide-slate-700">

{{-- Opening balance row --}}
<tr class="bg-slate-50/60 dark:bg-slate-800/30">
<td class="px-5 py-3 text-sm text-slate-500 dark:text-slate-400">
{{ $customer->opening_balance_date ? $customer->opening_balance_date->format('Y-m-d') : '—' }}
</td>
<td class="px-5 py-3 text-sm">
<span class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-300 font-medium">
<span class="w-2 h-2 rounded-full bg-slate-400 inline-block shrink-0"></span>
Opening Balance
</span>
</td>
<td class="px-5 py-3 text-sm text-slate-500 dark:text-slate-400">—</td>
<td class="px-5 py-3 text-sm font-mono text-right text-slate-400">—</td>
<td class="px-5 py-3 text-sm font-mono text-right text-slate-400">—</td>
<td class="px-5 py-3 text-sm font-mono font-semibold text-right tabular-nums {{ $openingBalance > 0 ? 'text-amber-600 dark:text-amber-400' : ($openingBalance < 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500') }}">
{{ $currencySymbol ?? '$' }} {{ number_format($openingBalance, 2) }}
</td>
</tr>

@forelse($ledgerRows as $row)
@php
$isDebit  = $row['debit']  > 0;
$isCredit = $row['credit'] > 0;
$badge = match($row['source_type']) {
    'sale'             => ['label' => 'Sale',      'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
    'payment_received' => ['label' => 'Payment',   'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'],
    'purchase'         => ['label' => 'Purchase',  'class' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400'],
    'payment_made'     => ['label' => 'Paid Out',  'class' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400'],
    default            => ['label' => 'Entry',     'class' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'],
};
@endphp
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
<td class="px-5 py-3 text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">
{{ $row['date']->format('Y-m-d') }}
<span class="text-slate-400 text-xs ml-1">{{ $row['date']->format('H:i') }}</span>
</td>
<td class="px-5 py-3 text-sm">
<div class="flex items-center gap-2">
<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wide {{ $badge['class'] }} whitespace-nowrap">
{{ $badge['label'] }}
</span>
<span class="text-slate-900 dark:text-white font-medium">{{ $row['description'] }}</span>
</div>
</td>
<td class="px-5 py-3 text-sm text-slate-500 dark:text-slate-400 font-mono">
{{ $row['reference'] ?: '—' }}
</td>
<td class="px-5 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums {{ $isDebit ? 'text-slate-900 dark:text-white font-semibold' : 'text-slate-300 dark:text-slate-600' }}">
@if($isDebit)
{{ $currencySymbol ?? '$' }} {{ number_format($row['debit'], 2) }}
@else
—
@endif
</td>
<td class="px-5 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums {{ $isCredit ? 'text-emerald-700 dark:text-emerald-400 font-semibold' : 'text-slate-300 dark:text-slate-600' }}">
@if($isCredit)
{{ $currencySymbol ?? '$' }} {{ number_format($row['credit'], 2) }}
@else
—
@endif
</td>
<td class="px-5 py-3 text-sm font-mono font-semibold text-right whitespace-nowrap tabular-nums {{ $row['running_balance'] > 0 ? 'text-amber-600 dark:text-amber-400' : ($row['running_balance'] < 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500') }}">
{{ $currencySymbol ?? '$' }} {{ number_format($row['running_balance'], 2) }}
@if($row['running_balance'] > 0)
<span class="text-[10px] font-bold ml-0.5">DR</span>
@elseif($row['running_balance'] < 0)
<span class="text-[10px] font-bold ml-0.5">CR</span>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="6" class="px-5 py-12 text-center text-slate-400 dark:text-slate-500">
<span class="material-symbols-outlined text-4xl block mb-2">receipt_long</span>
No transactions yet. Transactions are added automatically when sales, purchases, or payments are recorded for this customer.
</td>
</tr>
@endforelse

</tbody>

@if(count($ledgerRows) > 0)
<tfoot>
<tr class="bg-slate-50 dark:bg-slate-800/50 border-t-2 border-slate-200 dark:border-slate-700">
<td colspan="3" class="px-5 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Totals</td>
<td class="px-5 py-3 text-sm font-bold font-mono text-right tabular-nums text-slate-900 dark:text-white whitespace-nowrap">
{{ $currencySymbol ?? '$' }} {{ number_format($totalDebit, 2) }}
</td>
<td class="px-5 py-3 text-sm font-bold font-mono text-right tabular-nums text-emerald-700 dark:text-emerald-400 whitespace-nowrap">
{{ $currencySymbol ?? '$' }} {{ number_format($totalCredit, 2) }}
</td>
<td class="px-5 py-3 text-sm font-bold font-mono text-right tabular-nums whitespace-nowrap {{ $closingBalance > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
{{ $currencySymbol ?? '$' }} {{ number_format(abs($closingBalance), 2) }}
{{ $closingBalance > 0 ? 'DR' : ($closingBalance < 0 ? 'CR' : '') }}
</td>
</tr>
</tfoot>
@endif
</table>
</div>
</div>

</div>
<div class="mt-8 text-center text-xs text-slate-400 pb-4">© {{ date('Y') }} Nexus ERP Inc. All rights reserved.</div>
</div>
</main>
</body>
</html>
