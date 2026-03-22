<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Edit Purchase - Laiba Safety'])
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
@include('products.partials.sidebar', ['activeNav' => 'purchases'])

<main class="flex-1 flex flex-col h-full overflow-hidden relative" style="background:#FFFFFF;">

<header class="h-14 flex items-center justify-between px-6 md:px-8 shrink-0 z-10" style="border-bottom:1px solid #D3D8DE;background:#F8F9FA;">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 rounded-none" style="color:#5E5E5E;background:transparent;" type="button" data-sidebar-toggle aria-label="Toggle menu"><span class="material-symbols-outlined">menu</span></button>
<a href="{{ route('purchases.show', $purchase) }}" class="flex items-center gap-2 transition-colors font-bold text-sm" style="color:#5E5E5E;" onmouseover="this.style.color='#2B3437'" onmouseout="this.style.color='#5E5E5E'">
<span class="material-symbols-outlined" style="font-size:18px;">arrow_back</span>
<span class="hidden sm:inline">Back to purchase</span>
</a>
</div>
</header>

<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth no-scrollbar">
<div class="max-w-4xl mx-auto flex flex-col" style="gap:3rem;">

<div>
<div class="flex items-end justify-between" style="padding-bottom:0.75rem;border-bottom:2px solid #5E5E5E;">
<div>
<h2 class="font-bold" style="font-size:1.5rem;letter-spacing:-0.02em;color:#2B3437;">Edit purchase</h2>
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;margin-top:0.5rem;">Update line items &amp; supplier</p>
</div>
<span class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Form PUR-{{ $purchase->id }}</span>
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
<p class="text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Purchase details</p>
</div>
<div style="padding:2rem;">
<form method="POST" action="{{ route('purchases.update', $purchase) }}" id="purchase-form" novalidate>
@csrf
@method('PUT')

<div style="margin-bottom:1.5rem;">
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="customer_select">Supplier / customer</label>
<select class="arch-select w-full h-11 pl-4 pr-10 text-sm font-bold appearance-none cursor-pointer" id="customer_select">
<option value="">Add new supplier</option>
@foreach($customers as $c)
<option value="{{ $c->id }}" data-code="{{ e($c->customer_code) }}" data-name="{{ e($c->customer_name) }}" {{ old('customer_code', $purchase->customer_code) === $c->customer_code ? 'selected' : '' }}>{{ $c->customer_name }} ({{ $c->customer_code }})</option>
@endforeach
</select>
<p class="mt-1.5 text-xs font-bold" style="color:#5E5E5E;">Select existing or fill fields below manually</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2" style="gap:1.5rem;margin-bottom:1.5rem;">
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="date">Date <span style="color:#9F403D;">*</span></label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold" id="date" name="date" type="datetime-local" value="{{ old('date', $purchase->date->format('Y-m-d\TH:i')) }}" required>
</div>
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="invoice_number">Invoice number <span style="color:#9F403D;">*</span></label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold" id="invoice_number" name="invoice_number" type="text" value="{{ old('invoice_number', $purchase->invoice_number) }}" maxlength="100" required placeholder="e.g. INV-2024-001">
</div>
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="customer_code">Customer code</label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold" id="customer_code" name="customer_code" type="text" value="{{ old('customer_code', $purchase->customer_code) }}" maxlength="100" placeholder="Auto-filled from selection">
</div>
<div>
<label class="block text-[10px] font-bold uppercase mb-2" style="letter-spacing:0.05em;color:#5E5E5E;" for="customer_name">Customer name</label>
<input class="arch-input w-full h-11 px-4 text-sm font-bold" id="customer_name" name="customer_name" type="text" value="{{ old('customer_name', $purchase->customer_name) }}" maxlength="255" placeholder="Auto-filled from selection">
</div>
</div>

<div style="margin:2rem 0;border-top:1px solid #D3D8DE;"></div>

<p class="text-[10px] font-bold uppercase mb-4" style="letter-spacing:0.05em;color:#5E5E5E;">Line items</p>

