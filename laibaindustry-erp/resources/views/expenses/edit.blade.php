<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Edit Expense - Laiba Safety'])
<style>
body { background-color: #FFFFFF; color: #2B3437; font-family: 'Inter', sans-serif; color-scheme: light; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #2B3437; color: #FFFFFF; }
input::placeholder { color: rgba(94, 94, 94, 0.55) !important; }
.arch-input {
  background-color: #FFFFFF !important;
  border: 1px solid #D3D8DE !important;
  border-radius: 0 !important;
  color: #2B3437 !important;
  color-scheme: light !important;
}
.arch-input:focus {
  border-color: #5E5E5E !important;
  border-width: 2px !important;
  box-shadow: none !important;
  outline: none !important;
  --tw-ring-shadow: none !important;
}
main input:focus {
  outline: none !important;
  box-shadow: none !important;
}
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'expenses'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#FFFFFF;">

<header class="h-14 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 rounded-none" style="color:#5E5E5E;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu"><span class="material-symbols-outlined">menu</span></button>
<a href="{{ route('expenses.index') }}" class="flex items-center gap-2 transition-colors font-bold text-sm" style="color:#5E5E5E;" onmouseover="this.style.color='#2B3437'" onmouseout="this.style.color='#5E5E5E'">
<span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
<span class="hidden sm:inline">Back to Expenses</span>
</a>
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-xl mx-auto flex flex-col" style="gap:3rem;">

<div>
<div class="flex items-end justify-between" style="padding-bottom:0.75rem;border-bottom:2px solid #5E5E5E;">
<div>
<h2 class="font-bold" style="font-size:1.5rem;letter-spacing:-0.02em;color:#2B3437;">Edit expense</h2>
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-top:0.5rem;">Update entry</p>
</div>
<span class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Form EXP-{{ $expense->id }}</span>
</div>
</div>

@if ($errors->any())
<div style="border:1px solid #9F403D;padding:1rem 1.25rem;background:#FFFFFF;">
<p class="text-[10px] font-bold uppercase mb-2" style="color:#9F403D;letter-spacing:0.05em;">Please fix the following</p>
@foreach ($errors->all() as $err)
<p class="text-sm font-bold" style="color:#9F403D;margin-top:0.25rem;">{{ $err }}</p>
@endforeach
</div>
@endif

<div style="border:1px solid #D3D8DE;background:#FFFFFF;">
<div style="padding:1rem 1.5rem;border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Expense details</p>
</div>
<div style="padding:2rem;">
<form method="POST" action="{{ route('expenses.update', $expense) }}">
@csrf
@method('PUT')

<div style="margin-bottom:1.5rem;">
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="date">Date <span style="color:#9F403D;">*</span></label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold" type="date" name="date" id="date" value="{{ old('date', $expense->date->format('Y-m-d')) }}" required>
</div>
<div style="margin-bottom:1.5rem;">
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="type">Type <span style="color:#9F403D;">*</span></label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold" type="text" name="type" id="type" value="{{ old('type', $expense->type) }}" placeholder="e.g. Rent, Utilities" required>
</div>
<div style="margin-bottom:1.5rem;">
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="amount">Amount ({{ $currencySymbol ?? '$' }}) <span style="color:#9F403D;">*</span></label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold font-mono text-right tabular-nums" type="number" name="amount" id="amount" value="{{ old('amount', $expense->amount) }}" step="0.01" min="0" placeholder="0.00" required>
</div>

<div style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid #D3D8DE;" class="flex flex-wrap items-center gap-3">
<button type="submit" class="h-11 px-6 text-[11px] font-bold uppercase flex items-center gap-2 active:scale-[0.98] transition-all" style="background:#5E5E5E;color:#F8F8F8;border-radius:0;letter-spacing:0.05em;" onmouseover="this.style.opacity='0.92'" onmouseout="this.style.opacity='1'">
<span class="material-symbols-outlined" style="font-size:16px;">save</span>UPDATE EXPENSE
</button>
<a href="{{ route('expenses.index') }}" class="h-11 px-6 text-[11px] font-bold uppercase flex items-center gap-2 transition-all" style="color:#2B3437;border:1px solid #5E5E5E;border-radius:0;letter-spacing:0.05em;background:transparent;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">CANCEL</a>
</div>
</form>
</div>
</div>

<div class="text-center text-[10px] uppercase font-bold pb-4" style="letter-spacing:0.05em;color:#5E5E5E;">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</div>
</div>
</div>
</main>
</body>
</html>
