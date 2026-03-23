<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'New Purchase - ERP'])
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
@include('purchases.partials.stitch-design')
</head>
<body class="bg-[#F8F9FA] text-[#2B3437] h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'purchases'])
<main class="purchases-stitch flex-1 flex flex-col h-full overflow-hidden relative bg-[#F8F9FA]">
<header class="h-16 shrink-0 z-10 flex items-center justify-between px-6 border-b border-[#ABB3B7] bg-white">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-[#586064] hover:bg-[#F1F4F6] rounded-none border border-transparent hover:border-[#ABB3B7]" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined text-[#2B3437]">menu</span>
</button>
<h2 class="text-lg font-bold text-[#2B3437] hidden sm:block tracking-tight uppercase">New Purchase</h2>
</div>
</header>
<div class="flex-1 overflow-y-auto p-6 md:p-8 scroll-smooth">
<div class="max-w-[1400px] mx-auto flex flex-col gap-8">

<div class="flex flex-col gap-4">
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
<div class="flex flex-col gap-1 min-w-0">
<p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#586064]">RECORD_ENTRY_01</p>
<h1 class="text-3xl md:text-4xl font-black uppercase tracking-tighter text-[#2B3437] leading-none">New Purchase</h1>
</div>
<a class="st-btn-secondary h-10 px-4 inline-flex items-center gap-2 shrink-0" href="{{ route('purchases.index') }}">
<span class="material-symbols-outlined text-[18px]">arrow_back</span>
Back to ledger
</a>
</div>
<div class="h-0.5 w-full bg-[#5E5E5E]" role="presentation"></div>
</div>

<div class="sm:hidden">
<p class="st-label">Module</p>
<p class="text-xl font-black uppercase tracking-tight text-[#2B3437]">New Purchase</p>
</div>

@if (session('error'))
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D]">
{{ session('error') }}
</div>
@endif
@if ($errors->any())
<div class="border border-[#9F403D] bg-white px-4 py-3 text-sm text-[#9F403D]">
<ul class="list-disc list-inside space-y-0.5">
@foreach ($errors->all() as $err)
<li>{{ $err }}</li>
@endforeach
</ul>
</div>
@endif

<div class="max-w-4xl">
<div class="st-paper border border-[#ABB3B7] p-6 md:p-8 bg-white">
<form method="POST" action="{{ route('purchases.store') }}" id="purchase-form" novalidate>
@csrf
<div class="space-y-8">

<div>
<p class="st-label mb-3">Purchase details</p>
<div class="space-y-4">
<div>
<label class="st-label block mb-2" for="customer_select">Customer / Supplier</label>
<select class="st-select w-full px-4 py-2 text-sm" id="customer_select">
<option value="">Add new supplier</option>
@foreach($customers as $c)
<option value="{{ $c->id }}" data-code="{{ e($c->customer_code) }}" data-name="{{ e($c->customer_name) }}">{{ $c->customer_name }} ({{ $c->customer_code }})</option>
@endforeach
</select>
<p class="mt-2 text-xs text-[#586064]">Select an existing record to auto-fill, or enter details below</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
<div>
<label class="st-label block mb-2" for="date">Date <span class="text-[#9F403D]">*</span></label>
<input class="st-input w-full h-10 px-4 text-sm" id="date" name="date" type="datetime-local" value="{{ old('date', now()->format('Y-m-d\TH:i')) }}" required>
</div>
<div>
<label class="st-label block mb-2" for="invoice_number">Invoice Number <span class="text-[#9F403D]">*</span></label>
<input class="st-input w-full h-10 px-4 text-sm" id="invoice_number" name="invoice_number" type="text" value="{{ old('invoice_number') }}" maxlength="100" required placeholder="e.g. INV-2024-001">
</div>
<div>
<label class="st-label block mb-2" for="customer_code">Customer Code</label>
<input class="st-input w-full h-10 px-4 text-sm" id="customer_code" name="customer_code" type="text" value="{{ old('customer_code') }}" maxlength="100" placeholder="Auto-filled when selecting supplier">
</div>
<div>
<label class="st-label block mb-2" for="customer_name">Customer Name</label>
<input class="st-input w-full h-10 px-4 text-sm" id="customer_name" name="customer_name" type="text" value="{{ old('customer_name') }}" maxlength="255" placeholder="Auto-filled when selecting supplier">
</div>
</div>
</div>
</div>

