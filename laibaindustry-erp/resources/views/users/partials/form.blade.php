@php
    $user = $user ?? null;
    $roles = $roles ?? [];
@endphp

<div class="flex flex-col gap-5">
    <div>
        <label class="st-label block mb-2" for="name">Name <span class="text-[#9F403D]">*</span></label>
        <input class="st-input w-full h-10 px-3 text-sm @error('name') !border-[#9F403D] @enderror" id="name" name="name" type="text" value="{{ old('name', $user?->name) }}" required autofocus maxlength="255">
        @error('name')<p class="mt-1.5 text-xs text-[#9F403D] font-medium">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="st-label block mb-2" for="email">Email <span class="text-[#9F403D]">*</span></label>
        <input class="st-input w-full h-10 px-3 text-sm @error('email') !border-[#9F403D] @enderror" id="email" name="email" type="email" value="{{ old('email', $user?->email) }}" required maxlength="255">
        @error('email')<p class="mt-1.5 text-xs text-[#9F403D] font-medium">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="st-label block mb-2" for="password">{{ $user ? 'New password' : 'Password' }} @if (!$user)<span class="text-[#9F403D]">*</span>@endif</label>
        <input class="st-input w-full h-10 px-3 text-sm @error('password') !border-[#9F403D] @enderror" id="password" name="password" type="password" {{ $user ? '' : 'required' }} minlength="8" autocomplete="{{ $user ? 'new-password' : 'off' }}" placeholder="{{ $user ? 'Leave blank to keep current' : '' }}">
        @error('password')<p class="mt-1.5 text-xs text-[#9F403D] font-medium">{{ $message }}</p>@enderror
    </div>
    @if ($user)
    <div>
        <label class="st-label block mb-2" for="password_confirmation">Confirm new password</label>
        <input class="st-input w-full h-10 px-3 text-sm" id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password">
    </div>
    @else
    <div>
        <label class="st-label block mb-2" for="password_confirmation">Confirm password <span class="text-[#9F403D]">*</span></label>
        <input class="st-input w-full h-10 px-3 text-sm" id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="off">
    </div>
    @endif
    <div class="min-w-0">
        <label class="st-label block mb-2" for="role">Role <span class="text-[#9F403D]">*</span></label>
        <div class="relative isolate">
            <select class="st-select w-full min-w-0 h-10 pl-3 pr-12 text-sm appearance-none cursor-pointer @error('role') !border-[#9F403D] @enderror" id="role" name="role" required>
                <option value="">Select role</option>
                @foreach($roles as $value => $label)
                <option value="{{ $value }}" {{ old('role', $user?->role) == $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 material-symbols-outlined pointer-events-none text-[#586064] !text-[18px] leading-none w-6 flex items-center justify-center" aria-hidden="true">expand_more</span>
        </div>
        @error('role')<p class="mt-1.5 text-xs text-[#9F403D] font-medium">{{ $message }}</p>@enderror
    </div>
</div>
