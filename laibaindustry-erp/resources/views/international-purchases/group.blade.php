<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'International purchase group - ERP'])
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
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col gap-1">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">INT_PURCHASE_GROUP</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Invoices</h1>
<p class="text-sm text-[#586064] mt-2">{{ $displayName }}</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
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

<div class="grid grid-cols-1 md:grid-cols-2 gap-0 border border-[#ABB3B7] bg-white md:divide-x md:divide-[#ABB3B7]">
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Total invoices</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ number_format($orders->count()) }}</p>
</div>
<div class="p-6 border-2 border-[#5E5E5E] max-md:m-0 -m-px">
<p class="st-label st-label--primary mb-2">Group total</p>
<p class="text-2xl font-black font-mono text-[#5E5E5E] tabular-nums">{{ $currencySymbol }} {{ number_format($totalAmount, 2) }}</p>
</div>
</div>

<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[320px]">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">International purchase invoices</h3>
<p class="text-[11px] text-[#586064] mt-1">Date · invoice · products · total · actions</p>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[960px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Invoice</th>
<th class="st-th px-4 py-3 min-w-[220px]">Products</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Total</th>
@if(auth()->user()->role !== 'viewer')
<th class="st-th px-4 py-3 text-right w-36"></th>
@endif
</tr>
</thead>
<tbody>
@foreach($orders as $order)
@php
$lineCount = $order->lines->count();
$firstLine = $order->lines->first();
$firstName = $firstLine ? $firstLine->product_name : '—';
$linesSummary = $lineCount <= 1 ? ($lineCount === 0 ? 'No line items' : $firstName) : ($firstName.', +'.($lineCount - 1).' more');
$invLabel = $order->invoice_number ?: '#'.$order->id;
@endphp
<tr class="st-tr">
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ format_display_date($order->date) }}</td>
<td class="st-td px-4 py-3 text-sm font-bold text-[#2B3437]">
<a href="{{ route('international-purchases.show', $order) }}" class="text-[#5E5E5E] hover:underline">{{ $invLabel }}</a>
</td>
<td class="st-td px-4 py-3 text-sm text-[#586064] max-w-[300px]" title="{{ e($linesSummary) }}">
<span class="line-clamp-2">{{ $linesSummary }}</span>
@if($lineCount > 1)
<span class="block text-[10px] uppercase tracking-wide text-[#586064] mt-0.5">{{ $lineCount }} lines</span>
@endif
</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums text-[#5E5E5E]">{{ $currencySymbol }} {{ number_format((float) $order->total_amount, 2) }}</td>
@if(auth()->user()->role !== 'viewer')
<td class="st-td px-4 py-3 text-right">
<div class="flex items-center justify-end gap-1 flex-wrap">
<a href="{{ route('international-purchases.show', $order) }}" class="p-2 border border-transparent hover:border-[#ABB3B7] text-[#586064] hover:text-[#2B3437] hover:bg-[#F1F4F6]" title="View">
<span class="material-symbols-outlined text-[18px]">visibility</span>
</a>
<a href="{{ route('international-purchases.edit', $order) }}" class="p-2 border border-transparent hover:border-[#ABB3B7] text-[#586064] hover:text-[#2B3437] hover:bg-[#F1F4F6]" title="Edit">
<span class="material-symbols-outlined text-[18px]">edit</span>
</a>
<form method="POST" action="{{ route('international-purchases.destroy', $order) }}" class="inline-flex" data-confirm-delete="{{ e('Delete this invoice and all lines?') }}">
@csrf
@method('DELETE')
<button type="submit" class="p-2 border border-transparent hover:border-[#9F403D] text-[#586064] hover:text-[#9F403D] hover:bg-[#F1F4F6]" title="Delete">
<span class="material-symbols-outlined text-[18px]">delete</span>
</button>
</form>
</div>
</td>
@endif
</tr>
@endforeach
</tbody>
</table>
</div>
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-6 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
