<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'New Sale - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'sales'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-[#1a2632] border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<h2 class="text-xl font-bold text-slate-800 dark:text-white hidden sm:block">New Sale</h2>
</div>
</header>
<div class="flex-1 overflow-y-auto p-6 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-6">
<div class="sm:hidden">
<h2 class="text-2xl font-bold text-slate-800 dark:text-white">New Sale</h2>
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
<form method="POST" action="{{ route('sales.store') }}" id="sale-form" novalidate>
@csrf
<div class="space-y-6">
<h3 class="text-base font-semibold text-slate-800 dark:text-white">Sale Details</h3>
<div class="space-y-4">
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="customer_select">Customer</label>
<select class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" id="customer_select">
<option value="">Add new customer</option>
@foreach($customers ?? [] as $c)
<option value="{{ $c->id }}" data-code="{{ e($c->customer_code) }}" data-name="{{ e($c->customer_name) }}">{{ $c->customer_name }} ({{ $c->customer_code }})</option>
@endforeach
</select>
<p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Select existing customer to auto-fill, or add new below</p>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="date">Date <span class="text-red-500">*</span></label>
<input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" id="date" name="date" type="datetime-local" value="{{ old('date', now()->format('Y-m-d\TH:i')) }}" required>
</div>
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="invoice_number">Invoice number</label>
<input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" id="invoice_number" name="invoice_number" type="text" value="{{ old('invoice_number') }}" maxlength="100">
</div>
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="customer_code">Customer code</label>
<input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" id="customer_code" name="customer_code" type="text" value="{{ old('customer_code') }}" maxlength="100" placeholder="Auto-filled when selecting customer">
</div>
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="customer_name">Customer name</label>
<input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" id="customer_name" name="customer_name" type="text" value="{{ old('customer_name') }}" maxlength="255" placeholder="Auto-filled when selecting customer">
</div>
</div>
</div>

<h3 class="text-base font-semibold text-slate-800 dark:text-white pt-4">Line Items</h3>
<div class="overflow-x-auto -mx-4 sm:mx-0">
<table class="w-full text-left border-collapse min-w-[600px]">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Product</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase text-right w-24">Price</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase text-right w-20">Qty</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase text-right w-24">Amount</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase text-right w-24">VAT 15%</th>
<th class="px-4 py-3 w-12"></th>
</tr>
</thead>
<tbody id="line-items">
<tr class="line-item border-b border-slate-200 dark:border-slate-700">
<td class="px-4 py-3">
<select class="product-select w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 text-slate-900 dark:text-white" name="items[0][product_id]" required>
<option value="">Select product</option>
@foreach($products as $p)
<option value="{{ $p->id }}" data-price="{{ $p->selling_price ?? $p->cost_price }}" data-stock="{{ $p->stock_quantity }}">{{ $p->name }} ({{ $p->sku }})</option>
@endforeach
</select>
</td>
<td class="px-4 py-3">
<input class="price-input w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 text-right text-slate-900 dark:text-white" name="items[0][selling_price]" type="number" step="0.01" min="0" value="0" required>
</td>
<td class="px-4 py-3">
<input class="qty-input w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 text-right text-slate-900 dark:text-white" name="items[0][quantity]" type="number" min="1" value="1" required>
</td>
<td class="px-4 py-3 text-right">
<span class="amount-display font-mono text-sm text-slate-900 dark:text-white">0.00</span>
</td>
<td class="px-4 py-3 text-right">
<span class="vat-display font-mono text-sm text-slate-600 dark:text-slate-300">0.00</span>
</td>
<td class="px-4 py-3">
<button type="button" class="remove-row p-2 text-slate-400 hover:text-red-500 rounded-lg" title="Remove row"><span class="material-symbols-outlined text-[20px]">delete</span></button>
</td>
</tr>
</tbody>
</table>
</div>
<button type="button" id="add-row" class="mt-2 h-9 px-3 text-sm font-medium text-primary hover:bg-primary/5 rounded-lg border border-primary/30 transition-colors inline-flex items-center gap-2 whitespace-nowrap shrink-0">
<span class="material-symbols-outlined text-[18px] shrink-0">add</span>
<span>Add row</span>
</button>

<div class="flex justify-end pt-4 border-t border-slate-200 dark:border-slate-700 mt-6">
<div class="text-right space-y-1">
<p class="text-sm text-slate-600 dark:text-slate-400">Subtotal: <span id="subtotal-display" class="font-bold text-slate-900 dark:text-white">0.00</span></p>
<p class="text-sm text-slate-600 dark:text-slate-400">Total VAT (15%): <span id="tax-display" class="font-bold text-slate-900 dark:text-white">0.00</span></p>
<p class="text-lg font-bold text-slate-900 dark:text-white">Total: $<span id="total-display">0.00</span></p>
</div>
</div>
</div>

<div class="flex flex-wrap gap-3 mt-6">
<button type="submit" class="h-10 px-5 bg-primary hover:bg-blue-600 text-white text-sm font-bold rounded-lg transition-colors whitespace-nowrap">Save Sale</button>
<a href="{{ route('sales.index') }}" class="h-10 px-5 inline-flex items-center text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 rounded-lg border border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors whitespace-nowrap">Cancel</a>
</div>
</form>
</div>
</div>
<div class="mt-8 text-center text-xs text-slate-400 pb-4">© {{ date('Y') }} Nexus ERP Inc. All rights reserved.</div>
</div>
</main>

