<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Purchases - Laiba Safety'])
<style>
body { background-color: #FFFFFF; color: #2B3437; font-family: 'Inter', sans-serif; color-scheme: light; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #2B3437; color: #FFFFFF; }
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'purchases'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#FFFFFF;">

<header class="h-14 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 rounded-none" style="color:#5E5E5E;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
</div>
<div class="flex items-center gap-3">
<a href="{{ route('purchases.export') }}" class="h-9 px-4 text-[11px] font-bold uppercase flex items-center gap-2 transition-all" style="color:#2B3437;border:1px solid #5E5E5E;border-radius:0;letter-spacing:0.05em;background:transparent;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:14px;">download</span>
CSV
</a>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('purchases.create') }}" class="h-9 px-5 text-[11px] font-bold uppercase flex items-center gap-2 active:scale-[0.98] transition-all" style="background:#5E5E5E;color:#F8F8F8;border-radius:0;letter-spacing:0.05em;">
<span class="material-symbols-outlined" style="font-size:14px;">add</span>
NEW PURCHASE
</a>
@endif
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col" style="gap:3rem;">

<div>
<div class="flex items-end justify-between" style="padding-bottom:0.75rem;border-bottom:2px solid #5E5E5E;">
<h2 class="font-bold" style="font-size:1.5rem;letter-spacing:-0.02em;color:#2B3437;">Purchases</h2>
<span class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Section PUR-01</span>
</div>
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-top:0.75rem;">Procurement &amp; Payables Source</p>
</div>

@if (session('success'))
<div style="border:1px solid #D3D8DE;padding:0.75rem 1.25rem;" class="text-sm font-bold">{{ session('success') }}</div>
@endif
@if (session('error'))
<div style="border:1px solid #9F403D;padding:0.75rem 1.25rem;background:#FFFFFF;" class="text-sm font-bold"><span style="color:#9F403D;">{{ session('error') }}</span></div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3" style="gap:1px;background:#D3D8DE;border:1px solid #D3D8DE;">
<div style="background:#FFFFFF;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">Subtotal</p>
<p class="font-bold tabular-nums font-mono" style="font-size:1.25rem;letter-spacing:-0.02em;color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_subtotal ?? 0, 2) }}</p>
</div>
<div style="background:#FFFFFF;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">VAT (15%)</p>
<p class="font-bold tabular-nums font-mono" style="font-size:1.25rem;letter-spacing:-0.02em;color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_vat ?? 0, 2) }}</p>
</div>
<div style="background:#F8F9FA;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">Total Purchases</p>
<p class="font-bold tabular-nums font-mono" style="font-size:1.25rem;letter-spacing:-0.02em;color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_purchases ?? 0, 2) }}</p>
</div>
</div>

<form method="GET" action="{{ route('purchases.index') }}" class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3" style="border:1px solid #D3D8DE;padding:1rem 1.5rem;">
<div class="relative flex-1 min-w-0">
<span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined pointer-events-none" style="font-size:18px;color:#5E5E5E;">search</span>
<input class="w-full h-10 pl-10 pr-4 text-sm font-bold outline-none transition-all" style="background:transparent;border:1px solid #D3D8DE;border-radius:0;color:#2B3437;" name="search" type="text" placeholder="Search invoice, supplier, product..." value="{{ request('search') }}" onfocus="this.style.borderColor='#5E5E5E';this.style.borderWidth='2px'" onblur="this.style.borderColor='#D3D8DE';this.style.borderWidth='1px'"/>
</div>
<div class="flex items-center gap-3 shrink-0 flex-wrap">
<input class="h-10 px-3 text-sm font-bold outline-none transition-all" style="background:#FFFFFF;border:1px solid #D3D8DE;border-radius:0;color:#2B3437;color-scheme:light;" name="from" type="date" value="{{ request('from') }}" onfocus="this.style.borderColor='#5E5E5E';this.style.borderWidth='2px'" onblur="this.style.borderColor='#D3D8DE';this.style.borderWidth='1px'"/>
<input class="h-10 px-3 text-sm font-bold outline-none transition-all" style="background:#FFFFFF;border:1px solid #D3D8DE;border-radius:0;color:#2B3437;color-scheme:light;" name="to" type="date" value="{{ request('to') }}" onfocus="this.style.borderColor='#5E5E5E';this.style.borderWidth='2px'" onblur="this.style.borderColor='#D3D8DE';this.style.borderWidth='1px'"/>
</div>
<button type="submit" class="h-10 px-5 text-[11px] font-bold uppercase flex items-center justify-center gap-2 shrink-0" style="background:#5E5E5E;color:#F8F8F8;border-radius:0;letter-spacing:0.05em;">
<span class="material-symbols-outlined" style="font-size:14px;">filter_list</span>FILTER
</button>
@if(request('search') || request('from') || request('to'))
<a href="{{ route('purchases.index') }}" class="h-10 px-4 text-[11px] font-bold uppercase flex items-center justify-center shrink-0" style="color:#5E5E5E;border:1px solid #5E5E5E;border-radius:0;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">CLEAR</a>
@endif
</form>

