<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Purchase #' . ($purchase->invoice_number ?: $purchase->id) . ' - Laiba Safety'])
<style>
body { background-color: #FFFFFF; color: #2B3437; font-family: 'Inter', sans-serif; color-scheme: light; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #2B3437; color: #FFFFFF; }
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'purchases'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#FFFFFF;">

<header class="h-14 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 rounded-none" style="color:#5E5E5E;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu"><span class="material-symbols-outlined">menu</span></button>
<a href="{{ route('purchases.index') }}" class="flex items-center gap-2 transition-colors font-bold text-sm" style="color:#5E5E5E;" onmouseover="this.style.color='#2B3437'" onmouseout="this.style.color='#5E5E5E'">
<span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
<span class="hidden sm:inline">Back to Purchases</span>
</a>
</div>
<div class="flex items-center gap-3">
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('purchases.edit', $purchase) }}" class="h-9 px-5 text-[11px] font-bold uppercase flex items-center gap-2 active:scale-[0.98] transition-all" style="background:#5E5E5E;color:#F8F8F8;border-radius:0;letter-spacing:0.05em;" onmouseover="this.style.opacity='0.92'" onmouseout="this.style.opacity='1'">
<span class="material-symbols-outlined" style="font-size:14px;">edit</span>EDIT
</a>
@endif
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[900px] mx-auto flex flex-col" style="gap:3rem;">

@php $symbol = $purchase->currency && $purchase->currency->symbol ? $purchase->currency->symbol : ($currencySymbol ?? '$'); @endphp

@if (session('success'))
<div style="border:1px solid #D3D8DE;padding:0.75rem 1.25rem;background:#F8F9FA;color:#2B3437;" class="text-sm font-bold">{{ session('success') }}</div>
@endif
@if (session('error'))
<div style="border:1px solid #9F403D;padding:0.75rem 1.25rem;background:#FFFFFF;" class="text-sm font-bold"><span style="color:#9F403D;">{{ session('error') }}</span></div>
@endif

<div>
<div class="flex flex-wrap items-end justify-between gap-4" style="padding-bottom:0.75rem;border-bottom:2px solid #5E5E5E;">
<div class="min-w-0 flex-1">
<h2 class="font-bold truncate" style="font-size:1.5rem;letter-spacing:-0.02em;color:#2B3437;">{{ $purchase->invoice_number ?: 'Purchase #' . $purchase->id }}</h2>
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-top:0.5rem;">Purchase record</p>
<p class="text-sm font-bold" style="color:#5E5E5E;margin-top:0.35rem;">{{ $purchase->date->format('l, F j, Y \a\t g:i A') }}</p>
</div>
</div>
<span class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;display:block;margin-top:0.75rem;text-align:right;">Doc PUR-{{ $purchase->id }}</span>
</div>

<div style="border:1px solid #D3D8DE;background:#FFFFFF;">
<div style="padding:1rem 1.5rem;border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Supplier information</p>
</div>
<div style="padding:2rem;">
<div class="grid grid-cols-1 sm:grid-cols-2" style="gap:1.5rem;">
<div>
<p class="text-[10px] font-bold uppercase mb-1" style="letter-spacing:0.05em;color:#5E5E5E;">Customer code</p>
<p class="text-sm font-bold" style="color:#2B3437;">{{ $purchase->customer_code ?: '—' }}</p>
</div>
<div>
<p class="text-[10px] font-bold uppercase mb-1" style="letter-spacing:0.05em;color:#5E5E5E;">Customer name</p>
<p class="text-sm font-bold" style="color:#2B3437;">{{ $purchase->customer_name ?: '—' }}</p>
</div>
@if($purchase->invoice_number)
<div>
<p class="text-[10px] font-bold uppercase mb-1" style="letter-spacing:0.05em;color:#5E5E5E;">Invoice</p>
<p class="text-sm font-bold tabular-nums" style="color:#2B3437;">{{ $purchase->invoice_number }}</p>
</div>
@endif
<div>
<p class="text-[10px] font-bold uppercase mb-1" style="letter-spacing:0.05em;color:#5E5E5E;">Date</p>
<p class="text-sm font-bold" style="color:#2B3437;">{{ $purchase->date->format('Y-m-d H:i') }}</p>
</div>
</div>
</div>
</div>

