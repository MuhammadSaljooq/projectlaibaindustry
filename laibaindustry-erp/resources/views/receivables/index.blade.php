<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Receivables - ERP'])
@include('partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'receivables'])

<main class="stitch-ui flex-1 flex flex-col h-full overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">Receivables</h2>
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">AR_LEDGER_05</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Receivables</h1>
</div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] lg:text-right shrink-0">Accounts receivable · read / record payment</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<div class="sm:hidden">
<p class="text-[11px] font-bold uppercase tracking-wider text-[#586064]">Module</p>
<p class="text-xl font-black uppercase tracking-tight text-[#2B3437]">Receivables</p>
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

<div class="grid grid-cols-1 md:grid-cols-3 gap-0 border border-[#ABB3B7] bg-white md:divide-x md:divide-[#ABB3B7]">
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Total Invoiced</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_amount ?? 0, 2) }}</p>
</div>
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Total Received</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_received ?? 0, 2) }}</p>
</div>
<div class="p-6 border-2 border-[#5E5E5E] -m-px">
<p class="st-label mb-2 text-[#5E5E5E]">Outstanding</p>
<p class="text-2xl font-black font-mono text-[#5E5E5E] tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_remaining ?? 0, 2) }}</p>
</div>
</div>

<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[320px]">
<div class="px-5 py-4 border-b border-[#ABB3B7] flex flex-wrap items-center justify-between gap-3 bg-[#EAEFF1]">
<div>
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">Open &amp; closed receivables</h3>
<p class="text-[11px] text-[#586064] mt-1 max-w-xl">Track amounts owed. Record payments via <span class="font-bold text-[#5E5E5E]">Record</span> on any row with a balance.</p>
</div>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[700px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Invoice</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Customer</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Bill</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Received</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Remaining</th>
<th class="st-th px-4 py-3 text-right w-32 whitespace-nowrap">Status</th>
</tr>
</thead>
<tbody>
@forelse($receivables as $r)
@php $remaining = (float)$r->amount - (float)$r->received; @endphp
<tr class="st-tr">
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ $r->date->format('Y-m-d') }}</td>
<td class="st-td px-4 py-3 text-sm font-semibold text-[#2B3437]">{{ $r->invoice_number ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm text-[#2B3437]">{{ $r->customer_name ?: $r->customer_code ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#2B3437]">{{ $currencySymbol ?? '$' }} {{ number_format($r->amount, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#586064]">{{ $currencySymbol ?? '$' }} {{ number_format($r->received, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums text-[#5E5E5E]">{{ $currencySymbol ?? '$' }} {{ number_format($remaining, 2) }}</td>
<td class="st-td px-4 py-3 text-right">
@if ($remaining > 0)
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('receivables.edit', $r) }}" class="text-[11px] font-bold uppercase tracking-wider text-[#5E5E5E] border border-[#5E5E5E] px-2 py-1 inline-flex items-center gap-1 hover:bg-[#F1F4F6]">
<span class="material-symbols-outlined text-[14px]">payments</span>
Record
</a>
@else
<span class="text-xs text-[#586064]">—</span>
@endif
@else
<span class="text-[10px] font-bold uppercase tracking-wider text-[#586064] border border-[#ABB3B7] px-2 py-1 inline-block bg-[#F8F9FA]">Paid</span>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="7" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No receivables yet</p>
<p class="text-xs">Receivables are created automatically when you record a sale.</p>
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

@if($receivables->hasPages())
<div class="p-4 border-t border-[#ABB3B7] flex flex-col sm:flex-row items-center justify-between gap-4 bg-[#F8F9FA]">
<p class="text-xs text-[#586064] uppercase tracking-wide">
Showing <span class="font-bold text-[#2B3437] tabular-nums">{{ $receivables->firstItem() }}</span>–<span class="font-bold text-[#2B3437] tabular-nums">{{ $receivables->lastItem() }}</span> of <span class="font-bold text-[#2B3437] tabular-nums">{{ $receivables->total() }}</span>
</p>
<nav class="flex items-stretch border border-[#ABB3B7] bg-white divide-x divide-[#ABB3B7]" aria-label="Pagination">
@if (!$receivables->onFirstPage())
<a class="p-2 text-[#586064] hover:bg-[#F1F4F6] inline-flex items-center justify-center" href="{{ $receivables->previousPageUrl() }}" aria-label="Previous"><span class="material-symbols-outlined text-[20px]">chevron_left</span></a>
@endif
@foreach ($receivables->getUrlRange(max(1, $receivables->currentPage() - 2), min($receivables->lastPage(), $receivables->currentPage() + 2)) ?: [1 => $receivables->url(1)] as $page => $url)
@if ($page == $receivables->currentPage())
<span class="px-3 py-2 text-xs font-bold uppercase tracking-wider bg-[#5E5E5E] text-[#F8F8F8] inline-flex items-center justify-center min-w-[2.5rem]">{{ $page }}</span>
@else
<a class="px-3 py-2 text-xs font-bold text-[#586064] hover:bg-[#F1F4F6] inline-flex items-center justify-center min-w-[2.5rem]" href="{{ $url }}">{{ $page }}</a>
@endif
@endforeach
@if ($receivables->hasMorePages())
<a class="p-2 text-[#586064] hover:bg-[#F1F4F6] inline-flex items-center justify-center" href="{{ $receivables->nextPageUrl() }}" aria-label="Next"><span class="material-symbols-outlined text-[20px]">chevron_right</span></a>
@endif
</nav>
</div>
@else
<div class="p-4 border-t border-[#ABB3B7] bg-[#F8F9FA]">
<p class="text-xs text-[#586064] uppercase tracking-wide">
@if($receivables->total() > 0)
Showing all <span class="font-bold text-[#2B3437] tabular-nums">{{ $receivables->total() }}</span> results
@else
No results
@endif
</p>
</div>
@endif
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-6 pb-2">© {{ date('Y') }} Nexus ERP Inc.</p>
</div>
</div>
</main>
</body>
</html>
