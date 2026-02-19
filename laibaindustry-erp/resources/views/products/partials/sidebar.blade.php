<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-20 opacity-0 pointer-events-none transition-opacity duration-300 md:hidden" aria-hidden="true"></div>
<aside id="sidebar" class="fixed md:static inset-y-0 left-0 w-64 bg-white dark:bg-[#1a2632] border-r border-slate-200 dark:border-slate-700 flex flex-col justify-between h-full shrink-0 z-30 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
<div class="flex flex-col h-full">
<div class="h-16 flex items-center px-6 border-b border-slate-100 dark:border-slate-800">
<div class="flex items-center gap-3">
<div class="bg-primary/10 text-primary p-1.5 rounded-lg">
<span class="material-symbols-outlined text-2xl">hexagon</span>
</div>
<div>
<h1 class="text-base font-bold leading-none text-slate-900 dark:text-white">Nexus ERP</h1>
<p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Enterprise Solution</p>
</div>
</div>
</div>
<div class="flex-1 overflow-y-auto py-4 px-3 space-y-1 no-scrollbar">
<div class="px-3 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Main Menu</div>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ ($activeNav ?? '') === 'dashboard' ? 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-blue-400' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary' }} transition-colors" href="{{ route('dashboard', absolute: false) }}">
<span class="material-symbols-outlined text-[22px]">dashboard</span>
<span class="text-sm font-medium">Dashboard</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ ($activeNav ?? '') === 'products' ? 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-blue-400' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary' }} transition-colors" href="{{ route('inventory.dashboard', absolute: false) }}">
<span class="material-symbols-outlined text-[22px]">inventory_2</span>
<span class="text-sm font-medium">Inventory</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ ($activeNav ?? '') === 'sales' ? 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-blue-400' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary' }} transition-colors" href="{{ route('sales.index', absolute: false) }}">
<span class="material-symbols-outlined text-[22px]">shopping_cart</span>
<span class="text-sm font-medium">Orders</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ ($activeNav ?? '') === 'customers' ? 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-blue-400' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary' }} transition-colors" href="{{ route('customers.index', absolute: false) }}">
<span class="material-symbols-outlined text-[22px]">group</span>
<span class="text-sm font-medium">Customers</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ ($activeNav ?? '') === 'receivables' ? 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-blue-400' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary' }} transition-colors" href="{{ route('receivables.index', absolute: false) }}">
<span class="material-symbols-outlined text-[22px]">payments</span>
<span class="text-sm font-medium">Receivables</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary transition-colors group" href="{{ route('products.index', absolute: false) }}">
<span class="material-symbols-outlined text-[22px] group-hover:text-primary">bar_chart</span>
<span class="text-sm font-medium">Reports</span>
</a>
<div class="mt-6 px-3 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">System</div>
@if(in_array(auth()->user()->role ?? '', ['admin', 'manager']))
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ ($activeNav ?? '') === 'users' ? 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-blue-400' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary' }} transition-colors" href="{{ route('users.index', absolute: false) }}">
<span class="material-symbols-outlined text-[22px]">manage_accounts</span>
<span class="text-sm font-medium">Users</span>
</a>
@endif
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary transition-colors group" href="#">
<span class="material-symbols-outlined text-[22px] group-hover:text-primary">settings</span>
<span class="text-sm font-medium">Settings</span>
</a>
</div>
<div class="p-4 border-t border-slate-100 dark:border-slate-800">
<form method="POST" action="{{ route('logout', absolute: false) }}" class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition-colors">
@csrf
<div class="h-9 w-9 rounded-full bg-slate-200 dark:bg-slate-700 bg-cover bg-center border-2 border-white dark:border-slate-600 shadow-sm" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAEFlpSGGEtjXJFRGVokTYO__I__d_7SuNR3lkmM_BQHu_oa0EpS7JWyL_U7kUhobpswzGKWvS54W9s91mr_xuCVO1iqywaCpcOpuOBOsUfxCrEC5n6z5Nywk70Wgm-r0VmjCd7XCD6jg5XYVxVj-MBhD5hIg2je7C9JC4cTjsi0-0ClU5NTO7Xxr1bZ66IkdWjupwQH4dkj6Qvv0JTgZrD-swCniaApQKvCJDzNLL1e4wtfDFCVbY74UDqzmpIOAEKmVRnZU6o_w8');"></div>
<div class="flex-1 min-w-0 text-left">
<p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ auth()->user()->name }}</p>
<p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ ucfirst(auth()->user()->role) }}</p>
</div>
<button type="submit" class="material-symbols-outlined text-slate-400">logout</button>
</form>
</div>
</div>
</aside>
<script>
(function(){var s=document.getElementById('sidebar'),o=document.getElementById('sidebar-overlay');function open(){s?.classList.remove('-translate-x-full');o?.classList.remove('opacity-0','pointer-events-none');document.body.style.overflow='hidden'}function close(){s?.classList.add('-translate-x-full');o?.classList.add('opacity-0','pointer-events-none');document.body.style.overflow=''}document.querySelectorAll('[data-sidebar-toggle]').forEach(function(b){b.addEventListener('click',function(){s?.classList.contains('-translate-x-full')?open():close()})});o?.addEventListener('click',close);document.addEventListener('keydown',function(e){if(e.key==='Escape')close()})})();
</script>
