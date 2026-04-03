{{--
    resources/views/quotations/_form.blade.php
    Used by: quotations/create.blade.php and quotations/edit.blade.php
--}}

@php
    $isEdit = isset($quotation) && $quotation->exists;
    $formRoute = $isEdit
        ? route('quotations.update', $quotation)
        : route('quotations.store');
    $method = $isEdit ? 'PUT' : 'POST';
    $quotationInitialItems = $isEdit
        ? $quotation->items->map(fn ($i) => [
            'description' => $i->description,
            'quantity' => (float) $i->quantity,
            'unit_price' => (float) $i->unit_price,
            'tax_rate' => (float) $i->tax_rate,
        ])->values()->all()
        : [];
@endphp

<form action="{{ $formRoute }}" method="POST" x-data="quotationForm()" x-init="init()">
    @csrf
    @method($method)

    {{-- ── Errors ──────────────────────────────────────────────────────── --}}
    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 border border-red-300 p-4 text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════
         QUOTATION DETAILS
    ══════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-5">
        <h2 class="text-sm font-bold text-[#1a237e] uppercase tracking-wide mb-4">
            Quotation Details / تفاصيل الاقتباس
        </h2>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="col-span-2 md:col-span-2">
                <label class="block text-xs font-semibold text-gray-600 mb-1">
                    Quotation No. / رقم الاقتباس <span class="text-red-500">*</span>
                </label>
                <input type="text" name="quotation_number"
                       value="{{ old('quotation_number', $isEdit ? $quotation->quotation_number : '') }}"
                       placeholder="e.g. QT-2026-0001"
                       maxlength="40"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a237e] focus:border-transparent"
                       required
                       @if($isEdit) autocomplete="off" @endif>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">
                    Quotation Date <span class="text-red-500">*</span>
                </label>
                <input type="date" name="quotation_date"
                       value="{{ old('quotation_date', $isEdit ? $quotation->quotation_date->format('Y-m-d') : today()->format('Y-m-d')) }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a237e] focus:border-transparent" required>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Expiration Date</label>
                <input type="date" name="expiration_date"
                       value="{{ old('expiration_date', $isEdit ? optional($quotation->expiration_date)->format('Y-m-d') : '') }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a237e] focus:border-transparent">
            </div>
            <div class="col-span-2">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Salesperson / مندوب مبيعات</label>
                <input type="text" name="salesperson"
                       value="{{ old('salesperson', $isEdit ? $quotation->salesperson : '') }}"
                       placeholder="Name of salesperson"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a237e] focus:border-transparent">
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         CUSTOMER DETAILS
    ══════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-5">
        <h2 class="text-sm font-bold text-[#1a237e] uppercase tracking-wide mb-4">
            Customer Details / بيانات العميل
        </h2>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">
                    Customer Name / اسم العميل <span class="text-red-500">*</span>
                </label>
                <input type="text" name="customer_name"
                       value="{{ old('customer_name', $isEdit ? $quotation->customer_name : '') }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a237e] focus:border-transparent" required>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">VAT Number / الرقم الضريبي</label>
                <input type="text" name="customer_vat_number"
                       value="{{ old('customer_vat_number', $isEdit ? $quotation->customer_vat_number : '') }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a237e] focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">CR Number / السجل التجاري</label>
                <input type="text" name="customer_cr_number"
                       value="{{ old('customer_cr_number', $isEdit ? $quotation->customer_cr_number : '') }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a237e] focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Phone / رقم الهاتف</label>
                <input type="text" name="customer_phone"
                       value="{{ old('customer_phone', $isEdit ? $quotation->customer_phone : '') }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a237e] focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Email / البريد الإلكتروني</label>
                <input type="email" name="customer_email"
                       value="{{ old('customer_email', $isEdit ? $quotation->customer_email : '') }}"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a237e] focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Address / العنوان</label>
                <textarea name="customer_address" rows="2"
                          class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a237e] focus:border-transparent">{{ old('customer_address', $isEdit ? $quotation->customer_address : '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         ITEMS
    ══════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-bold text-[#1a237e] uppercase tracking-wide">
                Items / البنود
            </h2>
            <button type="button" @click="addRow()"
                    class="text-sm bg-[#1a237e] text-white px-3 py-1.5 rounded hover:bg-[#283593] transition-colors">
                + Add Row
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-[#1a237e] text-white">
                        <th class="px-3 py-2 text-center w-10">#</th>
                        <th class="px-3 py-2 text-left">Description / الوصف</th>
                        <th class="px-3 py-2 text-center w-24">Qty / كمية</th>
                        <th class="px-3 py-2 text-center w-28">Unit Price / سعر الوحدة</th>
                        <th class="px-3 py-2 text-center w-24">Tax % / الضريبة</th>
                        <th class="px-3 py-2 text-right w-28">Amount / المبلغ</th>
                        <th class="w-8"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, index) in rows" :key="row.id">
                        <tr :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                            <td class="px-3 py-2 text-center text-gray-500 text-xs" x-text="index + 1"></td>
                            <td class="px-2 py-1.5">
                                <input type="text"
                                       :name="'items[' + index + '][description]'"
                                       x-model="row.description"
                                       placeholder="Item description"
                                       class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-1 focus:ring-[#1a237e]" required>
                            </td>
                            <td class="px-2 py-1.5">
                                <input type="number"
                                       :name="'items[' + index + '][quantity]'"
                                       x-model.number="row.quantity"
                                       @input="calcRow(row)"
                                       min="0.001" step="0.001"
                                       class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-center focus:ring-1 focus:ring-[#1a237e]" required>
                            </td>
                            <td class="px-2 py-1.5">
                                <input type="number"
                                       :name="'items[' + index + '][unit_price]'"
                                       x-model.number="row.unit_price"
                                       @input="calcRow(row)"
                                       min="0" step="0.01"
                                       class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-center focus:ring-1 focus:ring-[#1a237e]" required>
                            </td>
                            <td class="px-2 py-1.5">
                                <input type="number"
                                       :name="'items[' + index + '][tax_rate]'"
                                       x-model.number="row.tax_rate"
                                       @input="calcRow(row)"
                                       min="0" max="100" step="0.01"
                                       class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-center focus:ring-1 focus:ring-[#1a237e]" required>
                            </td>
                            <td class="px-3 py-2 text-right font-semibold text-[#1a237e]"
                                x-text="'SAR ' + row.amount.toFixed(2)"></td>
                            <td class="px-2 py-1.5 text-center">
                                <button type="button" @click="removeRow(index)"
                                        x-show="rows.length > 1"
                                        class="text-red-400 hover:text-red-600 text-lg leading-none">&times;</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Live totals --}}
        <div class="flex justify-end mt-4">
            <div class="w-72 space-y-1 text-sm">
                <div class="flex justify-between text-gray-600">
                    <span>Untaxed Amount</span>
                    <span x-text="'SAR ' + untaxed.toFixed(2)"></span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>VAT Taxes</span>
                    <span x-text="'SAR ' + vatTotal.toFixed(2)"></span>
                </div>
                <div class="flex justify-between font-bold text-[#1a237e] border-t pt-1 text-base">
                    <span>Total / المجموع</span>
                    <span x-text="'SAR ' + grandTotal.toFixed(2)"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Notes ───────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-5">
        <label class="block text-xs font-semibold text-gray-600 mb-1">Notes (internal)</label>
        <textarea name="notes" rows="3"
                  class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-[#1a237e] focus:border-transparent"
                  placeholder="Optional internal notes…">{{ old('notes', $isEdit ? $quotation->notes : '') }}</textarea>
    </div>

    {{-- ── Actions ─────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-3">
        <button type="submit"
                class="bg-[#1a237e] text-white px-6 py-2.5 rounded-lg font-semibold text-sm hover:bg-[#283593] transition-colors">
            {{ $isEdit ? 'Update Quotation' : 'Create Quotation' }}
        </button>
        <a href="{{ route('quotations.index') }}"
           class="px-6 py-2.5 rounded-lg border border-gray-300 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
            Cancel
        </a>
    </div>
</form>

{{-- Alpine.js component for live row calculations --}}
<script>
function quotationForm() {
    return {
        rows: [],
        nextId: 0,

        get untaxed() {
            return this.rows.reduce((s, r) => s + (r.quantity * r.unit_price), 0);
        },
        get vatTotal() {
            return this.rows.reduce((s, r) => s + r.taxAmount, 0);
        },
        get grandTotal() {
            return this.untaxed + this.vatTotal;
        },

        init() {
            const existing = @json($quotationInitialItems);

            if (existing.length > 0) {
                existing.forEach(item => this.addRow(item));
            } else {
                this.addRow();
            }
        },

        addRow(data = {}) {
            const row = {
                id:          this.nextId++,
                description: data.description  ?? '',
                quantity:    data.quantity      ?? 1,
                unit_price:  data.unit_price    ?? 0,
                tax_rate:    data.tax_rate       ?? 15,
                taxAmount:   0,
                amount:      0,
            };
            this.calcRow(row);
            this.rows.push(row);
        },

        removeRow(index) {
            if (this.rows.length > 1) this.rows.splice(index, 1);
        },

        calcRow(row) {
            const subtotal   = (row.quantity || 0) * (row.unit_price || 0);
            row.taxAmount    = subtotal * ((row.tax_rate || 0) / 100);
            row.amount       = subtotal + row.taxAmount;
        },
    };
}
</script>
