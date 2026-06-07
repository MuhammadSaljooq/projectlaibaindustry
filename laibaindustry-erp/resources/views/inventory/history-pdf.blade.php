<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory History – {{ now()->format('d M Y') }}</title>
    <style>
        @page {
            margin: 18mm 34mm 22mm 34mm;
            size: A4 landscape;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #1a1a1a;
            background: #fff;
        }

        /* ── Page footer (page numbers) ─────────────────────── */
        .page-footer {
            position: fixed;
            bottom: -12mm;
            left: 0; right: 0;
            text-align: center;
            font-size: 7.5pt;
            color: #888;
        }
        .page-footer:after {
            content: "Page " counter(page) " of " counter(pages);
        }

        /* ── Header ─────────────────────────────────────────── */
        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #2b3437;
            padding-top: 6px;
            padding-bottom: 20px;
            margin-bottom: 18px;
        }
        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 80px;
        }
        .header-left img {
            max-width: 64px;
            max-height: 54px;
        }
        .header-center {
            display: table-cell;
            vertical-align: middle;
            padding-left: 16px;
        }
        .company-name {
            font-size: 12pt;
            font-weight: bold;
            color: #1a1a1a;
            line-height: 1.4;
        }
        .company-meta {
            font-size: 7.5pt;
            color: #555;
            margin-top: 5px;
            line-height: 1.7;
        }
        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 220px;
            padding-left: 20px;
            padding-right: 8px;
        }
        .report-title {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #2b3437;
            white-space: nowrap;
        }
        .report-subtitle {
            font-size: 7.5pt;
            margin-top: 6px;
            padding-right: 4px;
            color: #777;
            margin-top: 3px;
        }

        /* ── Filters strip ───────────────────────────────────── */
        .filters {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 9px 16px;
            font-size: 7.5pt;
            color: #444;
            margin-bottom: 12px;
        }
        .filters span {
            margin-right: 16px;
        }
        .filters strong {
            color: #1a1a1a;
        }

        /* ── Stats strip ─────────────────────────────────────── */
        .stats {
            display: table;
            width: 100%;
            border: 1px solid #cbd5e1;
            border-right: none;
            margin-bottom: 14px;
        }
        .stat-cell {
            display: table-cell;
            width: 33.33%;
            padding: 12px 18px;
            border-right: 1px solid #cbd5e1;
            vertical-align: top;
        }
        .stat-cell.highlight {
            background: #f8fafc;
            border-top: 3px solid #2b3437;
        }
        .stat-label {
            font-size: 6.5pt;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6b7280;
            margin-bottom: 3px;
        }
        .stat-value {
            font-size: 13pt;
            font-weight: bold;
            color: #2b3437;
        }

        /* ── Data table ──────────────────────────────────────── */
        table.data {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }
        table.data thead tr {
            background: #2b3437;
            color: #fff;
        }
        table.data thead th {
            padding: 9px 14px;
            text-align: left;
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: bold;
            white-space: nowrap;
        }
        table.data thead th.text-right {
            text-align: right;
        }
        table.data tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        table.data tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        table.data tbody td {
            padding: 7px 14px;
            vertical-align: top;
            color: #1a1a1a;
        }
        table.data tbody td.text-right {
            text-align: right;
            font-family: 'DejaVu Sans Mono', monospace;
        }
        table.data tbody td.mono {
            font-family: 'DejaVu Sans Mono', monospace;
        }
        table.data tbody td.muted {
            color: #6b7280;
        }
        table.data tbody td .product-name {
            font-weight: bold;
        }
        table.data tbody td .product-cat {
            font-size: 7pt;
            color: #9ca3af;
        }
        table.data tfoot tr {
            background: #f1f5f9;
            border-top: 2px solid #2b3437;
        }
        table.data tfoot td {
            padding: 8px 14px;
            font-weight: bold;
            font-size: 8pt;
        }
        table.data tfoot td.text-right {
            text-align: right;
            font-family: 'DejaVu Sans Mono', monospace;
        }

        .empty-row td {
            text-align: center;
            padding: 20px;
            color: #9ca3af;
            font-style: italic;
        }

        /* ── Footer note ─────────────────────────────────────── */
        .generated {
            margin-top: 10px;
            font-size: 7pt;
            color: #9ca3af;
            text-align: right;
            padding-right: 8px;
        }
    </style>
</head>
<body>

<div class="page-footer"></div>

