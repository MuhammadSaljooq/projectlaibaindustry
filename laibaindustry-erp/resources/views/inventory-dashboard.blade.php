<!DOCTYPE html>
<html lang="en" class="dark">
<head>
@include('partials.frontend-head', ['title' => 'Inventory - Laiba Safety'])
<style>
body { background-color: #131313; color: #e2e2e2; font-family: 'Inter', sans-serif; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #FFFFFF; color: #131313; }
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'products'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#131313;">

{{-- Header Bar --}}
<header class="h-16 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="background:#1B1B1B;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 hover:text-white rounded-md" style="color:#8e9192;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
</div>
<div class="flex items-center gap-3">
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('products.create') }}" class="h-9 px-5 text-[11px] font-bold uppercase flex items-center gap-2 active:scale-[0.98] transition-all" style="background:#FFFFFF;color:#2F3131;border-radius:0.375rem;letter-spacing:0.05em;">
<span class="material-symbols-outlined" style="font-size:14px;">add</span>
ADD ITEM
</a>
@endif
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col" style="gap:2rem;">

{{-- Page Heading --}}
<div>
<span class="text-[11px] font-medium uppercase block mb-2" style="letter-spacing:0.2em;color:#8e9192;">Stock Management</span>
<h2 class="text-white font-black" style="font-size:2.5rem;letter-spacing:-0.02em;line-height:1.1;">Inventory</h2>
</div>

{{-- Flash Messages --}}
@if (session('success'))
<div style="background:rgba(255,255,255,0.05);border-radius:0.5rem;padding:0.75rem 1.25rem;" class="text-sm font-medium text-white">
<span class="material-symbols-outlined align-middle" style="font-size:16px;margin-right:0.5rem;color:#FFFFFF;">check_circle</span>
{{ session('success') }}
</div>
@endif
@if (session('error'))
<div style="background:rgba(255,180,171,0.08);border-radius:0.5rem;padding:0.75rem 1.25rem;" class="text-sm font-medium" >
<span class="material-symbols-outlined align-middle" style="font-size:16px;margin-right:0.5rem;color:#FFB4AB;">error</span>
<span style="color:#FFB4AB;">{{ session('error') }}</span>
</div>
@endif

{{-- Stat Cards --}}
<div class="grid grid-cols-12" style="gap:1.5rem;">

{{-- Total Items --}}
<div class="col-span-12 md:col-span-4 relative overflow-hidden group" style="background:#1B1B1B;border-radius:0.5rem;padding:2rem;">
<div class="absolute top-0 right-0 p-4 opacity-[0.05] group-hover:opacity-[0.1] transition-opacity">
<span class="material-symbols-outlined" style="font-size:4rem;">inventory_2</span>
</div>
<p class="text-[10px] font-semibold uppercase" style="letter-spacing:0.15em;margin-bottom:1.5rem;color:#C4C7C8;">Total Items</p>
<h3 class="text-white font-black tabular-nums" style="font-size:clamp(2rem,5vw,3.5rem);letter-spacing:-0.03em;margin-bottom:0.5rem;">{{ number_format($totalItems ?? 0) }}</h3>
<p class="text-sm font-medium" style="color:#8e9192;">Products in catalogue</p>
</div>

{{-- Low Stock Alerts --}}
<div class="col-span-12 md:col-span-4 relative overflow-hidden group" style="background:#1B1B1B;border-radius:0.5rem;padding:2rem;">
<div class="absolute top-0 right-0 p-4 opacity-[0.05] group-hover:opacity-[0.1] transition-opacity">
<span class="material-symbols-outlined" style="font-size:4rem;">warning</span>
</div>
<p class="text-[10px] font-semibold uppercase" style="letter-spacing:0.15em;margin-bottom:1.5rem;color:#C4C7C8;">Low Stock Alerts</p>
<h3 class="font-black tabular-nums" style="font-size:clamp(2rem,5vw,3.5rem);letter-spacing:-0.03em;margin-bottom:0.5rem;color:{{ ($lowStockCount ?? 0) > 0 ? '#FFB4AB' : '#FFFFFF' }};">{{ number_format($lowStockCount ?? 0) }}</h3>
<p class="text-sm font-medium" style="color:#8e9192;">Items need restocking</p>
</div>

{{-- Total Value (Hero Inverted White) --}}
<div class="col-span-12 md:col-span-4 relative overflow-hidden group" style="background:linear-gradient(135deg,#FFFFFF,#C6C6C7);border-radius:0.5rem;padding:2rem;">
<p class="text-[10px] font-semibold uppercase" style="letter-spacing:0.15em;margin-bottom:1.5rem;color:rgba(42,49,49,0.5);">Total Inventory Value</p>
<h3 class="font-black tabular-nums" style="font-size:clamp(2rem,5vw,3.5rem);letter-spacing:-0.03em;margin-bottom:0.5rem;color:#1a1c1c;">{{ $currencySymbol }}{{ number_format($totalValue ?? 0, 0) }}</h3>
<p class="text-sm font-medium" style="color:rgba(42,49,49,0.45);">Cost-based valuation</p>
</div>

