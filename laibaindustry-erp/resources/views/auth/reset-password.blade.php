<!DOCTYPE html>

<html lang="en"><head>
@include('partials.frontend-head', ['title' => 'Reset Password'])
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-[#111418] dark:text-white">
<div class="flex min-h-screen w-full flex-row overflow-hidden">
<div class="hidden lg:flex w-1/2 relative bg-cover bg-center items-center justify-center auth-hero-bg">
<div class="absolute inset-0 bg-primary/20 backdrop-blur-[2px]"></div>
<div class="relative z-10 p-12 text-white max-w-lg">
<h2 class="text-4xl font-bold mb-6 drop-shadow-md">Set a new secure password.</h2>
<p class="text-lg font-medium opacity-90 drop-shadow-sm">Use at least 8 characters and keep your account protected.</p>
</div>
</div>

<div class="flex w-full lg:w-1/2 flex-col items-center justify-center p-6 sm:p-12 bg-white dark:bg-background-dark">
<div class="w-full max-w-[480px] flex flex-col gap-6">
<div class="flex flex-col gap-2 pb-4">
<div class="mb-4 flex items-center gap-2">
<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
<span class="material-symbols-outlined text-3xl">vpn_key</span>
</div>
<span class="text-xl font-bold tracking-tight text-[#111418] dark:text-white">Nexus ERP</span>
</div>
<h1 class="text-3xl font-bold leading-tight tracking-tight text-[#111418] dark:text-white">Reset Password</h1>
<p class="text-base font-normal text-slate-500 dark:text-slate-400">Enter your email and your new password.</p>
</div>

@if ($errors->any())
    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ $errors->first() }}
    </div>
@endif

<form action="{{ route('password.update', absolute: false) }}" class="flex flex-col gap-5" method="POST">
@csrf
<input type="hidden" name="token" value="{{ request()->route('token') }}">

<div class="flex flex-col gap-2">
<label class="text-base font-medium text-[#111418] dark:text-white" for="email">Email</label>
<div class="relative">
<input class="form-input w-full rounded-lg border border-slate-200 bg-background-light dark:bg-[#1a2632] dark:border-slate-700 px-4 py-3.5 text-base text-[#111418] dark:text-white placeholder-slate-400 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors" id="email" name="email" placeholder="name@company.com" type="email" value="{{ old('email') }}" required/>
<div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none flex items-center">
<span class="material-symbols-outlined">mail</span>
</div>
</div>
</div>

<div class="flex flex-col gap-2">
<label class="text-base font-medium text-[#111418] dark:text-white" for="password">New Password</label>
<div class="relative">
<input class="form-input w-full rounded-lg border border-slate-200 bg-background-light dark:bg-[#1a2632] dark:border-slate-700 px-4 py-3.5 pr-12 text-base text-[#111418] dark:text-white placeholder-slate-400 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors" id="password" name="password" placeholder="Enter new password" type="password" required/>
</div>
</div>

<div class="flex flex-col gap-2">
<label class="text-base font-medium text-[#111418] dark:text-white" for="password_confirmation">Confirm Password</label>
<div class="relative">
<input class="form-input w-full rounded-lg border border-slate-200 bg-background-light dark:bg-[#1a2632] dark:border-slate-700 px-4 py-3.5 pr-12 text-base text-[#111418] dark:text-white placeholder-slate-400 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors" id="password_confirmation" name="password_confirmation" placeholder="Confirm new password" type="password" required/>
</div>
</div>

<button class="flex w-full items-center justify-center rounded-lg bg-primary py-3.5 px-4 text-base font-bold text-white shadow-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 dark:focus:ring-offset-[#101922] transition-colors" type="submit">
                        Reset Password
                    </button>
<p class="text-center text-sm text-slate-600 dark:text-slate-400 mt-2">
<a class="font-bold text-primary hover:text-blue-600 transition-colors" href="{{ route('login', absolute: false) }}">Back to Login</a>
</p>
</form>
<div class="mt-8 flex justify-center text-xs text-slate-400 dark:text-slate-600">
<p>© 2024 Nexus ERP Systems. All rights reserved.</p>
</div>
</div>
</div>
</div>
</body></html>
