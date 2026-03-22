<!DOCTYPE html>
<html lang="en" class="dark">
<head>
@include('partials.frontend-head', ['title' => 'Sale #' . ($sale->invoice_number ?: $sale->id) . ' - Laiba Safety'])
<style>
body { background-color: #131313; color: #e2e2e2; font-family: 'Inter', sans-serif; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #FFFFFF; color: #131313; }
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'sales'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#131313;">

<header class="h-16 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="background:#1B1B1B;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 hover:text-white rounded-md" style="color:#8e9192;" type="button" data-sidebar-toggle aria-label="Toggle menu"><span class="material-symbols-outlined">menu</span></button>
<a href="{{ route('sales.index') }}" class="flex items-center gap-2 transition-colors" style="color:#C4C7C8;" onmouseover="this.style.color='#FFFFFF'" onmouseout="this.style.color='#C4C7C8'">
<span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
<span class="text-sm font-medium hidden sm:inline">Back to Sales</span>
</a>
</div>
<div class="flex items-center gap-3">
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('sales.edit', $sale) }}" class="h-9 px-5 text-[11px] font-bold uppercase flex items-center gap-2 active:scale-[0.98] transition-all" style="background:#FFFFFF;color:#2F3131;border-radius:0.375rem;letter-spacing:0.05em;" onmouseover="this.style.background='#C6C6C7'" onmouseout="this.style.background='#FFFFFF'">
<span class="material-symbols-outlined" style="font-size:14px;">edit</span>EDIT
</a>
@endif
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[900px] mx-auto flex flex-col" style="gap:2rem;">

{{-- Heading --}}
<div class="flex flex-wrap items-start justify-between gap-4">
<div>
<span class="text-[11px] font-medium uppercase block mb-2" style="letter-spacing:0.2em;color:#8e9192;">Sale Record</span>
<h2 class="text-white font-black" style="font-size:2rem;letter-spacing:-0.02em;line-height:1.1;">{{ $sale->invoice_number ?: '#' . $sale->id }}</h2>
<p class="text-sm font-medium" style="color:#C4C7C8;margin-top:0.5rem;">{{ $sale->date->format('l, F j, Y \a\t g:i A') }}</p>
</div>
<span class="text-[10px] font-bold uppercase px-3 py-1.5" style="letter-spacing:0.1em;background:rgba(255,255,255,0.05);color:#C4C7C8;border-radius:0.25rem;">{{ ucfirst($sale->status) }}</span>
</div>

{{-- Customer Info --}}
<div style="background:#1B1B1B;border-radius:0.5rem;padding:2rem;">
<p class="text-[10px] font-bold uppercase mb-4" style="letter-spacing:0.15em;color:#8e9192;">Customer Information</p>
<div class="grid grid-cols-1 sm:grid-cols-2" style="gap:1.5rem;">
<div>
<p class="text-[10px] font-bold uppercase mb-1" style="letter-spacing:0.15em;color:#C4C7C8;">Name</p>
<p class="text-sm font-bold text-white">{{ $sale->customer_name ?: $sale->customer_code ?: 'Walk-in' }}</p>
</div>
@if($sale->customer_code)
<div>
<p class="text-[10px] font-bold uppercase mb-1" style="letter-spacing:0.15em;color:#C4C7C8;">Code</p>
<p class="text-sm font-medium text-white tabular-nums">{{ $sale->customer_code }}</p>
</div>
@endif
@if($sale->invoice_number)
<div>
<p class="text-[10px] font-bold uppercase mb-1" style="letter-spacing:0.15em;color:#C4C7C8;">Invoice</p>
<p class="text-sm font-medium text-white tabular-nums">{{ $sale->invoice_number }}</p>
</div>
@endif
</div>
</div>

{{-- Line Items Table --}}
@php $symbol = $sale->currency && $sale->currency->symbol ? $sale->currency->symbol : ($currencySymbol ?? '$'); @endphp

