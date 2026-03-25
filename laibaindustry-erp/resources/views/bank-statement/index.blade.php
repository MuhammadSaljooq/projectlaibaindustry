<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Bank statement - ERP'])
@include('partials.stitch-design')
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'bank_statement'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">Bank statement</h2>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Bank statement</h1>
<p class="text-sm text-[#586064] mt-2">Cash inflow and cash outflow · @if(auth()->user()->role !== 'viewer') add rows per block @else read-only @endif</p>
</div>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

@if (session('success'))
<div class="border border-[#ABB3B7] bg-white px-4 py-3 text-sm text-[#2B3437]">
{{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-0 border border-[#ABB3B7] bg-white md:divide-x md:divide-[#ABB3B7]">
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Total cash inflow</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol }} {{ number_format($inflowTotal ?? 0, 2) }}</p>
</div>
<div class="p-6">
<p class="st-label mb-2">Total cash outflow</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $currencySymbol }} {{ number_format($outflowTotal ?? 0, 2) }}</p>
</div>
</div>

@php
    $canWrite = auth()->user()->role !== 'viewer';
@endphp

{{-- Cash inflow --}}
<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white overflow-hidden">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">Cash inflow</h3>
<p class="text-[11px] text-[#586064] mt-1">Date · company name · amount</p>
</div>