<div style="border:1px solid #D3D8DE;">
<div style="padding:1rem 1.5rem;border-bottom:1px solid #D3D8DE;background:#F8F9FA;" class="flex flex-wrap items-center justify-between gap-3">
<div>
<p class="text-sm font-bold" style="color:#2B3437;">Line Items</p>
<p class="text-xs font-bold mt-0.5" style="color:#5E5E5E;">Click a row to open purchase detail</p>
</div>
</div>

<div class="overflow-x-auto">
<table class="w-full text-left border-collapse" style="min-width:1000px;">
<thead>
<tr style="background:#F8F9FA;border-bottom:1px solid #D3D8DE;">
<th class="px-4 py-3 text-[10px] font-bold uppercase whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;">Date</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;">Code</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;">Supplier</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;">Invoice</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;">Product</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase text-right whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;">Price</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase text-right whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;">Qty</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase text-right whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;">Amount</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase text-right whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;">VAT</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase text-right whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;">Subtotal</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase whitespace-nowrap w-20" style="letter-spacing:0.05em;color:#5E5E5E;"></th>
</tr>
</thead>
<tbody>
@forelse($items as $item)
@php $purchase = $item->purchase; @endphp
<tr class="group transition-colors {{ $purchase ? 'cursor-pointer' : '' }}" style="border-top:1px solid #EAECEE;"
    onmouseenter="this.style.background='#F8F9FA'" onmouseleave="this.style.background='transparent'"
    @if($purchase) onclick="window.location.href='{{ route('purchases.show', $purchase) }}'" @endif>
<td class="px-4 py-3 text-sm whitespace-nowrap font-bold" style="color:#5E5E5E;">{{ $purchase ? $purchase->date->format('Y-m-d H:i') : '-' }}</td>
<td class="px-4 py-3 text-sm font-bold" style="color:#5E5E5E;">{{ $purchase && $purchase->customer_code ? $purchase->customer_code : '—' }}</td>
<td class="px-4 py-3 text-sm font-bold" style="color:#5E5E5E;">{{ $purchase && $purchase->customer_name ? $purchase->customer_name : '—' }}</td>
<td class="px-4 py-3 text-sm font-bold" style="color:#2B3437;">
@if($purchase)
<a href="{{ route('purchases.show', $purchase) }}" class="font-bold" style="color:#2B3437;text-decoration:underline;text-underline-offset:3px;text-decoration-color:#5E5E5E;" onclick="event.stopPropagation();">{{ $purchase->invoice_number ?: '#' . $purchase->id }}</a>
@else — @endif
</td>
<td class="px-4 py-3 text-sm font-bold" style="color:#2B3437;">{{ $item->product_name ?: '—' }}</td>
<td class="px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums font-bold" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($item->price, 2) }}</td>
<td class="px-4 py-3 text-sm font-mono text-right font-bold" style="color:#5E5E5E;">{{ number_format($item->quantity) }}</td>
<td class="px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums font-bold" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($item->amount, 2) }}</td>
<td class="px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums font-bold" style="color:#5E5E5E;">{{ $currencySymbol ?? '$' }} {{ number_format($item->vat_amount, 2) }}</td>
<td class="px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($item->subtotal, 2) }}</td>
<td class="px-4 py-3" onclick="event.stopPropagation();">
@if($purchase)
<a href="{{ route('purchases.show', $purchase) }}" class="text-[10px] font-bold uppercase" style="color:#5E5E5E;letter-spacing:0.05em;text-decoration:underline;text-underline-offset:3px;" onclick="event.stopPropagation();">View</a>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="11" class="px-6 py-16 text-center">
<p class="text-sm font-bold mb-1" style="color:#5E5E5E;">No purchases yet.</p>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('purchases.create') }}" class="text-[11px] font-bold uppercase" style="color:#2B3437;border-bottom:2px solid #5E5E5E;padding-bottom:0.25rem;letter-spacing:0.05em;">Create your first purchase</a>
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
@else
No results
@endif
</p>
@if($items->hasPages())
<nav class="flex items-center gap-1" aria-label="Pagination">
@if (!$items->onFirstPage())
<a class="p-1.5 transition-colors" style="color:#5E5E5E;" href="{{ $items->previousPageUrl() }}" onmouseenter="this.style.background='#EAECEE'" onmouseleave="this.style.background='transparent'"><span class="material-symbols-outlined" style="font-size:18px;">chevron_left</span></a>
@endif
@foreach ($items->getUrlRange(max(1, $items->currentPage() - 2), min($items->lastPage(), $items->currentPage() + 2)) ?: [1 => $items->url(1)] as $page => $url)
@if ($page == $items->currentPage())
<span class="px-3 py-1.5 text-xs font-bold" style="background:#5E5E5E;color:#F8F8F8;">{{ $page }}</span>
@else
<a class="px-3 py-1.5 text-xs font-bold transition-colors" style="color:#5E5E5E;" href="{{ $url }}" onmouseenter="this.style.background='#EAECEE'" onmouseleave="this.style.background='transparent'">{{ $page }}</a>
@endif
@endforeach
@if ($items->hasMorePages())
<a class="p-1.5 transition-colors" style="color:#5E5E5E;" href="{{ $items->nextPageUrl() }}" onmouseenter="this.style.background='#EAECEE'" onmouseleave="this.style.background='transparent'"><span class="material-symbols-outlined" style="font-size:18px;">chevron_right</span></a>
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
