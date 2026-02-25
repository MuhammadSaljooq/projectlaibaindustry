<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Record Payment - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'payables'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-[#1a2632] border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<a href="{{ route('payables.index') }}" class="p-2 text-slate-500 hover:text-primary hover:bg-primary/5 rounded-lg transition-colors hidden sm:flex items-center gap-1">
<span class="material-symbols-outlined text-[20px]">arrow_back</span>
</a>
<h2 class="text-xl font-bold text-slate-800 dark:text-white hidden sm:block">Record Payment</h2>
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-6">
<div class="sm:hidden">
<h2 class="text-2xl font-bold text-slate-800 dark:text-white">Record Payment</h2>
</div>

@if (session('error'))
<div class="rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-400">
{{ session('error') }}
</div>
@endif
@if ($errors->any())
<div class="rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-400">
<ul class="list-disc list-inside space-y-0.5">
@foreach ($errors->all() as $err)
<li>{{ $err }}</li>
@endforeach
</ul>
</div>
@endif

@php
$balance = max(0, (float)$payable->amount - (float)$payable->received);
$isPaid  = $balance <= 0;
@endphp

<div class="max-w-lg">
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">

<h3 class="text-base font-semibold text-slate-800 dark:text-white mb-5">Payable Details</h3>

<div class="space-y-3 text-sm mb-6 pb-6 border-b border-slate-200 dark:border-slate-700">
<div class="flex items-center justify-between gap-4">
<span class="text-slate-500 dark:text-slate-400">Date</span>
<span class="font-medium text-slate-900 dark:text-white">{{ $payable->date->format('Y-m-d') }}</span>
</div>
<div class="flex items-center justify-between gap-4">
<span class="text-slate-500 dark:text-slate-400">Invoice Number</span>
<span class="font-medium text-slate-900 dark:text-white">{{ $payable->invoice_number ?: '-' }}</span>
</div>
<div class="flex items-center justify-between gap-4">
<span class="text-slate-500 dark:text-slate-400">Customer Name</span>
<span class="font-medium text-slate-900 dark:text-white">{{ $payable->customer_name ?: '-' }}</span>
</div>
<div class="flex items-center justify-between gap-4">
<span class="text-slate-500 dark:text-slate-400">Customer Code</span>
<span class="font-medium text-slate-900 dark:text-white">{{ $payable->customer_code ?: '-' }}</span>
</div>
</div>

<div class="grid grid-cols-3 gap-3 mb-6">
<div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-3 text-center">
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Total Amount</p>
<p class="text-base font-bold text-slate-900 dark:text-white font-mono tabular-nums">
{{ $currencySymbol ?? '$' }} {{ number_format($payable->amount, 2) }}
</p>
</div>
<div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-3 text-center">
<p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-1">Already Paid</p>
<p class="text-base font-bold text-emerald-700 dark:text-emerald-300 font-mono tabular-nums">
{{ $currencySymbol ?? '$' }} {{ number_format($payable->received, 2) }}
</p>
</div>
<div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3 text-center">
<p class="text-xs font-semibold text-amber-600 dark:text-amber-500 uppercase tracking-wider mb-1">Balance Due</p>
<p class="text-base font-bold text-amber-700 dark:text-amber-400 font-mono tabular-nums">
{{ $currencySymbol ?? '$' }} {{ number_format($balance, 2) }}
</p>
</div>
</div>

@if(!$isPaid)
<form method="POST" action="{{ route('payables.update', $payable) }}" novalidate>
@csrf
@method('PUT')
<div class="space-y-4">
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5" for="payment">
Payment amount <span class="text-red-500">*</span>
</label>
<div class="relative">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">{{ $currencySymbol ?? '$' }}</span>
<input class="w-full h-11 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 pl-7 pr-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary text-right font-mono"
    id="payment"
    name="payment"
    type="number"
    step="0.01"
    min="0.01"
    max="{{ $balance }}"
    value="{{ old('payment', number_format($balance, 2, '.', '')) }}"
    placeholder="{{ number_format($balance, 2) }}"
    required>
</div>
<p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
Maximum payment: <strong>{{ $currencySymbol ?? '$' }} {{ number_format($balance, 2) }}</strong>
</p>
</div>
</div>

<div class="flex flex-wrap gap-3 mt-6">
<button type="submit"
    class="h-10 px-5 bg-primary hover:bg-blue-600 text-white text-sm font-bold rounded-lg transition-colors whitespace-nowrap inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">payments</span>
Record Payment
</button>
<a href="{{ route('payables.index') }}"
    class="h-10 px-5 inline-flex items-center text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors whitespace-nowrap">
Cancel
</a>
</div>
</form>
@else
<div class="flex items-center gap-2 p-4 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800">
<span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400">check_circle</span>
<p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">This payable has been fully paid.</p>
</div>
<a href="{{ route('payables.index') }}"
    class="inline-flex items-center gap-1.5 mt-4 text-sm font-medium text-primary hover:underline">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
Back to Payables
</a>
@endif

</div>
</div>

<div class="mt-8 text-center text-xs text-slate-400 pb-4">© {{ date('Y') }} Nexus ERP Inc. All rights reserved.</div>
</div>
</div>
</main>
</body>
</html>
