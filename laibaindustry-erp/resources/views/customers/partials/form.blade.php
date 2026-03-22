@php $customer = $customer ?? null; @endphp

@if (session('error'))
<div class="flex items-center gap-3 px-5 py-3.5 rounded-md mb-5" style="background:rgba(255,180,171,0.06);">
<span class="material-symbols-outlined" style="color:#FFB4AB;font-size:20px;">error</span>
<span class="text-sm font-medium" style="color:#FFB4AB;">{{ session('error') }}</span>
</div>
@endif
@if ($errors->any())
<div class="flex items-start gap-3 px-5 py-3.5 rounded-md mb-5" style="background:rgba(255,180,171,0.06);">
<span class="material-symbols-outlined shrink-0 mt-0.5" style="color:#FFB4AB;font-size:20px;">error</span>
<ul class="text-sm font-medium space-y-0.5" style="color:#FFB4AB;">
@foreach ($errors->all() as $err)
<li>{{ $err }}</li>
@endforeach
</ul>
</div>
@endif

<div class="flex flex-col gap-5">
<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
<div>
<label class="block text-[10px] font-semibold uppercase tracking-[0.15em] mb-2" style="color:#C4C7C8;" for="customer_code">
Customer Code <span style="color:#FFB4AB;">*</span>
</label>
<input class="arch-input w-full h-11 px-4 text-sm font-medium"
    id="customer_code"
    name="customer_code"
    type="text"
    value="{{ old('customer_code', $customer?->customer_code) }}"
    required
    maxlength="100"
    placeholder="e.g. CUST-001"
    autofocus>
@error('customer_code')
<p class="mt-1.5 text-xs font-medium" style="color:#FFB4AB;">{{ $message }}</p>
@enderror
</div>
<div>
<label class="block text-[10px] font-semibold uppercase tracking-[0.15em] mb-2" style="color:#C4C7C8;" for="customer_name">
Customer Name <span style="color:#FFB4AB;">*</span>
</label>
<input class="arch-input w-full h-11 px-4 text-sm font-medium"
    id="customer_name"
    name="customer_name"
    type="text"
    value="{{ old('customer_name', $customer?->customer_name) }}"
    required
    maxlength="255"
    placeholder="Full name">
@error('customer_name')
<p class="mt-1.5 text-xs font-medium" style="color:#FFB4AB;">{{ $message }}</p>
@enderror
</div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
<div>
<label class="block text-[10px] font-semibold uppercase tracking-[0.15em] mb-2" style="color:#C4C7C8;" for="phone">Phone</label>
<input class="arch-input w-full h-11 px-4 text-sm font-medium"
    id="phone"
    name="phone"
    type="text"
    value="{{ old('phone', $customer?->phone) }}"
    maxlength="50"
    placeholder="e.g. +92 300 1234567">
@error('phone')
<p class="mt-1.5 text-xs font-medium" style="color:#FFB4AB;">{{ $message }}</p>
@enderror
</div>
<div>
<label class="block text-[10px] font-semibold uppercase tracking-[0.15em] mb-2" style="color:#C4C7C8;" for="email">Email Address</label>
<input class="arch-input w-full h-11 px-4 text-sm font-medium"
    id="email"
    name="email"
    type="email"
    value="{{ old('email', $customer?->email) }}"
    maxlength="255"
    placeholder="email@example.com">
@error('email')
<p class="mt-1.5 text-xs font-medium" style="color:#FFB4AB;">{{ $message }}</p>
@enderror
</div>
</div>

<div>
<label class="block text-[10px] font-semibold uppercase tracking-[0.15em] mb-2" style="color:#C4C7C8;" for="address">Address</label>
<textarea class="arch-input w-full px-4 py-3 text-sm font-medium"
    id="address"
    name="address"
    rows="3"
    maxlength="1000"
    placeholder="Street, city, region">{{ old('address', $customer?->address) }}</textarea>
@error('address')
<p class="mt-1.5 text-xs font-medium" style="color:#FFB4AB;">{{ $message }}</p>
@enderror
</div>

<div class="pt-5 mt-1" style="border-top:1px solid rgba(68,71,72,0.2);">
<p class="text-xs font-semibold uppercase tracking-[0.15em] mb-1" style="color:#FFFFFF;">Opening Balance</p>
<p class="text-xs mb-5" style="color:#8e9192;">Set a starting balance for this customer's ledger. Positive = they owe you; negative = you owe them.</p>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
<div>
<label class="block text-[10px] font-semibold uppercase tracking-[0.15em] mb-2" style="color:#C4C7C8;" for="opening_balance">Amount</label>
<div class="relative">
<span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-medium" style="color:#8e9192;">{{ $currencySymbol ?? '$' }}</span>
<input class="arch-input w-full h-11 pl-8 pr-4 text-sm font-medium text-right font-mono"
    id="opening_balance"
    name="opening_balance"
    type="number"
    step="0.01"
    value="{{ old('opening_balance', $customer?->opening_balance ?? 0) }}">
</div>
@error('opening_balance')
<p class="mt-1.5 text-xs font-medium" style="color:#FFB4AB;">{{ $message }}</p>
@enderror
</div>
<div>
<label class="block text-[10px] font-semibold uppercase tracking-[0.15em] mb-2" style="color:#C4C7C8;" for="opening_balance_date">As of Date</label>
<input class="arch-input w-full h-11 px-4 text-sm font-medium"
    id="opening_balance_date"
    name="opening_balance_date"
    type="date"
    value="{{ old('opening_balance_date', $customer?->opening_balance_date?->format('Y-m-d')) }}">
@error('opening_balance_date')
<p class="mt-1.5 text-xs font-medium" style="color:#FFB4AB;">{{ $message }}</p>
@enderror
</div>
</div>
</div>
</div>
