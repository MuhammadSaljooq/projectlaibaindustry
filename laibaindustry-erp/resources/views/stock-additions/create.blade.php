<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Add Stock - Laiba Safety'])
@include('partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'stock_additions'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<a href="{{ route('stock-additions.index') }}" class="st-btn-secondary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
<span class="hidden sm:inline">Stock Additions</span>
</a>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-4xl mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col gap-1">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">STOCK_IN</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Add Stock</h1>
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
<form method="POST" action="{{ route('stock-additions.store') }}">
@csrf
<p class="st-label mb-6">Stock addition details</p>

<div class="space-y-5 max-w-xl">
<div>
<label class="st-label block mb-2" for="product_id">Product <span class="text-[#9F403D]">*</span></label>
<select class="st-select w-full h-10 pl-3 pr-12 text-sm cursor-pointer @error('product_id') !border-[#9F403D] @enderror" name="product_id" id="product_id" required>
<option value="" disabled {{ old('product_id') ? '' : 'selected' }}>Select product</option>
@foreach($products as $p)
<option value="{{ $p->id }}" {{ (string) old('product_id') === (string) $p->id ? 'selected' : '' }}>
{{ $p->name }}{{ $p->sku ? ' ('.$p->sku.')' : '' }} — stock: {{ number_format($p->stock_quantity) }}
</option>
@endforeach
</select>
@error('product_id')<p class="mt-1.5 text-xs text-[#9F403D] font-medium">{{ $message }}</p>@enderror
</div>

<div>
<label class="st-label block mb-2" for="date">Date <span class="text-[#9F403D]">*</span></label>
<input class="st-input w-full h-10 px-3 text-sm @error('date') !border-[#9F403D] @enderror" type="date" name="date" id="date" value="{{ old('date', now()->format('Y-m-d')) }}" required>
@error('date')<p class="mt-1.5 text-xs text-[#9F403D] font-medium">{{ $message }}</p>@enderror
</div>

<div>
<label class="st-label block mb-2" for="quantity">Quantity Added <span class="text-[#9F403D]">*</span></label>
<input class="st-input w-full h-10 px-3 text-sm tabular-nums @error('quantity') !border-[#9F403D] @enderror" type="number" name="quantity" id="quantity" value="{{ old('quantity') }}" min="1" step="1" placeholder="0" required>
@error('quantity')<p class="mt-1.5 text-xs text-[#9F403D] font-medium">{{ $message }}</p>@enderror
</div>

<div>
<label class="st-label block mb-2" for="unit_cost">Unit Cost <span class="text-[#586064] font-normal normal-case tracking-normal">(optional)</span></label>
<input class="st-input w-full h-10 px-3 text-sm tabular-nums @error('unit_cost') !border-[#9F403D] @enderror" type="number" name="unit_cost" id="unit_cost" value="{{ old('unit_cost') }}" min="0" step="0.01" placeholder="0.00">
@error('unit_cost')<p class="mt-1.5 text-xs text-[#9F403D] font-medium">{{ $message }}</p>@enderror
</div>

<div>
<label class="st-label block mb-2" for="reference">Reference / PO No. <span class="text-[#586064] font-normal normal-case tracking-normal">(optional)</span></label>
<input class="st-input w-full h-10 px-3 text-sm @error('reference') !border-[#9F403D] @enderror" type="text" name="reference" id="reference" value="{{ old('reference') }}" maxlength="255" placeholder="e.g. INV-2026-001">
@error('reference')<p class="mt-1.5 text-xs text-[#9F403D] font-medium">{{ $message }}</p>@enderror
</div>

<div>
<label class="st-label block mb-2" for="notes">Notes <span class="text-[#586064] font-normal normal-case tracking-normal">(optional)</span></label>
<textarea class="st-input w-full px-3 py-2 text-sm resize-none @error('notes') !border-[#9F403D] @enderror" name="notes" id="notes" rows="3" maxlength="1000" placeholder="Any additional notes…">{{ old('notes') }}</textarea>
@error('notes')<p class="mt-1.5 text-xs text-[#9F403D] font-medium">{{ $message }}</p>@enderror
</div>
</div>

<div class="flex flex-wrap items-center gap-3 mt-8 pt-6 border-t border-[#ABB3B7]">
<button type="submit" class="st-btn-primary h-10 px-5 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">save</span>
Save &amp; Update Inventory
</button>
<a href="{{ route('stock-additions.index') }}" class="st-btn-secondary h-10 px-5 inline-flex items-center gap-2">Cancel</a>
</div>
</form>
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-4 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
