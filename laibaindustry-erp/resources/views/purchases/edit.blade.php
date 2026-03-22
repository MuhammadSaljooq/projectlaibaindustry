<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Edit Purchase - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'purchases'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-[#1a2632] border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<h2 class="text-xl font-bold text-slate-800 dark:text-white hidden sm:block">Edit Purchase</h2>
</div>
</header>
<div class="flex-1 overflow-y-auto p-6 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-6">
<div class="sm:hidden">
<h2 class="text-2xl font-bold text-slate-800 dark:text-white">Edit Purchase</h2>
</div>

@if (session('error'))
<div class="rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-400">
{{ session('error') }}
</div>
@endif
@if ($errors->any())
<div class="rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-400">
<ul class="list-disc list-inside space-y-0.5">
@foreach ($errors->all() as $err)
<li>{{ $err }}</li>
@endforeach
</ul>
</div>
@endif

<div class="max-w-4xl">
<div class="bg-white dark:bg-[#1a2632] rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
<form method="POST" action="{{ route('purchases.update', $purchase) }}" id="purchase-form" novalidate>
@csrf
@method('PUT')
<div class="space-y-6">

<h3 class="text-base font-semibold text-slate-800 dark:text-white">Purchase Details</h3>

<div class="space-y-4">
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="customer_select">Customer / Supplier</label>
<select class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" id="customer_select">
<option value="">Add new supplier</option>
@foreach($customers as $c)
<option value="{{ $c->id }}" data-code="{{ e($c->customer_code) }}" data-name="{{ e($c->customer_name) }}" {{ old('customer_code', $purchase->customer_code) === $c->customer_code ? 'selected' : '' }}>{{ $c->customer_name }} ({{ $c->customer_code }})</option>
@endforeach
</select>
<p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Select an existing record to auto-fill, or enter details below</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="date">Date <span class="text-red-500">*</span></label>
<input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" id="date" name="date" type="datetime-local" value="{{ old('date', $purchase->date->format('Y-m-d\TH:i')) }}" required>
</div>
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="invoice_number">Invoice Number <span class="text-red-500">*</span></label>
<input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" id="invoice_number" name="invoice_number" type="text" value="{{ old('invoice_number', $purchase->invoice_number) }}" maxlength="100" required placeholder="e.g. INV-2024-001">
</div>
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="customer_code">Customer Code</label>
<input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" id="customer_code" name="customer_code" type="text" value="{{ old('customer_code', $purchase->customer_code) }}" maxlength="100" placeholder="Auto-filled when selecting supplier">
</div>
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="customer_name">Customer Name</label>
<input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" id="customer_name" name="customer_name" type="text" value="{{ old('customer_name', $purchase->customer_name) }}" maxlength="255" placeholder="Auto-filled when selecting supplier">
</div>
</div>
</div>

<h3 class="text-base font-semibold text-slate-800 dark:text-white pt-4">Line Items</h3>
<div class="overflow-x-auto -mx-4 sm:mx-0">
<table class="w-full text-left border-collapse min-w-[640px]">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Product Name</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase text-right w-28">Price</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase text-right w-20">Qty</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase text-right w-28">Amount</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase text-right w-28">VAT</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase text-right w-28">Subtotal</th>
<th class="px-4 py-3 w-12"></th>
</tr>
</thead>
<tbody id="line-items">
@foreach($purchase->items as $idx => $item)
<tr class="line-item border-b border-slate-200 dark:border-slate-700">
<td class="px-4 py-3">
<input class="product-name-input w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" name="items[{{ $idx }}][product_name]" type="text" placeholder="Enter product name" value="{{ old("items.{$idx}.product_name", $item->product_name) }}" required>
</td>
<td class="px-4 py-3">
<input class="price-input w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 text-right text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" name="items[{{ $idx }}][price]" type="number" step="0.01" min="0" value="{{ old("items.{$idx}.price", $item->price) }}" required>
</td>
<td class="px-4 py-3">
<input class="qty-input w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 text-right text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" name="items[{{ $idx }}][quantity]" type="number" min="1" value="{{ old("items.{$idx}.quantity", $item->quantity) }}" required>
</td>
<td class="px-4 py-3 text-right">
<span class="amount-display font-mono text-sm text-slate-900 dark:text-white">0.00</span>
</td>
<td class="px-4 py-3 text-right">
<span class="vat-display font-mono text-sm text-slate-600 dark:text-slate-300">0.00</span>
</td>
<td class="px-4 py-3 text-right">
<span class="subtotal-display font-mono text-sm font-medium text-slate-900 dark:text-white">0.00</span>
</td>
<td class="px-4 py-3">
<button type="button" class="remove-row p-2 text-slate-400 hover:text-red-500 rounded-lg transition-colors" title="Remove row">
<span class="material-symbols-outlined text-[20px]">delete</span>
</button>
</td>
</tr>
@endforeach
</tbody>
</table>
</div>

<button type="button" id="add-row" class="mt-2 h-9 px-3 text-sm font-medium text-primary hover:bg-primary/5 rounded-lg border border-primary/30 transition-colors inline-flex items-center gap-2 whitespace-nowrap shrink-0">
<span class="material-symbols-outlined text-[18px] shrink-0">add</span>
<span>Add row</span>
</button>

<div class="flex justify-end pt-4 border-t border-slate-200 dark:border-slate-700 mt-6">
<div class="text-right space-y-1.5 min-w-[240px]">
<div class="flex items-center justify-between gap-8 text-sm text-slate-600 dark:text-slate-400">
<span>Subtotal (excl. VAT)</span>
<span id="subtotal-display" class="font-bold text-slate-900 dark:text-white font-mono">0.00</span>
</div>
<div class="flex items-center justify-between gap-8 text-sm text-slate-600 dark:text-slate-400">
<span>Total VAT</span>
<span id="vat-display" class="font-bold text-slate-900 dark:text-white font-mono">0.00</span>
</div>
<div class="flex items-center justify-between gap-8 text-lg font-bold text-slate-900 dark:text-white border-t border-slate-200 dark:border-slate-700 pt-1.5">
<span>Total</span>
<span class="font-mono"><span id="total-display">0.00</span></span>
</div>
</div>
</div>

</div>

<div class="flex flex-wrap gap-3 mt-6">
<button type="submit" class="h-10 px-5 bg-primary hover:bg-blue-600 text-white text-sm font-bold rounded-lg transition-colors whitespace-nowrap">Update Purchase</button>
<a href="{{ route('purchases.show', $purchase) }}" class="h-10 px-5 inline-flex items-center text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors whitespace-nowrap">Cancel</a>
</div>
</form>
</div>
</div>
<div class="mt-8 text-center text-xs text-slate-400 pb-4">&copy; {{ date('Y') }} Laiba Safety. All rights reserved.</div>
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

    var rowIndex = {{ count($purchase->items) }};
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
