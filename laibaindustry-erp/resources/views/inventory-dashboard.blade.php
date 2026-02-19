<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Inventory Management - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'products'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-[#1a2632] border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<h2 class="text-xl font-bold text-slate-800 dark:text-white hidden sm:block">Inventory Management</h2>
</div>
</header>
<div class="flex-1 overflow-y-auto p-6 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-6">
<div class="sm:hidden">
<h2 class="text-2xl font-bold text-slate-800 dark:text-white">Inventory Management</h2>
</div>

@if (session('success'))
<div class="rounded-lg border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/20 dark:border-emerald-800 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-400">
{{ session('success') }}
</div>
@endif
@if (session('error'))
<div class="rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-400">
{{ session('error') }}
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm hover:shadow-md transition-shadow">
<div class="flex justify-between items-start mb-4">
<div>
<p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Items</p>
<h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($totalItems ?? 0) }}</h3>
</div>
<div class="p-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg">
<span class="material-symbols-outlined">inventory_2</span>
</div>
</div>
</div>
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm hover:shadow-md transition-shadow">
<div class="flex justify-between items-start mb-4">
<div>
<p class="text-sm font-medium text-slate-500 dark:text-slate-400">Low Stock Alerts</p>
<h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($lowStockCount ?? 0) }}</h3>
</div>
<div class="p-2 bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 rounded-lg">
<span class="material-symbols-outlined">warning</span>
</div>
</div>
</div>
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm hover:shadow-md transition-shadow">
<div class="flex justify-between items-start mb-4">
<div>
<p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Inventory Value</p>
<h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-1">${{ number_format($totalValue ?? 0, 2) }}</h3>
</div>
<div class="p-2 bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 rounded-lg">
<span class="material-symbols-outlined">attach_money</span>
</div>
</div>
</div>
</div>

