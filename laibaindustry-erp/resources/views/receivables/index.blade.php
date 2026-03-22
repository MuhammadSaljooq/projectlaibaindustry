<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Receivables - Laiba Safety'])
<style>
body { background-color: #FFFFFF; color: #2B3437; font-family: 'Inter', sans-serif; color-scheme: light; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #2B3437; color: #FFFFFF; }
.receivable-row-clickable { cursor: pointer; }
.receivable-row-clickable:focus-visible { outline: 2px solid #5E5E5E; outline-offset: -2px; }
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'receivables'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#FFFFFF;">

<header class="h-14 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 rounded-none" style="color:#5E5E5E;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
</div>
</header>

<div class="flex-1 overflow-y-auto no-scrollbar scroll-smooth">
<div class="max-w-[1400px] mx-auto px-6 md:px-8 py-8 flex flex-col" style="gap:3rem;">

<div>
<div class="flex items-end justify-between" style="padding-bottom:0.75rem;border-bottom:2px solid #5E5E5E;">
<h1 class="font-bold" style="font-size:1.5rem;letter-spacing:-0.02em;color:#2B3437;">Receivables</h1>
<span class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Section AR-01</span>
</div>
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-top:0.75rem;">Accounts Receivable</p>
</div>

@if (session('success'))
<div style="border:1px solid #D3D8DE;padding:0.75rem 1.25rem;" class="text-sm font-bold flex items-center gap-3">
<span class="material-symbols-outlined" style="color:#5E5E5E;font-size:20px;">check_circle</span>
<span>{{ session('success') }}</span>
</div>
@endif
@if (session('error'))
<div style="border:1px solid #9F403D;padding:0.75rem 1.25rem;" class="text-sm font-bold flex items-center gap-3">
<span class="material-symbols-outlined" style="color:#9F403D;font-size:20px;">error</span>
<span style="color:#9F403D;">{{ session('error') }}</span>
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3" style="gap:1px;background:#D3D8DE;border:1px solid #D3D8DE;">
<div class="text-center" style="background:#FFFFFF;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">Total Invoiced</p>
<p class="text-xl font-bold font-mono tabular-nums" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_amount ?? 0, 2) }}</p>
</div>
<div class="text-center" style="background:#FFFFFF;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">Total Received</p>
<p class="text-xl font-bold font-mono tabular-nums" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_received ?? 0, 2) }}</p>
</div>
<div class="text-center" style="background:#F8F9FA;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">Outstanding</p>
<p class="text-xl font-bold font-mono tabular-nums" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_remaining ?? 0, 2) }}</p>
</div>
</div>

<div style="border:1px solid #D3D8DE;">
<div style="padding:1rem 1.5rem;border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-sm font-bold" style="color:#2B3437;">All Receivables</p>
<p class="text-xs font-bold mt-0.5" style="color:#5E5E5E;">Click a row to open the receivable. Record payments on the detail page.</p>
</div>

<div class="overflow-x-auto">
<table class="w-full text-left border-collapse min-w-[700px]">
<thead>
<tr style="background:#F8F9FA;border-bottom:1px solid #D3D8DE;">
<th class="px-6 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Date</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Invoice</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Customer</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-center whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;min-width:7rem;">Bill</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-center whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;min-width:7rem;">Received</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-center whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;min-width:7rem;">Remaining</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-center w-28" style="letter-spacing:0.05em;color:#5E5E5E;">Status</th>
</tr>
</thead>
<tbody>
@forelse($receivables as $r)
@php $remaining = (float)$r->amount - (float)$r->received; @endphp
<tr
    class="group transition-colors receivable-row-clickable"
    style="border-top:1px solid #EAECEE;"
    tabindex="0"
    role="link"
    aria-label="Open receivable for {{ $r->customer_name ?: $r->customer_code ?: 'customer' }}"
    data-href="{{ auth()->user()->role === 'viewer' ? route('receivables.show', $r) : route('receivables.edit', $r) }}"
    onmouseenter="this.style.background='#F8F9FA'"
    onmouseleave="this.style.background='transparent'"
    onclick="if (!event.target.closest('a, button')) { window.location = this.dataset.href; }"
    onkeydown="if ((event.key === 'Enter' || event.key === ' ') && !event.target.closest('a, button')) { event.preventDefault(); window.location = this.dataset.href; }"
