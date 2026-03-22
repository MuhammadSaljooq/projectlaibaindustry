<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Users - Laiba Safety'])
<style>
body { background-color: #FFFFFF; color: #2B3437; font-family: 'Inter', sans-serif; color-scheme: light; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #2B3437; color: #FFFFFF; }
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'users'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#FFFFFF;">

<header class="h-14 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 rounded-none" style="color:#5E5E5E;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
</div>
<div class="flex items-center gap-3">
@can('create', App\Models\User::class)
<a href="{{ route('users.create') }}" class="h-9 px-5 text-[11px] font-bold uppercase flex items-center gap-2 active:scale-[0.98] transition-all" style="background:#5E5E5E;color:#F8F8F8;border-radius:0;letter-spacing:0.05em;">
<span class="material-symbols-outlined" style="font-size:14px;">add</span>
NEW USER
</a>
@endcan
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-[1400px] mx-auto flex flex-col" style="gap:3rem;">

<div>
<div class="flex items-end justify-between" style="padding-bottom:0.75rem;border-bottom:2px solid #5E5E5E;">
<h2 class="font-bold" style="font-size:1.5rem;letter-spacing:-0.02em;color:#2B3437;">Users</h2>
<span class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Section USR-01</span>
</div>
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-top:0.75rem;">Access &amp; roles</p>
</div>

@if (session('success'))
<div style="border:1px solid #D3D8DE;padding:0.75rem 1.25rem;" class="text-sm font-bold flex items-center gap-3">
<span class="material-symbols-outlined" style="color:#5E5E5E;font-size:20px;">check_circle</span>
<span>{{ session('success') }}</span>
</div>
@endif
@if (session('error'))
<div style="border:1px solid #9F403D;padding:0.75rem 1.25rem;background:#FFFFFF;" class="text-sm font-bold flex items-center gap-3">
<span class="material-symbols-outlined" style="color:#9F403D;font-size:20px;">error</span>
<span style="color:#9F403D;">{{ session('error') }}</span>
</div>
@endif

<div style="border:1px solid #D3D8DE;">
<div style="padding:1rem 1.5rem;border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-sm font-bold" style="color:#2B3437;">Directory</p>
<p class="text-xs font-bold mt-0.5" style="color:#5E5E5E;">System accounts and permissions.</p>
</div>

<div class="overflow-x-auto">
<table class="w-full text-left border-collapse" style="min-width:640px;">
<thead>
<tr style="background:#F8F9FA;border-bottom:1px solid #D3D8DE;">
<th class="px-6 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Name</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Email</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Role</th>
<th class="px-6 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;">Actions</th>
</tr>
</thead>
<tbody>
@forelse($users as $u)
<tr class="transition-colors" style="border-top:1px solid #EAECEE;" onmouseenter="this.style.background='#F8F9FA'" onmouseleave="this.style.background='transparent'">
<td class="px-6 py-4">
<div class="flex items-center gap-3">
<div class="h-10 w-10 flex items-center justify-center shrink-0" style="border:1px solid #D3D8DE;background:#F8F9FA;">
<span class="material-symbols-outlined" style="font-size:20px;color:#5E5E5E;">person</span>
</div>
<div>
<p class="text-sm font-bold" style="color:#2B3437;">{{ $u->name }}</p>
</div>
</div>
</td>
<td class="px-6 py-4 text-sm font-bold" style="color:#5E5E5E;">{{ $u->email }}</td>
<td class="px-6 py-4">
@php
$roleStyle = match($u->role) {
    'admin' => 'border:1px solid #5E5E5E;background:#F8F9FA;color:#2B3437;',
    'manager' => 'border:1px solid #D3D8DE;color:#5E5E5E;',
    default => 'border:1px solid #D3D8DE;color:#5E5E5E;background:#FFFFFF;',
};
@endphp
<span class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;{{ $roleStyle }}">{{ ucfirst($u->role) }}</span>
</td>
<td class="px-6 py-4 text-right">
<div class="inline-flex items-center justify-end gap-2 flex-wrap">
<a href="{{ route('users.edit', $u) }}" class="inline-flex items-center px-3 py-1.5 text-[11px] font-bold uppercase transition-all" style="color:#2B3437;border:1px solid #5E5E5E;letter-spacing:0.05em;" onmouseenter="this.style.background='#F8F9FA'" onmouseleave="this.style.background='transparent'" title="Edit user">Edit</a>
@can('delete', $u)
<form method="POST" action="{{ route('users.destroy', $u) }}" class="inline-flex" onsubmit="return confirm('Delete this user?');">
@csrf
@method('DELETE')
<button type="submit" class="inline-flex items-center px-3 py-1.5 text-[11px] font-bold uppercase transition-all" style="color:#9F403D;border:1px solid #9F403D;letter-spacing:0.05em;background:transparent;" onmouseenter="this.style.background='#F8F9FA'" onmouseleave="this.style.background='transparent'" title="Delete user">Delete</button>
</form>
@endcan
</div>
</td>
</tr>
@empty
<tr>
<td colspan="4" class="px-6 py-16 text-center">
<p class="text-sm font-bold" style="color:#5E5E5E;">No users yet.</p>
</td>
</tr>
@endforelse
</tbody>
</table>
</div>

@if($users->hasPages())
<div class="flex items-center justify-between px-6 py-4" style="border-top:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-xs font-bold" style="color:#5E5E5E;">
Showing <span style="color:#2B3437;">{{ $users->firstItem() }}</span>–<span style="color:#2B3437;">{{ $users->lastItem() }}</span> of <span style="color:#2B3437;">{{ $users->total() }}</span>
</p>
<nav class="flex items-center gap-1" aria-label="Pagination">
@if (!$users->onFirstPage())
<a class="p-1.5 transition-colors" style="color:#5E5E5E;" href="{{ $users->previousPageUrl() }}" onmouseenter="this.style.background='#EAECEE'" onmouseleave="this.style.background='transparent'"><span class="material-symbols-outlined" style="font-size:18px;">chevron_left</span></a>
@endif
@foreach ($users->getUrlRange(max(1, $users->currentPage() - 2), min($users->lastPage(), $users->currentPage() + 2)) ?: [1 => $users->url(1)] as $page => $url)
@if ($page == $users->currentPage())
<span class="w-8 h-8 flex items-center justify-center text-xs font-bold" style="background:#5E5E5E;color:#F8F8F8;">{{ $page }}</span>
@else
<a class="w-8 h-8 flex items-center justify-center text-xs font-bold transition-colors" style="color:#5E5E5E;" href="{{ $url }}" onmouseenter="this.style.background='#EAECEE'" onmouseleave="this.style.background='transparent'">{{ $page }}</a>
@endif
@endforeach
@if ($users->hasMorePages())
<a class="p-1.5 transition-colors" style="color:#5E5E5E;" href="{{ $users->nextPageUrl() }}" onmouseenter="this.style.background='#EAECEE'" onmouseleave="this.style.background='transparent'"><span class="material-symbols-outlined" style="font-size:18px;">chevron_right</span></a>
@endif
</nav>
</div>
@else
<div class="px-6 py-4" style="border-top:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-xs font-bold" style="color:#5E5E5E;">
@if($users->total() > 0)
Showing all <span style="color:#2B3437;">{{ $users->total() }}</span> results
@else
No results
@endif
</p>
</div>
@endif
</div>

<div class="text-center text-[10px] uppercase font-bold pb-4" style="letter-spacing:0.05em;color:#5E5E5E;">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</div>
</div>
</div>
</main>
</body>
</html>
