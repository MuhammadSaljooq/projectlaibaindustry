<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Receivable Detail - Laiba Safety'])
<style>
body { background-color: #FFFFFF; color: #2B3437; font-family: 'Inter', sans-serif; color-scheme: light; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #2B3437; color: #FFFFFF; }
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'receivables'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#FFFFFF;">

<header class="h-14 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 rounded-none" style="color:#5E5E5E;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<a href="{{ route('receivables.index') }}" class="flex items-center gap-2 text-sm font-bold transition-colors" style="color:#5E5E5E;" onmouseover="this.style.color='#2B3437'" onmouseout="this.style.color='#5E5E5E'">
<span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
Back to Receivables
</a>
</div>
</header>

<div class="flex-1 overflow-y-auto no-scrollbar scroll-smooth">
<div class="max-w-[700px] mx-auto px-6 md:px-8 py-8 flex flex-col" style="gap:3rem;">

<div>
<div class="flex items-end justify-between" style="padding-bottom:0.75rem;border-bottom:2px solid #5E5E5E;">
<div>
<h1 class="font-bold" style="font-size:1.5rem;letter-spacing:-0.02em;color:#2B3437;">Receivable detail</h1>
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-top:0.5rem;">Read-only summary</p>
</div>
<span class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">AR-{{ $receivable->id }}</span>
</div>
</div>

@php $remaining = (float)$receivable->amount - (float)$receivable->received; @endphp

<div style="border:1px solid #D3D8DE;background:#FFFFFF;">
<div style="padding:1rem 1.5rem;border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Balance details</p>
</div>
<div style="padding:2rem;">

<div class="grid grid-cols-2 sm:grid-cols-3 gap-6 mb-6">
<div>
<p class="text-[10px] font-bold uppercase mb-1.5" style="letter-spacing:0.05em;color:#5E5E5E;">Date</p>
<p class="text-sm font-bold" style="color:#2B3437;">{{ $receivable->date ? $receivable->date->format('Y-m-d') : '—' }}</p>
</div>
<div>
<p class="text-[10px] font-bold uppercase mb-1.5" style="letter-spacing:0.05em;color:#5E5E5E;">Invoice</p>
<p class="text-sm font-bold" style="color:#2B3437;">{{ $receivable->invoice_number ?: '—' }}</p>
</div>
<div class="col-span-2 sm:col-span-1">
<p class="text-[10px] font-bold uppercase mb-1.5" style="letter-spacing:0.05em;color:#5E5E5E;">Customer</p>
<p class="text-sm font-bold" style="color:#2B3437;">{{ $receivable->customer_name ?: $receivable->customer_code ?: '—' }}</p>
</div>
</div>

@if ($receivable->sales->isNotEmpty())
<div class="mb-8" style="border:1px solid #D3D8DE;">
<div style="padding:1rem 1.5rem;border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Invoices in this balance</p>
<p class="text-xs font-bold mt-0.5" style="color:#5E5E5E;">All sales contributing to this receivable.</p>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse min-w-[520px]">
<thead>
<tr style="background:#F8F9FA;border-bottom:1px solid #D3D8DE;">
<th class="px-4 py-2.5 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Date</th>
<th class="px-4 py-2.5 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Invoice #</th>
<th class="px-4 py-2.5 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;">Bill</th>
<th class="px-4 py-2.5 text-[10px] font-bold uppercase text-center w-24" style="letter-spacing:0.05em;color:#5E5E5E;">Sale</th>
</tr>
</thead>
<tbody>
@foreach ($receivable->sales as $s)
<tr style="border-top:1px solid #EAECEE;">
<td class="px-4 py-3 text-sm font-bold whitespace-nowrap" style="color:#5E5E5E;">{{ $s->date ? $s->date->format('Y-m-d') : '—' }}</td>
<td class="px-4 py-3 text-sm font-bold" style="color:#2B3437;">{{ $s->invoice_number ?: '—' }}</td>
<td class="px-4 py-3 text-sm font-mono font-bold text-right tabular-nums" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($s->total_amount, 2) }}</td>
<td class="px-4 py-3 text-center">
<a href="{{ route('sales.show', $s) }}" class="text-[10px] font-bold uppercase" style="color:#5E5E5E;border-bottom:1px solid #5E5E5E;letter-spacing:0.05em;">View</a>
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
</div>
@endif

<div class="pt-5 mb-6" style="border-top:1px solid #D3D8DE;">
<div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-center">
<div>
<p class="text-[10px] font-bold uppercase mb-1.5" style="letter-spacing:0.05em;color:#5E5E5E;">Bill</p>
<p class="text-lg font-bold font-mono tabular-nums" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($receivable->amount, 2) }}</p>
</div>
<div>
<p class="text-[10px] font-bold uppercase mb-1.5" style="letter-spacing:0.05em;color:#5E5E5E;">Already received</p>
<p class="text-lg font-bold font-mono tabular-nums" style="color:#5E5E5E;">{{ $currencySymbol ?? '$' }} {{ number_format($receivable->received, 2) }}</p>
</div>
<div>
<p class="text-[10px] font-bold uppercase mb-1.5" style="letter-spacing:0.05em;color:#5E5E5E;">Remaining</p>
<p class="text-lg font-bold font-mono tabular-nums" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($remaining, 2) }}</p>
</div>
</div>
</div>

@if (auth()->user()->role === 'viewer')
<div class="pt-5" style="border-top:1px solid #D3D8DE;">
<p class="text-xs font-bold" style="color:#5E5E5E;">You have view-only access. Ask an admin or manager to record payments.</p>
</div>
@elseif ($remaining > 0)
<div class="pt-5 flex flex-wrap gap-3" style="border-top:1px solid #D3D8DE;">
<a href="{{ route('receivables.edit', $receivable) }}" class="h-11 px-6 text-[11px] font-bold uppercase inline-flex items-center gap-2 transition-all whitespace-nowrap" style="background:#5E5E5E;color:#F8F8F8;letter-spacing:0.05em;" onmouseover="this.style.opacity='0.92'" onmouseout="this.style.opacity='1'">
<span class="material-symbols-outlined" style="font-size:16px;">payments</span>
Record payment
</a>
</div>
@else
<div class="pt-5 text-center" style="border-top:1px solid #D3D8DE;">
<span class="material-symbols-outlined block mb-2 mx-auto" style="font-size:32px;color:#5E5E5E;">check_circle</span>
<p class="text-sm font-bold" style="color:#5E5E5E;">This receivable is fully paid.</p>
</div>
@endif
</div>
</div>

<footer class="pb-8 text-center">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</p>
</footer>

</div>
</div>

</main>
</body>
</html>
