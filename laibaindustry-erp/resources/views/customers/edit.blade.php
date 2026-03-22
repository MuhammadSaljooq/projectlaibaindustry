<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Edit Customer - Laiba Safety'])
<style>
body { background-color: #FFFFFF; color: #2B3437; font-family: 'Inter', sans-serif; color-scheme: light; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #2B3437; color: #FFFFFF; }
input::placeholder, textarea::placeholder { color: rgba(94, 94, 94, 0.55) !important; }
.arch-input, textarea.arch-input {
  background-color: #FFFFFF !important;
  border: 1px solid #D3D8DE !important;
  border-radius: 0 !important;
  color: #2B3437 !important;
  color-scheme: light !important;
}
.arch-input:focus, textarea.arch-input:focus {
  border-color: #5E5E5E !important;
  border-width: 2px !important;
  box-shadow: none !important;
  outline: none !important;
  --tw-ring-shadow: none !important;
  --tw-ring-offset-shadow: none !important;
}
main input:focus, main textarea:focus {
  outline: none !important;
  --tw-ring-shadow: 0 0 #0000 !important;
  box-shadow: none !important;
}
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'customers'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#FFFFFF;">

<header class="h-14 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 rounded-none" style="color:#5E5E5E;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<a href="{{ route('customers.index') }}" class="flex items-center gap-2 text-sm font-bold transition-colors" style="color:#5E5E5E;" onmouseover="this.style.color='#2B3437'" onmouseout="this.style.color='#5E5E5E'">
<span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
Back to Customers
</a>
</div>
<div class="flex items-center gap-3">
<a href="{{ route('customers.statement', $customer) }}" class="h-9 px-4 text-[11px] font-bold uppercase inline-flex items-center gap-2 transition-all whitespace-nowrap" style="color:#2B3437;border:1px solid #5E5E5E;border-radius:0;letter-spacing:0.05em;background:transparent;" onmouseover="this.style.background='#F8F9FA'" onmouseleave="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:16px;">receipt_long</span>
STATEMENT
</a>
</div>
</header>

<div class="flex-1 overflow-y-auto no-scrollbar scroll-smooth">
<div class="max-w-[900px] mx-auto px-6 md:px-8 py-8 flex flex-col" style="gap:3rem;">

<div>
<div class="flex items-end justify-between flex-wrap gap-4" style="padding-bottom:0.75rem;border-bottom:2px solid #5E5E5E;">
<div>
<h1 class="font-bold" style="font-size:1.5rem;letter-spacing:-0.02em;color:#2B3437;">Edit Customer</h1>
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-top:0.5rem;">Modify Record</p>
<p class="text-sm font-bold mt-2" style="color:#5E5E5E;">{{ $customer->customer_name }} &middot; {{ $customer->customer_code }}</p>
</div>
<span class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Form CST-{{ $customer->id }}</span>
</div>
</div>

<div style="border:1px solid #D3D8DE;background:#FFFFFF;">
<div style="padding:1rem 1.5rem;border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Customer Details</p>
</div>
<div style="padding:2rem;">

<form method="POST" action="{{ route('customers.update', $customer) }}">
@csrf
@method('PUT')
@include('customers.partials.form')

<div class="flex flex-wrap items-center gap-3 mt-8 pt-6" style="border-top:1px solid #D3D8DE;">
<button type="submit" class="h-11 px-6 text-[11px] font-bold uppercase inline-flex items-center gap-2 transition-all whitespace-nowrap" style="background:#5E5E5E;color:#F8F8F8;border-radius:0;letter-spacing:0.05em;" onmouseover="this.style.opacity='0.92'" onmouseout="this.style.opacity='1'">
<span class="material-symbols-outlined" style="font-size:16px;">save</span>
UPDATE CUSTOMER
</button>
<a href="{{ route('customers.index') }}" class="h-11 px-6 text-[11px] font-bold uppercase inline-flex items-center transition-all whitespace-nowrap" style="color:#2B3437;border:1px solid #5E5E5E;border-radius:0;letter-spacing:0.05em;background:transparent;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">
CANCEL
</a>
</div>
</form>
</div>
</div>

@if(auth()->user()->role !== 'viewer')
<div style="border:1px solid #9F403D;background:#FFFFFF;">
<div style="padding:1rem 1.5rem;border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#9F403D;">Hazard — Danger Zone</p>
</div>
<div style="padding:1.5rem 2rem;">
<p class="text-xs font-bold mb-5" style="color:#5E5E5E;line-height:1.5;">Permanently delete this customer and all associated data.</p>
<form method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('Are you sure? This action cannot be undone.');">
@csrf
@method('DELETE')
<button type="submit" class="h-11 px-6 text-[11px] font-bold uppercase inline-flex items-center gap-2 transition-all whitespace-nowrap" style="color:#9F403D;border:1px solid #9F403D;border-radius:0;letter-spacing:0.05em;background:transparent;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:16px;">delete</span>
DELETE CUSTOMER
</button>
</form>
</div>
</div>
@endif

<footer class="pb-8 text-center">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</p>
</footer>

</div>
</div>

</main>
</body>
</html>
