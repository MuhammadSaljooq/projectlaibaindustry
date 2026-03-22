<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Sales - Laiba Safety'])
<style>
body { background-color: #FFFFFF; color: #2B3437; font-family: 'Inter', sans-serif; color-scheme: light; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #2B3437; color: #FFFFFF; }
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'sales'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#FFFFFF;">

<header class="h-14 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 rounded-none" style="color:#5E5E5E;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
</div>
<div class="flex items-center gap-3">
<a href="{{ route('sales.export') }}" class="h-9 px-4 text-[11px] font-bold uppercase flex items-center gap-2 transition-all" style="color:#2B3437;border:1px solid #5E5E5E;border-radius:0;letter-spacing:0.05em;background:transparent;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:14px;">download</span>
CSV
</a>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('sales.create') }}" class="h-9 px-5 text-[11px] font-bold uppercase flex items-center gap-2 active:scale-[0.98] transition-all" style="background:#5E5E5E;color:#F8F8F8;border-radius:0;letter-spacing:0.05em;">
<span class="material-symbols-outlined" style="font-size:14px;">add</span>
NEW SALE
</a>
@endif
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col" style="gap:3rem;">

<div>
<div class="flex items-end justify-between" style="padding-bottom:0.75rem;border-bottom:2px solid #5E5E5E;">
<h2 class="font-bold" style="font-size:1.5rem;letter-spacing:-0.02em;color:#2B3437;">Sales</h2>
<span class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Section SLS-01</span>
</div>
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-top:0.75rem;">Revenue Tracking</p>
</div>

@if (session('success'))
<div style="border:1px solid #D3D8DE;padding:0.75rem 1.25rem;" class="text-sm font-bold">
<span class="material-symbols-outlined align-middle" style="font-size:16px;margin-right:0.5rem;color:#5E5E5E;">check_circle</span>{{ session('success') }}
</div>
@endif
@if (session('error'))
<div style="border:1px solid #9F403D;padding:0.75rem 1.25rem;" class="text-sm font-bold">
<span class="material-symbols-outlined align-middle" style="font-size:16px;margin-right:0.5rem;color:#9F403D;">error</span><span style="color:#9F403D;">{{ session('error') }}</span>
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3" style="gap:1px;background:#D3D8DE;border:1px solid #D3D8DE;">
<div style="background:#FFFFFF;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">Subtotal</p>
<p class="font-bold tabular-nums" style="font-size:1.75rem;letter-spacing:-0.02em;color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_subtotal ?? 0, 2) }}</p>
</div>
<div style="background:#FFFFFF;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">VAT (15%)</p>
<p class="font-bold tabular-nums" style="font-size:1.75rem;letter-spacing:-0.02em;color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_vat ?? 0, 2) }}</p>
</div>
<div style="background:#F8F9FA;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">Total Sales</p>
<p class="font-bold tabular-nums" style="font-size:1.75rem;letter-spacing:-0.02em;color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_sales ?? 0, 2) }}</p>
</div>
</div>

