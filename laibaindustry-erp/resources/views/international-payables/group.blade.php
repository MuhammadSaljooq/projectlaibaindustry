<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'International payable group - ERP'])
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
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col gap-1">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">INT_PAYABLE_GROUP</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Payables</h1>
<p class="text-sm text-[#586064] mt-2">{{ $displayName }}</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<p class="text-[11px] text-[#586064] max-w-2xl">Invoices are split into open and settled sections like Receivables. Combined payments are tracked in batches and allocated oldest invoice first.</p>

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

<div class="grid grid-cols-1 md:grid-cols-3 gap-0 border border-[#ABB3B7] bg-white md:divide-x md:divide-[#ABB3B7]">
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Group bill</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol }} {{ number_format($groupTotals['total_bill'], 2) }}</p>
</div>
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Group paid</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol }} {{ number_format($groupTotals['total_direct_payments'], 2) }}</p>
</div>
<div class="p-6 border-2 border-[#5E5E5E] max-md:m-0 -m-px">
<p class="st-label st-label--primary mb-2">Outstanding</p>
<p class="text-2xl font-black font-mono text-[#5E5E5E] tabular-nums">{{ $currencySymbol }} {{ number_format($groupTotals['total_remaining'], 2) }}</p>
</div>
</div>

@if(auth()->user()->role !== 'viewer' && $groupTotals['total_remaining'] > 0.00001)
<div class="st-paper border border-[#ABB3B7] p-6 md:p-8 bg-white">
<p class="st-label mb-4">Record payment on total balance</p>
<form method="post" action="{{ route('international-payables.group.payments.store', ['groupKey' => $groupKeyEncoded]) }}" class="flex flex-col sm:flex-row sm:flex-wrap gap-4 sm:items-end">
@csrf
<div class="flex flex-col gap-1 min-w-[140px]">
<label class="st-label" for="gp_payment_date">Payment date</label>
<input class="st-input font-mono text-sm" type="date" id="gp_payment_date" name="payment_date" value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
</div>
<div class="flex flex-col gap-1 min-w-[140px]">
<label class="st-label" for="gp_amount">Amount</label>
<input class="st-input font-mono text-sm" type="number" id="gp_amount" name="amount" step="0.01" min="0.01" max="{{ $groupTotals['total_remaining'] }}" placeholder="0.00" value="{{ old('amount') }}" required>
</div>
<div class="flex flex-col gap-1 min-w-[220px]">
<label class="st-label" for="gp_notes">Notes (optional)</label>
<input class="st-input text-sm" type="text" id="gp_notes" name="notes" maxlength="500" value="{{ old('notes') }}" placeholder="Reference / remarks">
</div>
<button type="submit" class="st-btn-primary h-10 px-4 text-[10px] font-bold uppercase tracking-wider shrink-0">Record combined payment</button>
</form>
<p class="text-[10px] text-[#586064] mt-3">Maximum for this batch: {{ $currencySymbol }} {{ number_format($groupTotals['total_remaining'], 2) }}. Allocation is oldest invoice first.</p>
</div>
@endif

