<div id="supplier-account-ledger" class="scroll-mt-8">
<div class="flex flex-col gap-2 mb-4">
<h2 class="text-xl font-black uppercase tracking-tighter text-[#2B3437]">Account ledger</h2>
<p class="text-xs text-[#586064]">Credit increases amount owed (international purchases). Debit reduces it (payments). Balance is cumulative amount owed.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
<div class="border border-[#ABB3B7] bg-white p-4">
<p class="st-label st-label--primary mb-2">Total purchases</p>
<p class="text-xl font-black font-mono tabular-nums text-[#5E5E5E]">{{ $currencySymbol }} {{ number_format($ledgerTotalCredit, 2) }}</p>
<p class="text-[10px] text-[#586064] mt-1 m-0">Sum of credits (international purchases)</p>
</div>
<div class="border border-[#ABB3B7] bg-white p-4">
<p class="st-label st-label--primary mb-2">Total paid</p>
<p class="text-xl font-black font-mono tabular-nums text-[#5E5E5E]">{{ $currencySymbol }} {{ number_format($ledgerTotalPaid, 2) }}</p>
<p class="text-[10px] text-[#586064] mt-1 m-0">Sum of debits (payments to vendor)</p>
</div>
<div class="border border-[#ABB3B7] bg-white p-4">
<p class="st-label st-label--primary mb-2">Remaining balance</p>
<p class="text-xl font-black font-mono tabular-nums {{ abs($ledgerBalance) > 0.009 ? 'text-[#9F403D]' : 'text-[#5E5E5E]' }}">{{ $currencySymbol }} {{ number_format($ledgerBalance, 2) }}</p>
<p class="text-[10px] text-[#586064] mt-1 m-0">Purchases minus payments (amount still owed)</p>
</div>
</div>

@php
    $outstandingPayables = $outstandingPayables ?? collect();
    $outstandingByOrderId = [];
    foreach ($outstandingPayables as $ap) {
        $oid = (int) ($ap['order_id'] ?? 0);
        if ($oid > 0) {
            $outstandingByOrderId[$oid] = (float) ($ap['outstanding'] ?? 0);
        }
    }
@endphp
@if($outstandingPayables->isNotEmpty())
@php
    $agingNow = now();
    $agingOldestDate = null;
    foreach ($outstandingPayables as $ap) {
        if ($agingOldestDate === null || $ap['date'] < $agingOldestDate) {
            $agingOldestDate = $ap['date'];
        }
    }
