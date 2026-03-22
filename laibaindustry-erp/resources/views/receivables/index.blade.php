<!DOCTYPE html>
<html lang="en" class="dark">
<head>
@include('partials.frontend-head', ['title' => 'Receivables - Laiba Safety'])
<style>
body { background-color: #131313; color: #e2e2e2; font-family: 'Inter', sans-serif; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #FFFFFF; color: #131313; }
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'receivables'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#131313;">

<header class="h-16 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="background:#1B1B1B;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 hover:text-white rounded-md" style="color:#8e9192;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
</div>
</header>

<div class="flex-1 overflow-y-auto no-scrollbar">
<div class="max-w-[1400px] mx-auto px-6 md:px-8 py-8 flex flex-col gap-8">

<div>
<p class="text-xs font-medium uppercase tracking-[0.15em] mb-2" style="color:#8e9192;">Accounts Receivable</p>
<h1 class="text-4xl font-bold tracking-tight" style="color:#FFFFFF;letter-spacing:-0.02em;">Receivables</h1>
</div>

@if (session('success'))
<div class="flex items-center gap-3 px-5 py-3.5 rounded-md" style="background:rgba(255,255,255,0.04);">
<span class="material-symbols-outlined" style="color:#FFFFFF;font-size:20px;">check_circle</span>
<span class="text-sm font-medium" style="color:#C4C7C8;">{{ session('success') }}</span>
</div>
@endif
@if (session('error'))
<div class="flex items-center gap-3 px-5 py-3.5 rounded-md" style="background:rgba(255,180,171,0.06);">
<span class="material-symbols-outlined" style="color:#FFB4AB;font-size:20px;">error</span>
<span class="text-sm font-medium" style="color:#FFB4AB;">{{ session('error') }}</span>
</div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
<div class="rounded-lg p-5" style="background:#1B1B1B;">
<p class="text-[10px] font-semibold uppercase tracking-[0.15em] mb-3" style="color:#8e9192;">Total Invoiced</p>
<p class="text-2xl font-bold font-mono tabular-nums" style="color:#FFFFFF;">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_amount ?? 0, 2) }}</p>
</div>
<div class="rounded-lg p-5" style="background:#1B1B1B;">
<p class="text-[10px] font-semibold uppercase tracking-[0.15em] mb-3" style="color:#8e9192;">Total Received</p>
<p class="text-2xl font-bold font-mono tabular-nums" style="color:#FFFFFF;">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_received ?? 0, 2) }}</p>
</div>
<div class="rounded-lg p-5 relative overflow-hidden" style="background:#FFFFFF;">
<p class="text-[10px] font-semibold uppercase tracking-[0.15em] mb-3" style="color:#666;">Outstanding</p>
<p class="text-2xl font-bold font-mono tabular-nums" style="color:#131313;">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_remaining ?? 0, 2) }}</p>
<div class="absolute top-4 right-4">
<span class="material-symbols-outlined" style="font-size:32px;color:rgba(19,19,19,0.08);">account_balance_wallet</span>
</div>
</div>
</div>

<div class="rounded-lg overflow-hidden" style="background:#1B1B1B;">
<div class="px-6 py-4 flex items-center justify-between" style="border-bottom:1px solid rgba(68,71,72,0.15);">
<div>
<p class="text-sm font-semibold" style="color:#FFFFFF;">All Receivables</p>
<p class="text-xs mt-0.5" style="color:#8e9192;">Track amounts owed by customers. Record payments via Edit.</p>
</div>
</div>

