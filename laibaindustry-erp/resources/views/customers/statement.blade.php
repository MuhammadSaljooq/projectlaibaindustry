<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Statement — ' . $customer->customer_name . ' - Laiba Safety'])
<style>
body { background-color: #FFFFFF; color: #2B3437; font-family: 'Inter', sans-serif; color-scheme: light; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #2B3437; color: #FFFFFF; }
@media print {
    @page { margin: 20mm; }
    * { color: #000 !important; background: #fff !important; border-color: #ccc !important; box-shadow: none !important; }
    body { overflow: visible !important; height: auto !important; display: block !important; font-size: 10pt !important; }
    .no-print, nav, header, footer, [data-sidebar-toggle] { display: none !important; }
    main { overflow: visible !important; height: auto !important; position: static !important; width: 100% !important; }
    .no-scrollbar { overflow: visible !important; height: auto !important; }
    table { border-collapse: collapse !important; }
    th, td { border: 1px solid #ddd !important; padding: 6px 8px !important; }
    tr:hover { background: transparent !important; }
    .print-title { display: block !important; }
}
</style>
</head>
<body class="h-screen flex overflow-hidden">
<div class="no-print">@include('products.partials.sidebar', ['activeNav' => 'customers'])</div>

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#FFFFFF;">

<header class="h-14 flex items-center justify-between px-6 md:px-8 shrink-0 z-10 no-print" style="border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 rounded-none" style="color:#5E5E5E;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<a href="{{ route('customers.index') }}" class="flex items-center gap-2 text-sm font-bold transition-colors" style="color:#5E5E5E;" onmouseover="this.style.color='#2B3437'" onmouseout="this.style.color='#5E5E5E'">
<span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
Back to Customers
</a>
</div>
<div class="flex items-center gap-3">
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('customers.edit', $customer) }}" class="h-9 px-4 text-[11px] font-bold uppercase inline-flex items-center gap-2 transition-all whitespace-nowrap" style="color:#2B3437;border:1px solid #5E5E5E;border-radius:0;letter-spacing:0.05em;background:transparent;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:16px;">edit</span>
EDIT
</a>
@endif
<button type="button" onclick="window.print()" class="h-9 px-4 text-[11px] font-bold uppercase inline-flex items-center gap-2 transition-all whitespace-nowrap" style="background:#5E5E5E;color:#F8F8F8;border-radius:0;letter-spacing:0.05em;border:none;cursor:pointer;" onmouseover="this.style.opacity='0.92'" onmouseout="this.style.opacity='1'">
<span class="material-symbols-outlined" style="font-size:16px;">download</span>
PDF / PRINT
</button>
</div>
</header>

<div class="flex-1 overflow-y-auto no-scrollbar scroll-smooth">
<div class="max-w-[1200px] mx-auto px-6 md:px-8 py-8 flex flex-col" style="gap:3rem;">

<div>
<div class="flex flex-wrap items-end justify-between gap-4" style="padding-bottom:0.75rem;border-bottom:2px solid #5E5E5E;">
<div>
<h1 class="font-bold" style="font-size:1.75rem;letter-spacing:-0.02em;color:#2B3437;">{{ $customer->customer_name }}</h1>
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-top:0.5rem;">Account Statement</p>
<p class="text-sm font-bold font-mono mt-2 tabular-nums" style="color:#5E5E5E;">{{ $customer->customer_code }}</p>
</div>
<span class="text-[10px] font-bold uppercase px-3 py-1.5 shrink-0" style="letter-spacing:0.05em;border:1px solid #D3D8DE;color:#5E5E5E;">Stmt #{{ $customer->id }}</span>
</div>
</div>

@if (session('success'))
<div style="border:1px solid #D3D8DE;padding:0.75rem 1.25rem;" class="text-sm font-bold flex items-center gap-3">
<span class="material-symbols-outlined" style="color:#5E5E5E;font-size:20px;">check_circle</span>
<span>{{ session('success') }}</span>
</div>
@endif
@if (session('error'))
<div style="border:1px solid #9F403D;padding:0.75rem 1.25rem;" class="text-sm font-bold flex items-center gap-3">
<span class="material-symbols-outlined" style="color:#9F403D;font-size:20px;">error</span>
<span style="color:#9F403D;">{{ session('error') }}</span>
</div>
@endif

<div style="border:1px solid #D3D8DE;background:#FFFFFF;">
<div style="padding:1rem 1.5rem;border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Account Summary</p>
</div>
<div style="padding:1.5rem 2rem;">
<div class="flex flex-wrap items-start justify-between gap-6">
<div class="grid grid-cols-2 sm:grid-cols-4 gap-6 flex-1">
<div>
<p class="text-[10px] font-bold uppercase mb-1.5" style="letter-spacing:0.05em;color:#5E5E5E;">Phone</p>
<p class="text-sm font-bold" style="color:#2B3437;">{{ $customer->phone ?: '—' }}</p>
</div>
<div>
<p class="text-[10px] font-bold uppercase mb-1.5" style="letter-spacing:0.05em;color:#5E5E5E;">Email</p>
<p class="text-sm font-bold" style="color:#2B3437;">{{ $customer->email ?: '—' }}</p>
</div>
<div>
<p class="text-[10px] font-bold uppercase mb-1.5" style="letter-spacing:0.05em;color:#5E5E5E;">Address</p>
<p class="text-sm font-bold" style="color:#2B3437;">{{ $customer->address ?: '—' }}</p>
</div>
<div>
<p class="text-[10px] font-bold uppercase mb-1.5" style="letter-spacing:0.05em;color:#5E5E5E;">Opening Balance</p>
<p class="text-sm font-bold font-mono tabular-nums" style="color:#2B3437;">
{{ $currencySymbol ?? '$' }} {{ number_format($openingBalance, 2) }}
@if($customer->opening_balance_date)
<span class="text-[10px] font-sans ml-1 font-bold" style="color:#5E5E5E;">as of {{ $customer->opening_balance_date->format('Y-m-d') }}</span>
@endif
</p>
</div>
</div>
<div class="text-right shrink-0">
<p class="text-[10px] font-bold uppercase mb-1.5" style="letter-spacing:0.05em;color:#5E5E5E;">Closing Balance</p>
<p class="text-2xl font-bold font-mono tabular-nums" style="color:#2B3437;">
{{ $currencySymbol ?? '$' }} {{ number_format(abs($closingBalance), 2) }}
</p>
@if($closingBalance > 0)
<span class="inline-flex items-center mt-1 px-2 py-0.5 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;border:1px solid #D3D8DE;color:#2B3437;">Receivable</span>
@elseif($closingBalance < 0)
<span class="inline-flex items-center mt-1 px-2 py-0.5 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;border:1px solid #D3D8DE;color:#5E5E5E;">Payable</span>
@else
<span class="inline-flex items-center mt-1 px-2 py-0.5 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;border:1px solid #D3D8DE;color:#5E5E5E;">Settled</span>
@endif
</div>
</div>
</div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3" style="gap:1px;background:#D3D8DE;border:1px solid #D3D8DE;">
<div style="background:#FFFFFF;padding:1.5rem;">
<p class="text-[10px] font-bold uppercase mb-3" style="letter-spacing:0.05em;color:#5E5E5E;">Total Debit</p>
<p class="text-xl font-bold font-mono tabular-nums" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($totalDebit, 2) }}</p>
<p class="text-xs font-bold mt-2" style="color:#5E5E5E;">Sales + payments you made</p>
</div>
<div style="background:#FFFFFF;padding:1.5rem;">
<p class="text-[10px] font-bold uppercase mb-3" style="letter-spacing:0.05em;color:#5E5E5E;">Total Credit</p>
<p class="text-xl font-bold font-mono tabular-nums" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($totalCredit, 2) }}</p>
<p class="text-xs font-bold mt-2" style="color:#5E5E5E;">Payments received + purchases</p>
</div>
<div style="background:#F8F9FA;padding:1.5rem;">
<p class="text-[10px] font-bold uppercase mb-3" style="letter-spacing:0.05em;color:#5E5E5E;">Net Balance</p>
<p class="text-xl font-bold font-mono tabular-nums" style="color:#2B3437;">
{{ $currencySymbol ?? '$' }} {{ number_format(abs($closingBalance), 2) }}
</p>
<p class="text-xs font-bold mt-2" style="color:#5E5E5E;">
{{ $closingBalance > 0 ? 'Receivable' : ($closingBalance < 0 ? 'Payable' : 'Settled') }}
</p>
</div>
</div>

