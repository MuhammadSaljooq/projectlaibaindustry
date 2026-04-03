<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Edit '.$quotation->quotation_number.' - ERP'])
@include('partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'quotations'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<a href="{{ route('quotations.show', $quotation) }}" class="st-btn-secondary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
<span class="hidden sm:inline">Back</span>
</a>
</div>
<div class="flex items-center gap-2 flex-wrap justify-end">
<a href="{{ route('quotations.preview', $quotation) }}?v={{ $quotation->updated_at?->timestamp ?? $quotation->id }}" target="_blank" rel="noopener" class="st-btn-secondary h-9 px-3 text-[10px]">Preview</a>
<a href="{{ route('quotations.pdf', $quotation) }}?v={{ $quotation->updated_at?->timestamp ?? $quotation->id }}" class="st-btn-secondary h-9 px-3 text-[10px]">PDF</a>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-5xl mx-auto flex flex-col gap-6">
<div class="flex flex-col gap-1">
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Edit {{ $quotation->quotation_number }}</h1>
<p class="text-sm text-[#586064] mt-2">{{ $quotation->customer_name }}</p>
<div class="h-0.5 w-full bg-[#5E5E5E] mt-4" role="presentation"></div>
</div>
@include('quotations._form')
</div>
</div>
</main>
</body>
</html>
