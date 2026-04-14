<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Record Payment - ERP'])
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
<a href="{{ route('payables.index') }}" class="st-btn-secondary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
<span class="hidden sm:inline">Payables</span>
</a>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-4xl mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col gap-1">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">AP_PAY_24</p>
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

@php
$balance = max(0, (float) $payable->amount - (float) $payable->received);
$isPaid = $balance <= 0;
$ledgerPayments = $payable->paymentLedgerEntries;
@endphp

<div class="st-paper border border-[#ABB3B7] p-6 md:p-8 bg-white max-w-2xl">
<p class="st-label mb-6">Payable details</p>

<div class="space-y-3 text-sm mb-6 pb-6 border-b border-[#ABB3B7]">
<div class="flex items-center justify-between gap-4">
<span class="st-label !mb-0">Date</span>
<span class="font-semibold text-[#2B3437]">{{ format_display_date($payable->date) }}</span>
</div>
<div class="flex items-center justify-between gap-4">
<span class="st-label !mb-0">Invoice</span>
<span class="font-semibold text-[#2B3437]">{{ $payable->invoice_number ?: '—' }}</span>
</div>
<div class="flex items-center justify-between gap-4">
<span class="st-label !mb-0">Name</span>
<span class="font-semibold text-[#2B3437] text-right">{{ $payable->customer_name ?: '—' }}</span>
</div>
<div class="flex items-center justify-between gap-4">
<span class="st-label !mb-0">Code</span>
<span class="font-mono text-[#586064]">{{ $payable->customer_code ?: '—' }}</span>
</div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-0 border border-[#ABB3B7] bg-white sm:divide-x sm:divide-[#ABB3B7] mb-8">
<div class="p-4 border-b sm:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Bill</p>
<p class="text-lg font-bold font-mono tabular-nums text-[#2B3437]">{{ $currencySymbol }} {{ number_format($payable->amount, 2) }}</p>
</div>
<div class="p-4 border-b sm:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Paid</p>
<p class="text-lg font-bold font-mono tabular-nums text-[#2B3437]">{{ $currencySymbol }} {{ number_format($payable->received, 2) }}</p>
</div>
<div class="p-4 border-2 border-[#5E5E5E] max-sm:m-0 -m-px sm:-my-px sm:-mr-px">
<p class="st-label st-label--primary mb-2">Balance</p>
<p class="text-lg font-black font-mono tabular-nums text-[#5E5E5E]">{{ $currencySymbol }} {{ number_format($balance, 2) }}</p>
</div>
</div>

@if ($ledgerPayments->isNotEmpty())
<div class="mb-10 pb-10 border-b border-[#ABB3B7]">
<p class="st-label mb-4">Recorded payments</p>
<p class="text-xs text-[#586064] mb-4">Edit amounts or dates, or remove a row. Totals stay in sync with the account ledger.</p>
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
<form method="POST" action="{{ route('payables.payments.update', [$payable, $entry]) }}" class="flex flex-col sm:flex-row sm:flex-wrap sm:items-end gap-3 flex-1">
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
<label class="st-label block mb-1 text-[10px]" for="pay-debit-{{ $entry->id }}">Amount</label>
<input class="st-input w-full h-10 px-3 text-sm font-mono text-right tabular-nums" id="pay-debit-{{ $entry->id }}" name="debit_{{ $entry->id }}" type="number" step="0.01" min="0.01" value="{{ old('debit_'.$entry->id, number_format((float)$entry->debit, 2, '.', '')) }}" required>
@error('debit_'.$entry->id)
<p class="text-xs text-[#9F403D] mt-1">{{ $message }}</p>
@enderror
</div>
<button type="submit" class="st-btn-primary h-10 px-4 text-[10px] uppercase sm:mb-0 mb-1">Save</button>
</form>
<form method="POST" action="{{ route('payables.payments.destroy', [$payable, $entry]) }}" class="inline" data-confirm-delete="{{ e('Remove this payment from the ledger?') }}">
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
@elseif ((float)$payable->received > 0)
<div class="mb-10 pb-10 border-b border-[#ABB3B7]">
<p class="st-label mb-2">Adjust paid (no customer ledger)</p>
<p class="text-xs text-[#586064] mb-4">This payable has no linked customer code, so payments were not written to the ledger. You can correct the total paid here.</p>
<form method="POST" action="{{ route('payables.adjust-received', $payable) }}">
@csrf
@method('PUT')
<label class="st-label block mb-2" for="orphan_received">Total paid</label>
<input class="st-input w-full max-w-xs h-11 px-4 text-sm font-mono text-right tabular-nums" id="orphan_received" name="received" type="number" step="0.01" min="0" max="{{ $payable->amount }}" value="{{ old('received', number_format((float)$payable->received, 2, '.', '')) }}" required>
<label class="st-label block mb-2 mt-4" for="orphan_received_at">Paid date (optional)</label>
<input class="st-input w-full max-w-xs h-11 px-4 text-sm font-mono" id="orphan_received_at" name="received_date" type="date" value="{{ old('received_date', $payable->received_date?->format('Y-m-d')) }}">
<div class="mt-6">
<button type="submit" class="st-btn-primary h-11 px-6 text-[10px] uppercase">Save</button>
</div>
</form>
</div>
@endif

@if(!$isPaid)
<form method="POST" action="{{ route('payables.update', $payable) }}" novalidate>
@csrf
@method('PUT')
<div>
<label class="st-label block mb-2" for="payment_date">Payment date <span class="text-[#9F403D]">*</span></label>
<input class="st-input w-full max-w-md h-10 px-3 text-sm font-mono"
    id="payment_date"
    name="payment_date"
    type="date"
    value="{{ old('payment_date', now()->format('Y-m-d')) }}"
    required>
@error('payment_date')<p class="mt-1.5 text-xs text-[#9F403D] font-medium">{{ $message }}</p>@enderror
</div>

<div class="mt-5">
<label class="st-label block mb-2" for="received">Payment amount <span class="text-[#9F403D]">*</span></label>
<div class="relative max-w-md">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-[#586064] pointer-events-none">{{ $currencySymbol }}</span>
<input class="st-input w-full h-10 pl-8 pr-3 text-sm font-mono text-right tabular-nums @error('received') !border-[#9F403D] @enderror"
    id="received"
    name="received"
    type="number"
    step="0.01"
    min="0.01"
    max="{{ $balance }}"
    value="{{ old('received', number_format($balance, 2, '.', '')) }}"
    placeholder="{{ number_format($balance, 2) }}"
    required>
</div>
<p class="mt-2 text-xs text-[#586064]">Maximum: <strong class="font-mono text-[#2B3437]">{{ $currencySymbol }} {{ number_format($balance, 2) }}</strong></p>
@error('received')<p class="mt-1.5 text-xs text-[#9F403D] font-medium">{{ $message }}</p>@enderror
</div>

<div class="flex flex-wrap gap-3 mt-8 pt-6 border-t border-[#ABB3B7]">
<button type="submit" class="st-btn-primary h-10 px-5 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">payments</span>
Record payment
</button>
<a href="{{ route('payables.index') }}" class="st-btn-secondary h-10 px-5 inline-flex items-center gap-2">Cancel</a>
</div>
</form>
@else
@if ($ledgerPayments->isEmpty() && (float)$payable->received <= 0)
<div class="text-center py-6 border border-[#ABB3B7] bg-[#F8F9FA]">
<p class="st-label mb-2">Status</p>
<p class="text-sm font-semibold text-[#586064] mb-4">Nothing recorded yet.</p>
</div>
@elseif ($ledgerPayments->isNotEmpty())
<p class="text-sm font-semibold text-[#586064] mb-2">This payable is fully paid. Use the table above to correct payments if needed.</p>
@elseif ($ledgerPayments->isEmpty() && (float)$payable->received > 0)
<p class="text-sm text-[#586064] mb-2">Use <span class="font-semibold">Adjust paid</span> above to change the total if needed.</p>
@endif
<div class="border border-[#ABB3B7] bg-[#F8F9FA] px-4 py-4 flex items-start gap-3 mt-4">
<span class="material-symbols-outlined text-[#5E5E5E] text-[24px] shrink-0">check_circle</span>
<p class="text-sm font-semibold text-[#2B3437]">This payable is fully paid.</p>
</div>
<a href="{{ route('payables.index') }}" class="st-btn-secondary h-10 px-5 inline-flex items-center gap-2 mt-6 w-fit">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
All payables
</a>
@endif
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-4 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
