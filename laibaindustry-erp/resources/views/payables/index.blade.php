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
<button class="md:hidden p-2 st-touch-target text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
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
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] lg:text-right shrink-0">One row per customer group · click row to manage payments</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

@if (session('success'))
<div class="border border-[#ABB3B7] bg-white px-4 py-3 text-sm text-[#2B3437]">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D]">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 border border-[#ABB3B7] bg-white">
<div class="p-5 md:p-6 border-b border-r-0 md:border-r border-[#ABB3B7] min-h-[140px] flex flex-col justify-center">
<p class="st-label mb-2">Total payable</p>
<p class="text-[34px] md:text-[42px] leading-none font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol }} {{ number_format($totals->total_amount ?? 0, 2) }}</p>
</div>
<div class="p-5 md:p-6 border-b border-[#ABB3B7] min-h-[140px] flex flex-col justify-center">
<p class="st-label mb-2">Amount paid</p>
<p class="text-[34px] md:text-[42px] leading-none font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol }} {{ number_format($totals->total_received ?? 0, 2) }}</p>
</div>
<div class="p-5 md:p-6 border-r-0 md:border-r border-[#ABB3B7] min-h-[140px] flex flex-col justify-center">
<p class="st-label mb-2">Sales offset</p>
<p class="text-[34px] md:text-[42px] leading-none font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol }} {{ number_format(($totals->total_received ?? 0) - ($totals->total_direct_paid ?? 0), 2) }}</p>
</div>
<div class="p-5 md:p-6 border-2 border-[#5E5E5E] max-md:m-0 -m-px min-h-[140px] flex flex-col justify-center">
<p class="st-label st-label--primary mb-2">Outstanding</p>
<p class="text-[34px] md:text-[42px] leading-none font-black font-mono text-[#5E5E5E] tabular-nums">{{ $currencySymbol }} {{ number_format($totals->total_outstanding ?? 0, 2) }}</p>
</div>
</div>

<div class="flex flex-wrap items-center gap-3 p-5 bg-[#F8F9FA] border border-[#ABB3B7]">
<div class="relative flex-1 min-w-[200px]">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#586064] material-symbols-outlined text-[18px] pointer-events-none">search</span>
<input class="st-input w-full h-10 pl-10 pr-3 text-sm" id="pay-search" type="text" placeholder="Customer name or code…" autocomplete="off">
</div>
<button type="button" id="pay-clear-btn" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2 hidden">
<span class="material-symbols-outlined text-[16px]">close</span>Clear
</button>
</div>

