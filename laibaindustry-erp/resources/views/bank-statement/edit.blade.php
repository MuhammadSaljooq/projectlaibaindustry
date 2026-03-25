<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Edit bank entry - ERP'])
@include('partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'bank_statement'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<a href="{{ route('bank-statement.index') }}" class="st-btn-secondary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
<span class="hidden sm:inline">Bank statement</span>
</a>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-4xl mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col gap-1">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">Bank entry</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Edit entry</h1>
<p class="text-sm text-[#586064] mt-2">{{ $entry->flow_type === 'inflow' ? 'Cash inflow' : 'Cash outflow' }} · {{ $entry->transaction_date->format('Y-m-d') }}</p>
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
<form method="POST" action="{{ route('bank-statement.update', $entry) }}">
@csrf
@method('PUT')
<p class="st-label mb-6">Entry details</p>

<div class="space-y-5 max-w-xl">
<div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] mb-1">Flow type</p>
<p class="text-sm font-semibold text-[#2B3437]">{{ $entry->flow_type === 'inflow' ? 'Cash inflow' : 'Cash outflow' }}</p>
</div>
<div>
<label class="st-label block mb-2" for="transaction_date">Date</label>
<input class="st-input w-full h-10 px-3 text-sm" type="date" name="transaction_date" id="transaction_date" value="{{ old('transaction_date', $entry->transaction_date->format('Y-m-d')) }}" required>
</div>
<div>
<label class="st-label block mb-2" for="company_name">Company name</label>
<input class="st-input w-full h-10 px-3 text-sm" type="text" name="company_name" id="company_name" value="{{ old('company_name', $entry->company_name) }}" maxlength="255" required>
</div>
<div>
<label class="st-label block mb-2" for="amount">Amount</label>
<input class="st-input w-full h-10 px-3 text-sm tabular-nums" type="number" name="amount" id="amount" value="{{ old('amount', $entry->amount) }}" step="0.01" min="0.01" required>
</div>
</div>

<div class="flex flex-wrap items-center gap-3 mt-8 pt-6 border-t border-[#ABB3B7]">
<button type="submit" class="st-btn-primary h-10 px-5 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">save</span>
Update entry
</button>
<a href="{{ route('bank-statement.index') }}" class="st-btn-secondary h-10 px-5 inline-flex items-center gap-2">Cancel</a>
</div>
</form>
</div>

<div class="border border-[#9F403D] bg-white p-6">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
<div>
<p class="st-label st-label--error mb-1">Delete entry</p>
<p class="text-sm text-[#586064]">This cannot be undone.</p>
</div>
<form method="POST" action="{{ route('bank-statement.destroy', $entry) }}" onsubmit="return confirm('Delete this entry permanently?');">
@csrf
@method('DELETE')
<button type="submit" class="st-btn-secondary h-10 px-4 border-[#9F403D] text-[#9F403D] hover:bg-[#FDF5F5] inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">delete</span>
Delete
</button>
</form>
</div>
</div>

</div>
</div>
</main>
</body>
</html>
