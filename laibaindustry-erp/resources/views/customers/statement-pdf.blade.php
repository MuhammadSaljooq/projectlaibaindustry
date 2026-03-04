<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Statement - {{ $customer->customer_name }}</title>
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
        .header h1 {
            font-family: 'DejaVu Sans', 'Helvetica', sans-serif;
            font-size: 18pt;
            font-weight: bold;
            margin: 0 0 4px 0;
            letter-spacing: 0.5px;
        }
        .header .subtitle {
            font-size: 9pt;
            color: #444;
            margin: 0;
        }
        .info-table {
            width: 100%;
            margin-bottom: 28px;
            border-collapse: collapse;
        }
        .info-table td {
            width: 50%;
            vertical-align: top;
            padding: 0 8px 0 0;
        }
        .info-table td.right {
            text-align: right;
            padding: 0 0 0 8px;
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
        .statement-title {
            font-size: 12pt;
            font-weight: bold;
            margin: 20px 0 12px 0;
            padding-bottom: 6px;
            border-bottom: 1px solid #ccc;
        }
        table.ledger {
            width: 100%;
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
        table.ledger th.amt, table.ledger td.amt {
            text-align: right;
        }
        table.ledger td {
            padding: 9px 8px;
            border-bottom: 1px solid #e5e5e5;
            vertical-align: top;
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
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

<div class="header">
    <h1>ACCOUNT STATEMENT</h1>
    <p class="subtitle">{{ config('app.name', 'Laiba Safety') }} — Generated {{ now()->format('F j, Y \a\t g:i A') }}</p>
</div>

<table class="info-table">
    <tr>
        <td>
            <div class="info-block">
                <div class="info-label">Account Holder</div>
                <div class="info-value">{{ $customer->customer_name }}</div>
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
                <div class="info-value" style="font-size: 14pt; {{ $closingBalance > 0 ? 'color: #b45300;' : ($closingBalance < 0 ? 'color: #0d6b0d;' : '') }}">
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
            <th style="width: 14%;">Date</th>
            <th style="width: 28%;">Description</th>
            <th style="width: 18%;">Reference</th>
            <th class="amt" style="width: 13%;">Debit</th>
            <th class="amt" style="width: 13%;">Credit</th>
            <th class="amt" style="width: 14%;">Balance</th>
        </tr>
    </thead>
    <tbody>
        {{-- Opening balance --}}
        <tr class="opening">
            <td>{{ $customer->opening_balance_date ? $customer->opening_balance_date->format('d/m/Y') : '—' }}</td>
            <td>Opening Balance</td>
            <td>—</td>
            <td class="amt dash">—</td>
            <td class="amt dash">—</td>
            <td class="amt {{ $openingBalance > 0 ? 'balance-dr' : ($openingBalance < 0 ? 'balance-cr' : '') }}">
                {{ $currencySymbol ?? '$' }} {{ number_format($openingBalance, 2) }}
            </td>
        </tr>

        @foreach($ledgerRows as $row)
        <tr>
            <td>{{ $row['date']->format('d/m/Y') }}<br><span style="font-size: 8pt; color: #666;">{{ $row['date']->format('H:i') }}</span></td>
            <td>{{ $row['description'] }}</td>
            <td style="font-family: 'DejaVu Sans Mono', monospace;">{{ $row['reference'] ?: '—' }}</td>
            <td class="amt {{ $row['debit'] > 0 ? 'debit' : 'dash' }}">
                {{ $row['debit'] > 0 ? ($currencySymbol ?? '$') . ' ' . number_format($row['debit'], 2) : '—' }}
            </td>
            <td class="amt {{ $row['credit'] > 0 ? 'credit' : 'dash' }}">
                {{ $row['credit'] > 0 ? ($currencySymbol ?? '$') . ' ' . number_format($row['credit'], 2) : '—' }}
            </td>
            <td class="amt {{ $row['running_balance'] > 0 ? 'balance-dr' : ($row['running_balance'] < 0 ? 'balance-cr' : '') }}">
                {{ $currencySymbol ?? '$' }} {{ number_format($row['running_balance'], 2) }}
                @if($row['running_balance'] > 0) DR @elseif($row['running_balance'] < 0) CR @endif
            </td>
        </tr>
        @endforeach

        @if(count($ledgerRows) > 0)
        <tr class="total-row">
            <td colspan="3">Totals</td>
            <td class="amt debit">{{ $currencySymbol ?? '$' }} {{ number_format($totalDebit, 2) }}</td>
            <td class="amt credit">{{ $currencySymbol ?? '$' }} {{ number_format($totalCredit, 2) }}</td>
            <td class="amt {{ $closingBalance > 0 ? 'balance-dr' : 'balance-cr' }}">
                {{ $currencySymbol ?? '$' }} {{ number_format(abs($closingBalance), 2) }}
                {{ $closingBalance > 0 ? 'DR' : ($closingBalance < 0 ? 'CR' : '') }}
            </td>
        </tr>
        @endif
    </tbody>
</table>

@if(count($ledgerRows) == 0)
<p style="text-align: center; color: #666; padding: 24px 0;">No transactions recorded for this account.</p>
@endif

<div class="footer">
    <p>This statement is computer-generated and requires no signature.</p>
    <p>© {{ date('Y') }} {{ config('app.name', 'Laiba Safety') }}. All rights reserved.</p>
</div>

</body>
</html>
