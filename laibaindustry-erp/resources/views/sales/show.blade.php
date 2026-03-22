<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Sale #' . ($sale->invoice_number ?: $sale->id) . ' - Laiba Safety'])
<style>
body { background-color: #FFFFFF; color: #2B3437; font-family: 'Inter', sans-serif; color-scheme: light; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #2B3437; color: #FFFFFF; }
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'sales'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#FFFFFF;">

<header class="h-14 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 rounded-none" style="color:#5E5E5E;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu"><span class="material-symbols-outlined">menu</span></button>
<a href="{{ route('sales.index') }}" class="flex items-center gap-2 transition-colors font-bold text-sm" style="color:#5E5E5E;" onmouseover="this.style.color='#2B3437'" onmouseout="this.style.color='#5E5E5E'">
<span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
<span class="hidden sm:inline">Back to Sales</span>
</a>
</div>
<div class="flex items-center gap-3">
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('sales.edit', $sale) }}" class="h-9 px-5 text-[11px] font-bold uppercase flex items-center gap-2 active:scale-[0.98] transition-all" style="background:#5E5E5E;color:#F8F8F8;border-radius:0;letter-spacing:0.05em;" onmouseover="this.style.opacity='0.92'" onmouseout="this.style.opacity='1'">
<span class="material-symbols-outlined" style="font-size:14px;">edit</span>EDIT
</a>
@endif
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[900px] mx-auto flex flex-col" style="gap:3rem;">

@php $symbol = $sale->currency && $sale->currency->symbol ? $sale->currency->symbol : ($currencySymbol ?? '$'); @endphp

<div>
<div class="flex flex-wrap items-end justify-between gap-4" style="padding-bottom:0.75rem;border-bottom:2px solid #5E5E5E;">
<div class="min-w-0 flex-1">
<h2 class="font-bold truncate" style="font-size:1.5rem;letter-spacing:-0.02em;color:#2B3437;">{{ $sale->invoice_number ?: '#' . $sale->id }}</h2>
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-top:0.5rem;">Sale Record</p>
<p class="text-sm font-bold" style="color:#5E5E5E;margin-top:0.35rem;">{{ $sale->date->format('l, F j, Y \a\t g:i A') }}</p>
</div>
<span class="text-[10px] font-bold uppercase px-3 py-1.5 shrink-0" style="letter-spacing:0.05em;border:1px solid #D3D8DE;color:#5E5E5E;background:#FFFFFF;">{{ ucfirst($sale->status) }}</span>
</div>
<span class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;display:block;margin-top:0.75rem;text-align:right;">Doc SLS-{{ $sale->id }}</span>
</div>

<div style="border:1px solid #D3D8DE;background:#FFFFFF;">
<div style="padding:1rem 1.5rem;border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Customer Information</p>
</div>
<div style="padding:2rem;">
<div class="grid grid-cols-1 sm:grid-cols-2" style="gap:1.5rem;">
<div>
<p class="text-[10px] font-bold uppercase mb-1" style="letter-spacing:0.05em;color:#5E5E5E;">Name</p>
<p class="text-sm font-bold" style="color:#2B3437;">{{ $sale->customer_name ?: $sale->customer_code ?: 'Walk-in' }}</p>
</div>
@if($sale->customer_code)
<div>
<p class="text-[10px] font-bold uppercase mb-1" style="letter-spacing:0.05em;color:#5E5E5E;">Code</p>
<p class="text-sm font-bold tabular-nums" style="color:#2B3437;">{{ $sale->customer_code }}</p>
</div>
@endif
@if($sale->invoice_number)
<div>
<p class="text-[10px] font-bold uppercase mb-1" style="letter-spacing:0.05em;color:#5E5E5E;">Invoice</p>
<p class="text-sm font-bold tabular-nums" style="color:#2B3437;">{{ $sale->invoice_number }}</p>
</div>
@endif
</div>
</div>
</div>

