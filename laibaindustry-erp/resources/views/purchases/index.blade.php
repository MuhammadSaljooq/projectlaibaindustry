<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Purchases - ERP'])
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
@include('purchases.partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'purchases'])
<main class="purchases-stitch flex-1 flex flex-col h-full overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">Purchases</h2>
</div>
</header>
<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

{{-- Technical header: section id + headline + 2px primary rule (DESIGN.md) --}}
<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Purchases</h1>
</div>
<div class="flex flex-wrap items-center gap-3 shrink-0">
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] hidden lg:block mr-2">Ledger view</p>
<a class="st-btn-secondary h-10 px-4 inline-flex items-center justify-center gap-2 whitespace-nowrap" href="{{ route('purchases.export') }}">
<span class="material-symbols-outlined text-[18px]">download</span>
CSV
</a>
@if(auth()->user()->role !== 'viewer')
<a class="st-btn-primary h-10 px-5 inline-flex items-center justify-center gap-2 whitespace-nowrap" href="{{ route('purchases.create') }}">
<span class="material-symbols-outlined text-[20px]">add</span>
New Purchase
</a>
@endif
</div>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<div class="sm:hidden">
<p class="st-label">Module</p>
<p class="text-xl font-black uppercase tracking-tight text-[#2B3437]">Purchases</p>
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

{{-- Metrics --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-0 border border-[#ABB3B7] bg-white md:grid md:divide-x md:divide-[#ABB3B7]">
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Subtotal</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_subtotal ?? 0, 2) }}</p>
</div>
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">VAT (15%)</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_vat ?? 0, 2) }}</p>
</div>
<div class="p-6 border-2 border-[#5E5E5E] max-md:m-0 -m-px">
<p class="st-label mb-2 text-[#5E5E5E]">Total Purchases</p>
<p class="text-2xl font-black font-mono text-[#5E5E5E] tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($totals->total_purchases ?? 0, 2) }}</p>
</div>
</div>

{{-- Filter bar (structural, no rounded — DESIGN.md) --}}
<form method="GET" action="{{ route('purchases.index') }}" class="flex flex-wrap items-end gap-4 p-5 bg-[#F8F9FA] border border-[#ABB3B7]">
<div class="flex-1 min-w-[200px]">
<label class="st-label block mb-2" for="p-search">Search</label>
<div class="relative">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#586064] material-symbols-outlined text-[18px] pointer-events-none">search</span>
<input class="st-input w-full h-10 pl-10 pr-3 text-sm" id="p-search" type="text" name="search" value="{{ request('search') }}" placeholder="Invoice, vendor, product…">
</div>
</div>
<div class="min-w-[140px]">
<label class="st-label block mb-2" for="p-from">From</label>
<input class="st-input w-full h-10 px-3 text-sm font-mono" id="p-from" type="text" name="from" value="{{ old('from', filter_date_input_value(request('from'))) }}" placeholder="dd/mm/yyyy" inputmode="numeric" autocomplete="off">
</div>
<div class="min-w-[140px]">
<label class="st-label block mb-2" for="p-to">To</label>
<input class="st-input w-full h-10 px-3 text-sm font-mono" id="p-to" type="text" name="to" value="{{ old('to', filter_date_input_value(request('to'))) }}" placeholder="dd/mm/yyyy" inputmode="numeric" autocomplete="off">
</div>
<div class="flex flex-wrap gap-2">
<button type="submit" class="st-btn-primary h-10 px-4 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[16px]">filter_list</span>
Filter
</button>
@if(request('search') || request('from') || request('to'))
<a href="{{ route('purchases.index') }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[16px]">close</span>
Clear
</a>
@endif
</div>
</form>

