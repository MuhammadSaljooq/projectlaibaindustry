<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Customers - Laiba Safety'])
<style>
body { background-color: #FFFFFF; color: #2B3437; font-family: 'Inter', sans-serif; color-scheme: light; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #2B3437; color: #FFFFFF; }
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'customers'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#FFFFFF;">

<header class="h-14 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 rounded-none" style="color:#5E5E5E;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
</div>
<div class="flex items-center gap-3">
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('customers.create') }}" class="h-9 px-5 text-[11px] font-bold uppercase inline-flex items-center gap-2 transition-all whitespace-nowrap" style="background:#5E5E5E;color:#F8F8F8;border-radius:0;letter-spacing:0.05em;" onmouseover="this.style.opacity='0.92'" onmouseout="this.style.opacity='1'">
<span class="material-symbols-outlined" style="font-size:16px;">add</span>
ADD CUSTOMER
</a>
@endif
</div>
</header>

<div class="flex-1 overflow-y-auto no-scrollbar scroll-smooth">
<div class="max-w-[1400px] mx-auto px-6 md:px-8 py-8 flex flex-col" style="gap:3rem;">

<div>
<div class="flex items-end justify-between" style="padding-bottom:0.75rem;border-bottom:2px solid #5E5E5E;">
<h1 class="font-bold" style="font-size:1.5rem;letter-spacing:-0.02em;color:#2B3437;">Customers</h1>
<span class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Section CST-01</span>
</div>
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-top:0.75rem;">Client Directory</p>
</div>

@if (session('success'))
<div style="border:1px solid #D3D8DE;padding:0.75rem 1.25rem;" class="text-sm font-bold flex items-center gap-3">
<span class="material-symbols-outlined" style="color:#5E5E5E;font-size:20px;">check_circle</span>
<span>{{ session('success') }}</span>
</div>
@endif
@if (session('error'))
<div style="border:1px solid #9F403D;padding:0.75rem 1.25rem;" class="text-sm font-bold flex items-center gap-3">
<span class="material-symbols-outlined" style="color:#9F403D;font-size:20px;">error</span>
<span style="color:#9F403D;">{{ session('error') }}</span>
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3" style="gap:1px;background:#D3D8DE;border:1px solid #D3D8DE;">
<div style="background:#FFFFFF;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">Total Customers</p>
<p class="text-2xl font-bold tabular-nums" style="color:#2B3437;">{{ $customers->total() }}</p>
</div>
<div style="background:#FFFFFF;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">This Page</p>
<p class="text-2xl font-bold tabular-nums" style="color:#2B3437;">{{ $customers->count() }}</p>
</div>
<div style="background:#F8F9FA;padding:2rem;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-bottom:1.25rem;">Active Records</p>
<p class="text-2xl font-bold tabular-nums" style="color:#2B3437;">{{ $customers->total() }}</p>
</div>
</div>

