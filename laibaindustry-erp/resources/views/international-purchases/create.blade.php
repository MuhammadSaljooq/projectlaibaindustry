<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Add international purchase - ERP'])
@include('partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'international_purchases'])

<main class="stitch-ui flex-1 flex flex-col h-full min-h-0 overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<a href="{{ route('international-purchases.index') }}" class="st-btn-secondary h-9 px-3 inline-flex items-center gap-2 text-[10px]">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
<span class="hidden sm:inline">International purchases</span>
</a>
</div>
</header>

<div class="flex-1 min-h-0 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-4xl mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col gap-1">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">INT_PURCH_01</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">New international purchase</h1>
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

@php
$itemRows = old('items');
if (! is_array($itemRows) || count($itemRows) === 0) {
    $itemRows = [['product_name' => '', 'quantity' => 1, 'unit_price' => '']];
}
@endphp

<div class="st-paper border border-[#ABB3B7] p-6 md:p-8 bg-white">
<form method="POST" action="{{ route('international-purchases.store') }}" id="intl-purchase-form" novalidate>
@csrf

<p class="st-label mb-6">Purchase details</p>

<div class="space-y-5 max-w-xl mb-8">
<div>
<label class="st-label block mb-2" for="supplier_id">Vendor</label>
<select class="st-input w-full h-10 px-3 text-sm" name="supplier_id" id="supplier_id">
<option value="">— None —</option>
@foreach($suppliers as $s)
<option value="{{ $s->id }}" @selected((string) old('supplier_id') === (string) $s->id)>{{ $s->name }}</option>
@endforeach
</select>
<p class="text-[11px] text-[#586064] mt-1"><a href="{{ route('suppliers.create') }}" class="text-[#5E5E5E] font-bold underline underline-offset-2">Add a vendor</a> if missing.</p>
</div>
<div>
<label class="st-label block mb-2" for="date">Date <span class="text-[#9F403D]">*</span></label>
<input class="st-input w-full h-10 px-3 text-sm" type="date" name="date" id="date" value="{{ old('date', now()->format('Y-m-d')) }}" required>
</div>
</div>

<p class="st-label mb-4">Line items</p>

<div class="overflow-x-auto -mx-2 sm:mx-0 border border-[#ABB3B7]">
<table class="w-full text-left border-collapse min-w-[560px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3">Product</th>
<th class="st-th px-4 py-3 text-right w-24">Qty</th>
<th class="st-th px-4 py-3 text-right w-[120px]">Unit price</th>
<th class="st-th px-4 py-3 text-right w-[100px]">Amount</th>
<th class="st-th px-4 py-3 w-12"></th>
</tr>
</thead>
<tbody id="line-items">
@foreach($itemRows as $i => $row)
<tr class="line-item st-tr">
<td class="st-td px-4 py-3 align-top">
<input class="product-name-input st-input w-full h-10 px-3 text-sm" type="text" name="items[{{ $i }}][product_name]" value="{{ old("items.$i.product_name", $row['product_name'] ?? '') }}" placeholder="Product name or description" maxlength="255" @if($loop->first) required @endif>
</td>
<td class="st-td px-4 py-3">
<input class="qty-input st-input w-full h-10 px-3 text-sm text-right font-mono tabular-nums" type="number" name="items[{{ $i }}][quantity]" value="{{ old("items.$i.quantity", $row['quantity'] ?? 1) }}" min="1" step="1" @if($loop->first) required @endif>
</td>
<td class="st-td px-4 py-3">
<input class="price-input st-input w-full h-10 px-3 text-sm text-right font-mono tabular-nums" type="number" name="items[{{ $i }}][unit_price]" value="{{ old("items.$i.unit_price", $row['unit_price'] ?? '') }}" step="0.01" min="0" placeholder="0.00" @if($loop->first) required @endif>
</td>
<td class="st-td px-4 py-3 text-right">
<span class="amount-display text-sm font-bold font-mono tabular-nums text-[#2B3437]">0.00</span>
</td>
<td class="st-td px-4 py-3">
<button type="button" class="remove-row p-2 text-[#586064] hover:text-[#9F403D] border border-transparent hover:border-[#ABB3B7]" title="Remove row">
<span class="material-symbols-outlined text-[18px]">delete</span>
</button>
</td>
</tr>
@endforeach
</tbody>
</table>
</div>

<button type="button" id="add-row" class="st-btn-secondary mt-3 h-10 px-4 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">add</span>
Add row
</button>

<p class="text-[11px] text-[#586064] mt-3">Total per line is qty × unit price. Empty product rows are ignored on save.</p>

<div class="flex justify-end pt-6 border-t border-[#ABB3B7] mt-6">
<div class="text-right space-y-2 min-w-[220px]">
<div class="flex justify-between gap-8 text-base font-black text-[#2B3437] border-t border-[#ABB3B7] pt-2">
<span>Total</span>
<span class="font-mono tabular-nums">{{ $currencySymbol ?? '$' }}<span id="total-display">0.00</span></span>
</div>
</div>
</div>

<div class="flex flex-wrap items-center gap-3 mt-8 pt-6 border-t border-[#ABB3B7]">
<button type="submit" class="st-btn-primary h-10 px-5 inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">save</span>
Save
</button>
<a href="{{ route('international-purchases.index') }}" class="st-btn-secondary h-10 px-5 inline-flex items-center gap-2">Cancel</a>
</div>
</form>
</div>

<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-4 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
</div>
</div>
</main>

<script>
(function() {
    let rowIndex = {{ count($itemRows) }};
    const tbody = document.getElementById('line-items');
    function updateRow(row) {
        const pi = row.querySelector('.price-input');
        const qi = row.querySelector('.qty-input');
        const as = row.querySelector('.amount-display');
        const a = (parseFloat(pi && pi.value ? pi.value : 0) || 0) * (parseInt(qi && qi.value ? qi.value : 0, 10) || 0);
        if (as) as.textContent = a.toFixed(2);
    }
    function updateTotals() {
        let s = 0;
        document.querySelectorAll('.line-item').forEach(function(r) {
            const p = r.querySelector('.price-input');
            const q = r.querySelector('.qty-input');
            s += (parseFloat(p && p.value ? p.value : 0) || 0) * (parseInt(q && q.value ? q.value : 0, 10) || 0);
        });
        const tte = document.getElementById('total-display');
        if (tte) tte.textContent = s.toFixed(2);
    }
    function onRowChange() {
        document.querySelectorAll('.line-item').forEach(function(row) { updateRow(row); });
        updateTotals();
    }
    tbody?.addEventListener('input', function(e) {
        if (e.target && e.target.closest && e.target.closest('.line-item') && (e.target.classList.contains('price-input') || e.target.classList.contains('qty-input'))) {
            onRowChange();
        }
    });
    tbody?.addEventListener('click', function(e) {
        const btn = e.target && e.target.closest ? e.target.closest('.remove-row') : null;
        if (!btn || !tbody) return;
        if (tbody.querySelectorAll('.line-item').length <= 1) return;
        btn.closest('.line-item')?.remove();
        onRowChange();
    });
    document.getElementById('add-row')?.addEventListener('click', function() {
        const fr = tbody?.querySelector('.line-item');
        if (!fr || !tbody) return;
        const nr = fr.cloneNode(true);
        const pn = nr.querySelector('.product-name-input');
        const qi = nr.querySelector('.qty-input');
        const pi = nr.querySelector('.price-input');
        pn.value = '';
        pn.name = 'items[' + rowIndex + '][product_name]';
        pn.removeAttribute('required');
        qi.value = '1';
        qi.name = 'items[' + rowIndex + '][quantity]';
        qi.removeAttribute('required');
        pi.value = '';
        pi.name = 'items[' + rowIndex + '][unit_price]';
        pi.removeAttribute('required');
        nr.querySelector('.amount-display').textContent = '0.00';
        tbody.appendChild(nr);
        rowIndex++;
        onRowChange();
    });
    document.getElementById('intl-purchase-form')?.addEventListener('submit', function() {
        let idx = 0;
        document.querySelectorAll('.line-item').forEach(function(row) {
            const pn = row.querySelector('.product-name-input');
            if (!pn || !String(pn.value || '').trim()) {
                row.querySelectorAll('input,select').forEach(function(el) { el.removeAttribute('name'); });
            } else {
                const q = row.querySelector('.qty-input');
                const p = row.querySelector('.price-input');
                pn.setAttribute('name', 'items[' + idx + '][product_name]');
                if (q) q.setAttribute('name', 'items[' + idx + '][quantity]');
                if (p) p.setAttribute('name', 'items[' + idx + '][unit_price]');
                idx++;
            }
        });
    });
    onRowChange();
})();
</script>
</body>
</html>
