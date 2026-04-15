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
<p class="st-label mb-6">International purchase invoice</p>

<div class="space-y-3 text-sm mb-6 pb-6 border-b border-[#ABB3B7]">
<div class="flex items-center justify-between gap-4">
<span class="st-label !mb-0">Date</span>
<span class="font-semibold text-[#2B3437]">{{ format_display_date($order->date) }}</span>
</div>
<div class="flex items-center justify-between gap-4">
<span class="st-label !mb-0">Vendor</span>
<span class="font-semibold text-[#2B3437] text-right">{{ $order->supplier?->name ?? '—' }}</span>
</div>
@if($order->invoice_number)
<div class="flex items-center justify-between gap-4">
<span class="st-label !mb-0">Invoice / reference</span>
<span class="font-mono font-semibold text-[#2B3437] text-right">{{ $order->invoice_number }}</span>
</div>
@endif
</div>

@if($order->lines->isNotEmpty())
<div class="mb-6 border border-[#ABB3B7] overflow-hidden">
<table class="w-full text-left text-sm">
<thead class="bg-[#EAEFF1]">
<tr>
<th class="px-3 py-2 font-bold text-[10px] uppercase tracking-wider text-[#586064]">Product</th>
<th class="px-3 py-2 font-bold text-[10px] uppercase tracking-wider text-[#586064] text-right">Qty</th>
<th class="px-3 py-2 font-bold text-[10px] uppercase tracking-wider text-[#586064] text-right">Amount</th>
</tr>
</thead>
<tbody>
@foreach($order->lines as $line)
<tr class="border-t border-[#ABB3B7]">
<td class="px-3 py-2 text-[#2B3437]">{{ $line->product_name }}</td>
<td class="px-3 py-2 text-right tabular-nums text-[#586064]">{{ number_format($line->quantity) }}</td>
<td class="px-3 py-2 text-right font-mono tabular-nums">{{ $currencySymbol }} {{ number_format($line->total_amount, 2) }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-3 gap-0 border border-[#ABB3B7] bg-white sm:divide-x sm:divide-[#ABB3B7] mb-8">
<div class="p-4 border-b sm:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Bill</p>
<p class="text-lg font-bold font-mono tabular-nums text-[#2B3437]">{{ $currencySymbol }} {{ number_format((float) $order->total_amount, 2) }}</p>
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

@if(($payments ?? collect())->isNotEmpty() && auth()->user()->role !== 'viewer')
@php $latestPayment = ($payments ?? collect())->first(); @endphp
@if($latestPayment)
<div class="mb-8 p-4 border border-[#ABB3B7] bg-[#F8F9FA]">
<div class="flex flex-wrap items-center justify-between gap-3">
<div>
<p class="st-label mb-1">Quick action</p>
<p class="text-xs text-[#586064]">Delete latest recorded payment for this invoice.</p>
</div>
<form method="POST" action="{{ route('international-payables.payments.destroy', [$order, $latestPayment]) }}" class="inline" data-confirm-delete="{{ (bool) ($latestPayment->international_payable_group_payment_id ?? false) ? e('This payment belongs to a combined batch. Deleting here removes the full batch. Continue?') : e('Remove latest international payment entry?') }}">
@csrf
@method('DELETE')
<button type="submit" class="h-10 px-4 text-[10px] font-bold uppercase border border-[#9F403D] text-[#9F403D] hover:bg-[#FDF5F5]">{{ (bool) ($latestPayment->international_payable_group_payment_id ?? false) ? 'Delete Latest Batch' : 'Delete Latest Payment' }}</button>
</form>
</div>
</div>
@endif
@endif

@if(($payments ?? collect())->isNotEmpty() && auth()->user()->role !== 'viewer')
<div class="mb-10 pb-10 border-b border-[#ABB3B7]">
<p class="st-label mb-4">Recorded payments</p>
<p class="text-xs text-[#586064] mb-4">Edit amount/date or remove a payment row. This matches receivable payment handling.</p>
<div class="overflow-x-auto border border-[#ABB3B7]">
<table class="w-full text-left border-collapse min-w-[680px]">
<thead>
<tr class="bg-[#EAEFF1]">
<th class="st-th px-3 py-2 text-[10px] uppercase whitespace-nowrap">Date</th>
<th class="st-th px-3 py-2 text-[10px] uppercase text-right whitespace-nowrap">Amount</th>
<th class="st-th px-3 py-2 text-[10px] uppercase whitespace-nowrap">Notes</th>
<th class="st-th px-3 py-2 text-[10px] uppercase text-right w-40 whitespace-nowrap">Actions</th>
</tr>
</thead>
<tbody>
@foreach(($payments ?? collect()) as $payment)
@php $isGroupedPayment = (bool) ($payment->international_payable_group_payment_id ?? false); @endphp
<tr class="border-t border-[#ABB3B7] align-top">
<td class="st-td px-3 py-3" colspan="4">
<div class="flex flex-col gap-3">
<form method="POST" action="{{ route('international-payables.payments.update', [$order, $payment]) }}" class="grid grid-cols-1 lg:grid-cols-[180px_140px_minmax(200px,1fr)_auto] gap-3 items-end">
@csrf
@method('PATCH')
<div>
<label class="st-label block mb-1 text-[10px]" for="payment-date-{{ $payment->id }}">Payment date</label>
<input class="st-input w-full h-10 px-3 text-sm font-mono" id="payment-date-{{ $payment->id }}" name="payment_date_{{ $payment->id }}" type="date" value="{{ old('payment_date_'.$payment->id, $payment->payment_date?->format('Y-m-d')) }}" required @disabled($isGroupedPayment)>
@error('payment_date_'.$payment->id)
<p class="text-xs text-[#9F403D] mt-1">{{ $message }}</p>
@enderror
</div>
<div>
<label class="st-label block mb-1 text-[10px]" for="payment-amount-{{ $payment->id }}">Amount</label>
<input class="st-input w-full h-10 px-3 text-sm font-mono text-right tabular-nums" id="payment-amount-{{ $payment->id }}" name="amount_{{ $payment->id }}" type="number" step="0.01" min="0.01" value="{{ old('amount_'.$payment->id, number_format((float) $payment->amount, 2, '.', '')) }}" required @disabled($isGroupedPayment)>
@error('amount_'.$payment->id)
<p class="text-xs text-[#9F403D] mt-1">{{ $message }}</p>
@enderror
</div>
<div>
<label class="st-label block mb-1 text-[10px]" for="payment-notes-{{ $payment->id }}">Notes</label>
<input class="st-input w-full h-10 px-3 text-sm" id="payment-notes-{{ $payment->id }}" name="notes_{{ $payment->id }}" type="text" maxlength="500" value="{{ old('notes_'.$payment->id, $payment->notes) }}" placeholder="Optional reference" @disabled($isGroupedPayment)>
@error('notes_'.$payment->id)
<p class="text-xs text-[#9F403D] mt-1">{{ $message }}</p>
@enderror
</div>
@if(!$isGroupedPayment)
<button type="submit" class="st-btn-primary h-10 px-4 text-[10px] uppercase">Save</button>
@else
<span class="text-xs text-[#586064]">Batch payment (edit from group page)</span>
@endif
</form>
<form method="POST" action="{{ route('international-payables.payments.destroy', [$order, $payment]) }}" class="inline" data-confirm-delete="{{ $isGroupedPayment ? e('This payment belongs to a combined batch. Deleting here removes the full batch. Continue?') : e('Remove this international payment entry?') }}">
@csrf
@method('DELETE')
<button type="submit" class="h-10 px-4 text-[10px] font-bold uppercase border border-[#9F403D] text-[#9F403D] hover:bg-[#FDF5F5]">{{ $isGroupedPayment ? 'Delete Batch' : 'Delete' }}</button>
</form>
</div>
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
</div>
@endif

@if(!$isPaid && auth()->user()->role !== 'viewer')
<form method="POST" action="{{ route('international-payables.pay.store', $order) }}" novalidate>
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
<p class="text-sm font-semibold text-[#2B3437]">This invoice is fully paid.</p>
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
