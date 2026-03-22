<div id="sidebar-overlay" class="fixed inset-0 bg-black/40 z-20 opacity-0 pointer-events-none transition-opacity duration-300 md:hidden" aria-hidden="true"></div>
<aside id="sidebar" class="fixed md:static inset-y-0 left-0 w-64 flex flex-col h-full shrink-0 z-30 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out overflow-y-auto no-scrollbar" style="background:#F8F9FA;border-right:1px solid #D3D8DE;font-family:'Inter',sans-serif;">
<div class="flex flex-col h-full">

{{-- Brand --}}
<div style="padding:1.5rem 1rem 1rem 1rem;border-bottom:1px solid #D3D8DE;">
<h1 class="font-bold uppercase" style="font-size:1.25rem;letter-spacing:-0.02em;line-height:1.2;color:#2B3437;">Laiba Safety</h1>
<p class="uppercase font-bold" style="font-size:10px;letter-spacing:0.05em;color:#5E5E5E;margin-top:0.25rem;">Admin Console</p>
</div>

{{-- Nav Links --}}
<div class="flex-1 flex flex-col gap-0.5 px-3 overflow-y-auto no-scrollbar" style="padding-top:1rem;">

@php
$navItems = [
    ['key' => 'dashboard',    'icon' => 'dashboard',              'label' => 'Dashboard',   'route' => route('dashboard', absolute: false)],
    ['key' => 'products',     'icon' => 'inventory_2',            'label' => 'Inventory',   'route' => route('inventory.dashboard', absolute: false)],
    ['key' => 'sales',        'icon' => 'payments',               'label' => 'Sales',       'route' => route('sales.index', absolute: false)],
    ['key' => 'customers',    'icon' => 'group',                  'label' => 'Customers',   'route' => route('customers.index', absolute: false)],
    ['key' => 'receivables',  'icon' => 'account_balance_wallet', 'label' => 'Receivable',  'route' => route('receivables.index', absolute: false)],
    ['key' => 'purchases',    'icon' => 'shopping_cart',          'label' => 'Purchases',   'route' => route('purchases.index', absolute: false)],
    ['key' => 'payables',     'icon' => 'account_balance',        'label' => 'Payables',    'route' => route('payables.index', absolute: false)],
    ['key' => 'expenses',     'icon' => 'receipt_long',           'label' => 'Expenses',    'route' => route('expenses.index', absolute: false)],
    ['key' => 'vat',          'icon' => 'percent',                'label' => 'VAT',         'route' => route('vat.index', absolute: false)],
];
$active = $activeNav ?? '';
@endphp

@foreach($navItems as $item)
@if($active === $item['key'])
<a class="flex items-center gap-3 px-3 py-2.5 font-bold transition-all duration-200" style="background:#FFFFFF;border:1px solid #D3D8DE;border-left:2px solid #5E5E5E;color:#2B3437;" href="{{ $item['route'] }}">
<span class="material-symbols-outlined" style="font-size:20px;">{{ $item['icon'] }}</span>
<span class="text-sm" style="letter-spacing:-0.01em;">{{ $item['label'] }}</span>
</a>
@else
<a class="flex items-center gap-3 px-3 py-2.5 font-bold transition-all duration-200" style="color:#5E5E5E;border-left:2px solid transparent;" href="{{ $item['route'] }}" onmouseover="this.style.color='#2B3437';this.style.background='#FFFFFF';this.style.borderLeftColor='#5E5E5E'" onmouseout="this.style.color='#5E5E5E';this.style.background='transparent';this.style.borderLeftColor='transparent'">
<span class="material-symbols-outlined" style="font-size:20px;">{{ $item['icon'] }}</span>
<span class="text-sm" style="letter-spacing:-0.01em;">{{ $item['label'] }}</span>
</a>
@endif
@endforeach

{{-- Separator --}}
<div style="margin:1rem 0;border-top:1px solid #D3D8DE;"></div>

