<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Login - Laiba Safety'])
</head>
<body class="bg-black font-display text-white min-h-screen">
<div class="min-h-screen w-full flex items-center justify-center p-6 sm:p-8">
<div class="w-full max-w-md">
    {{-- Logo and Title --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl text-black">inventory_2</span>
            </div>
            <h1 class="text-2xl font-bold text-white">Laiba Safety</h1>
        </div>
    </div>

    {{-- Welcome Text --}}
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-white mb-2">Welcome back</h2>
        <p class="text-gray-400">Please enter your credentials to continue</p>
    </div>

    @if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-500/50 bg-red-500/10 px-4 py-3 text-sm text-red-400">
        {{ $errors->first() }}
    </div>
    @endif

    <form action="{{ route('login.store', absolute: false) }}" class="space-y-6" method="POST">
        @csrf
        {{-- Email Field --}}
        <div class="space-y-2">
            <label class="text-gray-300 text-sm font-medium" for="email">Email Address</label>
            <input
                class="w-full h-12 px-4 rounded-lg border border-white/50 bg-black text-white placeholder-gray-500 focus:border-white focus:ring-2 focus:ring-white/30 focus:outline-none transition-colors"
                id="email"
                name="email"
                type="email"
                placeholder="Enter your email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
            />
        </div>

        {{-- Password Field --}}
        <div class="space-y-2">
            <label class="text-gray-300 text-sm font-medium" for="password">Password</label>
            <div class="relative">
                <input
                    class="w-full h-12 px-4 pr-12 rounded-lg border border-white/50 bg-black text-white placeholder-gray-500 focus:border-white focus:ring-2 focus:ring-white/30 focus:outline-none transition-colors"
                    id="password"
                    name="password"
                    type="password"
                    placeholder="Enter your password"
                    required
                    autocomplete="current-password"
                />
                <button
                    type="button"
                    data-password-toggle="password"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition-colors focus:outline-none"
                    aria-label="Toggle password visibility"
                >
                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                </button>
            </div>
        </div>

        {{-- Remember me & Forgot password --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <label class="flex items-center gap-3 cursor-pointer group">
                <input class="h-5 w-5 rounded border-gray-500 bg-black text-white focus:ring-white/50 focus:ring-2" type="checkbox" name="remember"/>
                <span class="text-sm text-gray-400 group-hover:text-gray-300 transition-colors">Remember me</span>
            </label>
            <a class="text-sm text-gray-400 hover:text-white transition-colors" href="{{ route('password.request', absolute: false) }}">Forgot password?</a>
        </div>

        {{-- Sign In Button --}}
        <button
            type="submit"
            class="w-full h-12 rounded-lg bg-white hover:bg-gray-200 text-black font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-black"
        >
            Sign In
        </button>
    </form>

    {{-- Contact Admin Link --}}
    <div class="mt-6 text-center">
        <p class="text-gray-400 text-sm">
            Don't have an account? <a href="#" class="text-white hover:text-gray-300 transition-colors">Contact admin</a>
        </p>
    </div>

    {{-- Footer --}}
    <div class="mt-8 text-center text-xs text-gray-500">
        <p>© {{ date('Y') }} Laiba Safety. All rights reserved.</p>
    </div>
</div>
</div>
</body>
</html>
