<!DOCTYPE html>
<html lang="en" class="dark">
<head>
@include('partials.frontend-head', ['title' => 'Sales - Laiba Safety'])
<style>
body { background-color: #131313; color: #e2e2e2; font-family: 'Inter', sans-serif; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #FFFFFF; color: #131313; }
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'sales'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#131313;">

<header class="h-16 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="background:#1B1B1B;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 hover:text-white rounded-md" style="color:#8e9192;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
</div>
<div class="flex items-center gap-3">
<a href="{{ route('sales.export') }}" class="h-9 px-4 text-[11px] font-bold uppercase flex items-center gap-2 transition-all" style="color:#C4C7C8;border:1px solid rgba(68,71,72,0.4);border-radius:0.375rem;letter-spacing:0.05em;" onmouseover="this.style.color='#FFFFFF';this.style.borderColor='#FFFFFF'" onmouseout="this.style.color='#C4C7C8';this.style.borderColor='rgba(68,71,72,0.4)'">
<span class="material-symbols-outlined" style="font-size:14px;">download</span>
CSV
</a>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('sales.create') }}" class="h-9 px-5 text-[11px] font-bold uppercase flex items-center gap-2 active:scale-[0.98] transition-all" style="background:#FFFFFF;color:#2F3131;border-radius:0.375rem;letter-spacing:0.05em;">
<span class="material-symbols-outlined" style="font-size:14px;">add</span>
NEW SALE
</a>
@endif
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col" style="gap:2rem;">

{{-- Heading --}}
<div>
<span class="text-[11px] font-medium uppercase block mb-2" style="letter-spacing:0.2em;color:#8e9192;">Revenue Tracking</span>
<h2 class="text-white font-black" style="font-size:2.5rem;letter-spacing:-0.02em;line-height:1.1;">Sales</h2>
</div>

{{-- Flash --}}
@if (session('success'))
<div style="background:rgba(255,255,255,0.05);border-radius:0.5rem;padding:0.75rem 1.25rem;" class="text-sm font-medium text-white">
<span class="material-symbols-outlined align-middle" style="font-size:16px;margin-right:0.5rem;">check_circle</span>{{ session('success') }}
</div>
@endif
@if (session('error'))
<div style="background:rgba(255,180,171,0.08);border-radius:0.5rem;padding:0.75rem 1.25rem;" class="text-sm font-medium">
<span class="material-symbols-outlined align-middle" style="font-size:16px;margin-right:0.5rem;color:#FFB4AB;">error</span><span style="color:#FFB4AB;">{{ session('error') }}</span>
</div>
@endif

{{-- Stat Cards --}}
<div class="grid grid-cols-12" style="gap:1.5rem;">
<div class="col-span-12 md:col-span-4 relative overflow-hidden group" style="background:#1B1B1B;border-radius:0.5rem;padding:2rem;">
<p class="text-[10px] font-semibold uppercase" style="letter-spacing:0.15em;margin-bottom:1.5rem;color:#C4C7C8;">Subtotal</p>
<h3 class="text-white font-black tabular-nums" style="font-size:clamp(1.75rem,4vw,2.5rem);letter-spacing:-0.03em;">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_subtotal ?? 0, 2) }}</h3>
</div>
<div class="col-span-12 md:col-span-4 relative overflow-hidden group" style="background:#1B1B1B;border-radius:0.5rem;padding:2rem;">
<p class="text-[10px] font-semibold uppercase" style="letter-spacing:0.15em;margin-bottom:1.5rem;color:#C4C7C8;">VAT (15%)</p>
<h3 class="text-white font-black tabular-nums" style="font-size:clamp(1.75rem,4vw,2.5rem);letter-spacing:-0.03em;">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_vat ?? 0, 2) }}</h3>
</div>
<div class="col-span-12 md:col-span-4 relative overflow-hidden group" style="background:linear-gradient(135deg,#FFFFFF,#C6C6C7);border-radius:0.5rem;padding:2rem;">
<p class="text-[10px] font-semibold uppercase" style="letter-spacing:0.15em;margin-bottom:1.5rem;color:rgba(42,49,49,0.5);">Total Sales</p>
<h3 class="font-black tabular-nums" style="font-size:clamp(1.75rem,4vw,2.5rem);letter-spacing:-0.03em;color:#1a1c1c;">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_sales ?? 0, 2) }}</h3>
</div>
</div>

