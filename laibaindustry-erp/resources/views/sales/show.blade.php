<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Sale #' . ($sale->invoice_number ?: $sale->id) . ' - ERP'])
@include('partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'sales'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<a href="{{ route('sales.index') }}" class="st-btn-secondary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
<span class="hidden sm:inline">Sales</span>
</a>
</div>
<div class="flex items-center gap-2">
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('sales.edit', $sale) }}" class="st-btn-primary h-9 px-4 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">edit</span>
<span class="hidden sm:inline">Edit</span>
</a>
@endif
</div>
</header>

@php $symbol = $sale->currency && $sale->currency->symbol ? $sale->currency->symbol : ($currencySymbol ?? '$'); @endphp

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-[900px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
<div class="flex flex-col gap-1 min-w-0">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">SALE_VIEW_14</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">{{ $sale->invoice_number ?: '#' . $sale->id }}</h1>
<p class="text-sm text-[#586064] mt-1">{{ $sale->date->format('l, F j, Y \a\t g:i A') }}</p>
</div>
<span class="text-[10px] font-bold uppercase tracking-wider border border-[#ABB3B7] px-3 py-1.5 bg-[#F8F9FA] text-[#586064] shrink-0 self-start">{{ ucfirst($sale->status) }}</span>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

@if (session('success'))
<div class="border border-[#ABB3B7] bg-white px-4 py-3 text-sm text-[#2B3437]">{{ session('success') }}</div>
@endif
@if (session('warning'))
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D]">{{ session('warning') }}</div>
@endif

<div class="st-paper border border-[#ABB3B7] p-6 md:p-8 bg-white">
<p class="st-label mb-6">Customer</p>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
<div>
<p class="st-label mb-1">Name</p>
<p class="text-sm font-bold text-[#2B3437]">{{ $sale->customer_name ?: $sale->customer_code ?: 'Walk-in' }}</p>
</div>
@if($sale->customer_code)
<div>
<p class="st-label mb-1">Code</p>
<p class="text-sm font-mono font-semibold text-[#2B3437]">{{ $sale->customer_code }}</p>
</div>
@endif
@if($sale->invoice_number)
<div>
<p class="st-label mb-1">Invoice</p>
<p class="text-sm font-mono font-semibold text-[#2B3437]">{{ $sale->invoice_number }}</p>
</div>
@endif
</div>
</div>

<div class="st-paper border border-[#ABB3B7] bg-white overflow-hidden flex flex-col">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">Line items</h3>
</div>
<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[520px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 w-10">#</th>
<th class="st-th px-4 py-3">Product</th>
<th class="st-th px-4 py-3 text-right">Qty</th>
<th class="st-th px-4 py-3 text-right">Unit price</th>
<th class="st-th px-4 py-3 text-right">Tax</th>
<th class="st-th px-4 py-3 text-right">Line total</th>
</tr>
</thead>
<tbody>
@foreach($sale->items as $index => $item)
@php $lineTotal = ($item->selling_price * $item->quantity) + ($item->tax_applied ?? 0); @endphp
<tr class="st-tr">
<td class="st-td px-4 py-3 text-sm font-mono text-[#586064]">{{ $index + 1 }}</td>
<td class="st-td px-4 py-3">
<p class="text-sm font-bold text-[#2B3437]">{{ $item->product ? $item->product->name : 'Product #' . $item->product_id }}</p>
@if($item->product?->sku)
<p class="text-xs text-[#586064] mt-0.5"><span class="font-bold uppercase tracking-wide">Article no.</span> {{ $item->product->sku }}</p>
@endif
</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right text-[#586064]">{{ number_format($item->quantity) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#2B3437]">{{ $symbol }} {{ number_format($item->selling_price, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#586064]">{{ $symbol }} {{ number_format($item->tax_applied ?? 0, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums text-[#5E5E5E]">{{ $symbol }} {{ number_format($lineTotal, 2) }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>

<div class="px-5 py-4 border-t border-[#ABB3B7] bg-[#F8F9FA]">
<div class="flex justify-end">
<div class="min-w-[240px] space-y-2">
<div class="flex justify-between text-sm text-[#586064]"><span>Subtotal</span><span class="font-bold font-mono tabular-nums text-[#2B3437]">{{ $symbol }} {{ number_format($sale->subtotal, 2) }}</span></div>
<div class="flex justify-between text-sm text-[#586064]"><span>Tax ({{ number_format($sale->tax_rate ?? 0, 0) }}%)</span><span class="font-bold font-mono tabular-nums text-[#2B3437]">{{ $symbol }} {{ number_format($sale->tax_amount ?? 0, 2) }}</span></div>
@if((float)($sale->discount_amount ?? 0) > 0)
<div class="flex justify-between text-sm text-[#586064]"><span>Discount</span><span class="font-bold font-mono tabular-nums text-[#9F403D]">−{{ $symbol }} {{ number_format($sale->discount_amount, 2) }}</span></div>
@endif
<div class="flex justify-between text-base font-black text-[#2B3437] border-t border-[#ABB3B7] pt-2"><span>Total</span><span class="font-mono tabular-nums">{{ $symbol }} {{ number_format($sale->total_amount, 2) }}</span></div>
</div>
</div>
</div>
</div>

<div class="flex flex-wrap items-center gap-3">
<a href="{{ route('sales.index') }}" class="st-btn-secondary h-10 px-5 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">list</span>
All sales
</a>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('sales.edit', $sale) }}" class="st-btn-primary h-10 px-5 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">edit</span>
Edit sale
</a>
<form method="POST" action="{{ route('sales.destroy', $sale) }}" class="inline-flex" data-confirm-delete="{{ e('Delete this sale? Stock will be restored and the related receivable removed.') }}">
@csrf
@method('DELETE')
<button type="submit" class="h-10 px-5 inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider border border-[#9F403D] text-[#9F403D] bg-transparent hover:bg-[#F1F4F6]">
<span class="material-symbols-outlined text-[18px]">delete</span>
Delete
</button>
</form>
@endif
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-4 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