{{-- Ledger table --}}
<div class="st-paper flex flex-col flex-1 min-h-[400px] border border-[#ABB3B7] bg-white">
<div class="px-5 py-4 border-b border-[#ABB3B7] flex flex-wrap items-center justify-between gap-3 bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">Purchase invoices</h3>
<p class="text-[11px] text-[#586064] mt-0 sm:mt-0">One row per purchase · line details on the purchase page</p>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[900px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3 whitespace-nowrap max-w-[160px]">Customer</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Invoice</th>
<th class="st-th px-4 py-3 min-w-[200px]">Product</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Subtotal</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">VAT</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Total</th>
<th class="st-th px-4 py-3 text-right w-28"></th>
</tr>
</thead>
<tbody>
@forelse($purchases as $purchase)
@php
$rowSymbol = $purchase->currency && $purchase->currency->symbol ? $purchase->currency->symbol : ($currencySymbol ?? '$');
$lineCount = $purchase->items->count();
$firstItem = $purchase->items->first();
$firstName = $firstItem && trim((string) $firstItem->product_name) !== '' ? $firstItem->product_name : '—';
if ($lineCount === 0) {
    $linesSummary = 'No line items';
} elseif ($lineCount === 1) {
    $linesSummary = $firstName;
} else {
    $linesSummary = $firstName.', +'.($lineCount - 1).' more';
}
$showLabel = 'View purchase '.($purchase->invoice_number ?: '#'.$purchase->id);
@endphp
<tr class="st-tr cursor-pointer focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#5E5E5E]"
    data-purchase-show-url="{{ route('purchases.show', $purchase) }}" role="link" tabindex="0" aria-label="{{ e($showLabel) }}">
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ format_display_datetime($purchase->date) }}</td>
<td class="st-td px-4 py-3 text-sm font-semibold text-[#2B3437] truncate max-w-[160px]">{{ $purchase->customer_name ?: ($purchase->customer_code ?: '—') }}</td>
<td class="st-td px-4 py-3 text-sm font-bold text-[#2B3437]">
<a href="{{ route('purchases.show', $purchase) }}" class="text-[#5E5E5E] hover:underline" onclick="event.stopPropagation()">{{ $purchase->invoice_number ?: '#' . $purchase->id }}</a>
</td>
<td class="st-td px-4 py-3 text-sm text-[#586064] max-w-[280px]" title="{{ e($linesSummary) }}">
<span class="line-clamp-2">{{ $linesSummary }}</span>
@if($lineCount > 1)
<span class="block text-[10px] uppercase tracking-wide text-[#586064] mt-0.5">{{ $lineCount }} products</span>
@endif
</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#2B3437]">{{ $rowSymbol }} {{ number_format($purchase->subtotal ?? 0, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#586064]">{{ $rowSymbol }} {{ number_format($purchase->vat_amount ?? 0, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums text-[#5E5E5E]">{{ $rowSymbol }} {{ number_format($purchase->total_amount ?? 0, 2) }}</td>
<td class="st-td px-4 py-3 text-right" data-stop-row-nav>
<div class="flex items-center justify-end gap-1 flex-wrap">
<a href="{{ route('purchases.show', $purchase) }}" class="p-2 border border-transparent hover:border-[#ABB3B7] text-[#586064] hover:text-[#2B3437] hover:bg-[#F1F4F6]" title="View" onclick="event.stopPropagation()">
<span class="material-symbols-outlined text-[18px]">visibility</span>
</a>
@if(auth()->user()->role !== 'viewer')
<form method="POST" action="{{ route('purchases.destroy', $purchase) }}" class="inline-flex" data-confirm-delete="{{ e('Delete this purchase? Related payables and ledger entries will be removed.') }}">
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
No purchases yet.
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('purchases.create') }}" class="text-[#5E5E5E] font-bold underline underline-offset-2">Create your first purchase</a>
@else
<span>No purchases recorded yet.</span>
@endif
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

<div class="p-4 border-t border-[#ABB3B7] bg-[#F8F9FA]">
<p class="text-xs text-[#586064] uppercase tracking-wide">
@if($purchases->count() > 0)
Showing <span class="font-bold text-[#2B3437] tabular-nums">{{ $purchases->count() }}</span> purchases
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
    document.querySelectorAll('tr[data-purchase-show-url]').forEach(function (row) {
        var url = row.getAttribute('data-purchase-show-url');
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
