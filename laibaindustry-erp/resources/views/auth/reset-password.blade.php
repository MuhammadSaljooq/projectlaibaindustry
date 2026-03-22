<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Reset Password - Laiba Safety'])
</head>
<body class="bg-black font-display text-white min-h-screen">
<div class="min-h-screen w-full flex items-center justify-center p-6 sm:p-8">
<div class="w-full max-w-md">
    {{-- Logo and Title --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-2">
            <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl text-black">vpn_key</span>
            </div>
            <h1 class="text-2xl font-bold text-white">Laiba Safety</h1>
        </div>
    </div>

    {{-- Heading --}}
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-white mb-2">Reset Password</h2>
        <p class="text-gray-400">Enter your email and your new password.</p>
    </div>

    @if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-500/50 bg-red-500/10 px-4 py-3 text-sm text-red-400">
        {{ $errors->first() }}
    </div>
    @endif

    <form action="{{ route('password.update', absolute: false) }}" class="space-y-6" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ request()->route('token') }}">

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
            />
        </div>

        <div class="space-y-2">
            <label class="text-gray-300 text-sm font-medium" for="password">New Password</label>
            <div class="relative">
                <input
                    class="w-full h-12 px-4 pr-12 rounded-lg border border-white/50 bg-black text-white placeholder-gray-500 focus:border-white focus:ring-2 focus:ring-white/30 focus:outline-none transition-colors"
                    id="password"
                    name="password"
                    type="password"
                    placeholder="Enter new password"
                    required
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

        <div class="space-y-2">
            <label class="text-gray-300 text-sm font-medium" for="password_confirmation">Confirm Password</label>
            <div class="relative">
                <input
                    class="w-full h-12 px-4 pr-12 rounded-lg border border-white/50 bg-black text-white placeholder-gray-500 focus:border-white focus:ring-2 focus:ring-white/30 focus:outline-none transition-colors"
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    placeholder="Confirm new password"
                    required
                />
                <button
                    type="button"
                    data-password-toggle="password_confirmation"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition-colors focus:outline-none"
                    aria-label="Toggle password visibility"
                >
                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                </button>
            </div>
        </div>

        <button
            type="submit"
            class="w-full h-12 rounded-lg bg-white hover:bg-gray-200 text-black font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-black"
        >
            Reset Password
        </button>
    </form>

    <div class="mt-6 text-center">
        <a class="text-sm text-gray-400 hover:text-white transition-colors" href="{{ route('login', absolute: false) }}">Back to Login</a>
    </div>

    <div class="mt-8 text-center text-xs text-gray-500">
        <p>&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</p>
    </div>
</div>
</div>
<script>
document.querySelectorAll('[data-password-toggle]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var input = document.getElementById(btn.getAttribute('data-password-toggle'));
        if (!input) return;
        var icon = btn.querySelector('.material-symbols-outlined');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) icon.textContent = 'visibility_off';
        } else {
            input.type = 'password';
            if (icon) icon.textContent = 'visibility';
        }
    });
});
</script>
</body>
</html>
