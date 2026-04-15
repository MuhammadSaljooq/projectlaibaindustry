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

<div class="statement-title">Ledger Transactions</div>
<table class="ledger">
    <thead>
        <tr>
            <th>Date</th>
            <th>Description</th>
            <th>Invoice / Ref</th>
            <th class="amt">Debit</th>
            <th class="amt">Credit</th>
            <th class="amt">Balance</th>
        </tr>
    </thead>
    <tbody>
    @forelse($ledgerEntries as $e)
        <tr>
            <td>{{ format_display_date($e->date) }}</td>
            <td>{{ $e->description }}</td>
            <td>{{ $e->reference ?: '—' }}</td>
            <td class="amt">@if((float) $e->debit > 0){{ $currencySymbol }} {{ number_format($e->debit, 2) }}@else — @endif</td>
            <td class="amt">@if((float) $e->credit > 0){{ $currencySymbol }} {{ number_format($e->credit, 2) }}@else — @endif</td>
            <td class="amt">{{ $currencySymbol }} {{ number_format($e->running_balance ?? 0, 2) }}</td>
        </tr>
    @empty
        <tr><td colspan="6" class="empty">No ledger entries yet.</td></tr>
    @endforelse
    @if($ledgerEntries->count() > 0)
        <tr class="totals">
            <td colspan="3">Totals</td>
            <td class="amt">{{ $currencySymbol }} {{ number_format($ledgerTotalPaid, 2) }}</td>
            <td class="amt">{{ $currencySymbol }} {{ number_format($ledgerTotalCredit, 2) }}</td>
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
