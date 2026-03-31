<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Statement — ' . Str::title(Str::lower($customer->customer_name)) . ' - ERP'])
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
@php
    $statementFiltered = $statementFiltered ?? false;
    $stmtQuery = array_filter([
        'from' => old('from', request('from')),
        'to' => old('to', request('to')),
    ], fn ($v) => filled($v));
    $pdfHref = route('customers.statement.pdf', $customer);
    if ($stmtQuery !== []) {
        $pdfHref .= '?'.http_build_query($stmtQuery);
    }
@endphp
<div class="flex items-center gap-2 flex-wrap justify-end">
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('customers.edit', $customer) }}" class="st-btn-secondary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">edit</span>
<span class="hidden sm:inline">Edit</span>
</a>
@endif
<a href="{{ $pdfHref }}" class="st-btn-primary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
<span class="hidden sm:inline">Download PDF</span>
</a>
<button type="button" onclick="window.print()" class="st-btn-secondary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">print</span>
<span class="hidden sm:inline">Print</span>
</button>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth statement-print-root">
<div class="max-w-[1200px] mx-auto flex flex-col gap-8">
@php
    $company = \App\Support\StatementCompany::normalize($company ?? config('company'));
@endphp

<div class="st-paper border border-[#ABB3B7] p-5 md:p-6 bg-white">
<p class="st-label mb-2">Statement from</p>
@if(filled($company['name'] ?? null))
<p class="text-base font-bold text-[#2B3437] leading-snug">{{ $company['name'] }}</p>
@endif
@foreach($company['address_lines'] ?? [] as $addrLine)
@if(filled($addrLine))
<p class="text-sm text-[#2B3437] mt-1 leading-relaxed">{{ $addrLine }}</p>
@endif
@endforeach
<div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm text-[#2B3437]">
@if(filled($company['registration'] ?? null))
<div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] mb-0.5">CR</p>
<p class="font-mono font-semibold">{{ $company['registration'] }}</p>
</div>
@endif
@if(filled($company['vat_number'] ?? null))
<div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] mb-0.5">VAT</p>
<p class="font-mono font-semibold">{{ $company['vat_number'] }}</p>
</div>
@endif
@if(filled($company['phone'] ?? null))
<div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] mb-0.5">{{ $company['phone_label'] ?? 'Phone' }}</p>
<p class="font-semibold">{{ $company['phone'] }}</p>
</div>
@endif
@if(filled($company['email'] ?? null))
<div class="sm:col-span-2">
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] mb-0.5">Email</p>
<p class="font-semibold break-all">{{ $company['email'] }}</p>
</div>
@endif
</div>
</div>

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">Account statement</p>
<h1 class="text-4xl md:text-5xl font-black tracking-tight text-[#2B3437] leading-none">{{ Str::title(Str::lower($customer->customer_name)) }}</h1>
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

@if ($errors->any())
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D] space-y-1">
<p class="font-semibold">Could not apply date filter</p>
<ul class="list-disc list-inside text-[13px]">
@foreach ($errors->all() as $err)
<li>{{ $err }}</li>
@endforeach
</ul>
</div>
@endif

<div class="no-print st-paper border border-[#ABB3B7] p-4 md:p-5 bg-white">
<p class="st-label mb-3">Statement period</p>
<form method="get" action="{{ route('customers.statement', $customer) }}" class="flex flex-col sm:flex-row sm:flex-wrap sm:items-end gap-3">
<div class="flex flex-col gap-1 min-w-0">
<label class="text-[10px] font-bold uppercase tracking-widest text-[#586064]" for="stmt_from">From</label>
<input class="st-input w-full sm:w-44 px-3 py-2 text-sm" type="date" id="stmt_from" name="from" value="{{ old('from', request('from')) }}">
</div>
<div class="flex flex-col gap-1 min-w-0">
<label class="text-[10px] font-bold uppercase tracking-widest text-[#586064]" for="stmt_to">To</label>
<input class="st-input w-full sm:w-44 px-3 py-2 text-sm" type="date" id="stmt_to" name="to" value="{{ old('to', request('to')) }}">
</div>
<div class="flex flex-wrap items-center gap-2">
<button type="submit" class="st-btn-primary h-9 px-4 inline-flex items-center gap-2 text-[10px]">Apply</button>
<a href="{{ route('customers.statement', $customer) }}" class="st-btn-secondary h-9 px-4 inline-flex items-center gap-2 text-[10px]">Clear</a>
</div>
</form>
@if ($statementFiltered && isset($periodFrom, $periodTo))
<p class="text-sm text-[#2B3437] mt-3 font-semibold">Showing {{ $periodFrom->format('j M Y') }} – {{ $periodTo->format('j M Y') }}</p>
@endif
</div>

