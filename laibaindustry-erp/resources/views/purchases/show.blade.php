<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Purchase #' . $purchase->id . ' - ERP'])
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
<a href="{{ route('purchases.index') }}" class="p-2 text-[#586064] hover:bg-[#F1F4F6] border border-transparent hover:border-[#ABB3B7] hidden sm:flex items-center" aria-label="Back to purchases">
<span class="material-symbols-outlined text-[20px]">arrow_back</span>
</a>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase truncate max-w-[50vw]">
{{ $purchase->invoice_number ?: 'Purchase #' . $purchase->id }}
</h2>
</div>
<div class="flex items-center gap-2">
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('purchases.edit', $purchase) }}" class="st-btn-secondary h-9 px-3 inline-flex items-center gap-1.5 whitespace-nowrap">
<span class="material-symbols-outlined text-[18px]">edit</span>
<span class="hidden sm:inline">Edit</span>
</a>
<form method="POST" action="{{ route('purchases.destroy', $purchase) }}" data-confirm-delete="{{ e('Delete this purchase? This cannot be undone.') }}">
@csrf
@method('DELETE')
<button type="submit" class="h-9 px-3 inline-flex items-center gap-1.5 whitespace-nowrap text-[11px] font-bold uppercase tracking-wider border border-[#9F403D] text-[#9F403D] bg-transparent hover:bg-[#F1F4F6]">
<span class="material-symbols-outlined text-[18px]">delete</span>
<span class="hidden sm:inline">Delete</span>
</button>
</form>
@endif
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-[1000px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">RECORD_VIEW_03</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">{{ $purchase->invoice_number ?: 'Purchase #' . $purchase->id }}</h1>
<p class="text-sm text-[#586064] mt-1">{{ format_display_datetime($purchase->date) }}</p>
</div>
<a href="{{ route('purchases.index') }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2 shrink-0">
<span class="material-symbols-outlined text-[18px]">list</span>
Ledger
</a>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

@if (session('success'))
<div class="border border-[#ABB3B7] bg-white px-4 py-3 text-sm text-[#2B3437]">
{{ session('success') }}
</div>
@endif
@if (session('error'))
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D]">
{{ session('error') }}
</div>
@endif

<div class="st-paper border border-[#ABB3B7] p-6 md:p-8 bg-white">
<div class="grid grid-cols-1 sm:grid-cols-2 gap-x-10 gap-y-4 mb-8 pb-8 border-b border-[#ABB3B7]">
<div>
<p class="st-label mb-1">Customer Code</p>
<p class="text-sm font-semibold text-[#2B3437]">{{ $purchase->customer_code ?: '—' }}</p>
</div>
<div>
<p class="st-label mb-1">Customer Name</p>
<p class="text-sm font-semibold text-[#2B3437]">{{ $purchase->customer_name ?: '—' }}</p>
</div>
<div>
<p class="st-label mb-1">Invoice Number</p>
<p class="text-sm font-semibold text-[#2B3437]">{{ $purchase->invoice_number ?: '—' }}</p>
</div>
<div>
<p class="st-label mb-1">Date</p>
<p class="text-sm font-mono text-[#2B3437]">{{ format_display_datetime($purchase->date) }}</p>
</div>
</div>

<div class="overflow-x-auto -mx-2 sm:mx-0 border border-[#ABB3B7]">
<table class="w-full text-left border-collapse min-w-[600px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3">Product Name</th>
<th class="st-th px-4 py-3 text-right">Price</th>
<th class="st-th px-4 py-3 text-right">Qty</th>
<th class="st-th px-4 py-3 text-right">Amount</th>
<th class="st-th px-4 py-3 text-right">VAT 15%</th>
<th class="st-th px-4 py-3 text-right">Subtotal</th>
</tr>
</thead>
<tbody>
@foreach($purchase->items as $item)
<tr class="st-tr">
<td class="st-td px-4 py-3 text-sm text-[#2B3437]">{{ $item->product_name }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#2B3437]">{{ $currencySymbol ?? '$' }} {{ number_format($item->price, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right text-[#586064]">{{ number_format($item->quantity) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#2B3437]">{{ $currencySymbol ?? '$' }} {{ number_format($item->amount, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#586064]">{{ $currencySymbol ?? '$' }} {{ number_format($item->vat_amount, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums text-[#5E5E5E]">{{ $currencySymbol ?? '$' }} {{ number_format($item->subtotal, 2) }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>

<div class="mt-6 pt-6 border-t border-[#ABB3B7] flex justify-end">
<div class="text-right space-y-2 min-w-[260px]">
<div class="flex items-center justify-between gap-8 text-sm text-[#586064]">
<span>Subtotal (excl. VAT)</span>
<span class="font-bold text-[#2B3437] font-mono tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($purchase->subtotal, 2) }}</span>
</div>
<div class="flex items-center justify-between gap-8 text-sm text-[#586064]">
<span>VAT (15%)</span>
<span class="font-bold text-[#2B3437] font-mono tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($purchase->vat_amount, 2) }}</span>
</div>
<div class="flex items-center justify-between gap-8 text-base font-black text-[#2B3437] border-t border-[#ABB3B7] pt-2">
<span>Total</span>
<span class="font-mono tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($purchase->total_amount, 2) }}</span>
</div>
</div>
</div>

@php
    $offsetTotal = (float) $purchase->receivableOffsets->sum('amount');
@endphp
@if ($purchase->receivableOffsets->isNotEmpty())
<div class="mt-6 pt-6 border-t border-[#ABB3B7]">
<p class="st-label mb-3">Receivable offsets (auto-applied)</p>
<div class="overflow-x-auto border border-[#ABB3B7]">
<table class="w-full text-left border-collapse min-w-[560px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3">Receivable Invoice</th>
<th class="st-th px-4 py-3">Offset Date</th>
<th class="st-th px-4 py-3 text-right">Offset Amount</th>
</tr>
</thead>
<tbody>
@foreach ($purchase->receivableOffsets as $offset)
<tr class="st-tr">
<td class="st-td px-4 py-3 text-sm text-[#2B3437]">{{ $offset->receivable?->invoice_number ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-[#586064]">{{ $offset->offset_date ? format_display_date($offset->offset_date) : '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right tabular-nums text-[#5E5E5E]">{{ $currencySymbol ?? '$' }} {{ number_format((float) $offset->amount, 2) }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>
<p class="text-xs text-[#586064] mt-3">Total auto-offset from this purchase: <span class="font-mono font-bold text-[#2B3437]">{{ $currencySymbol ?? '$' }} {{ number_format($offsetTotal, 2) }}</span></p>
</div>
@endif
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-4 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
