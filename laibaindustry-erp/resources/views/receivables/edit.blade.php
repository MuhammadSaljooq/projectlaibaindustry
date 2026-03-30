<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Record Payment - ERP'])
@include('partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'receivables'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<a href="{{ route('receivables.index') }}" class="st-btn-secondary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
<span class="hidden sm:inline">Receivables</span>
</a>
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-[700px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col gap-1">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">PAYMENT_ENTRY_06</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Record Payment</h1>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

@if (session('error'))
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D]">
{{ session('error') }}
</div>
@endif

@php $remaining = (float)$receivable->amount - (float)$receivable->received; @endphp

<div class="st-paper border border-[#ABB3B7] p-6 md:p-8 bg-white">
<p class="st-label mb-6">Invoice details</p>

<div class="grid grid-cols-2 sm:grid-cols-3 gap-6 mb-8 pb-8 border-b border-[#ABB3B7]">
<div>
<p class="st-label mb-1">Invoice date</p>
<p class="text-sm font-semibold text-[#2B3437] font-mono">{{ $receivable->date->format('Y-m-d') }}</p>
</div>
<div>
<p class="st-label mb-1">Invoice</p>
<p class="text-sm font-semibold text-[#2B3437]">{{ $receivable->invoice_number ?: '—' }}</p>
</div>
<div class="col-span-2 sm:col-span-1">
<p class="st-label mb-1">Customer</p>
<p class="text-sm font-semibold text-[#2B3437]">{{ $receivable->customer_name ?: $receivable->customer_code ?: '—' }}</p>
</div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8 pb-8 border-b border-[#ABB3B7]">
<div>
<p class="st-label mb-1">Bill</p>
<p class="text-lg font-bold font-mono tabular-nums text-[#2B3437]">{{ $currencySymbol ?? '$' }} {{ number_format($receivable->amount, 2) }}</p>
</div>
<div>
<p class="st-label mb-1">Already Received</p>
<p class="text-lg font-bold font-mono tabular-nums text-[#586064]">{{ $currencySymbol ?? '$' }} {{ number_format($receivable->received, 2) }}</p>
</div>
<div>
<p class="st-label mb-1">Remaining</p>
<p class="text-lg font-black font-mono tabular-nums text-[#5E5E5E]">{{ $currencySymbol ?? '$' }} {{ number_format($remaining, 2) }}</p>
</div>
</div>

@if ($remaining > 0)
<form method="POST" action="{{ route('receivables.update', $receivable) }}">
@csrf
@method('PUT')

<label class="st-label block mb-2" for="payment_date">
Payment date <span class="text-[#9F403D]">*</span>
</label>
<input class="st-input w-full h-11 px-4 text-sm font-mono"
    id="payment_date"
    name="payment_date"
    type="date"
    value="{{ old('payment_date', now()->format('Y-m-d')) }}"
    required>
@error('payment_date')
<p class="text-xs text-[#9F403D] mt-1">{{ $message }}</p>
@enderror

<label class="st-label block mb-2 mt-6" for="received">
Payment amount <span class="text-[#9F403D]">*</span>
</label>
<input class="st-input w-full h-11 px-4 text-sm font-mono text-right tabular-nums"
    id="received"
    name="received"
    type="number"
    step="0.01"
    min="0.01"
    max="{{ $remaining }}"
    value="{{ old('received', $remaining) }}"
    placeholder="Max: {{ number_format($remaining, 2) }}"
    required>
<p class="text-xs mt-2 text-[#586064]">Maximum payment: {{ $currencySymbol ?? '$' }} {{ number_format($remaining, 2) }}</p>

<div class="flex flex-wrap items-center gap-3 mt-8">
<button type="submit" class="st-btn-primary h-11 px-6 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">payments</span>
Record payment
</button>
<a href="{{ route('receivables.index') }}" class="st-btn-secondary h-11 px-6 inline-flex items-center">Cancel</a>
</div>
</form>
@else
<div class="text-center py-6 border border-[#ABB3B7] bg-[#F8F9FA]">
<p class="st-label mb-2">Status</p>
<p class="text-sm font-semibold text-[#586064] mb-4">This receivable is fully paid.</p>
<a href="{{ route('receivables.index') }}" class="st-btn-secondary h-10 px-5 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
Back to receivables
</a>
</div>
@endif
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-4 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
