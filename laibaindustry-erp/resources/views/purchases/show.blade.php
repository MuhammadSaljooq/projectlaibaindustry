<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Purchase #' . $purchase->id . ' - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'purchases'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-[#1a2632] border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<a href="{{ route('purchases.index') }}" class="p-2 text-slate-500 hover:text-primary hover:bg-primary/5 rounded-lg transition-colors hidden sm:flex items-center gap-1">
<span class="material-symbols-outlined text-[20px]">arrow_back</span>
</a>
<h2 class="text-xl font-bold text-slate-800 dark:text-white hidden sm:block">
Purchase {{ $purchase->invoice_number ?: '#' . $purchase->id }}
</h2>
</div>
<div class="flex items-center gap-3">
@if(auth()->user()->role !== 'viewer')
<form method="POST" action="{{ route('purchases.destroy', $purchase) }}" onsubmit="return confirm('Delete this purchase? This cannot be undone.');">
@csrf
@method('DELETE')
<button type="submit" class="h-9 px-3 inline-flex items-center gap-1.5 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800 transition-colors whitespace-nowrap">
<span class="material-symbols-outlined text-[18px]">delete</span>
<span class="hidden sm:inline">Delete</span>
</button>
</form>
@endif
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 scroll-smooth">
<div class="max-w-[1000px] mx-auto flex flex-col gap-6">

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

<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
<div class="flex flex-wrap items-start justify-between gap-4 mb-6">
<div>
<h3 class="text-lg font-bold text-slate-900 dark:text-white">
{{ $purchase->invoice_number ?: 'Purchase #' . $purchase->id }}
</h3>
<p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $purchase->date->format('F j, Y H:i') }}</p>
</div>
<a href="{{ route('purchases.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-primary transition-colors">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
Back to Purchases
</a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 mb-6 pb-6 border-b border-slate-200 dark:border-slate-700">
<div>
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-0.5">Customer Code</p>
<p class="text-sm text-slate-900 dark:text-white">{{ $purchase->customer_code ?: '-' }}</p>
</div>
<div>
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-0.5">Customer Name</p>
<p class="text-sm text-slate-900 dark:text-white">{{ $purchase->customer_name ?: '-' }}</p>
</div>
<div>
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-0.5">Invoice Number</p>
<p class="text-sm text-slate-900 dark:text-white">{{ $purchase->invoice_number ?: '-' }}</p>
</div>
<div>
<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-0.5">Date</p>
<p class="text-sm text-slate-900 dark:text-white">{{ $purchase->date->format('Y-m-d H:i') }}</p>
</div>
</div>

<div class="overflow-x-auto -mx-6 px-6">
<table class="w-full text-left border-collapse min-w-[600px]">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Product Name</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Price</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Qty</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Amount</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">VAT 15%</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Subtotal</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-200 dark:divide-slate-700">
@foreach($purchase->items as $item)
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
<td class="px-4 py-3 text-sm text-slate-900 dark:text-white">{{ $item->product_name }}</td>
<td class="px-4 py-3 text-sm font-mono text-right text-slate-900 dark:text-white whitespace-nowrap">{{ $currencySymbol ?? '$' }} {{ number_format($item->price, 2) }}</td>
<td class="px-4 py-3 text-sm font-mono text-right text-slate-600 dark:text-slate-300">{{ number_format($item->quantity) }}</td>
<td class="px-4 py-3 text-sm font-mono text-right text-slate-900 dark:text-white whitespace-nowrap">{{ $currencySymbol ?? '$' }} {{ number_format($item->amount, 2) }}</td>
<td class="px-4 py-3 text-sm font-mono text-right text-slate-600 dark:text-slate-300 whitespace-nowrap">{{ $currencySymbol ?? '$' }} {{ number_format($item->vat_amount, 2) }}</td>
<td class="px-4 py-3 text-sm font-mono font-medium text-right text-slate-900 dark:text-white whitespace-nowrap">{{ $currencySymbol ?? '$' }} {{ number_format($item->subtotal, 2) }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>

<div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end">
<div class="text-right space-y-1.5 min-w-[240px]">
<div class="flex items-center justify-between gap-8 text-sm text-slate-600 dark:text-slate-400">
<span>Subtotal (excl. VAT)</span>
<span class="font-bold text-slate-900 dark:text-white font-mono tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($purchase->subtotal, 2) }}</span>
</div>
<div class="flex items-center justify-between gap-8 text-sm text-slate-600 dark:text-slate-400">
<span>VAT (15%)</span>
<span class="font-bold text-slate-900 dark:text-white font-mono tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($purchase->vat_amount, 2) }}</span>
</div>
<div class="flex items-center justify-between gap-8 text-lg font-bold text-slate-900 dark:text-white border-t border-slate-200 dark:border-slate-700 pt-1.5">
<span>Total</span>
<span class="font-mono tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($purchase->total_amount, 2) }}</span>
</div>
</div>
</div>
</div>

</div>
<div class="mt-8 text-center text-xs text-slate-400 pb-4">© {{ date('Y') }} Nexus ERP Inc. All rights reserved.</div>
</div>
</main>
</body>
</html>
