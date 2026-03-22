<!DOCTYPE html>
<html lang="en" class="dark">
<head>
@include('partials.frontend-head', ['title' => 'Statement — ' . $customer->customer_name . ' - Laiba Safety'])
<style>
body { background-color: #131313; color: #e2e2e2; font-family: 'Inter', sans-serif; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #FFFFFF; color: #131313; }
@media print {
    body { background: #fff !important; color: #000 !important; }
    .no-print { display: none !important; }
}
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'customers'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#131313;">

<header class="h-16 flex items-center justify-between px-6 md:px-8 shrink-0 z-10 no-print" style="background:#1B1B1B;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 hover:text-white rounded-md" style="color:#8e9192;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<a href="{{ route('customers.index') }}" class="flex items-center gap-2 text-sm font-medium transition-colors duration-150" style="color:#8e9192;" onmouseenter="this.style.color='#FFFFFF'" onmouseleave="this.style.color='#8e9192'">
<span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
Back to Customers
</a>
</div>
<div class="flex items-center gap-3">
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('customers.edit', $customer) }}" class="h-10 px-4 inline-flex items-center gap-2 text-sm font-medium rounded-md transition-all duration-200 whitespace-nowrap" style="color:#C4C7C8;border:1px solid rgba(68,71,72,0.4);" onmouseenter="this.style.borderColor='#8e9192';this.style.color='#FFFFFF'" onmouseleave="this.style.borderColor='rgba(68,71,72,0.4)';this.style.color='#C4C7C8'">
<span class="material-symbols-outlined" style="font-size:18px;">edit</span>
EDIT
</a>
@endif
<a href="{{ route('customers.statement.pdf', $customer) }}" target="_blank" class="h-10 px-4 inline-flex items-center gap-2 text-sm font-medium rounded-md transition-all duration-200 whitespace-nowrap" style="color:#C4C7C8;border:1px solid rgba(68,71,72,0.4);" onmouseenter="this.style.borderColor='#8e9192';this.style.color='#FFFFFF'" onmouseleave="this.style.borderColor='rgba(68,71,72,0.4)';this.style.color='#C4C7C8'">
<span class="material-symbols-outlined" style="font-size:18px;">download</span>
PDF
</a>
<button onclick="window.print()" class="h-10 px-4 inline-flex items-center gap-2 text-sm font-medium rounded-md transition-all duration-200 whitespace-nowrap" style="color:#C4C7C8;border:1px solid rgba(68,71,72,0.4);" onmouseenter="this.style.borderColor='#8e9192';this.style.color='#FFFFFF'" onmouseleave="this.style.borderColor='rgba(68,71,72,0.4)';this.style.color='#C4C7C8'">
<span class="material-symbols-outlined" style="font-size:18px;">print</span>
PRINT
</button>
</div>
</header>

<div class="flex-1 overflow-y-auto no-scrollbar">
<div class="max-w-[1200px] mx-auto px-6 md:px-8 py-8 flex flex-col gap-8">

<div>
<p class="text-xs font-medium uppercase tracking-[0.15em] mb-2" style="color:#8e9192;">Account Statement</p>
<h1 class="text-4xl font-bold tracking-tight" style="color:#FFFFFF;letter-spacing:-0.02em;">{{ $customer->customer_name }}</h1>
<p class="text-sm font-mono mt-2" style="color:#8e9192;">{{ $customer->customer_code }}</p>
</div>

@if (session('success'))
<div class="flex items-center gap-3 px-5 py-3.5 rounded-md" style="background:rgba(255,255,255,0.04);">
<span class="material-symbols-outlined" style="color:#FFFFFF;font-size:20px;">check_circle</span>
<span class="text-sm font-medium" style="color:#C4C7C8;">{{ session('success') }}</span>
</div>
@endif

