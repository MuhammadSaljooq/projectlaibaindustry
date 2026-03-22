<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Record Payment - Laiba Safety'])
<style>
body { background-color: #FFFFFF; color: #2B3437; font-family: 'Inter', sans-serif; color-scheme: light; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #2B3437; color: #FFFFFF; }
input::placeholder { color: rgba(94, 94, 94, 0.55) !important; }
.arch-input {
  background-color: #FFFFFF !important;
  border: 1px solid #D3D8DE !important;
  border-radius: 0 !important;
  color: #2B3437 !important;
  color-scheme: light !important;
}
.arch-input:focus {
  border-color: #5E5E5E !important;
  border-width: 2px !important;
  box-shadow: none !important;
  outline: none !important;
  --tw-ring-shadow: none !important;
  --tw-ring-offset-shadow: none !important;
}
main input:focus {
  outline: none !important;
  --tw-ring-shadow: 0 0 #0000 !important;
  box-shadow: none !important;
}
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'payables'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#FFFFFF;">

<header class="h-14 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 rounded-none" style="color:#5E5E5E;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<a href="{{ route('payables.index') }}" class="flex items-center gap-2 text-sm font-bold transition-colors" style="color:#5E5E5E;" onmouseover="this.style.color='#2B3437'" onmouseout="this.style.color='#5E5E5E'">
<span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
Back to Payables
</a>
</div>
</header>

<div class="flex-1 overflow-y-auto no-scrollbar scroll-smooth">
<div class="max-w-[700px] mx-auto px-6 md:px-8 py-8 flex flex-col" style="gap:3rem;">

<div>
<div class="flex items-end justify-between" style="padding-bottom:0.75rem;border-bottom:2px solid #5E5E5E;">
<div>
<h1 class="font-bold" style="font-size:1.5rem;letter-spacing:-0.02em;color:#2B3437;">Record Payment</h1>
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-top:0.5rem;">Supplier payment</p>
</div>
<span class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Form AP-{{ $payable->id }}</span>
</div>
</div>

@if (session('error'))
<div style="border:1px solid #9F403D;padding:0.75rem 1.25rem;" class="text-sm font-bold flex items-center gap-3">
<span class="material-symbols-outlined" style="color:#9F403D;font-size:20px;">error</span>
<span style="color:#9F403D;">{{ session('error') }}</span>
</div>
@endif
@if ($errors->any())
<div style="border:1px solid #9F403D;padding:1rem 1.25rem;background:#FFFFFF;">
<p class="text-[10px] font-bold uppercase mb-2" style="color:#9F403D;letter-spacing:0.05em;">Please fix the following</p>
@foreach ($errors->all() as $err)
<p class="text-sm font-bold" style="color:#9F403D;margin-top:0.25rem;">{{ $err }}</p>
@endforeach
</div>
@endif

@php
$balance = max(0, (float)$payable->amount - (float)$payable->received);
$isPaid  = $balance <= 0;
@endphp

<div style="border:1px solid #D3D8DE;background:#FFFFFF;">
<div style="padding:1rem 1.5rem;border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Payable details</p>
</div>
<div style="padding:2rem;">

<div class="grid grid-cols-2 sm:grid-cols-4 gap-6 mb-6">
<div>
<p class="text-[10px] font-bold uppercase mb-1.5" style="letter-spacing:0.05em;color:#5E5E5E;">Date</p>
<p class="text-sm font-bold" style="color:#2B3437;">{{ $payable->date->format('Y-m-d') }}</p>
</div>
<div>
<p class="text-[10px] font-bold uppercase mb-1.5" style="letter-spacing:0.05em;color:#5E5E5E;">Invoice</p>
<p class="text-sm font-bold" style="color:#2B3437;">{{ $payable->invoice_number ?: '—' }}</p>
</div>
<div>
<p class="text-[10px] font-bold uppercase mb-1.5" style="letter-spacing:0.05em;color:#5E5E5E;">Supplier</p>
<p class="text-sm font-bold" style="color:#2B3437;">{{ $payable->customer_name ?: '—' }}</p>
</div>
<div>
<p class="text-[10px] font-bold uppercase mb-1.5" style="letter-spacing:0.05em;color:#5E5E5E;">Code</p>
<p class="text-sm font-bold" style="color:#2B3437;">{{ $payable->customer_code ?: '—' }}</p>
</div>
</div>

<div class="pt-5 mb-6" style="border-top:1px solid #D3D8DE;">
<div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-center">
<div>
<p class="text-[10px] font-bold uppercase mb-1.5" style="letter-spacing:0.05em;color:#5E5E5E;">Bill</p>
<p class="text-lg font-bold font-mono tabular-nums" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($payable->amount, 2) }}</p>
</div>
<div>
<p class="text-[10px] font-bold uppercase mb-1.5" style="letter-spacing:0.05em;color:#5E5E5E;">Already paid</p>
<p class="text-lg font-bold font-mono tabular-nums" style="color:#5E5E5E;">{{ $currencySymbol ?? '$' }} {{ number_format($payable->received, 2) }}</p>
</div>
<div>
<p class="text-[10px] font-bold uppercase mb-1.5" style="letter-spacing:0.05em;color:#5E5E5E;">Balance due</p>
<p class="text-lg font-bold font-mono tabular-nums" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} {{ number_format($balance, 2) }}</p>
</div>
</div>
</div>

@if(!$isPaid)
<div class="pt-5" style="border-top:1px solid #D3D8DE;">
<form method="POST" action="{{ route('payables.update', $payable) }}" novalidate>
@csrf
@method('PUT')

<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="payment">
Payment amount <span style="color:#9F403D;">*</span>
</label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold font-mono text-right"
    id="payment"
    name="payment"
    type="number"
    step="0.01"
    min="0.01"
    max="{{ $balance }}"
    value="{{ old('payment', number_format($balance, 2, '.', '')) }}"
    placeholder="{{ number_format($balance, 2) }}"
    required>
<p class="text-xs font-bold mt-2" style="color:#5E5E5E;">Maximum: {{ $currencySymbol ?? '$' }} {{ number_format($balance, 2) }}</p>

<div class="flex flex-wrap items-center gap-3 mt-8">
<button type="submit" class="h-11 px-6 text-[11px] font-bold uppercase inline-flex items-center gap-2 transition-all whitespace-nowrap" style="background:#5E5E5E;color:#F8F8F8;letter-spacing:0.05em;" onmouseover="this.style.opacity='0.92'" onmouseout="this.style.opacity='1'">
<span class="material-symbols-outlined" style="font-size:16px;">payments</span>
RECORD PAYMENT
</button>
<a href="{{ route('payables.index') }}" class="h-11 px-6 text-[11px] font-bold uppercase inline-flex items-center transition-all whitespace-nowrap" style="color:#2B3437;border:1px solid #5E5E5E;letter-spacing:0.05em;background:transparent;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">
CANCEL
</a>
</div>
</form>
</div>
@else
<div class="pt-5 text-center" style="border-top:1px solid #D3D8DE;">
<span class="material-symbols-outlined block mb-2 mx-auto" style="font-size:32px;color:#5E5E5E;">check_circle</span>
<p class="text-sm font-bold mb-4" style="color:#5E5E5E;">This payable has been fully paid.</p>
<a href="{{ route('payables.index') }}" class="inline-flex items-center gap-2 text-[11px] font-bold uppercase" style="color:#2B3437;border-bottom:2px solid #5E5E5E;padding-bottom:0.25rem;letter-spacing:0.05em;">
<span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
Back to Payables
</a>
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
