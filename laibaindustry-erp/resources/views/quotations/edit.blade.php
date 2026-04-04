<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Edit '.$quotation->quotation_number.' - ERP'])
@include('partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'quotations'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between gap-3 px-4 sm:px-6 border-b border-[#ABB3B7] bg-white min-w-0">
<div class="flex items-center gap-2 sm:gap-4 min-w-0">
<button class="md:hidden shrink-0 p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<a href="{{ route('quotations.show', $quotation) }}" class="st-btn-secondary h-10 px-3 sm:px-4 inline-flex items-center justify-center gap-2 shrink-0 whitespace-nowrap">
<span class="material-symbols-outlined text-[20px] shrink-0 leading-none">arrow_back</span>
<span class="hidden sm:inline">Back</span>
</a>
</div>
<nav class="flex items-center gap-2 shrink-0" aria-label="Quotation actions">
<a href="{{ route('quotations.preview', $quotation) }}?v={{ $quotation->updated_at?->timestamp ?? $quotation->id }}" target="_blank" rel="noopener" class="st-btn-secondary h-10 px-3 sm:px-4 inline-flex items-center justify-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[20px] shrink-0 leading-none" aria-hidden="true">visibility</span>
<span>Preview</span>
</a>
<a href="{{ route('quotations.pdf', $quotation) }}?v={{ $quotation->updated_at?->timestamp ?? $quotation->id }}" class="st-btn-secondary h-10 px-3 sm:px-4 inline-flex items-center justify-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[20px] shrink-0 leading-none" aria-hidden="true">picture_as_pdf</span>
<span>PDF</span>
</a>
</nav>
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
