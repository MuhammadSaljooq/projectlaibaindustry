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
<p class="text-[10px] text-[#586064] mt-1 m-0">Sum of debits (payments to supplier)</p>
</div>
<div class="border border-[#ABB3B7] bg-white p-4">
<p class="st-label st-label--primary mb-2">Remaining balance</p>
<p class="text-xl font-black font-mono tabular-nums {{ abs($ledgerBalance) > 0.009 ? 'text-[#9F403D]' : 'text-[#5E5E5E]' }}">{{ $currencySymbol }} {{ number_format($ledgerBalance, 2) }}</p>
<p class="text-[10px] text-[#586064] mt-1 m-0">Purchases minus payments (amount still owed)</p>
</div>
</div>

<div class="st-paper flex flex-col border border-[#ABB3B7] bg-white min-h-[200px]">
<div class="overflow-x-auto w-full">
<table class="w-full text-left border-collapse min-w-[720px]">
<thead>
<tr class="st-thead">
<th class="st-th px-4 py-3 whitespace-nowrap">Date</th>
<th class="st-th px-4 py-3">Description</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Debit</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Credit</th>
<th class="st-th px-4 py-3 text-right whitespace-nowrap">Balance</th>
</tr>
</thead>
<tbody>
@forelse($ledgerEntries as $e)
<tr class="st-tr">
<td class="st-td px-4 py-3 text-sm whitespace-nowrap text-[#586064]">{{ format_display_date($e->date) }}</td>
<td class="st-td px-4 py-3 text-sm text-[#2B3437]">{{ $e->description }}</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums text-[#586064]">@if((float)$e->debit > 0){{ $currencySymbol }} {{ number_format($e->debit, 2) }}@else—@endif</td>
<td class="st-td px-4 py-3 text-sm font-mono text-right tabular-nums text-[#586064]">@if((float)$e->credit > 0){{ $currencySymbol }} {{ number_format($e->credit, 2) }}@else—@endif</td>
<td class="st-td px-4 py-3 text-sm font-mono font-bold text-right tabular-nums {{ ($e->running_balance ?? 0) > 0.009 ? 'text-[#9F403D]' : 'text-[#5E5E5E]' }}">{{ $currencySymbol }} {{ number_format($e->running_balance ?? 0, 2) }}</td>
</tr>
@empty
<tr>
<td colspan="5" class="px-6 py-14 text-center text-sm text-[#586064]">No ledger entries yet. Post international purchases with this supplier or record payments on those lines.</td>
</tr>
@endforelse
</tbody>
</table>
</div>
</div>
</div>