<div style="border:1px solid #D3D8DE;">
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse min-w-[600px]">
<thead>
<tr style="background:#F8F9FA;border-bottom:1px solid #D3D8DE;">
<th class="px-6 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Customer</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Code</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Contact</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Email</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;">Actions</th>
</tr>
</thead>
<tbody>
@forelse($customers as $customer)
<tr class="group transition-colors cursor-pointer" style="border-top:1px solid #EAECEE;" onmouseenter="this.style.background='#F8F9FA'" onmouseleave="this.style.background='transparent'" onclick="window.location='{{ route('customers.edit', $customer) }}'">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<span class="material-symbols-outlined shrink-0" style="color:#5E5E5E;font-size:20px;">person</span>
<p class="text-sm font-bold" style="color:#2B3437;">{{ $customer->customer_name }}</p>
</div>
</td>
<td class="px-6 py-4">
<span class="text-xs font-bold px-2 py-1 tabular-nums" style="border:1px solid #D3D8DE;color:#5E5E5E;letter-spacing:0.03em;">{{ $customer->customer_code }}</span>
</td>
<td class="px-6 py-4 text-sm font-bold" style="color:#5E5E5E;">{{ $customer->phone ?? '—' }}</td>
<td class="px-6 py-4 text-sm font-bold" style="color:#5E5E5E;">{{ $customer->email ?? '—' }}</td>
<td class="px-6 py-4 text-right" onclick="event.stopPropagation();">
<div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
<a href="{{ route('customers.statement', $customer) }}" class="p-2 transition-colors" style="color:#5E5E5E;" onmouseenter="this.style.color='#2B3437';this.style.background='#EAECEE'" onmouseleave="this.style.color='#5E5E5E';this.style.background='transparent'" title="Statement">
<span class="material-symbols-outlined" style="font-size:18px;">receipt_long</span>
</a>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('customers.edit', $customer) }}" class="p-2 transition-colors" style="color:#5E5E5E;" onmouseenter="this.style.color='#2B3437';this.style.background='#EAECEE'" onmouseleave="this.style.color='#5E5E5E';this.style.background='transparent'" title="Edit">
<span class="material-symbols-outlined" style="font-size:18px;">edit</span>
</a>
<form method="POST" action="{{ route('customers.destroy', $customer) }}" class="inline-flex" onsubmit="return confirm('Delete this customer?');">
@csrf
@method('DELETE')
<button type="submit" class="p-2 transition-colors" style="color:#5E5E5E;" onmouseenter="this.style.color='#9F403D'" onmouseleave="this.style.color='#5E5E5E'" title="Delete">
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
<span class="material-symbols-outlined block mb-3 mx-auto" style="font-size:40px;color:#D3D8DE;">group_off</span>
<p class="text-sm font-bold mb-1" style="color:#5E5E5E;">No customers yet</p>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('customers.create') }}" class="text-[11px] font-bold uppercase" style="color:#2B3437;border-bottom:2px solid #5E5E5E;padding-bottom:0.25rem;letter-spacing:0.05em;">Add your first customer</a>
@endif
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

@if($customers->hasPages())
<div class="flex items-center justify-between px-6 py-4" style="border-top:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-xs font-bold" style="color:#5E5E5E;">
Showing <span style="color:#2B3437;">{{ $customers->firstItem() }}</span>–<span style="color:#2B3437;">{{ $customers->lastItem() }}</span> of <span style="color:#2B3437;">{{ $customers->total() }}</span>
</p>
<nav class="flex items-center gap-1" aria-label="Pagination">
@if (!$customers->onFirstPage())
<a class="p-1.5 transition-colors" style="color:#5E5E5E;" href="{{ $customers->previousPageUrl() }}" onmouseenter="this.style.background='#EAECEE'" onmouseleave="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:18px;">chevron_left</span>
</a>
@endif
@foreach ($customers->getUrlRange(max(1, $customers->currentPage() - 2), min($customers->lastPage(), $customers->currentPage() + 2)) ?: [1 => $customers->url(1)] as $page => $url)
@if ($page == $customers->currentPage())
<span class="w-8 h-8 flex items-center justify-center text-xs font-bold" style="background:#5E5E5E;color:#F8F8F8;">{{ $page }}</span>
@else
<a class="w-8 h-8 flex items-center justify-center text-xs font-bold transition-colors" style="color:#5E5E5E;" href="{{ $url }}" onmouseenter="this.style.background='#EAECEE'" onmouseleave="this.style.background='transparent'">{{ $page }}</a>
@endif
@endforeach
@if ($customers->hasMorePages())
<a class="p-1.5 transition-colors" style="color:#5E5E5E;" href="{{ $customers->nextPageUrl() }}" onmouseenter="this.style.background='#EAECEE'" onmouseleave="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:18px;">chevron_right</span>
</a>
@endif
</nav>
</div>
@else
<div class="px-6 py-4" style="border-top:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-xs font-bold" style="color:#5E5E5E;">
@if($customers->total() > 0)
Showing all <span style="color:#2B3437;">{{ $customers->total() }}</span> results
@else
No results
@endif
</p>
</div>
@endif
</div>

<footer class="pb-8 text-center">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</p>
</footer>

</div>
</div>

</main>
</body>
</html>