{{-- ── Company header ─────────────────────────────────────────── --}}
<div class="header">
    <div class="header-left">
        @php
            $logoPath = public_path($company['logo'] ?? 'images/company/laiba-logo.png');
            $logoSrc  = '';
            if (file_exists($logoPath)) {
                $ext      = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
                $mime     = match($ext) { 'svg' => 'image/svg+xml', 'jpg','jpeg' => 'image/jpeg', 'gif' => 'image/gif', default => 'image/png' };
                $logoSrc  = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        @endphp
        @if($logoSrc)
            <img src="{{ $logoSrc }}" alt="Company logo">
        @endif
    </div>
    <div class="header-center">
        <div class="company-name">{{ $company['name'] }}</div>
        <div class="company-meta">
            @foreach($company['address_lines'] as $line){{ $line }}<br>@endforeach
            VAT: {{ $company['vat_number'] }} &nbsp;·&nbsp; {{ $company['phone'] }} &nbsp;·&nbsp; {{ $company['email'] }}
        </div>
    </div>
    <div class="header-right">
        <div class="report-title">Inventory History</div>
        <div class="report-subtitle">Sales history · one row per line item</div>
    </div>
</div>

{{-- ── Active filters ──────────────────────────────────────────── --}}
@php
    $hasFilters = $from || $to || $search || $productName;
@endphp
@if($hasFilters)
<div class="filters">
    <strong>Filters applied:</strong>&nbsp;
    @if($from || $to)
        <span>Date: <strong>{{ $from ? \Carbon\Carbon::parse($from)->format('d M Y') : '—' }}</strong>
        to <strong>{{ $to ? \Carbon\Carbon::parse($to)->format('d M Y') : '—' }}</strong></span>
    @endif
    @if($productName)
        <span>Product: <strong>{{ $productName }}</strong></span>
    @endif
    @if($search)
        <span>Search: <strong>"{{ $search }}"</strong></span>
    @endif
</div>
@endif

{{-- ── Stats strip ─────────────────────────────────────────────── --}}
<div class="stats">
    <div class="stat-cell">
        <div class="stat-label">Total lines</div>
        <div class="stat-value">{{ number_format($totals->total_lines ?? 0) }}</div>
    </div>
    <div class="stat-cell">
        <div class="stat-label">Total qty sold</div>
        <div class="stat-value">{{ number_format($totals->total_qty ?? 0) }}</div>
    </div>
    <div class="stat-cell highlight">
        <div class="stat-label">Total revenue</div>
        <div class="stat-value">{{ $currencySymbol }} {{ number_format($totals->total_revenue ?? 0, 2) }}</div>
    </div>
</div>

{{-- ── Data table ───────────────────────────────────────────────── --}}
<table class="data">
    <thead>
        <tr>
            <th>Date</th>
            <th>Product</th>
            <th>Article No.</th>
            <th>Customer</th>
            <th class="text-right">Qty</th>
            <th class="text-right">Unit Price</th>
            <th class="text-right">Line Total</th>
            <th class="text-right">After-Sale Stock</th>
            <th>Invoice</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $item)
            @php
                $customer  = $item->sale?->customer_name ?? $item->sale?->customer_code ?? 'Walk-in';
                $lineTotal = $item->quantity * $item->selling_price;
            @endphp
            <tr>
                <td class="muted mono" style="white-space:nowrap;">{{ format_display_datetime($item->sale?->date) }}</td>
                <td>
                    <div class="product-name">{{ $item->product?->name ?? 'Product #'.$item->product_id }}</div>
                    <div class="product-cat">{{ optional($item->product?->category)->name ?? '' }}</div>
                </td>
                <td class="mono muted">{{ $item->product?->sku ?? '—' }}</td>
                <td style="max-width:130px;">{{ $customer }}</td>
                <td class="text-right" style="font-weight:bold;">{{ $item->quantity }}</td>
                <td class="text-right muted">{{ $currencySymbol }} {{ number_format($item->selling_price, 2) }}</td>
                <td class="text-right" style="font-weight:bold;">{{ $currencySymbol }} {{ number_format($lineTotal, 2) }}</td>
                @php
                    $afterSaleStock = $itemStockMap[$item->id] ?? 0;
                    $isLow = $item->product && $afterSaleStock <= ($item->product->reorder_level ?? 0);
                @endphp
                <td class="text-right" style="font-weight:bold;{{ $isLow ? 'color:#9F403D;' : '' }}">
                    {{ number_format($afterSaleStock) }}
                </td>
                <td class="mono" style="font-weight:bold;">{{ $item->sale?->invoice_number ?? '—' }}</td>
            </tr>
        @empty
            <tr class="empty-row">
                <td colspan="9">No sales history found for the selected filters.</td>
            </tr>
        @endforelse
    </tbody>
    @if($items->isNotEmpty())
    <tfoot>
        <tr>
            <td colspan="4" style="color:#6b7280;">
                {{ number_format($items->count()) }} line{{ $items->count() === 1 ? '' : 's' }}
            </td>
            <td class="text-right">{{ number_format($totals->total_qty ?? 0) }}</td>
            <td></td>
            <td class="text-right">{{ $currencySymbol }} {{ number_format($totals->total_revenue ?? 0, 2) }}</td>
            <td></td>
            <td></td>
        </tr>
    </tfoot>
    @endif
</table>

<div class="generated">
    Generated {{ now()->format('d M Y, H:i') }} · Laiba Safety Admin Console
</div>

</body>
</html>