{{-- Search & Filter --}}
<form method="GET" action="{{ route('sales.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3" style="background:#1B1B1B;border-radius:0.5rem;padding:1.25rem 1.5rem;">
<div class="relative flex-1 min-w-0">
<span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined pointer-events-none" style="font-size:18px;color:#8e9192;">search</span>
<input class="w-full h-10 pl-10 pr-4 text-sm font-medium text-white placeholder-[#C4C7C8]/50 outline-none transition-all" style="background:transparent;border:1px solid rgba(68,71,72,0.4);border-radius:0.375rem;" name="search" type="text" placeholder="Search invoice, customer, product..." value="{{ request('search') }}" onfocus="this.style.borderColor='#FFFFFF';this.style.boxShadow='0 0 0 2px rgba(255,255,255,0.1)'" onblur="this.style.borderColor='rgba(68,71,72,0.4)';this.style.boxShadow='none'"/>
</div>
<div class="flex items-center gap-3 shrink-0">
<input class="h-10 px-3 text-sm font-medium text-white outline-none transition-all" style="background:transparent;border:1px solid rgba(68,71,72,0.4);border-radius:0.375rem;color-scheme:dark;" name="from" type="date" value="{{ request('from') }}" onfocus="this.style.borderColor='#FFFFFF'" onblur="this.style.borderColor='rgba(68,71,72,0.4)'"/>
<input class="h-10 px-3 text-sm font-medium text-white outline-none transition-all" style="background:transparent;border:1px solid rgba(68,71,72,0.4);border-radius:0.375rem;color-scheme:dark;" name="to" type="date" value="{{ request('to') }}" onfocus="this.style.borderColor='#FFFFFF'" onblur="this.style.borderColor='rgba(68,71,72,0.4)'"/>
</div>
<button type="submit" class="h-10 px-5 text-[11px] font-bold uppercase flex items-center justify-center gap-2 active:scale-[0.98] transition-all shrink-0" style="background:#FFFFFF;color:#2F3131;border-radius:0.375rem;letter-spacing:0.05em;">
<span class="material-symbols-outlined" style="font-size:14px;">filter_list</span>FILTER
</button>
@if(request('search') || request('from') || request('to'))
<a href="{{ route('sales.index') }}" class="h-10 px-4 text-[11px] font-bold uppercase flex items-center justify-center transition-all shrink-0" style="color:#C4C7C8;border:1px solid rgba(68,71,72,0.4);border-radius:0.375rem;" onmouseover="this.style.color='#FFFFFF'" onmouseout="this.style.color='#C4C7C8'">CLEAR</a>
@endif
</form>

{{-- Table --}}
<div style="background:#1B1B1B;border-radius:0.5rem;overflow:hidden;">
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse" style="min-width:900px;">
<thead>
<tr style="background:#0E0E0E;">
<th class="px-4 py-4 text-[10px] font-bold uppercase whitespace-nowrap" style="letter-spacing:0.15em;color:#8e9192;">Date</th>
<th class="px-4 py-4 text-[10px] font-bold uppercase whitespace-nowrap" style="letter-spacing:0.15em;color:#8e9192;">Customer</th>
<th class="px-4 py-4 text-[10px] font-bold uppercase whitespace-nowrap" style="letter-spacing:0.15em;color:#8e9192;">Invoice</th>
<th class="px-4 py-4 text-[10px] font-bold uppercase whitespace-nowrap" style="letter-spacing:0.15em;color:#8e9192;">Product</th>
<th class="px-4 py-4 text-[10px] font-bold uppercase whitespace-nowrap text-right" style="letter-spacing:0.15em;color:#8e9192;">Price</th>
<th class="px-4 py-4 text-[10px] font-bold uppercase whitespace-nowrap text-right" style="letter-spacing:0.15em;color:#8e9192;">Qty</th>
<th class="px-4 py-4 text-[10px] font-bold uppercase whitespace-nowrap text-right" style="letter-spacing:0.15em;color:#8e9192;">Amount</th>
<th class="px-4 py-4 text-[10px] font-bold uppercase whitespace-nowrap text-right" style="letter-spacing:0.15em;color:#8e9192;">VAT</th>
<th class="px-4 py-4 text-[10px] font-bold uppercase whitespace-nowrap text-right" style="letter-spacing:0.15em;color:#8e9192;">Total</th>
<th class="px-4 py-4 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.15em;color:#8e9192;width:100px;"></th>
</tr>
</thead>
<tbody>
@forelse($items as $item)
@php
$sale = $item->sale;
$amount = $item->selling_price * $item->quantity;
$vat = $item->tax_applied ?? 0;
$subtotal = $amount + $vat;
@endphp
<tr class="group transition-colors {{ $sale ? 'cursor-pointer' : '' }}" style="border-top:1px solid rgba(68,71,72,0.15);"
    onmouseover="this.style.background='#2A2A2A'" onmouseout="this.style.background='transparent'"
    @if($sale) onclick="window.location.href='{{ route('sales.show', $sale) }}'" @endif>
