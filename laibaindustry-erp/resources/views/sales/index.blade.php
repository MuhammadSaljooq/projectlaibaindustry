<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Sales - ERP'])
@include('partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'sales'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">Sales</h2>
</div>
<div class="flex items-center gap-2 flex-wrap justify-end">
<a href="{{ route('sales.export') }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[18px]">download</span>
CSV
</a>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('sales.create') }}" class="st-btn-primary h-10 px-5 inline-flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[20px]">add</span>
New sale
</a>
@endif
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Sales</h1>
</div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] lg:text-right shrink-0">One row per invoice · click row to open</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<div class="sm:hidden">
<p class="st-label">Module</p>
<p class="text-xl font-black uppercase tracking-tight text-[#2B3437]">Sales</p>
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
@if (session('warning'))
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D]">
{{ session('warning') }}
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-0 border border-[#ABB3B7] bg-white md:divide-x md:divide-[#ABB3B7]">
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Subtotal</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_subtotal ?? 0, 2) }}</p>
</div>
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">VAT (15%)</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_vat ?? 0, 2) }}</p>
</div>
<div class="p-6 border-2 border-[#5E5E5E] max-md:m-0 -m-px">
<p class="st-label st-label--primary mb-2">Total sales</p>
<p class="text-2xl font-black font-mono text-[#5E5E5E] tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_sales ?? 0, 2) }}</p>
</div>
</div>

<form method="GET" action="{{ route('sales.index') }}" class="flex flex-wrap items-end gap-4 p-5 bg-[#F8F9FA] border border-[#ABB3B7]">
<div class="flex-1 min-w-[200px]">
<label class="st-label block mb-2" for="s-search">Search</label>
<div class="relative">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#586064] material-symbols-outlined text-[18px] pointer-events-none">search</span>
<input class="st-input w-full h-10 pl-10 pr-3 text-sm" id="s-search" type="text" name="search" value="{{ request('search') }}" placeholder="Invoice, customer, product…">
</div>
</div>
<div class="min-w-[140px]">
<label class="st-label block mb-2" for="s-from">From</label>
<input class="st-input w-full h-10 px-3 text-sm" id="s-from" type="date" name="from" value="{{ request('from') }}">
</div>
<div class="min-w-[140px]">
<label class="st-label block mb-2" for="s-to">To</label>
<input class="st-input w-full h-10 px-3 text-sm" id="s-to" type="date" name="to" value="{{ request('to') }}">
</div>
<div class="flex flex-wrap gap-2">
<button type="submit" class="st-btn-primary h-10 px-4 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[16px]">filter_list</span>
Filter
</button>
@if(request('search') || request('from') || request('to'))
<a href="{{ route('sales.index') }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[16px]">close</span>
Clear
</a>
@endif
</div>
</form>

