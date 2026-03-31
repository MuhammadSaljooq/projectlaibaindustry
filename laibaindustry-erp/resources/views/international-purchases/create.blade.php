<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Add international purchase - ERP'])
@include('partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'international_purchases'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<a href="{{ route('international-purchases.index') }}" class="st-btn-secondary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
<span class="hidden sm:inline">International purchases</span>
</a>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-4xl mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col gap-1">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">INT_PURCH_01</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">New international purchase</h1>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

@if ($errors->any())
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D]">
<p class="st-label st-label--error mb-2">Please fix the following</p>
<ul class="list-disc list-inside space-y-0.5">
@foreach ($errors->all() as $err)
<li>{{ $err }}</li>
@endforeach
</ul>
</div>
@endif

<div class="st-paper border border-[#ABB3B7] p-6 md:p-8 bg-white">
<form method="POST" action="{{ route('international-purchases.store') }}">
@csrf
<p class="st-label mb-6">Line item</p>

<div class="space-y-5 max-w-xl">
<div>
<label class="st-label block mb-2" for="supplier_id">Supplier</label>
<select class="st-input w-full h-10 px-3 text-sm" name="supplier_id" id="supplier_id">
<option value="">— None —</option>
@foreach($suppliers as $s)
<option value="{{ $s->id }}" @selected((string) old('supplier_id') === (string) $s->id)>{{ $s->name }}</option>
@endforeach
</select>
<p class="text-[11px] text-[#586064] mt-1"><a href="{{ route('suppliers.create') }}" class="text-[#5E5E5E] font-bold underline underline-offset-2">Add a supplier</a> if missing.</p>
</div>
<div>
<label class="st-label block mb-2" for="date">Date</label>
<input class="st-input w-full h-10 px-3 text-sm" type="date" name="date" id="date" value="{{ old('date', now()->format('Y-m-d')) }}" required>
</div>
<div>
<label class="st-label block mb-2" for="product_name">Product</label>
<input class="st-input w-full h-10 px-3 text-sm" type="text" name="product_name" id="product_name" value="{{ old('product_name') }}" placeholder="Product name or description" required maxlength="255">
</div>
<div>
<label class="st-label block mb-2" for="quantity">Qty</label>
<input class="st-input w-full h-10 px-3 text-sm tabular-nums" type="number" name="quantity" id="quantity" value="{{ old('quantity', '1') }}" step="1" min="1" required>
</div>
<div>
<label class="st-label block mb-2" for="unit_price">Unit price</label>
<input class="st-input w-full h-10 px-3 text-sm tabular-nums" type="number" name="unit_price" id="unit_price" value="{{ old('unit_price') }}" step="0.01" min="0" placeholder="0.00" required>
<p class="text-[11px] text-[#586064] mt-1">Total is calculated as qty × unit price.</p>
</div>
</div>

<div class="flex flex-wrap items-center gap-3 mt-8 pt-6 border-t border-[#ABB3B7]">
<button type="submit" class="st-btn-primary h-10 px-5 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">save</span>
Save
</button>
<a href="{{ route('international-purchases.index') }}" class="st-btn-secondary h-10 px-5 inline-flex items-center gap-2">Cancel</a>
</div>
</form>
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-4 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
