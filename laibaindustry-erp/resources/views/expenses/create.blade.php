<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Add Expense - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'expenses'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-[#1a2632] border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<a href="{{ route('expenses.index') }}" class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-300 hover:text-primary transition-colors">
<span class="material-symbols-outlined">arrow_back</span>
<span class="text-xl font-bold text-slate-800 dark:text-white hidden sm:inline">Add Expense</span>
</a>
</div>
</header>
<div class="flex-1 overflow-y-auto p-6 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-6">
<div class="sm:hidden">
<h2 class="text-2xl font-bold text-slate-800 dark:text-white">Add Expense</h2>
</div>

@if ($errors->any())
<div class="rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-400">
{{ $errors->first() }}
</div>
@endif

<div class="max-w-xl">
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
<form method="POST" action="{{ route('expenses.store') }}">
@csrf
<div class="space-y-5">
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2" for="date">Date</label>
<input type="date" name="date" id="date" value="{{ old('date', now()->format('Y-m-d')) }}" required
    class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary">
</div>
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2" for="type">Type</label>
<input type="text" name="type" id="type" value="{{ old('type') }}" placeholder="e.g. Rent, Utilities, Office Supplies" required
    class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-primary focus:border-primary">
</div>
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2" for="amount">Amount</label>
<input type="number" name="amount" id="amount" value="{{ old('amount') }}" step="0.01" min="0" placeholder="0.00" required
    class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-primary focus:border-primary">
</div>
</div>
<div class="flex flex-wrap gap-3 mt-6">
<button type="submit" class="h-10 px-5 bg-primary hover:bg-blue-600 text-white text-sm font-bold rounded-lg transition-colors whitespace-nowrap">Save Expense</button>
<a href="{{ route('expenses.index') }}" class="h-10 px-5 inline-flex items-center text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors whitespace-nowrap">Cancel</a>
</div>
</form>
</div>
</div>
<div class="mt-8 text-center text-xs text-slate-400 pb-4">© {{ date('Y') }} Nexus ERP Inc. All rights reserved.</div>
</div>
</main>
</body>
</html>
