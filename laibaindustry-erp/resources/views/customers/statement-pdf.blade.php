<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Statement - {{ Str::title(Str::lower($customer->customer_name)) }}</title>
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
        .header {
            border-bottom: 2px solid #1a1a1a;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .header-banner {
            margin-bottom: 14px;
        }
        .doc-title-main {
            font-family: 'DejaVu Sans', 'Helvetica', sans-serif;
            font-size: 20pt;
            font-weight: bold;
            margin: 0 0 10px 0;
            letter-spacing: 0.08em;
            color: #111;
            text-align: center;
        }
        .header-name-line {
            font-size: 11.5pt;
            font-weight: bold;
            margin: 0 0 6px 0;
            line-height: 1.35;
            color: #1a1a1a;
            text-align: left;
        }
        .header-generated {
            font-size: 8.5pt;
            color: #555;
            margin: 10px 0 0 0;
            text-align: left;
        }
        .issuer-card {
            background: #f6f6f6;
            border: 1px solid #ddd;
            padding: 12px 14px;
            text-align: left;
        }
        .issuer-block-name {
            font-size: 11pt;
            font-weight: bold;
            margin: 0 0 4px 0;
            line-height: 1.35;
            color: #111;
        }
        .issuer-block-name:last-of-type {
            margin-bottom: 10px;
        }
        .issuer-address {
            font-size: 9pt;
            color: #333;
            margin: 0 0 5px 0;
            line-height: 1.45;
        }
        .issuer-address:last-of-type {
            margin-bottom: 12px;
        }
        .issuer-block-name + .issuer-address {
            margin-top: 0;
        }
        .issuer-detail-line {
            margin: 5px 0 0 0;
            font-size: 9.5pt;
            font-weight: 600;
            color: #1a1a1a;
            line-height: 1.45;
            text-align: left;
        }
        .issuer-address + .issuer-detail-line {
            margin-top: 10px;
        }
        .info-table {
            width: 100%;
            table-layout: fixed;
            margin-bottom: 28px;
            border-collapse: collapse;
        }
        .info-table td {
            width: 50%;
            vertical-align: top;
            padding: 0 12px 0 0;
        }
        .info-table td.right {
            text-align: right;
            padding: 0 0 0 16px;
            border-left: 1px solid #e5e5e5;
        }
        .info-block {
            margin-bottom: 12px;
        }
        .info-label {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #555;
            margin-bottom: 2px;
        }
        .info-value {
            font-size: 11pt;
            font-weight: 600;
        }
        .info-value-account-holder {
            font-size: 14pt;
            font-weight: 700;
        }
        .info-value-balance {
            font-size: 14pt;
            font-weight: 700;
        }
        .info-value-balance--dr {
            color: #b45300;
        }
        .info-value-balance--cr {
            color: #0d6b0d;
        }
        .info-value-balance--neutral {
            color: #1a1a1a;
        }
        .statement-title {
            font-size: 12pt;
            font-weight: bold;
            margin: 20px 0 12px 0;
            padding-bottom: 6px;
            border-bottom: 1px solid #ccc;
        }
        table.ledger {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 24px;
            font-size: 9pt;
        }
        table.ledger th {
            text-align: left;
            padding: 10px 8px;
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #333;
            border-bottom: 2px solid #1a1a1a;
            background: #f8f8f8;
        }
        table.ledger th.ledger-col-date,
        table.ledger td.ledger-col-date {
            width: 12%;
        }
        table.ledger th.ledger-col-desc,
        table.ledger td.ledger-col-desc {
            width: 24%;
            word-wrap: break-word;
        }
        table.ledger th.ledger-col-ref,
        table.ledger td.ledger-col-ref {
            width: 12%;
        }
        table.ledger th.ledger-col-invoice,
        table.ledger td.ledger-col-invoice {
            width: 14%;
            word-wrap: break-word;
        }
        table.ledger th.ledger-col-debit,
        table.ledger td.ledger-col-debit {
            width: 12%;
        }
        table.ledger th.ledger-col-credit,
        table.ledger td.ledger-col-credit {
            width: 12%;
        }
        table.ledger th.ledger-col-balance,
        table.ledger td.ledger-col-balance {
            width: 14%;
        }
        table.ledger td.ledger-code {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 8.5pt;
        }
        table.ledger .ledger-cell-time {
            font-size: 8pt;
            color: #666;
        }
        table.ledger th.amt, table.ledger td.amt {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }
        table.ledger td {
            padding: 9px 8px;
            border-bottom: 1px solid #e5e5e5;
            vertical-align: top;
        }
        table.ledger tr.total-row td.ledger-totals-label {
            text-align: left;
            font-weight: bold;
        }
        table.ledger thead {
            display: table-header-group;
        }
        table.ledger tbody tr {
            page-break-inside: avoid;
        }
        table.ledger tr.opening {
            background: #f5f5f5;
        }
        table.ledger tr.total-row {
            background: #e8e8e8;
            font-weight: bold;
            border-top: 2px solid #1a1a1a;
        }
        .debit { color: #1a1a1a; font-weight: 600; }
        .credit { color: #0d6b0d; font-weight: 600; }
        .balance-dr { color: #b45300; font-weight: 600; }
        .balance-cr { color: #0d6b0d; font-weight: 600; }
        .dash { color: #999; }
        .footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #ccc;
            font-size: 8pt;
            color: #666;
            text-align: center;
        }
        .footer-note {
            margin: 0 0 8px 0;
        }
        .footer-company {
            margin: 0 0 4px 0;
            font-weight: 600;
            color: #333;
            font-size: 8.5pt;
        }
        .footer-line {
            margin: 0;
            line-height: 1.4;
        }
        .footer-company + .footer-line {
            margin-top: 6px;
        }
        .footer-line + .footer-line {
            margin-top: 4px;
        }
        .footer-copyright {
            margin: 10px 0 0 0;
        }
        .ledger-empty {
            text-align: center;
            color: #666;
            padding: 24px 0;
            margin: 0;
            font-size: 9pt;
        }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
@php
    $company = \App\Support\StatementCompany::normalize($company ?? config('company'));
    $statementFiltered = $statementFiltered ?? false;
@endphp

<div class="header">
    <div class="header-banner">
        <h1 class="doc-title-main">ACCOUNT STATEMENT</h1>
        @if(filled($company['name'] ?? null))
        <p class="header-name-line">{{ $company['name'] }}</p>
        @endif
        <p class="header-generated">Generated {{ now()->format('F j, Y \a\t g:i A') }}@if($statementFiltered && isset($periodFrom, $periodTo)) · Period {{ $periodFrom->format('j M Y') }} – {{ $periodTo->format('j M Y') }}@endif</p>
    </div>
    <div class="issuer-card">
        @foreach($company['pdf_block_name_lines'] ?? [] as $line)
        @if(filled($line))
        <p class="issuer-block-name">{{ $line }}</p>
        @endif
        @endforeach
        @foreach($company['address_lines'] ?? [] as $addrLine)
        @if(filled($addrLine))
        <p class="issuer-address">{{ $addrLine }}</p>
        @endif
        @endforeach
        @if(filled($company['registration'] ?? null))
        <p class="issuer-detail-line">CR: {{ $company['registration'] }}</p>
        @endif
        @if(filled($company['vat_number'] ?? null))
        <p class="issuer-detail-line">VAT: {{ $company['vat_number'] }}</p>
        @endif
        @if(filled($company['phone'] ?? null))
        <p class="issuer-detail-line">Mobile: {{ $company['phone'] }}</p>
        <p class="issuer-detail-line">Whatsapp: {{ $company['phone'] }}</p>
        @endif
        @if(filled($company['email'] ?? null))
        <p class="issuer-detail-line">Email:  {{ $company['email'] }}</p>
        @endif
    </div>
</div>

<table class="info-table">
    <tr>
        <td>
            <div class="info-block">
                <div class="info-label">Account Holder</div>
                <div class="info-value info-value-account-holder">{{ Str::title(Str::lower($customer->customer_name)) }}</div>
            </div>
            <div class="info-block">
                <div class="info-label">Account Number</div>
                <div class="info-value">{{ $customer->customer_code ?: '—' }}</div>
            </div>
            @if($customer->address)
            <div class="info-block">
                <div class="info-label">Address</div>
                <div class="info-value">{{ $customer->address }}</div>
            </div>
            @endif
        </td>
        <td class="right">
            <div class="info-block">
                <div class="info-label">Statement Date</div>
                <div class="info-value">{{ now()->format('F j, Y') }}</div>
            </div>
            <div class="info-block">
                <div class="info-label">Closing Balance</div>
                <div @class([
                    'info-value',
                    'info-value-balance',
                    'info-value-balance--dr' => $closingBalance > 0,
                    'info-value-balance--cr' => $closingBalance < 0,
                    'info-value-balance--neutral' => $closingBalance == 0,
                ])>
                    {{ $currencySymbol ?? '$' }} {{ number_format(abs($closingBalance), 2) }}
                    @if($closingBalance > 0) DR @elseif($closingBalance < 0) CR @endif
                </div>
            </div>
        </td>
    </tr>
</table>

<div class="statement-title">Transaction History</div>

<table class="ledger">
    <thead>
        <tr>
            <th class="ledger-col-date">Date</th>
            <th class="ledger-col-desc">Description</th>
            <th class="ledger-col-ref">Customer code</th>
            <th class="ledger-col-invoice">Invoice #</th>
            <th class="amt ledger-col-debit">Debit</th>
            <th class="amt ledger-col-credit">Credit</th>
            <th class="amt ledger-col-balance">Balance</th>
        </tr>
    </thead>
    <tbody>
        {{-- Opening balance / balance brought forward --}}
        <tr class="opening">
            <td class="ledger-col-date">
                @if($statementFiltered && isset($periodFrom))
                    {{ $periodFrom->format('d/m/Y') }}
                @else
                    {{ $customer->opening_balance_date ? $customer->opening_balance_date->format('d/m/Y') : '—' }}
                @endif
            </td>
            <td class="ledger-col-desc">{{ $statementFiltered ? 'Balance brought forward' : 'Opening balance' }}</td>
            <td class="ledger-col-ref ledger-code">{{ $customer->customer_code ?: '—' }}</td>
            <td class="ledger-col-invoice ledger-code">—</td>
            <td class="amt dash ledger-col-debit">—</td>
            <td class="amt dash ledger-col-credit">—</td>
            <td class="amt ledger-col-balance {{ $openingBalance > 0 ? 'balance-dr' : ($openingBalance < 0 ? 'balance-cr' : '') }}">
                {{ $currencySymbol ?? '$' }} {{ number_format($openingBalance, 2) }}
            </td>
        </tr>

        @foreach($ledgerRows as $row)
        <tr>
            <td class="ledger-col-date">{{ $row['date']->format('d/m/Y') }}<br><span class="ledger-cell-time">{{ $row['date']->format('H:i') }}</span></td>
            <td class="ledger-col-desc">{{ $row['description'] }}</td>
            <td class="ledger-col-ref ledger-code">{{ $row['customer_code'] ?: '—' }}</td>
            <td class="ledger-col-invoice ledger-code">{{ $row['invoice_number'] ?: '—' }}</td>
            <td class="amt ledger-col-debit {{ $row['debit'] > 0 ? 'debit' : 'dash' }}">
                {{ $row['debit'] > 0 ? ($currencySymbol ?? '$') . ' ' . number_format($row['debit'], 2) : '—' }}
            </td>
            <td class="amt ledger-col-credit {{ $row['credit'] > 0 ? 'credit' : 'dash' }}">
                {{ $row['credit'] > 0 ? ($currencySymbol ?? '$') . ' ' . number_format($row['credit'], 2) : '—' }}
            </td>
            <td class="amt ledger-col-balance {{ $row['running_balance'] > 0 ? 'balance-dr' : ($row['running_balance'] < 0 ? 'balance-cr' : '') }}">
                {{ $currencySymbol ?? '$' }} {{ number_format($row['running_balance'], 2) }}
                @if($row['running_balance'] > 0) DR @elseif($row['running_balance'] < 0) CR @endif
            </td>
        </tr>
        @endforeach

        @if(count($ledgerRows) > 0)
        <tr class="total-row">
            <td colspan="4" class="ledger-totals-label">Totals</td>
            <td class="amt debit ledger-col-debit">{{ $currencySymbol ?? '$' }} {{ number_format($totalDebit, 2) }}</td>
            <td class="amt credit ledger-col-credit">{{ $currencySymbol ?? '$' }} {{ number_format($totalCredit, 2) }}</td>
            <td class="amt ledger-col-balance {{ $closingBalance > 0 ? 'balance-dr' : 'balance-cr' }}">
                {{ $currencySymbol ?? '$' }} {{ number_format(abs($closingBalance), 2) }}
                {{ $closingBalance > 0 ? 'DR' : ($closingBalance < 0 ? 'CR' : '') }}
            </td>
        </tr>
        @endif
    </tbody>
</table>

@if(count($ledgerRows) == 0)
<p class="ledger-empty">{{ $statementFiltered ? 'No transactions in this period.' : 'No transactions recorded for this account.' }}</p>
@endif

<div class="footer">
    <p class="footer-note">This statement is computer-generated and requires no signature.</p>
    @if(filled($company['name'] ?? null))
    <p class="footer-company">{{ $company['name'] }}</p>
    @endif
    @if(filled($company['registration'] ?? null) || filled($company['vat_number'] ?? null))
    <p class="footer-line">
        @if(filled($company['registration'] ?? null))<span><strong>CR</strong> {{ $company['registration'] }}</span>@endif
        @if(filled($company['registration'] ?? null) && filled($company['vat_number'] ?? null))<span> · </span>@endif
        @if(filled($company['vat_number'] ?? null))<span><strong>VAT</strong> {{ $company['vat_number'] }}</span>@endif
    </p>
    @endif
    @if(filled($company['phone'] ?? null) || filled($company['email'] ?? null))
    <p class="footer-line">
        @if(filled($company['phone'] ?? null))<span>{{ $company['phone_label'] ?? 'Phone' }}: {{ $company['phone'] }}</span>@endif
        @if(filled($company['phone'] ?? null) && filled($company['email'] ?? null))<span> · </span>@endif
        @if(filled($company['email'] ?? null))<span>{{ $company['email'] }}</span>@endif
    </p>
    @endif
    <p class="footer-copyright">© {{ now()->year }} {{ $company['name'] ?? config('app.name') }}. All rights reserved.</p>
</div>

</body>
</html>
