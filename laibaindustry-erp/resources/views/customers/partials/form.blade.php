@php $customer = $customer ?? null; @endphp

@if (session('error'))
<div style="border:1px solid #9F403D;padding:0.75rem 1.25rem;margin-bottom:1.25rem;" class="flex items-center gap-3">
<span class="material-symbols-outlined" style="color:#9F403D;font-size:20px;">error</span>
<span class="text-sm font-bold" style="color:#9F403D;">{{ session('error') }}</span>
</div>
@endif
@if ($errors->any())
<div style="border:1px solid #9F403D;padding:0.75rem 1.25rem;margin-bottom:1.25rem;" class="flex items-start gap-3">
<span class="material-symbols-outlined shrink-0 mt-0.5" style="color:#9F403D;font-size:20px;">error</span>
<ul class="text-sm font-bold space-y-0.5" style="color:#9F403D;">
@foreach ($errors->all() as $err)
<li>{{ $err }}</li>
@endforeach
</ul>
</div>
@endif

<div class="flex flex-col gap-5">
<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="customer_code">
Customer Code <span style="color:#9F403D;">*</span>
</label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold"
    id="customer_code"
    name="customer_code"
    type="text"
    value="{{ old('customer_code', $customer?->customer_code) }}"
    required
    maxlength="100"
    placeholder="e.g. CUST-001"
    autofocus>
@error('customer_code')
<p class="mt-1.5 text-xs font-bold" style="color:#9F403D;">{{ $message }}</p>
@enderror
</div>
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="customer_name">
Customer Name <span style="color:#9F403D;">*</span>
</label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold"
    id="customer_name"
    name="customer_name"
    type="text"
    value="{{ old('customer_name', $customer?->customer_name) }}"
    required
    maxlength="255"
    placeholder="Full name">
@error('customer_name')
<p class="mt-1.5 text-xs font-bold" style="color:#9F403D;">{{ $message }}</p>
@enderror
</div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="phone">Phone</label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold"
    id="phone"
    name="phone"
    type="text"
    value="{{ old('phone', $customer?->phone) }}"
    maxlength="50"
    placeholder="e.g. +92 300 1234567">
@error('phone')
<p class="mt-1.5 text-xs font-bold" style="color:#9F403D;">{{ $message }}</p>
@enderror
</div>
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="email">Email Address</label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold"
    id="email"
    name="email"
    type="email"
    value="{{ old('email', $customer?->email) }}"
    maxlength="255"
    placeholder="email@example.com">
@error('email')
<p class="mt-1.5 text-xs font-bold" style="color:#9F403D;">{{ $message }}</p>
@enderror
</div>
</div>

<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="address">Address</label>
<textarea class="arch-input w-full px-4 py-3 text-sm font-bold"
    id="address"
    name="address"
    rows="3"
    maxlength="1000"
    placeholder="Street, city, region">{{ old('address', $customer?->address) }}</textarea>
@error('address')
<p class="mt-1.5 text-xs font-bold" style="color:#9F403D;">{{ $message }}</p>
@enderror
</div>

<div class="pt-5 mt-1" style="border-top:1px solid #D3D8DE;">
<p class="text-[10px] font-bold uppercase mb-1" style="letter-spacing:0.05em;color:#2B3437;">Opening Balance</p>
<p class="text-xs font-bold mb-5" style="color:#5E5E5E;line-height:1.5;">Set a starting balance for this customer's ledger. Positive = they owe you; negative = you owe them.</p>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="opening_balance">Amount</label>
<div class="relative">
<span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold" style="color:#5E5E5E;">{{ $currencySymbol ?? '$' }}</span>
<input class="arch-input w-full h-11 pl-8 pr-4 text-sm font-bold text-right tabular-nums"
    id="opening_balance"
    name="opening_balance"
    type="number"
    step="0.01"
    value="{{ old('opening_balance', $customer?->opening_balance ?? 0) }}">
</div>
@error('opening_balance')
<p class="mt-1.5 text-xs font-bold" style="color:#9F403D;">{{ $message }}</p>
@enderror
</div>
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="opening_balance_date">As of Date</label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold"
    id="opening_balance_date"
    name="opening_balance_date"
    type="date"
    value="{{ old('opening_balance_date', $customer?->opening_balance_date?->format('Y-m-d')) }}">
@error('opening_balance_date')
<p class="mt-1.5 text-xs font-bold" style="color:#9F403D;">{{ $message }}</p>
@enderror
</div>
</div>
</div>
</div>
