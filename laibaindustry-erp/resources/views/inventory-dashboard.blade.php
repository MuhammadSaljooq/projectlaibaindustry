<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Inventory - Laiba Safety'])
<style>
body { background-color: #FFFFFF; color: #2B3437; font-family: 'Inter', sans-serif; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #2B3437; color: #FFFFFF; }
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'products'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#FFFFFF;">

<header class="h-14 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 rounded-none" style="color:#5E5E5E;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
</div>
<div class="flex items-center gap-3">
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('products.create') }}" class="h-9 px-5 text-[11px] font-bold uppercase flex items-center gap-2 active:scale-[0.98] transition-all" style="background:#5E5E5E;color:#F8F8F8;border-radius:0;letter-spacing:0.05em;">
<span class="material-symbols-outlined" style="font-size:14px;">add</span>
ADD ITEM
</a>
@endif
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col" style="gap:3rem;">

{{-- Technical Header --}}
<div>
<div class="flex items-end justify-between" style="padding-bottom:0.75rem;border-bottom:2px solid #5E5E5E;">
<h2 class="font-bold" style="font-size:1.5rem;letter-spacing:-0.02em;color:#2B3437;">Inventory</h2>
<span class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Section INV-01</span>
</div>
</div>

{{-- Flash Messages --}}
@if (session('success'))
<div style="border:1px solid #D3D8DE;padding:0.75rem 1.25rem;" class="text-sm font-bold">
<span class="material-symbols-outlined align-middle" style="font-size:16px;margin-right:0.5rem;color:#5E5E5E;">check_circle</span>
{{ session('success') }}
</div>
@endif
@if (session('error'))
<div style="border:1px solid #9F403D;padding:0.75rem 1.25rem;" class="text-sm font-bold">
<span class="material-symbols-outlined align-middle" style="font-size:16px;margin-right:0.5rem;color:#9F403D;">error</span>
<span style="color:#9F403D;">{{ session('error') }}</span>
</div>
@endif

{{-- Stat Modules --}}
<div class="grid grid-cols-1 md:grid-cols-3" style="gap:1px;background:#D3D8DE;border:1px solid #D3D8DE;">

<div style="background:#FFFFFF;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">Total Items</p>
<p class="font-bold tabular-nums" style="font-size:2rem;letter-spacing:-0.02em;color:#2B3437;">{{ number_format($totalItems ?? 0) }}</p>
<p class="text-xs font-bold" style="margin-top:0.5rem;color:#5E5E5E;">Products in catalogue</p>
</div>

<div style="background:#FFFFFF;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">Low Stock Alerts</p>
<p class="font-bold tabular-nums" style="font-size:2rem;letter-spacing:-0.02em;color:{{ ($lowStockCount ?? 0) > 0 ? '#9F403D' : '#2B3437' }};">{{ number_format($lowStockCount ?? 0) }}</p>
<p class="text-xs font-bold" style="margin-top:0.5rem;color:#5E5E5E;">Items need restocking</p>
</div>

<div style="background:#F8F9FA;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">Total Inventory Value</p>
<p class="font-bold tabular-nums" style="font-size:1.5rem;letter-spacing:-0.02em;color:#2B3437;word-break:break-all;">{{ $currencySymbol }} {{ number_format($totalValue ?? 0, 0) }}</p>
<p class="text-xs font-bold" style="margin-top:0.5rem;color:#5E5E5E;">Cost-based valuation</p>
</div>

</div>

