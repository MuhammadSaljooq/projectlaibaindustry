<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'New Sale - ERP'])
@include('partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'sales'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<a href="{{ route('sales.index') }}" class="st-btn-secondary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
<span class="hidden sm:inline">Sales</span>
</a>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-4xl mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col gap-1">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">SALE_ENTRY_12</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">Create sale</h1>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

@if ($errors->any())
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D]">
<p class="st-label st-label--error mb-2">Please fix the following</p>
<ul class="list-disc list-inside space-y-0.5">
@foreach ($errors->all() as $err)
<li>{{ $err }}</li>
@endforeach
</ul>
</div>
@endif
@if (session('error'))
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D]">{{ session('error') }}</div>
@endif

<div class="st-paper border border-[#ABB3B7] p-6 md:p-8 bg-white">
<form method="POST" action="{{ route('sales.store') }}" id="sale-form" novalidate>
@csrf

<p class="st-label mb-6">Sale details</p>

<div class="space-y-4 mb-6">
<label class="st-label block mb-2" for="customer_select">Customer</label>
<select class="st-select w-full pl-4 pr-10 py-2 text-sm appearance-none bg-no-repeat bg-[length:1rem] bg-[right_0.75rem_center]" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%23586064%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E');" id="customer_select">
<option value="">Select or add new customer</option>
@foreach($customers ?? [] as $c)
<option value="{{ $c->id }}" data-code="{{ e($c->customer_code) }}" data-name="{{ e($c->customer_name) }}">{{ $c->customer_name }} ({{ $c->customer_code }})</option>
@endforeach
</select>
<p class="text-xs text-[#586064] mt-2">Select existing or fill in below manually</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
<div>
<label class="st-label block mb-2" for="date">Date <span class="text-[#9F403D]">*</span></label>
<input class="st-input w-full h-11 px-4 text-sm" id="date" name="date" type="datetime-local" value="{{ old('date', now()->format('Y-m-d\TH:i')) }}" required>
</div>
<div>
<label class="st-label block mb-2" for="invoice_number">Invoice number <span class="text-[#9F403D]">*</span></label>
<input class="st-input w-full h-11 px-4 text-sm" id="invoice_number" name="invoice_number" type="text" value="{{ old('invoice_number') }}" maxlength="100" required placeholder="e.g. INV-2024-001">
</div>
<div>
<label class="st-label block mb-2" for="customer_code">Customer code</label>
<input class="st-input w-full h-11 px-4 text-sm" id="customer_code" name="customer_code" type="text" value="{{ old('customer_code') }}" maxlength="100" placeholder="Auto-filled from selection">
</div>
<div>
<label class="st-label block mb-2" for="customer_name">Customer name</label>
<input class="st-input w-full h-11 px-4 text-sm" id="customer_name" name="customer_name" type="text" value="{{ old('customer_name') }}" maxlength="255" placeholder="Auto-filled from selection">
</div>
</div>

<p class="st-label mb-4">Line items</p>

<div class="overflow-x-auto -mx-2 sm:mx-0 border border-[#ABB3B7]">
<table class="w-full text-left border-collapse min-w-[600px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3">Product</th>
<th class="st-th px-4 py-3 text-right w-[110px]">Price</th>
<th class="st-th px-4 py-3 text-right w-20">Qty</th>
<th class="st-th px-4 py-3 text-right w-[100px]">Amount</th>
<th class="st-th px-4 py-3 text-right w-[100px]">VAT 15%</th>
<th class="st-th px-4 py-3 w-12"></th>
</tr>
</thead>
<tbody id="line-items">
<tr class="line-item st-tr">
<td class="st-td px-4 py-3">
<select class="product-select st-select w-full h-10 pl-3 pr-9 text-sm appearance-none bg-no-repeat bg-[length:1rem] bg-[right_0.5rem_center]" style="background-image:url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 24 24%22 stroke=%22%23586064%22%3E%3Cpath stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%222%22 d=%22M19 9l-7 7-7-7%22/%3E%3C/svg%3E');" name="items[0][product_id]" required>
<option value="">Select product</option>
@foreach($products as $p)
<option value="{{ $p->id }}" data-price="{{ $p->selling_price ?? $p->cost_price }}" data-stock="{{ $p->stock_quantity }}">{{ $p->name }} ({{ $p->sku }})</option>
@endforeach
</select>
</td>
<td class="st-td px-4 py-3"><input class="price-input st-input w-full h-10 px-3 text-sm text-right font-mono tabular-nums" name="items[0][selling_price]" type="number" step="0.01" min="0" value="0" required></td>
<td class="st-td px-4 py-3"><input class="qty-input st-input w-full h-10 px-3 text-sm text-right font-mono tabular-nums" name="items[0][quantity]" type="number" min="1" value="1" required></td>
<td class="st-td px-4 py-3 text-right"><span class="amount-display text-sm font-bold font-mono tabular-nums text-[#2B3437]">0.00</span></td>
<td class="st-td px-4 py-3 text-right"><span class="vat-display text-sm font-mono tabular-nums text-[#586064]">0.00</span></td>
<td class="st-td px-4 py-3">
<button type="button" class="remove-row p-2 text-[#586064] hover:text-[#9F403D] border border-transparent hover:border-[#ABB3B7]" title="Remove row">
<span class="material-symbols-outlined text-[18px]">delete</span>
</button>
</td>
</tr>
</tbody>
</table>
</div>

<button type="button" id="add-row" class="st-btn-secondary mt-3 h-10 px-4 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">add</span>
Add row
</button>

<div class="flex justify-end pt-6 border-t border-[#ABB3B7] mt-6">
<div class="text-right space-y-2 min-w-[220px]">
<div class="flex justify-between gap-8 text-sm text-[#586064]"><span>Subtotal</span><span id="subtotal-display" class="font-bold font-mono tabular-nums text-[#2B3437]">0.00</span></div>
<div class="flex justify-between gap-8 text-sm text-[#586064]"><span>VAT (15%)</span><span id="tax-display" class="font-bold font-mono tabular-nums text-[#2B3437]">0.00</span></div>
<div class="flex justify-between gap-8 text-base font-black text-[#2B3437] border-t border-[#ABB3B7] pt-2"><span>Total</span><span class="font-mono tabular-nums">$<span id="total-display">0.00</span></span></div>
</div>
</div>

<div class="flex flex-wrap items-center gap-3 mt-8">
<button type="submit" class="st-btn-primary h-11 px-6 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">save</span>
Save sale
</button>
<a href="{{ route('sales.index') }}" class="st-btn-secondary h-11 px-6 inline-flex items-center">Cancel</a>
</div>
</form>
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-4 pb-2">© {{ date('Y') }} Nexus ERP Inc.</p>
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
