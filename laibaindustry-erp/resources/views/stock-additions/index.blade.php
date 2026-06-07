<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Stock Additions - Laiba Safety'])
@include('partials.stitch-design')
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'stock_additions'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">Stock Additions</h2>
</div>
<div class="flex items-center gap-2">
<a id="sa-pdf-btn" href="{{ route('stock-additions.pdf') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}" data-base="{{ route('stock-additions.pdf') }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
PDF
</a>
<a id="sa-csv-btn" href="{{ route('stock-additions.export') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}" data-base="{{ route('stock-additions.export') }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[18px]">download</span>
CSV
</a>
<a href="{{ route('stock-additions.create') }}" class="st-btn-primary h-10 px-4 inline-flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[18px]">add</span>
Add Stock
</a>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Stock Additions</h1>
</div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] lg:text-right shrink-0">Inbound stock · one row per addition</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
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

<div id="sa-stats" class="grid grid-cols-1 md:grid-cols-3 gap-0 border border-[#ABB3B7] bg-white md:divide-x md:divide-[#ABB3B7]">
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Total additions</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ number_format($totals->total_lines ?? 0) }}</p>
</div>
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Total qty added</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ number_format($totals->total_qty ?? 0) }}</p>
</div>
<div class="p-6 border-2 border-[#5E5E5E] max-md:m-0 -m-px">
<p class="st-label st-label--primary mb-2">Total cost value</p>
<p class="text-2xl font-black font-mono text-[#5E5E5E] tabular-nums">{{ $currencySymbol }} {{ number_format($totals->total_cost_value ?? 0, 2) }}</p>
</div>
</div>