{{-- Search & Filter --}}
<form method="GET" action="{{ route('inventory.dashboard') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3" style="border:1px solid #D3D8DE;padding:1rem 1.5rem;">
<div class="relative flex-1 min-w-0">
<span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined pointer-events-none" style="font-size:18px;color:#5E5E5E;">search</span>
<input class="w-full h-10 pl-10 pr-4 text-sm font-bold placeholder-gray-400 outline-none transition-all" style="background:transparent;border:1px solid #D3D8DE;border-radius:0;color:#2B3437;" name="search" type="text" placeholder="Search by name, Article #..." value="{{ request('search') }}" onfocus="this.style.borderColor='#5E5E5E';this.style.borderWidth='2px'" onblur="this.style.borderColor='#D3D8DE';this.style.borderWidth='1px'"/>
</div>
<div class="relative shrink-0 sm:w-48">
<select class="w-full h-10 pl-3 pr-10 text-sm font-bold outline-none appearance-none cursor-pointer transition-all" style="background:#FFFFFF;border:1px solid #D3D8DE;border-radius:0;color:#2B3437;" name="category_id" onfocus="this.style.borderColor='#5E5E5E';this.style.borderWidth='2px'" onblur="this.style.borderColor='#D3D8DE';this.style.borderWidth='1px'">
<option value="">All Categories</option>
<option value="all" {{ request('category_id') === 'all' ? 'selected' : '' }}>All Categories</option>
@foreach($categories ?? [] as $cat)
<option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
@endforeach
</select>
<span class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined pointer-events-none" style="font-size:18px;color:#5E5E5E;">expand_more</span>
</div>
<button type="submit" class="h-10 px-5 text-[11px] font-bold uppercase flex items-center justify-center gap-2 active:scale-[0.98] transition-all shrink-0" style="background:#5E5E5E;color:#F8F8F8;border-radius:0;letter-spacing:0.05em;">
<span class="material-symbols-outlined" style="font-size:14px;">filter_list</span>
FILTER
</button>
@if(request('search') || request('category_id'))
<a href="{{ route('inventory.dashboard') }}" class="h-10 px-4 text-[11px] font-bold uppercase flex items-center justify-center gap-1 transition-all shrink-0" style="color:#5E5E5E;border:1px solid #5E5E5E;border-radius:0;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">
CLEAR
</a>
@endif
</form>

{{-- Products Table --}}
<div style="border:1px solid #D3D8DE;">

<div class="overflow-x-auto">
<table class="w-full text-left border-collapse" style="min-width:700px;">
<thead>
<tr style="background:#F8F9FA;border-bottom:1px solid #D3D8DE;">
<th class="px-6 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Item</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Article #</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Category</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;">Stock</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;">Unit Price</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-center" style="letter-spacing:0.05em;color:#5E5E5E;">Status</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;">Actions</th>
</tr>
</thead>
<tbody>
@forelse($products ?? [] as $product)
<tr class="group transition-colors" style="border-top:1px solid #EAECEE;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<span class="material-symbols-outlined shrink-0" style="font-size:18px;color:#5E5E5E;">inventory_2</span>
<div class="min-w-0">
<p class="text-sm font-bold truncate" style="color:#2B3437;">{{ $product->name }}</p>
<p class="text-xs truncate" style="color:#5E5E5E;margin-top:0.125rem;">{{ Str::limit($product->description, 40) ?: '-' }}</p>
</div>
</div>
</td>
<td class="px-6 py-4">
<span class="text-sm font-bold tabular-nums" style="color:#5E5E5E;">{{ $product->sku }}</span>
</td>
<td class="px-6 py-4">
<span class="text-xs font-bold uppercase px-2 py-1" style="border:1px solid #D3D8DE;color:#5E5E5E;letter-spacing:0.05em;">{{ $product->category->name ?? '-' }}</span>
</td>
<td class="px-6 py-4 text-right">
<span class="text-sm font-black tabular-nums" style="color:{{ $product->stock_quantity <= 0 ? '#9F403D' : ($product->stock_quantity <= ($product->reorder_level ?? 10) ? '#9F403D' : '#2B3437') }};">{{ $product->stock_quantity }}</span>
</td>
<td class="px-6 py-4 text-right">
<span class="text-sm font-bold tabular-nums whitespace-nowrap" style="color:#2B3437;">{{ $currencySymbol }} {{ number_format($product->selling_price ?? $product->cost_price ?? 0, 2) }}</span>
</td>
<td class="px-6 py-4 text-center">
@if ($product->stock_quantity <= 0)
<span class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase px-2.5 py-1" style="border:1px solid #9F403D;color:#9F403D;letter-spacing:0.05em;">
Out of Stock
</span>
@elseif ($product->stock_quantity <= ($product->reorder_level ?? 10))
<span class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase px-2.5 py-1" style="border:1px solid #9F403D;color:#9F403D;letter-spacing:0.05em;">
Low Stock
</span>
@else
<span class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase px-2.5 py-1" style="border:1px solid #D3D8DE;color:#5E5E5E;letter-spacing:0.05em;">
In Stock
</span>
@endif
</td>
<td class="px-6 py-4 text-right">
@if(auth()->user()->role !== 'viewer')
<div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
<a href="{{ route('products.edit', $product) }}" class="p-1.5 transition-colors" style="color:#5E5E5E;" onmouseover="this.style.color='#2B3437';this.style.background='#F8F9FA'" onmouseout="this.style.color='#5E5E5E';this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:18px;">edit</span>
</a>
<form method="POST" action="{{ route('products.destroy', $product) }}" class="inline-flex" onsubmit="return confirm('Delete this product?');">
@csrf
@method('DELETE')
<button type="submit" class="p-1.5 transition-colors" style="color:#5E5E5E;" onmouseover="this.style.color='#9F403D'" onmouseout="this.style.color='#5E5E5E'">
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
<span class="material-symbols-outlined block" style="font-size:3rem;color:#D3D8DE;margin-bottom:1rem;">inventory_2</span>
<p class="text-sm font-bold" style="color:#5E5E5E;">No products found.</p>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('products.create') }}" class="inline-block text-[11px] font-bold uppercase" style="margin-top:1rem;color:#2B3437;border-bottom:2px solid #5E5E5E;padding-bottom:0.25rem;letter-spacing:0.05em;">Add First Product</a>
@endif
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