<div class="overflow-x-auto">
<table class="w-full text-left min-w-[700px]">
<thead>
<tr style="background:#0E0E0E;">
<th class="px-6 py-3.5 text-[10px] font-semibold uppercase tracking-[0.15em]" style="color:#8e9192;">Date</th>
<th class="px-6 py-3.5 text-[10px] font-semibold uppercase tracking-[0.15em]" style="color:#8e9192;">Invoice</th>
<th class="px-6 py-3.5 text-[10px] font-semibold uppercase tracking-[0.15em]" style="color:#8e9192;">Customer</th>
<th class="px-6 py-3.5 text-[10px] font-semibold uppercase tracking-[0.15em] text-right" style="color:#8e9192;">Amount</th>
<th class="px-6 py-3.5 text-[10px] font-semibold uppercase tracking-[0.15em] text-right" style="color:#8e9192;">Received</th>
<th class="px-6 py-3.5 text-[10px] font-semibold uppercase tracking-[0.15em] text-right" style="color:#8e9192;">Remaining</th>
<th class="px-6 py-3.5 text-[10px] font-semibold uppercase tracking-[0.15em] text-right w-28" style="color:#8e9192;">Status</th>
</tr>
</thead>
<tbody>
@forelse($receivables as $r)
@php $remaining = (float)$r->amount - (float)$r->received; @endphp
<tr class="group transition-colors duration-150" style="border-bottom:1px solid rgba(68,71,72,0.15);" onmouseenter="this.style.background='#2A2A2A'" onmouseleave="this.style.background='transparent'">
<td class="px-6 py-4 text-sm whitespace-nowrap" style="color:#C4C7C8;">{{ $r->date->format('Y-m-d') }}</td>
<td class="px-6 py-4 text-sm font-medium" style="color:#FFFFFF;">{{ $r->invoice_number ?: '—' }}</td>
<td class="px-6 py-4 text-sm" style="color:#C4C7C8;">{{ $r->customer_name ?: $r->customer_code ?: '—' }}</td>
<td class="px-6 py-4 text-sm font-mono text-right whitespace-nowrap tabular-nums" style="color:#FFFFFF;">{{ $currencySymbol ?? '$' }} {{ number_format($r->amount, 2) }}</td>
<td class="px-6 py-4 text-sm font-mono text-right whitespace-nowrap tabular-nums" style="color:#8e9192;">{{ $currencySymbol ?? '$' }} {{ number_format($r->received, 2) }}</td>
<td class="px-6 py-4 text-sm font-mono font-semibold text-right whitespace-nowrap tabular-nums" style="color:#FFFFFF;">{{ $currencySymbol ?? '$' }} {{ number_format($remaining, 2) }}</td>
<td class="px-6 py-4 text-right">
@if ($remaining > 0)
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('receivables.edit', $r) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold uppercase tracking-[0.05em] rounded-md transition-all duration-200 whitespace-nowrap" style="color:#FFFFFF;border:1px solid rgba(68,71,72,0.4);" onmouseenter="this.style.background='rgba(255,255,255,0.06)';this.style.borderColor='#8e9192'" onmouseleave="this.style.background='transparent';this.style.borderColor='rgba(68,71,72,0.4)'">
<span class="material-symbols-outlined" style="font-size:14px;">payments</span>
Record
</a>
@else
<span class="text-xs" style="color:#8e9192;">—</span>
@endif
@else
<span class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.1em] rounded" style="background:rgba(255,255,255,0.06);color:#C4C7C8;">Paid</span>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="7" class="px-6 py-16 text-center">
<span class="material-symbols-outlined block mb-3 mx-auto" style="font-size:40px;color:#353535;">account_balance_wallet</span>
<p class="text-sm mb-1" style="color:#8e9192;">No receivables yet</p>
<p class="text-xs" style="color:#555;">Receivables are created automatically when you create a sale.</p>
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

@if($receivables->hasPages())
<div class="flex items-center justify-between px-6 py-4" style="background:#0E0E0E;">
<p class="text-xs" style="color:#8e9192;">
Showing <span class="font-medium" style="color:#FFFFFF;">{{ $receivables->firstItem() }}</span>–<span class="font-medium" style="color:#FFFFFF;">{{ $receivables->lastItem() }}</span> of <span class="font-medium" style="color:#FFFFFF;">{{ $receivables->total() }}</span>
</p>
<nav class="flex items-center gap-1" aria-label="Pagination">
@if (!$receivables->onFirstPage())
<a class="p-1.5 rounded-md transition-colors" style="color:#C4C7C8;" href="{{ $receivables->previousPageUrl() }}" onmouseenter="this.style.background='#2A2A2A'" onmouseleave="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:18px;">chevron_left</span>
</a>
@endif
@foreach ($receivables->getUrlRange(max(1, $receivables->currentPage() - 2), min($receivables->lastPage(), $receivables->currentPage() + 2)) ?: [1 => $receivables->url(1)] as $page => $url)
@if ($page == $receivables->currentPage())
<span class="w-8 h-8 flex items-center justify-center text-xs font-bold rounded-md" style="background:#FFFFFF;color:#131313;">{{ $page }}</span>
@else
<a class="w-8 h-8 flex items-center justify-center text-xs font-medium rounded-md transition-colors" style="color:#C4C7C8;" href="{{ $url }}" onmouseenter="this.style.background='#2A2A2A'" onmouseleave="this.style.background='transparent'">{{ $page }}</a>
@endif
@endforeach
@if ($receivables->hasMorePages())
<a class="p-1.5 rounded-md transition-colors" style="color:#C4C7C8;" href="{{ $receivables->nextPageUrl() }}" onmouseenter="this.style.background='#2A2A2A'" onmouseleave="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:18px;">chevron_right</span>
</a>
@endif
</nav>
</div>
@else
<div class="px-6 py-4" style="background:#0E0E0E;">
<p class="text-xs" style="color:#8e9192;">
@if($receivables->total() > 0)
Showing all <span class="font-medium" style="color:#FFFFFF;">{{ $receivables->total() }}</span> results
@else
No results
@endif
</p>
</div>
@endif
</div>

<footer class="pt-4 pb-8 text-center">
<p class="text-xs" style="color:rgba(142,145,146,0.4);">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</p>
</footer>

</div>
</div>

</main>
</body>
</html>