<div style="border:1px solid #D3D8DE;">
<div style="padding:1rem 1.5rem;border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-sm font-bold" style="color:#2B3437;">Account Ledger</p>
<p class="text-xs font-bold mt-0.5" style="color:#5E5E5E;">All transactions in chronological order</p>
</div>

<div class="overflow-x-auto">
<table class="w-full text-left border-collapse min-w-[780px]">
<thead>
<tr style="background:#F8F9FA;border-bottom:1px solid #D3D8DE;">
<th class="px-5 py-3 text-[10px] font-bold uppercase w-32" style="letter-spacing:0.05em;color:#5E5E5E;">Date</th>
<th class="px-5 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Description</th>
<th class="px-5 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Reference</th>
<th class="px-5 py-3 text-[10px] font-bold uppercase text-right w-32" style="letter-spacing:0.05em;color:#5E5E5E;">Debit</th>
<th class="px-5 py-3 text-[10px] font-bold uppercase text-right w-32" style="letter-spacing:0.05em;color:#5E5E5E;">Credit</th>
<th class="px-5 py-3 text-[10px] font-bold uppercase text-right w-36" style="letter-spacing:0.05em;color:#5E5E5E;">Balance</th>
</tr>
</thead>
<tbody>

<tr style="background:#F8F9FA;border-top:1px solid #EAECEE;">
<td class="px-5 py-3 text-sm font-bold" style="color:#5E5E5E;">
{{ $customer->opening_balance_date ? $customer->opening_balance_date->format('Y-m-d') : '—' }}
</td>
<td class="px-5 py-3 text-sm">
<div class="flex items-center gap-2">
<span class="w-1.5 h-1.5 inline-block shrink-0" style="background:#5E5E5E;"></span>
<span class="font-bold" style="color:#2B3437;">Opening Balance</span>
</div>
</td>
<td class="px-5 py-3 text-sm font-bold" style="color:#5E5E5E;">—</td>
<td class="px-5 py-3 text-sm font-mono text-right font-bold" style="color:#D3D8DE;">—</td>
<td class="px-5 py-3 text-sm font-mono text-right font-bold" style="color:#D3D8DE;">—</td>
<td class="px-5 py-3 text-sm font-mono font-bold text-right tabular-nums" style="color:#2B3437;">
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
<tr class="transition-colors" style="border-top:1px solid #EAECEE;" onmouseenter="this.style.background='#F8F9FA'" onmouseleave="this.style.background='transparent'">
<td class="px-5 py-3 text-sm whitespace-nowrap font-bold" style="color:#5E5E5E;">
{{ $row['date']->format('Y-m-d') }}
<span class="text-xs ml-1" style="color:#5E5E5E;">{{ $row['date']->format('H:i') }}</span>
</td>
<td class="px-5 py-3 text-sm">
<div class="flex items-center gap-2.5">
<span class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold uppercase whitespace-nowrap" style="letter-spacing:0.05em;border:1px solid #D3D8DE;color:#5E5E5E;">
{{ $badge }}
</span>
<span class="font-bold" style="color:#2B3437;">{{ $row['description'] }}</span>
</div>
</td>
<td class="px-5 py-3 text-sm font-mono font-bold" style="color:#5E5E5E;">
{{ $row['reference'] ?: '—' }}
</td>
<td class="px-5 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums font-bold" style="color:{{ $isDebit ? '#2B3437' : '#D3D8DE' }};">
@if($isDebit)
{{ $currencySymbol ?? '$' }} {{ number_format($row['debit'], 2) }}
@else
—
@endif
</td>
<td class="px-5 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums font-bold" style="color:{{ $isCredit ? '#2B3437' : '#D3D8DE' }};">
@if($isCredit)
{{ $currencySymbol ?? '$' }} {{ number_format($row['credit'], 2) }}
@else
—
@endif
</td>
<td class="px-5 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums" style="color:#2B3437;">
{{ $currencySymbol ?? '$' }} {{ number_format($row['running_balance'], 2) }}
@if($row['running_balance'] > 0)
<span class="text-[10px] font-bold ml-0.5" style="color:#5E5E5E;">DR</span>
@elseif($row['running_balance'] < 0)
<span class="text-[10px] font-bold ml-0.5" style="color:#5E5E5E;">CR</span>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="6" class="px-5 py-16 text-center">
<span class="material-symbols-outlined block mb-3 mx-auto" style="font-size:40px;color:#D3D8DE;">receipt_long</span>
<p class="text-sm font-bold mb-1" style="color:#5E5E5E;">No transactions yet</p>
<p class="text-xs font-bold" style="color:#5E5E5E;">Transactions are added automatically when sales, purchases, or payments are recorded.</p>
</td>
</tr>
@endforelse

