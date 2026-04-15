<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Purchase group - ERP'])
@include('purchases.partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'purchases'])

<main class="purchases-stitch flex-1 flex flex-col h-full overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<a href="{{ route('purchases.index') }}" class="st-btn-secondary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
<span class="hidden sm:inline">Purchases</span>
</a>
</div>
</header>
<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col gap-1">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">PURCHASE_BY_CUSTOMER</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Invoices</h1>
<p class="text-sm text-[#586064] mt-2">{{ $displayName }}</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-0 border border-[#ABB3B7] bg-white md:grid md:divide-x md:divide-[#ABB3B7]">
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Subtotal</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_subtotal ?? 0, 2) }}</p>
</div>
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">VAT (15%)</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_vat ?? 0, 2) }}</p>
</div>
<div class="p-6 border-2 border-[#5E5E5E] max-md:m-0 -m-px">
<p class="st-label mb-2 text-[#5E5E5E]">Group total</p>
<p class="text-2xl font-black font-mono text-[#5E5E5E] tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_purchases ?? 0, 2) }}</p>
</div>
</div>

<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[320px]">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">Purchase invoices</h3>
<p class="text-[11px] text-[#586064] mt-1">All invoices for this customer group</p>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[960px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Invoice</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Code</th>
<th class="st-th px-4 py-3 min-w-[220px]">Products</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Subtotal</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">VAT</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Total</th>
<th class="st-th px-4 py-3 text-right w-28"></th>
</tr>
</thead>
<tbody>
@foreach($purchases as $purchase)
@php
$rowSymbol = $purchase->currency && $purchase->currency->symbol ? $purchase->currency->symbol : ($currencySymbol ?? '$');
$lineCount = $purchase->items->count();
$firstItem = $purchase->items->first();
$firstName = $firstItem && trim((string) $firstItem->product_name) !== '' ? $firstItem->product_name : '—';
$linesSummary = $lineCount <= 1 ? ($lineCount === 0 ? 'No line items' : $firstName) : ($firstName.', +'.($lineCount - 1).' more');
@endphp
<tr class="st-tr">
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ format_display_datetime($purchase->date) }}</td>
<td class="st-td px-4 py-3 text-sm font-bold text-[#2B3437]">
<a href="{{ route('purchases.show', $purchase) }}" class="text-[#5E5E5E] hover:underline">{{ $purchase->invoice_number ?: '#' . $purchase->id }}</a>
</td>
<td class="st-td px-4 py-3 text-sm font-mono text-[#586064]">{{ $purchase->customer_code ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm text-[#586064] max-w-[280px]" title="{{ e($linesSummary) }}">
<span class="line-clamp-2">{{ $linesSummary }}</span>
@if($lineCount > 1)
<span class="block text-[10px] uppercase tracking-wide text-[#586064] mt-0.5">{{ $lineCount }} products</span>
@endif
</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#2B3437]">{{ $rowSymbol }} {{ number_format($purchase->subtotal ?? 0, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#586064]">{{ $rowSymbol }} {{ number_format($purchase->vat_amount ?? 0, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums text-[#5E5E5E]">{{ $rowSymbol }} {{ number_format($purchase->total_amount ?? 0, 2) }}</td>
<td class="st-td px-4 py-3 text-right">
<a href="{{ route('purchases.show', $purchase) }}" class="p-2 border border-transparent hover:border-[#ABB3B7] text-[#586064] hover:text-[#2B3437] hover:bg-[#F1F4F6]" title="View">
<span class="material-symbols-outlined text-[18px]">visibility</span>
</a>
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-6 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
