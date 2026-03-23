<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Edit Customer - ERP'])
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
<a href="{{ route('customers.index') }}" class="st-btn-secondary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
<span class="hidden sm:inline">Customers</span>
</a>
</div>
<div class="flex items-center gap-2">
<a href="{{ route('customers.statement', $customer) }}" class="st-btn-secondary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">receipt_long</span>
<span class="hidden sm:inline">Statement</span>
</a>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-[900px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col gap-1">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">RECORD_AMEND_09</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Edit Customer</h1>
<p class="text-sm text-[#586064] mt-2 font-mono">{{ $customer->customer_name }} · {{ $customer->customer_code }}</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<div class="st-paper border border-[#ABB3B7] p-6 md:p-8 bg-white">
<p class="st-label mb-6">Customer details</p>

<form method="POST" action="{{ route('customers.update', $customer) }}">
@csrf
@method('PUT')
@include('customers.partials.form')

<div class="flex flex-wrap items-center gap-3 mt-8 pt-6 border-t border-[#ABB3B7]">
<button type="submit" class="st-btn-primary h-11 px-6 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">save</span>
Update customer
</button>
<a href="{{ route('customers.index') }}" class="st-btn-secondary h-11 px-6 inline-flex items-center">Cancel</a>
</div>
</form>
</div>

@if(auth()->user()->role !== 'viewer')
<div class="border border-[#9F403D] bg-white p-6 md:p-8">
<p class="st-label st-label--error mb-2">Hazard · delete</p>
<p class="text-xs text-[#586064] mb-5">Permanently remove this customer. This cannot be undone.</p>
<form method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('Are you sure? This action cannot be undone.');">
@csrf
@method('DELETE')
<button type="submit" class="h-11 px-6 inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider border border-[#9F403D] text-[#9F403D] bg-transparent hover:bg-[#F1F4F6]">
<span class="material-symbols-outlined text-[18px]">delete</span>
Delete customer
</button>
</form>
</div>
@endif

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-4 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
