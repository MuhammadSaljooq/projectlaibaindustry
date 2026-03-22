@php
    $user = $user ?? null;
    $roles = $roles ?? [];
@endphp

<div class="flex flex-col" style="gap:1.5rem;">
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="name">Name <span style="color:#9F403D;">*</span></label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold @error('name') arch-field-error @enderror" id="name" name="name" type="text" value="{{ old('name', $user?->name) }}" required autofocus maxlength="255">
@error('name')
<p class="mt-1.5 text-xs font-bold" style="color:#9F403D;">{{ $message }}</p>
@enderror
</div>
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="email">Email <span style="color:#9F403D;">*</span></label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold @error('email') arch-field-error @enderror" id="email" name="email" type="email" value="{{ old('email', $user?->email) }}" required maxlength="255">
@error('email')
<p class="mt-1.5 text-xs font-bold" style="color:#9F403D;">{{ $message }}</p>
@enderror
</div>
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="password">{{ $user ? 'New password' : 'Password' }} @if (!$user)<span style="color:#9F403D;">*</span>@endif</label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold @error('password') arch-field-error @enderror" id="password" name="password" type="password" {{ $user ? '' : 'required' }} minlength="8" autocomplete="{{ $user ? 'new-password' : 'off' }}" placeholder="{{ $user ? 'Leave blank to keep current' : '' }}">
@error('password')
<p class="mt-1.5 text-xs font-bold" style="color:#9F403D;">{{ $message }}</p>
@enderror
</div>
@if ($user)
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="password_confirmation">Confirm new password</label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold" id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password">
</div>
@else
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="password_confirmation">Confirm password <span style="color:#9F403D;">*</span></label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold" id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="off">
</div>
@endif
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="role">Role <span style="color:#9F403D;">*</span></label>
<select class="arch-select w-full h-11 pl-4 pr-10 text-sm font-bold appearance-none cursor-pointer @error('role') arch-field-error @enderror" id="role" name="role" required>
<option value="">Select role</option>
@foreach($roles as $value => $label)
<option value="{{ $value }}" {{ old('role', $user?->role) == $value ? 'selected' : '' }}>{{ $label }}</option>
@endforeach
</select>
@error('role')
<p class="mt-1.5 text-xs font-bold" style="color:#9F403D;">{{ $message }}</p>
@enderror
</div>
</div>