<form id="sa-form" method="GET" action="{{ route('stock-additions.index') }}" class="flex flex-wrap items-end gap-4 p-5 bg-[#F8F9FA] border border-[#ABB3B7]">
<div class="flex-1 min-w-[180px]">
<label class="st-label block mb-2" for="sa-search">Search</label>
<div class="relative">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#586064] material-symbols-outlined text-[18px] pointer-events-none">search</span>
<input class="st-input w-full h-10 pl-10 pr-3 text-sm" id="sa-search" type="text" name="search" value="{{ request('search') }}" placeholder="Product, article no., reference…" autocomplete="off">
</div>
</div>
<div class="min-w-[180px]">
<label class="st-label block mb-2" for="sa-product">Product</label>
<select class="st-select w-full h-10 pl-3 pr-12 text-sm cursor-pointer" id="sa-product" name="product_id">
<option value="">All products</option>
@foreach($products as $p)
<option value="{{ $p->id }}" {{ (string) request('product_id') === (string) $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
@endforeach
</select>
</div>
<div class="min-w-[130px]">
<label class="st-label block mb-2" for="sa-from">From</label>
<input class="st-input w-full h-10 px-3 text-sm font-mono" id="sa-from" type="text" name="from" value="{{ old('from', filter_date_input_value(request('from'))) }}" placeholder="dd/mm/yyyy" inputmode="numeric" autocomplete="off">
</div>
<div class="min-w-[130px]">
<label class="st-label block mb-2" for="sa-to">To</label>
<input class="st-input w-full h-10 px-3 text-sm font-mono" id="sa-to" type="text" name="to" value="{{ old('to', filter_date_input_value(request('to'))) }}" placeholder="dd/mm/yyyy" inputmode="numeric" autocomplete="off">
</div>
<div class="flex flex-wrap gap-2">
<button type="submit" class="st-btn-primary h-10 px-4 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[16px]">filter_list</span>
Filter
</button>
<a href="{{ route('stock-additions.index') }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2{{ request('search') || request('product_id') || request('from') || request('to') ? '' : ' hidden' }}" id="sa-clear-btn">
<span class="material-symbols-outlined text-[16px]">close</span>
Clear
</a>
</div>
</form>

<div id="sa-results" class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[320px]" style="transition:opacity .15s ease">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">Stock addition records</h3>
<p class="text-[11px] text-[#586064] mt-1">One row per stock addition · edits adjust product inventory automatically</p>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[900px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3">Product</th>
<th class="st-th px-4 py-3 whitespace-nowrap hidden sm:table-cell">Article no.</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Qty Added</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap hidden md:table-cell">Unit Cost</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Total Cost</th>
<th class="st-th px-4 py-3 whitespace-nowrap hidden lg:table-cell">Reference</th>
<th class="st-th px-4 py-3 whitespace-nowrap hidden lg:table-cell">Notes</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Actions</th>
</tr>
</thead>
<tbody>
@forelse($items as $item)
<tr class="st-tr">
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ $item->date?->format('d/m/Y') }}</td>
<td class="st-td px-4 py-3">
<div class="min-w-0">
<p class="text-sm font-bold text-[#2B3437] truncate">{{ $item->product?->name ?? 'Product #'.$item->product_id }}</p>
<p class="text-xs text-[#586064] mt-0.5 hidden sm:block">{{ optional($item->product?->category)->name ?? '—' }}</p>
</div>
</td>
<td class="st-td px-4 py-3 text-sm font-mono tabular-nums text-[#586064] hidden sm:table-cell">{{ $item->product?->sku ?? '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right tabular-nums text-[#2B3437]">+{{ number_format($item->quantity) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums text-[#586064] hidden md:table-cell">
{{ $item->unit_cost !== null ? $currencySymbol.' '.number_format($item->unit_cost, 2) : '—' }}
</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right tabular-nums text-[#5E5E5E]">
{{ $item->total_cost !== null ? $currencySymbol.' '.number_format($item->total_cost, 2) : '—' }}
</td>
<td class="st-td px-4 py-3 text-sm text-[#586064] hidden lg:table-cell max-w-[160px] truncate">{{ $item->reference ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm text-[#586064] hidden lg:table-cell max-w-[200px] truncate">{{ $item->notes ?: '—' }}</td>
<td class="st-td px-4 py-3 text-right whitespace-nowrap">
<div class="inline-flex items-center gap-2">
<a href="{{ route('stock-additions.edit', $item) }}" class="st-btn-secondary h-8 px-3 inline-flex items-center gap-1 text-xs">
<span class="material-symbols-outlined text-[15px]">edit</span>
Edit
</a>
<form method="POST" action="{{ route('stock-additions.destroy', $item) }}" onsubmit="return confirm('Delete this stock addition? This will reverse the inventory change.')">
@csrf
@method('DELETE')
<button type="submit" class="h-8 px-3 inline-flex items-center gap-1 text-xs border border-[#9F403D] text-[#9F403D] hover:bg-[#9F403D] hover:text-white transition-colors">
<span class="material-symbols-outlined text-[15px]">delete</span>
Delete
</button>
</form>
</div>
</td>
</tr>
@empty
<tr>
<td colspan="9" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No stock additions found</p>
@if(request('search') || request('product_id') || request('from') || request('to'))
<a href="{{ route('stock-additions.index') }}" class="text-[#5E5E5E] font-bold underline underline-offset-2">Clear filters</a>
@else
<a href="{{ route('stock-additions.create') }}" class="text-[#5E5E5E] font-bold underline underline-offset-2">Record your first stock addition</a>
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
Showing <span class="font-bold text-[#2B3437] tabular-nums">{{ $items->firstItem() }}–{{ $items->lastItem() }}</span> of <span class="font-bold text-[#2B3437] tabular-nums">{{ number_format($items->total()) }}</span> records
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
    var form        = document.getElementById('sa-form');
    var searchInput = document.getElementById('sa-search');
    var productSel  = document.getElementById('sa-product');
    var fromInput   = document.getElementById('sa-from');
    var toInput     = document.getElementById('sa-to');
    var clearBtn    = document.getElementById('sa-clear-btn');
    var pdfBtn      = document.getElementById('sa-pdf-btn');
    var csvBtn      = document.getElementById('sa-csv-btn');
    var statsEl     = document.getElementById('sa-stats');
    var resultsEl   = document.getElementById('sa-results');

    if (!form || !searchInput) return;

    var timer, ctrl;
    var reqId = 0;
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
                if (myId !== reqId) return;
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var ns  = doc.getElementById('sa-stats');
                var nr  = doc.getElementById('sa-results');
                if (statsEl && ns)   { statsEl.outerHTML   = ns.outerHTML;   statsEl   = document.getElementById('sa-stats'); }
                if (resultsEl && nr) { resultsEl.outerHTML = nr.outerHTML;   resultsEl = document.getElementById('sa-results'); }
            })
            .catch(function (e) { if (e.name !== 'AbortError') console.error(e); })
            .finally(function () {
                if (myId !== reqId) return;
                if (resultsEl) resultsEl.style.opacity = '';
            });
    }

    searchInput.addEventListener('input', function () {
        clearTimeout(timer);
        syncClear();
        timer = setTimeout(doFetch, DEBOUNCE);
    });

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

    [fromInput, toInput].forEach(function (el) {
        if (!el) return;
        el.addEventListener('change', function () {
            clearTimeout(timer);
            timer = setTimeout(doFetch, DEBOUNCE);
        });
    });

    productSel && productSel.addEventListener('change', function () {
        clearTimeout(timer);
        doFetch();
    });
})();
</script>
</body>
</html>
