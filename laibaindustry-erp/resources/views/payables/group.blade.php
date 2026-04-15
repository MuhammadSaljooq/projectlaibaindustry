<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Payable group - ERP'])
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

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-[1200px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col gap-1">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">AP_BY_CUSTOMER</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Invoices</h1>
<p class="text-sm text-[#586064] mt-2">{{ $displayName }}</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<p class="text-[11px] text-[#586064] max-w-2xl">Invoices are split into open and settled sections like Receivables. Combined payments are tracked in batches and allocated oldest invoice first.</p>

@if (session('success'))
<div class="border border-[#ABB3B7] bg-white px-4 py-3 text-sm text-[#2B3437]">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D]">{{ session('error') }}</div>
@endif

<div class="st-paper border border-[#ABB3B7] p-6 md:p-8 bg-white">
<p class="st-label mb-4">Totals for this customer</p>
<div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
<div>
<p class="st-label mb-1">Total bill</p>
<p class="text-lg font-bold font-mono tabular-nums text-[#2B3437]">{{ $currencySymbol }} {{ number_format($groupTotals['total_bill'], 2) }}</p>
</div>
<div>
<p class="st-label mb-1">Direct payment</p>
<p class="text-lg font-bold font-mono tabular-nums text-[#586064]">{{ $currencySymbol }} {{ number_format($groupTotals['total_direct_payments'], 2) }}</p>
</div>
<div>
<p class="st-label mb-1">Total paid</p>
<p class="text-lg font-bold font-mono tabular-nums text-[#586064]">{{ $currencySymbol }} {{ number_format($groupTotals['total_received'], 2) }}</p>
</div>
<div>
<p class="st-label mb-1">Total remaining</p>
<p class="text-lg font-black font-mono tabular-nums text-[#5E5E5E]">{{ $currencySymbol }} {{ number_format($groupTotals['total_remaining'], 2) }}</p>
</div>
</div>
</div>

@if(auth()->user()->role !== 'viewer' && $groupTotals['total_remaining'] > 0.00001)
<div class="st-paper border border-[#ABB3B7] p-6 md:p-8 bg-white">
<p class="st-label mb-4">Record payment on total balance</p>
<form method="post" action="{{ route('payables.group.payments.store', ['groupKey' => $groupKeyEncoded]) }}" class="flex flex-col sm:flex-row sm:flex-wrap gap-4 sm:items-end">
@csrf
<div class="flex flex-col gap-1 min-w-[140px]">
<label class="st-label" for="gp_payment_date">Payment date</label>
<input class="st-input font-mono text-sm" type="date" id="gp_payment_date" name="payment_date" value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
</div>
<div class="flex flex-col gap-1 min-w-[140px]">
<label class="st-label" for="gp_amount">Amount</label>
<input class="st-input font-mono text-sm" type="number" id="gp_amount" name="amount" step="0.01" min="0.01" max="{{ $groupTotals['total_remaining'] }}" placeholder="0.00" value="{{ old('amount') }}" required>
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
<a href="{{ route('payables.group.payments.edit', ['groupKey' => $groupKeyEncoded, 'payableGroupPayment' => $gp]) }}" class="text-[11px] font-bold uppercase tracking-wider text-[#5E5E5E] border border-[#5E5E5E] px-2 py-1 inline-flex items-center gap-1 hover:bg-[#F1F4F6] mr-2">Edit</a>
<form method="post" action="{{ route('payables.group.payments.destroy', ['groupKey' => $groupKeyEncoded, 'payableGroupPayment' => $gp]) }}" class="inline" data-confirm-delete="{{ e('Remove this combined payment?') }}">
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
<th class="st-th px-4 py-3 whitespace-nowrap">Code</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Bill</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Direct payment</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Paid</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Remaining</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Status</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Action</th>
</tr>
</thead>
<tbody>
@forelse ($openPayables as $p)
<tr class="st-tr">
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ format_display_date($p->date) }}</td>
<td class="st-td px-4 py-3 text-sm whitespace-nowrap font-mono text-[#586064]">{{ $p->received_date ? format_display_date($p->received_date) : '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-semibold text-[#2B3437]">{{ $p->invoice_number ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-[#586064]">{{ $p->customer_code ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums text-[#2B3437]">{{ $currencySymbol }} {{ number_format((float) $p->amount, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums text-[#586064]">{{ $currencySymbol }} {{ number_format((float) ($p->direct_payment_total ?? 0), 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums text-[#586064]">{{ $currencySymbol }} {{ number_format((float) $p->received, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right tabular-nums text-[#5E5E5E]">{{ $currencySymbol }} {{ number_format((float) $p->remaining_balance, 2) }}</td>
<td class="st-td px-4 py-3 text-right">
<span class="text-[10px] font-bold uppercase tracking-wider text-[#5E5E5E]">Open</span>
</td>
<td class="st-td px-4 py-3 text-right">
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('payables.edit', $p) }}" class="text-[11px] font-bold uppercase tracking-wider text-[#5E5E5E] border border-[#5E5E5E] px-2 py-1 inline-flex items-center gap-1 hover:bg-[#F1F4F6]">
<span class="material-symbols-outlined text-[14px]">edit</span>
Manage
</a>
@else
<span class="text-xs text-[#586064]">—</span>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="10" class="px-6 py-10 text-center text-sm text-[#586064]">No open invoices for this customer.</td>
</tr>
@endforelse
</tbody>
</table>
</div>
</div>

@if($settledPayables->isNotEmpty())
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
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Paid</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Status</th>
</tr>
</thead>
<tbody>
@foreach ($settledPayables as $p)
<tr class="st-tr">
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ format_display_date($p->date) }}</td>
<td class="st-td px-4 py-3 text-sm font-semibold text-[#2B3437]">{{ $p->invoice_number ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums text-[#2B3437]">{{ $currencySymbol }} {{ number_format((float) $p->amount, 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums text-[#586064]">{{ $currencySymbol }} {{ number_format((float) ($p->direct_payment_total ?? 0), 2) }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums text-[#2B3437]">{{ $currencySymbol }} {{ number_format((float) $p->received, 2) }}</td>
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

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-4 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