<div class="overflow-x-auto" style="margin:0 -0.5rem;">
<table class="w-full text-left border-collapse" style="min-width:640px;">
<thead>
<tr style="background:#F8F9FA;border-bottom:1px solid #D3D8DE;">
<th class="px-4 py-3 text-[10px] font-bold uppercase" style="letter-spacing:0.05em;color:#5E5E5E;">Product name</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;width:110px;">Price</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;width:80px;">Qty</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;width:100px;">Amount</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;width:100px;">VAT 15%</th>
<th class="px-4 py-3 text-[10px] font-bold uppercase text-right" style="letter-spacing:0.05em;color:#5E5E5E;width:100px;">Subtotal</th>
<th class="px-4 py-3" style="width:48px;"></th>
</tr>
</thead>
<tbody id="line-items">
@forelse($purchase->items as $idx => $item)
<tr class="line-item" style="border-top:1px solid #EAECEE;">
<td class="px-4 py-3">
<input class="product-name-input arch-input w-full h-10 px-3 text-sm font-bold" name="items[{{ $idx }}][product_name]" type="text" placeholder="Product name" value="{{ old("items.{$idx}.product_name", $item->product_name) }}" required>
</td>
<td class="px-4 py-3"><input class="price-input arch-input w-full h-10 px-3 text-right text-sm font-bold tabular-nums" name="items[{{ $idx }}][price]" type="number" step="0.01" min="0" value="{{ old("items.{$idx}.price", $item->price) }}" required></td>
<td class="px-4 py-3"><input class="qty-input arch-input w-full h-10 px-3 text-right text-sm font-bold tabular-nums" name="items[{{ $idx }}][quantity]" type="number" min="1" value="{{ old("items.{$idx}.quantity", $item->quantity) }}" required></td>
<td class="px-4 py-3 text-right"><span class="amount-display text-sm font-bold tabular-nums" style="color:#2B3437;">0.00</span></td>
<td class="px-4 py-3 text-right"><span class="vat-display text-sm tabular-nums" style="color:#5E5E5E;">0.00</span></td>
<td class="px-4 py-3 text-right"><span class="subtotal-display text-sm font-bold tabular-nums" style="color:#2B3437;">0.00</span></td>
<td class="px-4 py-3"><button type="button" class="remove-row p-1.5 transition-colors" style="color:#5E5E5E;" onmouseover="this.style.color='#9F403D'" onmouseout="this.style.color='#5E5E5E'" title="Remove"><span class="material-symbols-outlined" style="font-size:18px;">delete</span></button></td>
</tr>
@empty
<tr class="line-item" style="border-top:1px solid #EAECEE;">
<td class="px-4 py-3">
<input class="product-name-input arch-input w-full h-10 px-3 text-sm font-bold" name="items[0][product_name]" type="text" placeholder="Product name" value="{{ old('items.0.product_name') }}" required>
</td>
<td class="px-4 py-3"><input class="price-input arch-input w-full h-10 px-3 text-right text-sm font-bold tabular-nums" name="items[0][price]" type="number" step="0.01" min="0" value="{{ old('items.0.price', 0) }}" required></td>
<td class="px-4 py-3"><input class="qty-input arch-input w-full h-10 px-3 text-right text-sm font-bold tabular-nums" name="items[0][quantity]" type="number" min="1" value="{{ old('items.0.quantity', 1) }}" required></td>
<td class="px-4 py-3 text-right"><span class="amount-display text-sm font-bold tabular-nums" style="color:#2B3437;">0.00</span></td>
<td class="px-4 py-3 text-right"><span class="vat-display text-sm tabular-nums" style="color:#5E5E5E;">0.00</span></td>
<td class="px-4 py-3 text-right"><span class="subtotal-display text-sm font-bold tabular-nums" style="color:#2B3437;">0.00</span></td>
<td class="px-4 py-3"><button type="button" class="remove-row p-1.5 transition-colors" style="color:#5E5E5E;" onmouseover="this.style.color='#9F403D'" onmouseout="this.style.color='#5E5E5E'" title="Remove"><span class="material-symbols-outlined" style="font-size:18px;">delete</span></button></td>
</tr>
@endforelse
</tbody>
</table>
</div>

