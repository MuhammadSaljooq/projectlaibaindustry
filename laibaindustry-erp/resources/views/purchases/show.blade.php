<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Purchase ' . ($purchase->invoice_number ?: '#' . $purchase->id) . ' - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'purchases'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-black border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<a href="{{ route('purchases.index') }}" class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-300 hover:text-black dark:hover:text-white transition-colors">
<span class="material-symbols-outlined">arrow_back</span>
<span class="text-xl font-bold text-slate-800 dark:text-white hidden sm:inline">Back to Purchases</span>
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

<div class="bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
<div class="p-6 border-b border-slate-200 dark:border-slate-700">
<div class="flex flex-wrap items-start justify-between gap-4">
<div>
<h1 class="text-2xl font-bold text-slate-900 dark:text-white">Purchase {{ $purchase->invoice_number ?: '#' . $purchase->id }}</h1>
<p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $purchase->date->format('l, F j, Y \a\t g:i A') }}</p>
</div>
</div>
<div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
<div>
<span class="text-slate-500 dark:text-slate-400">Customer</span>
<p class="font-medium text-slate-900 dark:text-white">{{ $purchase->customer_name ?: $purchase->customer_code ?: '—' }}</p>
@if($purchase->customer_code)
<p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">Code: {{ $purchase->customer_code }}</p>
@endif
</div>
@if($purchase->invoice_number)
<div>
<span class="text-slate-500 dark:text-slate-400">Invoice number</span>
<p class="font-medium text-slate-900 dark:text-white font-mono">{{ $purchase->invoice_number }}</p>
</div>
@endif
</div>
</div>

@php $symbol = $purchase->currency && $purchase->currency->symbol ? $purchase->currency->symbol : ($currencySymbol ?? '$'); @endphp

<div class="overflow-x-auto">
<table class="w-full text-left border-collapse min-w-[520px]">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
<th class="px-6 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">#</th>
<th class="px-6 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Product</th>
<th class="px-6 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Qty</th>
<th class="px-6 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Unit price</th>
<th class="px-6 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Amount</th>
<th class="px-6 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">VAT 15%</th>
<th class="px-6 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Line total</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-200 dark:divide-slate-700">
@foreach($purchase->items as $index => $item)
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
<td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 font-mono">{{ $index + 1 }}</td>
<td class="px-6 py-4 font-medium text-slate-900 dark:text-white">{{ $item->product_name ?: '—' }}</td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 font-mono text-right">{{ number_format($item->quantity) }}</td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 font-mono text-right whitespace-nowrap"><span class="tabular-nums">{{ $symbol }} {{ number_format($item->price ?? 0, 2) }}</span></td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 font-mono text-right whitespace-nowrap"><span class="tabular-nums">{{ $symbol }} {{ number_format($item->amount ?? 0, 2) }}</span></td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 font-mono text-right whitespace-nowrap"><span class="tabular-nums">{{ $symbol }} {{ number_format($item->vat_amount ?? 0, 2) }}</span></td>
<td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white font-mono text-right whitespace-nowrap"><span class="tabular-nums">{{ $symbol }} {{ number_format($item->subtotal ?? 0, 2) }}</span></td>
</tr>
@endforeach
</tbody>
</table>
</div>

<div class="p-6 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/30">
<div class="max-w-xs ml-auto space-y-2">
<div class="flex justify-between text-sm">
<span class="text-slate-600 dark:text-slate-400">Subtotal</span>
<span class="font-mono text-slate-900 dark:text-white tabular-nums">{{ $symbol }} {{ number_format($purchase->subtotal ?? 0, 2) }}</span>
</div>
<div class="flex justify-between text-sm">
<span class="text-slate-600 dark:text-slate-400">VAT (15%)</span>
<span class="font-mono text-slate-900 dark:text-white tabular-nums">{{ $symbol }} {{ number_format($purchase->vat_amount ?? 0, 2) }}</span>
</div>
<div class="flex justify-between text-base font-bold pt-2 border-t border-slate-200 dark:border-slate-600">
<span class="text-slate-900 dark:text-white">Total</span>
<span class="font-mono text-black dark:text-white tabular-nums">{{ $symbol }} {{ number_format($purchase->total_amount ?? 0, 2) }}</span>
</div>
</div>
</div>
</div>

<div class="flex flex-wrap items-center gap-3 mt-6 pt-4 border-t border-slate-200 dark:border-slate-700">
<a href="{{ route('purchases.index') }}" class="inline-flex items-center justify-center gap-2 h-10 px-4 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-sm font-medium transition-colors shrink-0">
<span class="material-symbols-outlined text-[20px]">list</span>
All Purchases
</a>
<a href="{{ route('purchases.edit', $purchase) }}" class="inline-flex items-center justify-center gap-2 h-10 px-4 rounded-lg bg-black hover:bg-gray-800 text-white text-sm font-medium transition-colors shrink-0">
<span class="material-symbols-outlined text-[20px]">edit</span>
Edit Purchase
</a>
<form method="POST" action="{{ route('purchases.destroy', $purchase) }}" class="inline-flex items-center shrink-0" onsubmit="return confirm('Are you sure you want to delete this purchase?');">
@csrf
@method('DELETE')
<button type="submit" class="inline-flex items-center justify-center gap-2 h-10 px-4 rounded-lg bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 text-red-700 dark:text-red-400 text-sm font-medium transition-colors border border-red-200 dark:border-red-800 shrink-0">
<span class="material-symbols-outlined text-[20px]">delete</span>
Delete Purchase
</button>
</form>
</div>
</div>
</main>
</body>
</html>