@if(auth()->user()->role !== 'viewer')
<div class="no-print st-paper border border-[#ABB3B7] p-5 md:p-6 bg-white">
<p class="st-label mb-3">Email statement</p>
@if(filled($customer->email))
<form method="POST" action="{{ route('customers.statement.email', $customer) }}" class="flex flex-col gap-4 max-w-xl">
@csrf
@if (filled(old('from', request('from'))) && filled(old('to', request('to'))))
<input type="hidden" name="from" value="{{ old('from', request('from')) }}">
<input type="hidden" name="to" value="{{ old('to', request('to')) }}">
@endif
<div>
<p class="text-[11px] font-bold uppercase tracking-widest text-[#586064] mb-1">Recipient</p>
<p class="text-sm font-semibold text-[#2B3437] break-all">{{ $customer->email }}</p>
</div>
<div>
<label class="st-label block mb-2" for="email_message">Optional message</label>
<textarea class="st-input w-full min-h-[88px] px-3 py-2 text-sm" id="email_message" name="message" maxlength="2000" placeholder="Short note to include in the email body…">{{ old('message') }}</textarea>
<p class="text-[10px] text-[#586064] mt-1">Max 2000 characters. PDF is attached automatically.</p>
</div>
<button type="submit" class="st-btn-primary h-10 px-4 inline-flex items-center gap-2 text-[10px] w-fit">
<span class="material-symbols-outlined text-[18px]">mail</span>
Email statement
</button>
</form>
@else
<p class="text-sm text-[#9F403D] mb-3">This customer has no email address. Add one on the customer record to send statements by email.</p>
<a href="{{ route('customers.edit', $customer) }}" class="st-btn-secondary h-9 px-4 inline-flex items-center gap-2 text-[10px]">Edit customer</a>
@endif
</div>
@endif

<div class="st-paper border border-[#ABB3B7] p-6 bg-white">
<div class="flex flex-col lg:flex-row lg:flex-nowrap items-start justify-between gap-6">
<div class="flex min-w-0 w-full flex-1 flex-col gap-6">
<div class="grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-6 min-w-0">
<div class="min-w-0">
<p class="st-label mb-1">Phone</p>
<p class="text-sm font-semibold text-[#2B3437] break-words">{{ $customer->phone ?: '—' }}</p>
</div>
<div class="min-w-0">
<p class="st-label mb-1">Email</p>
<p class="text-sm font-semibold text-[#2B3437] break-all sm:break-words">{{ $customer->email ?: '—' }}</p>
</div>
<div class="min-w-0">
<p class="st-label mb-1">{{ $statementFiltered ? 'Balance brought forward' : 'Opening balance' }}</p>
<p class="text-sm font-mono font-semibold tabular-nums text-[#2B3437] break-words">
{{ $currencySymbol ?? '$' }} {{ number_format($openingBalance, 2) }}
@if($statementFiltered && isset($periodFrom))
<span class="text-[10px] font-sans ml-1 text-[#586064]">at start of period ({{ $periodFrom->format('Y-m-d') }})</span>
@elseif($customer->opening_balance_date)
<span class="text-[10px] font-sans ml-1 text-[#586064]">as of {{ $customer->opening_balance_date->format('Y-m-d') }}</span>
@endif
</p>
</div>
</div>
<div class="min-w-0 w-full">
<p class="st-label mb-1">Address</p>
<p class="text-sm font-semibold text-[#2B3437] break-words whitespace-pre-wrap">{{ $customer->address ?: '—' }}</p>
</div>
</div>
<div class="text-right shrink-0 border border-[#ABB3B7] p-4 bg-[#F8F9FA] w-full lg:w-auto lg:min-w-[200px]">
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
<div class="p-5 border-2 border-[#5E5E5E] max-md:m-0 -m-px">
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
<table class="w-full text-left border-collapse min-w-[800px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 w-32 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3">Description</th>
<th class="st-th px-4 py-3">Invoice #</th>
<th class="st-th px-4 py-3 text-right w-32 whitespace-nowrap">Debit</th>
<th class="st-th px-4 py-3 text-right w-32 whitespace-nowrap">Credit</th>
<th class="st-th px-4 py-3 text-right w-36 whitespace-nowrap">Balance</th>
</tr>
</thead>
<tbody>
<tr class="st-tr bg-[#F8F9FA]">
<td class="st-td px-4 py-3 text-sm text-[#586064]">
@if($statementFiltered && isset($periodFrom))
{{ $periodFrom->format('Y-m-d') }}
@else
{{ $customer->opening_balance_date ? $customer->opening_balance_date->format('Y-m-d') : '—' }}
@endif
</td>
<td class="st-td px-4 py-3 text-sm">
<span class="font-semibold text-[#586064]">{{ $statementFiltered ? 'Balance brought forward' : 'Opening balance' }}</span>
</td>
<td class="st-td px-4 py-3 text-sm font-mono text-[#586064]">—</td>
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
<td class="st-td px-4 py-3 text-sm font-mono text-[#586064] break-words max-w-[12rem]">
{{ $row['invoice_number'] ?: '—' }}
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
<p class="font-semibold text-[#2B3437] mb-1">{{ $statementFiltered ? 'No transactions in this period' : 'No transactions yet' }}</p>
<p class="text-xs">{{ $statementFiltered ? 'Try a different date range or clear the filter to see full history.' : 'Activity appears when sales, purchases, or payments are posted.' }}</p>
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

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-4 pb-2 no-print">© 2026 Laiba Safety. All rights reserved.</p>
<div class="print-title hidden text-center pt-6 text-[8pt] text-[#666]">
<p>This statement is computer-generated.</p>
<p>© 2026 Laiba Safety. All rights reserved.</p>
</div>

</div>
</div>

</main>
</body>
</html>
