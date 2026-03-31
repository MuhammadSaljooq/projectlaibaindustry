<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Record international payment - ERP'])
@include('partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'international_payables'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<a href="{{ route('international-payables.index') }}" class="st-btn-secondary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
<span class="hidden sm:inline">International payables</span>
</a>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-4xl mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col gap-1">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">INT_AP_01</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Record payment</h1>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

@if (session('error'))
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D]">
{{ session('error') }}
</div>
@endif
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

@php $isPaid = $balance <= 0.009; @endphp

<div class="st-paper border border-[#ABB3B7] p-6 md:p-8 bg-white max-w-2xl">
<p class="st-label mb-6">International purchase line</p>

<div class="space-y-3 text-sm mb-6 pb-6 border-b border-[#ABB3B7]">
<div class="flex items-center justify-between gap-4">
<span class="st-label !mb-0">Date</span>
<span class="font-semibold text-[#2B3437]">{{ $purchase->date->format('Y-m-d') }}</span>
</div>
<div class="flex items-center justify-between gap-4">
<span class="st-label !mb-0">Supplier</span>
<span class="font-semibold text-[#2B3437] text-right">{{ $purchase->supplier?->name ?? '—' }}</span>
</div>
<div class="flex items-center justify-between gap-4">
<span class="st-label !mb-0">Product</span>
<span class="font-semibold text-[#2B3437] text-right">{{ $purchase->product_name }}</span>
</div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-0 border border-[#ABB3B7] bg-white sm:divide-x sm:divide-[#ABB3B7] mb-8">
<div class="p-4 border-b sm:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Bill</p>
<p class="text-lg font-bold font-mono tabular-nums text-[#2B3437]">{{ $currencySymbol }} {{ number_format((float) $purchase->total_amount, 2) }}</p>
</div>
<div class="p-4 border-b sm:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Paid</p>
<p class="text-lg font-bold font-mono tabular-nums text-[#2B3437]">{{ $currencySymbol }} {{ number_format($paid, 2) }}</p>
</div>
<div class="p-4 border-2 border-[#5E5E5E] max-sm:m-0 -m-px sm:-my-px sm:-mr-px">
<p class="st-label st-label--primary mb-2">Balance</p>
<p class="text-lg font-black font-mono tabular-nums text-[#5E5E5E]">{{ $currencySymbol }} {{ number_format($balance, 2) }}</p>
</div>
</div>

@if(!$isPaid && auth()->user()->role !== 'viewer')
<form method="POST" action="{{ route('international-payables.pay.store', $purchase) }}" novalidate>
@csrf
<div class="space-y-5 max-w-md">
<div>
<label class="st-label block mb-2" for="payment_date">Payment date <span class="text-[#9F403D]">*</span></label>
<input class="st-input w-full h-10 px-3 text-sm" type="date" id="payment_date" name="payment_date" value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
</div>
<div>
<label class="st-label block mb-2" for="amount">Amount <span class="text-[#9F403D]">*</span></label>
<div class="relative">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-[#586064] pointer-events-none">{{ $currencySymbol }}</span>
<input class="st-input w-full h-10 pl-8 pr-3 text-sm font-mono text-right tabular-nums @error('amount') !border-[#9F403D] @enderror"
    id="amount"
    name="amount"
    type="number"
    step="0.01"
    min="0.01"
    max="{{ $balance }}"
    value="{{ old('amount', number_format($balance, 2, '.', '')) }}"
    required>
</div>
<p class="mt-2 text-xs text-[#586064]">Maximum: <strong class="font-mono text-[#2B3437]">{{ $currencySymbol }} {{ number_format($balance, 2) }}</strong></p>
@error('amount')<p class="mt-1.5 text-xs text-[#9F403D] font-medium">{{ $message }}</p>@enderror
</div>
<div>
<label class="st-label block mb-2" for="notes">Notes</label>
<input class="st-input w-full h-10 px-3 text-sm" type="text" id="notes" name="notes" value="{{ old('notes') }}" maxlength="500" placeholder="Optional reference">
</div>
</div>

<div class="flex flex-wrap gap-3 mt-8 pt-6 border-t border-[#ABB3B7]">
<button type="submit" class="st-btn-primary h-10 px-5 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">payments</span>
Record payment
</button>
<a href="{{ route('international-payables.index') }}" class="st-btn-secondary h-10 px-5 inline-flex items-center gap-2">Cancel</a>
</div>
</form>
@elseif(!$isPaid)
<div class="border border-[#ABB3B7] bg-[#F8F9FA] px-4 py-4">
<p class="text-sm text-[#586064]">Read-only: you cannot record payments.</p>
</div>
<a href="{{ route('international-payables.index') }}" class="st-btn-secondary h-10 px-5 inline-flex items-center gap-2 mt-6 w-fit">Back</a>
@else
<div class="border border-[#ABB3B7] bg-[#F8F9FA] px-4 py-4 flex items-start gap-3">
<span class="material-symbols-outlined text-[#5E5E5E] text-[24px] shrink-0">check_circle</span>
<p class="text-sm font-semibold text-[#2B3437]">This line is fully paid.</p>
</div>
<a href="{{ route('international-payables.index') }}" class="st-btn-secondary h-10 px-5 inline-flex items-center gap-2 mt-6 w-fit">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
All international payables
</a>
@endif
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-4 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
