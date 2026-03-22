<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Payables - Laiba Safety'])
<style>
body { background-color: #FFFFFF; color: #2B3437; font-family: 'Inter', sans-serif; color-scheme: light; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #2B3437; color: #FFFFFF; }
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'payables'])

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
<h1 class="font-bold" style="font-size:1.5rem;letter-spacing:-0.02em;color:#2B3437;">Payables</h1>
<span class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Section AP-01</span>
</div>
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-top:0.75rem;">Accounts Payable</p>
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
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">Total Payable</p>
<p class="text-xl font-bold font-mono tabular-nums" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_amount ?? 0, 2) }}</p>
</div>
<div class="text-center" style="background:#FFFFFF;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">Amount Paid</p>
<p class="text-xl font-bold font-mono tabular-nums" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_received ?? 0, 2) }}</p>
</div>
<div class="text-center" style="background:#F8F9FA;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">Outstanding Balance</p>
<p class="text-xl font-bold font-mono tabular-nums" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_outstanding ?? 0, 2) }}</p>
</div>
</div>

<div style="border:1px solid #D3D8DE;">
<div style="padding:1rem 1.5rem;border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-sm font-bold" style="color:#2B3437;">All Payables</p>
<p class="text-xs font-bold mt-0.5" style="color:#5E5E5E;">Created from purchases. Record supplier payments via Record.</p>
</div>

<div class="overflow-x-auto">
<table class="w-full text-left border-collapse min-w-[900px]">
<thead>
<tr style="background:#F8F9FA;border-bottom:1px solid #D3D8DE;">
<th class="px-6 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Date</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Invoice</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Supplier</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Code</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-center whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;min-width:7rem;">Bill</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-center whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;min-width:7rem;">Paid</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-center whitespace-nowrap" style="letter-spacing:0.05em;color:#5E5E5E;min-width:7rem;">Balance</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-center" style="letter-spacing:0.05em;color:#5E5E5E;min-width:140px;">Actions</th>
</tr>
</thead>
<tbody>
@forelse($payables as $p)
@php $balance = (float)$p->amount - (float)$p->received; @endphp
<tr class="transition-colors" style="border-top:1px solid #EAECEE;" onmouseenter="this.style.background='#F8F9FA'" onmouseleave="this.style.background='transparent'">
<td class="px-6 py-4 text-sm whitespace-nowrap font-bold" style="color:#5E5E5E;">{{ $p->date->format('Y-m-d') }}</td>
<td class="px-6 py-4 text-sm font-bold" style="color:#2B3437;">{{ $p->invoice_number ?: '—' }}</td>
<td class="px-6 py-4 text-sm font-bold" style="color:#5E5E5E;">{{ $p->customer_name ?: '—' }}</td>
<td class="px-6 py-4 text-sm font-bold" style="color:#5E5E5E;">{{ $p->customer_code ?: '—' }}</td>
<td class="px-6 py-4 text-sm font-mono text-center whitespace-nowrap tabular-nums font-bold" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($p->amount, 2) }}</td>
<td class="px-6 py-4 text-sm font-mono text-center whitespace-nowrap tabular-nums font-bold" style="color:#5E5E5E;">{{ $currencySymbol ?? '$' }} {{ number_format($p->received, 2) }}</td>
<td class="px-6 py-4 text-sm font-mono font-bold text-center whitespace-nowrap tabular-nums" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($balance, 2) }}</td>
<td class="px-6 py-4 text-center">
<div class="flex flex-wrap items-center justify-center gap-2 w-full">
@if($balance > 0)
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('payables.edit', $p) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-[11px] font-bold uppercase whitespace-nowrap transition-all" style="color:#2B3437;border:1px solid #5E5E5E;letter-spacing:0.05em;" onmouseenter="this.style.background='#F8F9FA'" onmouseleave="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:14px;">payments</span>
Record
</a>
@else
<span class="text-xs font-bold inline-block" style="color:#5E5E5E;">—</span>
@endif
@else
<span class="inline-flex items-center justify-center px-2.5 py-1 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;border:1px solid #D3D8DE;color:#5E5E5E;">Paid</span>
@endif
@if(in_array(auth()->user()->role, ['admin', 'manager']))
<form method="POST" action="{{ route('payables.destroy', $p) }}" class="inline-flex" onsubmit="return confirm('Delete this payable entry? This cannot be undone.');">
@csrf
@method('DELETE')
<button type="submit" class="inline-flex items-center justify-center w-9 h-9 transition-all" style="color:#9F403D;border:1px solid #D3D8DE;background:transparent;" title="Delete payable" onmouseenter="this.style.borderColor='#9F403D';this.style.background='#F8F9FA'" onmouseleave="this.style.borderColor='#D3D8DE';this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:18px;">delete</span>
</button>
</form>
@endif
</div>
</td>
</tr>
@empty
<tr>
<td colspan="8" class="px-6 py-16 text-center">
<span class="material-symbols-outlined block mb-3 mx-auto" style="font-size:40px;color:#D3D8DE;">account_balance</span>
<p class="text-sm font-bold mb-1" style="color:#5E5E5E;">No payables yet</p>
<p class="text-xs font-bold mb-3" style="color:#5E5E5E;">Payables are created when you save a purchase.</p>
<a href="{{ route('purchases.create') }}" class="text-[11px] font-bold uppercase" style="color:#2B3437;border-bottom:2px solid #5E5E5E;padding-bottom:0.25rem;letter-spacing:0.05em;">Create a purchase</a>
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

@if($payables->hasPages())
<div class="flex items-center justify-between px-6 py-4" style="border-top:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-xs font-bold" style="color:#5E5E5E;">
Showing <span style="color:#2B3437;">{{ $payables->firstItem() }}</span>–<span style="color:#2B3437;">{{ $payables->lastItem() }}</span> of <span style="color:#2B3437;">{{ $payables->total() }}</span>
</p>
<nav class="flex items-center gap-1" aria-label="Pagination">
@if (!$payables->onFirstPage())
<a class="p-1.5 transition-colors" style="color:#5E5E5E;" href="{{ $payables->previousPageUrl() }}" onmouseenter="this.style.background='#EAECEE'" onmouseleave="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:18px;">chevron_left</span>
</a>
@endif
@foreach ($payables->getUrlRange(max(1, $payables->currentPage() - 2), min($payables->lastPage(), $payables->currentPage() + 2)) ?: [1 => $payables->url(1)] as $page => $url)
@if ($page == $payables->currentPage())
<span class="w-8 h-8 flex items-center justify-center text-xs font-bold" style="background:#5E5E5E;color:#F8F8F8;">{{ $page }}</span>
@else
<a class="w-8 h-8 flex items-center justify-center text-xs font-bold transition-colors" style="color:#5E5E5E;" href="{{ $url }}" onmouseenter="this.style.background='#EAECEE'" onmouseleave="this.style.background='transparent'">{{ $page }}</a>
@endif
@endforeach
@if ($payables->hasMorePages())
<a class="p-1.5 transition-colors" style="color:#5E5E5E;" href="{{ $payables->nextPageUrl() }}" onmouseenter="this.style.background='#EAECEE'" onmouseleave="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:18px;">chevron_right</span>
</a>
@endif
</nav>
</div>
@else
<div class="px-6 py-4" style="border-top:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-xs font-bold" style="color:#5E5E5E;">
@if($payables->total() > 0)
Showing all <span style="color:#2B3437;">{{ $payables->total() }}</span> results
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
