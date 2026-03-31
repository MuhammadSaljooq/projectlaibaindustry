<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Suppliers - ERP'])
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
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">Suppliers</h2>
</div>
@if(auth()->user()->role !== 'viewer')
<div class="flex items-center gap-2 flex-wrap justify-end">
<a href="{{ route('suppliers.create') }}" class="st-btn-primary h-10 px-5 inline-flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[20px]">add</span>
New supplier
</a>
</div>
@endif
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Suppliers</h1>
</div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] lg:text-right shrink-0">Vendors · international purchase links · @if(auth()->user()->role !== 'viewer') click row to edit @else read-only @endif</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

@if (session('success'))
<div class="border border-[#ABB3B7] bg-white px-4 py-3 text-sm text-[#2B3437]">
{{ session('success') }}
</div>
@endif

<div class="border border-[#5E5E5E] bg-white p-6">
<p class="st-label st-label--primary mb-2">Total suppliers</p>
<p class="text-2xl font-black font-mono text-[#5E5E5E] tabular-nums">{{ number_format($totalSuppliersCount) }}</p>
</div>

<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[320px]">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">Supplier directory</h3>
<p class="text-[11px] text-[#586064] mt-1">Name · country · contact · phone · email</p>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[800px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3">Name</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Country</th>
<th class="st-th px-4 py-3">Contact</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Phone</th>
<th class="st-th px-4 py-3">Email</th>
@if(auth()->user()->role !== 'viewer')
<th class="st-th px-4 py-3 text-right w-36"></th>
@endif
</tr>
</thead>
<tbody>
@forelse($suppliers as $supplier)
<tr class="st-tr @if(auth()->user()->role !== 'viewer') cursor-pointer focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#5E5E5E] @endif"
    @if(auth()->user()->role !== 'viewer') data-supplier-edit-url="{{ route('suppliers.edit', $supplier) }}" role="link" tabindex="0" aria-label="Edit {{ e($supplier->name) }}" @endif>
<td class="st-td px-4 py-3 text-sm font-semibold text-[#2B3437]">{{ $supplier->name }}</td>
<td class="st-td px-4 py-3 text-sm text-[#586064]">{{ $supplier->country ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm text-[#586064]">{{ $supplier->contact_name ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm text-[#586064] whitespace-nowrap">{{ $supplier->phone ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm text-[#586064]">{{ $supplier->email ?: '—' }}</td>
@if(auth()->user()->role !== 'viewer')
<td class="st-td px-4 py-3 text-right" data-stop-row-nav>
<div class="flex items-center justify-end gap-1 flex-wrap">
<a href="{{ route('suppliers.edit', $supplier) }}" class="p-2 border border-transparent hover:border-[#ABB3B7] text-[#586064] hover:text-[#2B3437] hover:bg-[#F1F4F6]" title="Edit">
<span class="material-symbols-outlined text-[18px]">edit</span>
</a>
<form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="inline-flex" onsubmit="return confirm('Delete this supplier? Linked international purchases will keep the line but lose the supplier link.');">
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
<td colspan="{{ auth()->user()->role === 'viewer' ? 5 : 6 }}" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No suppliers yet</p>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('suppliers.create') }}" class="text-[#5E5E5E] font-bold underline underline-offset-2">Add first supplier</a>
@else
<span>No vendors on file.</span>
@endif
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

@if($suppliers->hasPages())
<div class="p-4 border-t border-[#ABB3B7] flex flex-col sm:flex-row items-center justify-between gap-4 bg-[#F8F9FA]">
<p class="text-xs text-[#586064] uppercase tracking-wide">
Showing <span class="font-bold text-[#2B3437] tabular-nums">{{ $suppliers->firstItem() }}</span>–<span class="font-bold text-[#2B3437] tabular-nums">{{ $suppliers->lastItem() }}</span> of <span class="font-bold text-[#2B3437] tabular-nums">{{ $suppliers->total() }}</span>
</p>
<nav class="flex items-stretch border border-[#ABB3B7] bg-white divide-x divide-[#ABB3B7]" aria-label="Pagination">
@if (!$suppliers->onFirstPage())
<a class="p-2 text-[#586064] hover:bg-[#F1F4F6] inline-flex items-center justify-center" href="{{ $suppliers->previousPageUrl() }}" aria-label="Previous"><span class="material-symbols-outlined text-[20px]">chevron_left</span></a>
@endif
@foreach ($suppliers->getUrlRange(max(1, $suppliers->currentPage() - 2), min($suppliers->lastPage(), $suppliers->currentPage() + 2)) ?: [1 => $suppliers->url(1)] as $page => $url)
@if ($page == $suppliers->currentPage())
<span class="px-3 py-2 text-xs font-bold uppercase tracking-wider bg-[#5E5E5E] text-[#F8F8F8] inline-flex items-center justify-center min-w-[2.5rem]">{{ $page }}</span>
@else
<a class="px-3 py-2 text-xs font-bold text-[#586064] hover:bg-[#F1F4F6] inline-flex items-center justify-center min-w-[2.5rem]" href="{{ $url }}">{{ $page }}</a>
@endif
@endforeach
@if ($suppliers->hasMorePages())
<a class="p-2 text-[#586064] hover:bg-[#F1F4F6] inline-flex items-center justify-center" href="{{ $suppliers->nextPageUrl() }}" aria-label="Next"><span class="material-symbols-outlined text-[20px]">chevron_right</span></a>
@endif
</nav>
</div>
@endif
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-6 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
@if(auth()->user()->role !== 'viewer')
<script>
(function () {
    document.querySelectorAll('tr[data-supplier-edit-url]').forEach(function (row) {
        var url = row.getAttribute('data-supplier-edit-url');
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
