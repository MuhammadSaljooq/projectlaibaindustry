@php
    $user = $user ?? null;
    $roles = $roles ?? [];
@endphp

<div class="flex flex-col gap-4">
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="name">Name <span class="text-red-500">*</span></label>
        <input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary @error('name') border-red-500 @enderror" id="name" name="name" type="text" value="{{ old('name', $user?->name) }}" required autofocus maxlength="255">
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="email">Email <span class="text-red-500">*</span></label>
        <input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary @error('email') border-red-500 @enderror" id="email" name="email" type="email" value="{{ old('email', $user?->email) }}" required maxlength="255">
        @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="password">{{ $user ? 'New password' : 'Password' }} @if (!$user)<span class="text-red-500">*</span>@endif</label>
        <input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary @error('password') border-red-500 @enderror" id="password" name="password" type="password" {{ $user ? '' : 'required' }} minlength="8" autocomplete="{{ $user ? 'new-password' : 'off' }}" placeholder="{{ $user ? 'Leave blank to keep current' : '' }}">
        @error('password')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    @if ($user)
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="password_confirmation">Confirm new password</label>
        <input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password">
    </div>
    @else
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="password_confirmation">Confirm password <span class="text-red-500">*</span></label>
        <input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="off">
    </div>
    @endif
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="role">Role <span class="text-red-500">*</span></label>
        <select class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary @error('role') border-red-500 @enderror" id="role" name="role" required>
            <option value="">Select role</option>
            @foreach($roles as $value => $label)
            <option value="{{ $value }}" {{ old('role', $user?->role) == $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('role')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
