<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Quotations - ERP'])
@include('partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'quotations'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 st-touch-target text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">Quotations</h2>
</div>
<div class="flex items-center gap-2 flex-wrap justify-end">
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('quotations.create') }}" class="st-btn-primary h-10 px-5 inline-flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[20px]">add</span>
New quotation
</a>
@endif
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">
<div class="flex flex-col gap-1">
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Quotations</h1>
<p class="text-sm text-[#586064] mt-2">الاقتباسات</p>
<div class="h-0.5 w-full bg-[#5E5E5E] mt-4" role="presentation"></div>
</div>

@if (session('success'))
<div class="border border-[#ABB3B7] bg-white px-4 py-3 text-sm text-[#2B3437]">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D]">{{ session('error') }}</div>
@endif

<div class="border border-[#ABB3B7] bg-white overflow-x-auto">
<table class="w-full text-left border-collapse min-w-[820px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3">Number</th>
<th class="st-th px-4 py-3">Customer</th>
<th class="st-th px-4 py-3">Date</th>
<th class="st-th px-4 py-3">Expires</th>
<th class="st-th px-4 py-3 text-center">Status</th>
<th class="st-th px-4 py-3 text-right">Total</th>
<th class="st-th px-4 py-3 w-52"></th>
</tr>
</thead>
<tbody>
@forelse ($quotations as $q)
<tr class="st-tr hover:bg-[#F8F9FA] cursor-pointer focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#5E5E5E]"
    data-open-url="{{ route('quotations.show', $q) }}"
    title="Open quotation"
    role="link" tabindex="0" aria-label="Open quotation {{ $q->quotation_number }}">
<td class="st-td px-4 py-3 font-mono font-semibold">
<a href="{{ route('quotations.show', $q) }}" class="text-[#137fec] hover:underline">{{ $q->quotation_number }}</a>
</td>
<td class="st-td px-4 py-3">{{ $q->customer_name }}</td>
<td class="st-td px-4 py-3">{{ $q->quotation_date->format('j M Y') }}</td>
<td class="st-td px-4 py-3 text-[#586064]">{{ $q->expiration_date ? $q->expiration_date->format('j M Y') : '—' }}</td>
<td class="st-td px-4 py-3 text-center">
<span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide
@if($q->status === 'draft') bg-gray-100 text-gray-600
@elseif($q->status === 'sent') bg-blue-50 text-blue-800
@elseif($q->status === 'accepted') bg-green-50 text-green-800
@elseif($q->status === 'rejected') bg-red-50 text-red-800
@else bg-amber-50 text-amber-800
@endif">{{ $q->status }}</span>
</td>
<td class="st-td px-4 py-3 text-right font-mono tabular-nums">SAR {{ number_format((float) $q->total_amount, 2) }}</td>
<td class="st-td px-4 py-3 text-right" data-stop-row-nav>
<div class="flex flex-wrap justify-end gap-1">
<a href="{{ route('quotations.preview', $q) }}?v={{ $q->updated_at?->timestamp ?? $q->id }}" target="_blank" rel="noopener" class="st-btn-secondary h-8 px-2 st-touch-target text-[10px] inline-flex items-center gap-1">Preview</a>
<a href="{{ route('quotations.pdf', $q) }}?v={{ $q->updated_at?->timestamp ?? $q->id }}" class="st-btn-secondary h-8 px-2 st-touch-target text-[10px] inline-flex items-center gap-1">PDF</a>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('quotations.edit', $q) }}" class="st-btn-primary h-8 px-2 st-touch-target text-[10px] inline-flex items-center gap-1">Edit</a>
@endif
</div>
</td>
</tr>
@empty
<tr class="st-tr"><td class="st-td px-4 py-8 text-center text-[#586064]" colspan="7">No quotations yet.@if(auth()->user()->role !== 'viewer') <a href="{{ route('quotations.create') }}" class="text-[#137fec] font-semibold underline ml-1">Create one</a>@endif</td></tr>
@endforelse
</tbody>
</table>
</div>

<div class="mt-2">
{{ $quotations->links() }}
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-4">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
<script>
(function () {
    var rows = Array.from(document.querySelectorAll('tr[data-open-url]'));
    rows.forEach(function (row) {
        var url = row.getAttribute('data-open-url');
        if (!url) return;
        row.addEventListener('click', function (event) {
            if (event.target.closest('[data-stop-row-nav], a, button, form, input, select, textarea, label')) return;
            window.location.href = url;
        });
        row.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            if (event.target.closest('[data-stop-row-nav], a, button, form, input, select, textarea, label')) return;
            event.preventDefault();
            window.location.href = url;
        });
    });
})();
</script>
</body>
</html>