<td class="px-4 py-4 text-sm whitespace-nowrap" style="color:#C4C7C8;">{{ $sale ? $sale->date->format('Y-m-d') : '-' }}</td>
<td class="px-4 py-4 text-sm font-medium text-white truncate" style="max-width:160px;">{{ $sale?->customer_name ?: ($sale?->customer_code ?: '-') }}</td>
<td class="px-4 py-4 text-sm font-bold text-white">
@if($sale)
<a href="{{ route('sales.show', $sale) }}" class="hover:underline" style="text-decoration-color:#444748;" onclick="event.stopPropagation();">{{ $sale->invoice_number ?: '#' . $sale->id }}</a>
@else - @endif
</td>
<td class="px-4 py-4 text-sm" style="color:#C4C7C8;">{{ $item->product?->name ?: 'Product #' . $item->product_id }}</td>
<td class="px-4 py-4 text-sm tabular-nums text-right text-white whitespace-nowrap">{{ $currencySymbol ?? '$' }} {{ number_format($item->selling_price, 2) }}</td>
<td class="px-4 py-4 text-sm tabular-nums text-right" style="color:#C4C7C8;">{{ number_format($item->quantity) }}</td>
<td class="px-4 py-4 text-sm tabular-nums text-right text-white whitespace-nowrap">{{ $currencySymbol ?? '$' }} {{ number_format($amount, 2) }}</td>
<td class="px-4 py-4 text-sm tabular-nums text-right whitespace-nowrap" style="color:#C4C7C8;">{{ $currencySymbol ?? '$' }} {{ number_format($vat, 2) }}</td>
<td class="px-4 py-4 text-sm font-bold tabular-nums text-right text-white whitespace-nowrap">{{ $currencySymbol ?? '$' }} {{ number_format($subtotal, 2) }}</td>
<td class="px-4 py-4 text-right" onclick="event.stopPropagation();">
@if($sale)
<div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
<a href="{{ route('sales.show', $sale) }}" class="p-1.5 transition-colors" style="color:#8e9192;border-radius:0.25rem;" onmouseover="this.style.color='#FFFFFF';this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.color='#8e9192';this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
</a>
@if(auth()->user()->role !== 'viewer')
<form method="POST" action="{{ route('sales.destroy', $sale) }}" class="inline-flex" onsubmit="return confirm('Delete this sale? Stock will be restored.');">
@csrf @method('DELETE')
<button type="submit" class="p-1.5 transition-colors" style="color:#8e9192;border-radius:0.25rem;" onmouseover="this.style.color='#FFB4AB';this.style.background='rgba(255,180,171,0.08)'" onmouseout="this.style.color='#8e9192';this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:18px;">delete</span>
</button>
</form>
@endif
</div>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="10" class="px-6 py-16 text-center">
<span class="material-symbols-outlined block" style="font-size:3rem;color:#353535;margin-bottom:1rem;">receipt</span>
<p class="text-sm font-medium" style="color:#8e9192;">No sales recorded yet.</p>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('sales.create') }}" class="inline-block text-[11px] font-bold uppercase text-white" style="margin-top:1rem;border-bottom:1px solid #FFFFFF;padding-bottom:0.25rem;">Create First Sale</a>
@endif
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

{{-- Pagination --}}
<div class="flex flex-col sm:flex-row items-center justify-between gap-4" style="padding:1rem 1.5rem;background:#0E0E0E;">
<p class="text-xs font-medium" style="color:#8e9192;">
@if($items->total() > 0)
Showing <span class="text-white font-bold">{{ $items->firstItem() }}</span> to <span class="text-white font-bold">{{ $items->lastItem() }}</span> of <span class="text-white font-bold">{{ $items->total() }}</span> line items
@else No results @endif
</p>
@if($items->hasPages())
<nav class="flex items-center gap-1" aria-label="Pagination">
@if (!$items->onFirstPage())
<a class="p-1.5 transition-colors" style="color:#C4C7C8;border-radius:0.25rem;" href="{{ $items->previousPageUrl() }}" onmouseover="this.style.background='#2A2A2A'" onmouseout="this.style.background='transparent'"><span class="material-symbols-outlined" style="font-size:18px;">chevron_left</span></a>
@endif
@foreach ($items->getUrlRange(max(1, $items->currentPage() - 2), min($items->lastPage(), $items->currentPage() + 2)) ?: [1 => $items->url(1)] as $page => $url)
@if ($page == $items->currentPage())
<span class="px-3 py-1.5 text-xs font-bold" style="background:#FFFFFF;color:#2F3131;border-radius:0.375rem;">{{ $page }}</span>
@else
<a class="px-3 py-1.5 text-xs font-medium transition-colors" style="color:#C4C7C8;border-radius:0.375rem;" href="{{ $url }}" onmouseover="this.style.background='#2A2A2A';this.style.color='#FFFFFF'" onmouseout="this.style.background='transparent';this.style.color='#C4C7C8'">{{ $page }}</a>
@endif
@endforeach
@if ($items->hasMorePages())
<a class="p-1.5 transition-colors" style="color:#C4C7C8;border-radius:0.25rem;" href="{{ $items->nextPageUrl() }}" onmouseover="this.style.background='#2A2A2A'" onmouseout="this.style.background='transparent'"><span class="material-symbols-outlined" style="font-size:18px;">chevron_right</span></a>
@endif
</nav>
@endif
</div>
</div>

<div class="text-center text-[10px] uppercase font-medium pb-4" style="margin-top:2rem;letter-spacing:0.15em;color:#8e9192;">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</div>
</div>
</div>
</main>
</body>
</html>
