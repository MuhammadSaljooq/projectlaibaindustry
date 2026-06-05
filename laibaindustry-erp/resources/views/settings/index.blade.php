<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Settings - ERP'])
@include('partials.stitch-design')
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'settings'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">Settings</h2>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-4xl mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Settings</h1>
</div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] lg:text-right shrink-0">Regional · currency default</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<div class="sm:hidden">
<p class="st-label">Module</p>
<p class="text-xl font-black uppercase tracking-tight text-[#2B3437]">Settings</p>
</div>

@if (session('success'))
<div class="border border-[#ABB3B7] bg-white px-4 py-3 text-sm text-[#2B3437]">
{{ session('success') }}
</div>
@endif
@if (session('error'))
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D]">
{{ session('error') }}
</div>
@endif
@if ($errors->any())
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D]">
<p class="st-label st-label--error mb-2">Please fix the following</p>
<ul class="list-disc list-inside space-y-0.5">
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif

<div class="st-paper border border-[#ABB3B7] bg-white overflow-hidden flex flex-col">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1] flex items-start gap-4">
<div class="h-10 w-10 shrink-0 border border-[#ABB3B7] bg-white flex items-center justify-center">
<span class="material-symbols-outlined text-[#5E5E5E] text-[22px]">tune</span>
</div>
<div class="min-w-0">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">General</h3>
<p class="text-[11px] text-[#586064] mt-1">Timezone, language, VAT reference, and default reporting currency.</p>
</div>
</div>

<form method="POST" action="{{ route('settings.update') }}">
@csrf
<div class="p-6 md:p-8">
<p class="text-[11px] text-[#586064] border border-[#ABB3B7] bg-[#F8F9FA] px-3 py-2 mb-6">
<strong class="text-[#2B3437]">Note:</strong> Saving updates the <strong class="text-[#5E5E5E]">default currency</strong> only. Other controls below are for display / future use.
</p>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6 max-w-3xl">
<div>
<label class="st-label block mb-2" for="timezone">Timezone</label>
<select class="st-select w-full min-w-0 h-10 pl-3 pr-12 text-sm cursor-not-allowed opacity-70" id="timezone" name="timezone" disabled>
<option value="UTC">UTC</option>
<option value="America/New_York">America / New York</option>
<option value="Europe/London">Europe / London</option>
<option value="Asia/Karachi">Asia / Karachi</option>
<option value="Asia/Riyadh">Asia / Riyadh</option>
</select>
</div>

<div>
<label class="st-label block mb-2" for="language">Language</label>
<select class="st-select w-full min-w-0 h-10 pl-3 pr-12 text-sm cursor-pointer" id="language" name="language">
<option value="en">English</option>
<option value="ar">العربية (Arabic)</option>
<option value="ur">Urdu</option>
<option value="fr">Français (French)</option>
</select>
</div>

<div>
<label class="st-label block mb-2" for="currency">Default currency</label>
<select class="st-select w-full min-w-0 h-10 pl-3 pr-12 text-sm cursor-pointer" id="currency" name="currency" required>
<option value="SAR" {{ ($defaultCurrencyCode ?? 'USD') === 'SAR' ? 'selected' : '' }}>SAR — Saudi Riyal</option>
<option value="USD" {{ ($defaultCurrencyCode ?? 'USD') === 'USD' ? 'selected' : '' }}>USD — US Dollar</option>
<option value="EUR" {{ ($defaultCurrencyCode ?? 'USD') === 'EUR' ? 'selected' : '' }}>EUR — Euro</option>
<option value="GBP" {{ ($defaultCurrencyCode ?? 'USD') === 'GBP' ? 'selected' : '' }}>GBP — British Pound</option>
<option value="PKR" {{ ($defaultCurrencyCode ?? 'USD') === 'PKR' ? 'selected' : '' }}>PKR — Pakistani Rupee</option>
<option value="AED" {{ ($defaultCurrencyCode ?? 'USD') === 'AED' ? 'selected' : '' }}>AED — UAE Dirham</option>
</select>
</div>

<div>
<label class="st-label block mb-2" for="vat_percent">VAT reference (%)</label>
<input class="st-input w-full h-10 px-3 text-sm tabular-nums opacity-70" id="vat_percent" name="vat_percent" type="number" min="0" max="100" step="0.01" value="15" placeholder="e.g. 15" disabled title="Not persisted — VAT rate is configured in application logic">
</div>
</div>

<div class="mt-8 pt-6 border-t border-[#ABB3B7]">
<button type="submit" class="st-btn-primary h-10 px-5 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">save</span>
Save settings
</button>
</div>
</div>
</form>
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-6 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
