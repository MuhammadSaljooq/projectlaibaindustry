<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vendor Statement - {{ $supplier->name }}</title>
    <style>
        @page { margin: 25mm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', 'Helvetica', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
        }
        .header { border-bottom: 2px solid #1a1a1a; padding-bottom: 16px; margin-bottom: 24px; }
        .title { font-size: 20pt; font-weight: bold; margin: 0 0 10px 0; letter-spacing: 0.08em; text-align: center; }
        .subtitle { margin: 0; color: #444; text-align: left; font-size: 9pt; }
        .issuer-card { background: #f6f6f6; border: 1px solid #ddd; padding: 12px 14px; margin-top: 14px; }
        .issuer-line { margin: 0 0 4px 0; }
        .issuer-line:last-child { margin-bottom: 0; }
        .info-table { width: 100%; table-layout: fixed; margin-bottom: 24px; border-collapse: collapse; }
        .info-table td { width: 50%; vertical-align: top; padding: 0 12px 0 0; }
        .info-table td.right { text-align: right; padding: 0 0 0 16px; border-left: 1px solid #e5e5e5; }
        .label { font-size: 8pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #555; margin-bottom: 2px; }
        .value { font-size: 11pt; font-weight: 600; }
        .value-balance { font-size: 14pt; font-weight: 700; }
        .statement-title { font-size: 12pt; font-weight: bold; margin: 18px 0 12px 0; padding-bottom: 6px; border-bottom: 1px solid #ccc; }
        .aging-box { border: 1px solid #d8d8d8; margin: 4px 0 18px 0; }
        .aging-head { background: #f3f3f3; border-bottom: 1px solid #d8d8d8; padding: 8px 10px; font-size: 8pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.08em; color: #4b4b4b; }
        .aging-summary { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .aging-summary td { width: 50%; padding: 10px; vertical-align: top; }
        .aging-label { font-size: 8pt; text-transform: uppercase; letter-spacing: 0.08em; color: #555; margin-bottom: 4px; }
        .aging-value { font-size: 12pt; font-weight: 700; margin: 0; }
        .aging-sub { font-size: 8pt; color: #666; margin-top: 3px; }
        table.aging-lines { width: 100%; border-collapse: collapse; font-size: 8.8pt; table-layout: fixed; }
        table.aging-lines th { text-align: left; padding: 7px 8px; font-size: 7.8pt; text-transform: uppercase; border-top: 1px solid #d8d8d8; border-bottom: 1px solid #d8d8d8; background: #fafafa; }
        table.aging-lines td { padding: 7px 8px; border-bottom: 1px solid #ededed; }
        table.aging-lines th.amt, table.aging-lines td.amt { text-align: right; font-variant-numeric: tabular-nums; }
        table.ledger { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 9pt; table-layout: fixed; }
        table.ledger th { text-align: left; padding: 10px 8px; font-size: 8pt; text-transform: uppercase; border-bottom: 2px solid #1a1a1a; background: #f8f8f8; }
        table.ledger td { padding: 9px 8px; border-bottom: 1px solid #e5e5e5; vertical-align: top; }
        table.ledger th.amt, table.ledger td.amt { text-align: right; font-variant-numeric: tabular-nums; }
        table.ledger thead { display: table-header-group; }
        table.ledger tbody tr { page-break-inside: avoid; }
        .muted { color: #777; }
        .totals { background: #e8e8e8; font-weight: bold; border-top: 2px solid #1a1a1a; }
        .empty { text-align: center; color: #666; padding: 24px 0; margin: 0; font-size: 9pt; }
        .footer { margin-top: 28px; padding-top: 14px; border-top: 1px solid #ccc; font-size: 8pt; color: #666; text-align: center; }
    </style>
</head>
<body>
@php $company = \App\Support\StatementCompany::normalize($company ?? config('company')); @endphp
<div class="header">
    <h1 class="title">VENDOR STATEMENT</h1>
    <p class="subtitle">Generated {{ now()->format('F j, Y \a\t g:i A') }}</p>
    <div class="issuer-card">
        @if(filled($company['name'] ?? null))<p class="issuer-line"><strong>{{ $company['name'] }}</strong></p>@endif
        @foreach($company['address_lines'] ?? [] as $line)
            @if(filled($line))<p class="issuer-line">{{ $line }}</p>@endif
        @endforeach
        @if(filled($company['registration'] ?? null))<p class="issuer-line"><strong>CR:</strong> {{ $company['registration'] }}</p>@endif
        @if(filled($company['vat_number'] ?? null))<p class="issuer-line"><strong>VAT:</strong> {{ $company['vat_number'] }}</p>@endif
        @if(filled($company['phone'] ?? null))<p class="issuer-line"><strong>{{ $company['phone_label'] ?? 'Phone' }}:</strong> {{ $company['phone'] }}</p>@endif
        @if(filled($company['email'] ?? null))<p class="issuer-line"><strong>Email:</strong> {{ $company['email'] }}</p>@endif
    </div>
</div>

<table class="info-table">
    <tr>
        <td>
            <div class="label">Vendor</div>
            <div class="value">{{ $supplier->name }}</div>
            <div style="height: 8px;"></div>
            <div class="label">Country</div>
            <div class="value">{{ $supplier->country ?: '—' }}</div>
        </td>
        <td class="right">
            <div class="label">Remaining Balance</div>
            <div class="value-balance">{{ $currencySymbol }} {{ number_format($ledgerBalance, 2) }}</div>
            <div class="muted">Purchases minus payments</div>
        </td>
    </tr>
</table>

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
        $oldestOutstandingDate = null;
        foreach ($outstandingPayables as $ap) {
            if ($oldestOutstandingDate === null || $ap['date'] < $oldestOutstandingDate) {
                $oldestOutstandingDate = $ap['date'];
            }
        }
        $oldestOutstandingDays = $oldestOutstandingDate ? (int) $oldestOutstandingDate->diffInDays(now(), true) : null;
    @endphp
    <div class="statement-title">Invoice Aging</div>
    <div class="aging-box">
        <div class="aging-head">
            {{ $outstandingPayables->count() }} outstanding invoice{{ $outstandingPayables->count() === 1 ? '' : 's' }}
        </div>
        <table class="aging-summary">
            <tr>
                <td>
                    <div class="aging-label">Oldest outstanding invoice</div>
                    <p class="aging-value">{{ $oldestOutstandingDays !== null ? $oldestOutstandingDays . ' days' : '—' }}</p>
                    <div class="aging-sub">Since {{ $oldestOutstandingDate ? $oldestOutstandingDate->format('d/m/Y') : '—' }}</div>
                </td>
                <td style="text-align: right;">
                    <div class="aging-label">Total outstanding</div>
                    <p class="aging-value">{{ $currencySymbol }} {{ number_format($outstandingPayables->sum('outstanding'), 2) }}</p>
                </td>
            </tr>
        </table>
        <table class="aging-lines">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Invoice date</th>
                    <th class="amt">Days outstanding</th>
                    <th class="amt">Outstanding</th>
                </tr>
            </thead>
            <tbody>
            @foreach($outstandingPayables as $ap)
                <tr>
                    <td>{{ $ap['invoice_number'] ?: '—' }}</td>
                    <td>{{ format_display_date($ap['date']) }}</td>
                    <td class="amt">{{ (int) $ap['date']->diffInDays(now(), true) }} days</td>
                    <td class="amt">{{ $currencySymbol }} {{ number_format($ap['outstanding'], 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif

<div class="statement-title">Ledger Transactions</div>
<table class="ledger">
    <thead>
        <tr>
            <th>Date</th>
            <th>Description</th>
            <th>REFRENCE</th>
            <th class="amt">Days Outstanding</th>
            <th class="amt">Credit</th>
            <th class="amt">Debit</th>
            <th class="amt">Balance</th>
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
        <tr>
            <td>{{ format_display_date($e->date) }}</td>
            <td>{{ $e->description }}</td>
            <td>{{ $displayReference ?: '—' }}</td>
            <td class="amt">
                @if(($e->source_type ?? null) === 'international_purchase_order')
                    @php
                        $orderId = (int) ($e->source_id ?? 0);
                        $isOutstanding = $orderId > 0 && (($outstandingByOrderId[$orderId] ?? 0) > 0.009);
                    @endphp
                    @if($isOutstanding && $e->date)
                        {{ (int) $e->date->diffInDays(now(), true) }} days
                    @else
                        Paid
                    @endif
                @else
                    —
                @endif
            </td>
            <td class="amt">@if((float) $e->credit > 0){{ $currencySymbol }} {{ number_format($e->credit, 2) }}@else — @endif</td>
            <td class="amt">@if((float) $e->debit > 0){{ $currencySymbol }} {{ number_format($e->debit, 2) }}@else — @endif</td>
            <td class="amt">{{ $currencySymbol }} {{ number_format($e->running_balance ?? 0, 2) }}</td>
        </tr>
    @empty
        <tr><td colspan="7" class="empty">No ledger entries yet.</td></tr>
    @endforelse
    @if($ledgerEntries->count() > 0)
        <tr class="totals">
            <td colspan="4">Totals</td>
            <td class="amt">{{ $currencySymbol }} {{ number_format($ledgerTotalCredit, 2) }}</td>
            <td class="amt">{{ $currencySymbol }} {{ number_format($ledgerTotalPaid, 2) }}</td>
            <td class="amt">{{ $currencySymbol }} {{ number_format($ledgerBalance, 2) }}</td>
        </tr>
    @endif
    </tbody>
</table>

<div class="footer">
    This statement is computer-generated and requires no signature.
</div>
</body>
</html>