@if($canWrite)
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#F8F9FA]">
@if ($errors->storeInflow->any())
<ul class="text-sm text-[#9F403D] mb-3 list-disc list-inside">
@foreach ($errors->storeInflow->all() as $err)
<li>{{ $err }}</li>
@endforeach
</ul>
@endif
<form method="POST" action="{{ route('bank-statement.store') }}" class="flex flex-col lg:flex-row lg:flex-wrap lg:items-end gap-3">
@csrf
<input type="hidden" name="flow_type" value="inflow">
<div class="flex flex-col gap-1 min-w-0 lg:w-40">
<label class="text-[10px] font-bold uppercase tracking-widest text-[#586064]" for="inflow_date">Date</label>
<input class="st-input w-full h-10 px-3 text-sm" type="date" id="inflow_date" name="transaction_date" value="{{ old('transaction_date') }}" required>
</div>
<div class="flex flex-col gap-1 min-w-0 flex-1 lg:min-w-[200px]">
<label class="text-[10px] font-bold uppercase tracking-widest text-[#586064]" for="inflow_company">Company name</label>
<input class="st-input w-full h-10 px-3 text-sm" type="text" id="inflow_company" name="company_name" value="{{ old('company_name') }}" maxlength="255" placeholder="Company" required>
</div>
<div class="flex flex-col gap-1 min-w-0 lg:w-36">
<label class="text-[10px] font-bold uppercase tracking-widest text-[#586064]" for="inflow_amount">Amount</label>
<input class="st-input w-full h-10 px-3 text-sm tabular-nums" type="number" id="inflow_amount" name="amount" value="{{ old('amount') }}" step="0.01" min="0.01" placeholder="0.00" required>
</div>
<button type="submit" class="st-btn-primary h-10 px-4 inline-flex items-center gap-2 text-[10px] w-fit">
<span class="material-symbols-outlined text-[18px]">add</span>
Add inflow
</button>
</form>
</div>
@endif

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[560px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3">Company name</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Amount</th>
@if($canWrite)
<th class="st-th px-4 py-3 text-right w-36"></th>
@endif
</tr>
</thead>
<tbody>
@forelse($inflows as $row)
<tr class="st-tr">
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ $row->transaction_date->format('Y-m-d') }}</td>
<td class="st-td px-4 py-3 text-sm font-semibold text-[#2B3437]">{{ $row->company_name }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums text-[#5E5E5E]">{{ $currencySymbol }} {{ number_format($row->amount, 2) }}</td>
@if($canWrite)
<td class="st-td px-4 py-3 text-right">
<div class="flex items-center justify-end gap-1 flex-wrap">
<a href="{{ route('bank-statement.edit', $row) }}" class="p-2 border border-transparent hover:border-[#ABB3B7] text-[#586064] hover:text-[#2B3437] hover:bg-[#F1F4F6]" title="Edit">
<span class="material-symbols-outlined text-[18px]">edit</span>
</a>
<form method="POST" action="{{ route('bank-statement.destroy', $row) }}" class="inline-flex" onsubmit="return confirm('Delete this entry?');">
@csrf
@method('DELETE')
<button type="submit" class="p-2 border border-transparent hover:border-[#9F403D] text-[#586064] hover:text-[#9F403D] hover:bg-[#F1F4F6]" title="Delete">
<span class="material-symbols-outlined text-[18px]">delete</span>
</button>
</form>
</div>
</td>
@endif
</tr>
@empty
<tr>
<td colspan="{{ $canWrite ? 4 : 3 }}" class="px-6 py-10 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437]">No cash inflow entries yet</p>
</td>
</tr>
@endforelse
</tbody>
</table>
</div>
</div>

{{-- Cash outflow --}}
<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white overflow-hidden">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">Cash outflow</h3>
<p class="text-[11px] text-[#586064] mt-1">Date · company name · amount</p>
</div>

@if($canWrite)
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#F8F9FA]">
@if ($errors->storeOutflow->any())
<ul class="text-sm text-[#9F403D] mb-3 list-disc list-inside">
@foreach ($errors->storeOutflow->all() as $err)
<li>{{ $err }}</li>
@endforeach
</ul>
@endif
<form method="POST" action="{{ route('bank-statement.store') }}" class="flex flex-col lg:flex-row lg:flex-wrap lg:items-end gap-3">
@csrf
<input type="hidden" name="flow_type" value="outflow">
<div class="flex flex-col gap-1 min-w-0 lg:w-40">
<label class="text-[10px] font-bold uppercase tracking-widest text-[#586064]" for="outflow_date">Date</label>
<input class="st-input w-full h-10 px-3 text-sm" type="date" id="outflow_date" name="transaction_date" value="{{ old('transaction_date') }}" required>
</div>
<div class="flex flex-col gap-1 min-w-0 flex-1 lg:min-w-[200px]">
<label class="text-[10px] font-bold uppercase tracking-widest text-[#586064]" for="outflow_company">Company name</label>
<input class="st-input w-full h-10 px-3 text-sm" type="text" id="outflow_company" name="company_name" value="{{ old('company_name') }}" maxlength="255" placeholder="Company" required>
</div>
<div class="flex flex-col gap-1 min-w-0 lg:w-36">
<label class="text-[10px] font-bold uppercase tracking-widest text-[#586064]" for="outflow_amount">Amount</label>
<input class="st-input w-full h-10 px-3 text-sm tabular-nums" type="number" id="outflow_amount" name="amount" value="{{ old('amount') }}" step="0.01" min="0.01" placeholder="0.00" required>
</div>
<button type="submit" class="st-btn-primary h-10 px-4 inline-flex items-center gap-2 text-[10px] w-fit">
<span class="material-symbols-outlined text-[18px]">add</span>
Add outflow
</button>
</form>
</div>
@endif

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[560px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3">Company name</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Amount</th>
@if($canWrite)
<th class="st-th px-4 py-3 text-right w-36"></th>
@endif
</tr>
</thead>
<tbody>
@forelse($outflows as $row)
<tr class="st-tr">
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ $row->transaction_date->format('Y-m-d') }}</td>
<td class="st-td px-4 py-3 text-sm font-semibold text-[#2B3437]">{{ $row->company_name }}</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right whitespace-nowrap tabular-nums text-[#5E5E5E]">{{ $currencySymbol }} {{ number_format($row->amount, 2) }}</td>
@if($canWrite)
<td class="st-td px-4 py-3 text-right">
<div class="flex items-center justify-end gap-1 flex-wrap">
<a href="{{ route('bank-statement.edit', $row) }}" class="p-2 border border-transparent hover:border-[#ABB3B7] text-[#586064] hover:text-[#2B3437] hover:bg-[#F1F4F6]" title="Edit">
<span class="material-symbols-outlined text-[18px]">edit</span>
</a>
<form method="POST" action="{{ route('bank-statement.destroy', $row) }}" class="inline-flex" onsubmit="return confirm('Delete this entry?');">
@csrf
@method('DELETE')
<button type="submit" class="p-2 border border-transparent hover:border-[#9F403D] text-[#586064] hover:text-[#9F403D] hover:bg-[#F1F4F6]" title="Delete">
<span class="material-symbols-outlined text-[18px]">delete</span>
</button>
</form>
</div>
</td>
@endif
</tr>
@empty
<tr>
<td colspan="{{ $canWrite ? 4 : 3 }}" class="px-6 py-10 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437]">No cash outflow entries yet</p>
</td>
</tr>
@endforelse
</tbody>
</table>
</div>
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-6 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
</body>
</html>
