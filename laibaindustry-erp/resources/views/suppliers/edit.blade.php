<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Edit supplier - ERP'])
@include('partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'suppliers'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<a href="{{ route('suppliers.index') }}" class="st-btn-secondary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
<span class="hidden sm:inline">Suppliers</span>
</a>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-4xl mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col gap-1">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">SUP_02</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Edit supplier</h1>
<p class="text-sm text-[#586064] mt-2">{{ $supplier->name }}</p>
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
<form method="POST" action="{{ route('suppliers.update', $supplier) }}">
@csrf
@method('PUT')
<p class="st-label mb-6">Vendor details</p>

<div class="space-y-5 max-w-xl">
<div>
<label class="st-label block mb-2" for="name">Name</label>
<input class="st-input w-full h-10 px-3 text-sm" type="text" name="name" id="name" value="{{ old('name', $supplier->name) }}" required maxlength="255">
</div>
<div>
<label class="st-label block mb-2" for="country">Country</label>
<input class="st-input w-full h-10 px-3 text-sm" type="text" name="country" id="country" value="{{ old('country', $supplier->country) }}" maxlength="128">
</div>
<div>
<label class="st-label block mb-2" for="contact_name">Contact name</label>
<input class="st-input w-full h-10 px-3 text-sm" type="text" name="contact_name" id="contact_name" value="{{ old('contact_name', $supplier->contact_name) }}" maxlength="255">
</div>
<div>
<label class="st-label block mb-2" for="phone">Phone</label>
<input class="st-input w-full h-10 px-3 text-sm" type="text" name="phone" id="phone" value="{{ old('phone', $supplier->phone) }}" maxlength="64">
</div>
<div>
<label class="st-label block mb-2" for="email">Email</label>
<input class="st-input w-full h-10 px-3 text-sm" type="email" name="email" id="email" value="{{ old('email', $supplier->email) }}" maxlength="255">
</div>
<div>
<label class="st-label block mb-2" for="notes">Notes</label>
<textarea class="st-input w-full min-h-[100px] px-3 py-2 text-sm" name="notes" id="notes">{{ old('notes', $supplier->notes) }}</textarea>
</div>
</div>

<div class="flex flex-wrap items-center gap-3 mt-8 pt-6 border-t border-[#ABB3B7]">
<button type="submit" class="st-btn-primary h-10 px-5 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">save</span>
Update
</button>
<a href="{{ route('suppliers.index') }}" class="st-btn-secondary h-10 px-5 inline-flex items-center gap-2">Cancel</a>
</div>
</form>
</div>

<div class="border border-[#9F403D] bg-white p-6">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
<div>
<p class="st-label st-label--error mb-1">Hazard · delete</p>
<p class="text-xs text-[#586064]">International purchase lines will keep history; supplier link is cleared.</p>
</div>
<form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" onsubmit="return confirm('Delete this supplier?');">
@csrf
@method('DELETE')
<button type="submit" class="st-btn-secondary h-10 px-5 inline-flex items-center gap-2 border-[#9F403D] text-[#9F403D] hover:bg-[#F1F4F6] shrink-0">
<span class="material-symbols-outlined text-[18px]">delete</span>
Delete
</button>
</form>
</div>
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-4 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
