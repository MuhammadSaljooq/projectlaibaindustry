<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-20 opacity-0 pointer-events-none transition-opacity duration-200 md:hidden" aria-hidden="true"></div>
<aside id="sidebar" class="fixed md:static inset-y-0 left-0 w-64 flex flex-col h-full shrink-0 z-30 transform -translate-x-full md:translate-x-0 transition-transform duration-200 ease-in-out overflow-y-auto no-scrollbar bg-white border-r border-[#ABB3B7] text-[#2B3437]">
<div class="flex flex-col h-full font-[Inter,system-ui,sans-serif]">

{{-- Brand --}}
<div class="px-4 pt-5 pb-4 border-b border-[#ABB3B7]">
<div class="flex items-center justify-between gap-2">
<div>
<h1 class="text-[#2B3437] font-black uppercase tracking-tighter text-xl leading-tight whitespace-nowrap">Laiba Safety</h1>
<p class="mt-0.5 text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">Admin Console</p>
</div>
<img src="/images/laiba-logo-icon.png" alt="Laiba Industry logo" class="h-16 w-auto object-contain shrink-0">
</div>
</div>

{{-- Nav Links --}}
<div class="flex-1 flex flex-col gap-0.5 px-2 py-3 overflow-y-auto no-scrollbar">

@php
$navItems = [
    ['key' => 'dashboard',    'icon' => 'dashboard',              'label' => 'Dashboard',   'route' => route('dashboard', absolute: false)],
    ['key' => 'products',     'icon' => 'inventory_2',            'label' => 'Inventory',   'route' => route('inventory.dashboard', absolute: false)],
    ['key' => 'customers',    'icon' => 'group',                  'label' => 'Customers',   'route' => route('customers.index', absolute: false)],
    ['key' => 'suppliers',    'icon' => 'storefront',             'label' => 'Vendors',     'route' => route('suppliers.index', absolute: false)],
    ['key' => 'sales',        'icon' => 'payments',               'label' => 'Sales',       'route' => route('sales.index', absolute: false)],
    ['key' => 'receivables',  'icon' => 'account_balance_wallet', 'label' => 'Receivable',  'route' => route('receivables.index', absolute: false)],
    ['key' => 'purchases',    'icon' => 'shopping_cart',          'label' => 'Purchases',   'route' => route('purchases.index', absolute: false)],
    ['key' => 'payables',     'icon' => 'account_balance',        'label' => 'Payables',    'route' => route('payables.index', absolute: false)],
    ['key' => 'international_purchases', 'icon' => 'flight',      'label' => 'International purchases', 'route' => route('international-purchases.index', absolute: false)],
    ['key' => 'international_payables', 'icon' => 'public',       'label' => 'International payables', 'route' => route('international-payables.index', absolute: false)],
    ['key' => 'expenses',     'icon' => 'receipt_long',           'label' => 'Expenses',    'route' => route('expenses.index', absolute: false)],
    ['key' => 'bank_statement', 'icon' => 'description',          'label' => 'Bank statement', 'route' => route('bank-statement.index', absolute: false)],
    ['key' => 'quotations',   'icon' => 'request_quote',          'label' => 'Quotations',  'route' => route('quotations.index', absolute: false)],
    ['key' => 'vat',          'icon' => 'percent',                'label' => 'VAT',         'route' => route('vat.index', absolute: false)],
];
$active = $activeNav ?? '';
@endphp

@foreach($navItems as $item)
@if($active === $item['key'])
<a class="flex items-center gap-3 px-3 py-2.5 text-[#2B3437] font-bold bg-[#EAEFF1] border-l-2 border-[#5E5E5E] rounded-none transition-colors" href="{{ $item['route'] }}">
<span class="material-symbols-outlined text-[20px] text-[#5E5E5E]">{{ $item['icon'] }}</span>
<span class="text-[11px] font-bold uppercase tracking-tight">{{ $item['label'] }}</span>
</a>
@else
<a class="flex items-center gap-3 px-3 py-2.5 font-medium text-[#586064] border-l-2 border-transparent rounded-none hover:bg-[#F1F4F6] hover:text-[#2B3437] transition-colors" href="{{ $item['route'] }}">
<span class="material-symbols-outlined text-[20px]">{{ $item['icon'] }}</span>
<span class="text-sm tracking-tight">{{ $item['label'] }}</span>
</a>
@endif
@endforeach

<div class="my-4 border-t border-[#ABB3B7]"></div>