<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col flex-1 min-h-[500px]">
<form method="GET" action="{{ route('inventory.dashboard') }}" class="p-5 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
<div class="flex flex-col sm:flex-row gap-3 flex-1 min-w-0">
<div class="relative group min-w-0 sm:min-w-[200px]">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 material-symbols-outlined text-[20px] group-focus-within:text-primary transition-colors pointer-events-none">search</span>
<input class="h-10 pl-10 pr-4 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary w-full sm:w-64 placeholder-slate-400 text-slate-700 dark:text-slate-200 transition-all outline-none" name="search" type="text" placeholder="Search inventory..." value="{{ request('search') }}"/>
</div>
<div class="relative shrink-0">
<select class="h-10 pl-3 pr-10 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-slate-700 dark:text-slate-200 outline-none appearance-none cursor-pointer w-full sm:w-auto min-w-[160px]" name="category_id">
<option value="">Filter by Category</option>
<option value="all" {{ request('category_id') === 'all' ? 'selected' : '' }}>All Categories</option>
@foreach($categories ?? [] as $cat)
<option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
@endforeach
</select>
<span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 material-symbols-outlined text-[20px] pointer-events-none">expand_more</span>
</div>
</div>
<div class="flex flex-wrap gap-2 shrink-0">
<button type="submit" class="h-10 px-4 bg-primary hover:bg-blue-600 text-white text-sm font-bold rounded-lg inline-flex items-center justify-center gap-2 whitespace-nowrap transition-colors">Search</button>
<a href="{{ route('inventory.dashboard') }}" class="h-10 px-4 inline-flex items-center justify-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 whitespace-nowrap">Clear</a>
<a href="{{ route('products.create') }}" class="h-10 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg inline-flex items-center justify-center gap-2 whitespace-nowrap transition-colors shadow-sm hover:shadow active:scale-95 shrink-0">
<span class="material-symbols-outlined text-[20px] shrink-0">add</span>
<span>Add New Item</span>
</a>
</div>
</form>
<div class="overflow-x-auto w-full -mx-4 sm:mx-0">
<table class="w-full text-left border-collapse min-w-[640px]">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-[350px]">Item Name</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">SKU</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Category</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Stock Level</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Unit Price</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">Status</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-200 dark:divide-slate-700">
@forelse($products ?? [] as $product)
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="h-10 w-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-slate-400">inventory_2</span>
</div>
<div>
<p class="text-sm font-semibold text-slate-900 dark:text-white group-hover:text-primary transition-colors">{{ $product->name }}</p>
<p class="text-xs text-slate-500 dark:text-slate-400">{{ Str::limit($product->description, 30) ?: '-' }}</p>
</div>
</div>
</td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 font-medium">{{ $product->sku }}</td>
<td class="px-6 py-4">
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300">{{ $product->category->name ?? '-' }}</span>
</td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 font-mono text-right">{{ $product->stock_quantity }}</td>
<td class="px-6 py-4 text-sm text-slate-900 dark:text-white font-bold font-mono text-right">${{ number_format($product->selling_price ?? $product->cost_price ?? 0, 2) }}</td>
<td class="px-6 py-4 text-center">
@if ($product->stock_quantity <= 0)
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-400 border border-slate-300 dark:border-slate-600"><span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-1.5"></span>Out of Stock</span>
@elseif ($product->stock_quantity <= ($product->reorder_level ?? 10))
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400 border border-orange-200 dark:border-orange-800"><span class="w-1.5 h-1.5 rounded-full bg-orange-500 mr-1.5"></span>Low Stock</span>
@else
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>In Stock</span>
@endif
</td>
<td class="px-6 py-4 text-right">
<div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
<a href="{{ route('products.edit', $product) }}" class="p-1.5 text-slate-400 hover:text-primary hover:bg-slate-100 dark:hover:bg-slate-700 rounded-md transition-colors"><span class="material-symbols-outlined text-[20px]">edit</span></a>
<form method="POST" action="{{ route('products.destroy', $product) }}" class="inline-flex" onsubmit="return confirm('Are you sure you want to delete this product?');">
@csrf
@method('DELETE')
<button type="submit" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"><span class="material-symbols-outlined text-[20px]">delete</span></button>
</form>
</div>
</td>
</tr>
@empty
<tr>
<td colspan="7" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
<span class="material-symbols-outlined text-4xl mb-2 block">inventory_2</span>
No products yet. <a href="{{ route('products.create') }}" class="text-primary font-medium hover:underline">Add your first product</a>
</td>
</tr>
@endforelse
</tbody>
</table>
</div>
<div class="p-4 border-t border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-4">
<p class="text-sm text-slate-500 dark:text-slate-400">
@if(isset($products) && $products->total() > 0)
Showing <span class="font-medium text-slate-900 dark:text-white">{{ $products->firstItem() }}</span> to <span class="font-medium text-slate-900 dark:text-white">{{ $products->lastItem() }}</span> of <span class="font-medium text-slate-900 dark:text-white">{{ $products->total() }}</span> results
@else
No results
@endif
</p>
@if(isset($products) && $products->hasPages())
<nav class="flex items-center gap-1" aria-label="Pagination">
@if (!$products->onFirstPage())
<a class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors" href="{{ $products->previousPageUrl() }}"><span class="material-symbols-outlined text-[20px]">chevron_left</span></a>
@endif
@foreach ($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) ?: [1 => $products->url(1)] as $page => $url)
@if ($page == $products->currentPage())
<span class="px-3 py-1.5 text-sm font-medium rounded-lg bg-primary text-white">{{ $page }}</span>
@else
<a class="px-3 py-1.5 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" href="{{ $url }}">{{ $page }}</a>
@endif
@endforeach
@if ($products->hasMorePages())
<a class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors" href="{{ $products->nextPageUrl() }}"><span class="material-symbols-outlined text-[20px]">chevron_right</span></a>
@endif
</nav>
@endif
</div>
</div>
</div>
<div class="mt-8 text-center text-xs text-slate-400 pb-4">© {{ date('Y') }} Nexus ERP Inc. All rights reserved.</div>
</div>
</main>
</body>
</html>
