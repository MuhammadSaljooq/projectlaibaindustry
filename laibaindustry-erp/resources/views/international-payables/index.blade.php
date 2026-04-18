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
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] lg:text-right shrink-0">One row per vendor name · click row to open invoices</p>
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

<div class="flex flex-wrap items-center gap-3 p-5 bg-[#F8F9FA] border border-[#ABB3B7]">
<div class="relative flex-1 min-w-[200px]">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#586064] material-symbols-outlined text-[18px] pointer-events-none">search</span>
<input class="st-input w-full h-10 pl-10 pr-3 text-sm" id="intlpay-search" type="text" placeholder="Vendor name…" autocomplete="off">
</div>
<button type="button" id="intlpay-clear-btn" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2 hidden">
<span class="material-symbols-outlined text-[16px]">close</span>Clear
</button>
</div>

<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[320px]">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">International payable groups</h3>
<p class="text-[11px] text-[#586064] mt-1">Grouped by vendor name · click to manage invoice payments</p>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[900px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3">Vendor</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Invoices</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Latest invoice</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Bill</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Paid</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Balance</th>
<th class="st-th px-4 py-3 text-right w-28 whitespace-nowrap">Status</th>
</tr>
</thead>
<tbody id="intlpay-tbody">
@forelse($orderGroups as $g)
@php $groupUrl = route('international-payables.group', ['groupKey' => $g['group_key_encoded']]); @endphp
<tr class="st-tr cursor-pointer focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#5E5E5E]"
    data-intl-pay-group-url="{{ $groupUrl }}"
    data-search-text="{{ mb_strtolower($g['display_name'] ?? '', 'UTF-8') }}"
    data-invoice-count="{{ $g['invoice_count'] }}"
    role="link" tabindex="0" aria-label="Open international payables for {{ e($g['display_name']) }}">
<td class="st-td px-4 py-3 text-sm text-[#2B3437]"><p class="font-semibold">{{ $g['display_name'] }}</p></td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums text-[#2B3437]">{{ number_format($g['invoice_count']) }}</td>
<td class="st-td px-4 py-3 text-sm whitespace-nowrap font-mono text-[#586064]">{{ $g['latest_invoice_date'] ? format_display_date($g['latest_invoice_date']) : '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#2B3437]">{{ $currencySymbol }} {{ number_format((float) $g['total_bill'], 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#586064]">{{ $currencySymbol }} {{ number_format((float) $g['total_paid'], 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums {{ (float) $g['total_balance'] > 0.009 ? 'text-[#9F403D]' : 'text-[#5E5E5E]' }}">{{ $currencySymbol }} {{ number_format((float) $g['total_balance'], 2) }}</td>
<td class="st-td px-4 py-3 text-right">
@if((float) $g['total_balance'] > 0.009)
<span class="text-[10px] font-bold uppercase tracking-wider text-[#5E5E5E]">Open</span>
@else
<span class="text-[10px] font-bold uppercase tracking-wider text-[#586064] border border-[#ABB3B7] px-2 py-1 inline-block bg-[#F8F9FA]">Paid</span>
@endif
</td>
</tr>
@empty
<tr id="intlpay-empty-db">
<td colspan="7" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No international purchases yet</p>
<a href="{{ route('international-purchases.create') }}" class="text-[#5E5E5E] font-bold underline underline-offset-2">Add a purchase first</a>
</td>
</tr>
@endforelse
<tr id="intlpay-no-results" style="display:none"><td colspan="7" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No vendor groups match your search</p>
<p class="text-xs"><button type="button" id="intlpay-no-results-clear" class="font-bold text-[#5E5E5E] underline underline-offset-2">Clear search</button> to see all groups.</p>
</td></tr>
</tbody>
</table>
</div>

<div class="p-4 border-t border-[#ABB3B7] bg-[#F8F9FA]">
<p class="text-xs text-[#586064] uppercase tracking-wide" id="intlpay-footer-text" data-total="{{ $orderGroups->count() }}" data-invoices="{{ $totalInvoiceCount }}">
@if($orderGroups->count() > 0)
Showing <span class="font-bold text-[#2B3437] tabular-nums">{{ $orderGroups->count() }}</span> vendor groups · <span class="font-bold text-[#2B3437] tabular-nums">{{ $totalInvoiceCount }}</span> invoices total
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
    var searchInput  = document.getElementById('intlpay-search');
    var tbody        = document.getElementById('intlpay-tbody');
    var rows         = tbody ? Array.from(tbody.querySelectorAll('tr[data-search-text]')) : [];
    var noResults    = document.getElementById('intlpay-no-results');
    var noResultsClr = document.getElementById('intlpay-no-results-clear');
    var clearBtn     = document.getElementById('intlpay-clear-btn');
    var footer       = document.getElementById('intlpay-footer-text');
    var total        = parseInt((footer && footer.getAttribute('data-total')) || '0', 10);

    function esc(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    function filterRows(query) {
        var needle = query.trim().toLowerCase(), visible = 0, invoices = 0;
        rows.forEach(function (row) {
            var show = needle === '' || (row.getAttribute('data-search-text') || '').indexOf(needle) !== -1;
            row.style.display = show ? '' : 'none';
            if (show) { visible++; invoices += parseInt(row.getAttribute('data-invoice-count') || '0', 10); }
        });
        if (noResults) noResults.style.display = (visible === 0 && needle !== '') ? '' : 'none';
        if (clearBtn) clearBtn.classList.toggle('hidden', needle === '');
        if (footer) {
            if (visible > 0 && needle !== '') footer.innerHTML = 'Showing <span class="font-bold text-[#2B3437] tabular-nums">' + visible + '</span> of <span class="font-bold text-[#2B3437] tabular-nums">' + total + '</span> groups matching &ldquo;' + esc(query.trim()) + '&rdquo; · <span class="font-bold text-[#2B3437] tabular-nums">' + invoices + '</span> invoices';
            else if (visible > 0) footer.innerHTML = 'Showing <span class="font-bold text-[#2B3437] tabular-nums">' + visible + '</span> vendor group' + (visible === 1 ? '' : 's') + ' · <span class="font-bold text-[#2B3437] tabular-nums">' + invoices + '</span> invoices total';
            else if (needle !== '') footer.textContent = 'No matches';
            else footer.textContent = 'No results';
        }
    }

    function clearSearch() { if (searchInput) { searchInput.value = ''; filterRows(''); searchInput.focus(); } }

    if (searchInput) searchInput.addEventListener('input', function () { filterRows(this.value); });
    if (clearBtn) clearBtn.addEventListener('click', clearSearch);
    if (noResultsClr) noResultsClr.addEventListener('click', clearSearch);

    rows.forEach(function (row) {
        var url = row.getAttribute('data-intl-pay-group-url');
        if (!url) return;
        row.addEventListener('click', function (e) { if (e.target.closest('[data-stop-row-nav], a, button, form')) return; window.location.href = url; });
        row.addEventListener('keydown', function (e) { if (e.key !== 'Enter' && e.key !== ' ') return; if (e.target.closest('[data-stop-row-nav], a, button, input, textarea, select')) return; e.preventDefault(); window.location.href = url; });
    });
})();
</script>
</body>
</html>
