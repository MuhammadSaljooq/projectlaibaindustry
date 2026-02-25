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
</div>
