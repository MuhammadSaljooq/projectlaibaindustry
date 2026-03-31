<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'International payables - ERP'])
@include('partials.stitch-design')
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'international_payables'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">International payables</h2>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">International payables</h1>
</div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] lg:text-right shrink-0">Linked to international purchases · @if(auth()->user()->role !== 'viewer') click open balance to pay @else read-only @endif</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<div class="sm:hidden">
<p class="st-label">Module</p>
<p class="text-xl font-black uppercase tracking-tight text-[#2B3437]">International payables</p>
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
<p class="st-label mb-2">Total billed</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol }} {{ number_format($billTotal ?? 0, 2) }}</p>
</div>
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Amount paid</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol }} {{ number_format($paidTotal ?? 0, 2) }}</p>
</div>
<div class="p-6 border-2 border-[#5E5E5E] max-md:m-0 -m-px">
<p class="st-label st-label--primary mb-2">Outstanding</p>
<p class="text-2xl font-black font-mono text-[#5E5E5E] tabular-nums">{{ $currencySymbol }} {{ number_format($outstanding ?? 0, 2) }}</p>
</div>
</div>

<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[320px]">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">International purchase obligations</h3>
<p class="text-[11px] text-[#586064] mt-1">Each row is an international purchase line · bill vs payments recorded here</p>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[960px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3">Supplier</th>
<th class="st-th px-4 py-3">Product</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Bill</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Paid</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Balance</th>
<th class="st-th px-4 py-3 text-right w-44"></th>
</tr>
</thead>
<tbody>
@forelse($purchases as $p)
@php
    $paidRow = (float) ($p->payable_payments_sum_amount ?? 0);
    $billRow = (float) $p->total_amount;
    $bal = max(0, $billRow - $paidRow);
@endphp
<tr class="st-tr @if(auth()->user()->role !== 'viewer' && $bal > 0.009) cursor-pointer focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#5E5E5E] @endif"
    @if(auth()->user()->role !== 'viewer' && $bal > 0.009) data-intl-pay-url="{{ route('international-payables.pay', $p) }}" role="link" tabindex="0" aria-label="Record payment {{ e($p->product_name) }}" @endif>
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ $p->date->format('Y-m-d') }}</td>
<td class="st-td px-4 py-3 text-sm text-[#586064]">{{ $p->supplier?->name ?? '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-semibold text-[#2B3437]">{{ $p->product_name }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#2B3437]">{{ $currencySymbol }} {{ number_format($billRow, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums {{ $paidRow > 0 ? 'text-[#2B3437] font-semibold' : 'text-[#586064]' }}">{{ $currencySymbol }} {{ number_format($paidRow, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums {{ $bal > 0.009 ? 'text-[#9F403D]' : 'text-[#5E5E5E]' }}">{{ $currencySymbol }} {{ number_format($bal, 2) }}</td>
<td class="st-td px-4 py-3 text-right" data-stop-row-nav>
@if(auth()->user()->role !== 'viewer')
@if($bal > 0.009)
<a href="{{ route('international-payables.pay', $p) }}" class="st-btn-primary h-9 px-4 inline-flex items-center gap-2 text-[11px] whitespace-nowrap">
<span class="material-symbols-outlined text-[16px]">payments</span>
Pay
</a>
@else
<span class="text-[11px] font-bold uppercase tracking-wide text-[#5E5E5E]">Paid</span>
@endif
@else
<span class="text-xs text-[#586064]">—</span>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="7" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No international purchases yet</p>
<a href="{{ route('international-purchases.create') }}" class="text-[#5E5E5E] font-bold underline underline-offset-2">Add a purchase line first</a>
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

@if($purchases->hasPages())
<div class="p-4 border-t border-[#ABB3B7] flex flex-col sm:flex-row items-center justify-between gap-4 bg-[#F8F9FA]">
<p class="text-xs text-[#586064] uppercase tracking-wide">
Showing <span class="font-bold text-[#2B3437] tabular-nums">{{ $purchases->firstItem() }}</span>–<span class="font-bold text-[#2B3437] tabular-nums">{{ $purchases->lastItem() }}</span> of <span class="font-bold text-[#2B3437] tabular-nums">{{ $purchases->total() }}</span>
</p>
<nav class="flex items-stretch border border-[#ABB3B7] bg-white divide-x divide-[#ABB3B7]" aria-label="Pagination">
@if (!$purchases->onFirstPage())
<a class="p-2 text-[#586064] hover:bg-[#F1F4F6] inline-flex items-center justify-center" href="{{ $purchases->previousPageUrl() }}" aria-label="Previous"><span class="material-symbols-outlined text-[20px]">chevron_left</span></a>
@endif
@foreach ($purchases->getUrlRange(max(1, $purchases->currentPage() - 2), min($purchases->lastPage(), $purchases->currentPage() + 2)) ?: [1 => $purchases->url(1)] as $page => $url)
@if ($page == $purchases->currentPage())
<span class="px-3 py-2 text-xs font-bold uppercase tracking-wider bg-[#5E5E5E] text-[#F8F8F8] inline-flex items-center justify-center min-w-[2.5rem]">{{ $page }}</span>
@else
<a class="px-3 py-2 text-xs font-bold text-[#586064] hover:bg-[#F1F4F6] inline-flex items-center justify-center min-w-[2.5rem]" href="{{ $url }}">{{ $page }}</a>
@endif
@endforeach
@if ($purchases->hasMorePages())
<a class="p-2 text-[#586064] hover:bg-[#F1F4F6] inline-flex items-center justify-center" href="{{ $purchases->nextPageUrl() }}" aria-label="Next"><span class="material-symbols-outlined text-[20px]">chevron_right</span></a>
@endif
</nav>
</div>
@endif
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-6 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
@if(auth()->user()->role !== 'viewer')
<script>
(function () {
    document.querySelectorAll('tr[data-intl-pay-url]').forEach(function (row) {
        var url = row.getAttribute('data-intl-pay-url');
        if (!url) return;
        row.addEventListener('click', function (e) {
            if (e.target.closest('[data-stop-row-nav], a, button, form')) return;
            window.location.href = url;
        });
        row.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            if (e.target.closest('[data-stop-row-nav], a, button, input, textarea, select')) return;
            e.preventDefault();
            window.location.href = url;
        });
    });
})();
</script>
@endif
</body>
</html>