<div class="rounded-lg p-6" style="background:#1B1B1B;">
<div class="flex flex-wrap items-start justify-between gap-6">
<div class="grid grid-cols-2 sm:grid-cols-4 gap-6 flex-1">
<div>
<p class="text-[10px] font-semibold uppercase tracking-[0.15em] mb-1.5" style="color:#8e9192;">Phone</p>
<p class="text-sm font-medium" style="color:#FFFFFF;">{{ $customer->phone ?: '—' }}</p>
</div>
<div>
<p class="text-[10px] font-semibold uppercase tracking-[0.15em] mb-1.5" style="color:#8e9192;">Email</p>
<p class="text-sm font-medium" style="color:#FFFFFF;">{{ $customer->email ?: '—' }}</p>
</div>
<div>
<p class="text-[10px] font-semibold uppercase tracking-[0.15em] mb-1.5" style="color:#8e9192;">Address</p>
<p class="text-sm font-medium" style="color:#FFFFFF;">{{ $customer->address ?: '—' }}</p>
</div>
<div>
<p class="text-[10px] font-semibold uppercase tracking-[0.15em] mb-1.5" style="color:#8e9192;">Opening Balance</p>
<p class="text-sm font-mono font-medium tabular-nums" style="color:#FFFFFF;">
{{ $currencySymbol ?? '$' }} {{ number_format($openingBalance, 2) }}
@if($customer->opening_balance_date)
<span class="text-[10px] font-sans ml-1" style="color:#8e9192;">as of {{ $customer->opening_balance_date->format('Y-m-d') }}</span>
@endif
</p>
</div>
</div>
<div class="text-right shrink-0">
<p class="text-[10px] font-semibold uppercase tracking-[0.15em] mb-1.5" style="color:#8e9192;">Closing Balance</p>
<p class="text-3xl font-bold font-mono tabular-nums" style="color:#FFFFFF;">
{{ $currencySymbol ?? '$' }} {{ number_format(abs($closingBalance), 2) }}
</p>
@if($closingBalance > 0)
<span class="inline-flex items-center mt-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-[0.1em]" style="background:rgba(255,255,255,0.08);color:#FFFFFF;">Receivable</span>
@elseif($closingBalance < 0)
<span class="inline-flex items-center mt-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-[0.1em]" style="background:rgba(255,255,255,0.08);color:#C4C7C8;">Payable</span>
@else
<span class="inline-flex items-center mt-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-[0.1em]" style="background:rgba(255,255,255,0.08);color:#8e9192;">Settled</span>
@endif
</div>
</div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
<div class="rounded-lg p-5" style="background:#1B1B1B;">
<p class="text-[10px] font-semibold uppercase tracking-[0.15em] mb-3" style="color:#8e9192;">Total Debit</p>
<p class="text-2xl font-bold font-mono tabular-nums" style="color:#FFFFFF;">{{ $currencySymbol ?? '$' }} {{ number_format($totalDebit, 2) }}</p>
<p class="text-xs mt-2" style="color:#8e9192;">Sales + payments you made</p>
</div>
<div class="rounded-lg p-5" style="background:#1B1B1B;">
<p class="text-[10px] font-semibold uppercase tracking-[0.15em] mb-3" style="color:#8e9192;">Total Credit</p>
<p class="text-2xl font-bold font-mono tabular-nums" style="color:#FFFFFF;">{{ $currencySymbol ?? '$' }} {{ number_format($totalCredit, 2) }}</p>
<p class="text-xs mt-2" style="color:#8e9192;">Payments received + purchases</p>
</div>
<div class="rounded-lg p-5 relative overflow-hidden" style="background:#FFFFFF;">
<p class="text-[10px] font-semibold uppercase tracking-[0.15em] mb-3" style="color:#666;">Net Balance</p>
<p class="text-2xl font-bold font-mono tabular-nums" style="color:#131313;">
{{ $currencySymbol ?? '$' }} {{ number_format(abs($closingBalance), 2) }}
</p>
<p class="text-xs mt-2" style="color:#666;">
{{ $closingBalance > 0 ? 'Receivable' : ($closingBalance < 0 ? 'Payable' : 'Settled') }}
</p>
<div class="absolute top-4 right-4">
<span class="material-symbols-outlined" style="font-size:32px;color:rgba(19,19,19,0.08);">account_balance</span>
</div>
</div>
</div>

<div class="rounded-lg overflow-hidden" style="background:#1B1B1B;">
<div class="px-6 py-4 flex items-center justify-between" style="border-bottom:1px solid rgba(68,71,72,0.15);">
<div>
<p class="text-sm font-semibold" style="color:#FFFFFF;">Account Ledger</p>
<p class="text-xs mt-0.5" style="color:#8e9192;">All transactions in chronological order</p>
</div>
</div>

<div class="overflow-x-auto">
<table class="w-full text-left min-w-[780px]">
<thead>
<tr style="background:#0E0E0E;">
<th class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.15em] w-32" style="color:#8e9192;">Date</th>
<th class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.15em]" style="color:#8e9192;">Description</th>
<th class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.15em]" style="color:#8e9192;">Reference</th>
<th class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.15em] text-right w-32" style="color:#8e9192;">Debit</th>
<th class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.15em] text-right w-32" style="color:#8e9192;">Credit</th>
<th class="px-5 py-3.5 text-[10px] font-semibold uppercase tracking-[0.15em] text-right w-36" style="color:#8e9192;">Balance</th>
</tr>
</thead>
<tbody>

