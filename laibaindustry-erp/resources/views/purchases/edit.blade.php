<!DOCTYPE html>
<html lang="en">
<head>
@include('partials.frontend-head', ['title' => 'Edit Purchase - ERP'])
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white h-screen flex overflow-hidden">
@include('products.partials.sidebar', ['activeNav' => 'purchases'])
<main class="flex-1 flex flex-col h-full overflow-hidden relative">
<header class="h-16 bg-white dark:bg-black border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-6 shrink-0 z-10">
<div class="flex items-center gap-4">
<button class="md:hidden p-2 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" type="button" data-sidebar-toggle aria-label="Toggle menu">
<span class="material-symbols-outlined">menu</span>
</button>
<a href="{{ route('purchases.show', $purchase) }}" class="inline-flex items-center gap-2 text-slate-600 dark:text-slate-300 hover:text-black dark:hover:text-white transition-colors">
<span class="material-symbols-outlined">arrow_back</span>
<span class="text-xl font-bold text-slate-800 dark:text-white hidden sm:block">Purchase {{ $purchase->invoice_number ?: '#' . $purchase->id }}</span>
</a>
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
<div class="bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
<form method="POST" action="{{ route('purchases.update', $purchase) }}" id="purchase-form">
@csrf
@method('PUT')
<div class="space-y-6">
<h3 class="text-base font-semibold text-slate-800 dark:text-white">Purchase Details</h3>
<div class="space-y-4">
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="customer_select">Supplier / Customer</label>
<select class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" id="customer_select">
<option value="">Enter manually below</option>
@foreach($customers ?? [] as $c)
<option value="{{ $c->id }}" data-code="{{ e($c->customer_code) }}" data-name="{{ e($c->customer_name) }}" {{ (old('customer_code') ?: $purchase->customer_code) == $c->customer_code ? 'selected' : '' }}>{{ $c->customer_name }} ({{ $c->customer_code }})</option>
@endforeach
</select>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="date">Date <span class="text-red-500">*</span></label>
<input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" id="date" name="date" type="datetime-local" value="{{ old('date', $purchase->date->format('Y-m-d\TH:i')) }}" required>
</div>
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="invoice_number">Invoice number</label>
<input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" id="invoice_number" name="invoice_number" type="text" value="{{ old('invoice_number', $purchase->invoice_number) }}" maxlength="100">
</div>
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="customer_code">Customer code</label>
<input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" id="customer_code" name="customer_code" type="text" value="{{ old('customer_code', $purchase->customer_code) }}" maxlength="100">
</div>
<div>
<label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" for="customer_name">Customer name</label>
<input class="w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2.5 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary" id="customer_name" name="customer_name" type="text" value="{{ old('customer_name', $purchase->customer_name) }}" maxlength="255">
</div>
</div>
</div>

<h3 class="text-base font-semibold text-slate-800 dark:text-white pt-4">Line Items</h3>
<div class="overflow-x-auto -mx-4 sm:mx-0">
<table class="w-full text-left border-collapse min-w-[600px]">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">Product name</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase text-right w-28">Price</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase text-right w-20">Qty</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase text-right w-24">Amount</th>
<th class="px-4 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase text-right w-24">VAT 15%</th>
<th class="px-4 py-3 w-12"></th>
</tr>
</thead>
<tbody id="line-items">
@php
$itemsForForm = old('items');
if ($itemsForForm === null) {
    $itemsForForm = $purchase->items->map(fn($i) => ['product_name' => $i->product_name, 'price' => $i->price, 'quantity' => $i->quantity])->values()->all();
}
if (empty($itemsForForm)) {
    $itemsForForm = [['product_name' => '', 'price' => 0, 'quantity' => 1]];
}
@endphp
@foreach($itemsForForm as $idx => $row)
@php
$price = (float)($row['price'] ?? 0);
$qty = (int)($row['quantity'] ?? 1);
$amount = round($price * $qty, 2);
$vat = round($amount * 0.15, 2);
@endphp
<tr class="line-item border-b border-slate-200 dark:border-slate-700">
<td class="px-4 py-3">
<input class="product-name w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 text-slate-900 dark:text-white" name="items[{{ $idx }}][product_name]" type="text" placeholder="Product name" value="{{ $row['product_name'] ?? '' }}" required>
</td>
<td class="px-4 py-3">
<input class="price-input w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 text-right text-slate-900 dark:text-white" name="items[{{ $idx }}][price]" type="number" step="0.01" min="0" value="{{ $price }}" required>
</td>
<td class="px-4 py-3">
<input class="qty-input w-full h-10 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 text-right text-slate-900 dark:text-white" name="items[{{ $idx }}][quantity]" type="number" min="1" value="{{ $qty }}" required>
</td>
<td class="px-4 py-3 text-right">
<span class="amount-display font-mono text-sm text-slate-900 dark:text-white">{{ number_format($amount, 2) }}</span>
</td>
<td class="px-4 py-3 text-right">
<span class="vat-display font-mono text-sm text-slate-600 dark:text-slate-300">{{ number_format($vat, 2) }}</span>
</td>
<td class="px-4 py-3">
<button type="button" class="remove-row p-2 text-slate-400 hover:text-red-500 rounded-lg" title="Remove row"><span class="material-symbols-outlined text-[20px]">delete</span></button>
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
<button type="button" id="add-row" class="mt-2 h-9 px-3 text-sm font-medium text-black dark:text-white hover:bg-black/5 dark:hover:bg-white/5 rounded-lg border border-black/30 dark:border-white/30 transition-colors inline-flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">add</span>
Add row
</button>

