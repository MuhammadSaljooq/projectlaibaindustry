@php $customer = $customer ?? null; @endphp

@if (session('error'))
<div class="rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-400 mb-4">
{{ session('error') }}
</div>
@endif
@if ($errors->any())
<div class="rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-400 mb-4">
<ul class="list-disc list-inside space-y-0.5">
@foreach ($errors->all() as $err)
<li>{{ $err }}</li>
@endforeach
</ul>
</div>
@endif

<div class="flex flex-col gap-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="customer_code">
                Customer Code <span class="text-red-500">*</span>
            </label>
            <input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary @error('customer_code') border-red-500 dark:border-red-500 @enderror"
                id="customer_code"
                name="customer_code"
                type="text"
                value="{{ old('customer_code', $customer?->customer_code) }}"
                required
                maxlength="100"
                placeholder="e.g. CUST-001"
                autofocus>
            @error('customer_code')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="customer_name">
                Customer Name <span class="text-red-500">*</span>
            </label>
            <input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary @error('customer_name') border-red-500 dark:border-red-500 @enderror"
                id="customer_name"
                name="customer_name"
                type="text"
                value="{{ old('customer_name', $customer?->customer_name) }}"
                required
                maxlength="255">
            @error('customer_name')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="phone">Phone</label>
            <input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary @error('phone') border-red-500 dark:border-red-500 @enderror"
                id="phone"
                name="phone"
                type="text"
                value="{{ old('phone', $customer?->phone) }}"
                maxlength="50">
            @error('phone')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="email">Email Address</label>
            <input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary @error('email') border-red-500 dark:border-red-500 @enderror"
                id="email"
                name="email"
                type="email"
                value="{{ old('email', $customer?->email) }}"
                maxlength="255">
            @error('email')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="address">Address</label>
        <textarea class="w-full rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary @error('address') border-red-500 dark:border-red-500 @enderror"
            id="address"
            name="address"
            rows="3"
            maxlength="1000">{{ old('address', $customer?->address) }}</textarea>
        @error('address')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Opening balance section --}}
    <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Opening Balance</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Set a starting balance for this customer's ledger. A positive value means they owe you money; negative means you owe them.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="opening_balance">
                    Opening Balance
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">{{ $currencySymbol ?? '$' }}</span>
                    <input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 pl-7 pr-4 py-2.5 text-right text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary font-mono @error('opening_balance') border-red-500 dark:border-red-500 @enderror"
                        id="opening_balance"
                        name="opening_balance"
                        type="number"
                        step="0.01"
                        value="{{ old('opening_balance', $customer?->opening_balance ?? 0) }}">
                </div>
                @error('opening_balance')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="opening_balance_date">
                    As of Date
                </label>
                <input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary @error('opening_balance_date') border-red-500 dark:border-red-500 @enderror"
                    id="opening_balance_date"
                    name="opening_balance_date"
                    type="date"
                    value="{{ old('opening_balance_date', $customer?->opening_balance_date?->format('Y-m-d')) }}">
                @error('opening_balance_date')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>
