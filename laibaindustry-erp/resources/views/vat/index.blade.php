<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'VAT - Laiba Safety'])
<style>
body { background-color: #FFFFFF; color: #2B3437; font-family: 'Inter', sans-serif; color-scheme: light; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #2B3437; color: #FFFFFF; }
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'vat'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#FFFFFF;">

<header class="h-14 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 rounded-none" style="color:#5E5E5E;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
</div>
<div class="flex items-center gap-3">
<a href="{{ route('vat.export') }}" class="h-9 px-4 text-[11px] font-bold uppercase flex items-center gap-2 transition-all" style="color:#2B3437;border:1px solid #5E5E5E;border-radius:0;letter-spacing:0.05em;background:transparent;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:14px;">download</span>
CSV
</a>
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col" style="gap:3rem;">

<div>
<div class="flex items-end justify-between" style="padding-bottom:0.75rem;border-bottom:2px solid #5E5E5E;">
<h2 class="font-bold" style="font-size:1.5rem;letter-spacing:-0.02em;color:#2B3437;">VAT</h2>
<span class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Section VAT-01</span>
</div>
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-top:0.75rem;">Sales &amp; purchase tax ledger</p>
</div>

@php $netVat = (float)($totals->net_vat ?? 0); @endphp

<div class="grid grid-cols-1 md:grid-cols-3" style="gap:1px;background:#D3D8DE;border:1px solid #D3D8DE;">
<div style="background:#FFFFFF;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">Sales VAT (output)</p>
<p class="font-bold tabular-nums font-mono" style="font-size:1.25rem;letter-spacing:-0.02em;color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($totals->sales_vat ?? 0, 2) }}</p>
</div>
<div style="background:#FFFFFF;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">Purchase VAT (input)</p>
<p class="font-bold tabular-nums font-mono" style="font-size:1.25rem;letter-spacing:-0.02em;color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($totals->purchase_vat ?? 0, 2) }}</p>
</div>
<div style="background:#F8F9FA;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">Net VAT payable</p>
<p class="font-bold tabular-nums font-mono" style="font-size:1.25rem;letter-spacing:-0.02em;{{ $netVat < 0 ? 'color:#9F403D;' : 'color:#2B3437;' }}">{{ $currencySymbol ?? '$' }} {{ number_format($netVat, 2) }}</p>
</div>
</div>

<form method="GET" action="{{ route('vat.index') }}" class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3" style="border:1px solid #D3D8DE;padding:1rem 1.5rem;">
<div class="relative flex-1 min-w-0">
<span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined pointer-events-none" style="font-size:18px;color:#5E5E5E;">search</span>
<input class="w-full h-10 pl-10 pr-4 text-sm font-bold outline-none transition-all" style="background:transparent;border:1px solid #D3D8DE;border-radius:0;color:#2B3437;" name="search" type="text" placeholder="Search invoice, customer, supplier..." value="{{ request('search') }}" onfocus="this.style.borderColor='#5E5E5E';this.style.borderWidth='2px'" onblur="this.style.borderColor='#D3D8DE';this.style.borderWidth='1px'"/>
</div>
<div class="flex items-center gap-3 shrink-0 flex-wrap">
<input class="h-10 px-3 text-sm font-bold outline-none transition-all" style="background:#FFFFFF;border:1px solid #D3D8DE;border-radius:0;color:#2B3437;color-scheme:light;" name="from" type="date" value="{{ request('from') }}" onfocus="this.style.borderColor='#5E5E5E';this.style.borderWidth='2px'" onblur="this.style.borderColor='#D3D8DE';this.style.borderWidth='1px'"/>
<input class="h-10 px-3 text-sm font-bold outline-none transition-all" style="background:#FFFFFF;border:1px solid #D3D8DE;border-radius:0;color:#2B3437;color-scheme:light;" name="to" type="date" value="{{ request('to') }}" onfocus="this.style.borderColor='#5E5E5E';this.style.borderWidth='2px'" onblur="this.style.borderColor='#D3D8DE';this.style.borderWidth='1px'"/>
</div>
<button type="submit" class="h-10 px-5 text-[11px] font-bold uppercase flex items-center justify-center gap-2 shrink-0" style="background:#5E5E5E;color:#F8F8F8;border-radius:0;letter-spacing:0.05em;">
<span class="material-symbols-outlined" style="font-size:14px;">filter_list</span>FILTER
</button>
@if(request('search') || request('from') || request('to'))
<a href="{{ route('vat.index') }}" class="h-10 px-4 text-[11px] font-bold uppercase flex items-center justify-center shrink-0" style="color:#5E5E5E;border:1px solid #5E5E5E;border-radius:0;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">CLEAR</a>
@endif
</form>

<div style="border:1px solid #D3D8DE;">
<div style="padding:1rem 1.5rem;border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-sm font-bold" style="color:#2B3437;">VAT entries</p>
<p class="text-xs font-bold mt-0.5" style="color:#5E5E5E;">Recorded automatically from sales and purchases.</p>
</div>

<div class="overflow-x-auto">
<table class="w-full text-left border-collapse" style="min-width:900px;">
<thead>
<tr style="background:#F8F9FA;border-bottom:1px solid #D3D8DE;">
<th class="px-6 py-3 text-[10px] font-bold uppercase whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;">Date</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;">Type</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;">Invoice</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;">Customer / supplier</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-right whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;">Subtotal</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-right whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;">VAT rate</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-right whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;">VAT amount</th>
</tr>
</thead>
<tbody>
@forelse($entries as $entry)
<tr class="transition-colors" style="border-top:1px solid #EAECEE;" onmouseenter="this.style.background='#F8F9FA'" onmouseleave="this.style.background='transparent'">
<td class="px-6 py-4 text-sm whitespace-nowrap font-bold" style="color:#5E5E5E;">{{ $entry->date->format('Y-m-d') }}</td>
<td class="px-6 py-4">
@if($entry->type === 'sale')
<span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;border:1px solid #D3D8DE;color:#2B3437;background:#FFFFFF;">
<span class="material-symbols-outlined" style="font-size:14px;">shopping_cart</span>
Sale
</span>
@else
<span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;border:1px solid #5E5E5E;color:#5E5E5E;background:#F8F9FA;">
<span class="material-symbols-outlined" style="font-size:14px;">shopping_bag</span>
Purchase
</span>
@endif
</td>
<td class="px-6 py-4 text-sm font-bold" style="color:#2B3437;">{{ $entry->invoice_number ?: '—' }}</td>
<td class="px-6 py-4 text-sm font-bold" style="color:#5E5E5E;">{{ $entry->customer_name ?: $entry->customer_code ?: '—' }}</td>
<td class="px-6 py-4 text-sm font-mono text-right whitespace-nowrap tabular-nums font-bold" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($entry->subtotal, 2) }}</td>
<td class="px-6 py-4 text-sm font-mono text-right whitespace-nowrap tabular-nums font-bold" style="color:#5E5E5E;">{{ number_format($entry->vat_rate, 2) }}%</td>
<td class="px-6 py-4 text-sm font-mono text-right whitespace-nowrap tabular-nums font-bold" style="color:{{ $entry->type === 'sale' ? '#2B3437' : '#5E5E5E' }};">{{ $currencySymbol ?? '$' }} {{ number_format($entry->vat_amount, 2) }}</td>
</tr>
@empty
<tr>
<td colspan="7" class="px-6 py-16 text-center">
<span class="material-symbols-outlined block mb-3 mx-auto" style="font-size:40px;color:#D3D8DE;">receipt</span>
<p class="text-sm font-bold mb-1" style="color:#5E5E5E;">No VAT entries yet</p>
<p class="text-xs font-bold" style="color:#5E5E5E;">Entries are created when you record a sale or purchase.</p>
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

