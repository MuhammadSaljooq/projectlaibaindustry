<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Edit Product - Laiba Safety'])
<style>
body { background-color: #FFFFFF; color: #2B3437; font-family: 'Inter', sans-serif; color-scheme: light; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #2B3437; color: #FFFFFF; }
input::placeholder, textarea::placeholder { color: rgba(94, 94, 94, 0.55); }
input[type="number"]::-webkit-inner-spin-button { opacity: 0.5; }
main input:focus, main select:focus {
  outline: none !important;
  --tw-ring-shadow: 0 0 #0000 !important;
  --tw-ring-offset-shadow: 0 0 #0000 !important;
  box-shadow: none !important;
}
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'products'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#FFFFFF;">

<header class="h-14 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 rounded-none" style="color:#5E5E5E;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<a href="{{ route('inventory.dashboard') }}" class="flex items-center gap-2 transition-colors font-bold text-sm" style="color:#5E5E5E;" onmouseover="this.style.color='#2B3437'" onmouseout="this.style.color='#5E5E5E'">
<span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
<span class="hidden sm:inline">Back to Inventory</span>
</a>
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-2xl mx-auto flex flex-col" style="gap:3rem;">

<div>
<div class="flex items-end justify-between" style="padding-bottom:0.75rem;border-bottom:2px solid #5E5E5E;">
<div>
<h2 class="font-bold" style="font-size:1.5rem;letter-spacing:-0.02em;color:#2B3437;">Edit Product</h2>
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-top:0.5rem;">Modify Entry</p>
<p class="text-sm font-bold" style="color:#2B3437;margin-top:0.5rem;">{{ $product->name }} <span style="color:#5E5E5E;">/ {{ $product->sku }}</span></p>
</div>
<span class="text-[10px] font-bold uppercase self-start" style="letter-spacing:0.05em;color:#5E5E5E;">Form PRD-{{ $product->id }}</span>
</div>
</div>

@if ($errors->any())
<div style="border:1px solid #9F403D;padding:1rem 1.25rem;background:#FFFFFF;">
<p class="text-[10px] font-bold uppercase mb-2" style="color:#9F403D;letter-spacing:0.05em;">Please fix the following</p>
@foreach ($errors->all() as $error)
<p class="text-sm font-bold" style="color:#9F403D;margin-top:0.25rem;">{{ $error }}</p>
@endforeach
</div>
@endif

<div style="border:1px solid #D3D8DE;background:#FFFFFF;">

<div style="padding:1rem 1.5rem;border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Product Details</p>
</div>

<div style="padding:2rem;">

<form method="POST" action="{{ route('products.update', $product) }}">
@csrf
@method('PUT')

@include('products.partials.form')

<div style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid #D3D8DE;" class="flex flex-wrap items-center gap-3">
<button type="submit" class="h-11 px-6 text-[11px] font-bold uppercase flex items-center gap-2 active:scale-[0.98] transition-all" style="background:#5E5E5E;color:#F8F8F8;border-radius:0;letter-spacing:0.05em;" onmouseover="this.style.opacity='0.92'" onmouseout="this.style.opacity='1'">
<span class="material-symbols-outlined" style="font-size:16px;">save</span>
UPDATE PRODUCT
</button>
<a href="{{ route('inventory.dashboard') }}" class="h-11 px-6 text-[11px] font-bold uppercase flex items-center gap-2 transition-all" style="color:#2B3437;border:1px solid #5E5E5E;border-radius:0;letter-spacing:0.05em;background:transparent;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">
CANCEL
</a>
</div>
</form>

</div>
</div>

<div style="border:1px solid #9F403D;background:#FFFFFF;">
<div style="padding:1rem 1.5rem;border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#9F403D;">Hazard — Danger Zone</p>
</div>
<div style="padding:1.5rem 2rem;" class="flex items-center justify-between gap-4 flex-wrap">
<p class="text-xs font-bold" style="color:#5E5E5E;line-height:1.5;">Permanently delete this product from inventory. This action cannot be undone.</p>
<form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
@csrf
@method('DELETE')
<button type="submit" class="h-9 px-5 text-[11px] font-bold uppercase flex items-center gap-2 active:scale-[0.98] transition-all" style="color:#9F403D;border:1px solid #9F403D;border-radius:0;letter-spacing:0.05em;background:transparent;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:16px;">delete</span>
DELETE PRODUCT
</button>
</form>
</div>
</div>

<div class="text-center text-[10px] uppercase font-bold pb-4" style="letter-spacing:0.05em;color:#5E5E5E;">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</div>

</div>
</div>
</main>

</body>
</html>