<button type="button" id="add-row" class="mt-3 h-9 px-4 text-[11px] font-bold uppercase flex items-center gap-2 transition-all" style="color:#2B3437;border:1px solid #5E5E5E;border-radius:0;letter-spacing:0.05em;background:transparent;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">
<span class="material-symbols-outlined" style="font-size:16px;">add</span>ADD ROW
</button>

<div style="margin-top:2rem;border-top:1px solid #D3D8DE;padding-top:1.5rem;">
<div class="flex justify-end">
<div style="min-width:220px;" class="flex flex-col gap-2">
<div class="flex justify-between text-sm font-bold"><span style="color:#5E5E5E;">Subtotal (excl. VAT)</span><span class="tabular-nums" style="color:#2B3437;" id="subtotal-display">0.00</span></div>
<div class="flex justify-between text-sm font-bold"><span style="color:#5E5E5E;">VAT (15%)</span><span class="tabular-nums" style="color:#2B3437;" id="vat-display">0.00</span></div>
<div class="flex justify-between text-base font-bold" style="padding-top:0.75rem;border-top:1px solid #D3D8DE;"><span style="color:#2B3437;">Total</span><span class="tabular-nums" style="color:#2B3437;">{{ $currencySymbol ?? '$' }} <span id="total-display">0.00</span></span></div>
</div>
</div>
</div>

<div style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid #D3D8DE;" class="flex flex-wrap items-center gap-3">
<button type="submit" class="h-11 px-6 text-[11px] font-bold uppercase flex items-center gap-2 active:scale-[0.98] transition-all" style="background:#5E5E5E;color:#F8F8F8;border-radius:0;letter-spacing:0.05em;" onmouseover="this.style.opacity='0.92'" onmouseout="this.style.opacity='1'">
<span class="material-symbols-outlined" style="font-size:16px;">save</span>UPDATE PURCHASE
</button>
<a href="{{ route('purchases.show', $purchase) }}" class="h-11 px-6 text-[11px] font-bold uppercase flex items-center gap-2 transition-all" style="color:#2B3437;border:1px solid #5E5E5E;border-radius:0;letter-spacing:0.05em;background:transparent;" onmouseover="this.style.background='#F8F9FA'" onmouseout="this.style.background='transparent'">CANCEL</a>
</div>
</form>
</div>
</div>

<div class="text-center text-[10px] uppercase font-bold pb-4" style="letter-spacing:0.05em;color:#5E5E5E;">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</div>
</div>
</div>
</main>

