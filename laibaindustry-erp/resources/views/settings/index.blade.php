<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Settings - Laiba Safety'])
<style>
body { background-color: #FFFFFF; color: #2B3437; font-family: 'Inter', sans-serif; color-scheme: light; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #2B3437; color: #FFFFFF; }
.arch-input, .arch-select {
  background-color: #FFFFFF !important;
  border: 1px solid #D3D8DE !important;
  border-radius: 0 !important;
  color: #2B3437 !important;
  color-scheme: light !important;
}
.arch-input:focus, .arch-select:focus {
  border-color: #5E5E5E !important;
  border-width: 2px !important;
  box-shadow: none !important;
  outline: none !important;
  --tw-ring-shadow: none !important;
}
.arch-select option { background: #FFFFFF; color: #2B3437; }
main input:focus, main select:focus { outline: none !important; box-shadow: none !important; }
.arch-select:disabled { opacity: 0.65; cursor: not-allowed; }
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'settings'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#FFFFFF;">

<header class="h-14 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 rounded-none" style="color:#5E5E5E;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[900px] mx-auto flex flex-col" style="gap:3rem;">

<div>
<div class="flex items-end justify-between" style="padding-bottom:0.75rem;border-bottom:2px solid #5E5E5E;">
<h2 class="font-bold" style="font-size:1.5rem;letter-spacing:-0.02em;color:#2B3437;">Settings</h2>
<span class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Section CFG-01</span>
</div>
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-top:0.75rem;">System preferences</p>
</div>

@if (session('success'))
<div style="border:1px solid #D3D8DE;padding:0.75rem 1.25rem;" class="text-sm font-bold flex items-center gap-3">
<span class="material-symbols-outlined" style="color:#5E5E5E;font-size:20px;">check_circle</span>
<span>{{ session('success') }}</span>
</div>
@endif
@if (session('error'))
<div style="border:1px solid #9F403D;padding:0.75rem 1.25rem;background:#FFFFFF;" class="text-sm font-bold flex items-center gap-3">
<span class="material-symbols-outlined" style="color:#9F403D;font-size:20px;">error</span>
<span style="color:#9F403D;">{{ session('error') }}</span>
</div>
@endif
@if ($errors->any())
<div style="border:1px solid #9F403D;padding:1rem 1.25rem;background:#FFFFFF;">
<p class="text-[10px] font-bold uppercase mb-2" style="color:#9F403D;letter-spacing:0.05em;">Please fix the following</p>
@foreach ($errors->all() as $error)
<p class="text-sm font-bold" style="color:#9F403D;margin-top:0.25rem;">{{ $error }}</p>
@endforeach
</div>
@endif

<div style="border:1px solid #D3D8DE;background:#FFFFFF;">
<div style="padding:1rem 1.5rem;border-bottom:1px solid #D3D8DE;background:#F8F9FA;" class="flex items-start gap-3">
<div class="flex items-center justify-center shrink-0" style="width:40px;height:40px;border:1px solid #D3D8DE;background:#FFFFFF;">
<span class="material-symbols-outlined" style="font-size:22px;color:#5E5E5E;">tune</span>
</div>
<div class="min-w-0">
<p class="text-sm font-bold" style="color:#2B3437;">General</p>
<p class="text-xs font-bold mt-0.5" style="color:#5E5E5E;">Locale, currency, and tax display defaults.</p>
</div>
</div>

<form method="POST" action="{{ route('settings.update') }}">
@csrf
<div style="padding:2rem;">
<div class="grid grid-cols-1 sm:grid-cols-2" style="gap:1.5rem;max-width:42rem;">
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="timezone">Timezone</label>
<select class="arch-select w-full h-11 pl-4 pr-10 text-sm font-bold appearance-none cursor-pointer" id="timezone" name="timezone" disabled>
<option value="UTC">UTC</option>
<option value="America/New_York">America / New York</option>
<option value="Europe/London">Europe / London</option>
<option value="Asia/Karachi">Asia / Karachi</option>
<option value="Asia/Riyadh">Asia / Riyadh</option>
</select>
<p class="text-[10px] font-bold uppercase mt-1.5" style="letter-spacing:0.05em;color:#5E5E5E;">Coming soon</p>
</div>
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="language">Language</label>
<select class="arch-select w-full h-11 pl-4 pr-10 text-sm font-bold appearance-none cursor-pointer" id="language" name="language">
<option value="en">English</option>
<option value="ar">العربية (Arabic)</option>
<option value="ur">Urdu</option>
<option value="fr">Français (French)</option>
</select>
<p class="text-[10px] font-bold uppercase mt-1.5" style="letter-spacing:0.05em;color:#5E5E5E;">UI only — not persisted</p>
</div>
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="currency">Default currency <span style="color:#9F403D;">*</span></label>
<select class="arch-select w-full h-11 pl-4 pr-10 text-sm font-bold appearance-none cursor-pointer" id="currency" name="currency" required>
<option value="SAR" {{ ($defaultCurrencyCode ?? 'USD') === 'SAR' ? 'selected' : '' }}>SAR — Saudi Riyal</option>
<option value="USD" {{ ($defaultCurrencyCode ?? 'USD') === 'USD' ? 'selected' : '' }}>USD — US Dollar</option>
<option value="EUR" {{ ($defaultCurrencyCode ?? 'USD') === 'EUR' ? 'selected' : '' }}>EUR — Euro</option>
<option value="GBP" {{ ($defaultCurrencyCode ?? 'USD') === 'GBP' ? 'selected' : '' }}>GBP — British Pound</option>
<option value="PKR" {{ ($defaultCurrencyCode ?? 'USD') === 'PKR' ? 'selected' : '' }}>PKR — Pakistani Rupee</option>
<option value="AED" {{ ($defaultCurrencyCode ?? 'USD') === 'AED' ? 'selected' : '' }}>AED — UAE Dirham</option>
</select>
</div>
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="vat_percent">VAT (%)</label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold font-mono tabular-nums" id="vat_percent" name="vat_percent" type="number" min="0" max="100" step="0.01" value="15" placeholder="e.g. 15">
<p class="text-[10px] font-bold uppercase mt-1.5" style="letter-spacing:0.05em;color:#5E5E5E;">Reference — use Tax settings for live rate</p>
</div>
</div>

<div style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid #D3D8DE;">
<button type="submit" class="h-11 px-6 text-[11px] font-bold uppercase inline-flex items-center gap-2 active:scale-[0.98] transition-all" style="background:#5E5E5E;color:#F8F8F8;border-radius:0;letter-spacing:0.05em;" onmouseover="this.style.opacity='0.92'" onmouseout="this.style.opacity='1'">
<span class="material-symbols-outlined" style="font-size:16px;">save</span>SAVE SETTINGS
</button>
<p class="text-[10px] font-bold uppercase mt-3" style="letter-spacing:0.05em;color:#5E5E5E;">Saving updates the <strong style="color:#2B3437;">default currency</strong> in the database.</p>
</div>
</div>
</form>
</div>

<div class="text-center text-[10px] uppercase font-bold pb-4" style="letter-spacing:0.05em;color:#5E5E5E;">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</div>
</div>
</div>
</main>
</body>
</html>
