<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Payable ' . ($payable->invoice_number ?: '#' . $payable->id) . ' - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'payables'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-black border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<a href="{{ route('payables.index') }}" class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-300 hover:text-black dark:hover:text-white transition-colors">
<span class="material-symbols-outlined">arrow_back</span>
<span class="text-xl font-bold text-slate-800 dark:text-white hidden sm:inline">Back to Payables</span>
</a>
</div>
</header>
<div class="flex-1 overflow-y-auto p-6 scroll-smooth">
<div class="max-w-[900px] mx-auto flex flex-col gap-6">

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

@php $remaining = (float)$payable->amount - (float)($payable->received ?? 0); @endphp

<div class="bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
<div class="p-6 border-b border-slate-200 dark:border-slate-700">
<h1 class="text-2xl font-bold text-slate-900 dark:text-white">Payable {{ $payable->invoice_number ?: '#' . $payable->id }}</h1>
<p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $payable->date->format('l, F j, Y') }}</p>
<dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
<div>
<span class="text-slate-500 dark:text-slate-400">Customer</span>
<p class="font-medium text-slate-900 dark:text-white">{{ $payable->customer_name ?: $payable->customer_code ?: '—' }}</p>
@if($payable->customer_code)
<p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">Code: {{ $payable->customer_code }}</p>
@endif
</div>
@if($payable->invoice_number)
<div>
<span class="text-slate-500 dark:text-slate-400">Invoice number</span>
<p class="font-medium text-slate-900 dark:text-white font-mono">{{ $payable->invoice_number }}</p>
</div>
@endif
<div>
<span class="text-slate-500 dark:text-slate-400">Total amount</span>
<p class="font-mono font-bold text-slate-900 dark:text-white tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($payable->amount, 2) }}</p>
</div>
<div>
<span class="text-slate-500 dark:text-slate-400">Paid</span>
<p class="font-mono text-slate-900 dark:text-white tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($payable->received ?? 0, 2) }}</p>
</div>
<div>
<span class="text-slate-500 dark:text-slate-400">Remaining</span>
<p class="font-mono font-bold text-lg tabular-nums {{ $remaining > 0 ? 'text-black dark:text-white' : 'text-gray-600 dark:text-gray-400' }}">{{ $currencySymbol ?? '$' }} {{ number_format($remaining, 2) }}</p>
</div>
@if($payable->received_date)
<div>
<span class="text-slate-500 dark:text-slate-400">Last payment date</span>
<p class="text-slate-900 dark:text-white">{{ $payable->received_date->format('Y-m-d') }}</p>
</div>
@endif
</dl>
</div>

<div id="record-payment" class="p-6 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/30">
@if ($remaining > 0)
<h3 class="text-base font-semibold text-slate-800 dark:text-white mb-4">Record payment</h3>
<form method="POST" action="{{ route('payables.update', $payable) }}">
@csrf
@method('PUT')
<div class="flex flex-wrap items-end gap-4">
<div class="flex-1 min-w-[200px]">
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="received">Amount <span class="text-red-500">*</span></label>
<input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" id="received" name="received" type="number" step="0.01" min="0.01" max="{{ $remaining }}" value="{{ old('received', $remaining) }}" placeholder="Max {{ number_format($remaining, 2) }}" required>
</div>
<button type="submit" class="h-10 px-5 bg-black hover:bg-gray-800 text-white text-sm font-bold rounded-lg transition-colors whitespace-nowrap">Record payment</button>
</div>
</form>
@else
<p class="text-gray-600 dark:text-gray-400 font-medium">This payable has been fully paid.</p>
@endif
</div>
</div>

<div class="flex flex-wrap items-center gap-3 mt-6 pt-4 border-t border-slate-200 dark:border-slate-700">
<a href="{{ route('payables.index') }}" class="inline-flex items-center justify-center gap-2 h-10 px-4 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-sm font-medium transition-colors shrink-0">
<span class="material-symbols-outlined text-[20px]">list</span>
All Payables
</a>
@if($payable->purchase_id)
<a href="{{ route('purchases.show', $payable->purchase) }}" class="inline-flex items-center justify-center gap-2 h-10 px-4 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-sm font-medium transition-colors shrink-0">
<span class="material-symbols-outlined text-[20px]">receipt</span>
View Purchase
</a>
@endif
<form method="POST" action="{{ route('payables.destroy', $payable) }}" class="inline-flex items-center shrink-0" onsubmit="return confirm('Are you sure you want to delete this payable?');">
@csrf
@method('DELETE')
<button type="submit" class="inline-flex items-center justify-center gap-2 h-10 px-4 rounded-lg bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 text-red-700 dark:text-red-400 text-sm font-medium transition-colors border border-red-200 dark:border-red-800 shrink-0">
<span class="material-symbols-outlined text-[20px]">delete</span>
Delete Payable
</button>
</form>
</div>
</div>
</main>
</body>
</html>