{{-- System Section --}}
@if(in_array(auth()->user()->role ?? '', ['admin', 'manager']))
@if($active === 'users')
<a class="flex items-center gap-3 px-3 py-2.5 font-bold transition-all duration-200" style="background:#FFFFFF;border:1px solid #D3D8DE;border-left:2px solid #5E5E5E;color:#2B3437;" href="{{ route('users.index', absolute: false) }}">
<span class="material-symbols-outlined" style="font-size:20px;">person</span>
<span class="text-sm" style="letter-spacing:-0.01em;">Users</span>
</a>
@else
<a class="flex items-center gap-3 px-3 py-2.5 font-bold transition-all duration-200" style="color:#5E5E5E;border-left:2px solid transparent;" href="{{ route('users.index', absolute: false) }}" onmouseover="this.style.color='#2B3437';this.style.background='#FFFFFF';this.style.borderLeftColor='#5E5E5E'" onmouseout="this.style.color='#5E5E5E';this.style.background='transparent';this.style.borderLeftColor='transparent'">
<span class="material-symbols-outlined" style="font-size:20px;">person</span>
<span class="text-sm" style="letter-spacing:-0.01em;">Users</span>
</a>
@endif
@endif

@if($active === 'settings')
<a class="flex items-center gap-3 px-3 py-2.5 font-bold transition-all duration-200" style="background:#FFFFFF;border:1px solid #D3D8DE;border-left:2px solid #5E5E5E;color:#2B3437;" href="{{ route('settings.index', absolute: false) }}">
<span class="material-symbols-outlined" style="font-size:20px;">settings</span>
<span class="text-sm" style="letter-spacing:-0.01em;">Settings</span>
</a>
@else
<a class="flex items-center gap-3 px-3 py-2.5 font-bold transition-all duration-200" style="color:#5E5E5E;border-left:2px solid transparent;" href="{{ route('settings.index', absolute: false) }}" onmouseover="this.style.color='#2B3437';this.style.background='#FFFFFF';this.style.borderLeftColor='#5E5E5E'" onmouseout="this.style.color='#5E5E5E';this.style.background='transparent';this.style.borderLeftColor='transparent'">
<span class="material-symbols-outlined" style="font-size:20px;">settings</span>
<span class="text-sm" style="letter-spacing:-0.01em;">Settings</span>
</a>
@endif

</div>

{{-- User Profile Footer --}}
<div style="padding:0.75rem;margin-top:auto;border-top:1px solid #D3D8DE;">
<form method="POST" action="{{ route('logout', absolute: false) }}" class="flex items-center gap-3 cursor-pointer transition-colors" style="padding:0.5rem;border:1px solid #D3D8DE;background:#FFFFFF;">
@csrf
<div class="shrink-0 overflow-hidden" style="width:2.5rem;height:2.5rem;background:#EAECEE;">
<div class="w-full h-full bg-cover bg-center" style="filter:grayscale(100%);background-image:url('https://lh3.googleusercontent.com/aida-public/AB6AXuAEFlpSGGEtjXJFRGVokTYO__I__d_7SuNR3lkmM_BQHu_oa0EpS7JWyL_U7kUhobpswzGKWvS54W9s91mr_xuCVO1iqywaCpcOpuOBOsUfxCrEC5n6z5Nywk70Wgm-r0VmjCd7XCD6jg5XYVxVj-MBhD5hIg2je7C9JC4cTjsi0-0ClU5NTO7Xxr1bZ66IkdWjupwQH4dkj6Qvv0JTgZrD-swCniaApQKvCJDzNLL1e4wtfDFCVbY74UDqzmpIOAEKmVRnZU6o_w8');"></div>
</div>
<div class="flex-1 min-w-0 text-left">
<p class="text-sm font-bold truncate" style="color:#2B3437;">{{ auth()->user()->name }}</p>
<p class="truncate uppercase font-bold" style="font-size:10px;letter-spacing:0.05em;color:#5E5E5E;">{{ ucfirst(auth()->user()->role) }}</p>
</div>
<button type="submit" class="material-symbols-outlined shrink-0" style="color:#5E5E5E;font-size:18px;" title="Logout">logout</button>
</form>
</div>

</div>
</aside>
<style>
#sidebar .material-symbols-outlined{font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24}
.no-scrollbar::-webkit-scrollbar{display:none}.no-scrollbar{-ms-overflow-style:none;scrollbar-width:none}
</style>
<script>
(function(){var s=document.getElementById('sidebar'),o=document.getElementById('sidebar-overlay');function open(){s?.classList.remove('-translate-x-full');o?.classList.remove('opacity-0','pointer-events-none');document.body.style.overflow='hidden'}function close(){s?.classList.add('-translate-x-full');o?.classList.add('opacity-0','pointer-events-none');document.body.style.overflow=''}document.querySelectorAll('[data-sidebar-toggle]').forEach(function(b){b.addEventListener('click',function(){s?.classList.contains('-translate-x-full')?open():close()})});o?.addEventListener('click',close);document.addEventListener('keydown',function(e){if(e.key==='Escape')close()})})();
</script>