{{-- System Section --}}
@if(in_array(auth()->user()->role ?? '', ['admin', 'manager']))
@if($active === 'users')
<a class="flex items-center gap-3 px-3 py-2.5 text-[#2B3437] font-bold bg-[#EAEFF1] border-l-2 border-[#5E5E5E] rounded-none transition-colors" href="{{ route('users.index', absolute: false) }}">
<span class="material-symbols-outlined text-[20px] text-[#5E5E5E]">person</span>
<span class="text-[11px] font-bold uppercase tracking-tight">Users</span>
</a>
@else
<a class="flex items-center gap-3 px-3 py-2.5 font-medium text-[#586064] border-l-2 border-transparent rounded-none hover:bg-[#F1F4F6] hover:text-[#2B3437] transition-colors" href="{{ route('users.index', absolute: false) }}">
<span class="material-symbols-outlined text-[20px]">person</span>
<span class="text-sm tracking-tight">Users</span>
</a>
@endif
@endif

@if($active === 'settings')
<a class="flex items-center gap-3 px-3 py-2.5 text-[#2B3437] font-bold bg-[#EAEFF1] border-l-2 border-[#5E5E5E] rounded-none transition-colors" href="{{ route('settings.index', absolute: false) }}">
<span class="material-symbols-outlined text-[20px] text-[#5E5E5E]">settings</span>
<span class="text-[11px] font-bold uppercase tracking-tight">Settings</span>
</a>
@else
<a class="flex items-center gap-3 px-3 py-2.5 font-medium text-[#586064] border-l-2 border-transparent rounded-none hover:bg-[#F1F4F6] hover:text-[#2B3437] transition-colors" href="{{ route('settings.index', absolute: false) }}">
<span class="material-symbols-outlined text-[20px]">settings</span>
<span class="text-sm tracking-tight">Settings</span>
</a>
@endif

</div>

{{-- User Profile Footer --}}
<div class="mt-auto p-3 border-t border-[#ABB3B7] bg-[#F8F9FA]">
<form method="POST" action="{{ route('logout', absolute: false) }}" class="flex items-center gap-3 p-2 border border-[#ABB3B7] bg-white">
@csrf
<div class="shrink-0 w-10 h-10 border border-[#ABB3B7] bg-[#EAEFF1] flex items-center justify-center text-xs font-bold text-[#586064]">
{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
</div>
<div class="flex-1 min-w-0 text-left">
<p class="text-sm font-bold text-[#2B3437] truncate">{{ auth()->user()->name }}</p>
<p class="truncate text-[10px] font-bold uppercase tracking-wider text-[#586064]">{{ ucfirst(auth()->user()->role) }}</p>
</div>
<button type="submit" class="material-symbols-outlined shrink-0 text-[#586064] hover:text-[#2B3437] text-[18px] p-1 border border-transparent hover:border-[#ABB3B7]" title="Logout">logout</button>
</form>
</div>

</div>
</aside>
<style>
#sidebar .material-symbols-outlined {
  font-family:'Material Symbols Outlined','Material Icons',sans-serif;
  font-weight:normal;
  font-style:normal;
  line-height:1;
  letter-spacing:normal;
  text-transform:none;
  font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24;
  -webkit-font-smoothing:antialiased;
  display:inline-block;
  vertical-align:middle;
}
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
<script>
(function () {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebar-overlay');
    var desktopMedia = window.matchMedia('(min-width: 768px)');

    if (!sidebar || !overlay) return;

    function isOpen() {
        return !sidebar.classList.contains('-translate-x-full');
    }

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('opacity-0', 'pointer-events-none');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('opacity-0', 'pointer-events-none');
        document.body.style.overflow = '';
    }

    function toggleSidebar() {
        if (isOpen()) closeSidebar();
        else openSidebar();
    }

    // Delegated binding: works even when toggle buttons render after this partial.
    document.addEventListener('click', function (event) {
        var toggle = event.target.closest('[data-sidebar-toggle]');
        if (toggle) {
            event.preventDefault();
            toggleSidebar();
            return;
        }

        // On mobile, close after selecting a nav link in sidebar.
        var navLink = event.target.closest('#sidebar a[href]');
        if (navLink && !desktopMedia.matches) {
            closeSidebar();
        }
    });

    overlay.addEventListener('click', closeSidebar);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeSidebar();
    });

    // Ensure mobile overlay state is cleared when switching to desktop width.
    function onBreakpointChange() {
        if (desktopMedia.matches) closeSidebar();
    }

    if (typeof desktopMedia.addEventListener === 'function') {
        desktopMedia.addEventListener('change', onBreakpointChange);
    } else if (typeof desktopMedia.addListener === 'function') {
        desktopMedia.addListener(onBreakpointChange);
    }
})();
</script>
