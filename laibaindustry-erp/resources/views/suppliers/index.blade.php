<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Vendors - ERP'])
@include('partials.stitch-design')
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'suppliers'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">Vendors</h2>
</div>
@if(auth()->user()->role !== 'viewer')
<div class="flex items-center gap-2 flex-wrap justify-end">
<a href="{{ route('suppliers.create') }}" class="st-btn-primary h-10 px-5 inline-flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[20px]">add</span>
New vendor
</a>
</div>
@endif
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Vendors</h1>
</div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] lg:text-right shrink-0">Vendors · international purchase links · @if(auth()->user()->role !== 'viewer') click row to edit @else read-only @endif</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<div class="sm:hidden">
<p class="st-label">Module</p>
<p class="text-xl font-black uppercase tracking-tight text-[#2B3437]">Vendors</p>
</div>

@if (session('success'))
<div class="border border-[#ABB3B7] bg-white px-4 py-3 text-sm text-[#2B3437]">
{{ session('success') }}
</div>
@endif

{{-- Stats strip --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-0 border border-[#ABB3B7] bg-white md:divide-x md:divide-[#ABB3B7]">
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Total vendors</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ number_format($totalSuppliersCount) }}</p>
</div>
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2" id="sup-listed-label">{{ ($search !== '' || $country !== '') ? 'Matching filter' : 'Listed' }}</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums" id="sup-listed-count">{{ $suppliers->count() }}</p>
</div>
<div class="p-6 border-2 border-[#5E5E5E] max-md:m-0 -m-px">
<p class="st-label st-label--primary mb-2">Search</p>
<p class="text-sm font-mono text-[#5E5E5E] break-all" id="sup-search-display">{{ $search !== '' ? $search : ($country !== '' ? $country : '—') }}</p>
</div>
</div>

{{-- Search & filter bar --}}
<form id="sup-form" method="GET" action="{{ route('suppliers.index') }}" class="flex flex-wrap items-end gap-4 p-5 bg-[#F8F9FA] border border-[#ABB3B7]">
<div class="flex-1 min-w-[200px]">
<label class="st-label block mb-2" for="sup-search">Search</label>
<div class="relative">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#586064] material-symbols-outlined text-[18px] pointer-events-none">search</span>
<input class="st-input w-full h-10 pl-10 pr-3 text-sm" id="sup-search" type="text" name="search" value="{{ $search }}" placeholder="Name, contact, email, phone…" autocomplete="off">
</div>
</div>
@if($countries->isNotEmpty())
<div class="min-w-[160px]">
<label class="st-label block mb-2" for="s-country">Country</label>
<div class="relative">
<select class="st-select w-full h-10 pl-3 pr-12 text-sm" id="s-country" name="country">
<option value="">All countries</option>
@foreach($countries as $c)
<option value="{{ $c }}" @selected($country === $c)>{{ $c }}</option>
@endforeach
</select>
</div>
</div>
@endif
<div class="flex flex-wrap gap-2">
<button type="submit" class="st-btn-primary h-10 px-4 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[16px]">filter_list</span>
Filter
</button>
<a href="{{ route('suppliers.index') }}" id="sup-clear-btn" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2{{ ($search !== '' || $country !== '') ? '' : ' hidden' }}">
<span class="material-symbols-outlined text-[16px]">close</span>
Clear
</a>
</div>
</form>

<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[320px]">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">Vendor directory</h3>
<p class="text-[11px] text-[#586064] mt-1">Name · country · contact · phone · email · balance owed · ledger, edit, and delete from actions column</p>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[920px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3">Name</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Country</th>
<th class="st-th px-4 py-3">Contact</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Phone</th>
<th class="st-th px-4 py-3">Email</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Balance owed</th>
<th class="st-th px-4 py-3 text-right w-44">Actions</th>
</tr>
</thead>
<tbody id="sup-tbody">
@forelse($suppliers as $supplier)
@php $rowBalance = (float) ($balances[$supplier->id] ?? 0); @endphp
<tr class="st-tr @if(auth()->user()->role !== 'viewer') cursor-pointer focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#5E5E5E] @endif"
    data-search-text="{{ mb_strtolower(($supplier->name ?? '').' '.($supplier->contact_name ?? '').' '.($supplier->email ?? '').' '.($supplier->phone ?? ''), 'UTF-8') }}"
    @if(auth()->user()->role !== 'viewer') data-supplier-edit-url="{{ route('suppliers.edit', $supplier) }}" role="link" tabindex="0" aria-label="Edit {{ e($supplier->name) }}" @endif>
<td class="st-td px-4 py-3 text-sm font-semibold text-[#2B3437]">{{ $supplier->name }}</td>
<td class="st-td px-4 py-3 text-sm text-[#586064]">{{ $supplier->country ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm text-[#586064]">{{ $supplier->contact_name ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm text-[#586064] whitespace-nowrap">{{ $supplier->phone ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm text-[#586064]">{{ $supplier->email ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums {{ $rowBalance > 0.009 ? 'text-[#9F403D]' : 'text-[#5E5E5E]' }}">{{ $currencySymbol }} {{ number_format($rowBalance, 2) }}</td>
<td class="st-td px-4 py-3 text-right" data-stop-row-nav>
<div class="flex items-center justify-end gap-1 flex-wrap">
<a href="{{ route('suppliers.ledger', $supplier) }}" class="p-2 border border-transparent hover:border-[#ABB3B7] text-[#586064] hover:text-[#2B3437] hover:bg-[#F1F4F6]" title="Ledger">
<span class="material-symbols-outlined text-[18px]">receipt_long</span>
</a>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('suppliers.edit', $supplier) }}" class="p-2 border border-transparent hover:border-[#ABB3B7] text-[#586064] hover:text-[#2B3437] hover:bg-[#F1F4F6]" title="Edit">
<span class="material-symbols-outlined text-[18px]">edit</span>
</a>
<form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="inline-flex" data-confirm-delete="{{ e('Delete this vendor? Linked international purchases will keep the line but lose the vendor link.') }}">
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
<tr id="sup-empty-db">
<td colspan="7" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No vendors yet</p>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('suppliers.create') }}" class="text-[#5E5E5E] font-bold underline underline-offset-2">Add first vendor</a>
@else
<span>No vendors on file.</span>
@endif
</td>
</tr>
@endforelse
<tr id="sup-no-results" style="display:none">
<td colspan="7" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No vendors match your search</p>
<p class="text-xs">Try a different term, or <button type="button" id="sup-no-results-clear" class="font-bold text-[#5E5E5E] underline underline-offset-2">clear search</button> to see all.</p>
</td>
</tr>
</tbody>
</table>
</div>

<div class="p-4 border-t border-[#ABB3B7] bg-[#F8F9FA]">
<p class="text-xs text-[#586064] uppercase tracking-wide" id="sup-footer-text" data-total="{{ $suppliers->count() }}">
@if($suppliers->count() > 0)
Showing <span class="font-bold text-[#2B3437] tabular-nums">{{ $suppliers->count() }}</span> vendor{{ $suppliers->count() === 1 ? '' : 's' }}
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
    var searchInput  = document.getElementById('sup-search');
    var tbody        = document.getElementById('sup-tbody');
    var rows         = tbody ? Array.from(tbody.querySelectorAll('tr[data-search-text]')) : [];
    var noResults    = document.getElementById('sup-no-results');
    var noResultsClr = document.getElementById('sup-no-results-clear');
    var listedLabel  = document.getElementById('sup-listed-label');
    var listedCount  = document.getElementById('sup-listed-count');
    var searchDisp   = document.getElementById('sup-search-display');
    var clearBtn     = document.getElementById('sup-clear-btn');
    var footer       = document.getElementById('sup-footer-text');
    var total        = parseInt((footer && footer.getAttribute('data-total')) || '0', 10);
    var hasCountry   = !!(new URLSearchParams(window.location.search).get('country'));

    function esc(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    function filterRows(query) {
        var needle = query.trim().toLowerCase(), visible = 0;
        rows.forEach(function (row) {
            var show = needle === '' || (row.getAttribute('data-search-text') || '').indexOf(needle) !== -1;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (noResults) noResults.style.display = (visible === 0 && needle !== '') ? '' : 'none';
        if (listedLabel) listedLabel.textContent = (needle !== '' || hasCountry) ? 'Matching filter' : 'Listed';
        if (listedCount) listedCount.textContent = visible;
        if (searchDisp)  searchDisp.textContent  = needle !== '' ? query.trim() : (hasCountry ? new URLSearchParams(window.location.search).get('country') : '—');
        if (clearBtn) clearBtn.classList.toggle('hidden', needle === '' && !hasCountry);
        if (footer) {
            if (visible > 0 && needle !== '') footer.innerHTML = 'Showing <span class="font-bold text-[#2B3437] tabular-nums">' + visible + '</span> of <span class="font-bold text-[#2B3437] tabular-nums">' + total + '</span> vendor' + (total === 1 ? '' : 's') + ' matching &ldquo;' + esc(query.trim()) + '&rdquo;';
            else if (visible > 0) footer.innerHTML = 'Showing <span class="font-bold text-[#2B3437] tabular-nums">' + visible + '</span> vendor' + (visible === 1 ? '' : 's');
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
    if (clearBtn) clearBtn.addEventListener('click', function (e) { if (!hasCountry) { e.preventDefault(); clearSearch(); } });
    if (noResultsClr) noResultsClr.addEventListener('click', clearSearch);
    if (searchInput && searchInput.value.trim() !== '') filterRows(searchInput.value);

    rows.forEach(function (row) {
        var url = row.getAttribute('data-supplier-edit-url');
        if (!url) return;
        row.addEventListener('click', function (e) { if (e.target.closest('[data-stop-row-nav], a, button, form')) return; window.location.href = url; });
        row.addEventListener('keydown', function (e) { if (e.key !== 'Enter' && e.key !== ' ') return; if (e.target.closest('[data-stop-row-nav], a, button, input, textarea, select')) return; e.preventDefault(); window.location.href = url; });
    });
})();
</script>
</body>
</html>
