<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Payables - ERP'])
@include('partials.stitch-design')
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'payables'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">Payables</h2>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Payables</h1>
</div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] lg:text-right shrink-0">Supplier AP · @if(auth()->user()->role !== 'viewer') click open balance to pay @else read-only @endif</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<div class="sm:hidden">
<p class="st-label">Module</p>
<p class="text-xl font-black uppercase tracking-tight text-[#2B3437]">Payables</p>
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
<p class="st-label mb-2">Total payable</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol }} {{ number_format($totals->total_amount ?? 0, 2) }}</p>
</div>
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Amount paid</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol }} {{ number_format($totals->total_received ?? 0, 2) }}</p>
</div>
<div class="p-6 border-2 border-[#5E5E5E] max-md:m-0 -m-px">
<p class="st-label st-label--primary mb-2">Outstanding</p>
<p class="text-2xl font-black font-mono text-[#5E5E5E] tabular-nums">{{ $currencySymbol }} {{ number_format($totals->total_outstanding ?? 0, 2) }}</p>
</div>
</div>

<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[320px]">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">Accounts payable ledger</h3>
<p class="text-[11px] text-[#586064] mt-1">From purchases · record payment or delete (admin/manager)</p>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[960px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Invoice</th>
<th class="st-th px-4 py-3">Customer name</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Code</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Bill</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Paid</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Balance</th>
<th class="st-th px-4 py-3 text-right w-44"></th>
</tr>
</thead>
<tbody>
@forelse($payables as $p)
@php $balance = (float) $p->amount - (float) $p->received; @endphp
<tr class="st-tr @if(auth()->user()->role !== 'viewer' && $balance > 0) cursor-pointer focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#5E5E5E] @endif"
    @if(auth()->user()->role !== 'viewer' && $balance > 0) data-payable-edit-url="{{ route('payables.edit', $p) }}" role="link" tabindex="0" aria-label="Record payment {{ e($p->invoice_number ?: '#' . $p->id) }}" @endif>
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ format_display_date($p->date) }}</td>
<td class="st-td px-4 py-3 text-sm font-semibold text-[#2B3437]">{{ $p->invoice_number ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm text-[#586064] truncate max-w-[200px]">{{ $p->customer_name ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-[#586064]">{{ $p->customer_code ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#2B3437]">{{ $currencySymbol }} {{ number_format($p->amount, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums {{ (float) $p->received > 0 ? 'text-[#2B3437] font-semibold' : 'text-[#586064]' }}">{{ $currencySymbol }} {{ number_format($p->received, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums {{ $balance > 0 ? 'text-[#9F403D]' : 'text-[#5E5E5E]' }}">{{ $currencySymbol }} {{ number_format($balance, 2) }}</td>
<td class="st-td px-4 py-3 text-right" data-stop-row-nav>
<div class="inline-flex items-center justify-end gap-1 flex-wrap">
@if($balance > 0)
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('payables.edit', $p) }}" class="st-btn-primary h-9 px-3 text-[10px] inline-flex items-center gap-1.5 whitespace-nowrap">
<span class="material-symbols-outlined text-[16px]">payments</span>
Pay
</a>
@else
<span class="text-xs text-[#586064]">—</span>
@endif
@else
<span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wide text-[#5E5E5E] border border-[#ABB3B7] px-2 py-1">
<span class="material-symbols-outlined text-[14px]">check</span>
Paid
</span>
@endif
@if(in_array(auth()->user()->role, ['admin', 'manager']))
<form method="POST" action="{{ route('payables.destroy', $p) }}" class="inline-flex" data-confirm-delete="{{ e('Delete this payable? This cannot be undone.') }}">
@csrf
@method('DELETE')
<button type="submit" class="p-2 border border-transparent hover:border-[#9F403D] text-[#586064] hover:text-[#9F403D] hover:bg-[#F1F4F6]" title="Delete">
<span class="material-symbols-outlined text-[18px]">delete</span>
</button>
</form>
@endif
</div>
</td>
</tr>
@empty
<tr>
<td colspan="8" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No payables yet</p>
<p class="mb-3">Created when you save a purchase.</p>
<a href="{{ route('purchases.create') }}" class="text-[#5E5E5E] font-bold underline underline-offset-2">New purchase</a>
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

@if($payables->hasPages())
<div class="p-4 border-t border-[#ABB3B7] flex flex-col sm:flex-row items-center justify-between gap-4 bg-[#F8F9FA]">
<p class="text-xs text-[#586064] uppercase tracking-wide">
Showing <span class="font-bold text-[#2B3437] tabular-nums">{{ $payables->firstItem() }}</span>–<span class="font-bold text-[#2B3437] tabular-nums">{{ $payables->lastItem() }}</span> of <span class="font-bold text-[#2B3437] tabular-nums">{{ $payables->total() }}</span>
</p>
<nav class="flex items-stretch border border-[#ABB3B7] bg-white divide-x divide-[#ABB3B7]" aria-label="Pagination">
@if (!$payables->onFirstPage())
<a class="p-2 text-[#586064] hover:bg-[#F1F4F6] inline-flex items-center justify-center" href="{{ $payables->previousPageUrl() }}" aria-label="Previous"><span class="material-symbols-outlined text-[20px]">chevron_left</span></a>
@endif
@foreach ($payables->getUrlRange(max(1, $payables->currentPage() - 2), min($payables->lastPage(), $payables->currentPage() + 2)) ?: [1 => $payables->url(1)] as $page => $url)
@if ($page == $payables->currentPage())
<span class="px-3 py-2 text-xs font-bold uppercase tracking-wider bg-[#5E5E5E] text-[#F8F8F8] inline-flex items-center justify-center min-w-[2.5rem]">{{ $page }}</span>
@else
<a class="px-3 py-2 text-xs font-bold text-[#586064] hover:bg-[#F1F4F6] inline-flex items-center justify-center min-w-[2.5rem]" href="{{ $url }}">{{ $page }}</a>
@endif
@endforeach
@if ($payables->hasMorePages())
<a class="p-2 text-[#586064] hover:bg-[#F1F4F6] inline-flex items-center justify-center" href="{{ $payables->nextPageUrl() }}" aria-label="Next"><span class="material-symbols-outlined text-[20px]">chevron_right</span></a>
@endif
</nav>
</div>
@else
<div class="p-4 border-t border-[#ABB3B7] bg-[#F8F9FA]">
<p class="text-xs text-[#586064] uppercase tracking-wide">
@if($payables->total() > 0)
Showing all <span class="font-bold text-[#2B3437] tabular-nums">{{ $payables->total() }}</span> results
@else
No results
@endif
</p>
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
    document.querySelectorAll('tr[data-payable-edit-url]').forEach(function (row) {
        var url = row.getAttribute('data-payable-edit-url');
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
