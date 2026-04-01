<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Users - ERP'])
@include('partials.stitch-design')
<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'users'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">Users</h2>
</div>
<div class="flex items-center gap-2">
<a href="{{ route('users.create') }}" class="st-btn-primary h-10 px-5 inline-flex items-center gap-2 whitespace-nowrap">
<span class="material-symbols-outlined text-[20px]">add</span>
Add user
</a>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Users</h1>
</div>
<p class="text-[10px] font-bold uppercase tracking-widest text-[#586064] lg:text-right shrink-0">Access control · click row to edit</p>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<div class="sm:hidden">
<p class="st-label">Module</p>
<p class="text-xl font-black uppercase tracking-tight text-[#2B3437]">Users</p>
</div>

@if (session('success'))
<div class="border border-[#ABB3B7] bg-white px-4 py-3 text-sm text-[#2B3437]">
{{ session('success') }}
</div>
@endif
@if (session('error'))
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D]">
{{ session('error') }}
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-0 border border-[#ABB3B7] bg-white md:divide-x md:divide-[#ABB3B7]">
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">On this page</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ $users->count() }}</p>
</div>
<div class="p-6 border-b md:border-b-0 border-[#ABB3B7]">
<p class="st-label mb-2">Admin accounts</p>
<p class="text-2xl font-bold font-mono text-[#2B3437] tabular-nums">{{ number_format($adminCount ?? 0) }}</p>
</div>
<div class="p-6 border-2 border-[#5E5E5E] max-md:m-0 -m-px">
<p class="st-label st-label--primary mb-2">Total users</p>
<p class="text-2xl font-black font-mono text-[#5E5E5E] tabular-nums">{{ number_format($users->count()) }}</p>
</div>
</div>

<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[320px]">
<div class="px-5 py-4 border-b border-[#ABB3B7] bg-[#EAEFF1]">
<h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">User ledger</h3>
<p class="text-[11px] text-[#586064] mt-1">Name · email · role · edit or delete from actions</p>
</div>

<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[640px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3">Name</th>
<th class="st-th px-4 py-3">Email</th>
<th class="st-th px-4 py-3 whitespace-nowrap">Role</th>
<th class="st-th px-4 py-3 text-right w-36"></th>
</tr>
</thead>
<tbody>
@forelse($users as $u)
<tr class="st-tr cursor-pointer focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#5E5E5E]"
    data-user-edit-url="{{ route('users.edit', $u) }}" role="link" tabindex="0" aria-label="Edit {{ e($u->name) }}">
<td class="st-td px-4 py-3">
<div class="flex items-center gap-3 min-w-0">
<div class="h-9 w-9 shrink-0 border border-[#ABB3B7] bg-[#EAEFF1] flex items-center justify-center">
<span class="material-symbols-outlined text-[#586064] text-[18px]">person</span>
</div>
<p class="text-sm font-bold text-[#2B3437] truncate">{{ $u->name }}</p>
</div>
</td>
<td class="st-td px-4 py-3 text-sm text-[#586064] truncate max-w-[240px]">{{ $u->email }}</td>
<td class="st-td px-4 py-3">
@if($u->role === 'admin')
<span class="inline-block text-[10px] font-bold uppercase tracking-wide px-2 py-1 border-2 border-[#5E5E5E] text-[#5E5E5E]">Admin</span>
@elseif($u->role === 'manager')
<span class="inline-block text-[10px] font-bold uppercase tracking-wide px-2 py-1 border border-[#ABB3B7] text-[#586064]">Manager</span>
@else
<span class="inline-block text-[10px] font-bold uppercase tracking-wide px-2 py-1 border border-[#ABB3B7] text-[#586064]">Viewer</span>
@endif
</td>
<td class="st-td px-4 py-3 text-right" data-stop-row-nav>
<div class="flex items-center justify-end gap-1 flex-wrap">
<a href="{{ route('users.edit', $u) }}" class="p-2 border border-transparent hover:border-[#ABB3B7] text-[#586064] hover:text-[#2B3437] hover:bg-[#F1F4F6]" title="Edit">
<span class="material-symbols-outlined text-[18px]">edit</span>
</a>
@can('delete', $u)
<form method="POST" action="{{ route('users.destroy', $u) }}" class="inline-flex" data-confirm-delete="{{ e('Delete this user?') }}">
@csrf
@method('DELETE')
<button type="submit" class="p-2 border border-transparent hover:border-[#9F403D] text-[#586064] hover:text-[#9F403D] hover:bg-[#F1F4F6]" title="Delete">
<span class="material-symbols-outlined text-[18px]">delete</span>
</button>
</form>
@endcan
</div>
</td>
</tr>
@empty
<tr>
<td colspan="4" class="px-6 py-14 text-center text-sm text-[#586064] border-b border-[#ABB3B7]">
<p class="font-semibold text-[#2B3437] mb-1">No users</p>
<a href="{{ route('users.create') }}" class="text-[#5E5E5E] font-bold underline underline-offset-2">Add first user</a>
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

<div class="p-4 border-t border-[#ABB3B7] bg-[#F8F9FA]">
<p class="text-xs text-[#586064] uppercase tracking-wide">
@if($users->count() > 0)
Showing <span class="font-bold text-[#2B3437] tabular-nums">{{ $users->count() }}</span> users
@else
No results
@endif
</p>
</div>
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-6 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>
<script>
(function () {
    document.querySelectorAll('tr[data-user-edit-url]').forEach(function (row) {
        var url = row.getAttribute('data-user-edit-url');
        if (!url) return;
        row.addEventListener('click', function (e) {
            if (e.target.closest('[data-stop-row-nav], a, button, form')) return;
            window.location.href = url;
        });
        row.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            if (e.target.closest('[data-stop-row-nav], a, button, input, textarea, select')) return;
            e.preventDefault();
            window.location.href = url;
        });
    });
})();
</script>
</body>
</html>