<form method="GET" action="{{ route('sales.index') }}" class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3" style="border:1px solid #D3D8DE;padding:1rem 1.5rem;">
<div class="relative flex-1 min-w-0">
<span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined pointer-events-none" style="font-size:18px;color:#5E5E5E;">search</span>
<input class="w-full h-10 pl-10 pr-4 text-sm font-bold outline-none transition-all" style="background:transparent;border:1px solid #D3D8DE;border-radius:0;color:#2B3437;" name="search" type="text" placeholder="Search invoice, customer, product..." value="{{ request('search') }}" onfocus="this.style.borderColor='#5E5E5E';this.style.borderWidth='2px'" onblur="this.style.borderColor='#D3D8DE';this.style.borderWidth='1px'"/>
</div>
<div class="flex items-center gap-3 shrink-0 flex-wrap">
<input class="h-10 px-3 text-sm font-bold outline-none transition-all" style="background:#FFFFFF;border:1px solid #D3D8DE;border-radius:0;color:#2B3437;color-scheme:light;" name="from" type="date" value="{{ request('from') }}" onfocus="this.style.borderColor='#5E5E5E';this.style.borderWidth='2px'" onblur="this.style.borderColor='#D3D8DE';this.style.borderWidth='1px'"/>
<input class="h-10 px-3 text-sm font-bold outline-none transition-all" style="background:#FFFFFF;border:1px solid #D3D8DE;border-radius:0;color:#2B3437;color-scheme:light;" name="to" type="date" value="{{ request('to') }}" onfocus="this.style.borderColor='#5E5E5E';this.style.borderWidth='2px'" onblur="this.style.borderColor='#D3D8DE';this.style.borderWidth='1px'"/>
</div>
<button type="submit" class="h-10 px-5 text-[11px] font-bold uppercase flex items-center justify-center gap-2 active:scale-[0.98] transition-all shrink-0" style="background:#5E5E5E;color:#F8F8F8;border-radius:0;letter-spacing:0.05em;">
<span class="material-symbols-outlined" style="font-size:14px;">filter_list</span>FILTER
</button>
@if(request('search') || request('from') || request('to'))
<a href="{{ route('sales.index') }}" class="h-10 px-4 text-[11px] font-bold uppercase flex items-center justify-center transition-all shrink-0" style="color:#5E5E5E;border:1px solid #5E5E5E;border-radius:0;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">CLEAR</a>
@endif
</form>

<div style="border:1px solid #D3D8DE;">
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse" style="min-width:900px;">
<thead>
<tr style="background:#F8F9FA;border-bottom:1px solid #D3D8DE;">
<th class="px-4 py-3 text-[10px] font-bold uppercase whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;">Date</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;">Customer</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;">Invoice</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;">Product</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase whitespace-nowrap text-right" style="letter-spacing:0.05em;color:#5E5E5E;">Price</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase whitespace-nowrap text-right" style="letter-spacing:0.05em;color:#5E5E5E;">Qty</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase whitespace-nowrap text-right" style="letter-spacing:0.05em;color:#5E5E5E;">Amount</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase whitespace-nowrap text-right" style="letter-spacing:0.05em;color:#5E5E5E;">VAT</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase whitespace-nowrap text-right" style="letter-spacing:0.05em;color:#5E5E5E;">Total</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;width:100px;"></th>
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
<tr class="group transition-colors {{ $sale ? 'cursor-pointer' : '' }}" style="border-top:1px solid #EAECEE;"
    onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'"
    @if($sale) onclick="window.location.href='{{ route('sales.show', $sale) }}'" @endif>