@endphp
<div class="border border-[#5E5E5E] bg-white mb-4" data-oldest-date="{{ $agingOldestDate?->toIso8601String() }}">
    <div class="px-5 py-3 border-b border-[#ABB3B7] bg-[#EAEFF1] flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[#5E5E5E] text-[18px]">schedule</span>
            <h3 class="text-xs font-bold uppercase tracking-widest text-[#586064]">Invoice Aging</h3>
        </div>
        <p class="text-[10px] font-bold uppercase tracking-widest text-[#586064]">
            {{ $outstandingPayables->count() }} outstanding invoice{{ $outstandingPayables->count() === 1 ? '' : 's' }}
            · auto-updating
        </p>
    </div>

    <div class="px-5 py-4 border-b border-[#ABB3B7] flex flex-col sm:flex-row sm:items-center gap-4">
        <div>
            <p class="st-label mb-1">Oldest outstanding invoice</p>
            <p class="text-3xl font-black font-mono tabular-nums text-[#5E5E5E]">
                <span id="supplier-aging-oldest-days">—</span>
            </p>
            <p class="text-[10px] text-[#586064] mt-0.5 uppercase tracking-wider">
                since {{ $agingOldestDate ? \Carbon\Carbon::parse($agingOldestDate)->format('d/m/Y') : '—' }}
            </p>
        </div>
        <div class="sm:ml-auto text-right">
            <p class="st-label mb-1">Total outstanding</p>
            <p class="text-2xl font-black font-mono tabular-nums text-[#9F403D]">
                {{ $currencySymbol }} {{ number_format($outstandingPayables->sum('outstanding'), 2) }}
            </p>
        </div>
    </div>

    <div class="border-t border-[#ABB3B7] overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[520px]">
            <thead>
            <tr class="st-thead">
                <th class="st-th px-4 py-2 whitespace-nowrap">Invoice #</th>
                <th class="st-th px-4 py-2 whitespace-nowrap">Invoice date</th>
                <th class="st-th px-4 py-2 whitespace-nowrap">Days outstanding</th>
                <th class="st-th px-4 py-2 text-right whitespace-nowrap">Outstanding</th>
            </tr>
            </thead>
            <tbody>
            @foreach($outstandingPayables as $ap)
            <tr class="st-tr">
                <td class="st-td px-4 py-2 text-sm font-mono text-[#586064]">{{ $ap['invoice_number'] ?: '—' }}</td>
                <td class="st-td px-4 py-2 text-sm text-[#586064] whitespace-nowrap">{{ format_display_date($ap['date']) }}</td>
                <td class="st-td px-4 py-2">
                    <span class="text-sm font-bold font-mono tabular-nums text-[#5E5E5E]" data-supplier-invoice-date="{{ $ap['date']->toIso8601String() }}">
                        {{ (int) $ap['date']->diffInDays($agingNow, true) }} days
                    </span>
                </td>
                <td class="st-td px-4 py-2 text-sm font-bold font-mono text-right tabular-nums text-[#9F403D]">
                    {{ $currencySymbol }} {{ number_format($ap['outstanding'], 2) }}
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[200px]">
<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[760px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3">Description</th>
<th class="st-th px-4 py-3 whitespace-nowrap">REFRENCE</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Days Outstanding</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Credit</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Debit</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Balance</th>
</tr>
</thead>
<tbody>
@forelse($ledgerEntries as $e)
@php
    $displayReference = $e->reference;
    if (($e->source_type ?? null) === 'international_payable_payment') {
        if (filled($e->notes)) {
            $displayReference = $e->notes;
        } elseif (is_string($displayReference) && preg_match('/\bIPP-\d+\b/i', $displayReference)) {
            $displayReference = null;
        }
    }
@endphp
<tr class="st-tr">
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ format_display_date($e->date) }}</td>
<td class="st-td px-4 py-3 text-sm text-[#2B3437]">{{ $e->description }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-[#586064] whitespace-nowrap">{{ $displayReference ?: '—' }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums whitespace-nowrap">
    @if(($e->source_type ?? null) === 'international_purchase_order')
        @php
            $orderId = (int) ($e->source_id ?? 0);
            $isOutstanding = $orderId > 0 && (($outstandingByOrderId[$orderId] ?? 0) > 0.009);
        @endphp
        @if($isOutstanding && $e->date)
            <span class="font-bold text-[#5E5E5E]">{{ (int) $e->date->diffInDays(now(), true) }} days</span>
        @else
            <span class="text-[10px] font-bold uppercase tracking-wider border border-[#2B8A3E] bg-[#F4FBF6] text-[#2B8A3E] px-2 py-0.5">Paid</span>
        @endif
    @else
        <span class="text-[#ABB3B7]">—</span>
    @endif
</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums text-[#586064]">@if((float)$e->credit > 0){{ $currencySymbol }} {{ number_format($e->credit, 2) }}@else—@endif</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums text-[#586064]">@if((float)$e->debit > 0){{ $currencySymbol }} {{ number_format($e->debit, 2) }}@else—@endif</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right tabular-nums {{ ($e->running_balance ?? 0) > 0.009 ? 'text-[#9F403D]' : 'text-[#5E5E5E]' }}">{{ $currencySymbol }} {{ number_format($e->running_balance ?? 0, 2) }}</td>
</tr>
@empty
<tr>
<td colspan="7" class="px-6 py-14 text-center text-sm text-[#586064]">No ledger entries yet. Post international purchases with this vendor or record payments on those lines.</td>
</tr>
@endforelse
</tbody>
</table>
</div>
</div>

<script>
    (() => {
        const root = document.getElementById('supplier-account-ledger');
        if (!root) return;

        const oldestDateIso = root.querySelector('[data-oldest-date]')?.getAttribute('data-oldest-date');
        const oldestEl = document.getElementById('supplier-aging-oldest-days');
        const dayNodes = Array.from(root.querySelectorAll('[data-supplier-invoice-date]'));

        const diffDays = (iso) => {
            if (!iso) return null;
            const dt = new Date(iso);
            if (Number.isNaN(dt.getTime())) return null;
            const now = new Date();
            const ms = now.getTime() - dt.getTime();
            return Math.max(0, Math.floor(ms / 86400000));
        };

        const updateAging = () => {
            const oldestDays = diffDays(oldestDateIso);
            if (oldestEl) {
                oldestEl.textContent = oldestDays === null ? '—' : `${oldestDays} days`;
            }

            dayNodes.forEach((node) => {
                const iso = node.getAttribute('data-supplier-invoice-date');
                const days = diffDays(iso);
                node.textContent = days === null ? '—' : `${days} days`;
            });
        };

        updateAging();
        setInterval(updateAging, 60000);
    })();
</script>
</div>
