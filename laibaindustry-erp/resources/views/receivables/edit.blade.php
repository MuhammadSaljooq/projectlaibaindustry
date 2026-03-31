<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Manage receivable - ERP'])
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
<div class="max-w-[900px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col gap-1">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">RECEIVABLE_MANAGE</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Manage receivable</h1>
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

@php
    $remaining = (float)$receivable->amount - (float)$receivable->received;
    $ledgerPayments = $receivable->paymentLedgerEntries;
@endphp

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
<p class="st-label mb-1">Already received</p>
<p class="text-lg font-bold font-mono tabular-nums text-[#586064]">{{ $currencySymbol ?? '$' }} {{ number_format($receivable->received, 2) }}</p>
</div>
<div>
<p class="st-label mb-1">Remaining</p>
<p class="text-lg font-black font-mono tabular-nums text-[#5E5E5E]">{{ $currencySymbol ?? '$' }} {{ number_format($remaining, 2) }}</p>
</div>
</div>

@if ($ledgerPayments->isNotEmpty())
<div class="mb-10 pb-10 border-b border-[#ABB3B7]">
<p class="st-label mb-4">Recorded payments</p>
<p class="text-xs text-[#586064] mb-4">Edit amounts or dates, or remove a row. Totals stay in sync with the customer ledger.</p>
<div class="overflow-x-auto border border-[#ABB3B7]">
<table class="w-full text-left border-collapse min-w-[640px]">
<thead>
<tr class="bg-[#EAEFF1]">
<th class="st-th px-3 py-2 text-[10px] uppercase whitespace-nowrap">Date</th>
<th class="st-th px-3 py-2 text-[10px] uppercase text-right whitespace-nowrap">Amount</th>
<th class="st-th px-3 py-2 text-[10px] uppercase text-right w-40 whitespace-nowrap">Actions</th>
</tr>
</thead>
<tbody>
@foreach ($ledgerPayments as $entry)
<tr class="border-t border-[#ABB3B7] align-top">
<td class="st-td px-3 py-3" colspan="3">
<div class="flex flex-col lg:flex-row lg:flex-wrap lg:items-end gap-3">
<form method="POST" action="{{ route('receivables.payments.update', [$receivable, $entry]) }}" class="flex flex-col sm:flex-row sm:flex-wrap sm:items-end gap-3 flex-1">
@csrf
@method('PATCH')
<div class="flex-1 min-w-[140px]">
<label class="st-label block mb-1 text-[10px]" for="pay-date-{{ $entry->id }}">Payment date</label>
<input class="st-input w-full h-10 px-3 text-sm font-mono" id="pay-date-{{ $entry->id }}" name="date_{{ $entry->id }}" type="date" value="{{ old('date_'.$entry->id, $entry->date->format('Y-m-d')) }}" required>
@error('date_'.$entry->id)
<p class="text-xs text-[#9F403D] mt-1">{{ $message }}</p>
@enderror
</div>
<div class="w-full sm:w-36">
<label class="st-label block mb-1 text-[10px]" for="pay-credit-{{ $entry->id }}">Amount</label>
<input class="st-input w-full h-10 px-3 text-sm font-mono text-right tabular-nums" id="pay-credit-{{ $entry->id }}" name="credit_{{ $entry->id }}" type="number" step="0.01" min="0.01" value="{{ old('credit_'.$entry->id, number_format((float)$entry->credit, 2, '.', '')) }}" required>
@error('credit_'.$entry->id)
<p class="text-xs text-[#9F403D] mt-1">{{ $message }}</p>
@enderror
</div>
<button type="submit" class="st-btn-primary h-10 px-4 text-[10px] uppercase sm:mb-0 mb-1">Save</button>
</form>
<form method="POST" action="{{ route('receivables.payments.destroy', [$receivable, $entry]) }}" class="inline" data-confirm-delete="{{ e('Remove this payment from the ledger?') }}">
@csrf
@method('DELETE')
<button type="submit" class="h-10 px-4 text-[10px] font-bold uppercase border border-[#9F403D] text-[#9F403D] hover:bg-[#FDF5F5]">Delete</button>
</form>
</div>
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
</div>
@elseif ((float)$receivable->received > 0)
<div class="mb-10 pb-10 border-b border-[#ABB3B7]">
<p class="st-label mb-2">Adjust received (no customer ledger)</p>
<p class="text-xs text-[#586064] mb-4">This receivable has no linked customer code, so payments were not written to the ledger. You can correct the total received here.</p>
<form method="POST" action="{{ route('receivables.adjust-received', $receivable) }}">
@csrf
@method('PUT')
<label class="st-label block mb-2" for="orphan_received">Total received</label>
<input class="st-input w-full max-w-xs h-11 px-4 text-sm font-mono text-right tabular-nums" id="orphan_received" name="received" type="number" step="0.01" min="0" max="{{ $receivable->amount }}" value="{{ old('received', number_format((float)$receivable->received, 2, '.', '')) }}" required>
<label class="st-label block mb-2 mt-4" for="orphan_payment_at">Payment date (optional)</label>
<input class="st-input w-full max-w-xs h-11 px-4 text-sm font-mono" id="orphan_payment_at" name="payment_received_at" type="date" value="{{ old('payment_received_at', $receivable->payment_received_at?->format('Y-m-d')) }}">
<div class="mt-6">
<button type="submit" class="st-btn-primary h-11 px-6 text-[10px] uppercase">Save</button>
</div>
</form>
</div>
@endif

@if ($remaining > 0)
<p class="st-label mb-4">Record payment</p>
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
@error('received')
<p class="text-xs text-[#9F403D] mt-1">{{ $message }}</p>
@enderror
<p class="text-xs mt-2 text-[#586064]">Maximum payment: {{ $currencySymbol ?? '$' }} {{ number_format($remaining, 2) }}</p>

<div class="flex flex-wrap items-center gap-3 mt-8">
<button type="submit" class="st-btn-primary h-11 px-6 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">payments</span>
Record payment
</button>
</div>
</form>
@else
@if ($ledgerPayments->isEmpty() && (float)$receivable->received <= 0)
<div class="text-center py-6 border border-[#ABB3B7] bg-[#F8F9FA]">
<p class="st-label mb-2">Status</p>
<p class="text-sm font-semibold text-[#586064] mb-4">Nothing recorded yet.</p>
</div>
@elseif ($ledgerPayments->isNotEmpty())
<p class="text-sm font-semibold text-[#586064] mb-2">This receivable is fully paid. Use the table above to correct payments if needed.</p>
@elseif ($ledgerPayments->isEmpty() && (float)$receivable->received > 0)
<p class="text-sm text-[#586064] mb-2">Use <span class="font-semibold">Adjust received</span> above to change the total if needed.</p>
@endif
@endif

<div class="mt-8 pt-6 border-t border-[#ABB3B7]">
<a href="{{ route('receivables.index') }}" class="st-btn-secondary h-10 px-5 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
Back to receivables
</a>
</div>
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-4 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
