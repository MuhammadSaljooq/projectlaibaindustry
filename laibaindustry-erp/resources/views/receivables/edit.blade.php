<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Record Payment - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'receivables'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-black border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<h2 class="text-xl font-bold text-slate-800 dark:text-white hidden sm:block">Record Payment</h2>
</div>
</header>
<div class="flex-1 overflow-y-auto p-6 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-6">
<div class="sm:hidden">
<h2 class="text-2xl font-bold text-slate-800 dark:text-white">Record Payment</h2>
</div>

@if (session('error'))
<div class="rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-400">
{{ session('error') }}
</div>
@endif

@php $remaining = (float)$receivable->amount - (float)$receivable->received; @endphp
<div class="max-w-lg">
<div class="bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
<h3 class="text-base font-semibold text-slate-800 dark:text-white mb-4">Receivable Details</h3>
<dl class="space-y-2 text-sm mb-6">
<dt class="text-slate-500 dark:text-slate-400">Date</dt>
<dd class="text-slate-900 dark:text-white font-medium">{{ $receivable->date->format('Y-m-d') }}</dd>
<dt class="text-slate-500 dark:text-slate-400">Invoice</dt>
<dd class="text-slate-900 dark:text-white font-medium">{{ $receivable->invoice_number ?: '-' }}</dd>
<dt class="text-slate-500 dark:text-slate-400">Customer</dt>
<dd class="text-slate-900 dark:text-white font-medium">{{ $receivable->customer_name ?: $receivable->customer_code ?: '-' }}</dd>
<dt class="text-slate-500 dark:text-slate-400">Total amount</dt>
<dd class="text-slate-900 dark:text-white font-mono font-bold tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($receivable->amount, 2) }}</dd>
<dt class="text-slate-500 dark:text-slate-400">Already received</dt>
<dd class="text-slate-900 dark:text-white font-mono tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($receivable->received, 2) }}</dd>
<dt class="text-slate-500 dark:text-slate-400">Remaining</dt>
<dd class="text-black dark:text-white font-mono font-bold text-lg tabular-nums">{{ $currencySymbol ?? '$' }} {{ number_format($remaining, 2) }}</dd>
</dl>

@if ($remaining > 0)
<form method="POST" action="{{ route('receivables.update', $receivable) }}">
@csrf
@method('PUT')
<div class="space-y-4">
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="received">Payment amount <span class="text-red-500">*</span></label>
<input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" id="received" name="received" type="number" step="0.01" min="0.01" max="{{ $remaining }}" value="{{ old('received', $remaining) }}" placeholder="Max: {{ number_format($remaining, 2) }}" required>
</div>
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="payment_date">Payment date</label>
<input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" id="payment_date" name="payment_date" type="date" value="{{ old('payment_date', now()->format('Y-m-d')) }}">
</div>
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="payment_type">Payment type</label>
<select class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" id="payment_type" name="payment_type">
<option value="">—</option>
<option value="Cash" {{ old('payment_type') === 'Cash' ? 'selected' : '' }}>Cash</option>
<option value="Bank Transfer" {{ old('payment_type') === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
<option value="Card" {{ old('payment_type') === 'Card' ? 'selected' : '' }}>Card</option>
<option value="Cheque" {{ old('payment_type') === 'Cheque' ? 'selected' : '' }}>Cheque</option>
<option value="Other" {{ old('payment_type') === 'Other' ? 'selected' : '' }}>Other</option>
</select>
</div>
</div>
<div class="flex flex-wrap gap-3 mt-6">
<button type="submit" class="h-10 px-5 bg-black hover:bg-gray-800 text-white text-sm font-bold rounded-lg transition-colors whitespace-nowrap">Record Payment</button>
<a href="{{ route('receivables.index') }}" class="h-10 px-5 inline-flex items-center text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors whitespace-nowrap">Cancel</a>
</div>
</form>
@else
<p class="text-gray-600 dark:text-gray-400 font-medium">This receivable has been fully paid.</p>
<a href="{{ route('receivables.index') }}" class="inline-flex items-center mt-4 text-sm font-medium text-black dark:text-white hover:underline">← Back to receivables</a>
@endif
</div>
</div>
<div class="mt-8 text-center text-xs text-slate-400 pb-4">© {{ date('Y') }} Nexus ERP Inc. All rights reserved.</div>
</div>
</main>
</body>
</html>
