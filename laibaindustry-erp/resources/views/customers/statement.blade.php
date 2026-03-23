<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Statement — ' . $customer->customer_name . ' - ERP'])
@include('partials.stitch-design')
<style>
@media print {
    @page { margin: 16mm; }
    .no-print, [data-sidebar-toggle] { display: none !important; }
    #sidebar-overlay { display: none !important; }
    aside#sidebar { display: none !important; }
    body { background: #fff !important; color: #000 !important; overflow: visible !important; height: auto !important; }
    main.stitch-ui { background: #fff !important; color: #000 !important; overflow: visible !important; height: auto !important; }
    .statement-print-root { padding: 0 !important; }
    .st-paper, .st-tr, .st-td, .st-th { border-color: #ccc !important; }
    .st-thead, tr[style*="background"] { background: #f0f0f0 !important; }
    * { box-shadow: none !important; }
    table { break-inside: auto; }
    tr { break-inside: avoid; break-after: auto; }
    .print-title { display: block !important; }
}
</style>
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
<div class="no-print">@include('products.partials.sidebar', ['activeNav' => 'customers'])</div>

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">

<header class="no-print h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<a href="{{ route('customers.index') }}" class="st-btn-secondary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
<span class="hidden sm:inline">Customers</span>
</a>
</div>
<div class="flex items-center gap-2">
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('customers.edit', $customer) }}" class="st-btn-secondary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">edit</span>
<span class="hidden sm:inline">Edit</span>
</a>
@endif
<button type="button" onclick="window.print()" class="st-btn-primary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">download</span>
<span class="hidden sm:inline">PDF / Print</span>
</button>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth statement-print-root">
<div class="max-w-[1200px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">ACCT_STMT_10</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">{{ $customer->customer_name }}</h1>
<p class="text-sm font-mono text-[#586064] mt-1">{{ $customer->customer_code }}</p>
</div>
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

<div class="st-paper border border-[#ABB3B7] p-6 bg-white">
<div class="flex flex-wrap items-start justify-between gap-6">
<div class="grid grid-cols-2 sm:grid-cols-4 gap-6 flex-1">
<div>
<p class="st-label mb-1">Phone</p>
<p class="text-sm font-semibold text-[#2B3437]">{{ $customer->phone ?: '—' }}</p>
</div>
<div>
<p class="st-label mb-1">Email</p>
<p class="text-sm font-semibold text-[#2B3437]">{{ $customer->email ?: '—' }}</p>
</div>
<div class="col-span-2 sm:col-span-1">
<p class="st-label mb-1">Address</p>
<p class="text-sm font-semibold text-[#2B3437]">{{ $customer->address ?: '—' }}</p>
</div>
<div>
<p class="st-label mb-1">Opening balance</p>
<p class="text-sm font-mono font-semibold tabular-nums text-[#2B3437]">
{{ $currencySymbol ?? '$' }} {{ number_format($openingBalance, 2) }}
@if($customer->opening_balance_date)
<span class="text-[10px] font-sans ml-1 text-[#586064]">as of {{ $customer->opening_balance_date->format('Y-m-d') }}</span>
@endif
</p>
</div>
</div>
<div class="text-right shrink-0 border border-[#ABB3B7] p-4 bg-[#F8F9FA] min-w-[200px]">
<p class="st-label mb-1">Closing balance</p>
<p class="text-3xl font-black font-mono tabular-nums text-[#5E5E5E]">
{{ $currencySymbol ?? '$' }} {{ number_format(abs($closingBalance), 2) }}
</p>
@if($closingBalance > 0)
<span class="inline-block mt-2 text-[10px] font-bold uppercase tracking-wider text-[#586064] border border-[#ABB3B7] px-2 py-0.5 bg-white">Receivable</span>
@elseif($closingBalance < 0)
<span class="inline-block mt-2 text-[10px] font-bold uppercase tracking-wider text-[#586064] border border-[#ABB3B7] px-2 py-0.5 bg-white">Payable</span>
@else
<span class="inline-block mt-2 text-[10px] font-bold uppercase tracking-wider text-[#586064] border border-[#ABB3B7] px-2 py-0.5 bg-white">Settled</span>
@endif
</div>
</div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-0 border border-[#ABB3B7] bg-white md:divide-x md:divide-[#ABB3B7]">
<div class="p-5 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Total debit</p>
<p class="text-2xl font-bold font-mono tabular-nums text-[#2B3437]">{{ $currencySymbol ?? '$' }} {{ number_format($totalDebit, 2) }}</p>
<p class="text-xs mt-2 text-[#586064]">Sales + payments you made</p>
</div>
<div class="p-5 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Total credit</p>
<p class="text-2xl font-bold font-mono tabular-nums text-[#2B3437]">{{ $currencySymbol ?? '$' }} {{ number_format($totalCredit, 2) }}</p>
<p class="text-xs mt-2 text-[#586064]">Payments received + purchases</p>
</div>
<div class="p-5 border-2 border-[#5E5E5E] -m-px">
<p class="st-label st-label--primary mb-2">Net balance</p>
<p class="text-2xl font-black font-mono tabular-nums text-[#5E5E5E]">
{{ $currencySymbol ?? '$' }} {{ number_format(abs($closingBalance), 2) }}
</p>
<p class="text-xs mt-2 text-[#586064]">
{{ $closingBalance > 0 ? 'Receivable' : ($closingBalance < 0 ? 'Payable' : 'Settled') }}
</p>
</div>
</div>

<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white overflow-hidden">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">Account ledger</h3>
<p class="text-[11px] text-[#586064] mt-1">Chronological transactions</p>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[780px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 w-32 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3">Description</th>
<th class="st-th px-4 py-3">Customer code</th>
<th class="st-th px-4 py-3 text-right w-32 whitespace-nowrap">Debit</th>
<th class="st-th px-4 py-3 text-right w-32 whitespace-nowrap">Credit</th>
<th class="st-th px-4 py-3 text-right w-36 whitespace-nowrap">Balance</th>
</tr>
</thead>
<tbody>
<tr class="st-tr bg-[#F8F9FA]">
<td class="st-td px-4 py-3 text-sm text-[#586064]">
{{ $customer->opening_balance_date ? $customer->opening_balance_date->format('Y-m-d') : '—' }}
</td>
<td class="st-td px-4 py-3 text-sm">
<span class="font-semibold text-[#586064]">Opening balance</span>
</td>
<td class="st-td px-4 py-3 text-sm font-mono text-[#586064]">{{ $customer->customer_code ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right text-[#ABB3B7]">—</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right text-[#ABB3B7]">—</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right tabular-nums text-[#2B3437]">
{{ $currencySymbol ?? '$' }} {{ number_format($openingBalance, 2) }}
</td>
</tr>

@forelse($ledgerRows as $row)
@php
$isDebit  = $row['debit']  > 0;
$isCredit = $row['credit'] > 0;
$badge = match($row['source_type']) {
    'sale'             => 'Sale',
    'payment_received' => 'Payment',
    'purchase'         => 'Purchase',
    'payment_made'     => 'Paid Out',
    default            => 'Entry',
};
@endphp
<tr class="st-tr">
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">
{{ $row['date']->format('Y-m-d') }}
<span class="text-xs ml-1 text-[#ABB3B7]">{{ $row['date']->format('H:i') }}</span>
</td>
<td class="st-td px-4 py-3 text-sm">
<div class="flex items-center gap-2 flex-wrap">
<span class="text-[10px] font-bold uppercase tracking-wider border border-[#ABB3B7] bg-[#F8F9FA] text-[#586064] px-2 py-0.5 whitespace-nowrap">{{ $badge }}</span>
<span class="font-semibold text-[#2B3437]">{{ $row['description'] }}</span>
</div>
</td>
<td class="st-td px-4 py-3 text-sm font-mono text-[#586064]">
{{ $row['reference'] ?: '—' }}
</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums {{ $isDebit ? 'font-bold text-[#2B3437]' : 'text-[#ABB3B7]' }}">
@if($isDebit)
{{ $currencySymbol ?? '$' }} {{ number_format($row['debit'], 2) }}
@else
—
@endif
</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums {{ $isCredit ? 'font-bold text-[#2B3437]' : 'text-[#ABB3B7]' }}">
@if($isCredit)
{{ $currencySymbol ?? '$' }} {{ number_format($row['credit'], 2) }}
@else
—
@endif
</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums text-[#5E5E5E]">
{{ $currencySymbol ?? '$' }} {{ number_format($row['running_balance'], 2) }}
@if($row['running_balance'] > 0)
<span class="text-[10px] font-bold ml-0.5 text-[#586064]">DR</span>
@elseif($row['running_balance'] < 0)
<span class="text-[10px] font-bold ml-0.5 text-[#586064]">CR</span>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="6" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No transactions yet</p>
<p class="text-xs">Activity appears when sales, purchases, or payments are posted.</p>
</td>
</tr>
@endforelse
</tbody>

@if(count($ledgerRows) > 0)
<tfoot>
<tr class="bg-[#EAEFF1] border-t-2 border-[#ABB3B7]">
<td colspan="3" class="px-4 py-3 st-label">Totals</td>
<td class="px-4 py-3 text-sm font-bold font-mono text-right tabular-nums whitespace-nowrap text-[#2B3437]">
{{ $currencySymbol ?? '$' }} {{ number_format($totalDebit, 2) }}
</td>
<td class="px-4 py-3 text-sm font-bold font-mono text-right tabular-nums whitespace-nowrap text-[#2B3437]">
{{ $currencySymbol ?? '$' }} {{ number_format($totalCredit, 2) }}
</td>
<td class="px-4 py-3 text-sm font-bold font-mono text-right tabular-nums whitespace-nowrap text-[#5E5E5E]">
{{ $currencySymbol ?? '$' }} {{ number_format(abs($closingBalance), 2) }}
{{ $closingBalance > 0 ? 'DR' : ($closingBalance < 0 ? 'CR' : '') }}
</td>
</tr>
</tfoot>
@endif
</table>
</div>
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-4 pb-2 no-print">© {{ date('Y') }} Nexus ERP Inc.</p>
<div class="print-title hidden text-center pt-6 text-[8pt] text-[#666]">
<p>This statement is computer-generated.</p>
<p>© {{ date('Y') }} Laiba Safety. All rights reserved.</p>
</div>

</div>
</div>

</main>
</body>
</html>