<div class="pt-6 flex flex-wrap items-center gap-4">
<button type="submit" class="h-10 px-6 bg-black hover:bg-gray-800 text-white text-sm font-bold rounded-lg inline-flex items-center justify-center gap-2 shadow-sm">
<span class="material-symbols-outlined text-[20px]">save</span>
Update Purchase
</button>
<a href="{{ route('purchases.show', $purchase) }}" class="h-10 px-4 inline-flex items-center justify-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600">
Cancel
</a>
<form method="POST" action="{{ route('purchases.destroy', $purchase) }}" class="inline-flex" onsubmit="return confirm('Are you sure you want to delete this purchase?');">
@csrf
@method('DELETE')
<button type="submit" class="h-10 px-4 inline-flex items-center justify-center gap-2 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
<span class="material-symbols-outlined text-[18px]">delete</span>
Delete
</button>
</form>
</div>
</div>
</form>
</div>
</div>
</div>
</main>
<script>
(function() {
var form = document.getElementById('purchase-form');
var tbody = document.getElementById('line-items');
var addBtn = document.getElementById('add-row');
var customerSelect = document.getElementById('customer_select');
var customerCode = document.getElementById('customer_code');
var customerName = document.getElementById('customer_name');

if (customerSelect && customerCode && customerName) {
customerSelect.addEventListener('change', function() {
var opt = this.options[this.selectedIndex];
if (opt.value && opt.dataset.code !== undefined) {
customerCode.value = opt.dataset.code || '';
customerName.value = opt.dataset.name || '';
}
});
}

function updateRow(row) {
var priceIn = row.querySelector('.price-input');
var qtyIn = row.querySelector('.qty-input');
var price = parseFloat(priceIn?.value) || 0;
var qty = parseInt(qtyIn?.value, 10) || 0;
var amount = price * qty;
var vat = Math.round(amount * 0.15 * 100) / 100;
row.querySelector('.amount-display').textContent = amount.toFixed(2);
row.querySelector('.vat-display').textContent = vat.toFixed(2);
}

function bindRow(row) {
row.querySelectorAll('.price-input, .qty-input').forEach(function(inp) {
inp.addEventListener('input', function() { updateRow(row); });
});
row.querySelector('.remove-row')?.addEventListener('click', function() {
if (tbody.querySelectorAll('.line-item').length > 1) row.remove();
});
}

function reindexRows() {
tbody.querySelectorAll('.line-item').forEach(function(row, i) {
row.querySelectorAll('[name]').forEach(function(inp) {
inp.name = inp.name.replace(/items\[\d+\]/, 'items[' + i + ']');
});
});
}

addBtn?.addEventListener('click', function() {
var first = tbody.querySelector('.line-item');
var clone = first.cloneNode(true);
clone.querySelector('.product-name').value = '';
clone.querySelector('.price-input').value = '0';
clone.querySelector('.qty-input').value = '1';
clone.querySelector('.amount-display').textContent = '0.00';
clone.querySelector('.vat-display').textContent = '0.00';
tbody.appendChild(clone);
bindRow(clone);
reindexRows();
});

tbody.querySelectorAll('.line-item').forEach(bindRow);
tbody.querySelectorAll('.line-item').forEach(updateRow);
})();
</script>
</body>
</html>