<div style="border:1px solid #D3D8DE;">
<div style="padding:1rem 1.5rem;border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Line items</p>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse" style="min-width:600px;">
<thead>
<tr style="background:#F8F9FA;border-bottom:1px solid #D3D8DE;">
<th class="px-6 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;width:40px;">#</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Product</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;">Qty</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;">Unit price</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;">VAT 15%</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;">Line total</th>
</tr>
</thead>
<tbody>
@foreach($purchase->items as $index => $item)
<tr class="transition-colors" style="border-top:1px solid #EAECEE;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">
<td class="px-6 py-4 text-sm tabular-nums font-bold" style="color:#5E5E5E;">{{ $index + 1 }}</td>
<td class="px-6 py-4">
<p class="text-sm font-bold" style="color:#2B3437;">{{ $item->product_name }}</p>
</td>
<td class="px-6 py-4 text-sm tabular-nums text-right font-bold" style="color:#5E5E5E;">{{ number_format($item->quantity) }}</td>
<td class="px-6 py-4 text-sm tabular-nums text-right font-bold whitespace-nowrap" style="color:#2B3437;">{{ $symbol }} {{ number_format($item->price, 2) }}</td>
<td class="px-6 py-4 text-sm tabular-nums text-right whitespace-nowrap font-bold" style="color:#5E5E5E;">{{ $symbol }} {{ number_format($item->vat_amount, 2) }}</td>
<td class="px-6 py-4 text-sm font-bold tabular-nums text-right whitespace-nowrap" style="color:#2B3437;">{{ $symbol }} {{ number_format($item->subtotal, 2) }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>

<div style="padding:1.5rem 2rem;border-top:1px solid #D3D8DE;background:#F8F9FA;">
<div class="flex justify-end">
<div style="min-width:240px;" class="flex flex-col gap-2">
<div class="flex justify-between text-sm font-bold"><span style="color:#5E5E5E;">Subtotal (excl. VAT)</span><span class="tabular-nums" style="color:#2B3437;">{{ $symbol }} {{ number_format($purchase->subtotal, 2) }}</span></div>
<div class="flex justify-between text-sm font-bold"><span style="color:#5E5E5E;">VAT (15%)</span><span class="tabular-nums" style="color:#2B3437;">{{ $symbol }} {{ number_format($purchase->vat_amount, 2) }}</span></div>
<div class="flex justify-between text-base font-bold" style="padding-top:0.75rem;border-top:1px solid #D3D8DE;"><span style="color:#2B3437;">Total</span><span class="tabular-nums" style="color:#2B3437;">{{ $symbol }} {{ number_format($purchase->total_amount, 2) }}</span></div>
</div>
</div>
</div>
</div>

<div class="flex flex-wrap items-center gap-3">
<a href="{{ route('purchases.index') }}" class="h-10 px-5 text-[11px] font-bold uppercase flex items-center gap-2 transition-all" style="color:#2B3437;border:1px solid #5E5E5E;border-radius:0;letter-spacing:0.05em;background:transparent;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:16px;">list</span>ALL PURCHASES
</a>
@if(auth()->user()->role !== 'viewer')
<a href="{{ route('purchases.edit', $purchase) }}" class="h-10 px-5 text-[11px] font-bold uppercase flex items-center gap-2 active:scale-[0.98] transition-all" style="background:#5E5E5E;color:#F8F8F8;border-radius:0;letter-spacing:0.05em;" onmouseover="this.style.opacity='0.92'" onmouseout="this.style.opacity='1'">
<span class="material-symbols-outlined" style="font-size:16px;">edit</span>EDIT PURCHASE
</a>
<form method="POST" action="{{ route('purchases.destroy', $purchase) }}" class="inline-flex" onsubmit="return confirm('Delete this purchase? This cannot be undone.');">
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
