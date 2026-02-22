<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Settings - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'settings'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-[#1a2632] border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<h2 class="text-xl font-bold text-slate-800 dark:text-white hidden sm:block">Settings</h2>
</div>
</header>
<div class="flex-1 overflow-y-auto p-6 scroll-smooth">
<div class="max-w-[900px] mx-auto flex flex-col gap-6">
<div class="sm:hidden">
<h2 class="text-2xl font-bold text-slate-800 dark:text-white">Settings</h2>
</div>

@if (session('success'))
<div class="rounded-lg border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/20 dark:border-emerald-800 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-400">
{{ session('success') }}
</div>
@endif
@if (session('error'))
<div class="rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-400">
{{ session('error') }}
</div>
@endif

<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
<div class="p-5 border-b border-slate-200 dark:border-slate-700 flex items-center gap-3">
<div class="p-2 bg-primary/10 text-primary rounded-lg shrink-0">
<span class="material-symbols-outlined text-[22px]">tune</span>
</div>
<div class="min-w-0">
<h3 class="text-base font-semibold text-slate-800 dark:text-white">General</h3>
<p class="text-sm text-slate-500 dark:text-slate-400">Language, currency, timezone, and VAT</p>
</div>
</div>
<form method="POST" action="{{ route('settings.update') }}">
@csrf
<div class="p-5 sm:p-6">
<div class="grid grid-cols-1 sm:grid-cols-2 gap-5 sm:gap-6 max-w-2xl">
<div class="flex flex-col gap-1.5">
<label class="text-sm font-medium text-slate-700 dark:text-slate-300" for="timezone">Timezone</label>
<select class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary min-w-0" id="timezone" name="timezone" disabled>
<option value="UTC">UTC</option>
<option value="America/New_York">America/New York</option>
<option value="Europe/London">Europe/London</option>
<option value="Asia/Karachi">Asia/Karachi</option>
<option value="Asia/Riyadh">Asia/Riyadh</option>
</select>
</div>
<div class="flex flex-col gap-1.5">
<label class="text-sm font-medium text-slate-700 dark:text-slate-300" for="language">Language</label>
<select class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary min-w-0" id="language" name="language">
<option value="en">English</option>
<option value="ar">العربية (Arabic)</option>
<option value="ur">Urdu</option>
<option value="fr">Français (French)</option>
</select>
</div>
<div class="flex flex-col gap-1.5">
<label class="text-sm font-medium text-slate-700 dark:text-slate-300" for="currency">Currency</label>
<select class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary min-w-0" id="currency" name="currency">
<option value="SAR" {{ ($defaultCurrencyCode ?? 'USD') === 'SAR' ? 'selected' : '' }}>SAR (Saudi Riyal)</option>
<option value="USD" {{ ($defaultCurrencyCode ?? 'USD') === 'USD' ? 'selected' : '' }}>USD (US Dollar)</option>
<option value="EUR" {{ ($defaultCurrencyCode ?? 'USD') === 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
<option value="GBP" {{ ($defaultCurrencyCode ?? 'USD') === 'GBP' ? 'selected' : '' }}>GBP (British Pound)</option>
<option value="PKR" {{ ($defaultCurrencyCode ?? 'USD') === 'PKR' ? 'selected' : '' }}>PKR (Pakistani Rupee)</option>
<option value="AED" {{ ($defaultCurrencyCode ?? 'USD') === 'AED' ? 'selected' : '' }}>AED (UAE Dirham)</option>
</select>
</div>
<div class="flex flex-col gap-1.5">
<label class="text-sm font-medium text-slate-700 dark:text-slate-300" for="vat_percent">VAT (%)</label>
<input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary min-w-0" id="vat_percent" name="vat_percent" type="number" min="0" max="100" step="0.01" value="15" placeholder="e.g. 15">
</div>
</div>
<div class="mt-6">
<button type="submit" class="h-10 px-5 rounded-lg bg-primary hover:bg-blue-600 text-white text-sm font-medium transition-colors">Save settings</button>
</div>
</div>
</form>
</div>

<div class="mt-8 text-center text-xs text-slate-400 pb-4">© {{ date('Y') }} Nexus ERP Inc. All rights reserved.</div>
</div>
</main>
</body>
</html>
