<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Expenses - ERP'])
@include('partials.stitch-design')
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'expenses'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">Expenses</h2>
</div>
<div class="flex items-center gap-2 flex-wrap justify-end">
<a href="{{ route('expenses.export') }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[18px]">download</span>
CSV
</a>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('expenses.create') }}" class="st-btn-primary h-10 px-5 inline-flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[20px]">add</span>
New expense
</a>
@endif
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Expenses</h1>
</div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] lg:text-right shrink-0">Operating ledger · @if(auth()->user()->role !== 'viewer') click row to edit @else read-only @endif</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<div class="sm:hidden">
<p class="st-label">Module</p>
<p class="text-xl font-black uppercase tracking-tight text-[#2B3437]">Expenses</p>
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
<p class="st-label mb-2">Filtered total</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums" id="exp-filtered-total">{{ $currencySymbol }} {{ number_format($filteredTotal ?? 0, 2) }}</p>
</div>
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Matching entries</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums" id="exp-matching-count">{{ number_format($expenses->count()) }}</p>
</div>
<div class="p-6 border-2 border-[#5E5E5E] max-md:m-0 -m-px">
<p class="st-label st-label--primary mb-2">All-time total</p>
<p class="text-2xl font-black font-mono text-[#5E5E5E] tabular-nums">{{ $currencySymbol }} {{ number_format($totalAmount ?? 0, 2) }}</p>
</div>
</div>

<form id="exp-form" method="GET" action="{{ route('expenses.index') }}" class="flex flex-wrap items-end gap-4 p-5 bg-[#F8F9FA] border border-[#ABB3B7]">
<div class="flex-1 min-w-[200px]">
<label class="st-label block mb-2" for="e-search">Search</label>
<div class="relative">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#586064] material-symbols-outlined text-[18px] pointer-events-none">search</span>
<input class="st-input w-full h-10 pl-10 pr-3 text-sm" id="e-search" type="text" name="search" value="{{ request('search') }}" placeholder="Expense type…" autocomplete="off">
</div>
</div>
<div class="min-w-[140px]">
<label class="st-label block mb-2" for="e-from">From</label>
<input class="st-input w-full h-10 px-3 text-sm font-mono" id="e-from" type="text" name="from" value="{{ old('from', filter_date_input_value(request('from'))) }}" placeholder="dd/mm/yyyy" inputmode="numeric" autocomplete="off">
</div>
<div class="min-w-[140px]">
<label class="st-label block mb-2" for="e-to">To</label>
<input class="st-input w-full h-10 px-3 text-sm font-mono" id="e-to" type="text" name="to" value="{{ old('to', filter_date_input_value(request('to'))) }}" placeholder="dd/mm/yyyy" inputmode="numeric" autocomplete="off">
</div>
<div class="flex flex-wrap gap-2">
<button type="submit" class="st-btn-primary h-10 px-4 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[16px]">filter_list</span>
Filter
</button>
<a href="{{ route('expenses.index') }}" id="exp-clear-btn" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2{{ request('search') || request('from') || request('to') ? '' : ' hidden' }}">
<span class="material-symbols-outlined text-[16px]">close</span>
Clear
</a>
</div>
</form>

