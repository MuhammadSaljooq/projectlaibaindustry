<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Edit Product - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'products'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-[#1a2632] border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu"><span class="material-symbols-outlined">menu</span></button>
<h2 class="text-xl font-bold text-slate-800 dark:text-white">Edit Product</h2>
</div>
</header>
<div class="flex-1 overflow-y-auto p-6">
<div class="max-w-2xl mx-auto">
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
<form method="POST" action="{{ route('products.update', $product) }}">
@csrf
@method('PUT')
@include('products.partials.form')
<div class="flex flex-wrap gap-3 mt-6">
<button type="submit" class="h-10 px-5 bg-primary hover:bg-blue-600 text-white text-sm font-bold rounded-lg transition-colors whitespace-nowrap">Update Product</button>
<a href="{{ route('inventory.dashboard') }}" class="h-10 px-5 inline-flex items-center text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors whitespace-nowrap">Cancel</a>
</div>
</form>
</div>
</div>
</div>
</main>
</body>
</html>
