<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'New Sale - Laiba Safety'])
<style>
body { background-color: #FFFFFF; color: #2B3437; font-family: 'Inter', sans-serif; color-scheme: light; }
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; font-size: 1.25rem; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::selection { background: #2B3437; color: #FFFFFF; }
input::placeholder { color: rgba(94, 94, 94, 0.55) !important; }
.arch-input, .arch-select {
  background-color: #FFFFFF !important;
  border: 1px solid #D3D8DE !important;
  border-radius: 0 !important;
  color: #2B3437 !important;
  color-scheme: light !important;
}
.arch-input:focus, .arch-select:focus {
  border-color: #5E5E5E !important;
  border-width: 2px !important;
  box-shadow: none !important;
  outline: none !important;
  --tw-ring-shadow: none !important;
  --tw-ring-offset-shadow: none !important;
}
.arch-select option { background: #FFFFFF; color: #2B3437; }
main input:focus, main select:focus {
  outline: none !important;
  --tw-ring-shadow: 0 0 #0000 !important;
  --tw-ring-offset-shadow: 0 0 #0000 !important;
  box-shadow: none !important;
}
</style>
</head>
<body class="h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'sales'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#FFFFFF;">

<header class="h-14 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 rounded-none" style="color:#5E5E5E;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu"><span class="material-symbols-outlined">menu</span></button>
<a href="{{ route('sales.index') }}" class="flex items-center gap-2 transition-colors font-bold text-sm" style="color:#5E5E5E;" onmouseover="this.style.color='#2B3437'" onmouseout="this.style.color='#5E5E5E'">
<span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
<span class="hidden sm:inline">Back to Sales</span>
</a>
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-4xl mx-auto flex flex-col" style="gap:3rem;">

<div>
<div class="flex items-end justify-between" style="padding-bottom:0.75rem;border-bottom:2px solid #5E5E5E;">
<div>
<h2 class="font-bold" style="font-size:1.5rem;letter-spacing:-0.02em;color:#2B3437;">Create Sale</h2>
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-top:0.5rem;">New Transaction</p>
</div>
<span class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Form SLS-NEW</span>
</div>
</div>

@if ($errors->any())
<div style="border:1px solid #9F403D;padding:1rem 1.25rem;background:#FFFFFF;">
<p class="text-[10px] font-bold uppercase mb-2" style="color:#9F403D;letter-spacing:0.05em;">Please fix the following</p>
@foreach ($errors->all() as $err)
<p class="text-sm font-bold" style="color:#9F403D;margin-top:0.25rem;">{{ $err }}</p>
@endforeach
</div>
@endif
@if (session('error'))
<div style="border:1px solid #9F403D;padding:0.75rem 1.25rem;background:#FFFFFF;" class="text-sm font-bold"><span style="color:#9F403D;">{{ session('error') }}</span></div>
@endif

<div style="border:1px solid #D3D8DE;background:#FFFFFF;">
<div style="padding:1rem 1.5rem;border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Sale Details</p>
</div>
<div style="padding:2rem;">
<form method="POST" action="{{ route('sales.store') }}" id="sale-form" novalidate>
@csrf

<div style="margin-bottom:1.5rem;">
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="customer_select">Customer</label>
<select class="arch-select w-full h-11 pl-4 pr-10 text-sm font-bold appearance-none cursor-pointer" id="customer_select">
<option value="">Select or add new customer</option>
@foreach($customers ?? [] as $c)
<option value="{{ $c->id }}" data-code="{{ e($c->customer_code) }}" data-name="{{ e($c->customer_name) }}">{{ $c->customer_name }} ({{ $c->customer_code }})</option>
@endforeach
</select>
<p class="mt-1.5 text-xs font-bold" style="color:#5E5E5E;">Select existing or fill in below manually</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2" style="gap:1.5rem;margin-bottom:1.5rem;">
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="date">Date <span style="color:#9F403D;">*</span></label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold" id="date" name="date" type="datetime-local" value="{{ old('date', now()->format('Y-m-d\TH:i')) }}" required>
</div>
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="invoice_number">Invoice Number <span style="color:#9F403D;">*</span></label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold" id="invoice_number" name="invoice_number" type="text" value="{{ old('invoice_number') }}" maxlength="100" required placeholder="e.g. INV-2024-001">
</div>
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="customer_code">Customer Code</label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold" id="customer_code" name="customer_code" type="text" value="{{ old('customer_code') }}" maxlength="100" placeholder="Auto-filled from selection">
</div>
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="customer_name">Customer Name</label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold" id="customer_name" name="customer_name" type="text" value="{{ old('customer_name') }}" maxlength="255" placeholder="Auto-filled from selection">
</div>
</div>

<div style="margin:2rem 0;border-top:1px solid #D3D8DE;"></div>

<p class="text-[10px] font-bold uppercase mb-4" style="letter-spacing:0.05em;color:#5E5E5E;">Line Items</p>

<div class="overflow-x-auto" style="margin:0 -0.5rem;">
<table class="w-full text-left border-collapse" style="min-width:600px;">
<thead>
<tr style="background:#F8F9FA;border-bottom:1px solid #D3D8DE;">
<th class="px-4 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Product</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;width:110px;">Price</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;width:80px;">Qty</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;width:100px;">Amount</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;width:100px;">VAT 15%</th>
<th class="px-4 py-3" style="width:48px;"></th>
</tr>
</thead>
<tbody id="line-items">
<tr class="line-item" style="border-top:1px solid #EAECEE;">
<td class="px-4 py-3">
<select class="product-select arch-select w-full h-10 pl-3 pr-8 text-sm font-bold appearance-none" name="items[0][product_id]" required>
<option value="">Select product</option>
@foreach($products as $p)
<option value="{{ $p->id }}" data-price="{{ $p->selling_price ?? $p->cost_price }}" data-stock="{{ $p->stock_quantity }}">{{ $p->name }} (Article #{{ $p->sku }})</option>
@endforeach
</select>
</td>
<td class="px-4 py-3"><input class="price-input arch-input w-full h-10 px-3 text-right text-sm font-bold tabular-nums" name="items[0][selling_price]" type="number" step="0.01" min="0" value="0" required></td>
<td class="px-4 py-3"><input class="qty-input arch-input w-full h-10 px-3 text-right text-sm font-bold tabular-nums" name="items[0][quantity]" type="number" min="1" value="1" required></td>
<td class="px-4 py-3 text-right"><span class="amount-display text-sm font-bold tabular-nums" style="color:#2B3437;">0.00</span></td>
<td class="px-4 py-3 text-right"><span class="vat-display text-sm tabular-nums" style="color:#5E5E5E;">0.00</span></td>
<td class="px-4 py-3"><button type="button" class="remove-row p-1.5 transition-colors" style="color:#5E5E5E;" onmouseover="this.style.color='#9F403D'" onmouseout="this.style.color='#5E5E5E'" title="Remove"><span class="material-symbols-outlined" style="font-size:18px;">delete</span></button></td>
</tr>
</tbody>
</table>
</div>

<button type="button" id="add-row" class="mt-3 h-9 px-4 text-[11px] font-bold uppercase flex items-center gap-2 transition-all" style="color:#2B3437;border:1px solid #5E5E5E;border-radius:0;letter-spacing:0.05em;background:transparent;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:16px;">add</span>ADD ROW
</button>

<div style="margin-top:2rem;border-top:1px solid #D3D8DE;padding-top:1.5rem;">
<div class="flex justify-end">
<div style="min-width:220px;" class="flex flex-col gap-2">
<div class="flex justify-between text-sm font-bold"><span style="color:#5E5E5E;">Subtotal</span><span class="tabular-nums" style="color:#2B3437;" id="subtotal-display">0.00</span></div>
<div class="flex justify-between text-sm font-bold"><span style="color:#5E5E5E;">VAT (15%)</span><span class="tabular-nums" style="color:#2B3437;" id="tax-display">0.00</span></div>
<div class="flex justify-between text-base font-bold" style="padding-top:0.75rem;border-top:1px solid #D3D8DE;"><span style="color:#2B3437;">Total</span><span class="tabular-nums" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} <span id="total-display">0.00</span></span></div>
</div>
</div>
</div>

<div style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid #D3D8DE;" class="flex flex-wrap items-center gap-3">
<button type="submit" class="h-11 px-6 text-[11px] font-bold uppercase flex items-center gap-2 active:scale-[0.98] transition-all" style="background:#5E5E5E;color:#F8F8F8;border-radius:0;letter-spacing:0.05em;" onmouseover="this.style.opacity='0.92'" onmouseout="this.style.opacity='1'">
<span class="material-symbols-outlined" style="font-size:16px;">save</span>SAVE SALE
</button>
<a href="{{ route('sales.index') }}" class="h-11 px-6 text-[11px] font-bold uppercase flex items-center gap-2 transition-all" style="color:#2B3437;border:1px solid #5E5E5E;border-radius:0;letter-spacing:0.05em;background:transparent;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">CANCEL</a>
</div>
</form>
</div>
</div>

<div class="text-center text-[10px] uppercase font-bold pb-4" style="letter-spacing:0.05em;color:#5E5E5E;">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</div>
</div>
</div>
</main>

<script>
(function() {
    var cs = document.getElementById('customer_select'), cc = document.getElementById('customer_code'), cn = document.getElementById('customer_name');
    if (cs && cc && cn) {
        cs.addEventListener('change', function() { var o = this.options[this.selectedIndex]; if (o && o.value) { cc.value = o.getAttribute('data-code')||''; cn.value = o.getAttribute('data-name')||''; } else { cc.value=''; cn.value=''; } });
        if (cc.value || cn.value) { for (var i=0;i<cs.options.length;i++) { if (cs.options[i].value && cs.options[i].getAttribute('data-code')===cc.value) { cs.selectedIndex=i; break; } } }
    }
    document.getElementById('sale-form')?.addEventListener('submit', function() {
        var idx=0; document.querySelectorAll('.line-item').forEach(function(row) {
            var sel=row.querySelector('.product-select'); if(!sel||!sel.value){row.querySelectorAll('input,select').forEach(function(el){el.removeAttribute('name')});}else{var p=row.querySelector('.price-input'),q=row.querySelector('.qty-input');sel.setAttribute('name','items['+idx+'][product_id]');if(p)p.setAttribute('name','items['+idx+'][selling_price]');if(q)q.setAttribute('name','items['+idx+'][quantity]');idx++;}
        });
    });
    const products = @json($products->mapWithKeys(fn($p) => [$p->id => ['price' => (float)($p->selling_price ?? $p->cost_price ?? 0), 'stock' => $p->stock_quantity]])->all());
    let rowIndex = 1;
    function updateRow(row) { const pi=row.querySelector('.price-input'),qi=row.querySelector('.qty-input'),as=row.querySelector('.amount-display'),vs=row.querySelector('.vat-display'); const a=(parseFloat(pi?.value||0)||0)*(parseInt(qi?.value||0,10)||0),v=a*0.15; if(as)as.textContent=a.toFixed(2); if(vs)vs.textContent=v.toFixed(2); }
    function updateTotals() { let s=0; document.querySelectorAll('.line-item').forEach(r=>{const p=r.querySelector('.price-input'),q=r.querySelector('.qty-input');s+=(parseFloat(p?.value||0)||0)*(parseInt(q?.value||0,10)||0);}); const t=s*0.15,tot=s+t; const se=document.getElementById('subtotal-display'),te=document.getElementById('tax-display'),tte=document.getElementById('total-display'); if(se)se.textContent=s.toFixed(2); if(te)te.textContent=t.toFixed(2); if(tte)tte.textContent=tot.toFixed(2); }
    function onRowChange() { document.querySelectorAll('.line-item').forEach(updateRow); updateTotals(); }
    document.getElementById('add-row')?.addEventListener('click', function() {
        const tbody=document.getElementById('line-items'),fr=tbody.querySelector('.line-item'); if(!fr)return;
        const nr=fr.cloneNode(true); nr.querySelector('.product-select').value=''; nr.querySelector('.product-select').name='items['+rowIndex+'][product_id]';
        nr.querySelector('.price-input').value='0'; nr.querySelector('.price-input').name='items['+rowIndex+'][selling_price]';
        nr.querySelector('.qty-input').value='1'; nr.querySelector('.qty-input').name='items['+rowIndex+'][quantity]';
        nr.querySelector('.amount-display').textContent='0.00'; nr.querySelector('.vat-display').textContent='0.00';
        nr.querySelectorAll('input,select').forEach(el=>el.removeAttribute('required'));
        tbody.appendChild(nr); rowIndex++; bindRowEvents();
    });
    function bindRowEvents() {
        document.querySelectorAll('.line-item').forEach(row => {
            row.querySelector('.product-select')?.addEventListener('change', function() { const pid=this.value,prod=products[pid]; if(prod){row.querySelector('.price-input').value=prod.price;} onRowChange(); });
            row.querySelector('.price-input')?.addEventListener('input', onRowChange);
            row.querySelector('.qty-input')?.addEventListener('input', onRowChange);
        });
        document.querySelectorAll('.remove-row').forEach(btn => { btn.onclick = function() { if(document.querySelectorAll('.line-item').length<=1)return; this.closest('.line-item').remove(); onRowChange(); }; });
    }
    bindRowEvents(); onRowChange();
})();
</script>
</body>
</html>
