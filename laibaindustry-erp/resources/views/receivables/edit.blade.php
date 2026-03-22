<!DOCTYPE html>
<html lang="en" class="dark">
<head>
@include('partials.frontend-head', ['title' => 'Record Payment - Laiba Safety'])
<style>
body { background-color: #131313; color: #e2e2e2; font-family: 'Inter', sans-serif; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #FFFFFF; color: #131313; }
input::placeholder { color: rgba(196,199,200,0.5) !important; }
.arch-input { background-color:#1B1B1B !important; border:1px solid rgba(68,71,72,0.4) !important; border-radius:0.375rem !important; color:#FFFFFF !important; color-scheme:dark; }
.arch-input:focus { border-color:#FFFFFF !important; box-shadow:0 0 0 2px rgba(255,255,255,0.1) !important; outline:none !important; --tw-ring-shadow:none !important; }
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'receivables'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#131313;">

<header class="h-16 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="background:#1B1B1B;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 hover:text-white rounded-md" style="color:#8e9192;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<a href="{{ route('receivables.index') }}" class="flex items-center gap-2 text-sm font-medium transition-colors duration-150" style="color:#8e9192;" onmouseenter="this.style.color='#FFFFFF'" onmouseleave="this.style.color='#8e9192'">
<span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
Back to Receivables
</a>
</div>
</header>

<div class="flex-1 overflow-y-auto no-scrollbar">
<div class="max-w-[700px] mx-auto px-6 md:px-8 py-8 flex flex-col gap-8">

<div>
<p class="text-xs font-medium uppercase tracking-[0.15em] mb-2" style="color:#8e9192;">Payment Collection</p>
<h1 class="text-4xl font-bold tracking-tight" style="color:#FFFFFF;letter-spacing:-0.02em;">Record Payment</h1>
</div>

@if (session('error'))
<div class="flex items-center gap-3 px-5 py-3.5 rounded-md" style="background:rgba(255,180,171,0.06);">
<span class="material-symbols-outlined" style="color:#FFB4AB;font-size:20px;">error</span>
<span class="text-sm font-medium" style="color:#FFB4AB;">{{ session('error') }}</span>
</div>
@endif

@php $remaining = (float)$receivable->amount - (float)$receivable->received; @endphp

<div class="rounded-lg p-6 md:p-8" style="background:#1B1B1B;">
<p class="text-xs font-semibold uppercase tracking-[0.15em] mb-6" style="color:#8e9192;">Invoice Details</p>

<div class="grid grid-cols-2 sm:grid-cols-3 gap-6 mb-6">
<div>
<p class="text-[10px] font-semibold uppercase tracking-[0.15em] mb-1.5" style="color:#8e9192;">Date</p>
<p class="text-sm font-medium" style="color:#FFFFFF;">{{ $receivable->date->format('Y-m-d') }}</p>
</div>
<div>
<p class="text-[10px] font-semibold uppercase tracking-[0.15em] mb-1.5" style="color:#8e9192;">Invoice</p>
<p class="text-sm font-medium" style="color:#FFFFFF;">{{ $receivable->invoice_number ?: '—' }}</p>
</div>
<div>
<p class="text-[10px] font-semibold uppercase tracking-[0.15em] mb-1.5" style="color:#8e9192;">Customer</p>
<p class="text-sm font-medium" style="color:#FFFFFF;">{{ $receivable->customer_name ?: $receivable->customer_code ?: '—' }}</p>
</div>
</div>

<div class="pt-5 mb-6" style="border-top:1px solid rgba(68,71,72,0.2);">
<div class="grid grid-cols-3 gap-6">
<div>
<p class="text-[10px] font-semibold uppercase tracking-[0.15em] mb-1.5" style="color:#8e9192;">Total Amount</p>
<p class="text-lg font-bold font-mono tabular-nums" style="color:#FFFFFF;">{{ $currencySymbol ?? '$' }} {{ number_format($receivable->amount, 2) }}</p>
</div>
<div>
<p class="text-[10px] font-semibold uppercase tracking-[0.15em] mb-1.5" style="color:#8e9192;">Already Received</p>
<p class="text-lg font-bold font-mono tabular-nums" style="color:#8e9192;">{{ $currencySymbol ?? '$' }} {{ number_format($receivable->received, 2) }}</p>
</div>
<div>
<p class="text-[10px] font-semibold uppercase tracking-[0.15em] mb-1.5" style="color:#8e9192;">Remaining</p>
<p class="text-lg font-bold font-mono tabular-nums" style="color:#FFFFFF;">{{ $currencySymbol ?? '$' }} {{ number_format($remaining, 2) }}</p>
</div>
</div>
</div>

@if ($remaining > 0)
<div class="pt-5" style="border-top:1px solid rgba(68,71,72,0.2);">
<form method="POST" action="{{ route('receivables.update', $receivable) }}">
@csrf
@method('PUT')

<label class="block text-[10px] font-semibold uppercase tracking-[0.15em] mb-2" style="color:#C4C7C8;" for="received">
Payment Amount <span style="color:#FFB4AB;">*</span>
</label>
<input class="arch-input w-full h-11 px-4 text-sm font-medium font-mono text-right"
    id="received"
    name="received"
    type="number"
    step="0.01"
    min="0.01"
    max="{{ $remaining }}"
    value="{{ old('received', $remaining) }}"
    placeholder="Max: {{ number_format($remaining, 2) }}"
    required>
<p class="text-xs mt-2" style="color:#8e9192;">Enter the amount received. Max: {{ $currencySymbol ?? '$' }} {{ number_format($remaining, 2) }}</p>

<div class="flex flex-wrap items-center gap-3 mt-8">
<button type="submit" class="h-11 px-6 inline-flex items-center gap-2 text-sm font-bold rounded-md transition-all duration-200 whitespace-nowrap" style="background:#FFFFFF;color:#2F3131;" onmouseenter="this.style.background='#C6C6C7'" onmouseleave="this.style.background='#FFFFFF'">
<span class="material-symbols-outlined" style="font-size:18px;color:#2F3131;">payments</span>
RECORD PAYMENT
</button>
<a href="{{ route('receivables.index') }}" class="h-11 px-6 inline-flex items-center text-sm font-medium rounded-md transition-all duration-200 whitespace-nowrap" style="color:#C4C7C8;border:1px solid rgba(68,71,72,0.4);" onmouseenter="this.style.borderColor='#8e9192';this.style.color='#FFFFFF'" onmouseleave="this.style.borderColor='rgba(68,71,72,0.4)';this.style.color='#C4C7C8'">
CANCEL
</a>
</div>
</form>
</div>
@else
<div class="pt-5 text-center" style="border-top:1px solid rgba(68,71,72,0.2);">
<span class="material-symbols-outlined block mb-2 mx-auto" style="font-size:32px;color:#C4C7C8;">check_circle</span>
<p class="text-sm font-medium mb-4" style="color:#C4C7C8;">This receivable has been fully paid.</p>
<a href="{{ route('receivables.index') }}" class="inline-flex items-center gap-2 text-sm font-medium transition-colors duration-150" style="color:#8e9192;" onmouseenter="this.style.color='#FFFFFF'" onmouseleave="this.style.color='#8e9192'">
<span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
Back to Receivables
</a>
</div>
@endif
</div>

<footer class="pt-4 pb-8 text-center">
<p class="text-xs" style="color:rgba(142,145,146,0.4);">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</p>
</footer>

</div>
</div>

</main>
</body>
</html>