</div>

{{-- Search & Filter Bar --}}
<form method="GET" action="{{ route('inventory.dashboard') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3" style="background:#1B1B1B;border-radius:0.5rem;padding:1.25rem 1.5rem;">
<div class="relative flex-1 min-w-0">
<span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined pointer-events-none" style="font-size:18px;color:#8e9192;">search</span>
<input class="w-full h-10 pl-10 pr-4 text-sm font-medium text-white placeholder-[#C4C7C8]/50 outline-none transition-all" style="background:transparent;border:1px solid rgba(68,71,72,0.4);border-radius:0.375rem;" name="search" type="text" placeholder="Search by name, SKU..." value="{{ request('search') }}" onfocus="this.style.borderColor='#FFFFFF';this.style.boxShadow='0 0 0 2px rgba(255,255,255,0.1)'" onblur="this.style.borderColor='rgba(68,71,72,0.4)';this.style.boxShadow='none'"/>
</div>
<div class="relative shrink-0 sm:w-48">
<select class="w-full h-10 pl-3 pr-10 text-sm font-medium text-white outline-none appearance-none cursor-pointer transition-all" style="background:transparent;border:1px solid rgba(68,71,72,0.4);border-radius:0.375rem;" name="category_id" onfocus="this.style.borderColor='#FFFFFF'" onblur="this.style.borderColor='rgba(68,71,72,0.4)'">
<option value="" style="background:#1B1B1B;">All Categories</option>
<option value="all" style="background:#1B1B1B;" {{ request('category_id') === 'all' ? 'selected' : '' }}>All Categories</option>
@foreach($categories ?? [] as $cat)
<option value="{{ $cat->id }}" style="background:#1B1B1B;" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
@endforeach
</select>
<span class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined pointer-events-none" style="font-size:18px;color:#8e9192;">expand_more</span>
</div>
<button type="submit" class="h-10 px-5 text-[11px] font-bold uppercase flex items-center justify-center gap-2 active:scale-[0.98] transition-all shrink-0" style="background:#FFFFFF;color:#2F3131;border-radius:0.375rem;letter-spacing:0.05em;">
<span class="material-symbols-outlined" style="font-size:14px;">filter_list</span>
FILTER
</button>
@if(request('search') || request('category_id'))
<a href="{{ route('inventory.dashboard') }}" class="h-10 px-4 text-[11px] font-bold uppercase flex items-center justify-center gap-1 transition-all shrink-0" style="color:#C4C7C8;border:1px solid rgba(68,71,72,0.4);border-radius:0.375rem;" onmouseover="this.style.color='#FFFFFF';this.style.borderColor='#FFFFFF'" onmouseout="this.style.color='#C4C7C8';this.style.borderColor='rgba(68,71,72,0.4)'">
CLEAR
</a>
@endif
</form>

{{-- Products Table --}}
<div style="background:#1B1B1B;border-radius:0.5rem;overflow:hidden;">

