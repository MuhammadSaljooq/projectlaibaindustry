<!DOCTYPE html>

<html lang="en"><head>
@include('partials.frontend-head', ['title' => 'ERP Login'])
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-[#111418] dark:text-white">
<div class="flex min-h-screen w-full flex-row overflow-hidden">
<div class="hidden lg:flex w-1/2 relative bg-cover bg-center items-center justify-center auth-hero-bg" data-alt="Modern corporate office interior with glass walls and clean lighting">
<div class="absolute inset-0 bg-primary/20 backdrop-blur-[2px]"></div>
<div class="relative z-10 p-12 text-white max-w-lg">
<h2 class="text-4xl font-bold mb-6 drop-shadow-md">Manage your business with confidence.</h2>
<p class="text-lg font-medium opacity-90 drop-shadow-sm">Streamlined operations, real-time analytics, and seamless integration all in one powerful ERP platform.</p>
</div>
</div>
<div class="flex w-full lg:w-1/2 flex-col items-center justify-center p-6 sm:p-12 bg-white dark:bg-background-dark">
<div class="w-full max-w-[480px] flex flex-col gap-6">
<div class="flex flex-col gap-2 pb-4">
<div class="mb-4 flex items-center gap-2">
<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
<span class="material-symbols-outlined text-3xl">grid_view</span>
</div>
<span class="text-xl font-bold tracking-tight text-[#111418] dark:text-white">Nexus ERP</span>
</div>
<h1 class="text-3xl font-bold leading-tight tracking-tight text-[#111418] dark:text-white">Welcome back</h1>
<p class="text-base font-normal text-slate-500 dark:text-slate-400">Please enter your details to sign in to the ERP portal.</p>
</div>

@if ($errors->any())
    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ $errors->first() }}
    </div>
@endif

<form action="{{ route('login.store', absolute: false) }}" class="flex flex-col gap-5" method="POST">
@csrf
<div class="flex flex-col gap-2">
<label class="text-base font-medium text-[#111418] dark:text-white" for="email">Email or Username</label>
<div class="relative">
<input class="form-input w-full rounded-lg border border-slate-200 bg-background-light dark:bg-[#1a2632] dark:border-slate-700 px-4 py-3.5 text-base text-[#111418] dark:text-white placeholder-slate-400 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors" id="email" name="email" placeholder="name@company.com" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"/>
<div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none flex items-center">
<span class="material-symbols-outlined">mail</span>
</div>
</div>
</div>
<div class="flex flex-col gap-2">
<label class="text-base font-medium text-[#111418] dark:text-white" for="password">Password</label>
<div class="relative group">
<input class="form-input w-full rounded-lg border border-slate-200 bg-background-light dark:bg-[#1a2632] dark:border-slate-700 px-4 py-3.5 pr-12 text-base text-[#111418] dark:text-white placeholder-slate-400 focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none transition-colors" id="password" name="password" placeholder="Enter your password" type="password" required autocomplete="current-password"/>
<button class="absolute right-0 top-0 h-full w-12 flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors focus:outline-none" type="button" data-password-toggle="password">
<span class="material-symbols-outlined">visibility</span>
</button>
</div>
</div>
<div class="flex flex-wrap items-center justify-between gap-4 py-1">
<label class="flex items-center gap-3 cursor-pointer group">
<input class="h-5 w-5 rounded border-slate-300 text-primary focus:ring-primary dark:border-slate-600 dark:bg-[#1a2632]" type="checkbox" name="remember"/>
<span class="text-sm font-medium text-slate-600 group-hover:text-slate-800 dark:text-slate-400 dark:group-hover:text-slate-200 transition-colors">Remember me</span>
</label>
<a class="text-sm font-semibold text-primary hover:text-blue-600 transition-colors" href="{{ route('password.request', absolute: false) }}">Forgot password?</a>
</div>
<button class="flex w-full items-center justify-center rounded-lg bg-primary py-3.5 px-4 text-base font-bold text-white shadow-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 dark:focus:ring-offset-[#101922] transition-colors" type="submit">
                        Sign In
                    </button>
<p class="text-center text-sm text-slate-600 dark:text-slate-400 mt-2">
                        Don't have an account?
                        <a class="font-bold text-primary hover:text-blue-600 transition-colors" href="#">Sign Up</a>
</p>
</form>
<div class="mt-8 flex justify-center text-xs text-slate-400 dark:text-slate-600">
<p>© 2024 Nexus ERP Systems. All rights reserved.</p>
</div>
</div>
</div>
</div>
</body></html>