<div style="background:#1B1B1B;border-radius:0.5rem;overflow:hidden;">
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse" style="min-width:520px;">
<thead>
<tr style="background:#0E0E0E;">
<th class="px-6 py-4 text-[10px] font-bold uppercase" style="letter-spacing:0.15em;color:#8e9192;width:40px;">#</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase" style="letter-spacing:0.15em;color:#8e9192;">Product</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.15em;color:#8e9192;">Qty</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.15em;color:#8e9192;">Unit Price</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.15em;color:#8e9192;">Tax</th>
<th class="px-6 py-4 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.15em;color:#8e9192;">Line Total</th>
</tr>
</thead>
<tbody>
@foreach($sale->items as $index => $item)
@php $lineTotal = ($item->selling_price * $item->quantity) + ($item->tax_applied ?? 0); @endphp
<tr class="transition-colors" style="border-top:1px solid rgba(68,71,72,0.15);" onmouseover="this.style.background='#2A2A2A'" onmouseout="this.style.background='transparent'">
<td class="px-6 py-4 text-sm tabular-nums" style="color:#8e9192;">{{ $index + 1 }}</td>
<td class="px-6 py-4">
<p class="text-sm font-bold text-white">{{ $item->product ? $item->product->name : 'Product #' . $item->product_id }}</p>
@if($item->product?->sku)
<p class="text-xs" style="color:#8e9192;margin-top:0.125rem;">{{ $item->product->sku }}</p>
@endif
</td>
<td class="px-6 py-4 text-sm tabular-nums text-right" style="color:#C4C7C8;">{{ number_format($item->quantity) }}</td>
<td class="px-6 py-4 text-sm tabular-nums text-right text-white whitespace-nowrap">{{ $symbol }} {{ number_format($item->selling_price, 2) }}</td>
<td class="px-6 py-4 text-sm tabular-nums text-right whitespace-nowrap" style="color:#C4C7C8;">{{ $symbol }} {{ number_format($item->tax_applied ?? 0, 2) }}</td>
<td class="px-6 py-4 text-sm font-bold tabular-nums text-right text-white whitespace-nowrap">{{ $symbol }} {{ number_format($lineTotal, 2) }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>

{{-- Summary --}}
<div style="padding:1.5rem 2rem;background:#0E0E0E;">
<div class="flex justify-end">
<div style="min-width:240px;" class="flex flex-col gap-2">
<div class="flex justify-between text-sm"><span style="color:#8e9192;">Subtotal</span><span class="text-white font-bold tabular-nums">{{ $symbol }} {{ number_format($sale->subtotal, 2) }}</span></div>
<div class="flex justify-between text-sm"><span style="color:#8e9192;">Tax ({{ number_format($sale->tax_rate ?? 0, 0) }}%)</span><span class="text-white font-bold tabular-nums">{{ $symbol }} {{ number_format($sale->tax_amount ?? 0, 2) }}</span></div>
@if((float)($sale->discount_amount ?? 0) > 0)
<div class="flex justify-between text-sm"><span style="color:#8e9192;">Discount</span><span class="font-bold tabular-nums" style="color:#FFB4AB;">−{{ $symbol }} {{ number_format($sale->discount_amount, 2) }}</span></div>
@endif
<div class="flex justify-between text-lg font-black" style="padding-top:0.75rem;border-top:1px solid rgba(68,71,72,0.2);"><span class="text-white">Total</span><span class="text-white tabular-nums">{{ $symbol }} {{ number_format($sale->total_amount, 2) }}</span></div>
</div>
</div>
</div>
</div>

{{-- Actions --}}
<div class="flex flex-wrap items-center gap-3">
<a href="{{ route('sales.index') }}" class="h-10 px-5 text-[11px] font-bold uppercase flex items-center gap-2 transition-all" style="color:#C4C7C8;border:1px solid rgba(68,71,72,0.4);border-radius:0.375rem;letter-spacing:0.05em;" onmouseover="this.style.color='#FFFFFF';this.style.borderColor='#FFFFFF'" onmouseout="this.style.color='#C4C7C8';this.style.borderColor='rgba(68,71,72,0.4)'">
<span class="material-symbols-outlined" style="font-size:16px;">list</span>ALL SALES
</a>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('sales.edit', $sale) }}" class="h-10 px-5 text-[11px] font-bold uppercase flex items-center gap-2 active:scale-[0.98] transition-all" style="background:#FFFFFF;color:#2F3131;border-radius:0.375rem;letter-spacing:0.05em;" onmouseover="this.style.background='#C6C6C7'" onmouseout="this.style.background='#FFFFFF'">
<span class="material-symbols-outlined" style="font-size:16px;">edit</span>EDIT SALE
</a>
<form method="POST" action="{{ route('sales.destroy', $sale) }}" class="inline-flex" onsubmit="return confirm('Delete this sale? Stock will be restored and the related receivable removed.');">
@csrf @method('DELETE')
<button type="submit" class="h-10 px-5 text-[11px] font-bold uppercase flex items-center gap-2 active:scale-[0.98] transition-all" style="color:#FFB4AB;border:1px solid rgba(255,180,171,0.2);border-radius:0.375rem;letter-spacing:0.05em;" onmouseover="this.style.background='rgba(255,180,171,0.08)';this.style.borderColor='#FFB4AB'" onmouseout="this.style.background='transparent';this.style.borderColor='rgba(255,180,171,0.2)'">
<span class="material-symbols-outlined" style="font-size:16px;">delete</span>DELETE
</button>
</form>
@endif
</div>

<div class="text-center text-[10px] uppercase font-medium pb-4" style="margin-top:1rem;letter-spacing:0.15em;color:#8e9192;">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</div>
</div>
</div>
</main>
</body>
</html>