{{-- Table Header --}}
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse" style="min-width:700px;">
<thead>
<tr style="background:#0E0E0E;">
<th class="px-6 py-4 text-[10px] font-bold uppercase" style="letter-spacing:0.15em;color:#8e9192;">Item</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase" style="letter-spacing:0.15em;color:#8e9192;">SKU</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase" style="letter-spacing:0.15em;color:#8e9192;">Category</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.15em;color:#8e9192;">Stock</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.15em;color:#8e9192;">Unit Price</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase text-center" style="letter-spacing:0.15em;color:#8e9192;">Status</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.15em;color:#8e9192;">Actions</th>
</tr>
</thead>
<tbody>
@forelse($products ?? [] as $product)
<tr class="group transition-colors" style="border-top:1px solid rgba(68,71,72,0.15);" onmouseover="this.style.background='#2A2A2A'" onmouseout="this.style.background='transparent'">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="flex items-center justify-center shrink-0" style="width:2.5rem;height:2.5rem;background:#353535;border-radius:0.25rem;">
<span class="material-symbols-outlined" style="font-size:16px;color:#C4C7C8;">inventory_2</span>
</div>
<div class="min-w-0">
<p class="text-sm font-bold text-white truncate">{{ $product->name }}</p>
<p class="text-xs truncate" style="color:#8e9192;margin-top:0.125rem;">{{ Str::limit($product->description, 40) ?: '-' }}</p>
</div>
</div>
</td>
<td class="px-6 py-4">
<span class="text-sm font-medium tabular-nums" style="color:#C4C7C8;">{{ $product->sku }}</span>
</td>
<td class="px-6 py-4">
<span class="text-xs font-bold uppercase px-2 py-1" style="background:#353535;border-radius:0.25rem;color:#C4C7C8;letter-spacing:0.03em;">{{ $product->category->name ?? '-' }}</span>
</td>
<td class="px-6 py-4 text-right">
<span class="text-sm font-black tabular-nums" style="color:{{ $product->stock_quantity <= 0 ? '#FFB4AB' : ($product->stock_quantity <= ($product->reorder_level ?? 10) ? '#FFB4AB' : '#FFFFFF') }};">{{ $product->stock_quantity }}</span>
</td>
<td class="px-6 py-4 text-right">
<span class="text-sm font-bold tabular-nums text-white whitespace-nowrap">{{ $currencySymbol }} {{ number_format($product->selling_price ?? $product->cost_price ?? 0, 2) }}</span>
</td>
<td class="px-6 py-4 text-center">
@if ($product->stock_quantity <= 0)
<span class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase px-2.5 py-1" style="background:rgba(255,180,171,0.08);color:#FFB4AB;border-radius:0.25rem;letter-spacing:0.05em;">
<span style="width:5px;height:5px;border-radius:50%;background:#FFB4AB;display:inline-block;"></span>
Out of Stock
</span>
@elseif ($product->stock_quantity <= ($product->reorder_level ?? 10))
<span class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase px-2.5 py-1" style="background:rgba(255,180,171,0.08);color:#FFB4AB;border-radius:0.25rem;letter-spacing:0.05em;">
<span style="width:5px;height:5px;border-radius:50%;background:#FFB4AB;display:inline-block;"></span>
Low Stock
</span>
@else
<span class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase px-2.5 py-1" style="background:rgba(255,255,255,0.05);color:#C4C7C8;border-radius:0.25rem;letter-spacing:0.05em;">
<span style="width:5px;height:5px;border-radius:50%;background:#FFFFFF;display:inline-block;"></span>
In Stock
</span>
@endif
</td>
<td class="px-6 py-4 text-right">
@if(auth()->user()->role !== 'viewer')
<div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
<a href="{{ route('products.edit', $product) }}" class="p-1.5 transition-colors" style="color:#8e9192;border-radius:0.25rem;" onmouseover="this.style.color='#FFFFFF';this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.color='#8e9192';this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:18px;">edit</span>
</a>
<form method="POST" action="{{ route('products.destroy', $product) }}" class="inline-flex" onsubmit="return confirm('Delete this product?');">
@csrf
@method('DELETE')
<button type="submit" class="p-1.5 transition-colors" style="color:#8e9192;border-radius:0.25rem;" onmouseover="this.style.color='#FFB4AB';this.style.background='rgba(255,180,171,0.08)'" onmouseout="this.style.color='#8e9192';this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:18px;">delete</span>
</button>
</form>
</div>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="7" class="px-6 py-16 text-center">
<span class="material-symbols-outlined block" style="font-size:3rem;color:#353535;margin-bottom:1rem;">inventory_2</span>
<p class="text-sm font-medium" style="color:#8e9192;">No products found.</p>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('products.create') }}" class="inline-block text-[11px] font-bold uppercase text-white" style="margin-top:1rem;letter-spacing:-0.02em;border-bottom:1px solid #FFFFFF;padding-bottom:0.25rem;">Add First Product</a>
@endif
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

{{-- Pagination --}}
<div class="flex flex-col sm:flex-row items-center justify-between gap-4" style="padding:1rem 1.5rem;background:#0E0E0E;">
<p class="text-xs font-medium" style="color:#8e9192;">
@if(isset($products) && $products->total() > 0)
Showing <span class="text-white font-bold">{{ $products->firstItem() }}</span> to <span class="text-white font-bold">{{ $products->lastItem() }}</span> of <span class="text-white font-bold">{{ $products->total() }}</span> results
@else
No results
@endif
</p>
@if(isset($products) && $products->hasPages())
<nav class="flex items-center gap-1" aria-label="Pagination">
@if (!$products->onFirstPage())
<a class="p-1.5 transition-colors" style="color:#C4C7C8;border-radius:0.25rem;" href="{{ $products->previousPageUrl() }}" onmouseover="this.style.background='#2A2A2A'" onmouseout="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:18px;">chevron_left</span>
</a>
@endif
@foreach ($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) ?: [1 => $products->url(1)] as $page => $url)
@if ($page == $products->currentPage())
<span class="px-3 py-1.5 text-xs font-bold" style="background:#FFFFFF;color:#2F3131;border-radius:0.375rem;">{{ $page }}</span>
@else
<a class="px-3 py-1.5 text-xs font-medium transition-colors" style="color:#C4C7C8;border-radius:0.375rem;" href="{{ $url }}" onmouseover="this.style.background='#2A2A2A';this.style.color='#FFFFFF'" onmouseout="this.style.background='transparent';this.style.color='#C4C7C8'">{{ $page }}</a>
@endif
@endforeach
@if ($products->hasMorePages())
<a class="p-1.5 transition-colors" style="color:#C4C7C8;border-radius:0.25rem;" href="{{ $products->nextPageUrl() }}" onmouseover="this.style.background='#2A2A2A'" onmouseout="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:18px;">chevron_right</span>
</a>
@endif
</nav>
@endif
</div>

</div>

{{-- Footer --}}
<div class="text-center text-[10px] uppercase font-medium pb-4" style="margin-top:2rem;letter-spacing:0.15em;color:#8e9192;">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</div>

</div>
</div>
</main>

</body>
</html>
