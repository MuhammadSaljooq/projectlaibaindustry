<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Statement - {{ Str::title(Str::lower($customer->customer_name)) }}</title>
    <style>
        @page { margin: 18mm 16mm; }
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
            padding-bottom: 16px;
            margin-bottom: 14px;
            border-bottom: 1px solid #e5e7eb;
        }
        .header-banner {
            margin-bottom: 0;
        }
        .doc-title-main {
            font-family: 'DejaVu Sans', 'Helvetica', sans-serif;
            font-size: 18pt;
            font-weight: bold;
            margin: 0 0 8px 0;
            letter-spacing: 0.2em;
            color: #0f172a;
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
        .statement-summary {
            border: 1px solid #d1d5db;
            border-top: 3px solid #1e3a5f;
            margin-bottom: 28px;
            background: #ffffff;
        }
        .issuer-card {
            background: #f8fafc;
            border: none;
            border-bottom: 1px solid #e2e8f0;
            padding: 18px 22px 20px 22px;
            text-align: left;
        }
        .issuer-block-name {
            font-size: 11.5pt;
            font-weight: bold;
            margin: 0 0 5px 0;
            line-height: 1.35;
            color: #0f172a;
            letter-spacing: -0.01em;
        }
        .issuer-block-name:last-of-type {
            margin-bottom: 12px;
        }
        .issuer-address {
            font-size: 9pt;
            color: #475569;
            margin: 0 0 6px 0;
            line-height: 1.55;
        }
        .issuer-address:last-of-type {
            margin-bottom: 14px;
        }
        .issuer-block-name + .issuer-address {
            margin-top: 0;
        }
        .issuer-detail-line {
            margin: 0 0 5px 0;
            font-size: 8.75pt;
            font-weight: normal;
            color: #334155;
            line-height: 1.5;
            text-align: left;
        }
        .issuer-detail-line strong {
            font-weight: 700;
            color: #0f172a;
        }
        .issuer-address + .issuer-detail-line {
            margin-top: 12px;
        }
        .info-table {
            width: 100%;
            table-layout: fixed;
            margin-bottom: 0;
            border-collapse: collapse;
            border-top: none;
        }
        .info-table td {
            width: 50%;
            vertical-align: top;
            padding: 20px 22px 22px 22px;
            background: #ffffff;
        }
        .info-table td.info-col-left {
            padding-right: 18px;
        }
        .info-table td.right {
            text-align: right;
            padding-left: 18px;
            padding-right: 22px;
            border-left: 1px solid #e2e8f0;
        }
        .info-block {
            margin-bottom: 22px;
        }
        .info-block:last-child {
            margin-bottom: 0;
        }
        .info-label {
            font-size: 7.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: #64748b;
            margin-bottom: 7px;
            line-height: 1.3;
        }
        .info-value {
            font-size: 11pt;
            font-weight: 600;
            color: #1e293b;
            line-height: 1.35;
        }
        .info-value-account-holder {
            font-size: 13pt;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.02em;
        }
        .info-value-balance {
            font-size: 13pt;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .info-value-balance--dr {
            color: #1d4ed8;
        }
        .info-value-balance--cr {
            color: #0f766e;
        }
        .info-value-balance--neutral {
            color: #0f172a;
        }
        .info-value-statement-date {
            font-size: 11pt;
            font-weight: 600;
            color: #1e293b;
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
            font-size: 8.5pt;
        }
        table.ledger td {
            border-bottom: 1px solid #e5e5e5;
            border-right: 1px solid #ececec;
            vertical-align: top;
            padding: 7px 6px;
        }
        table.ledger th {
            text-align: center;
            padding: 7px 8px;
            font-weight: bold;
            font-size: 7.5pt;
            text-transform: none;
            letter-spacing: 0;
            color: #333;
            border-bottom: 2px solid #1a1a1a;
            border-right: 1px solid #ececec;
            background: #f8f8f8;
            vertical-align: bottom;
            line-height: 1.25;
            word-wrap: normal;
            overflow-wrap: normal;
        }
        table.ledger th.ledger-col-date,
        table.ledger td.ledger-col-date {
            width: 12%;
            padding-left: 6px;
            padding-right: 10px;
        }
        table.ledger th.ledger-col-desc,
        table.ledger td.ledger-col-desc {
            width: 24%;
            padding-left: 4px;
            padding-right: 8px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        table.ledger th.ledger-col-invoice,
        table.ledger td.ledger-col-invoice {
            width: 12%;
            padding-left: 4px;
            padding-right: 8px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        table.ledger th.ledger-col-days,
        table.ledger td.ledger-col-days {
            width: 11%;
            padding-left: 4px;
            padding-right: 8px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        table.ledger th.ledger-col-debit,
        table.ledger td.ledger-col-debit {
            width: 12%;
            padding-left: 8px;
            padding-right: 8px;
        }
        table.ledger th.ledger-col-credit,
        table.ledger td.ledger-col-credit {
            width: 12%;
            padding-left: 8px;
            padding-right: 8px;
        }
        table.ledger th.ledger-col-balance,
        table.ledger td.ledger-col-balance {
            width: 17%;
            padding-left: 8px;
            padding-right: 6px;
        }
        table.ledger td.ledger-code {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 8pt;
        }
        table.ledger thead th {
            text-align: center;
        }
        table.ledger td.amt {
            text-align: center;
            font-variant-numeric: tabular-nums;
        }
        table.ledger td {
            text-align: center;
        }
        table.ledger td.ledger-col-date {
            white-space: nowrap;
        }
        table.ledger td.ledger-col-debit,
        table.ledger td.ledger-col-credit,
        table.ledger td.ledger-col-balance {
            /* Allow large amounts to wrap instead of overflowing into adjacent columns. */
            white-space: normal;
            word-break: break-word;
            overflow-wrap: anywhere;
            line-height: 1.25;
        }
        table.ledger th:last-child,
        table.ledger td:last-child {
            border-right: none;
        }
        table.ledger tr.total-row td.ledger-totals-label {
            text-align: center;
            font-weight: bold;
            padding-left: 6px;
            padding-right: 10px;
        }
        table.ledger thead {
            display: table-header-group;
        }
        table.ledger tr.opening {
            background: #f5f5f5;
        }
        table.ledger tr.total-row {
            background: #e8e8e8;
            font-weight: bold;
            border-top: 2px solid #1a1a1a;
            page-break-inside: avoid;
        }
        .debit { color: #0f172a; font-weight: 600; }
        .credit { color: #0d6b0d; font-weight: 600; }
        .balance-dr { color: #1d4ed8; font-weight: 600; }
        .balance-cr { color: #0f766e; font-weight: 600; }
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
</div>

<div class="statement-summary">
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
        <p class="issuer-detail-line"><strong>CR</strong> {{ $company['registration'] }}</p>
        @endif
        @if(filled($company['vat_number'] ?? null))
        <p class="issuer-detail-line"><strong>VAT</strong> {{ $company['vat_number'] }}</p>
        @endif
        @if(filled($company['phone'] ?? null))
        <p class="issuer-detail-line"><strong>Mobile</strong> {{ $company['phone'] }}</p>
        <p class="issuer-detail-line"><strong>WhatsApp</strong> {{ $company['phone'] }}</p>
        @endif
        @if(filled($company['email'] ?? null))
        <p class="issuer-detail-line"><strong>Email</strong> {{ $company['email'] }}</p>
        @endif
    </div>

<table class="info-table">
    <tr>
        <td class="info-col-left">
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
            @php
                $signedClosingBalance = ($closingBalance < 0 ? '-' : '').($currencySymbol ?? '$').' '.number_format(abs($closingBalance), 2);
            @endphp
            <div class="info-block">
                <div class="info-label">Statement Date</div>
                <div class="info-value info-value-statement-date">{{ now()->format('F j, Y') }}</div>
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
                    {{ $signedClosingBalance }}
                    @if($closingBalance > 0) DR @elseif($closingBalance < 0) CR @endif
                </div>
            </div>
        </td>
    </tr>
</table>
</div>

<div class="statement-title">Transaction History</div>

<table class="ledger">
    <colgroup>
        <col style="width:12%;">
        <col style="width:24%;">
        <col style="width:12%;">
        <col style="width:11%;">
        <col style="width:12%;">
        <col style="width:12%;">
        <col style="width:17%;">
    </colgroup>
    <thead>
        <tr>
            <th class="ledger-col-date">Date</th>
            <th class="ledger-col-desc">Description</th>
            <th class="ledger-col-invoice">Invoice #</th>
            <th class="ledger-col-days">Days out.</th>
            <th class="ledger-col-debit">Debit</th>
            <th class="ledger-col-credit">Credit</th>
            <th class="ledger-col-balance">Balance</th>
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
            <td class="ledger-col-invoice ledger-code">—</td>
            <td class="amt dash ledger-col-days">—</td>
            <td class="amt dash ledger-col-debit">—</td>
            <td class="amt dash ledger-col-credit">—</td>
            <td class="amt ledger-col-balance {{ $openingBalance > 0 ? 'balance-dr' : ($openingBalance < 0 ? 'balance-cr' : '') }}">
                {{ $currencySymbol ?? '$' }} {{ number_format($openingBalance, 2) }}
            </td>
        </tr>

        @foreach($ledgerRows as $row)
        <tr>
            <td class="ledger-col-date">{{ $row['date']->format('d/m/Y') }}</td>
            <td class="ledger-col-desc">{{ $row['description'] }}</td>
            <td class="ledger-col-invoice ledger-code">{{ $row['invoice_number'] ?: '—' }}</td>
            <td class="amt ledger-col-days">
                @if(($row['source_type'] ?? '') === 'sale')
                    @php
                        $statementShowsReceivable = ($closingBalance ?? 0) > 0.009;
                        $invKey = trim((string) ($row['invoice_number'] ?? ''));
                        $fifoPaid = $invKey !== '' && ! empty(($fifoPaidInvoiceNumbers ?? [])[$invKey] ?? false);
                        $showDaysOutstanding = $statementShowsReceivable && ! $fifoPaid;
                    @endphp
                    @if($showDaysOutstanding)
                        <span class="debit">{{ (int) $row['date']->diffInDays(now(), true) }} days</span>
                    @else
                        <span class="credit" style="font-size: 7pt; font-weight: 700;">PAID</span>
                    @endif
                @else
                    <span class="dash">—</span>
                @endif
            </td>
            <td class="amt ledger-col-debit {{ $row['debit'] > 0 ? 'debit' : 'dash' }}">
                {{ $row['debit'] > 0 ? ($currencySymbol ?? '$') . ' ' . number_format($row['debit'], 2) : '—' }}
            </td>
            <td class="amt ledger-col-credit {{ $row['credit'] > 0 ? 'credit' : 'dash' }}">
                {{ $row['credit'] > 0 ? ($currencySymbol ?? '$') . ' ' . number_format($row['credit'], 2) : '—' }}
            </td>
            <td class="amt ledger-col-balance {{ $row['running_balance'] > 0 ? 'balance-dr' : ($row['running_balance'] < 0 ? 'balance-cr' : '') }}">
                {{ $currencySymbol ?? '$' }} {{ number_format($row['running_balance'], 2) }}@if($row['running_balance'] > 0)&nbsp;DR @elseif($row['running_balance'] < 0)&nbsp;CR @endif
            </td>
        </tr>
        @endforeach

        @if(count($ledgerRows) > 0)
        <tr class="total-row">
            <td colspan="4" class="ledger-totals-label">Totals</td>
            <td class="amt debit ledger-col-debit">{{ $currencySymbol ?? '$' }} {{ number_format($totalDebit, 2) }}</td>
            <td class="amt credit ledger-col-credit">{{ $currencySymbol ?? '$' }} {{ number_format($totalCredit, 2) }}</td>
            <td class="amt ledger-col-balance {{ $closingBalance > 0 ? 'balance-dr' : 'balance-cr' }}">
                {{ $signedClosingBalance }}@if($closingBalance > 0)&nbsp;DR @elseif($closingBalance < 0)&nbsp;CR @endif
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
