<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Sale #' . ($sale->invoice_number ?: $sale->id) . ' - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'sales'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-[#1a2632] border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<a href="{{ route('sales.index') }}" class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-300 hover:text-primary transition-colors">
<span class="material-symbols-outlined">arrow_back</span>
<span class="text-xl font-bold text-slate-800 dark:text-white hidden sm:inline">Back to Sales</span>
</a>
</div>
</header>
<div class="flex-1 overflow-y-auto p-6 scroll-smooth">
<div class="max-w-[900px] mx-auto flex flex-col gap-6">

<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
<div class="p-6 border-b border-slate-200 dark:border-slate-700">
<div class="flex flex-wrap items-start justify-between gap-4">
<div>
<h1 class="text-2xl font-bold text-slate-900 dark:text-white">Sale {{ $sale->invoice_number ?: '#' . $sale->id }}</h1>
<p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $sale->date->format('l, F j, Y \a\t g:i A') }}</p>
</div>
<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $sale->status === 'completed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300' }}">{{ ucfirst($sale->status) }}</span>
</div>
<div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
<div>
<span class="text-slate-500 dark:text-slate-400">Customer</span>
<p class="font-medium text-slate-900 dark:text-white">{{ $sale->customer_name ?: $sale->customer_code ?: 'Walk-in' }}</p>
@if($sale->customer_code)
<p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">Code: {{ $sale->customer_code }}</p>
@endif
</div>
@if($sale->invoice_number)
<div>
<span class="text-slate-500 dark:text-slate-400">Invoice number</span>
<p class="font-medium text-slate-900 dark:text-white font-mono">{{ $sale->invoice_number }}</p>
</div>
@endif
</div>
</div>

@php $symbol = $sale->currency && $sale->currency->symbol ? $sale->currency->symbol : '$'; @endphp

<div class="overflow-x-auto">
<table class="w-full text-left border-collapse min-w-[520px]">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
<th class="px-6 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">#</th>
<th class="px-6 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Product</th>
<th class="px-6 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Qty</th>
<th class="px-6 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Unit price</th>
<th class="px-6 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Tax</th>
<th class="px-6 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Line total</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-200 dark:divide-slate-700">
@foreach($sale->items as $index => $item)
@php
$lineTotal = ($item->selling_price * $item->quantity) + ($item->tax_applied ?? 0);
@endphp
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
<td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 font-mono">{{ $index + 1 }}</td>
<td class="px-6 py-4">
<p class="font-medium text-slate-900 dark:text-white">{{ $item->product ? $item->product->name : 'Product #' . $item->product_id }}</p>
@if($item->product && $item->product->sku)
<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">SKU: {{ $item->product->sku }}</p>
@endif
</td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 font-mono text-right">{{ number_format($item->quantity) }}</td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 font-mono text-right">{{ $symbol }}{{ number_format($item->selling_price, 2) }}</td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 font-mono text-right">{{ $symbol }}{{ number_format($item->tax_applied ?? 0, 2) }}</td>
<td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white font-mono text-right">{{ $symbol }}{{ number_format($lineTotal, 2) }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>

<div class="p-6 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/30">
<div class="max-w-xs ml-auto space-y-2">
<div class="flex justify-between text-sm">
<span class="text-slate-600 dark:text-slate-400">Subtotal</span>
<span class="font-mono text-slate-900 dark:text-white">{{ $symbol }}{{ number_format($sale->subtotal, 2) }}</span>
</div>
<div class="flex justify-between text-sm">
<span class="text-slate-600 dark:text-slate-400">Tax ({{ number_format($sale->tax_rate ?? 0, 0) }}%)</span>
<span class="font-mono text-slate-900 dark:text-white">{{ $symbol }}{{ number_format($sale->tax_amount ?? 0, 2) }}</span>
</div>
@if((float)($sale->discount_amount ?? 0) > 0)
<div class="flex justify-between text-sm">
<span class="text-slate-600 dark:text-slate-400">Discount</span>
<span class="font-mono text-slate-900 dark:text-white">-{{ $symbol }}{{ number_format($sale->discount_amount, 2) }}</span>
</div>
@endif
<div class="flex justify-between text-base font-bold pt-2 border-t border-slate-200 dark:border-slate-600">
<span class="text-slate-900 dark:text-white">Total</span>
<span class="font-mono text-primary">{{ $symbol }}{{ number_format($sale->total_amount, 2) }}</span>
</div>
</div>
</div>
</div>

<div class="flex flex-wrap gap-3 mt-4">
<a href="{{ route('sales.index') }}" class="inline-flex items-center gap-2 h-10 px-4 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-sm font-medium rounded-lg transition-colors">
<span class="material-symbols-outlined text-[20px]">list</span>
All Sales
</a>
</div>
<div class="mt-8 text-center text-xs text-slate-400 pb-4">© {{ date('Y') }} Nexus ERP Inc. All rights reserved.</div>
</div>
</main>
</body>
</html>
