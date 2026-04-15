<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Edit combined payable payment - ERP'])
@include('partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'payables'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<a href="{{ route('payables.group', ['groupKey' => $groupKeyEncoded]) }}" class="st-btn-secondary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
<span class="hidden sm:inline">Customer invoices</span>
</a>
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-[640px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col gap-1">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">AP_COMBINED_PAYMENT</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Edit combined payment</h1>
<p class="text-sm text-[#586064] mt-2">{{ $displayName }}</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

@if (session('error'))
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D]">
{{ session('error') }}
</div>
@endif

<div class="st-paper border border-[#ABB3B7] p-6 md:p-8 bg-white">
<p class="st-label mb-4">Update amount or date</p>
<p class="text-[11px] text-[#586064] mb-6">You may allocate up to {{ $currencySymbol ?? '$' }} {{ number_format($maxAllowed, 2) }} (current combined remaining plus this payment).</p>
<form method="post" action="{{ route('payables.group.payments.update', ['groupKey' => $groupKeyEncoded, 'payableGroupPayment' => $groupPayment]) }}" class="flex flex-col gap-6">
@csrf
@method('PATCH')
<div class="flex flex-col gap-1">
<label class="st-label" for="payment_date">Payment date</label>
<input class="st-input font-mono text-sm max-w-xs" type="date" id="payment_date" name="payment_date" value="{{ old('payment_date', $groupPayment->payment_date->format('Y-m-d')) }}" required>
</div>
<div class="flex flex-col gap-1">
<label class="st-label" for="amount">Amount</label>
<input class="st-input font-mono text-sm max-w-xs" type="number" id="amount" name="amount" step="0.01" min="0.01" max="{{ $maxAllowed }}" value="{{ old('amount', $groupPayment->amount) }}" required>
</div>
<button type="submit" class="st-btn-primary h-10 px-4 text-[10px] font-bold uppercase tracking-wider self-start">Save changes</button>
</form>
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-4 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
