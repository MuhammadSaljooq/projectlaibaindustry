<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'VAT - ERP'])
@include('partials.stitch-design')
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'vat'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">VAT</h2>
</div>
<div class="flex items-center gap-2 flex-wrap justify-end">
<a href="{{ route('vat.export') }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[18px]">download</span>
CSV export
</a>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Value added tax</h1>
</div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] lg:text-right shrink-0">Output · input · net · current filters</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<div class="sm:hidden">
<p class="st-label">Module</p>
<p class="text-xl font-black uppercase tracking-tight text-[#2B3437]">VAT</p>
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

<div class="grid grid-cols-1 md:grid-cols-3 gap-0 border border-[#ABB3B7] bg-white md:divide-x md:divide-[#ABB3B7]">
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Output VAT <span class="font-normal normal-case text-[#586064]">(filtered)</span></p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums" id="vat-output-vat">{{ $currencySymbol }} {{ number_format($filteredSalesVat ?? 0, 2) }}</p>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] mt-2">Sales</p>
</div>
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Input VAT <span class="font-normal normal-case text-[#586064]">(filtered)</span></p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums" id="vat-input-vat">{{ $currencySymbol }} {{ number_format($filteredPurchaseVat ?? 0, 2) }}</p>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] mt-2">Purchases</p>
</div>
<div class="p-6 border-2 border-[#5E5E5E] max-md:m-0 -m-px">
<p class="st-label st-label--primary mb-2">Net VAT <span class="font-normal normal-case text-[#586064]">(filtered)</span></p>
<p class="text-2xl font-black font-mono tabular-nums {{ ($filteredNetVat ?? 0) < 0 ? 'text-[#9F403D]' : 'text-[#5E5E5E]' }}" id="vat-net-vat">{{ $currencySymbol }} {{ number_format($filteredNetVat ?? 0, 2) }}</p>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] mt-2" id="vat-net-label">{{ ($filteredNetVat ?? 0) >= 0 ? 'Payable' : 'Refundable' }}</p>
</div>
</div>

<p class="text-[11px] text-[#586064] border border-[#ABB3B7] bg-[#F8F9FA] px-4 py-3">
<span class="font-bold text-[#2B3437] uppercase tracking-wide">All-time ·</span>
Output {{ $currencySymbol }} {{ number_format($totals->sales_vat ?? 0, 2) }}
· Input {{ $currencySymbol }} {{ number_format($totals->purchase_vat ?? 0, 2) }}
· Net <span class="font-mono font-bold {{ ($totals->net_vat ?? 0) < 0 ? 'text-[#9F403D]' : 'text-[#2B3437]' }}">{{ $currencySymbol }} {{ number_format($totals->net_vat ?? 0, 2) }}</span>
</p>

<form id="vat-form" method="GET" action="{{ route('vat.index') }}" class="flex flex-wrap items-end gap-4 p-5 bg-[#F8F9FA] border border-[#ABB3B7]">
<div class="flex-1 min-w-[200px]">
<label class="st-label block mb-2" for="v-search">Search</label>
<div class="relative">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#586064] material-symbols-outlined text-[18px] pointer-events-none">search</span>
<input class="st-input w-full h-10 pl-10 pr-3 text-sm" id="v-search" type="text" name="search" value="{{ request('search') }}" placeholder="Invoice, customer, code…" autocomplete="off">
</div>
</div>
<div class="min-w-[140px]">
<label class="st-label block mb-2" for="v-from">From</label>
<input class="st-input w-full h-10 px-3 text-sm font-mono" id="v-from" type="text" name="from" value="{{ old('from', filter_date_input_value(request('from'))) }}" placeholder="dd/mm/yyyy" inputmode="numeric" autocomplete="off">
</div>
<div class="min-w-[140px]">
<label class="st-label block mb-2" for="v-to">To</label>
<input class="st-input w-full h-10 px-3 text-sm font-mono" id="v-to" type="text" name="to" value="{{ old('to', filter_date_input_value(request('to'))) }}" placeholder="dd/mm/yyyy" inputmode="numeric" autocomplete="off">
</div>
<div class="flex flex-wrap gap-2">
<button type="submit" class="st-btn-primary h-10 px-4 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[16px]">filter_list</span>
Filter
</button>
<a href="{{ route('vat.index') }}" id="vat-clear-btn" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2{{ request('search') || request('from') || request('to') ? '' : ' hidden' }}">
<span class="material-symbols-outlined text-[16px]">close</span>
Clear
</a>
</div>
</form>