<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[320px]">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">Sales invoices</h3>
<p class="text-[11px] text-[#586064] mt-1">One row per sale · line details on the sale page</p>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[900px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3 whitespace-nowrap max-w-[160px]">Customer</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Invoice</th>
<th class="st-th px-4 py-3 min-w-[200px]">Lines</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Subtotal</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">VAT</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Total</th>
<th class="st-th px-4 py-3 text-right w-28"></th>
</tr>
</thead>
<tbody>
@forelse($sales as $sale)
@php
$rowSymbol = $sale->currency && $sale->currency->symbol ? $sale->currency->symbol : ($currencySymbol ?? '$');
$lineCount = $sale->items->count();
$firstItem = $sale->items->first();
$firstName = $firstItem ? ($firstItem->product?->name ?: 'Product #'.$firstItem->product_id) : '—';
if ($lineCount === 0) {
    $linesSummary = 'No line items';
} elseif ($lineCount === 1) {
    $linesSummary = $firstName;
} else {
    $linesSummary = $firstName.', +'.($lineCount - 1).' more';
}
$showLabel = 'View sale '.($sale->invoice_number ?: '#'.$sale->id);
@endphp
<tr class="st-tr cursor-pointer focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#5E5E5E]"
    data-sale-show-url="{{ route('sales.show', $sale) }}" role="link" tabindex="0" aria-label="{{ e($showLabel) }}">
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ $sale->date->format('Y-m-d') }}</td>
<td class="st-td px-4 py-3 text-sm font-semibold text-[#2B3437] truncate max-w-[160px]">{{ $sale->customer_name ?: ($sale->customer_code ?: '—') }}</td>
<td class="st-td px-4 py-3 text-sm font-bold text-[#2B3437]">
<a href="{{ route('sales.show', $sale) }}" class="text-[#5E5E5E] hover:underline" onclick="event.stopPropagation()">{{ $sale->invoice_number ?: '#' . $sale->id }}</a>
</td>
<td class="st-td px-4 py-3 text-sm text-[#586064] max-w-[280px]" title="{{ e($linesSummary) }}">
<span class="line-clamp-2">{{ $linesSummary }}</span>
@if($lineCount > 1)
<span class="block text-[10px] uppercase tracking-wide text-[#586064] mt-0.5">{{ $lineCount }} products</span>
@endif
</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#2B3437]">{{ $rowSymbol }} {{ number_format($sale->subtotal ?? 0, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#586064]">{{ $rowSymbol }} {{ number_format($sale->tax_amount ?? 0, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums text-[#5E5E5E]">{{ $rowSymbol }} {{ number_format($sale->total_amount ?? 0, 2) }}</td>
<td class="st-td px-4 py-3 text-right" data-stop-row-nav>
<div class="flex items-center justify-end gap-1 flex-wrap">
<a href="{{ route('sales.show', $sale) }}" class="p-2 border border-transparent hover:border-[#ABB3B7] text-[#586064] hover:text-[#2B3437] hover:bg-[#F1F4F6]" title="View">
<span class="material-symbols-outlined text-[18px]">visibility</span>
</a>
@if(auth()->user()->role !== 'viewer')
<form method="POST" action="{{ route('sales.destroy', $sale) }}" class="inline-flex" onsubmit="return confirm('Delete this sale? Stock will be restored.');">
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
<p class="font-semibold text-[#2B3437] mb-1">No sales recorded yet</p>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('sales.create') }}" class="text-[#5E5E5E] font-bold underline underline-offset-2">Create first sale</a>
@endif
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

@if($sales->hasPages())
<div class="p-4 border-t border-[#ABB3B7] flex flex-col sm:flex-row items-center justify-between gap-4 bg-[#F8F9FA]">
<p class="text-xs text-[#586064] uppercase tracking-wide">
Showing <span class="font-bold text-[#2B3437] tabular-nums">{{ $sales->firstItem() }}</span>–<span class="font-bold text-[#2B3437] tabular-nums">{{ $sales->lastItem() }}</span> of <span class="font-bold text-[#2B3437] tabular-nums">{{ $sales->total() }}</span> sales
</p>
<nav class="flex items-stretch border border-[#ABB3B7] bg-white divide-x divide-[#ABB3B7]" aria-label="Pagination">
@if (!$sales->onFirstPage())
<a class="p-2 text-[#586064] hover:bg-[#F1F4F6] inline-flex items-center justify-center" href="{{ $sales->previousPageUrl() }}" aria-label="Previous"><span class="material-symbols-outlined text-[20px]">chevron_left</span></a>
@endif
@foreach ($sales->getUrlRange(max(1, $sales->currentPage() - 2), min($sales->lastPage(), $sales->currentPage() + 2)) ?: [1 => $sales->url(1)] as $page => $url)
@if ($page == $sales->currentPage())
<span class="px-3 py-2 text-xs font-bold uppercase tracking-wider bg-[#5E5E5E] text-[#F8F8F8] inline-flex items-center justify-center min-w-[2.5rem]">{{ $page }}</span>
@else
<a class="px-3 py-2 text-xs font-bold text-[#586064] hover:bg-[#F1F4F6] inline-flex items-center justify-center min-w-[2.5rem]" href="{{ $url }}">{{ $page }}</a>
@endif
@endforeach
@if ($sales->hasMorePages())
<a class="p-2 text-[#586064] hover:bg-[#F1F4F6] inline-flex items-center justify-center" href="{{ $sales->nextPageUrl() }}" aria-label="Next"><span class="material-symbols-outlined text-[20px]">chevron_right</span></a>
@endif
</nav>
</div>
@else
<div class="p-4 border-t border-[#ABB3B7] bg-[#F8F9FA]">
<p class="text-xs text-[#586064] uppercase tracking-wide">
@if($sales->total() > 0)
Showing all <span class="font-bold text-[#2B3437] tabular-nums">{{ $sales->total() }}</span> sales
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
<script>
(function () {
    document.querySelectorAll('tr[data-sale-show-url]').forEach(function (row) {
        var url = row.getAttribute('data-sale-show-url');
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
</body>
</html>
