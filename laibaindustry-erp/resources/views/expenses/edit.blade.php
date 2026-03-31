<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Edit Expense - ERP'])
@include('partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'expenses'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<a href="{{ route('expenses.index') }}" class="st-btn-secondary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
<span class="hidden sm:inline">Expenses</span>
</a>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-4xl mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col gap-1">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">EXP_AMEND_17</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Edit expense</h1>
<p class="text-sm text-[#586064] mt-2">{{ $expense->type }} · {{ $expense->date->format('Y-m-d') }}</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

@if ($errors->any())
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D]">
<p class="st-label st-label--error mb-2">Please fix the following</p>
<ul class="list-disc list-inside space-y-0.5">
@foreach ($errors->all() as $err)
<li>{{ $err }}</li>
@endforeach
</ul>
</div>
@endif

<div class="st-paper border border-[#ABB3B7] p-6 md:p-8 bg-white">
<form method="POST" action="{{ route('expenses.update', $expense) }}">
@csrf
@method('PUT')
<p class="st-label mb-6">Expense details</p>

<div class="space-y-5 max-w-xl">
<div>
<label class="st-label block mb-2" for="date">Date</label>
<input class="st-input w-full h-10 px-3 text-sm" type="date" name="date" id="date" value="{{ old('date', $expense->date->format('Y-m-d')) }}" required>
</div>
<div>
<label class="st-label block mb-2" for="type">Type</label>
<input class="st-input w-full h-10 px-3 text-sm" type="text" name="type" id="type" value="{{ old('type', $expense->type) }}" placeholder="e.g. Rent, utilities" required maxlength="255">
</div>
<div>
<label class="st-label block mb-2" for="amount">Amount</label>
<input class="st-input w-full h-10 px-3 text-sm tabular-nums" type="number" name="amount" id="amount" value="{{ old('amount', $expense->amount) }}" step="0.01" min="0" placeholder="0.00" required>
</div>
</div>

<div class="flex flex-wrap items-center gap-3 mt-8 pt-6 border-t border-[#ABB3B7]">
<button type="submit" class="st-btn-primary h-10 px-5 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">save</span>
Update expense
</button>
<a href="{{ route('expenses.index') }}" class="st-btn-secondary h-10 px-5 inline-flex items-center gap-2">Cancel</a>
</div>
</form>
</div>

<div class="border border-[#9F403D] bg-white p-6">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
<div>
<p class="st-label st-label--error mb-1">Hazard · delete</p>
<p class="text-xs text-[#586064]">Remove this expense record permanently.</p>
</div>
<form method="POST" action="{{ route('expenses.destroy', $expense) }}" data-confirm-delete="{{ e('Delete this expense?') }}">
@csrf
@method('DELETE')
<button type="submit" class="st-btn-secondary h-10 px-5 inline-flex items-center gap-2 border-[#9F403D] text-[#9F403D] hover:bg-[#F1F4F6] shrink-0">
<span class="material-symbols-outlined text-[18px]">delete</span>
Delete
</button>
</form>
</div>
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-4 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
