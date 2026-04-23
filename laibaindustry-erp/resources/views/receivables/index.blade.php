<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Receivables - ERP'])
@include('partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'receivables'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 st-touch-target text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
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
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Receivables</h1>
</div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] lg:text-right shrink-0">Accounts receivable · by customer</p>
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

<div class="grid grid-cols-1 md:grid-cols-2 border border-[#ABB3B7] bg-white">
<div class="p-5 md:p-6 border-b border-r-0 md:border-r border-[#ABB3B7] min-h-[140px] flex flex-col justify-center">
<p class="st-label mb-2">Total Invoiced</p>
<p class="text-[34px] md:text-[42px] leading-none font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_amount ?? 0, 2) }}</p>
</div>
<div class="p-5 md:p-6 border-b border-[#ABB3B7] min-h-[140px] flex flex-col justify-center">
<p class="st-label mb-2">Total Received</p>
<p class="text-[34px] md:text-[42px] leading-none font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_received ?? 0, 2) }}</p>
</div>
<div class="p-5 md:p-6 border-r-0 md:border-r border-[#ABB3B7] min-h-[140px] flex flex-col justify-center">
<p class="st-label mb-2">Purchase Offset</p>
<p class="text-[34px] md:text-[42px] leading-none font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_purchase_offsets ?? 0, 2) }}</p>
</div>
<div class="p-5 md:p-6 border-2 border-[#5E5E5E] max-md:m-0 -m-px min-h-[140px] flex flex-col justify-center">
<p class="st-label mb-2 text-[#5E5E5E]">Outstanding</p>
<p class="text-[34px] md:text-[42px] leading-none font-black font-mono text-[#5E5E5E] tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_remaining ?? 0, 2) }}</p>
</div>
</div>

<form id="ar-form" method="GET" action="{{ route('receivables.index') }}" class="flex flex-wrap items-end gap-4 p-5 bg-[#F8F9FA] border border-[#ABB3B7] border-t-0">
<div class="flex-1 min-w-[200px]">
<label class="st-label block mb-2" for="ar-search">Search customers</label>
<div class="relative">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#586064] material-symbols-outlined text-[18px] pointer-events-none">search</span>
<input class="st-input w-full h-10 pl-10 pr-3 text-sm" id="ar-search" type="text" name="search" value="{{ $search ?? '' }}" placeholder="Customer name or code…" autocomplete="off">
</div>
</div>
<div class="flex flex-wrap gap-2">
<button type="submit" class="st-btn-primary h-10 px-4 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[16px]">search</span>
Search
</button>
<a href="{{ route('receivables.index') }}" id="ar-clear-btn" class="st-btn-secondary h-10 px-4 inline-flex items-center{{ ($search ?? '') !== '' ? '' : ' hidden' }}">Clear</a>
</div>
</form>