<script>
(function() {
    var customerSelect = document.getElementById('customer_select');
    var customerCodeInput = document.getElementById('customer_code');
    var customerNameInput = document.getElementById('customer_name');
    if (customerSelect && customerCodeInput && customerNameInput) {
        customerSelect.addEventListener('change', function() {
            var opt = this.options[this.selectedIndex];
            if (opt && opt.value) {
                customerCodeInput.value = opt.getAttribute('data-code') || '';
                customerNameInput.value = opt.getAttribute('data-name') || '';
            } else {
                customerCodeInput.value = '';
                customerNameInput.value = '';
            }
        });
        var code = customerCodeInput.value, name = customerNameInput.value;
        if (code || name) {
            for (var i = 0; i < customerSelect.options.length; i++) {
                var o = customerSelect.options[i];
                if (o.value && o.getAttribute('data-code') === code) { customerSelect.selectedIndex = i; break; }
            }
        }
    }

    // Strip empty rows before submit so validation only sees rows with products
    document.getElementById('sale-form')?.addEventListener('submit', function() {
        var idx = 0;
        document.querySelectorAll('.line-item').forEach(function(row) {
            var sel = row.querySelector('.product-select');
            if (!sel || !sel.value) {
                row.querySelectorAll('input, select').forEach(function(el) { el.removeAttribute('name'); });
            } else {
                var p = row.querySelector('.price-input'), q = row.querySelector('.qty-input');
                sel.setAttribute('name', 'items[' + idx + '][product_id]');
                if (p) p.setAttribute('name', 'items[' + idx + '][selling_price]');
                if (q) q.setAttribute('name', 'items[' + idx + '][quantity]');
                idx++;
            }
        });
    });

    const products = @json($products->mapWithKeys(fn($p) => [$p->id => ['price' => (float)($p->selling_price ?? $p->cost_price ?? 0), 'stock' => $p->stock_quantity]])->all());
    let rowIndex = 1;

    function updateRow(row) {
        const priceInput = row.querySelector('.price-input');
        const qtyInput = row.querySelector('.qty-input');
        const amountSpan = row.querySelector('.amount-display');
        const vatSpan = row.querySelector('.vat-display');
        const amount = (parseFloat(priceInput?.value || 0) || 0) * (parseInt(qtyInput?.value || 0, 10) || 0);
        const vat = amount * 0.15;
        if (amountSpan) amountSpan.textContent = amount.toFixed(2);
        if (vatSpan) vatSpan.textContent = vat.toFixed(2);
    }

    function updateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.line-item').forEach(row => {
            const priceInput = row.querySelector('.price-input');
            const qtyInput = row.querySelector('.qty-input');
            subtotal += (parseFloat(priceInput?.value || 0) || 0) * (parseInt(qtyInput?.value || 0, 10) || 0);
        });
        const tax = subtotal * 0.15;
        const total = subtotal + tax;
        const subEl = document.getElementById('subtotal-display');
        const taxEl = document.getElementById('tax-display');
        const totalEl = document.getElementById('total-display');
        if (subEl) subEl.textContent = subtotal.toFixed(2);
        if (taxEl) taxEl.textContent = tax.toFixed(2);
        if (totalEl) totalEl.textContent = total.toFixed(2);
    }

    function onRowChange() {
        document.querySelectorAll('.line-item').forEach(updateRow);
        updateTotals();
    }

    document.getElementById('add-row')?.addEventListener('click', function() {
        const tbody = document.getElementById('line-items');
        const firstRow = tbody.querySelector('.line-item');
        if (!firstRow) return;
        const newRow = firstRow.cloneNode(true);
        newRow.querySelector('.product-select').value = '';
        newRow.querySelector('.product-select').name = `items[${rowIndex}][product_id]`;
        newRow.querySelector('.price-input').value = '0';
        newRow.querySelector('.price-input').name = `items[${rowIndex}][selling_price]`;
        newRow.querySelector('.qty-input').value = '1';
        newRow.querySelector('.qty-input').name = `items[${rowIndex}][quantity]`;
        newRow.querySelector('.amount-display').textContent = '0.00';
        newRow.querySelector('.vat-display').textContent = '0.00';
        newRow.querySelectorAll('input, select').forEach(el => el.removeAttribute('required'));
        tbody.appendChild(newRow);
        rowIndex++;
        bindRowEvents();
    });

    function bindRowEvents() {
        document.querySelectorAll('.line-item').forEach(row => {
            row.querySelector('.product-select')?.addEventListener('change', function() {
                const pid = this.value;
                const prod = products[pid];
                if (prod) {
                    row.querySelector('.price-input').value = prod.price;
                }
                onRowChange();
            });
            row.querySelector('.price-input')?.addEventListener('input', onRowChange);
            row.querySelector('.qty-input')?.addEventListener('input', onRowChange);
        });
        document.querySelectorAll('.remove-row').forEach(btn => {
            btn.onclick = function() {
                const rows = document.querySelectorAll('.line-item');
                if (rows.length <= 1) return;
                this.closest('.line-item').remove();
                onRowChange();
            };
        });
    }

    bindRowEvents();
    onRowChange();
})();
</script>
</body>
</html>
