<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Inventory History - Laiba Safety'])
@include('partials.stitch-design')
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'products'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">Inventory History</h2>
</div>
<div class="flex items-center gap-2">
<a id="ih-pdf-btn" href="{{ route('inventory.history.pdf') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}" data-base="{{ route('inventory.history.pdf') }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
PDF
</a>
<a id="ih-csv-btn" href="{{ route('inventory.history.export') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}" data-base="{{ route('inventory.history.export') }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[18px]">download</span>
CSV
</a>
<a href="{{ route('inventory.dashboard') }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
Inventory
</a>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Inventory History</h1>
</div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] lg:text-right shrink-0">Sales history · one row per line item</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<div class="sm:hidden">
<p class="st-label">Module</p>
<p class="text-xl font-black uppercase tracking-tight text-[#2B3437]">Inventory History</p>
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
@if ($errors->any())
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D] space-y-1">
<p class="font-semibold">Could not apply date filter</p>
<ul class="list-disc list-inside text-[13px]">
@foreach ($errors->all() as $err)
<li>{{ $err }}</li>
@endforeach
</ul>
</div>
@endif

<div id="ih-stats" class="grid grid-cols-1 md:grid-cols-3 gap-0 border border-[#ABB3B7] bg-white md:divide-x md:divide-[#ABB3B7]">
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Total lines</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ number_format($totals->total_lines ?? 0) }}</p>
</div>
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Total qty sold</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ number_format($totals->total_qty ?? 0) }}</p>
</div>
<div class="p-6 border-2 border-[#5E5E5E] max-md:m-0 -m-px">
<p class="st-label st-label--primary mb-2">Total revenue</p>
<p class="text-2xl font-black font-mono text-[#5E5E5E] tabular-nums">{{ $currencySymbol }} {{ number_format($totals->total_revenue ?? 0, 2) }}</p>
</div>
</div>