<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[320px]">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">Expense ledger</h3>
<p class="text-[11px] text-[#586064] mt-1">Date · type · amount · edit or delete from actions</p>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[640px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3">Type</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Amount</th>
@if(auth()->user()->role !== 'viewer')
<th class="st-th px-4 py-3 text-right w-36"></th>
@endif
</tr>
</thead>
<tbody id="exp-tbody">
@forelse($expenses as $expense)
<tr class="st-tr @if(auth()->user()->role !== 'viewer') cursor-pointer focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#5E5E5E] @endif"
    data-search-text="{{ mb_strtolower($expense->type ?? '', 'UTF-8') }}"
    data-amount="{{ $expense->amount }}"
    @if(auth()->user()->role !== 'viewer') data-expense-edit-url="{{ route('expenses.edit', $expense) }}" role="link" tabindex="0" aria-label="Edit expense {{ e($expense->type) }}" @endif>
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ format_display_date($expense->date) }}</td>
<td class="st-td px-4 py-3 text-sm font-semibold text-[#2B3437]">{{ $expense->type }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums text-[#5E5E5E]">{{ $currencySymbol }} {{ number_format($expense->amount, 2) }}</td>
@if(auth()->user()->role !== 'viewer')
<td class="st-td px-4 py-3 text-right" data-stop-row-nav>
<div class="flex items-center justify-end gap-1 flex-wrap">
<a href="{{ route('expenses.edit', $expense) }}" class="p-2 border border-transparent hover:border-[#ABB3B7] text-[#586064] hover:text-[#2B3437] hover:bg-[#F1F4F6]" title="Edit">
<span class="material-symbols-outlined text-[18px]">edit</span>
</a>
<form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="inline-flex" data-confirm-delete="{{ e('Delete this expense?') }}">
@csrf
@method('DELETE')
<button type="submit" class="p-2 border border-transparent hover:border-[#9F403D] text-[#586064] hover:text-[#9F403D] hover:bg-[#F1F4F6]" title="Delete">
<span class="material-symbols-outlined text-[18px]">delete</span>
</button>
</form>
</div>
</td>
@endif
</tr>
@empty
<tr id="exp-empty-db">
<td colspan="{{ auth()->user()->role === 'viewer' ? 3 : 4 }}" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No expenses yet</p>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('expenses.create') }}" class="text-[#5E5E5E] font-bold underline underline-offset-2">Add first expense</a>
@else
<span>No expenses recorded.</span>
@endif
</td>
</tr>
@endforelse
<tr id="exp-no-results" style="display:none">
<td colspan="{{ auth()->user()->role === 'viewer' ? 3 : 4 }}" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No expenses match your search</p>
<p class="text-xs">Try a different term, or <button type="button" id="exp-no-results-clear" class="font-bold text-[#5E5E5E] underline underline-offset-2">clear search</button> to see all.</p>
</td>
</tr>
</tbody>
</table>
</div>

<div class="p-4 border-t border-[#ABB3B7] bg-[#F8F9FA]">
<p class="text-xs text-[#586064] uppercase tracking-wide" id="exp-footer-text" data-total="{{ $expenses->count() }}" data-currency="{{ $currencySymbol }}">
@if($expenses->count() > 0)
Showing <span class="font-bold text-[#2B3437] tabular-nums">{{ $expenses->count() }}</span> expenses
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
    var searchInput   = document.getElementById('e-search');
    var tbody         = document.getElementById('exp-tbody');
    var rows          = tbody ? Array.from(tbody.querySelectorAll('tr[data-search-text]')) : [];
    var noResults     = document.getElementById('exp-no-results');
    var noResultsClr  = document.getElementById('exp-no-results-clear');
    var clearBtn      = document.getElementById('exp-clear-btn');
    var footer        = document.getElementById('exp-footer-text');
    var total         = parseInt((footer && footer.getAttribute('data-total')) || '0', 10);
    var sym           = (footer && footer.getAttribute('data-currency')) || '';
    var filteredTotal = document.getElementById('exp-filtered-total');
    var matchingCount = document.getElementById('exp-matching-count');
    var hasDateFilter = !!(new URLSearchParams(window.location.search).get('from') || new URLSearchParams(window.location.search).get('to'));

    function fmt(n) { return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function esc(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    function filterRows(query) {
        var needle = query.trim().toLowerCase(), visible = 0, sum = 0;
        rows.forEach(function (row) {
            var show = needle === '' || (row.getAttribute('data-search-text') || '').indexOf(needle) !== -1;
            row.style.display = show ? '' : 'none';
            if (show) { visible++; sum += parseFloat(row.getAttribute('data-amount') || '0'); }
        });
        if (noResults) noResults.style.display = (visible === 0 && needle !== '') ? '' : 'none';
        if (filteredTotal) filteredTotal.textContent = sym + ' ' + fmt(sum);
        if (matchingCount) matchingCount.textContent = visible;
        if (clearBtn) clearBtn.classList.toggle('hidden', needle === '' && !hasDateFilter);
        if (footer) {
            if (visible > 0 && needle !== '') footer.innerHTML = 'Showing <span class="font-bold text-[#2B3437] tabular-nums">' + visible + '</span> of <span class="font-bold text-[#2B3437] tabular-nums">' + total + '</span> expenses matching &ldquo;' + esc(query.trim()) + '&rdquo;';
            else if (visible > 0) footer.innerHTML = 'Showing <span class="font-bold text-[#2B3437] tabular-nums">' + visible + '</span> expense' + (visible === 1 ? '' : 's');
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

    rows.forEach(function (row) {
        var url = row.getAttribute('data-expense-edit-url');
        if (!url) return;
        row.addEventListener('click', function (e) { if (e.target.closest('[data-stop-row-nav], a, button, form')) return; window.location.href = url; });
        row.addEventListener('keydown', function (e) { if (e.key !== 'Enter' && e.key !== ' ') return; if (e.target.closest('[data-stop-row-nav], a, button, input, textarea, select')) return; e.preventDefault(); window.location.href = url; });
    });
})();
</script>
</body>
</html>