@if($entries->hasPages())
<div class="flex items-center justify-between px-6 py-4" style="border-top:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-xs font-bold" style="color:#5E5E5E;">
Showing <span style="color:#2B3437;">{{ $entries->firstItem() }}</span>–<span style="color:#2B3437;">{{ $entries->lastItem() }}</span> of <span style="color:#2B3437;">{{ $entries->total() }}</span>
</p>
<nav class="flex items-center gap-1" aria-label="Pagination">
@if (!$entries->onFirstPage())
<a class="p-1.5 transition-colors" style="color:#5E5E5E;" href="{{ $entries->previousPageUrl() }}" onmouseenter="this.style.background='#EAECEE'" onmouseleave="this.style.background='transparent'"><span class="material-symbols-outlined" style="font-size:18px;">chevron_left</span></a>
@endif
@foreach ($entries->getUrlRange(max(1, $entries->currentPage() - 2), min($entries->lastPage(), $entries->currentPage() + 2)) ?: [1 => $entries->url(1)] as $page => $url)
@if ($page == $entries->currentPage())
<span class="w-8 h-8 flex items-center justify-center text-xs font-bold" style="background:#5E5E5E;color:#F8F8F8;">{{ $page }}</span>
@else
<a class="w-8 h-8 flex items-center justify-center text-xs font-bold transition-colors" style="color:#5E5E5E;" href="{{ $url }}" onmouseenter="this.style.background='#EAECEE'" onmouseleave="this.style.background='transparent'">{{ $page }}</a>
@endif
@endforeach
@if ($entries->hasMorePages())
<a class="p-1.5 transition-colors" style="color:#5E5E5E;" href="{{ $entries->nextPageUrl() }}" onmouseenter="this.style.background='#EAECEE'" onmouseleave="this.style.background='transparent'"><span class="material-symbols-outlined" style="font-size:18px;">chevron_right</span></a>
@endif
</nav>
</div>
@else
<div class="px-6 py-4" style="border-top:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-xs font-bold" style="color:#5E5E5E;">
@if($entries->total() > 0)
Showing all <span style="color:#2B3437;">{{ $entries->total() }}</span> results
@else
No results
@endif
</p>
</div>
@endif
</div>

<div class="text-center text-[10px] uppercase font-bold pb-4" style="letter-spacing:0.05em;color:#5E5E5E;">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</div>
</div>
</div>
</main>
</body>
</html>
