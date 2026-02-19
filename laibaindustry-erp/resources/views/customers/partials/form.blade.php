@php
    $customer = $customer ?? null;
@endphp

<div class="flex flex-col gap-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="customer_name">Name <span class="text-red-500">*</span></label>
        <input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary @error('customer_name') border-red-500 dark:border-red-500 @enderror" id="customer_name" name="customer_name" type="text" value="{{ old('customer_name', $customer?->customer_name) }}" required autofocus maxlength="255">
        @error('customer_name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="phone">Contact</label>
        <input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary @error('phone') border-red-500 dark:border-red-500 @enderror" id="phone" name="phone" type="text" value="{{ old('phone', $customer?->phone) }}" maxlength="50">
        @error('phone')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="email">Email address</label>
        <input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary @error('email') border-red-500 dark:border-red-500 @enderror" id="email" name="email" type="email" value="{{ old('email', $customer?->email) }}" maxlength="255">
        @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
