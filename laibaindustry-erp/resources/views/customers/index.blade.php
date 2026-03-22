<!DOCTYPE html>
<html lang="en" class="dark">
<head>
@include('partials.frontend-head', ['title' => 'Customers - Laiba Safety'])
<style>
body { background-color: #131313; color: #e2e2e2; font-family: 'Inter', sans-serif; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #FFFFFF; color: #131313; }
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'customers'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#131313;">

<header class="h-16 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="background:#1B1B1B;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 hover:text-white rounded-md" style="color:#8e9192;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
</div>
<div class="flex items-center gap-3">
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('customers.create') }}" class="h-10 px-5 inline-flex items-center gap-2 text-sm font-bold rounded-md transition-all duration-200 whitespace-nowrap" style="background:#FFFFFF;color:#2F3131;" onmouseenter="this.style.background='#C6C6C7'" onmouseleave="this.style.background='#FFFFFF'">
<span class="material-symbols-outlined" style="font-size:18px;">add</span>
ADD CUSTOMER
</a>
@endif
</div>
</header>

<div class="flex-1 overflow-y-auto no-scrollbar">
<div class="max-w-[1400px] mx-auto px-6 md:px-8 py-8 flex flex-col gap-8">

<div>
<p class="text-xs font-medium uppercase tracking-[0.15em] mb-2" style="color:#8e9192;">Client Directory</p>
<h1 class="text-4xl font-bold tracking-tight" style="color:#FFFFFF;letter-spacing:-0.02em;">Customers</h1>
</div>

@if (session('success'))
<div class="flex items-center gap-3 px-5 py-3.5 rounded-md" style="background:rgba(255,255,255,0.04);">
<span class="material-symbols-outlined" style="color:#FFFFFF;font-size:20px;">check_circle</span>
<span class="text-sm font-medium" style="color:#C4C7C8;">{{ session('success') }}</span>
</div>
@endif
@if (session('error'))
<div class="flex items-center gap-3 px-5 py-3.5 rounded-md" style="background:rgba(255,180,171,0.06);">
<span class="material-symbols-outlined" style="color:#FFB4AB;font-size:20px;">error</span>
<span class="text-sm font-medium" style="color:#FFB4AB;">{{ session('error') }}</span>
</div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
<div class="rounded-lg p-5" style="background:#1B1B1B;">
<p class="text-xs font-medium uppercase tracking-[0.15em] mb-3" style="color:#8e9192;">Total Customers</p>
<p class="text-2xl font-bold" style="color:#FFFFFF;">{{ $customers->total() }}</p>
</div>
<div class="rounded-lg p-5" style="background:#1B1B1B;">
<p class="text-xs font-medium uppercase tracking-[0.15em] mb-3" style="color:#8e9192;">This Page</p>
<p class="text-2xl font-bold" style="color:#FFFFFF;">{{ $customers->count() }}</p>
</div>
<div class="rounded-lg p-5 relative overflow-hidden" style="background:#FFFFFF;">
<p class="text-xs font-medium uppercase tracking-[0.15em] mb-3" style="color:#666;">Active Records</p>
<p class="text-2xl font-bold" style="color:#131313;">{{ $customers->total() }}</p>
<div class="absolute top-4 right-4">
<span class="material-symbols-outlined" style="font-size:32px;color:rgba(19,19,19,0.08);">group</span>
</div>
</div>
</div>