<form id="ih-form" method="GET" action="{{ route('inventory.history') }}" class="flex flex-wrap items-end gap-4 p-5 bg-[#F8F9FA] border border-[#ABB3B7]">
<div class="flex-1 min-w-[180px]">
<label class="st-label block mb-2" for="ih-search">Search</label>
<div class="relative">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#586064] material-symbols-outlined text-[18px] pointer-events-none">search</span>
<input class="st-input w-full h-10 pl-10 pr-3 text-sm" id="ih-search" type="text" name="search" value="{{ request('search') }}" placeholder="Product, article no., customer…" autocomplete="off">
</div>
</div>
<div class="min-w-[180px]">
<label class="st-label block mb-2" for="ih-product">Product</label>
<select class="st-select w-full h-10 pl-3 pr-12 text-sm cursor-pointer" id="ih-product" name="product_id">
<option value="">All products</option>
@foreach($products as $p)
<option value="{{ $p->id }}" {{ (string) request('product_id') === (string) $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
@endforeach
</select>
</div>
<div class="min-w-[130px]">
<label class="st-label block mb-2" for="ih-from">From</label>
<input class="st-input w-full h-10 px-3 text-sm font-mono" id="ih-from" type="text" name="from" value="{{ old('from', filter_date_input_value(request('from'))) }}" placeholder="dd/mm/yyyy" inputmode="numeric" autocomplete="off">
</div>
<div class="min-w-[130px]">
<label class="st-label block mb-2" for="ih-to">To</label>
<input class="st-input w-full h-10 px-3 text-sm font-mono" id="ih-to" type="text" name="to" value="{{ old('to', filter_date_input_value(request('to'))) }}" placeholder="dd/mm/yyyy" inputmode="numeric" autocomplete="off">
</div>
<div class="flex flex-wrap gap-2">
<button type="submit" class="st-btn-primary h-10 px-4 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[16px]">filter_list</span>
Filter
</button>
<a href="{{ route('inventory.history') }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2{{ request('search') || request('product_id') || request('from') || request('to') ? '' : ' hidden' }}" id="ih-clear-btn">
<span class="material-symbols-outlined text-[16px]">close</span>
Clear
</a>
</div>
</form>

<div id="ih-results" class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[320px]" style="transition:opacity .15s ease">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">Sales line items</h3>
<p class="text-[11px] text-[#586064] mt-1">One row per product per invoice · click invoice to open sale</p>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[900px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3">Product</th>
<th class="st-th px-4 py-3 whitespace-nowrap hidden sm:table-cell">Article no.</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Customer</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Qty</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap hidden md:table-cell">Unit price</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Line total</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap hidden lg:table-cell">Stock (current)</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Invoice</th>
</tr>
</thead>
<tbody>
@forelse($items as $item)
@php
$customer = $item->sale?->customer_name ?? $item->sale?->customer_code ?? 'Walk-in';
$lineTotal = $item->quantity * $item->selling_price;
$ihSearchText = mb_strtolower(
    ($item->product?->name ?? '') . ' ' .
    ($item->product?->sku ?? '') . ' ' .
    $customer . ' ' .
    ($item->sale?->customer_code ?? '') . ' ' .
    ($item->sale?->invoice_number ?? ''),
    'UTF-8'
);
@endphp
<tr class="st-tr" data-ih-search-text="{{ $ihSearchText }}">
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ format_display_datetime($item->sale?->date) }}</td>
<td class="st-td px-4 py-3">
<div class="min-w-0">
<p class="text-sm font-bold text-[#2B3437] truncate">{{ $item->product?->name ?? 'Product #'.$item->product_id }}</p>
<p class="text-xs text-[#586064] mt-0.5 hidden sm:block">{{ optional($item->product?->category)->name ?? '—' }}</p>
</div>
</td>
<td class="st-td px-4 py-3 text-sm font-mono tabular-nums text-[#586064] hidden sm:table-cell">{{ $item->product?->sku ?? '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-semibold text-[#2B3437] truncate max-w-[160px]">{{ $customer }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right tabular-nums text-[#2B3437]">{{ $item->quantity }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums text-[#586064] hidden md:table-cell">{{ $currencySymbol }} {{ number_format($item->selling_price, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right tabular-nums text-[#5E5E5E]">{{ $currencySymbol }} {{ number_format($lineTotal, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right tabular-nums hidden lg:table-cell {{ ($item->product?->stock_quantity ?? 0) <= ($item->product?->reorder_level ?? 0) ? 'text-[#9F403D]' : 'text-[#2B3437]' }}">
{{ $item->product ? number_format($item->product->stock_quantity) : '—' }}
</td>
<td class="st-td px-4 py-3 text-sm">
@if($item->sale)
<a href="{{ route('sales.show', $item->sale) }}" class="font-bold text-[#5E5E5E] hover:underline underline-offset-2">
{{ $item->sale->invoice_number ?: '#'.$item->sale->id }}
</a>
@else
<span class="text-[#586064]">—</span>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="9" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No sales history found</p>
@if(request('search') || request('product_id') || request('customer') || request('from') || request('to'))
<a href="{{ route('inventory.history') }}" class="text-[#5E5E5E] font-bold underline underline-offset-2">Clear filters</a>
@endif
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

<div class="p-4 border-t border-[#ABB3B7] bg-[#F8F9FA] flex items-center justify-between gap-4 flex-wrap">
<p class="text-xs text-[#586064] uppercase tracking-wide">
@if($items->total() > 0)
Showing <span class="font-bold text-[#2B3437] tabular-nums">{{ $items->firstItem() }}–{{ $items->lastItem() }}</span> of <span class="font-bold text-[#2B3437] tabular-nums">{{ number_format($items->total()) }}</span> lines
@else
No results
@endif
</p>
@if($items->hasPages())
<div class="text-sm">{{ $items->links() }}</div>
@endif
</div>
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-6 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
<script>
(function () {
    var form        = document.getElementById('ih-form');
    var searchInput = document.getElementById('ih-search');
    var productSel  = document.getElementById('ih-product');
    var fromInput   = document.getElementById('ih-from');
    var toInput     = document.getElementById('ih-to');
    var clearBtn    = document.getElementById('ih-clear-btn');
    var pdfBtn      = document.getElementById('ih-pdf-btn');
    var csvBtn      = document.getElementById('ih-csv-btn');
    var statsEl     = document.getElementById('ih-stats');
    var resultsEl   = document.getElementById('ih-results');

    if (!form || !searchInput) return;

    var timer, ctrl;
    var reqId = 0; // monotonic counter — lets finally() know if it's the latest request

    var DEBOUNCE = 350;

    function syncClear() {
        if (!clearBtn) return;
        var active = searchInput.value.trim() !== '' ||
                     (productSel && productSel.value !== '') ||
                     (fromInput && fromInput.value.trim() !== '') ||
                     (toInput   && toInput.value.trim()   !== '');
        clearBtn.classList.toggle('hidden', !active);
    }

    function updateExportLinks(qs) {
        if (pdfBtn) pdfBtn.href = pdfBtn.dataset.base + (qs ? '?' + qs : '');
        if (csvBtn) csvBtn.href = csvBtn.dataset.base + (qs ? '?' + qs : '');
    }

    function doFetch() {
        if (ctrl) ctrl.abort();
        ctrl = new AbortController();

        var myId = ++reqId;

        var params = new URLSearchParams(new FormData(form)).toString();
        var url    = form.action + (params ? '?' + params : '');

        history.replaceState(null, '', url);
        syncClear();
        updateExportLinks(params);

        if (resultsEl) resultsEl.style.opacity = '0.4';

        fetch(url, { signal: ctrl.signal, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.text(); })
            .then(function (html) {
                if (myId !== reqId) return; // a newer request already fired — discard this response
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var ns  = doc.getElementById('ih-stats');
                var nr  = doc.getElementById('ih-results');
                if (statsEl && ns)   { statsEl.outerHTML   = ns.outerHTML;   statsEl   = document.getElementById('ih-stats'); }
                if (resultsEl && nr) { resultsEl.outerHTML = nr.outerHTML;   resultsEl = document.getElementById('ih-results'); }
            })
            .catch(function (e) { if (e.name !== 'AbortError') console.error(e); })
            .finally(function () {
                // Only restore opacity for the most recent request to avoid clearing
                // the dim while a newer request is still loading.
                if (myId !== reqId) return;
                if (resultsEl) resultsEl.style.opacity = '';
            });
    }

    // ── Search: debounced live fetch on every keystroke
    searchInput.addEventListener('input', function () {
        clearTimeout(timer);
        syncClear();
        timer = setTimeout(doFetch, DEBOUNCE);
    });

    // ── Prevent Enter from doing a full form submit in any filter input
    [searchInput, fromInput, toInput].forEach(function (el) {
        if (!el) return;
        el.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(timer);
                doFetch();
            }
        });
    });

    // ── Date fields: fetch when the user finishes editing (on blur after change)
    [fromInput, toInput].forEach(function (el) {
        if (!el) return;
        el.addEventListener('change', function () {
            clearTimeout(timer);
            timer = setTimeout(doFetch, DEBOUNCE);
        });
    });

    // ── Product dropdown: fetch immediately on selection
    productSel && productSel.addEventListener('change', function () {
        clearTimeout(timer);
        doFetch();
    });
})();
</script>
</body>
</html>
