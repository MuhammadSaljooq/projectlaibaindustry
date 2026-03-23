@php $customer = $customer ?? null; @endphp

@if (session('error'))
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D] mb-5">
{{ session('error') }}
</div>
@endif
@if ($errors->any())
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D] mb-5">
<ul class="list-disc list-inside space-y-0.5">
@foreach ($errors->all() as $err)
<li>{{ $err }}</li>
@endforeach
</ul>
</div>
@endif

<div class="flex flex-col gap-5">
<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
<div>
<label class="st-label block mb-2" for="customer_code">
Customer Code <span class="text-[#9F403D]">*</span>
</label>
<input class="st-input w-full h-11 px-4 text-sm"
    id="customer_code"
    name="customer_code"
    type="text"
    value="{{ old('customer_code', $customer?->customer_code) }}"
    required
    maxlength="100"
    placeholder="e.g. CUST-001"
    @if(!$customer) autofocus @endif>
@error('customer_code')
<p class="mt-1.5 text-xs font-medium text-[#9F403D]">{{ $message }}</p>
@enderror
</div>
<div>
<label class="st-label block mb-2" for="customer_name">
Customer Name <span class="text-[#9F403D]">*</span>
</label>
<input class="st-input w-full h-11 px-4 text-sm"
    id="customer_name"
    name="customer_name"
    type="text"
    value="{{ old('customer_name', $customer?->customer_name) }}"
    required
    maxlength="255"
    placeholder="Full name">
@error('customer_name')
<p class="mt-1.5 text-xs font-medium text-[#9F403D]">{{ $message }}</p>
@enderror
</div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
<div>
<label class="st-label block mb-2" for="phone">Phone</label>
<input class="st-input w-full h-11 px-4 text-sm"
    id="phone"
    name="phone"
    type="text"
    value="{{ old('phone', $customer?->phone) }}"
    maxlength="50"
    placeholder="e.g. +92 300 1234567">
@error('phone')
<p class="mt-1.5 text-xs font-medium text-[#9F403D]">{{ $message }}</p>
@enderror
</div>
<div>
<label class="st-label block mb-2" for="email">Email Address</label>
<input class="st-input w-full h-11 px-4 text-sm"
    id="email"
    name="email"
    type="email"
    value="{{ old('email', $customer?->email) }}"
    maxlength="255"
    placeholder="email@example.com">
@error('email')
<p class="mt-1.5 text-xs font-medium text-[#9F403D]">{{ $message }}</p>
@enderror
</div>
</div>

<div>
<label class="st-label block mb-2" for="address">Address</label>
<textarea class="st-input w-full px-4 py-3 text-sm min-h-[5.5rem]"
    id="address"
    name="address"
    rows="3"
    maxlength="1000"
    placeholder="Street, city, region">{{ old('address', $customer?->address) }}</textarea>
@error('address')
<p class="mt-1.5 text-xs font-medium text-[#9F403D]">{{ $message }}</p>
@enderror
</div>

<div class="pt-5 mt-1 border-t border-[#ABB3B7]">
<p class="st-label mb-1">Opening balance</p>
<p class="text-xs mb-5 text-[#586064]">Starting ledger balance. Positive = they owe you; negative = you owe them.</p>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
<div>
<label class="st-label block mb-2" for="opening_balance">Amount</label>
<div class="relative">
<span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-medium text-[#586064]">{{ $currencySymbol ?? '$' }}</span>
<input class="st-input w-full h-11 pl-8 pr-4 text-sm text-right font-mono tabular-nums"
    id="opening_balance"
    name="opening_balance"
    type="number"
    step="0.01"
    value="{{ old('opening_balance', $customer?->opening_balance ?? 0) }}">
</div>
@error('opening_balance')
<p class="mt-1.5 text-xs font-medium text-[#9F403D]">{{ $message }}</p>
@enderror
</div>
<div>
<label class="st-label block mb-2" for="opening_balance_date">As of Date</label>
<input class="st-input w-full h-11 px-4 text-sm"
    id="opening_balance_date"
    name="opening_balance_date"
    type="date"
    value="{{ old('opening_balance_date', $customer?->opening_balance_date?->format('Y-m-d')) }}">
@error('opening_balance_date')
<p class="mt-1.5 text-xs font-medium text-[#9F403D]">{{ $message }}</p>
@enderror
</div>
</div>
</div>
</div>
