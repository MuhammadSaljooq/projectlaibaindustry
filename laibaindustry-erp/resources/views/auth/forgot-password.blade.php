<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Forgot Password - Laiba Safety'])
</head>
<body class="bg-black font-display text-white min-h-screen">
<div class="min-h-screen w-full flex items-center justify-center p-6 sm:p-8">
<div class="w-full max-w-md">
    {{-- Logo and Title --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl text-black">lock_reset</span>
            </div>
            <h1 class="text-2xl font-bold text-white">Laiba Safety</h1>
        </div>
    </div>

    {{-- Heading --}}
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-white mb-2">Forgot Password</h2>
        <p class="text-gray-400">Enter your email to receive reset instructions.</p>
    </div>

    @if (session('status'))
    <div class="mb-6 rounded-lg border border-emerald-500/50 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400">
        {{ session('status') }}
    </div>
    @endif

    @if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-500/50 bg-red-500/10 px-4 py-3 text-sm text-red-400">
        {{ $errors->first() }}
    </div>
    @endif

    <form action="{{ route('password.email', absolute: false) }}" class="space-y-6" method="POST">
        @csrf
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
            />
        </div>

        <button
            type="submit"
            class="w-full h-12 rounded-lg bg-white hover:bg-gray-200 text-black font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-black"
        >
            Send Reset Link
        </button>
    </form>

    <div class="mt-6 text-center">
        <a class="text-sm text-gray-400 hover:text-white transition-colors" href="{{ route('login', absolute: false) }}">Back to Login</a>
    </div>

    <div class="mt-8 text-center text-xs text-gray-500">
        <p>© 2026 Laiba Safety. All rights reserved.</p>
    </div>
</div>
</div>
</body>
</html>