<script>
(function () {
    var customerSelect = document.getElementById('customer_select');
    var customerCodeInput = document.getElementById('customer_code');
    var customerNameInput = document.getElementById('customer_name');

    if (customerSelect && customerCodeInput && customerNameInput) {
        customerSelect.addEventListener('change', function () {
            var opt = this.options[this.selectedIndex];
            if (opt && opt.value) {
                customerCodeInput.value = opt.getAttribute('data-code') || '';
                customerNameInput.value = opt.getAttribute('data-name') || '';
            } else {
                customerCodeInput.value = '';
                customerNameInput.value = '';
            }
        });
    }

    document.getElementById('purchase-form')?.addEventListener('submit', function () {
        var idx = 0;
        document.querySelectorAll('.line-item').forEach(function (row) {
            var nameInput = row.querySelector('.product-name-input');
            if (!nameInput || !nameInput.value.trim()) {
                row.querySelectorAll('input').forEach(function (el) { el.removeAttribute('name'); });
            } else {
                var price = row.querySelector('.price-input');
                var qty = row.querySelector('.qty-input');
                nameInput.setAttribute('name', 'items[' + idx + '][product_name]');
                if (price) price.setAttribute('name', 'items[' + idx + '][price]');
                if (qty)   qty.setAttribute('name',   'items[' + idx + '][quantity]');
                idx++;
            }
        });
    });

    var rowIndex = {{ $purchase->items->count() > 0 ? $purchase->items->count() : 1 }};
    var VAT_RATE = 0.15;

    function calcRow(row) {
        var price    = parseFloat(row.querySelector('.price-input')?.value) || 0;
        var qty      = parseInt(row.querySelector('.qty-input')?.value, 10) || 0;
        var amount   = price * qty;
        var vat      = amount * VAT_RATE;
        var subtotal = amount + vat;

        var amountSpan   = row.querySelector('.amount-display');
        var vatSpan      = row.querySelector('.vat-display');
        var subtotalSpan = row.querySelector('.subtotal-display');

        if (amountSpan)   amountSpan.textContent   = amount.toFixed(2);
        if (vatSpan)      vatSpan.textContent       = vat.toFixed(2);
        if (subtotalSpan) subtotalSpan.textContent  = subtotal.toFixed(2);
    }

    function recalcTotals() {
        var totalSubtotal = 0;
        var totalVat      = 0;

        document.querySelectorAll('.line-item').forEach(function (row) {
            var price  = parseFloat(row.querySelector('.price-input')?.value) || 0;
            var qty    = parseInt(row.querySelector('.qty-input')?.value, 10) || 0;
            var amount = price * qty;
            totalSubtotal += amount;
            totalVat      += amount * VAT_RATE;
        });

        var total = totalSubtotal + totalVat;

        var subEl   = document.getElementById('subtotal-display');
        var vatEl   = document.getElementById('vat-display');
        var totalEl = document.getElementById('total-display');

        if (subEl)   subEl.textContent   = totalSubtotal.toFixed(2);
        if (vatEl)   vatEl.textContent   = totalVat.toFixed(2);
        if (totalEl) totalEl.textContent = total.toFixed(2);
    }

    function onRowChange() {
        document.querySelectorAll('.line-item').forEach(calcRow);
        recalcTotals();
    }

    function bindRowEvents() {
        document.querySelectorAll('.line-item').forEach(function (row) {
            row.querySelector('.price-input')?.addEventListener('input', onRowChange);
            row.querySelector('.qty-input')?.addEventListener('input', onRowChange);
        });

        document.querySelectorAll('.remove-row').forEach(function (btn) {
            btn.onclick = function () {
                if (document.querySelectorAll('.line-item').length <= 1) return;
                this.closest('.line-item').remove();
                onRowChange();
            };
        });
    }

    document.getElementById('add-row')?.addEventListener('click', function () {
        var tbody = document.getElementById('line-items');
        var firstRow = tbody.querySelector('.line-item');
        if (!firstRow) return;

        var newRow = firstRow.cloneNode(true);
        newRow.querySelector('.product-name-input').value = '';
        newRow.querySelector('.product-name-input').setAttribute('name', 'items[' + rowIndex + '][product_name]');
        newRow.querySelector('.price-input').value = '0';
        newRow.querySelector('.price-input').setAttribute('name', 'items[' + rowIndex + '][price]');
        newRow.querySelector('.qty-input').value = '1';
        newRow.querySelector('.qty-input').setAttribute('name', 'items[' + rowIndex + '][quantity]');
        newRow.querySelector('.amount-display').textContent = '0.00';
        newRow.querySelector('.vat-display').textContent    = '0.00';
        newRow.querySelector('.subtotal-display').textContent = '0.00';
        newRow.querySelectorAll('input').forEach(function (el) { el.removeAttribute('required'); });

        tbody.appendChild(newRow);
        rowIndex++;
        bindRowEvents();
        newRow.querySelector('.product-name-input')?.focus();
    });

    bindRowEvents();
    onRowChange();
})();
</script>
</body>
</html>
