<!DOCTYPE html>

<html lang="en"><head>
@include('partials.frontend-head', ['title' => 'ERP Main Dashboard'])
</head>
<body class="bg-background-light dark:bg-background-dark text-[#111418] font-display min-h-screen">
<div class="flex h-screen w-full overflow-hidden">
<aside class="flex w-64 flex-col justify-between border-r border-[#e5e7eb] bg-white dark:bg-slate-900 dark:border-slate-800 transition-all duration-300">
<div class="flex flex-col gap-4 p-4">
<div class="flex items-center gap-2 px-2 py-3">
<div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-white">
<span class="material-symbols-outlined text-xl">grid_view</span>
</div>
<div class="flex flex-col">
<h1 class="text-base font-bold leading-tight tracking-tight text-[#111418] dark:text-white">EnterpriseOne</h1>
<p class="text-xs font-medium text-[#637588] dark:text-slate-400">v2.4.0</p>
</div>
</div>
<nav class="flex flex-col gap-2">
<a class="flex items-center gap-3 px-3 py-2 rounded-lg bg-primary/10 text-primary font-medium" href="{{ route('dashboard', absolute: false) }}">
<span class="material-symbols-outlined">dashboard</span>
<span class="text-sm">Dashboard</span>
</a>
<a class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#637588] dark:text-slate-400 hover:bg-[#f3f4f6] dark:hover:bg-slate-800 transition-colors" href="{{ route('currencies.index', absolute: false) }}">
<span class="material-symbols-outlined">payments</span>
<span class="text-sm font-medium">Finance</span>
</a>
<a class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#637588] dark:text-slate-400 hover:bg-[#f3f4f6] dark:hover:bg-slate-800 transition-colors" href="{{ route('customers.index', absolute: false) }}">
<span class="material-symbols-outlined">groups</span>
<span class="text-sm font-medium">HR</span>
</a>
<a class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#637588] dark:text-slate-400 hover:bg-[#f3f4f6] dark:hover:bg-slate-800 transition-colors" href="{{ route('inventory.dashboard', absolute: false) }}">
<span class="material-symbols-outlined">inventory_2</span>
<span class="text-sm font-medium">Inventory</span>
</a>
<a class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#637588] dark:text-slate-400 hover:bg-[#f3f4f6] dark:hover:bg-slate-800 transition-colors" href="{{ route('receivables.index', absolute: false) }}">
<span class="material-symbols-outlined">account_circle</span>
<span class="text-sm font-medium">CRM</span>
</a>
<a class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#637588] dark:text-slate-400 hover:bg-[#f3f4f6] dark:hover:bg-slate-800 transition-colors" href="{{ route('sales.index', absolute: false) }}">
<span class="material-symbols-outlined">shopping_cart</span>
<span class="text-sm font-medium">Sales</span>
</a>
</nav>
</div>
<div class="p-4 border-t border-[#e5e7eb] dark:border-slate-800">
<a class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#637588] dark:text-slate-400 hover:bg-[#f3f4f6] dark:hover:bg-slate-800 transition-colors" href="#">
<span class="material-symbols-outlined">settings</span>
<span class="text-sm font-medium">Settings</span>
</a>
<form method="POST" action="{{ route('logout', absolute: false) }}">
@csrf
<button class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-[#637588] dark:text-slate-400 hover:bg-[#f3f4f6] dark:hover:bg-slate-800 transition-colors text-left" type="submit">
<span class="material-symbols-outlined">logout</span>
<span class="text-sm font-medium">Log Out</span>
</button>
</form>
</div>
</aside>
<main class="flex flex-1 flex-col overflow-y-auto bg-background-light dark:bg-background-dark">
<header class="sticky top-0 z-10 flex h-16 w-full items-center justify-between border-b border-[#e5e7eb] bg-white dark:bg-slate-900 dark:border-slate-800 px-6 backdrop-blur-md bg-opacity-90">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 text-gray-600 dark:text-slate-300">
<span class="material-symbols-outlined">menu</span>
</button>
<h2 class="text-lg font-bold leading-tight text-[#111418] dark:text-white hidden sm:block">Dashboard Overview</h2>
</div>
<div class="flex items-center gap-4 md:gap-6">
<div class="hidden sm:flex relative items-center">
<span class="absolute left-3 text-[#637588] material-symbols-outlined text-[20px]">search</span>
<input class="h-10 w-64 rounded-lg bg-[#f3f4f6] dark:bg-slate-800 border-none pl-10 pr-4 text-sm font-medium text-[#111418] dark:text-white placeholder-[#637588] focus:ring-2 focus:ring-primary/50 transition-all" placeholder="Search orders, invoices..." type="text"/>
</div>
<div class="flex items-center gap-3">
<button class="relative flex h-10 w-10 items-center justify-center rounded-full bg-white dark:bg-slate-800 hover:bg-[#f3f4f6] dark:hover:bg-slate-700 text-[#111418] dark:text-slate-200 border border-[#e5e7eb] dark:border-slate-700 transition-colors">
<span class="material-symbols-outlined text-[20px]">notifications</span>
<span class="absolute top-2 right-2 h-2 w-2 rounded-full bg-red-500 border border-white dark:border-slate-800"></span>
</button>
<div class="flex items-center gap-3 pl-3 border-l border-[#e5e7eb] dark:border-slate-700">
<div class="text-right hidden md:block">
<p class="text-sm font-bold text-[#111418] dark:text-white leading-none">{{ auth()->user()->name }}</p>
<p class="text-xs font-medium text-[#637588] dark:text-slate-400 mt-1">{{ ucfirst(auth()->user()->role) }}</p>
</div>
<button class="h-10 w-10 overflow-hidden rounded-full border border-[#e5e7eb] dark:border-slate-700">
<img alt="Admin Profile" class="h-full w-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBBtim2LarBodVMdbCA3VOZMXjuNcTvxsxi5oEoNyJuUw_23h61XCqTGcNN5FbLso2Dvftk2dBZLfof_4qpublu41LTPMNd2iAx6prT1xrWMPHXErrkBmEa-5tVTaiUmd_fBu67olQ9JahOuhrSvhjyI2wtbQyf7R1eQHy9Vt6E_hEW6jFDfvQEUIG3x41kuLDzlw7ftTWhTF_s60znfGDhUo0R6XBPQzGwaqt9Ti_Q4vxtKEx0j4nqanGIc3AQs662qZu5Ecft9oI"/>
</button>
</div>
</div>
</div>
</header>
<div class="p-6">
<div class="sm:hidden mb-6">
<h2 class="text-2xl font-bold text-[#111418] dark:text-white">Welcome back, {{ auth()->user()->name }}!</h2>
<p class="text-[#637588] dark:text-slate-400">Here's what's happening with your business today.</p>
</div>
<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 mb-8">
<div class="rounded-xl border border-[#e5e7eb] dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm hover:shadow-md transition-shadow">
<div class="flex items-center justify-between mb-4">
<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400">
<span class="material-symbols-outlined">attach_money</span>
</div>
<span class="flex items-center gap-1 text-xs font-bold text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-2 py-1 rounded-full">
<span class="material-symbols-outlined text-sm">trending_up</span>
                                +12%
                            </span>
</div>
<p class="text-sm font-medium text-[#637588] dark:text-slate-400">Total Revenue</p>
<h3 class="text-2xl font-bold text-[#111418] dark:text-white mt-1">$124,500</h3>
</div>
<div class="rounded-xl border border-[#e5e7eb] dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm hover:shadow-md transition-shadow">
<div class="flex items-center justify-between mb-4">
<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-primary dark:bg-blue-900/20 dark:text-blue-400">
<span class="material-symbols-outlined">receipt_long</span>
</div>
</div>
<p class="text-sm font-medium text-[#637588] dark:text-slate-400">Open Invoices</p>
<h3 class="text-2xl font-bold text-[#111418] dark:text-white mt-1">45</h3>
</div>
<div class="rounded-xl border border-[#e5e7eb] dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm hover:shadow-md transition-shadow">
<div class="flex items-center justify-between mb-4">
<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400">
<span class="material-symbols-outlined">person_add</span>
</div>
<span class="flex items-center gap-1 text-xs font-bold text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-2 py-1 rounded-full">
<span class="material-symbols-outlined text-sm">trending_up</span>
                                +5%
                            </span>
</div>
<p class="text-sm font-medium text-[#637588] dark:text-slate-400">New Customers</p>
<h3 class="text-2xl font-bold text-[#111418] dark:text-white mt-1">12</h3>
</div>
<div class="rounded-xl border border-[#e5e7eb] dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm hover:shadow-md transition-shadow hidden xl:block">
<div class="flex items-center justify-between mb-4">
<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-50 text-orange-600 dark:bg-orange-900/20 dark:text-orange-400">
<span class="material-symbols-outlined">account_balance_wallet</span>
</div>
<span class="flex items-center gap-1 text-xs font-bold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 px-2 py-1 rounded-full">
<span class="material-symbols-outlined text-sm">trending_down</span>
                                -2%
                            </span>
</div>
<p class="text-sm font-medium text-[#637588] dark:text-slate-400">Net Profit</p>
<h3 class="text-2xl font-bold text-[#111418] dark:text-white mt-1">$42,300</h3>
</div>
</div>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
<div class="lg:col-span-2 rounded-xl border border-[#e5e7eb] dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm">
<div class="flex items-center justify-between mb-6">
<div>
<h3 class="text-lg font-bold text-[#111418] dark:text-white">Sales Overview</h3>
<p class="text-sm text-[#637588] dark:text-slate-400">Performance over last 6 months</p>
</div>
<div class="flex items-center gap-2">
<span class="text-2xl font-bold text-[#111418] dark:text-white">$45,200</span>
<span class="text-sm font-medium text-green-600 dark:text-green-400 flex items-center bg-green-50 dark:bg-green-900/20 px-2 py-1 rounded">
<span class="material-symbols-outlined text-sm mr-1">arrow_upward</span>
                                    15%
                                </span>
</div>
</div>
<div class="w-full h-72 relative">
<div class="absolute inset-0 flex flex-col justify-between text-xs text-[#637588] dark:text-slate-500">
<div class="flex w-full border-b border-dashed border-[#e5e7eb] dark:border-slate-800 pb-2"><span>$50k</span></div>
<div class="flex w-full border-b border-dashed border-[#e5e7eb] dark:border-slate-800 pb-2"><span>$40k</span></div>
<div class="flex w-full border-b border-dashed border-[#e5e7eb] dark:border-slate-800 pb-2"><span>$30k</span></div>
<div class="flex w-full border-b border-dashed border-[#e5e7eb] dark:border-slate-800 pb-2"><span>$20k</span></div>
<div class="flex w-full border-b border-dashed border-[#e5e7eb] dark:border-slate-800 pb-2"><span>$10k</span></div>
<div class="flex w-full"><span>$0</span></div>
</div>
<svg class="absolute inset-0 h-full w-full pt-6 pb-6" preserveaspectratio="none" viewbox="0 0 100 50">
<defs>
<lineargradient id="chartGradient" x1="0" x2="0" y1="0" y2="1">
<stop offset="0%" stop-color="#137fec" stop-opacity="0.2"></stop>
<stop offset="100%" stop-color="#137fec" stop-opacity="0"></stop>
</lineargradient>
</defs>
<path d="M0 40 Q 10 35 20 25 T 40 30 T 60 15 T 80 20 T 100 5 L 100 50 L 0 50 Z" fill="url(#chartGradient)"></path>
<path d="M0 40 Q 10 35 20 25 T 40 30 T 60 15 T 80 20 T 100 5" fill="none" stroke="#137fec" stroke-width="0.8"></path>
<circle cx="20" cy="25" fill="#fff" r="1.5" stroke="#137fec" stroke-width="0.5"></circle>
<circle cx="40" cy="30" fill="#fff" r="1.5" stroke="#137fec" stroke-width="0.5"></circle>
<circle cx="60" cy="15" fill="#fff" r="1.5" stroke="#137fec" stroke-width="0.5"></circle>
<circle cx="80" cy="20" fill="#fff" r="1.5" stroke="#137fec" stroke-width="0.5"></circle>
</svg>
</div>
<div class="flex justify-between mt-2 px-2 text-xs font-medium text-[#637588] dark:text-slate-400">
<span>Jan</span>
<span>Feb</span>
<span>Mar</span>
<span>Apr</span>
<span>May</span>
<span>Jun</span>
</div>
</div>
<div class="flex flex-col gap-6">
<div class="rounded-xl border border-[#e5e7eb] dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm flex flex-col">
<div class="flex items-center justify-between mb-4">
<h3 class="text-lg font-bold text-[#111418] dark:text-white">Pending Tasks</h3>
<button class="text-primary text-sm font-bold hover:underline">View All</button>
</div>
<div class="flex flex-col gap-1">
<label class="group flex items-start gap-3 py-3 border-b border-[#f3f4f6] dark:border-slate-800 last:border-0 cursor-pointer">
<input class="mt-1 h-5 w-5 rounded border-[#dbe0e6] text-primary focus:ring-offset-0 focus:ring-0 cursor-pointer transition-colors" type="checkbox"/>
<div class="flex flex-col flex-1">
<span class="text-sm font-medium text-[#111418] dark:text-white group-hover:text-primary transition-colors">Review Q3 Report</span>
<span class="text-xs font-bold text-red-500 bg-red-50 dark:bg-red-900/20 px-2 py-0.5 rounded w-fit mt-1">High Priority</span>
</div>
</label>
<label class="group flex items-start gap-3 py-3 border-b border-[#f3f4f6] dark:border-slate-800 last:border-0 cursor-pointer">
<input class="mt-1 h-5 w-5 rounded border-[#dbe0e6] text-primary focus:ring-offset-0 focus:ring-0 cursor-pointer transition-colors" type="checkbox"/>
<div class="flex flex-col flex-1">
<span class="text-sm font-medium text-[#111418] dark:text-white group-hover:text-primary transition-colors">Approve Leave Request</span>
<span class="text-xs font-bold text-orange-500 bg-orange-50 dark:bg-orange-900/20 px-2 py-0.5 rounded w-fit mt-1">Medium Priority</span>
</div>
</label>
<label class="group flex items-start gap-3 py-3 border-b border-[#f3f4f6] dark:border-slate-800 last:border-0 cursor-pointer">
<input class="mt-1 h-5 w-5 rounded border-[#dbe0e6] text-primary focus:ring-offset-0 focus:ring-0 cursor-pointer transition-colors" type="checkbox"/>
<div class="flex flex-col flex-1">
<span class="text-sm font-medium text-[#111418] dark:text-white group-hover:text-primary transition-colors">Stock Check</span>
<span class="text-xs font-bold text-green-500 bg-green-50 dark:bg-green-900/20 px-2 py-0.5 rounded w-fit mt-1">Low Priority</span>
</div>
</label>
</div>
</div>
<div class="rounded-xl border border-[#e5e7eb] dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm flex flex-col flex-1">
<div class="flex items-center justify-between mb-4">
<h3 class="text-lg font-bold text-[#111418] dark:text-white">Recent Activity</h3>
</div>
<div class="relative pl-2">
<div class="absolute left-[19px] top-2 bottom-4 w-[2px] bg-[#f3f4f6] dark:bg-slate-800"></div>
<div class="flex flex-col gap-6">
<div class="relative flex gap-4 items-start">
<div class="z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 border-white dark:border-slate-900 bg-blue-100 text-primary">
<span class="material-symbols-outlined text-[18px]">person_add</span>
</div>
<div class="pt-1">
<p class="text-sm font-medium text-[#111418] dark:text-white">
<span class="font-bold">John Doe</span> added a new vendor
                                            </p>
<p class="text-xs text-[#637588] dark:text-slate-400 mt-0.5">2 hours ago</p>
</div>
</div>
<div class="relative flex gap-4 items-start">
<div class="z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 border-white dark:border-slate-900 bg-green-100 text-green-600">
<span class="material-symbols-outlined text-[18px]">check_circle</span>
</div>
<div class="pt-1">
<p class="text-sm font-medium text-[#111418] dark:text-white">
                                                System backup completed
                                            </p>
<p class="text-xs text-[#637588] dark:text-slate-400 mt-0.5">5 hours ago</p>
</div>
</div>
<div class="relative flex gap-4 items-start">
<div class="z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 border-white dark:border-slate-900 bg-orange-100 text-orange-600">
<span class="material-symbols-outlined text-[18px]">inventory</span>
</div>
<div class="pt-1">
<p class="text-sm font-medium text-[#111418] dark:text-white">
<span class="font-bold">Inventory</span> low for Item #402
                                            </p>
<p class="text-xs text-[#637588] dark:text-slate-400 mt-0.5">Yesterday, 4:00 PM</p>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</main>
</div>
</body></html>
