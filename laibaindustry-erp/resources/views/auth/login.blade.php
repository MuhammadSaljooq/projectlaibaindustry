<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Login - Laiba Safety'])
</head>
<body class="min-h-screen w-full flex bg-black text-white antialiased">
<div class="w-full flex items-center justify-center p-6 sm:p-8">
<div class="w-full max-w-md">
{{-- Logo and title --}}
<div class="mb-8">
<div class="flex items-center gap-2 mb-2">
<div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-black text-[28px]">inventory_2</span>
</div>
<h1 class="text-2xl font-bold text-white tracking-tight">Laiba Safety</h1>
</div>
</div>

{{-- Welcome text --}}
<div class="mb-8">
<h2 class="text-3xl font-bold text-white mb-2">Welcome back</h2>
<p class="text-gray-400">Please enter your credentials to continue</p>
</div>

@if ($errors->any())
<div class="mb-6 rounded-lg border border-red-500/50 bg-red-500/10 px-4 py-3 text-sm text-red-200">
{{ $errors->first() }}
</div>
@endif

{{-- Login form --}}
<form action="{{ route('login.store', absolute: false) }}" method="POST" class="space-y-6">
@csrf
<div class="space-y-2">
<label for="email" class="block text-sm font-medium text-gray-300">Email Address</label>
<input
id="email"
name="email"
type="email"
placeholder="Enter your email"
value="{{ old('email') }}"
required
autofocus
autocomplete="username"
class="h-12 w-full rounded-lg border border-white/30 bg-black px-4 py-3 text-white placeholder:text-gray-500 focus:border-white focus:ring-2 focus:ring-white/30 focus:outline-none transition-colors"
/>
</div>

<div class="space-y-2">
<label for="password" class="block text-sm font-medium text-gray-300">Password</label>
<div class="relative">
<input
id="password"
name="password"
type="password"
placeholder="Enter your password"
required
autocomplete="current-password"
class="h-12 w-full rounded-lg border border-white/30 bg-black pl-4 pr-12 py-3 text-white placeholder:text-gray-500 focus:border-white focus:ring-2 focus:ring-white/30 focus:outline-none transition-colors"
/>
<button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition-colors focus:outline-none" data-password-toggle="password" aria-label="Toggle password visibility">
<span class="material-symbols-outlined text-[22px]">visibility</span>
</button>
</div>
</div>

<div class="flex flex-wrap items-center justify-between gap-4 py-1">
<label class="flex items-center gap-3 cursor-pointer group">
<input class="h-5 w-5 rounded border-gray-500 bg-black text-white focus:ring-white focus:ring-offset-0 focus:ring-2" type="checkbox" name="remember"/>
<span class="text-sm font-medium text-gray-400 group-hover:text-gray-300 transition-colors">Remember me</span>
</label>
@if(Route::has('password.request'))
<a class="text-sm font-medium text-gray-400 hover:text-white transition-colors" href="{{ route('password.request', absolute: false) }}">Forgot password?</a>
@endif
</div>

<button type="submit" class="w-full h-12 rounded-lg bg-white text-black font-bold hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-black transition-colors">
Sign In
</button>
</form>

<div class="mt-6 text-center">
<p class="text-gray-400">
Don't have an account?
<a href="#" class="text-white hover:text-gray-300 transition-colors">Contact admin</a>
</p>
</div>

<div class="mt-8 text-center text-xs text-gray-500">
<p>© {{ date('Y') }} Laiba Safety. All rights reserved.</p>
</div>
</div>
</div>
</body>
</html>