@if($groupPayments->isNotEmpty())
<div class="st-paper border border-[#ABB3B7] bg-white overflow-x-auto">
<p class="st-label px-4 pt-4 pb-2">Combined payments</p>
<table class="w-full text-left border-collapse min-w-[480px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Payment date</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Amount</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Actions</th>
</tr>
</thead>
<tbody>
@foreach ($groupPayments as $gp)
<tr class="st-tr">
<td class="st-td px-4 py-3 text-sm font-mono text-[#586064]">{{ format_display_date($gp->payment_date) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums text-[#2B3437]">{{ $currencySymbol }} {{ number_format($gp->amount, 2) }}</td>
<td class="st-td px-4 py-3 text-right">
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('international-payables.group.payments.edit', ['groupKey' => $groupKeyEncoded, 'internationalPayableGroupPayment' => $gp]) }}" class="text-[11px] font-bold uppercase tracking-wider text-[#5E5E5E] border border-[#5E5E5E] px-2 py-1 inline-flex items-center gap-1 hover:bg-[#F1F4F6] mr-2">Edit</a>
<form method="post" action="{{ route('international-payables.group.payments.destroy', ['groupKey' => $groupKeyEncoded, 'internationalPayableGroupPayment' => $gp]) }}" class="inline" data-confirm-delete="{{ e('Remove this combined payment?') }}">
@csrf
@method('DELETE')
<button type="submit" class="text-[11px] font-bold uppercase tracking-wider text-[#9F403D] border border-[#9F403D] px-2 py-1 hover:bg-[#F1F4F6]">Remove</button>
</form>
@else
<span class="text-xs text-[#586064]">—</span>
@endif
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
@endif

<div class="st-paper border border-[#ABB3B7] bg-white overflow-x-auto">
<div class="px-4 pt-4 pb-2 border-b border-[#ABB3B7] bg-[#F8F9FA] sticky left-0">
<p class="st-label">Open invoices</p>
</div>
<div class="min-w-[900px]">
<table class="w-full text-left border-collapse">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Payment date</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Invoice</th>
<th class="st-th px-4 py-3">Products</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Bill</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Direct payment</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Received total</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Remaining</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Status</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Action</th>
</tr>
</thead>
<tbody>
@forelse($openOrders as $o)
@php
    $paidRow = (float) ($o->direct_payment_total ?? 0);
    $billRow = (float) $o->total_amount;
    $bal = max(0, (float) ($o->remaining_balance ?? 0));
    $lineCount = $o->relationLoaded('lines') ? $o->lines->count() : 0;
    $firstLine = $o->relationLoaded('lines') ? $o->lines->first() : null;
    $linesSummary = $firstLine ? ($lineCount > 1 ? $firstLine->product_name.', +'.($lineCount - 1).' more' : $firstLine->product_name) : '—';
    $invLabel = $o->invoice_number ?: '#'.$o->id;
@endphp
<tr class="st-tr">
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ format_display_date($o->date) }}</td>
<td class="st-td px-4 py-3 text-sm whitespace-nowrap font-mono text-[#586064]">{{ $o->payablePayments->sortByDesc('payment_date')->first()?->payment_date ? format_display_date($o->payablePayments->sortByDesc('payment_date')->first()->payment_date) : '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-bold text-[#2B3437]">{{ $invLabel }}</td>
<td class="st-td px-4 py-3 text-sm text-[#586064] max-w-[220px]"><span class="line-clamp-2">{{ $linesSummary }}</span></td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#2B3437]">{{ $currencySymbol }} {{ number_format($billRow, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#586064]">{{ $currencySymbol }} {{ number_format($paidRow, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums text-[#2B3437]">{{ $currencySymbol }} {{ number_format($paidRow, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums {{ $bal > 0.009 ? 'text-[#9F403D]' : 'text-[#5E5E5E]' }}">{{ $currencySymbol }} {{ number_format($bal, 2) }}</td>
<td class="st-td px-4 py-3 text-right">
<span class="text-[10px] font-bold uppercase tracking-wider text-[#5E5E5E]">Open</span>
</td>
<td class="st-td px-4 py-3 text-right">
@if(auth()->user()->role !== 'viewer')
@php $latestPayment = $o->payablePayments->first(); @endphp
<a href="{{ route('international-payables.pay', $o) }}" class="st-btn-primary h-9 px-4 inline-flex items-center gap-2 text-[11px] whitespace-nowrap mr-2">
<span class="material-symbols-outlined text-[16px]">payments</span>
Manage
</a>
@if($latestPayment)
<form method="POST" action="{{ route('international-payables.payments.destroy', ['international_purchase' => $o, 'internationalPayablePayment' => $latestPayment, 'groupKey' => $groupKeyEncoded]) }}" class="inline-flex" data-confirm-delete="{{ e('Delete latest recorded payment for this invoice?') }}">
@csrf
@method('DELETE')
<button type="submit" class="h-9 px-3 inline-flex items-center gap-1.5 whitespace-nowrap text-[11px] font-bold uppercase tracking-wider border border-[#9F403D] text-[#9F403D] bg-transparent hover:bg-[#F1F4F6]">
<span class="material-symbols-outlined text-[16px]">delete</span>
Delete payment
</button>
</form>
@endif
@else
<span class="text-xs text-[#586064]">—</span>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="10" class="px-6 py-10 text-center text-sm text-[#586064]">No open invoices for this vendor.</td>
</tr>
@endforelse
</tbody>
</table>
</div>
</div>

@if($settledOrders->isNotEmpty())
<div class="st-paper border border-[#ABB3B7] bg-white overflow-x-auto">
<div class="px-4 pt-4 pb-2 border-b border-[#ABB3B7] bg-[#F8F9FA] sticky left-0">
<p class="st-label">Settled invoices</p>
</div>
<div class="min-w-[760px]">
<table class="w-full text-left border-collapse">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Invoice</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Bill</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Direct payment</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Received total</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Status</th>
</tr>
</thead>
<tbody>
@foreach ($settledOrders as $o)
@php $paidRow = (float) ($o->direct_payment_total ?? 0); @endphp
<tr class="st-tr">
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ format_display_date($o->date) }}</td>
<td class="st-td px-4 py-3 text-sm font-semibold text-[#2B3437]">{{ $o->invoice_number ?: '#'.$o->id }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums text-[#2B3437]">{{ $currencySymbol }} {{ number_format((float) $o->total_amount, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums text-[#586064]">{{ $currencySymbol }} {{ number_format($paidRow, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums text-[#2B3437]">{{ $currencySymbol }} {{ number_format($paidRow, 2) }}</td>
<td class="st-td px-4 py-3 text-right">
<span class="text-[10px] font-bold uppercase tracking-wider text-[#586064] border border-[#ABB3B7] px-2 py-1 inline-block bg-[#F8F9FA]">Paid</span>
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
</div>
@endif

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-6 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
