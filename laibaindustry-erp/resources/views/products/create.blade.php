<!DOCTYPE html>
<html lang="en" class="dark">
<head>
@include('partials.frontend-head', ['title' => 'Add Product - Laiba Safety'])
<style>
body { background-color: #131313; color: #e2e2e2; font-family: 'Inter', sans-serif; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #FFFFFF; color: #131313; }
input::placeholder, select option[value=""] { color: rgba(196,199,200,0.5); }
input[type="number"]::-webkit-inner-spin-button { opacity: 0.3; }
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'products'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#131313;">

{{-- Header --}}
<header class="h-16 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="background:#1B1B1B;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 hover:text-white rounded-md" style="color:#8e9192;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<a href="{{ route('inventory.dashboard') }}" class="flex items-center gap-2 transition-colors" style="color:#C4C7C8;" onmouseover="this.style.color='#FFFFFF'" onmouseout="this.style.color='#C4C7C8'">
<span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
<span class="text-sm font-medium hidden sm:inline">Back to Inventory</span>
</a>
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-2xl mx-auto flex flex-col" style="gap:2rem;">

{{-- Page Heading --}}
<div>
<span class="text-[11px] font-medium uppercase block mb-2" style="letter-spacing:0.2em;color:#8e9192;">New Entry</span>
<h2 class="text-white font-black" style="font-size:2rem;letter-spacing:-0.02em;line-height:1.1;">Add Product</h2>
</div>

{{-- Validation Errors --}}
@if ($errors->any())
<div style="background:rgba(255,180,171,0.08);border-radius:0.5rem;padding:1rem 1.25rem;">
<p class="text-xs font-bold uppercase mb-2" style="color:#FFB4AB;letter-spacing:0.1em;">Please fix the following</p>
@foreach ($errors->all() as $error)
<p class="text-sm font-medium" style="color:#FFB4AB;margin-top:0.25rem;">{{ $error }}</p>
@endforeach
</div>
@endif

{{-- Form Card --}}
<div style="background:#1B1B1B;border-radius:0.5rem;padding:2rem;">

<form method="POST" action="{{ route('products.store') }}">
@csrf

{{-- Section Label --}}
<p class="text-[10px] font-bold uppercase mb-6" style="letter-spacing:0.15em;color:#8e9192;">Product Details</p>

@include('products.partials.form')

{{-- Action Buttons --}}
<div class="flex flex-wrap items-center gap-3" style="margin-top:2rem;">
<button type="submit" class="h-11 px-6 text-[11px] font-bold uppercase flex items-center gap-2 active:scale-[0.98] transition-all" style="background:#FFFFFF;color:#2F3131;border-radius:0.375rem;letter-spacing:0.05em;" onmouseover="this.style.background='#C6C6C7'" onmouseout="this.style.background='#FFFFFF'">
<span class="material-symbols-outlined" style="font-size:16px;">save</span>
SAVE PRODUCT
</button>
<a href="{{ route('inventory.dashboard') }}" class="h-11 px-6 text-[11px] font-bold uppercase flex items-center gap-2 transition-all" style="color:#C4C7C8;border:1px solid rgba(142,145,146,0.2);border-radius:0.375rem;letter-spacing:0.05em;" onmouseover="this.style.color='#FFFFFF';this.style.borderColor='rgba(255,255,255,0.3)'" onmouseout="this.style.color='#C4C7C8';this.style.borderColor='rgba(142,145,146,0.2)'">
CANCEL
</a>
</div>
</form>

</div>

{{-- Footer --}}
<div class="text-center text-[10px] uppercase font-medium pb-4" style="margin-top:1rem;letter-spacing:0.15em;color:#8e9192;">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</div>

</div>
</div>
</main>

</body>
</html>
