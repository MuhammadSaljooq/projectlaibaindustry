<!DOCTYPE html>

<html lang="en"><head>
@include('partials.frontend-head', ['title' => 'Inventory Management - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
<aside class="w-64 bg-white dark:bg-[#1a2632] border-r border-slate-200 dark:border-slate-700 flex flex-col justify-between h-full shrink-0 z-20 transition-all duration-300">
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
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary transition-colors group" href="{{ route('dashboard', absolute: false) }}">
<span class="material-symbols-outlined text-[22px] group-hover:text-primary">dashboard</span>
<span class="text-sm font-medium">Dashboard</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary/10 text-primary dark:bg-primary/20 dark:text-blue-400 transition-colors" href="{{ route('inventory.dashboard', absolute: false) }}">
<span class="material-symbols-outlined text-[22px]">inventory_2</span>
<span class="text-sm font-bold">Inventory</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary transition-colors group" href="{{ route('sales.index', absolute: false) }}">
<span class="material-symbols-outlined text-[22px] group-hover:text-primary">shopping_cart</span>
<span class="text-sm font-medium">Orders</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary transition-colors group" href="{{ route('customers.index', absolute: false) }}">
<span class="material-symbols-outlined text-[22px] group-hover:text-primary">group</span>
<span class="text-sm font-medium">Customers</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-primary transition-colors group" href="{{ route('products.index', absolute: false) }}">
<span class="material-symbols-outlined text-[22px] group-hover:text-primary">bar_chart</span>
<span class="text-sm font-medium">Reports</span>
</a>
<div class="mt-6 px-3 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">System</div>
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
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-[#1a2632] border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
<span class="material-symbols-outlined">menu</span>
</button>
<h2 class="text-xl font-bold text-slate-800 dark:text-white hidden sm:block">Inventory Management</h2>
</div>
<div class="flex items-center gap-4">
<div class="relative hidden sm:block">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 material-symbols-outlined text-[20px]">search</span>
<input class="h-9 pl-10 pr-4 text-sm bg-slate-50 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary/50 w-64 placeholder-slate-400 text-slate-700 dark:text-slate-200 transition-all" placeholder="Global search..." type="text"/>
</div>
<button class="p-2 text-slate-500 hover:text-primary hover:bg-primary/5 rounded-full relative transition-colors">
<span class="material-symbols-outlined">notifications</span>
<span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border border-white dark:border-[#1a2632]"></span>
</button>
</div>
</header>
<div class="flex-1 overflow-y-auto p-6 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-6">
<div class="sm:hidden">
<h2 class="text-2xl font-bold text-slate-800 dark:text-white">Inventory Management</h2>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm hover:shadow-md transition-shadow">
<div class="flex justify-between items-start mb-4">
<div>
<p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Items</p>
<h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-1">1,245</h3>
</div>
<div class="p-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg">
<span class="material-symbols-outlined">inventory_2</span>
</div>
</div>
<div class="flex items-center gap-2">
<span class="text-xs font-semibold text-emerald-600 bg-emerald-50 dark:bg-emerald-900/30 dark:text-emerald-400 px-2 py-0.5 rounded-full flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">trending_up</span> 5%
                            </span>
<span class="text-xs text-slate-500 dark:text-slate-400">vs last month</span>
</div>
</div>
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm hover:shadow-md transition-shadow">
<div class="flex justify-between items-start mb-4">
<div>
<p class="text-sm font-medium text-slate-500 dark:text-slate-400">Low Stock Alerts</p>
<h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-1">12</h3>
</div>
<div class="p-2 bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 rounded-lg">
<span class="material-symbols-outlined">warning</span>
</div>
</div>
<div class="flex items-center gap-2">
<span class="text-xs font-semibold text-orange-600 bg-orange-50 dark:bg-orange-900/30 dark:text-orange-400 px-2 py-0.5 rounded-full flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">priority_high</span> 2 Items
                            </span>
<span class="text-xs text-slate-500 dark:text-slate-400">critical level</span>
</div>
</div>
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm hover:shadow-md transition-shadow">
<div class="flex justify-between items-start mb-4">
<div>
<p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Inventory Value</p>
<h3 class="text-3xl font-bold text-slate-900 dark:text-white mt-1">$452,000</h3>
</div>
<div class="p-2 bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 rounded-lg">
<span class="material-symbols-outlined">attach_money</span>
</div>
</div>
<div class="flex items-center gap-2">
<span class="text-xs font-semibold text-rose-600 bg-rose-50 dark:bg-rose-900/30 dark:text-rose-400 px-2 py-0.5 rounded-full flex items-center gap-1">
<span class="material-symbols-outlined text-[14px]">trending_down</span> 1%
                            </span>
<span class="text-xs text-slate-500 dark:text-slate-400">vs last month</span>
</div>
</div>
</div>
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col flex-1 min-h-[500px]">
<div class="p-5 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
<div class="flex flex-col sm:flex-row gap-3 flex-1">
<div class="relative group">
<span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 material-symbols-outlined text-[20px] group-focus-within:text-primary transition-colors">search</span>
<input class="h-10 pl-10 pr-4 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary w-full sm:w-64 placeholder-slate-400 text-slate-700 dark:text-slate-200 transition-all outline-none" placeholder="Search inventory..." type="text"/>
</div>
<div class="relative">
<select class="h-10 pl-3 pr-10 text-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-slate-700 dark:text-slate-200 outline-none appearance-none cursor-pointer min-w-[160px]">
<option disabled="" selected="" value="">Filter by Category</option>
<option value="all">All Categories</option>
<option value="electronics">Electronics</option>
<option value="furniture">Furniture</option>
<option value="accessories">Accessories</option>
</select>
<span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 material-symbols-outlined text-[20px] pointer-events-none">expand_more</span>
</div>
</div>
<button class="h-10 px-4 bg-primary hover:bg-blue-600 text-white text-sm font-bold rounded-lg flex items-center justify-center gap-2 transition-colors shadow-sm hover:shadow active:scale-95">
<span class="material-symbols-outlined text-[20px]">add</span>
<span>Add New Item</span>
</button>
</div>
<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-[350px]">Item Name</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">SKU</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Category</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Stock Level</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Unit Price</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">Status</th>
<th class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-200 dark:divide-slate-700">
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
<td class="px-6 py-4"><div class="flex items-center gap-3"><div class="h-10 w-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center overflow-hidden shrink-0"><img alt="Office chair thumbnail" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDjdHoExO_yYiEkj9AjXp5-NrJTbgWuOyIF4nDyDZb5yLMCT5dAK37aDeoPCFeofin1FrcQfUrF6WeC96zHwNWqcZ8J7vOxMTpyCdU3LNcfrCpcatVONnro4mYM1ZqisWxXNPcwmtqMkPGJMz_FCyXtcvapjAYQVYHcAGdwmGDvbUrQY7lB0hbzKKInrBjTgvgmgpVVjVrOke6ehgLbp2yf2zGcl5wnj_1BuRiZmAQQKTq5q1NqRj9L2pxJwgwJQ5PJbn5Hheypk_g"/></div><div><p class="text-sm font-semibold text-slate-900 dark:text-white group-hover:text-primary transition-colors">Ergonomic Office Chair</p><p class="text-xs text-slate-500 dark:text-slate-400">Comfort Series</p></div></div></td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 font-medium">OFF-001</td>
<td class="px-6 py-4"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300">Furniture</span></td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 font-mono text-right">45</td>
<td class="px-6 py-4 text-sm text-slate-900 dark:text-white font-bold font-mono text-right">$299.00</td>
<td class="px-6 py-4 text-center"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>In Stock</span></td>
<td class="px-6 py-4 text-right"><div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity"><button class="p-1.5 text-slate-400 hover:text-primary hover:bg-slate-100 dark:hover:bg-slate-700 rounded-md transition-colors"><span class="material-symbols-outlined text-[20px]">edit</span></button><button class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"><span class="material-symbols-outlined text-[20px]">delete</span></button></div></td>
</tr>
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
<td class="px-6 py-4"><div class="flex items-center gap-3"><div class="h-10 w-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center overflow-hidden shrink-0"><img alt="Wireless mouse thumbnail" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBGPnhA--rx3bJK3QUcBknxQzXw07vfx1mmzLQxKQhj4V9EZNTl0AFRkVi_J_Sxl3qFQrtn329MK4NZvTU6b-rK0xdkaJ-OxRAxlCyJWCOdnv3NpNkYhppcY65lpM7eiHDVBrqVOoO3McPQsSB8XtSvA_jfegcNO9KHVrC1UnfIErRd1TOAqwSHhqkoCTeLIvlCLy7XefPhDHQHT2e6jPXycz1z7gL0osOBhCDGQeYTJzwnt-6LWnGu3sEqvAX1sBUMm_cRRe1ngpM"/></div><div><p class="text-sm font-semibold text-slate-900 dark:text-white group-hover:text-primary transition-colors">Wireless Mouse</p><p class="text-xs text-slate-500 dark:text-slate-400">Tech Pro</p></div></div></td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 font-medium">TEC-204</td>
<td class="px-6 py-4"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">Electronics</span></td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 font-mono text-right">3</td>
<td class="px-6 py-4 text-sm text-slate-900 dark:text-white font-bold font-mono text-right">$25.00</td>
<td class="px-6 py-4 text-center"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400 border border-orange-200 dark:border-orange-800"><span class="w-1.5 h-1.5 rounded-full bg-orange-500 mr-1.5"></span>Low Stock</span></td>
<td class="px-6 py-4 text-right"><div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity"><button class="p-1.5 text-slate-400 hover:text-primary hover:bg-slate-100 dark:hover:bg-slate-700 rounded-md transition-colors"><span class="material-symbols-outlined text-[20px]">edit</span></button><button class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"><span class="material-symbols-outlined text-[20px]">delete</span></button></div></td>
</tr>
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
<td class="px-6 py-4"><div class="flex items-center gap-3"><div class="h-10 w-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center overflow-hidden shrink-0"><img alt="Mechanical keyboard thumbnail" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDXQh28Hb52IzNH0o573g53h6AFxW69iqoFIhMOIqpeKL65T1ge_o0PcadZTvs-29KJ74QICZ1armxYphtOFEZETM2YF-rYCUN09plAIIKvoC7KlDql2vdOLlZla3CpqX33SmSse0YV9bQBSmaYoS1oalamTaKaUlSwZUOjw2WP6TmEL7tar1UH4aiMm5Iq-SqJDYxcDrAaO4vabzzO41u-dH5WgNP3eGVUGkr9ufXew5mtxO30pxcNNAcdaF18uSXhaIO1YSeM-4g"/></div><div><p class="text-sm font-semibold text-slate-900 dark:text-white group-hover:text-primary transition-colors">Mechanical Keyboard</p><p class="text-xs text-slate-500 dark:text-slate-400">RGB Backlit</p></div></div></td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 font-medium">TEC-105</td>
<td class="px-6 py-4"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">Electronics</span></td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 font-mono text-right">120</td>
<td class="px-6 py-4 text-sm text-slate-900 dark:text-white font-bold font-mono text-right">$89.00</td>
<td class="px-6 py-4 text-center"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>In Stock</span></td>
<td class="px-6 py-4 text-right"><div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity"><button class="p-1.5 text-slate-400 hover:text-primary hover:bg-slate-100 dark:hover:bg-slate-700 rounded-md transition-colors"><span class="material-symbols-outlined text-[20px]">edit</span></button><button class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"><span class="material-symbols-outlined text-[20px]">delete</span></button></div></td>
</tr>
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
<td class="px-6 py-4"><div class="flex items-center gap-3"><div class="h-10 w-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center overflow-hidden shrink-0"><img alt="Standing Desk thumbnail" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDVOt2VB87TgZBfwuTtgePIKh6QWdojlP5uNpW-Usm7Ja4-LldTigsuINKqpV_0P9_HF0aSrSv60mGzIRsAz7HAi1zATxaVIOBocrNejZkEnFDfoP9AxqVmZwfZL-CusSKpDssbdVnDeVI021rsBh6kJmIam7WAyPATFPWJ8c2-oFJ2kFB7zqE-0GobAFP-27TiEOeuONuxUFpIpX1u3sIsITP3bO1WwbT27PUOcvWsSeUHkDVa2elWElFH9Qsmx7mJ0S1gyiKZmME"/></div><div><p class="text-sm font-semibold text-slate-900 dark:text-white group-hover:text-primary transition-colors">Standing Desk</p><p class="text-xs text-slate-500 dark:text-slate-400">Adjustable Height</p></div></div></td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 font-medium">OFF-002</td>
<td class="px-6 py-4"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300">Furniture</span></td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 font-mono text-right">0</td>
<td class="px-6 py-4 text-sm text-slate-900 dark:text-white font-bold font-mono text-right">$450.00</td>
<td class="px-6 py-4 text-center"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-400 border border-slate-300 dark:border-slate-600"><span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-1.5"></span>Out of Stock</span></td>
<td class="px-6 py-4 text-right"><div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity"><button class="p-1.5 text-slate-400 hover:text-primary hover:bg-slate-100 dark:hover:bg-slate-700 rounded-md transition-colors"><span class="material-symbols-outlined text-[20px]">edit</span></button><button class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"><span class="material-symbols-outlined text-[20px]">delete</span></button></div></td>
</tr>
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
<td class="px-6 py-4"><div class="flex items-center gap-3"><div class="h-10 w-10 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center overflow-hidden shrink-0"><img alt="USB-C Hub thumbnail" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAtG-cnetpdMmF1q46U-BtHKG99W9nXUJmexjxzK3LDMFhK9ihpiXHz0KCLe7WqRXc_paY_4oHYdsDTp3WUDKoe90SjG71QkBh18EV7uh6CHcUbvsBAZLF4RBRClD0WO5vDHGRczNZL4fCVVIJNKEU6fFqvEcTGdNOVT2l743EJSrqrKLgYMJGkdlsMAVrPWYS2LfkEe3qhs4E4vkfVPgeeX8NpD_cfd80c8LBH8gREcTtTI3TcCodvV_U1IlSPlDcPjRlbToGfJA4"/></div><div><p class="text-sm font-semibold text-slate-900 dark:text-white group-hover:text-primary transition-colors">USB-C Hub</p><p class="text-xs text-slate-500 dark:text-slate-400">7-in-1 Adapter</p></div></div></td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 font-medium">ACC-405</td>
<td class="px-6 py-4"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">Accessories</span></td>
<td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 font-mono text-right">15</td>
<td class="px-6 py-4 text-sm text-slate-900 dark:text-white font-bold font-mono text-right">$45.00</td>
<td class="px-6 py-4 text-center"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400 border border-orange-200 dark:border-orange-800"><span class="w-1.5 h-1.5 rounded-full bg-orange-500 mr-1.5"></span>Low Stock</span></td>
<td class="px-6 py-4 text-right"><div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity"><button class="p-1.5 text-slate-400 hover:text-primary hover:bg-slate-100 dark:hover:bg-slate-700 rounded-md transition-colors"><span class="material-symbols-outlined text-[20px]">edit</span></button><button class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"><span class="material-symbols-outlined text-[20px]">delete</span></button></div></td>
</tr>
</tbody>
</table>
</div>
<div class="p-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
<p class="text-sm text-slate-500 dark:text-slate-400">Showing <span class="font-medium text-slate-900 dark:text-white">1</span> to <span class="font-medium text-slate-900 dark:text-white">5</span> of <span class="font-medium text-slate-900 dark:text-white">1,245</span> results</p>
<nav class="flex items-center gap-1">
<button class="p-1.5 rounded hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 disabled:opacity-50"><span class="material-symbols-outlined text-[20px]">chevron_left</span></button>
<button class="px-3 py-1 text-sm font-medium rounded bg-primary text-white">1</button>
<button class="px-3 py-1 text-sm font-medium rounded text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">2</button>
<button class="px-3 py-1 text-sm font-medium rounded text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">3</button>
<span class="px-2 text-slate-400">...</span>
<button class="px-3 py-1 text-sm font-medium rounded text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">12</button>
<button class="p-1.5 rounded hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400"><span class="material-symbols-outlined text-[20px]">chevron_right</span></button>
</nav>
</div>
</div>
</div>
<div class="mt-8 text-center text-xs text-slate-400 pb-4">© 2023 Nexus ERP Inc. All rights reserved.</div>
</div>
</main>
</body></html>
