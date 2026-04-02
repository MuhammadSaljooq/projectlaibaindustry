<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => ($order->invoice_number ?: 'Invoice #'.$order->id).' - International purchase - ERP'])
@include('partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'international_purchases'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<a href="{{ route('international-purchases.index') }}" class="st-btn-secondary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
<span class="hidden sm:inline">International purchases</span>
</a>
</div>
<div class="flex items-center gap-2">
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('international-purchases.edit', $order) }}" class="st-btn-primary h-9 px-4 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">edit</span>
<span class="hidden sm:inline">Edit</span>
</a>
@endif
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-[900px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col gap-1 min-w-0">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">INT_PURCH_VIEW</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">{{ $order->invoice_number ?: '#'.$order->id }}</h1>
<p class="text-sm text-[#586064] mt-1">{{ format_display_date($order->date) }}</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

@if (session('success'))
<div class="border border-[#ABB3B7] bg-white px-4 py-3 text-sm text-[#2B3437]">{{ session('success') }}</div>
@endif

<div class="st-paper border border-[#ABB3B7] p-6 md:p-8 bg-white">
<p class="st-label mb-6">Vendor</p>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
<div>
<p class="st-label mb-1">Name</p>
<p class="text-sm font-bold text-[#2B3437]">{{ $order->supplier?->name ?? '—' }}</p>
</div>
@if($order->invoice_number)
<div>
<p class="st-label mb-1">Invoice / reference</p>
<p class="text-sm font-mono font-semibold text-[#2B3437]">{{ $order->invoice_number }}</p>
</div>
@endif
</div>
</div>

<div class="st-paper border border-[#ABB3B7] bg-white overflow-hidden flex flex-col">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">Line items</h3>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse min-w-[560px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3">Product</th>
<th class="st-th px-4 py-3 text-right">Qty</th>
<th class="st-th px-4 py-3 text-right">Unit price</th>
<th class="st-th px-4 py-3 text-right">Amount</th>
</tr>
</thead>
<tbody>
@foreach($order->lines as $line)
<tr class="st-tr">
<td class="st-td px-4 py-3 text-sm font-semibold text-[#2B3437]">{{ $line->product_name }}</td>
<td class="st-td px-4 py-3 text-sm text-right tabular-nums text-[#586064]">{{ number_format($line->quantity) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums text-[#5E5E5E]">{{ $currencySymbol }} {{ number_format($line->unit_price, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right tabular-nums text-[#2B3437]">{{ $currencySymbol }} {{ number_format($line->total_amount, 2) }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>
<div class="px-5 py-4 border-t border-[#ABB3B7] bg-[#F8F9FA] flex justify-end">
<p class="text-base font-black text-[#2B3437]">Total <span class="font-mono tabular-nums text-[#5E5E5E] ml-4">{{ $currencySymbol }} {{ number_format($order->total_amount, 2) }}</span></p>
</div>
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-4 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
