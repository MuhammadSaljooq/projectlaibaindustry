<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => $quotation->quotation_number.' - ERP'])
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
<a href="{{ route('quotations.index') }}" class="st-btn-secondary h-10 px-3 sm:px-4 inline-flex items-center justify-center gap-2 shrink-0 whitespace-nowrap">
<span class="material-symbols-outlined text-[20px] shrink-0">arrow_back</span>
<span class="hidden sm:inline">Quotations</span>
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
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('quotations.edit', $quotation) }}" class="st-btn-primary h-10 px-3 sm:px-4 inline-flex items-center justify-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[20px] shrink-0 leading-none" aria-hidden="true">edit</span>
<span>Edit</span>
</a>
@endif
</nav>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-5xl mx-auto flex flex-col gap-6">

<div class="flex flex-wrap items-end justify-between gap-4">
<div>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">{{ $quotation->quotation_number }}</h1>
<p class="text-sm text-[#586064] mt-2">{{ $quotation->customer_name }}</p>
</div>
<span class="inline-block px-3 py-1 rounded text-[10px] font-bold uppercase tracking-wide
@if($quotation->status === 'draft') bg-gray-100 text-gray-600
@elseif($quotation->status === 'sent') bg-blue-50 text-blue-800
@elseif($quotation->status === 'accepted') bg-green-50 text-green-800
@elseif($quotation->status === 'rejected') bg-red-50 text-red-800
@else bg-amber-50 text-amber-800
@endif">{{ $quotation->status }}</span>
</div>

@if (session('success'))
<div class="border border-[#ABB3B7] bg-white px-4 py-3 text-sm text-[#2B3437]">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D]">{{ session('error') }}</div>
@endif

<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
<div class="border border-[#ABB3B7] bg-white p-4">
<div class="text-[10px] font-bold uppercase tracking-widest text-[#586064] mb-1">Date</div>
<div class="font-semibold text-sm">{{ $quotation->quotation_date->format('j M Y') }}</div>
</div>
<div class="border border-[#ABB3B7] bg-white p-4">
<div class="text-[10px] font-bold uppercase tracking-widest text-[#586064] mb-1">Expires</div>
<div class="font-semibold text-sm">{{ $quotation->expiration_date ? $quotation->expiration_date->format('j M Y') : '—' }}</div>
</div>
<div class="border border-[#ABB3B7] bg-white p-4">
<div class="text-[10px] font-bold uppercase tracking-widest text-[#586064] mb-1">Salesperson</div>
<div class="font-semibold text-sm">{{ $quotation->salesperson ?? '—' }}</div>
</div>
<div class="border border-[#2B3437] bg-[#2B3437] text-white p-4">
<div class="text-[10px] font-bold uppercase tracking-widest text-white/80 mb-1">Total</div>
<div class="font-bold text-lg">SAR {{ number_format((float) $quotation->total_amount, 2) }}</div>
</div>
</div>

<div class="border border-[#ABB3B7] bg-white p-5">
<h2 class="text-[10px] font-bold uppercase tracking-widest text-[#586064] mb-3">Customer</h2>
<dl class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 text-sm">
<div><dt class="text-xs text-[#586064]">Name</dt><dd class="font-semibold">{{ $quotation->customer_name }}</dd></div>
<div><dt class="text-xs text-[#586064]">VAT</dt><dd>{{ $quotation->customer_vat_number ?? '—' }}</dd></div>
<div><dt class="text-xs text-[#586064]">CR</dt><dd>{{ $quotation->customer_cr_number ?? '—' }}</dd></div>
<div><dt class="text-xs text-[#586064]">Phone</dt><dd>{{ $quotation->customer_phone ?? '—' }}</dd></div>
<div><dt class="text-xs text-[#586064]">Email</dt><dd>{{ $quotation->customer_email ?? '—' }}</dd></div>
<div class="sm:col-span-2"><dt class="text-xs text-[#586064]">Address</dt><dd>{{ $quotation->customer_address ?? '—' }}</dd></div>
</dl>
</div>

<div class="border border-[#ABB3B7] bg-white overflow-x-auto">
<table class="w-full text-sm min-w-[640px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 text-center w-10">#</th>
<th class="st-th px-4 py-3 text-left">Description</th>
<th class="st-th px-4 py-3 text-center w-24">Qty</th>
<th class="st-th px-4 py-3 text-right w-28">Unit</th>
<th class="st-th px-4 py-3 text-right w-28">Tax</th>
<th class="st-th px-4 py-3 text-right w-28">Amount</th>
</tr>
</thead>
<tbody>
@foreach ($quotation->items as $i => $item)
<tr class="st-tr {{ $i % 2 === 1 ? 'bg-[#F8F9FA]' : '' }}">
<td class="st-td px-4 py-3 text-center text-[#586064] text-xs">{{ $i + 1 }}</td>
<td class="st-td px-4 py-3">{{ $item->description }}</td>
<td class="st-td px-4 py-3 text-center">{{ rtrim(rtrim(number_format((float) $item->quantity, 3), '0'), '.') }}</td>
<td class="st-td px-4 py-3 text-right font-mono tabular-nums">{{ number_format((float) $item->unit_price, 2) }}</td>
<td class="st-td px-4 py-3 text-right text-xs text-[#586064]">{{ number_format((float) $item->tax_rate, 2) }}% ({{ number_format((float) $item->tax_amount, 2) }})</td>
<td class="st-td px-4 py-3 text-right font-semibold font-mono tabular-nums">SAR {{ number_format((float) $item->amount, 2) }}</td>
</tr>
@endforeach
</tbody>
<tfoot>
<tr class="border-t border-[#ABB3B7] bg-[#F8F9FA] text-sm">
<td colspan="5" class="px-4 py-2 text-right text-[#586064]">Untaxed</td>
<td class="px-4 py-2 text-right font-mono">SAR {{ number_format((float) $quotation->untaxed_amount, 2) }}</td>
</tr>
<tr class="bg-[#F8F9FA] text-sm">
<td colspan="5" class="px-4 py-2 text-right text-[#586064]">VAT</td>
<td class="px-4 py-2 text-right font-mono">SAR {{ number_format((float) $quotation->vat_amount, 2) }}</td>
</tr>
<tr class="bg-[#2B3437] text-white font-bold">
<td colspan="5" class="px-4 py-3 text-right">Total</td>
<td class="px-4 py-3 text-right font-mono">SAR {{ number_format((float) $quotation->total_amount, 2) }}</td>
</tr>
</tfoot>
</table>
</div>

@if(filled($quotation->notes))
<div class="border border-[#ABB3B7] bg-white p-4 text-sm">
<span class="text-[10px] font-bold uppercase tracking-widest text-[#586064]">Notes</span>
<p class="mt-2 text-[#2B3437] whitespace-pre-wrap">{{ $quotation->notes }}</p>
</div>
@endif

@if(auth()->user()->role !== 'viewer')
<form action="{{ route('quotations.destroy', $quotation) }}" method="POST" class="pt-2" onsubmit="return confirm('Delete this quotation? It can be restored from the database only if your admin uses soft-delete tools.');">
@csrf
@method('DELETE')
<button type="submit" class="text-xs text-[#9F403D] hover:underline">Delete quotation</button>
</form>
@endif

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-6">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