<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[320px] -mt-px">
<div class="px-5 py-4 border-b border-[#ABB3B7] flex flex-wrap items-center justify-between gap-3 bg-[#EAEFF1]">
<div>
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">Customers (aggregated)</h3>
<p class="text-[11px] text-[#586064] mt-1 max-w-xl">One row per customer (or same name without code). <span class="font-bold text-[#5E5E5E]">Click a row</span> to see invoices and open Manage on each.</p>
</div>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[680px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Customer</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Invoices</th>
<th class="st-th px-4 py-3 whitespace-nowrap hidden sm:table-cell">Latest invoice</th>
<th class="st-th px-4 py-3 whitespace-nowrap hidden sm:table-cell">Latest payment</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Total bill</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Received</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Remaining</th>
<th class="st-th px-4 py-3 text-right w-28 whitespace-nowrap">Status</th>
</tr>
</thead>
<tbody id="ar-tbody">
@forelse($receivableGroups as $g)
@php
    $remaining = (float) $g->total_amount - (float) $g->total_received;
    $groupUrl = route('receivables.group', ['groupKey' => \App\Models\Receivable::encodeGroupKeyForRoute($g->ar_group_key)]);
    $latestInv = $g->latest_invoice_date ? \Carbon\Carbon::parse($g->latest_invoice_date) : null;
    $latestPay = $g->latest_payment_at ? \Carbon\Carbon::parse($g->latest_payment_at) : null;
    $aggName = $g->agg_customer_name ?: '—';
    $aggCode = $g->agg_customer_code ? trim((string) $g->agg_customer_code) : '';
@endphp
<tr class="st-tr cursor-pointer hover:bg-[#F1F4F6] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#5E5E5E]"
data-receivable-group-url="{{ $groupUrl }}"
data-search-text="{{ mb_strtolower($aggName !== '—' ? $aggName : '', 'UTF-8') }} {{ mb_strtolower($aggCode, 'UTF-8') }}"
tabindex="0"
aria-label="Open receivables for {{ $aggName }}"
>
<td class="st-td px-4 py-3 text-sm text-[#2B3437]">
<p class="font-semibold">{{ $aggName }}</p>
@if ($aggCode !== '')
<p class="text-[11px] text-[#586064] font-mono mt-0.5">{{ $aggCode }}</p>
@endif
</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums text-[#2B3437]">{{ (int) $g->invoice_count }}</td>
<td class="st-td px-4 py-3 text-sm whitespace-nowrap font-mono text-[#586064] hidden sm:table-cell">{{ $latestInv ? format_display_date($latestInv) : '—' }}</td>
<td class="st-td px-4 py-3 text-sm whitespace-nowrap font-mono text-[#586064] hidden sm:table-cell">{{ $latestPay ? format_display_date($latestPay) : '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#2B3437]">{{ $currencySymbol ?? '$' }} {{ number_format((float) $g->total_amount, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#586064]">{{ $currencySymbol ?? '$' }} {{ number_format((float) $g->total_received, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums text-[#5E5E5E]">{{ $currencySymbol ?? '$' }} {{ number_format($remaining, 2) }}</td>
<td class="st-td px-4 py-3 text-right">
@if ($remaining <= 0)
<span class="text-[10px] font-bold uppercase tracking-wider text-[#586064] border border-[#ABB3B7] px-2 py-1 inline-block bg-[#F8F9FA]">Paid</span>
@else
<span class="text-[10px] font-bold uppercase tracking-wider text-[#5E5E5E]">Open</span>
@endif
</td>
</tr>
@empty
<tr id="ar-empty-db">
<td colspan="8" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No receivables yet</p>
<p class="text-xs">Receivables are created automatically when you record a sale.</p>
</td>
</tr>
@endforelse
<tr id="ar-no-results" style="display:none">
<td colspan="8" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No customers match your search</p>
<p class="text-xs">Try a different name or code, or <button type="button" id="ar-no-results-clear" class="font-bold text-[#5E5E5E] underline underline-offset-2">clear search</button> to see all.</p>
</td>
</tr>
</tbody>
</table>
</div>

<div class="p-4 border-t border-[#ABB3B7] bg-[#F8F9FA]">
<p class="text-xs text-[#586064] uppercase tracking-wide" id="ar-footer-text" data-total="{{ $totalGroupsCount }}">
@if($totalGroupsCount > 0)
Showing <span class="font-bold text-[#2B3437] tabular-nums">{{ $totalGroupsCount }}</span> customer{{ $totalGroupsCount === 1 ? '' : 's' }}
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
  var searchInput  = document.getElementById('ar-search');
  var arForm       = document.getElementById('ar-form');
  var tbody        = document.getElementById('ar-tbody');
  var rows         = tbody ? Array.from(tbody.querySelectorAll('tr[data-search-text]')) : [];
  var noResults    = document.getElementById('ar-no-results');
  var noResultsClr = document.getElementById('ar-no-results-clear');
  var listedLabel  = document.getElementById('ar-listed-label');
  var listedCount  = document.getElementById('ar-listed-count');
  var searchDisp   = document.getElementById('ar-search-display');
  var clearBtn     = document.getElementById('ar-clear-btn');
  var footer       = document.getElementById('ar-footer-text');
  var total        = parseInt((footer && footer.getAttribute('data-total')) || '0', 10);

  function esc(str) {
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function filterRows(query) {
    var needle  = query.trim().toLowerCase();
    var visible = 0;

    rows.forEach(function (row) {
      var match = needle === '' || (row.getAttribute('data-search-text') || '').indexOf(needle) !== -1;
      row.style.display = match ? '' : 'none';
      if (match) visible++;
    });

    if (noResults) noResults.style.display = (visible === 0 && needle !== '') ? '' : 'none';

    if (listedLabel) listedLabel.textContent = needle !== '' ? 'Matching search' : 'Listed';
    if (listedCount) listedCount.textContent = visible;
    if (searchDisp)  searchDisp.textContent  = needle !== '' ? query.trim() : '—';
    if (clearBtn)    clearBtn.classList.toggle('hidden', needle === '');

    if (footer) {
      if (visible > 0 && needle !== '') {
        footer.innerHTML = 'Showing <span class="font-bold text-[#2B3437] tabular-nums">' + visible + '</span> of <span class="font-bold text-[#2B3437] tabular-nums">' + total + '</span> customer' + (total === 1 ? '' : 's') + ' matching &ldquo;' + esc(query.trim()) + '&rdquo;';
      } else if (visible > 0) {
        footer.innerHTML = 'Showing <span class="font-bold text-[#2B3437] tabular-nums">' + visible + '</span> customer' + (visible === 1 ? '' : 's');
      } else if (needle !== '') {
        footer.textContent = 'No matches';
      } else {
        footer.textContent = 'No results';
      }
    }

    try {
      var url = new URL(window.location.href);
      needle !== '' ? url.searchParams.set('search', query.trim()) : url.searchParams.delete('search');
      history.replaceState(null, '', url.toString());
    } catch (e) {}
  }

  function clearSearch() {
    if (searchInput) { searchInput.value = ''; filterRows(''); searchInput.focus(); }
  }

  if (arForm) {
    arForm.addEventListener('submit', function (e) {
      e.preventDefault();
      if (searchInput) filterRows(searchInput.value);
    });
  }

  if (searchInput) {
    searchInput.addEventListener('input', function () { filterRows(this.value); });
  }

  if (clearBtn) {
    clearBtn.addEventListener('click', function (e) { e.preventDefault(); clearSearch(); });
  }

  if (noResultsClr) {
    noResultsClr.addEventListener('click', clearSearch);
  }

  // Apply initial filter (pre-filled from URL ?search param)
  if (searchInput && searchInput.value.trim() !== '') {
    filterRows(searchInput.value);
  }

  // Row click / keyboard navigation
  rows.forEach(function (tr) {
    var url = tr.getAttribute('data-receivable-group-url');
    if (!url) return;
    tr.addEventListener('click', function (e) {
      if (e.target.closest('a, button, input, select, textarea, label')) return;
      window.location.href = url;
    });
    tr.addEventListener('keydown', function (e) {
      if (e.key !== 'Enter' && e.key !== ' ') return;
      if (e.target.closest('a, button, input, select, textarea, label')) return;
      e.preventDefault();
      window.location.href = url;
    });
  });
})();
</script>
</body>
</html>