<div style="border:1px solid #D3D8DE;">
<div style="padding:1rem 1.5rem;border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Line Items</p>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse" style="min-width:520px;">
<thead>
<tr style="background:#F8F9FA;border-bottom:1px solid #D3D8DE;">
<th class="px-6 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;width:40px;">#</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Product</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;">Qty</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;">Unit Price</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;">Tax</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;">Line Total</th>
</tr>
</thead>
<tbody>
@foreach($sale->items as $index => $item)
@php $lineTotal = ($item->selling_price * $item->quantity) + ($item->tax_applied ?? 0); @endphp
<tr class="transition-colors" style="border-top:1px solid #EAECEE;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">
<td class="px-6 py-4 text-sm tabular-nums font-bold" style="color:#5E5E5E;">{{ $index + 1 }}</td>
<td class="px-6 py-4">
<p class="text-sm font-bold" style="color:#2B3437;">{{ $item->product ? $item->product->name : 'Product #' . $item->product_id }}</p>
@if($item->product?->sku)
<p class="text-xs font-bold" style="color:#5E5E5E;margin-top:0.125rem;letter-spacing:0.03em;">Article #{{ $item->product->sku }}</p>
@endif
</td>
<td class="px-6 py-4 text-sm tabular-nums text-right font-bold" style="color:#5E5E5E;">{{ number_format($item->quantity) }}</td>
<td class="px-6 py-4 text-sm tabular-nums text-right font-bold whitespace-nowrap" style="color:#2B3437;">{{ $symbol }} {{ number_format($item->selling_price, 2) }}</td>
<td class="px-6 py-4 text-sm tabular-nums text-right whitespace-nowrap font-bold" style="color:#5E5E5E;">{{ $symbol }} {{ number_format($item->tax_applied ?? 0, 2) }}</td>
<td class="px-6 py-4 text-sm font-bold tabular-nums text-right whitespace-nowrap" style="color:#2B3437;">{{ $symbol }} {{ number_format($lineTotal, 2) }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>

<div style="padding:1.5rem 2rem;border-top:1px solid #D3D8DE;background:#F8F9FA;">
<div class="flex justify-end">
<div style="min-width:240px;" class="flex flex-col gap-2">
<div class="flex justify-between text-sm font-bold"><span style="color:#5E5E5E;">Subtotal</span><span class="tabular-nums" style="color:#2B3437;">{{ $symbol }} {{ number_format($sale->subtotal, 2) }}</span></div>
<div class="flex justify-between text-sm font-bold"><span style="color:#5E5E5E;">Tax ({{ number_format($sale->tax_rate ?? 0, 0) }}%)</span><span class="tabular-nums" style="color:#2B3437;">{{ $symbol }} {{ number_format($sale->tax_amount ?? 0, 2) }}</span></div>
@if((float)($sale->discount_amount ?? 0) > 0)
<div class="flex justify-between text-sm font-bold"><span style="color:#5E5E5E;">Discount</span><span class="tabular-nums" style="color:#9F403D;">−{{ $symbol }} {{ number_format($sale->discount_amount, 2) }}</span></div>
@endif
<div class="flex justify-between text-base font-bold" style="padding-top:0.75rem;border-top:1px solid #D3D8DE;"><span style="color:#2B3437;">Total</span><span class="tabular-nums" style="color:#2B3437;">{{ $symbol }} {{ number_format($sale->total_amount, 2) }}</span></div>
</div>
</div>
</div>
</div>

<div class="flex flex-wrap items-center gap-3">
<a href="{{ route('sales.index') }}" class="h-10 px-5 text-[11px] font-bold uppercase flex items-center gap-2 transition-all" style="color:#2B3437;border:1px solid #5E5E5E;border-radius:0;letter-spacing:0.05em;background:transparent;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:16px;">list</span>ALL SALES
</a>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('sales.edit', $sale) }}" class="h-10 px-5 text-[11px] font-bold uppercase flex items-center gap-2 active:scale-[0.98] transition-all" style="background:#5E5E5E;color:#F8F8F8;border-radius:0;letter-spacing:0.05em;" onmouseover="this.style.opacity='0.92'" onmouseout="this.style.opacity='1'">
<span class="material-symbols-outlined" style="font-size:16px;">edit</span>EDIT SALE
</a>
<form method="POST" action="{{ route('sales.destroy', $sale) }}" class="inline-flex" onsubmit="return confirm('Delete this sale? Stock will be restored and the related receivable removed.');">
@csrf @method('DELETE')
<button type="submit" class="h-10 px-5 text-[11px] font-bold uppercase flex items-center gap-2 active:scale-[0.98] transition-all" style="color:#9F403D;border:1px solid #9F403D;border-radius:0;letter-spacing:0.05em;background:transparent;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:16px;">delete</span>DELETE
</button>
</form>
@endif
</div>

<div class="text-center text-[10px] uppercase font-bold pb-4" style="letter-spacing:0.05em;color:#5E5E5E;">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</div>
</div>
</div>
</main>
</body>
</html>
