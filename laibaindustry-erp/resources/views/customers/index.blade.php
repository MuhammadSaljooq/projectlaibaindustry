<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Customers - ERP'])
@include('partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'customers'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">Customers</h2>
</div>
<div class="flex items-center gap-2 flex-wrap justify-end">
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('customers.create') }}" class="st-btn-primary h-10 px-5 inline-flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[20px]">add</span>
Add customer
</a>
@endif
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Customers</h1>
</div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] lg:text-right shrink-0">Directory · @if(auth()->user()->role !== 'viewer') click row to edit @else statement from actions @endif</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<div class="sm:hidden">
<p class="st-label">Module</p>
<p class="text-xl font-black uppercase tracking-tight text-[#2B3437]">Customers</p>
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
<p class="st-label mb-2">Total customers</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $totalCustomers ?? $customers->count() }}</p>
</div>
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">{{ !empty($search) ? 'Matching search' : 'Listed' }}</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $customers->count() }}</p>
</div>
<div class="p-6 border-2 border-[#5E5E5E] max-md:m-0 -m-px">
<p class="st-label st-label--primary mb-2">Search</p>
<p class="text-sm font-mono text-[#5E5E5E] break-all">{{ !empty($search) ? $search : '—' }}</p>
</div>
</div>

<form method="GET" action="{{ route('customers.index') }}" class="flex flex-wrap items-end gap-4 p-5 bg-[#F8F9FA] border border-[#ABB3B7]">
<div class="flex-1 min-w-[200px]">
<label class="st-label block mb-2" for="c-search">Search</label>
<div class="relative">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#586064] material-symbols-outlined text-[18px] pointer-events-none">search</span>
<input class="st-input w-full h-10 pl-10 pr-3 text-sm" id="c-search" type="text" name="search" value="{{ $search ?? request('search') }}" placeholder="Customer name or code…" autocomplete="off">
</div>
</div>
<div class="flex flex-wrap gap-2">
<button type="submit" class="st-btn-primary h-10 px-4 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[16px]">filter_list</span>
Filter
</button>
@if(($search ?? '') !== '')
<a href="{{ route('customers.index') }}" class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[16px]">close</span>
Clear
</a>
@endif
</div>
</form>

<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[320px]">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">Customer ledger</h3>
<p class="text-[11px] text-[#586064] mt-1">Name, code, contact · statement and edit from actions column</p>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[600px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3">Customer</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Code</th>
<th class="st-th px-4 py-3">Contact</th>
<th class="st-th px-4 py-3">Email</th>
<th class="st-th px-4 py-3 text-right w-44">Actions</th>
</tr>
</thead>
<tbody>
@forelse($customers as $customer)
<tr class="st-tr @if(auth()->user()->role !== 'viewer') cursor-pointer focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#5E5E5E] @endif" @if(auth()->user()->role !== 'viewer') data-customer-edit-url="{{ route('customers.edit', $customer) }}" role="link" tabindex="0" aria-label="Edit {{ e($customer->customer_name) }}" @endif>
<td class="st-td px-4 py-3">
<div class="flex items-center gap-3">
<div class="h-9 w-9 shrink-0 border border-[#ABB3B7] bg-[#EAEFF1] flex items-center justify-center">
<span class="material-symbols-outlined text-[#586064] text-[18px]">person</span>
</div>
<p class="text-sm font-bold text-[#2B3437]">{{ $customer->customer_name }}</p>
</div>
</td>
<td class="st-td px-4 py-3">
<span class="text-xs font-mono px-2 py-1 border border-[#ABB3B7] bg-[#F8F9FA] text-[#586064]">{{ $customer->customer_code }}</span>
</td>
<td class="st-td px-4 py-3 text-sm text-[#586064]">{{ $customer->phone ?? '—' }}</td>
<td class="st-td px-4 py-3 text-sm text-[#586064]">{{ $customer->email ?? '—' }}</td>
<td class="st-td px-4 py-3 text-right" data-stop-row-nav>
<div class="flex items-center justify-end gap-1 flex-wrap">
<a href="{{ route('customers.statement', $customer) }}" class="p-2 border border-transparent hover:border-[#ABB3B7] text-[#586064] hover:text-[#2B3437] hover:bg-[#F1F4F6]" title="Statement">
<span class="material-symbols-outlined text-[18px]">receipt_long</span>
</a>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('customers.edit', $customer) }}" class="p-2 border border-transparent hover:border-[#ABB3B7] text-[#586064] hover:text-[#2B3437] hover:bg-[#F1F4F6]" title="Edit">
<span class="material-symbols-outlined text-[18px]">edit</span>
</a>
<form method="POST" action="{{ route('customers.destroy', $customer) }}" class="inline-flex" data-confirm-delete="{{ e('Delete this customer?') }}">
@csrf
@method('DELETE')
<button type="submit" class="p-2 border border-transparent hover:border-[#9F403D] text-[#586064] hover:text-[#9F403D] hover:bg-[#F1F4F6]" title="Delete">
<span class="material-symbols-outlined text-[18px]">delete</span>
</button>
</form>
@endif
</div>
</td>
</tr>
@empty
<tr>
<td colspan="5" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No customers yet</p>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('customers.create') }}" class="text-[#5E5E5E] font-bold underline underline-offset-2">Add your first customer</a>
@endif
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

<div class="p-4 border-t border-[#ABB3B7] bg-[#F8F9FA]">
<p class="text-xs text-[#586064] uppercase tracking-wide">
@if($customers->count() > 0)
Showing <span class="font-bold text-[#2B3437] tabular-nums">{{ $customers->count() }}</span> @if(!empty($search))matching "{{ $search }}" @endif customer{{ $customers->count() === 1 ? '' : 's' }}
@else
@if(!empty($search))
No customers match "{{ $search }}". <a href="{{ route('customers.index') }}" class="font-bold text-[#5E5E5E] underline underline-offset-2">Clear search</a>
@else
No results
@endif
@endif
</p>
</div>
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-6 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
<script>
(function () {
    document.querySelectorAll('tr[data-customer-edit-url]').forEach(function (row) {
        row.addEventListener('click', function (e) {
            if (e.target.closest('[data-stop-row-nav], a, button, form')) return;
            var url = row.getAttribute('data-customer-edit-url');
            if (url) window.location.href = url;
        });
        row.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            if (e.target.closest('[data-stop-row-nav], a, button, input, textarea, select')) return;
            e.preventDefault();
            var url = row.getAttribute('data-customer-edit-url');
            if (url) window.location.href = url;
        });
    });
})();
</script>
</body>
</html>