>
<td class="px-6 py-4 text-sm whitespace-nowrap font-bold" style="color:#5E5E5E;">{{ $r->date ? $r->date->format('Y-m-d') : '—' }}</td>
<td class="px-6 py-4 text-sm font-bold" style="color:#2B3437;">
@php $invCount = (int) ($r->sales_count ?? 0); @endphp
@if ($invCount > 1)
<span class="inline-flex items-center gap-1.5">{{ $invCount }} invoices</span>
@elseif ($invCount === 1)
{{ $r->invoice_number ?: '—' }}
@else
—
@endif
</td>
<td class="px-6 py-4 text-sm font-bold" style="color:#5E5E5E;">{{ $r->customer_name ?: $r->customer_code ?: '—' }}</td>
<td class="px-6 py-4 text-sm font-mono text-center whitespace-nowrap tabular-nums font-bold" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($r->amount, 2) }}</td>
<td class="px-6 py-4 text-sm font-mono text-center whitespace-nowrap tabular-nums font-bold" style="color:#5E5E5E;">{{ $currencySymbol ?? '$' }} {{ number_format($r->received, 2) }}</td>
<td class="px-6 py-4 text-sm font-mono font-bold text-center whitespace-nowrap tabular-nums" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($remaining, 2) }}</td>
<td class="px-6 py-4 text-center">
@if ($remaining > 0)
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('receivables.edit', $r) }}" onclick="event.stopPropagation()" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-[11px] font-bold uppercase whitespace-nowrap transition-all mx-auto" style="color:#2B3437;border:1px solid #5E5E5E;letter-spacing:0.05em;" onmouseenter="this.style.background='#F8F9FA'" onmouseleave="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:14px;">payments</span>
Record
</a>
@else
<span class="text-xs font-bold inline-block" style="color:#5E5E5E;">—</span>
@endif
@else
<span class="inline-flex items-center justify-center px-2.5 py-1 text-[10px] font-bold uppercase mx-auto" style="letter-spacing:0.05em;border:1px solid #D3D8DE;color:#5E5E5E;">Paid</span>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="7" class="px-6 py-16 text-center">
<span class="material-symbols-outlined block mb-3 mx-auto" style="font-size:40px;color:#D3D8DE;">account_balance_wallet</span>
<p class="text-sm font-bold mb-1" style="color:#5E5E5E;">No receivables yet</p>
<p class="text-xs font-bold" style="color:#5E5E5E;">Receivables are created automatically when you create a sale.</p>
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

@if($receivables->hasPages())
<div class="flex items-center justify-between px-6 py-4" style="border-top:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-xs font-bold" style="color:#5E5E5E;">
Showing <span style="color:#2B3437;">{{ $receivables->firstItem() }}</span>–<span style="color:#2B3437;">{{ $receivables->lastItem() }}</span> of <span style="color:#2B3437;">{{ $receivables->total() }}</span>
</p>
<nav class="flex items-center gap-1" aria-label="Pagination">
@if (!$receivables->onFirstPage())
<a class="p-1.5 transition-colors" style="color:#5E5E5E;" href="{{ $receivables->previousPageUrl() }}" onmouseenter="this.style.background='#EAECEE'" onmouseleave="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:18px;">chevron_left</span>
</a>
@endif
@foreach ($receivables->getUrlRange(max(1, $receivables->currentPage() - 2), min($receivables->lastPage(), $receivables->currentPage() + 2)) ?: [1 => $receivables->url(1)] as $page => $url)
@if ($page == $receivables->currentPage())
<span class="w-8 h-8 flex items-center justify-center text-xs font-bold" style="background:#5E5E5E;color:#F8F8F8;">{{ $page }}</span>
@else
<a class="w-8 h-8 flex items-center justify-center text-xs font-bold transition-colors" style="color:#5E5E5E;" href="{{ $url }}" onmouseenter="this.style.background='#EAECEE'" onmouseleave="this.style.background='transparent'">{{ $page }}</a>
@endif
@endforeach
@if ($receivables->hasMorePages())
<a class="p-1.5 transition-colors" style="color:#5E5E5E;" href="{{ $receivables->nextPageUrl() }}" onmouseenter="this.style.background='#EAECEE'" onmouseleave="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:18px;">chevron_right</span>
</a>
@endif
</nav>
</div>
@else
<div class="px-6 py-4" style="border-top:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-xs font-bold" style="color:#5E5E5E;">
@if($receivables->total() > 0)
Showing all <span style="color:#2B3437;">{{ $receivables->total() }}</span> results
@else
No results
@endif
</p>
</div>
@endif
</div>

<footer class="pb-8 text-center">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</p>
</footer>

</div>
</div>

</main>
</body>
</html>