<tr style="background:rgba(14,14,14,0.4);">
<td class="px-5 py-3 text-sm" style="color:#8e9192;">
{{ $customer->opening_balance_date ? $customer->opening_balance_date->format('Y-m-d') : '—' }}
</td>
<td class="px-5 py-3 text-sm">
<div class="flex items-center gap-2">
<span class="w-1.5 h-1.5 rounded-full inline-block shrink-0" style="background:#8e9192;"></span>
<span class="font-medium" style="color:#C4C7C8;">Opening Balance</span>
</div>
</td>
<td class="px-5 py-3 text-sm" style="color:#8e9192;">—</td>
<td class="px-5 py-3 text-sm font-mono text-right" style="color:#555;">—</td>
<td class="px-5 py-3 text-sm font-mono text-right" style="color:#555;">—</td>
<td class="px-5 py-3 text-sm font-mono font-semibold text-right tabular-nums" style="color:#FFFFFF;">
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
<tr class="transition-colors duration-150" style="border-bottom:1px solid rgba(68,71,72,0.1);" onmouseenter="this.style.background='#2A2A2A'" onmouseleave="this.style.background='transparent'">
<td class="px-5 py-3 text-sm whitespace-nowrap" style="color:#C4C7C8;">
{{ $row['date']->format('Y-m-d') }}
<span class="text-xs ml-1" style="color:#555;">{{ $row['date']->format('H:i') }}</span>
</td>
<td class="px-5 py-3 text-sm">
<div class="flex items-center gap-2.5">
<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-[0.05em] whitespace-nowrap" style="background:#353535;color:#C4C7C8;">
{{ $badge }}
</span>
<span class="font-medium" style="color:#FFFFFF;">{{ $row['description'] }}</span>
</div>
</td>
<td class="px-5 py-3 text-sm font-mono" style="color:#8e9192;">
{{ $row['reference'] ?: '—' }}
</td>
<td class="px-5 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums" style="color:{{ $isDebit ? '#FFFFFF' : '#353535' }};font-weight:{{ $isDebit ? '600' : '400' }};">
@if($isDebit)
{{ $currencySymbol ?? '$' }} {{ number_format($row['debit'], 2) }}
@else
—
@endif
</td>
<td class="px-5 py-3 text-sm font-mono text-right whitespace-nowrap tabular-nums" style="color:{{ $isCredit ? '#FFFFFF' : '#353535' }};font-weight:{{ $isCredit ? '600' : '400' }};">
@if($isCredit)
{{ $currencySymbol ?? '$' }} {{ number_format($row['credit'], 2) }}
@else
—
@endif
</td>
<td class="px-5 py-3 text-sm font-mono font-semibold text-right whitespace-nowrap tabular-nums" style="color:#FFFFFF;">
{{ $currencySymbol ?? '$' }} {{ number_format($row['running_balance'], 2) }}
@if($row['running_balance'] > 0)
<span class="text-[10px] font-bold ml-0.5" style="color:#C4C7C8;">DR</span>
@elseif($row['running_balance'] < 0)
<span class="text-[10px] font-bold ml-0.5" style="color:#C4C7C8;">CR</span>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="6" class="px-5 py-16 text-center">
<span class="material-symbols-outlined block mb-3 mx-auto" style="font-size:40px;color:#353535;">receipt_long</span>
<p class="text-sm mb-1" style="color:#8e9192;">No transactions yet</p>
<p class="text-xs" style="color:#555;">Transactions are added automatically when sales, purchases, or payments are recorded.</p>
</td>
</tr>
@endforelse

</tbody>

@if(count($ledgerRows) > 0)
<tfoot>
<tr style="background:#0E0E0E;">
<td colspan="3" class="px-5 py-3.5 text-[10px] font-bold uppercase tracking-[0.15em]" style="color:#8e9192;">Totals</td>
<td class="px-5 py-3.5 text-sm font-bold font-mono text-right tabular-nums whitespace-nowrap" style="color:#FFFFFF;">
{{ $currencySymbol ?? '$' }} {{ number_format($totalDebit, 2) }}
</td>
<td class="px-5 py-3.5 text-sm font-bold font-mono text-right tabular-nums whitespace-nowrap" style="color:#FFFFFF;">
{{ $currencySymbol ?? '$' }} {{ number_format($totalCredit, 2) }}
</td>
<td class="px-5 py-3.5 text-sm font-bold font-mono text-right tabular-nums whitespace-nowrap" style="color:#FFFFFF;">
{{ $currencySymbol ?? '$' }} {{ number_format(abs($closingBalance), 2) }}
{{ $closingBalance > 0 ? 'DR' : ($closingBalance < 0 ? 'CR' : '') }}
</td>
</tr>
</tfoot>
@endif
</table>
</div>
</div>

<footer class="pt-4 pb-8 text-center">
<p class="text-xs" style="color:rgba(142,145,146,0.4);">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</p>
</footer>

</div>
</div>

</main>
</body>
</html>
