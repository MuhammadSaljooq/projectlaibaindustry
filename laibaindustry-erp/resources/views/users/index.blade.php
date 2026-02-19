<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Users - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'users'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-[#1a2632] border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<h2 class="text-xl font-bold text-slate-800 dark:text-white hidden sm:block">Users</h2>
</div>
</header>
<div class="flex-1 overflow-y-auto p-6 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-6">
<div class="sm:hidden">
<h2 class="text-2xl font-bold text-slate-800 dark:text-white">Users</h2>
</div>

@if (session('success'))
<div class="rounded-lg border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/20 dark:border-emerald-800 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-400">
{{ session('success') }}
</div>
@endif
@if (session('error'))
<div class="rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-400">
{{ session('error') }}
</div>
@endif

<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col flex-1 min-h-[400px]">
<div class="p-5 border-b border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-3">
<h3 class="text-base font-semibold text-slate-800 dark:text-white">Users</h3>
<a class="h-10 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg inline-flex items-center justify-center gap-2 whitespace-nowrap transition-colors shadow-sm hover:shadow active:scale-[0.98] shrink-0" href="{{ route('users.create') }}">
<span class="material-symbols-outlined text-[20px] shrink-0">add</span>
<span>Add User</span>
</a>
</div>

<div class="overflow-x-auto w-full -mx-4 sm:mx-0">
<table class="w-full text-left border-collapse min-w-[500px]">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-[250px]">Name</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Role</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-200 dark:divide-slate-700">
@forelse($users as $u)
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="h-10 w-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-slate-400">person</span>
</div>
<div>
<p class="text-sm font-semibold text-slate-900 dark:text-white group-hover:text-primary transition-colors">{{ $u->name }}</p>
</div>
</div>
</td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $u->email }}</td>
<td class="px-6 py-4">
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $u->role === 'admin' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400' : ($u->role === 'manager' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300') }}">{{ ucfirst($u->role) }}</span>
</td>
<td class="px-6 py-4 text-right">
<div class="flex items-center justify-end gap-2 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
<a class="p-1.5 text-slate-400 hover:text-primary hover:bg-slate-100 dark:hover:bg-slate-700 rounded-md transition-colors" href="{{ route('users.edit', $u) }}" title="Edit user">
<span class="material-symbols-outlined text-[20px]">edit</span>
</a>
@can('delete', $u)
<form method="POST" action="{{ route('users.destroy', $u) }}" class="inline-flex" onsubmit="return confirm('Are you sure you want to delete this user?');">
@csrf
@method('DELETE')
<button type="submit" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors" title="Delete user">
<span class="material-symbols-outlined text-[20px]">delete</span>
</button>
</form>
@endcan
</div>
</td>
</tr>
@empty
<tr>
<td colspan="4" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
No users yet.
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

<div class="p-4 border-t border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-4">
<p class="text-sm text-slate-500 dark:text-slate-400">
@if($users->total() > 0)
Showing <span class="font-medium text-slate-900 dark:text-white">{{ $users->firstItem() }}</span> to <span class="font-medium text-slate-900 dark:text-white">{{ $users->lastItem() }}</span> of <span class="font-medium text-slate-900 dark:text-white">{{ $users->total() }}</span> results
@else
No results
@endif
</p>
@if($users->hasPages())
<nav class="flex items-center gap-1" aria-label="Pagination">
@if (!$users->onFirstPage())
<a class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors" href="{{ $users->previousPageUrl() }}"><span class="material-symbols-outlined text-[20px]">chevron_left</span></a>
@endif
@foreach ($users->getUrlRange(max(1, $users->currentPage() - 2), min($users->lastPage(), $users->currentPage() + 2)) ?: [1 => $users->url(1)] as $page => $url)
@if ($page == $users->currentPage())
<span class="px-3 py-1.5 text-sm font-medium rounded-lg bg-primary text-white">{{ $page }}</span>
@else
<a class="px-3 py-1.5 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" href="{{ $url }}">{{ $page }}</a>
@endif
@endforeach
@if ($users->hasMorePages())
<a class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 transition-colors" href="{{ $users->nextPageUrl() }}"><span class="material-symbols-outlined text-[20px]">chevron_right</span></a>
@endif
</nav>
@endif
</div>
</div>
</div>
<div class="mt-8 text-center text-xs text-slate-400 pb-4">© {{ date('Y') }} Nexus ERP Inc. All rights reserved.</div>
</div>
</main>
</body>
</html>