<div>
<p class="st-label mb-3">Line items</p>
<div class="overflow-x-auto -mx-2 sm:mx-0 border border-[#ABB3B7]">
<table class="w-full text-left border-collapse min-w-[640px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3">Product Name</th>
<th class="st-th px-4 py-3 text-right w-28">Price</th>
<th class="st-th px-4 py-3 text-right w-20">Qty</th>
<th class="st-th px-4 py-3 text-right w-28">Amount</th>
<th class="st-th px-4 py-3 text-right w-28">VAT 15%</th>
<th class="st-th px-4 py-3 text-right w-28">Subtotal</th>
<th class="st-th px-4 py-3 w-12"></th>
</tr>
</thead>
<tbody id="line-items">
<tr class="line-item st-tr">
<td class="st-td px-4 py-3">
<input class="product-name-input st-input w-full h-10 px-3 text-sm" name="items[0][product_name]" type="text" placeholder="Enter product name" required>
</td>
<td class="st-td px-4 py-3">
<input class="price-input st-input w-full h-10 px-3 text-sm text-right" name="items[0][price]" type="number" step="0.01" min="0" value="0" required>
</td>
<td class="st-td px-4 py-3">
<input class="qty-input st-input w-full h-10 px-3 text-sm text-right" name="items[0][quantity]" type="number" min="1" value="1" required>
</td>
<td class="st-td px-4 py-3 text-right">
<span class="amount-display font-mono text-sm text-[#2B3437]">0.00</span>
</td>
<td class="st-td px-4 py-3 text-right">
<span class="vat-display font-mono text-sm text-[#586064]">0.00</span>
</td>
<td class="st-td px-4 py-3 text-right">
<span class="subtotal-display font-mono text-sm font-bold text-[#5E5E5E]">0.00</span>
</td>
<td class="st-td px-4 py-3">
<button type="button" class="remove-row p-2 text-[#586064] hover:text-[#9F403D] border border-transparent hover:border-[#ABB3B7]" title="Remove row">
<span class="material-symbols-outlined text-[20px]">delete</span>
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
<div class="text-right space-y-2 min-w-[240px]">
<div class="flex items-center justify-between gap-8 text-sm text-[#586064]">
<span>Subtotal (excl. VAT)</span>
<span id="subtotal-display" class="font-bold text-[#2B3437] font-mono tabular-nums">0.00</span>
</div>
<div class="flex items-center justify-between gap-8 text-sm text-[#586064]">
<span>Total VAT (15%)</span>
<span id="vat-display" class="font-bold text-[#2B3437] font-mono tabular-nums">0.00</span>
</div>
<div class="flex items-center justify-between gap-8 text-base font-black text-[#2B3437] border-t border-[#ABB3B7] pt-2">
<span>Total</span>
<span class="font-mono tabular-nums">$<span id="total-display">0.00</span></span>
</div>
</div>
</div>
</div>

</div>

<div class="flex flex-wrap gap-3 mt-8">
<button type="submit" class="st-btn-primary h-10 px-6">Save Purchase</button>
<a href="{{ route('purchases.index') }}" class="st-btn-secondary h-10 px-6 inline-flex items-center">Cancel</a>
</div>
</form>
</div>
</div>
<p class="text-center text-[10px] uppercase tracking-widest text-[#586064] pt-6 pb-2">© 2026 Laiba Safety. All rights reserved.</p>
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
        var existingCode = customerCodeInput.value;
        if (existingCode) {
            for (var i = 0; i < customerSelect.options.length; i++) {
                if (customerSelect.options[i].getAttribute('data-code') === existingCode) {
                    customerSelect.selectedIndex = i;
                    break;
                }
            }
        }
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

    var rowIndex = 1;

    function calcRow(row) {
        var price    = parseFloat(row.querySelector('.price-input')?.value) || 0;
        var qty      = parseInt(row.querySelector('.qty-input')?.value, 10) || 0;
        var amount   = price * qty;
        var vat      = amount * 0.15;
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
            totalVat      += amount * 0.15;
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
