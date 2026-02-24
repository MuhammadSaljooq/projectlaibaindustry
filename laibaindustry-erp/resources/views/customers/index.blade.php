<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Customers - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'customers'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-black border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<h2 class="text-xl font-bold text-slate-800 dark:text-white hidden sm:block">Customers</h2>
</div>
<div class="flex items-center gap-4">
<div class="relative hidden sm:block">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 material-symbols-outlined text-[20px]">search</span>
<input class="h-9 pl-10 pr-4 text-sm bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-black/50 dark:focus:ring-white/50 w-64 placeholder-slate-400 text-slate-700 dark:text-slate-200 transition-all" placeholder="Global search..." type="text" disabled>
</div>
<button class="p-2 text-slate-500 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 rounded-full relative transition-colors" type="button">
<span class="material-symbols-outlined">notifications</span>
<span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border border-white dark:border-[#1a2632]"></span>
</button>
</div>
</header>
<div class="flex-1 overflow-y-auto p-6 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-6">
<div class="sm:hidden">
<h2 class="text-2xl font-bold text-slate-800 dark:text-white">Customers</h2>
</div>

@if (session('success'))
<div class="rounded-lg border border-gray-300 bg-gray-100 dark:bg-gray-800/50 dark:border-gray-700 px-4 py-3 text-sm text-gray-800 dark:text-gray-300">
{{ session('success') }}
</div>
@endif
@if (session('error'))
<div class="rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-400">
{{ session('error') }}
</div>
@endif

<div class="bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col flex-1 min-h-[400px]">
<form method="GET" action="{{ route('customers.index') }}" class="p-5 border-b border-slate-200 dark:border-slate-700">
<div class="flex flex-col gap-4">
<div class="flex flex-wrap items-center justify-between gap-3">
<h3 class="text-base font-semibold text-slate-800 dark:text-white">Customers</h3>
@if(auth()->user()->role !== 'viewer')
<a class="order-3 sm:order-none h-10 px-4 bg-black hover:bg-gray-800 text-white text-sm font-bold rounded-lg inline-flex items-center justify-center gap-2 whitespace-nowrap transition-colors shadow-sm hover:shadow active:scale-[0.98] shrink-0" href="{{ route('customers.create') }}">
<span class="material-symbols-outlined text-[20px] shrink-0">add</span>
<span>Add Customer</span>
</a>
@endif
</div>
<div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-3">
<div class="relative flex-1 min-w-0 w-full sm:max-w-md">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 material-symbols-outlined text-[20px] group-focus-within:text-black dark:group-focus-within:text-white transition-colors pointer-events-none">search</span>
<input class="h-10 w-full min-w-0 pl-10 pr-4 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary placeholder-slate-400 text-slate-700 dark:text-slate-200 transition-all outline-none" name="search" type="text" placeholder="Search by name, code, email, phone..." value="{{ request('search') }}"/>
</div>
<div class="flex flex-wrap gap-2">
<button type="submit" class="h-10 px-4 bg-black hover:bg-gray-800 text-white text-sm font-bold rounded-lg inline-flex items-center justify-center gap-2 whitespace-nowrap transition-colors shrink-0">Search</button>
<a href="{{ route('customers.index') }}" class="h-10 px-4 inline-flex items-center justify-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600 whitespace-nowrap shrink-0">Clear</a>
</div>
</div>
</div>
</form>

<div class="overflow-x-auto w-full -mx-4 sm:mx-0">
<table class="w-full text-left border-collapse min-w-[500px]">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-[250px]">Name</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Contact</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email address</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-200 dark:divide-slate-700">
@forelse($customers as $customer)
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="h-10 w-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-slate-400">person</span>
</div>
<div>
<p class="text-sm font-semibold text-slate-900 dark:text-white group-hover:text-black dark:group-hover:text-white transition-colors">{{ $customer->customer_name }}</p>
<p class="text-xs text-slate-500 dark:text-slate-400">{{ $customer->customer_code }}</p>
</div>
</div>
</td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $customer->phone ?? '-' }}</td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $customer->email ?? '-' }}</td>
<td class="px-6 py-4 text-right">
<div class="flex items-center justify-end gap-2 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
<a class="p-1.5 text-slate-400 hover:text-black dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700 rounded-md transition-colors" href="{{ route('customers.statement', $customer) }}" title="View statement">
<span class="material-symbols-outlined text-[20px]">receipt_long</span>
</a>
@if(auth()->user()->role !== 'viewer')
<a class="p-1.5 text-slate-400 hover:text-black dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700 rounded-md transition-colors" href="{{ route('customers.edit', $customer) }}">
<span class="material-symbols-outlined text-[20px]">edit</span>
</a>
<form method="POST" action="{{ route('customers.destroy', $customer) }}" class="inline-flex" onsubmit="return confirm('Are you sure you want to delete this customer?');">
@csrf
@method('DELETE')
<button type="submit" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors">
<span class="material-symbols-outlined text-[20px]">delete</span>
</button>
</form>
@endif
</div>
</td>
</tr>
@empty
<tr>
<td colspan="4" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
No customers yet. @if(auth()->user()->role !== 'viewer')<a href="{{ route('customers.create') }}" class="text-black dark:text-white font-medium hover:underline">Add your first customer</a>@else<span>No customers recorded yet.</span>@endif
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

<div class="p-4 border-t border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-4">
<p class="text-sm text-slate-500 dark:text-slate-400">
@if($customers->total() > 0)
Showing <span class="font-medium text-slate-900 dark:text-white">{{ $customers->firstItem() }}</span> to <span class="font-medium text-slate-900 dark:text-white">{{ $customers->lastItem() }}</span> of <span class="font-medium text-slate-900 dark:text-white">{{ $customers->total() }}</span> results
@else
No results
@endif
</p>
@if($customers->hasPages())
<nav class="flex items-center gap-1" aria-label="Pagination">
@if (!$customers->onFirstPage())
<a class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors" href="{{ $customers->previousPageUrl() }}"><span class="material-symbols-outlined text-[20px]">chevron_left</span></a>
@endif
@foreach ($customers->getUrlRange(max(1, $customers->currentPage() - 2), min($customers->lastPage(), $customers->currentPage() + 2)) ?: [1 => $customers->url(1)] as $page => $url)
@if ($page == $customers->currentPage())
<span class="px-3 py-1.5 text-sm font-medium rounded-lg bg-black text-white dark:bg-white dark:text-black">{{ $page }}</span>
@else
<a class="px-3 py-1.5 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" href="{{ $url }}">{{ $page }}</a>
@endif
@endforeach
@if ($customers->hasMorePages())
<a class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors" href="{{ $customers->nextPageUrl() }}"><span class="material-symbols-outlined text-[20px]">chevron_right</span></a>
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
