<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Inventory - Laiba Safety'])
@include('partials.stitch-design')
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'products'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">Inventory</h2>
</div>
<div class="flex items-center gap-2">
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('products.create') }}" class="st-btn-primary h-10 px-5 inline-flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[20px]">add</span>
Add item
</a>
@endif
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Inventory</h1>
</div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] lg:text-right shrink-0">Stock ledger · cost-based valuation</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<div class="sm:hidden">
<p class="st-label">Module</p>
<p class="text-xl font-black uppercase tracking-tight text-[#2B3437]">Inventory</p>
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
<p class="st-label mb-2">Total items</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ number_format($totalItems ?? 0) }}</p>
</div>
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Low stock alerts</p>
<p class="text-2xl font-bold font-mono tabular-nums {{ ($lowStockCount ?? 0) > 0 ? 'text-[#9F403D]' : 'text-[#2B3437]' }}">{{ number_format($lowStockCount ?? 0) }}</p>
</div>
<div class="p-6 border-2 border-[#5E5E5E] max-md:m-0 -m-px">
<p class="st-label st-label--primary mb-2">Inventory value</p>
<p class="text-2xl font-black font-mono text-[#5E5E5E] tabular-nums">{{ $currencySymbol }} {{ number_format($totalValue ?? 0, 0) }}</p>
</div>
</div>

<form method="GET" action="{{ route('inventory.dashboard') }}" class="flex flex-wrap items-end gap-4 p-5 bg-[#F8F9FA] border border-[#ABB3B7]">
<div class="flex-1 min-w-[200px]">
<label class="st-label block mb-2" for="inv-search">Search</label>
<div class="relative">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#586064] material-symbols-outlined text-[18px] pointer-events-none">search</span>
<input class="st-input w-full h-10 pl-10 pr-3 text-sm" id="inv-search" type="text" name="search" value="{{ request('search') }}" placeholder="Name, article no.…">
</div>
</div>
<div class="min-w-[200px]">
<label class="st-label block mb-2" for="inv-cat">Category</label>
<div class="relative isolate">
<select class="st-select w-full h-10 pl-3 pr-12 text-sm appearance-none cursor-pointer" id="inv-cat" name="category_id">
<option value="">All categories</option>
@foreach($categories ?? [] as $cat)
<option value="{{ $cat->id }}" {{ (string) request('category_id') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
@endforeach
</select>
<span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined pointer-events-none text-[#586064] !text-[18px] leading-none w-6 flex items-center justify-center" aria-hidden="true">expand_more</span>
</div>
</div>
<div class="flex flex-wrap gap-2">
<button type="submit" class="st-btn-primary h-10 px-4 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[16px]">filter_list</span>
Filter
</button>
@if(request('search') || request('category_id'))
<a href="{{ route('inventory.dashboard') }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[16px]">close</span>
Clear
</a>
@endif
</div>
</form>

<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[320px]">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">Product stock ledger</h3>
<p class="text-[11px] text-[#586064] mt-1">Article no. · category · stock status · edit from actions</p>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[900px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3">Item</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Article no.</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Category</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Stock</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Unit price</th>
<th class="st-th px-4 py-3 text-center whitespace-nowrap">Status</th>
<th class="st-th px-4 py-3 text-right w-36"></th>
</tr>
</thead>
<tbody>
@forelse($products ?? [] as $product)
<tr class="st-tr @if(auth()->user()->role !== 'viewer') cursor-pointer focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#5E5E5E] @endif"
    @if(auth()->user()->role !== 'viewer') data-product-edit-url="{{ route('products.edit', $product) }}" role="link" tabindex="0" aria-label="Edit {{ e($product->name) }}" @endif>
<td class="st-td px-4 py-3">
<div class="flex items-center gap-3 min-w-0">
<div class="h-9 w-9 shrink-0 border border-[#ABB3B7] bg-[#EAEFF1] flex items-center justify-center">
<span class="material-symbols-outlined text-[#586064] text-[18px]">inventory_2</span>
</div>
<div class="min-w-0">
<p class="text-sm font-bold text-[#2B3437] truncate">{{ $product->name }}</p>
<p class="text-xs text-[#586064] truncate mt-0.5">{{ \Illuminate\Support\Str::limit($product->description ?? '', 48) ?: '—' }}</p>
</div>
</div>
</td>
<td class="st-td px-4 py-3 text-sm font-mono tabular-nums text-[#586064]">{{ $product->sku }}</td>
<td class="st-td px-4 py-3 text-xs font-bold uppercase tracking-wide text-[#586064]">{{ optional($product->category)->name ?? '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right tabular-nums {{ $product->stock_quantity <= 0 ? 'text-[#9F403D]' : ($product->stock_quantity <= ($product->reorder_level ?? 10) ? 'text-[#9F403D]' : 'text-[#2B3437]') }}">{{ $product->stock_quantity }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#2B3437]">{{ $currencySymbol }} {{ number_format($product->selling_price ?? $product->cost_price ?? 0, 2) }}</td>
<td class="st-td px-4 py-3 text-center">
@if ($product->stock_quantity <= 0)
<span class="inline-block text-[10px] font-bold uppercase tracking-wide px-2 py-1 border border-[#9F403D] text-[#9F403D]">Out</span>
@elseif ($product->stock_quantity <= ($product->reorder_level ?? 10))
<span class="inline-block text-[10px] font-bold uppercase tracking-wide px-2 py-1 border border-[#9F403D] text-[#9F403D]">Low</span>
@else
<span class="inline-block text-[10px] font-bold uppercase tracking-wide px-2 py-1 border border-[#ABB3B7] text-[#586064]">OK</span>
@endif
</td>
<td class="st-td px-4 py-3 text-right" data-stop-row-nav>
@if(auth()->user()->role !== 'viewer')
<div class="flex items-center justify-end gap-1 flex-wrap">
<a href="{{ route('products.edit', $product) }}" class="p-2 border border-transparent hover:border-[#ABB3B7] text-[#586064] hover:text-[#2B3437] hover:bg-[#F1F4F6]" title="Edit">
<span class="material-symbols-outlined text-[18px]">edit</span>
</a>
<form method="POST" action="{{ route('products.destroy', $product) }}" class="inline-flex" data-confirm-delete="{{ e('Delete this product?') }}">
@csrf
@method('DELETE')
<button type="submit" class="p-2 border border-transparent hover:border-[#9F403D] text-[#586064] hover:text-[#9F403D] hover:bg-[#F1F4F6]" title="Delete">
<span class="material-symbols-outlined text-[18px]">delete</span>
</button>
</form>
</div>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="7" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No products found</p>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('products.create') }}" class="text-[#5E5E5E] font-bold underline underline-offset-2">Add first product</a>
@endif
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

@if(isset($products))
<div class="p-4 border-t border-[#ABB3B7] bg-[#F8F9FA]">
<p class="text-xs text-[#586064] uppercase tracking-wide">
@if($products->count() > 0)
Showing <span class="font-bold text-[#2B3437] tabular-nums">{{ $products->count() }}</span> products
@else
No results
@endif
</p>
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
    document.querySelectorAll('tr[data-product-edit-url]').forEach(function (row) {
        var url = row.getAttribute('data-product-edit-url');
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