<div class="rounded-lg overflow-hidden" style="background:#1B1B1B;">
<div class="overflow-x-auto">
<table class="w-full text-left min-w-[600px]">
<thead>
<tr style="background:#0E0E0E;">
<th class="px-6 py-3.5 text-[10px] font-semibold uppercase tracking-[0.15em]" style="color:#8e9192;">Customer</th>
<th class="px-6 py-3.5 text-[10px] font-semibold uppercase tracking-[0.15em]" style="color:#8e9192;">Code</th>
<th class="px-6 py-3.5 text-[10px] font-semibold uppercase tracking-[0.15em]" style="color:#8e9192;">Contact</th>
<th class="px-6 py-3.5 text-[10px] font-semibold uppercase tracking-[0.15em]" style="color:#8e9192;">Email</th>
<th class="px-6 py-3.5 text-[10px] font-semibold uppercase tracking-[0.15em] text-right" style="color:#8e9192;">Actions</th>
</tr>
</thead>
<tbody>
@forelse($customers as $customer)
<tr class="group transition-colors duration-150 cursor-pointer" style="border-bottom:1px solid rgba(68,71,72,0.15);" onmouseenter="this.style.background='#2A2A2A'" onmouseleave="this.style.background='transparent'" onclick="window.location='{{ route('customers.edit', $customer) }}'">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="h-9 w-9 rounded flex items-center justify-center shrink-0" style="background:#353535;">
<span class="material-symbols-outlined" style="color:#8e9192;font-size:18px;">person</span>
</div>
<p class="text-sm font-semibold" style="color:#FFFFFF;">{{ $customer->customer_name }}</p>
</div>
</td>
<td class="px-6 py-4">
<span class="text-xs font-mono px-2 py-1 rounded" style="background:#353535;color:#C4C7C8;">{{ $customer->customer_code }}</span>
</td>
<td class="px-6 py-4 text-sm" style="color:#C4C7C8;">{{ $customer->phone ?? '—' }}</td>
<td class="px-6 py-4 text-sm" style="color:#C4C7C8;">{{ $customer->email ?? '—' }}</td>
<td class="px-6 py-4 text-right" onclick="event.stopPropagation();">
<div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
<a href="{{ route('customers.statement', $customer) }}" class="p-2 rounded-md transition-colors duration-150" style="color:#8e9192;" onmouseenter="this.style.color='#FFFFFF';this.style.background='rgba(255,255,255,0.06)'" onmouseleave="this.style.color='#8e9192';this.style.background='transparent'" title="Statement">
<span class="material-symbols-outlined" style="font-size:18px;">receipt_long</span>
</a>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('customers.edit', $customer) }}" class="p-2 rounded-md transition-colors duration-150" style="color:#8e9192;" onmouseenter="this.style.color='#FFFFFF';this.style.background='rgba(255,255,255,0.06)'" onmouseleave="this.style.color='#8e9192';this.style.background='transparent'" title="Edit">
<span class="material-symbols-outlined" style="font-size:18px;">edit</span>
</a>
<form method="POST" action="{{ route('customers.destroy', $customer) }}" class="inline-flex" onsubmit="return confirm('Delete this customer?');">
@csrf
@method('DELETE')
<button type="submit" class="p-2 rounded-md transition-colors duration-150" style="color:#8e9192;" onmouseenter="this.style.color='#FFB4AB';this.style.background='rgba(255,180,171,0.06)'" onmouseleave="this.style.color='#8e9192';this.style.background='transparent'" title="Delete">
<span class="material-symbols-outlined" style="font-size:18px;">delete</span>
</button>
</form>
@endif
</div>
</td>
</tr>
@empty
<tr>
<td colspan="5" class="px-6 py-16 text-center">
<span class="material-symbols-outlined block mb-3 mx-auto" style="font-size:40px;color:#353535;">group_off</span>
<p class="text-sm mb-1" style="color:#8e9192;">No customers yet</p>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('customers.create') }}" class="text-sm font-medium hover:underline" style="color:#FFFFFF;">Add your first customer</a>
@endif
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

@if($customers->hasPages())
<div class="flex items-center justify-between px-6 py-4" style="background:#0E0E0E;">
<p class="text-xs" style="color:#8e9192;">
Showing <span class="font-medium" style="color:#FFFFFF;">{{ $customers->firstItem() }}</span>–<span class="font-medium" style="color:#FFFFFF;">{{ $customers->lastItem() }}</span> of <span class="font-medium" style="color:#FFFFFF;">{{ $customers->total() }}</span>
</p>
<nav class="flex items-center gap-1" aria-label="Pagination">
@if (!$customers->onFirstPage())
<a class="p-1.5 rounded-md transition-colors" style="color:#C4C7C8;" href="{{ $customers->previousPageUrl() }}" onmouseenter="this.style.background='#2A2A2A'" onmouseleave="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:18px;">chevron_left</span>
</a>
@endif
@foreach ($customers->getUrlRange(max(1, $customers->currentPage() - 2), min($customers->lastPage(), $customers->currentPage() + 2)) ?: [1 => $customers->url(1)] as $page => $url)
@if ($page == $customers->currentPage())
<span class="w-8 h-8 flex items-center justify-center text-xs font-bold rounded-md" style="background:#FFFFFF;color:#131313;">{{ $page }}</span>
@else
<a class="w-8 h-8 flex items-center justify-center text-xs font-medium rounded-md transition-colors" style="color:#C4C7C8;" href="{{ $url }}" onmouseenter="this.style.background='#2A2A2A'" onmouseleave="this.style.background='transparent'">{{ $page }}</a>
@endif
@endforeach
@if ($customers->hasMorePages())
<a class="p-1.5 rounded-md transition-colors" style="color:#C4C7C8;" href="{{ $customers->nextPageUrl() }}" onmouseenter="this.style.background='#2A2A2A'" onmouseleave="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:18px;">chevron_right</span>
</a>
@endif
</nav>
</div>
@else
<div class="px-6 py-4" style="background:#0E0E0E;">
<p class="text-xs" style="color:#8e9192;">
@if($customers->total() > 0)
Showing all <span class="font-medium" style="color:#FFFFFF;">{{ $customers->total() }}</span> results
@else
No results
@endif
</p>
</div>
@endif
</div>

<footer class="pt-4 pb-8 text-center">
<p class="text-xs" style="color:rgba(142,145,146,0.4);">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</p>
</footer>

</div>
</div>

</main>
</body>
</html>