{{-- Pagination --}}
<div class="flex flex-col sm:flex-row items-center justify-between gap-4" style="padding:1rem 1.5rem;border-top:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-xs font-bold" style="color:#5E5E5E;">
@if(isset($products) && $products->total() > 0)
Showing <span style="color:#2B3437;">{{ $products->firstItem() }}</span> to <span style="color:#2B3437;">{{ $products->lastItem() }}</span> of <span style="color:#2B3437;">{{ $products->total() }}</span> results
@else
No results
@endif
</p>
@if(isset($products) && $products->hasPages())
<nav class="flex items-center gap-1" aria-label="Pagination">
@if (!$products->onFirstPage())
<a class="p-1.5 transition-colors" style="color:#5E5E5E;" href="{{ $products->previousPageUrl() }}" onmouseover="this.style.background='#EAECEE'" onmouseout="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:18px;">chevron_left</span>
</a>
@endif
@foreach ($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) ?: [1 => $products->url(1)] as $page => $url)
@if ($page == $products->currentPage())
<span class="px-3 py-1.5 text-xs font-bold" style="background:#5E5E5E;color:#F8F8F8;">{{ $page }}</span>
@else
<a class="px-3 py-1.5 text-xs font-bold transition-colors" style="color:#5E5E5E;" href="{{ $url }}" onmouseover="this.style.background='#EAECEE'" onmouseout="this.style.background='transparent'">{{ $page }}</a>
@endif
@endforeach
@if ($products->hasMorePages())
<a class="p-1.5 transition-colors" style="color:#5E5E5E;" href="{{ $products->nextPageUrl() }}" onmouseover="this.style.background='#EAECEE'" onmouseout="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:18px;">chevron_right</span>
</a>
@endif
</nav>
@endif
</div>

</div>

{{-- Footer --}}
<div class="text-center text-[10px] uppercase font-bold pb-4" style="margin-top:2rem;letter-spacing:0.05em;color:#5E5E5E;">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</div>

</div>
</div>
</main>

</body>
</html>