<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[320px]">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">VAT entry ledger</h3>
<p class="text-[11px] text-[#586064] mt-1">Mirrored from sales and purchases · export CSV from header</p>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[900px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Type</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Invoice</th>
<th class="st-th px-4 py-3">Customer / vendor</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Subtotal</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">VAT %</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">VAT amount</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap w-28">Actions</th>
</tr>
</thead>
<tbody id="vat-tbody">
@forelse($entries as $entry)
<tr class="st-tr"
    data-search-text="{{ mb_strtolower(($entry->invoice_number ?? '').' '.($entry->customer_name ?? '').' '.($entry->customer_code ?? '').' '.($entry->type ?? ''), 'UTF-8') }}"
    data-vat-amount="{{ $entry->vat_amount }}"
    data-entry-type="{{ $entry->type }}">
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ format_display_date($entry->date) }}</td>
<td class="st-td px-4 py-3">
@if($entry->type === 'sale')
<span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wide px-2 py-1 border border-[#5E5E5E] text-[#5E5E5E]">
<span class="material-symbols-outlined text-[14px]">payments</span>
Sale
</span>
@else
<span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wide px-2 py-1 border border-[#ABB3B7] text-[#586064]">
<span class="material-symbols-outlined text-[14px]">shopping_cart</span>
Purchase
</span>
@endif
</td>
<td class="st-td px-4 py-3 text-sm font-semibold text-[#2B3437]">{{ $entry->invoice_number ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm text-[#586064]">{{ $entry->customer_name ?: $entry->customer_code ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#2B3437]">{{ $currencySymbol }} {{ number_format($entry->subtotal, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#586064]">{{ number_format($entry->vat_rate, 2) }}%</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums {{ $entry->type === 'sale' ? 'text-[#5E5E5E]' : 'text-[#586064]' }}">{{ $currencySymbol }} {{ number_format($entry->vat_amount, 2) }}</td>
<td class="st-td px-4 py-3 text-right whitespace-nowrap">
@if(auth()->user()->role !== 'viewer')
<form method="post" action="{{ route('vat.destroy', $entry) }}" class="inline" data-confirm-delete="{{ e('Remove this VAT ledger row? The related sale or purchase will not be deleted.') }}">
@csrf
@method('DELETE')
@if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
@if(request('from'))<input type="hidden" name="from" value="{{ request('from') }}">@endif
@if(request('to'))<input type="hidden" name="to" value="{{ request('to') }}">@endif
<button type="submit" class="text-[11px] font-bold uppercase tracking-wider text-[#9F403D] border border-[#9F403D] px-2 py-1 hover:bg-[#F1F4F6]">Delete</button>
</form>
@else
<span class="text-xs text-[#586064]">—</span>
@endif
</td>
</tr>
@empty
<tr id="vat-empty-db">
<td colspan="8" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No VAT entries</p>
<p class="max-w-md mx-auto">Entries are created when you record sales and purchases.</p>
</td>
</tr>
@endforelse
<tr id="vat-no-results" style="display:none">
<td colspan="8" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No entries match your search</p>
<p class="text-xs">Try a different term, or <button type="button" id="vat-no-results-clear" class="font-bold text-[#5E5E5E] underline underline-offset-2">clear search</button> to see all.</p>
</td>
</tr>
</tbody>
</table>
</div>

<div class="p-4 border-t border-[#ABB3B7] bg-[#F8F9FA]">
<p class="text-xs text-[#586064] uppercase tracking-wide" id="vat-footer-text" data-total="{{ $entries->count() }}" data-currency="{{ $currencySymbol }}">
@if($entries->count() > 0)
Showing <span class="font-bold text-[#2B3437] tabular-nums">{{ $entries->count() }}</span> VAT entries
@else
No results
@endif
</p>
</div>
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-6 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
<script>
(function () {
    var searchInput  = document.getElementById('v-search');
    var tbody        = document.getElementById('vat-tbody');
    var rows         = tbody ? Array.from(tbody.querySelectorAll('tr[data-search-text]')) : [];
    var noResults    = document.getElementById('vat-no-results');
    var noResultsClr = document.getElementById('vat-no-results-clear');
    var clearBtn     = document.getElementById('vat-clear-btn');
    var footer       = document.getElementById('vat-footer-text');
    var total        = parseInt((footer && footer.getAttribute('data-total')) || '0', 10);
    var sym          = (footer && footer.getAttribute('data-currency')) || '';
    var outputVat    = document.getElementById('vat-output-vat');
    var inputVat     = document.getElementById('vat-input-vat');
    var netVat       = document.getElementById('vat-net-vat');
    var netLabel     = document.getElementById('vat-net-label');
    var hasDateFilter = !!(new URLSearchParams(window.location.search).get('from') || new URLSearchParams(window.location.search).get('to'));

    function fmt(n) { return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function esc(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    function filterRows(query) {
        var needle = query.trim().toLowerCase(), visible = 0, salesVat = 0, purchaseVat = 0;
        rows.forEach(function (row) {
            var show = needle === '' || (row.getAttribute('data-search-text') || '').indexOf(needle) !== -1;
            row.style.display = show ? '' : 'none';
            if (show) {
                visible++;
                var amt = parseFloat(row.getAttribute('data-vat-amount') || '0');
                if (row.getAttribute('data-entry-type') === 'sale') salesVat += amt;
                else purchaseVat += amt;
            }
        });
        var net = salesVat - purchaseVat;
        if (noResults) noResults.style.display = (visible === 0 && needle !== '') ? '' : 'none';
        if (outputVat) outputVat.textContent = sym + ' ' + fmt(salesVat);
        if (inputVat)  inputVat.textContent  = sym + ' ' + fmt(purchaseVat);
        if (netVat) {
            netVat.textContent = sym + ' ' + fmt(net);
            netVat.className = netVat.className.replace(/text-\[#[0-9A-Fa-f]+\]/g, '') + (net < 0 ? ' text-[#9F403D]' : ' text-[#5E5E5E]');
        }
        if (netLabel) netLabel.textContent = net >= 0 ? 'Payable' : 'Refundable';
        if (clearBtn) clearBtn.classList.toggle('hidden', needle === '' && !hasDateFilter);
        if (footer) {
            if (visible > 0 && needle !== '') footer.innerHTML = 'Showing <span class="font-bold text-[#2B3437] tabular-nums">' + visible + '</span> of <span class="font-bold text-[#2B3437] tabular-nums">' + total + '</span> VAT entries matching &ldquo;' + esc(query.trim()) + '&rdquo;';
            else if (visible > 0) footer.innerHTML = 'Showing <span class="font-bold text-[#2B3437] tabular-nums">' + visible + '</span> VAT entr' + (visible === 1 ? 'y' : 'ies');
            else if (needle !== '') footer.textContent = 'No matches';
            else footer.textContent = 'No results';
        }
        try { var url = new URL(window.location.href); needle !== '' ? url.searchParams.set('search', query.trim()) : url.searchParams.delete('search'); history.replaceState(null, '', url.toString()); } catch (e) {}
    }

    function clearSearch() { if (searchInput) { searchInput.value = ''; filterRows(''); searchInput.focus(); } }

    if (searchInput) {
        searchInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); filterRows(this.value); } });
        searchInput.addEventListener('input', function () { filterRows(this.value); });
    }
    if (clearBtn) clearBtn.addEventListener('click', function (e) { if (!hasDateFilter) { e.preventDefault(); clearSearch(); } });
    if (noResultsClr) noResultsClr.addEventListener('click', clearSearch);
    if (searchInput && searchInput.value.trim() !== '') filterRows(searchInput.value);
})();
</script>
</body>
</html>