<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[320px]">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">Accounts payable groups</h3>
<p class="text-[11px] text-[#586064] mt-1">Grouped by customer code/name · one row per customer group</p>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[700px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3">Customer</th>
<th class="st-th px-4 py-3 whitespace-nowrap hidden sm:table-cell">Code</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Invoices</th>
<th class="st-th px-4 py-3 whitespace-nowrap hidden sm:table-cell">Latest invoice</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Bill</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap hidden sm:table-cell">Direct</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap hidden sm:table-cell">Offset</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap hidden sm:table-cell">Paid</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Balance</th>
<th class="st-th st-sticky-col st-thead-cell px-4 py-3 text-right w-44"></th>
</tr>
</thead>
<tbody id="pay-tbody">
@forelse($payableGroups as $g)
@php $groupUrl = route('payables.group', ['groupKey' => $g['group_key_encoded']]); @endphp
<tr class="st-tr cursor-pointer focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#5E5E5E]"
    data-payable-group-url="{{ $groupUrl }}"
    data-search-text="{{ mb_strtolower(($g['display_name'] ?? '').' '.($g['display_code'] ?? ''), 'UTF-8') }}"
    data-invoice-count="{{ $g['invoice_count'] }}"
    role="link" tabindex="0" aria-label="Open payables for {{ e($g['display_name']) }}">
<td class="st-td px-4 py-3 text-sm text-[#2B3437]"><p class="font-semibold">{{ $g['display_name'] }}</p></td>
<td class="st-td px-4 py-3 text-sm font-mono text-[#586064] hidden sm:table-cell">{{ $g['display_code'] !== '' ? $g['display_code'] : '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums text-[#2B3437]">{{ number_format($g['invoice_count']) }}</td>
<td class="st-td px-4 py-3 text-sm whitespace-nowrap font-mono text-[#586064] hidden sm:table-cell">{{ $g['latest_invoice_date'] ? format_display_date($g['latest_invoice_date']) : '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#2B3437]">{{ $currencySymbol }} {{ number_format((float) $g['total_bill'], 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#586064] hidden sm:table-cell">{{ $currencySymbol }} {{ number_format((float) ($g['total_direct_paid'] ?? 0), 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#586064] hidden sm:table-cell">{{ $currencySymbol }} {{ number_format((float) ($g['total_offset_paid'] ?? 0), 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#586064] hidden sm:table-cell">{{ $currencySymbol }} {{ number_format((float) $g['total_paid'], 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums {{ (float) $g['total_balance'] > 0.009 ? 'text-[#9F403D]' : 'text-[#5E5E5E]' }}">{{ $currencySymbol }} {{ number_format((float) $g['total_balance'], 2) }}</td>
<td class="st-td st-sticky-col px-4 py-3 text-right" data-stop-row-nav>
@if((float) $g['total_balance'] > 0.009 && auth()->user()->role !== 'viewer')
<a href="{{ $groupUrl }}" class="st-btn-primary h-9 px-3 st-touch-target text-[10px] inline-flex items-center gap-1.5 whitespace-nowrap"><span class="material-symbols-outlined text-[16px]">payments</span>Pay</a>
@elseif((float) $g['total_balance'] <= 0.009)
<span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wide text-[#5E5E5E] border border-[#ABB3B7] px-2 py-1"><span class="material-symbols-outlined text-[14px]">check</span>Paid</span>
@else
<span class="text-xs text-[#586064]">—</span>
@endif
</td>
</tr>
@empty
<tr id="pay-empty-db">
<td colspan="10" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No payables yet</p>
<p class="mb-3">Created when you save a purchase.</p>
<a href="{{ route('purchases.create') }}" class="text-[#5E5E5E] font-bold underline underline-offset-2">New purchase</a>
</td>
</tr>
@endforelse
<tr id="pay-no-results" style="display:none"><td colspan="10" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No customer groups match your search</p>
<p class="text-xs"><button type="button" id="pay-no-results-clear" class="font-bold text-[#5E5E5E] underline underline-offset-2">Clear search</button> to see all groups.</p>
</td></tr>
</tbody>
</table>
</div>

<div class="p-4 border-t border-[#ABB3B7] bg-[#F8F9FA]">
<p class="text-xs text-[#586064] uppercase tracking-wide" id="pay-footer-text" data-total="{{ $payableGroups->count() }}" data-invoices="{{ $totalInvoiceCount }}">
@if($payableGroups->count() > 0)
Showing <span class="font-bold text-[#2B3437] tabular-nums">{{ $payableGroups->count() }}</span> customer groups · <span class="font-bold text-[#2B3437] tabular-nums">{{ $totalInvoiceCount }}</span> invoices total
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
    var searchInput  = document.getElementById('pay-search');
    var tbody        = document.getElementById('pay-tbody');
    var rows         = tbody ? Array.from(tbody.querySelectorAll('tr[data-search-text]')) : [];
    var noResults    = document.getElementById('pay-no-results');
    var noResultsClr = document.getElementById('pay-no-results-clear');
    var clearBtn     = document.getElementById('pay-clear-btn');
    var footer       = document.getElementById('pay-footer-text');
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
            else if (visible > 0) footer.innerHTML = 'Showing <span class="font-bold text-[#2B3437] tabular-nums">' + visible + '</span> customer group' + (visible === 1 ? '' : 's') + ' · <span class="font-bold text-[#2B3437] tabular-nums">' + invoices + '</span> invoices total';
            else if (needle !== '') footer.textContent = 'No matches';
            else footer.textContent = 'No results';
        }
    }

    function clearSearch() { if (searchInput) { searchInput.value = ''; filterRows(''); searchInput.focus(); } }

    if (searchInput) searchInput.addEventListener('input', function () { filterRows(this.value); });
    if (clearBtn) clearBtn.addEventListener('click', clearSearch);
    if (noResultsClr) noResultsClr.addEventListener('click', clearSearch);

    rows.forEach(function (row) {
        var url = row.getAttribute('data-payable-group-url');
        if (!url) return;
        row.addEventListener('click', function (e) { if (e.target.closest('[data-stop-row-nav], a, button, form')) return; window.location.href = url; });
        row.addEventListener('keydown', function (e) { if (e.key !== 'Enter' && e.key !== ' ') return; if (e.target.closest('[data-stop-row-nav], a, button, input, textarea, select')) return; e.preventDefault(); window.location.href = url; });
    });
})();
</script>
</body>
</html>