</tbody>

@if(count($ledgerRows) > 0)
<tfoot>
<tr style="background:#F8F9FA;border-top:1px solid #D3D8DE;">
<td colspan="3" class="px-5 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Totals</td>
<td class="px-5 py-3 text-sm font-bold font-mono text-right tabular-nums whitespace-nowrap" style="color:#2B3437;">
{{ $currencySymbol ?? '$' }} {{ number_format($totalDebit, 2) }}
</td>
<td class="px-5 py-3 text-sm font-bold font-mono text-right tabular-nums whitespace-nowrap" style="color:#2B3437;">
{{ $currencySymbol ?? '$' }} {{ number_format($totalCredit, 2) }}
</td>
<td class="px-5 py-3 text-sm font-bold font-mono text-right tabular-nums whitespace-nowrap" style="color:#2B3437;">
{{ $currencySymbol ?? '$' }} {{ number_format(abs($closingBalance), 2) }}
{{ $closingBalance > 0 ? 'DR' : ($closingBalance < 0 ? 'CR' : '') }}
</td>
</tr>
</tfoot>
@endif
</table>
</div>
</div>

<footer class="pt-4 pb-8 text-center no-print">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</p>
</footer>
<div class="print-title" style="display:none;text-align:center;padding-top:24px;font-size:8pt;color:#666;">
<p>This statement is computer-generated and requires no signature.</p>
<p>&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</p>
</div>

</div>
</div>

</main>
</body>
</html>