<td class="px-4 py-4 text-sm whitespace-nowrap" style="color:#5E5E5E;">{{ $sale ? $sale->date->format('Y-m-d') : '-' }}</td>
<td class="px-4 py-4 text-sm font-bold truncate" style="max-width:160px;color:#2B3437;">{{ $sale?->customer_name ?: ($sale?->customer_code ?: '-') }}</td>
<td class="px-4 py-4 text-sm font-bold" style="color:#2B3437;">
@if($sale)
<a href="{{ route('sales.show', $sale) }}" class="font-bold" style="color:#2B3437;text-decoration:underline;text-underline-offset:3px;text-decoration-color:#5E5E5E;" onclick="event.stopPropagation();">{{ $sale->invoice_number ?: '#' . $sale->id }}</a>
@else - @endif
</td>
<td class="px-4 py-4 text-sm" style="color:#5E5E5E;">{{ $item->product?->name ?: 'Product #' . $item->product_id }}</td>
<td class="px-4 py-4 text-sm tabular-nums text-right font-bold whitespace-nowrap" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($item->selling_price, 2) }}</td>
<td class="px-4 py-4 text-sm tabular-nums text-right" style="color:#5E5E5E;">{{ number_format($item->quantity) }}</td>
<td class="px-4 py-4 text-sm tabular-nums text-right font-bold whitespace-nowrap" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($amount, 2) }}</td>
<td class="px-4 py-4 text-sm tabular-nums text-right whitespace-nowrap" style="color:#5E5E5E;">{{ $currencySymbol ?? '$' }} {{ number_format($vat, 2) }}</td>
<td class="px-4 py-4 text-sm font-bold tabular-nums text-right whitespace-nowrap" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($subtotal, 2) }}</td>
<td class="px-4 py-4 text-right" onclick="event.stopPropagation();">
@if($sale)
<div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
<a href="{{ route('sales.show', $sale) }}" class="p-1.5 transition-colors" style="color:#5E5E5E;" onmouseover="this.style.color='#2B3437';this.style.background='#EAECEE'" onmouseout="this.style.color='#5E5E5E';this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
</a>
@if(auth()->user()->role !== 'viewer')
<form method="POST" action="{{ route('sales.destroy', $sale) }}" class="inline-flex" onsubmit="return confirm('Delete this sale? Stock will be restored.');">
@csrf @method('DELETE')
<button type="submit" class="p-1.5 transition-colors" style="color:#5E5E5E;" onmouseover="this.style.color='#9F403D'" onmouseout="this.style.color='#5E5E5E'">
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
<span class="material-symbols-outlined block" style="font-size:3rem;color:#D3D8DE;margin-bottom:1rem;">receipt</span>
<p class="text-sm font-bold" style="color:#5E5E5E;">No sales recorded yet.</p>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('sales.create') }}" class="inline-block text-[11px] font-bold uppercase" style="margin-top:1rem;color:#2B3437;border-bottom:2px solid #5E5E5E;padding-bottom:0.25rem;letter-spacing:0.05em;">Create First Sale</a>
@endif
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

<div class="flex flex-col sm:flex-row items-center justify-between gap-4" style="padding:1rem 1.5rem;border-top:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-xs font-bold" style="color:#5E5E5E;">
@if($items->total() > 0)
Showing <span style="color:#2B3437;">{{ $items->firstItem() }}</span> to <span style="color:#2B3437;">{{ $items->lastItem() }}</span> of <span style="color:#2B3437;">{{ $items->total() }}</span> line items
@else No results @endif
</p>
@if($items->hasPages())
<nav class="flex items-center gap-1" aria-label="Pagination">
@if (!$items->onFirstPage())
<a class="p-1.5 transition-colors" style="color:#5E5E5E;" href="{{ $items->previousPageUrl() }}" onmouseover="this.style.background='#EAECEE'" onmouseout="this.style.background='transparent'"><span class="material-symbols-outlined" style="font-size:18px;">chevron_left</span></a>
@endif
@foreach ($items->getUrlRange(max(1, $items->currentPage() - 2), min($items->lastPage(), $items->currentPage() + 2)) ?: [1 => $items->url(1)] as $page => $url)
@if ($page == $items->currentPage())
<span class="px-3 py-1.5 text-xs font-bold" style="background:#5E5E5E;color:#F8F8F8;">{{ $page }}</span>
@else
<a class="px-3 py-1.5 text-xs font-bold transition-colors" style="color:#5E5E5E;" href="{{ $url }}" onmouseover="this.style.background='#EAECEE'" onmouseout="this.style.background='transparent'">{{ $page }}</a>
@endif
@endforeach
@if ($items->hasMorePages())
<a class="p-1.5 transition-colors" style="color:#5E5E5E;" href="{{ $items->nextPageUrl() }}" onmouseover="this.style.background='#EAECEE'" onmouseout="this.style.background='transparent'"><span class="material-symbols-outlined" style="font-size:18px;">chevron_right</span></a>
@endif
</nav>
@endif
</div>
</div>

<div class="text-center text-[10px] uppercase font-bold pb-4" style="letter-spacing:0.05em;color:#5E5E5E;">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</div>
</div>
</div>
</main>
</body>
</html>
