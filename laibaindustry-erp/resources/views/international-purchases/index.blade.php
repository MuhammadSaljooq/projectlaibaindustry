<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'International purchases - ERP'])
@include('partials.stitch-design')
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'international_purchases'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">International purchases</h2>
</div>
<div class="flex items-center gap-2 flex-wrap justify-end">
<a href="{{ route('international-purchases.export') }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[18px]">download</span>
CSV
</a>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('international-purchases.create') }}" class="st-btn-primary h-10 px-5 inline-flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[20px]">add</span>
New entry
</a>
@endif
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">International purchases</h1>
</div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] lg:text-right shrink-0">Import ledger · @if(auth()->user()->role !== 'viewer') click row to edit @else read-only @endif</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<div class="sm:hidden">
<p class="st-label">Module</p>
<p class="text-xl font-black uppercase tracking-tight text-[#2B3437]">International purchases</p>
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

<div class="grid grid-cols-1 md:grid-cols-2 gap-0 border border-[#ABB3B7] bg-white md:divide-x md:divide-[#ABB3B7]">
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Total entries</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ number_format($purchases->count()) }}</p>
</div>
<div class="p-6 border-2 border-[#5E5E5E] max-md:m-0 -m-px">
<p class="st-label st-label--primary mb-2">All-time total</p>
<p class="text-2xl font-black font-mono text-[#5E5E5E] tabular-nums">{{ $currencySymbol }} {{ number_format($totalAmount ?? 0, 2) }}</p>
</div>
</div>

<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[320px]">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">International purchase ledger</h3>
<p class="text-[11px] text-[#586064] mt-1">Date · vendor · product · qty · unit price · total · actions</p>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[880px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3">Vendor</th>
<th class="st-th px-4 py-3">Product</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Qty</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Price</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Total</th>
@if(auth()->user()->role !== 'viewer')
<th class="st-th px-4 py-3 text-right w-36"></th>
@endif
</tr>
</thead>
<tbody>
@forelse($purchases as $purchase)
<tr class="st-tr @if(auth()->user()->role !== 'viewer') cursor-pointer focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#5E5E5E] @endif"
    @if(auth()->user()->role !== 'viewer') data-ip-edit-url="{{ route('international-purchases.edit', $purchase) }}" role="link" tabindex="0" aria-label="Edit {{ e($purchase->product_name) }}" @endif>
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ format_display_date($purchase->date) }}</td>
<td class="st-td px-4 py-3 text-sm text-[#586064]">{{ $purchase->supplier?->name ?? '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-semibold text-[#2B3437]">{{ $purchase->product_name }}</td>
<td class="st-td px-4 py-3 text-sm text-right tabular-nums text-[#586064]">{{ number_format($purchase->quantity) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums text-[#5E5E5E]">{{ $currencySymbol }} {{ number_format($purchase->unit_price, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums text-[#5E5E5E]">{{ $currencySymbol }} {{ number_format($purchase->total_amount, 2) }}</td>
@if(auth()->user()->role !== 'viewer')
<td class="st-td px-4 py-3 text-right" data-stop-row-nav>
<div class="flex items-center justify-end gap-1 flex-wrap">
<a href="{{ route('international-purchases.edit', $purchase) }}" class="p-2 border border-transparent hover:border-[#ABB3B7] text-[#586064] hover:text-[#2B3437] hover:bg-[#F1F4F6]" title="Edit">
<span class="material-symbols-outlined text-[18px]">edit</span>
</a>
<form method="POST" action="{{ route('international-purchases.destroy', $purchase) }}" class="inline-flex" data-confirm-delete="{{ e('Delete this entry?') }}">
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
<tr>
<td colspan="{{ auth()->user()->role === 'viewer' ? 6 : 7 }}" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No international purchases yet</p>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('international-purchases.create') }}" class="text-[#5E5E5E] font-bold underline underline-offset-2">Add first entry</a>
@else
<span>No records.</span>
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
Showing <span class="font-bold text-[#2B3437] tabular-nums">{{ $purchases->count() }}</span> international purchases
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
@if(auth()->user()->role !== 'viewer')
<script>
(function () {
    document.querySelectorAll('tr[data-ip-edit-url]').forEach(function (row) {
        var url = row.getAttribute('data-ip-edit-url');
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
@endif
</body>
</html>
