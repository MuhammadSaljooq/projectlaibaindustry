{{--
    resources/views/quotations/pdf.blade.php
    DomPDF quotation layout. Logo: config company.logo (QuotationPdfLogo).
--}}
@php
    $company = \App\Support\StatementCompany::normalize(config('company'));
    $logoSrc = \App\Support\QuotationPdfLogo::dataUri();
    $logoPathHint = $company['logo'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Quotation {{ $quotation->quotation_number }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    /* White margin on all sides of the page (DomPDF @page) */
    @page {
        margin: 22mm 22mm 24mm 22mm; /* top, right, bottom, left — balanced frame */
    }

    body {
        font-family: 'DejaVu Sans', 'Arial', sans-serif;
        font-size: 9.5pt;
        line-height: 1.45;
        word-spacing: normal;
        letter-spacing: normal;
        color: #1a1a1a;
        background: #fff;
        padding: 0;
    }

    :root {
        --dark-blue: #1a237e;
        --light-blue: #eef0fb;
        --label-bg: #f3f5fc;
        --row-alt: #f9fafb;
        --border: #c8c8c8;
        --muted: #4a4a4a;
    }

    .page {
        width: 100%;
        max-width: 100%;
        padding: 0;
    }

    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .bold { font-weight: bold; }
    .white { color: #ffffff; }
    .dark-blue { color: #1a237e; }
    .gray { color: #555; }
    .small { font-size: 8pt; line-height: 1.5; }
    .section { margin-bottom: 10px; }

    .field-label {
        font-size: 8pt;
        font-weight: bold;
        color: #1a237e;
        line-height: 1.35;
    }
    .value-ltr {
        direction: ltr;
        unicode-bidi: isolate;
        text-align: left;
        padding: 6px 10px;
        font-size: 9pt;
        line-height: 1.4;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: auto;
    }
    .header-table,
    .details-table,
    .customer-table,
    .items-table,
    .totals-wrap {
        table-layout: fixed;
    }
    .totals-right-table {
        table-layout: fixed;
        width: 100%;
    }
    td, th { padding: 0; vertical-align: middle; }

    /* ── Header ─────────────────────────────────────────────────────── */
    .header-table {
        border: 2px solid var(--dark-blue);
    }
    .header-logo-cell {
        width: 22%;
        background: #ffffff;
        text-align: center;
        vertical-align: middle;
        padding: 4px 6px;
        border-right: 2px solid var(--dark-blue);
    }
    .header-logo-wrap {
        text-align: center;
        line-height: 0;
    }
    /* Fit inside column; width + height:auto keeps aspect ratio (no horizontal stretch) */
    .header-logo-cell img {
        max-width: 100%;
        width: auto;
        max-height: 72pt;
        height: auto;
        display: block;
        margin: 0 auto;
    }
    .header-logo-placeholder {
        font-size: 7.5pt;
        color: #888;
        direction: ltr;
        padding: 8px;
        line-height: 1.4;
    }
    .header-info-cell {
        width: 48%;
        background: #ffffff;
        padding: 14px 18px;
        vertical-align: middle;
        text-align: center;
    }
    .header-info-cell .company-name-line {
        font-size: 10.5pt;
        font-weight: bold;
        color: var(--dark-blue);
        line-height: 1.35;
        margin-bottom: 2px;
    }
    .header-info-cell .company-name-line + .company-name-line {
        font-size: 10pt;
        margin-bottom: 6px;
    }
    .header-info-cell .company-detail {
        font-size: 8pt;
        color: var(--muted);
        line-height: 1.45;
        margin-top: 2px;
    }
    .header-title-cell {
        width: 30%;
        background-color: var(--dark-blue);
        text-align: center;
        padding: 16px 14px;
        vertical-align: middle;
    }
    .header-title-cell .doc-title {
        font-size: 16pt;
        font-weight: bold;
        color: #ffffff;
        line-height: 1.25;
    }
    .header-title-cell .title-divider {
        color: rgba(255,255,255,0.45);
        font-size: 7pt;
        margin: 6px 0;
    }
    .header-title-cell .brand-sub {
        font-size: 8pt;
        font-weight: bold;
        color: #d0d4ff;
        margin-top: 2px;
    }

    /* ── Details & customer ─────────────────────────────────────────── */
    .details-table td,
    .customer-table td {
        border: 1px solid var(--border);
        vertical-align: top;
    }
    .details-table .label-cell {
        width: 20%;
        background: var(--label-bg);
        padding: 8px 10px;
    }
    .details-table .value-cell {
        width: 30%;
        background: #fff;
    }
    .section-header-row td {
        background: var(--dark-blue);
        color: #ffffff;
        font-weight: bold;
        font-size: 9.5pt;
        text-align: center;
        padding: 10px 14px;
        border: 1px solid var(--dark-blue);
        line-height: 1.5;
    }
    /* Customer block: compact, two fields per row (label | value | label | value) */
    .customer-table-compact td {
        border: 1px solid #c8c8c8;
        vertical-align: top;
    }
    .customer-table-compact .section-header-row td {
        padding: 7px 12px;
        font-size: 8.5pt;
    }
    .customer-table-compact .customer-label {
        width: 16%;
        background: #f3f5fc;
        padding: 5px 6px;
    }
    .customer-table-compact .customer-value {
        width: 34%;
        background: #ffffff;
        padding: 5px 8px;
        direction: ltr;
        unicode-bidi: isolate;
        text-align: left;
        font-size: 8.5pt;
        line-height: 1.4;
    }
    .customer-table-compact .field-label {
        font-size: 7.5pt;
        line-height: 1.25;
    }

    /* Column headers: hex only (no var()) + first-row backup for DomPDF */
    .items-table > tbody > tr:first-child > th {
        background-color: #1a237e !important;
        color: #ffffff !important;
        border: 1px solid #1a237e !important;
        font-weight: bold !important;
        font-size: 9pt !important;
        padding: 10px 5px !important;
        vertical-align: middle !important;
        font-family: 'DejaVu Sans', 'Arial', sans-serif !important;
    }
    .items-table > tbody > tr:first-child > th.col-desc { text-align: left !important; }
    .items-table > tbody > tr:first-child > th.col-sno,
    .items-table > tbody > tr:first-child > th.col-qty { text-align: center !important; }
    .items-table > tbody > tr:first-child > th.col-price,
    .items-table > tbody > tr:first-child > th.col-tax,
    .items-table > tbody > tr:first-child > th.col-amount { text-align: right !important; }
    .items-table th.items-col-h {
        font-family: 'DejaVu Sans', 'Arial', sans-serif;
    }
    .items-table td {
        border: 1px solid var(--border);
        padding: 6px 7px;
        font-size: 9pt;
        line-height: 1.4;
        vertical-align: middle;
    }
    .items-table tr.row-alt td { background: var(--row-alt); }
    .items-table tr.row-white td { background: #ffffff; }
    .col-sno { width: 7%; text-align: center; }
    .col-desc { width: 36%; text-align: left; direction: ltr; unicode-bidi: isolate; }
    .col-qty { width: 10%; text-align: center; }
    .col-price { width: 14%; text-align: right; direction: ltr; unicode-bidi: isolate; }
    .col-tax { width: 13%; text-align: right; direction: ltr; unicode-bidi: isolate; }
    .col-amount { width: 20%; text-align: right; direction: ltr; unicode-bidi: isolate; }

    /* ── Totals ─────────────────────────────────────────────────────── */
    .totals-wrap td { vertical-align: top; }
    .totals-words-cell {
        width: 58%;
        border: 1px solid var(--border);
        padding: 12px 14px;
        vertical-align: middle;
    }
    .totals-words-label {
        font-weight: bold;
        font-size: 8.5pt;
        margin-bottom: 6px;
        color: #1a237e;
    }
    .totals-words-value {
        font-style: italic;
        color: #333;
        line-height: 1.4;
        direction: ltr;
        unicode-bidi: isolate;
    }
    .totals-right-cell { width: 42%; padding: 0; }
    .totals-right-table { width: 100%; border-collapse: collapse; }
    .totals-right-table td {
        border: 1px solid var(--border);
        padding: 8px 12px;
        font-size: 9pt;
        line-height: 1.5;
        vertical-align: middle;
    }
    .totals-right-table .total-label { background: #f4f4f4; width: 64%; font-weight: bold; font-size: 8.5pt; color: #333; }
    .totals-right-table .total-value {
        text-align: right;
        background: #fff;
        width: 36%;
        direction: ltr;
        unicode-bidi: isolate;
        font-weight: 600;
    }
    .totals-right-table .grand-label {
        background: var(--dark-blue);
        color: #fff;
        text-align: center;
        border: 1px solid var(--dark-blue);
        padding: 8px 6px !important;
        font-weight: bold;
        font-size: 9.5pt;
    }
    .totals-right-table .grand-value {
        background: var(--light-blue);
        font-weight: bold;
        text-align: right;
        font-size: 10pt;
        direction: ltr;
        unicode-bidi: isolate;
    }

    /* ── Signatures ─────────────────────────────────────────────────── */
    .signature-table {
        width: 100%;
        margin-top: 14px;
        border-collapse: collapse;
    }
    .signature-table td {
        width: 50%;
        padding: 12px 16px 8px;
        vertical-align: top;
        font-size: 9pt;
        line-height: 1.5;
    }
    .sig-title {
        font-weight: bold;
        font-size: 9.5pt;
        margin-bottom: 14px;
    }
    .sig-line {
        border-bottom: 1px solid #333;
        margin-bottom: 12px;
        padding-bottom: 16px;
    }
    .sig-label { font-size: 8.5pt; color: #555; margin-bottom: 4px; }

    .footer {
        margin-top: 14px;
        border-top: 2px solid var(--dark-blue);
        padding-top: 8px;
        text-align: center;
        font-size: 7.5pt;
        color: #666;
        line-height: 1.45;
    }
</style>
</head>
<body>
<div class="page">

    <table class="header-table section">
        <tr>
            <td class="header-logo-cell">
                @if ($logoSrc)
                    <div class="header-logo-wrap">
                        {{-- Explicit width (pt) helps DomPDF; height auto keeps aspect ratio --}}
                        <img src="{{ $logoSrc }}" alt="Logo" style="max-width:100%; width:auto; max-height:72pt; height:auto;">
                    </div>
                @else
                    <div class="header-logo-placeholder">Logo not found. Place PNG at public/{{ $logoPathHint }} (or set COMPANY_LOGO).</div>
                @endif
            </td>
            <td class="header-info-cell">
                @foreach ($company['pdf_header_name_lines'] as $line)
                    <div class="company-name-line">{{ $line }}</div>
                @endforeach
                <div class="company-detail">
                    CR: {{ $company['registration'] }}
                    <span style="padding: 0 0.5em;">|</span>
                    VAT#: {{ $company['vat_number'] }}
                </div>
                @foreach ($company['address_lines'] as $line)
                    <div class="company-detail">{{ $line }}</div>
                @endforeach
                <div class="company-detail">
                    {{ preg_replace('/\s+/', ' ', $company['phone']) }}
                    <span style="padding: 0 0.5em;">|</span>
                    {{ $company['email'] }}
                </div>
            </td>
            <td class="header-title-cell">
                <div class="doc-title">Quotation</div>
                <div class="title-divider">─────────────</div>
                <div class="brand-sub">LAIBA INDUSTRY</div>
            </td>
        </tr>
    </table>

    <table class="details-table section">
        <tr>
            <td class="label-cell"><span class="field-label">Quotation No</span></td>
            <td class="value-cell value-ltr bold dark-blue">{{ $quotation->quotation_number }}</td>
            <td class="label-cell"><span class="field-label">Quotation Date</span></td>
            <td class="value-cell value-ltr">{{ $quotation->quotation_date->format('d M Y') }}</td>
        </tr>
        <tr>
            <td class="label-cell"><span class="field-label">Expiration</span></td>
            <td class="value-cell value-ltr">
                {{ $quotation->expiration_date ? $quotation->expiration_date->format('d M Y') : '—' }}
            </td>
            <td class="label-cell"><span class="field-label">Salesperson</span></td>
            <td class="value-cell value-ltr">{{ $quotation->salesperson ?? '—' }}</td>
        </tr>
    </table>

    <table class="customer-table customer-table-compact section">
        <tr class="section-header-row">
            <td colspan="4">Customer Details</td>
        </tr>
        <tr>
            <td class="customer-label"><span class="field-label">Customer Name</span></td>
            <td class="customer-value bold">{{ $quotation->customer_name }}</td>
            <td class="customer-label"><span class="field-label">VAT Number</span></td>
            <td class="customer-value">{{ $quotation->customer_vat_number ?? '—' }}</td>
        </tr>
        <tr>
            <td class="customer-label"><span class="field-label">CR Number</span></td>
            <td class="customer-value">{{ $quotation->customer_cr_number ?? '—' }}</td>
            <td class="customer-label"><span class="field-label">Phone</span></td>
            <td class="customer-value">{{ $quotation->customer_phone ?? '—' }}</td>
        </tr>
        <tr>
            <td class="customer-label"><span class="field-label">Email</span></td>
            <td class="customer-value">{{ $quotation->customer_email ?? '—' }}</td>
            <td class="customer-label"><span class="field-label">Address</span></td>
            <td class="customer-value">{{ $quotation->customer_address ?? '—' }}</td>
        </tr>
    </table>

    @php
        $ih = 'background-color:#1a237e;color:#ffffff;font-weight:bold;padding:10px 5px;border:1px solid #1a237e;font-size:9pt;line-height:1.3;vertical-align:middle;font-family:DejaVu Sans, Arial, sans-serif;';
    @endphp
    <table class="items-table section">
        {{-- First row in tbody with <th>: DomPDF sometimes drops <thead>; inline styles avoid var() issues --}}
        <tbody>
            <tr>
                <th scope="col" class="items-col-h col-sno" style="{{ $ih }} text-align:center; width:7%;">Sr no</th>
                <th scope="col" class="items-col-h col-desc" style="{{ $ih }} text-align:left; width:36%;">Description</th>
                <th scope="col" class="items-col-h col-qty" style="{{ $ih }} text-align:center; width:10%;">QTV</th>
                <th scope="col" class="items-col-h col-price" style="{{ $ih }} text-align:right; width:14%;">Price</th>
                <th scope="col" class="items-col-h col-tax" style="{{ $ih }} text-align:right; width:13%;">Vat</th>
                <th scope="col" class="items-col-h col-amount" style="{{ $ih }} text-align:right; width:20%;">Total</th>
            </tr>
            @foreach ($quotation->items as $index => $item)
            <tr class="{{ $index % 2 === 0 ? 'row-white' : 'row-alt' }}">
                <td class="col-sno">{{ $index + 1 }}</td>
                <td class="col-desc">{{ $item->description }}</td>
                <td class="col-qty">{{ rtrim(rtrim(number_format((float) $item->quantity, 3), '0'), '.') }}</td>
                <td class="col-price">{{ number_format((float) $item->unit_price, 2) }}</td>
                <td class="col-tax">{{ number_format((float) $item->tax_rate, 2) }}%<br>
                    <span class="small gray">({{ number_format((float) $item->tax_amount, 2) }})</span>
                </td>
                <td class="col-amount bold">{{ number_format((float) $item->amount, 2) }}</td>
            </tr>
            @endforeach

            @for ($i = $quotation->items->count(); $i < 5; $i++)
            <tr class="{{ $i % 2 === 0 ? 'row-white' : 'row-alt' }}">
                <td class="col-sno">&nbsp;</td>
                <td class="col-desc">&nbsp;</td>
                <td class="col-qty">&nbsp;</td>
                <td class="col-price">&nbsp;</td>
                <td class="col-tax">&nbsp;</td>
                <td class="col-amount">&nbsp;</td>
            </tr>
            @endfor
        </tbody>
    </table>

    <table class="totals-wrap section">
        <tr>
            <td class="totals-words-cell">
                <div class="totals-words-label">Amount in Words</div>
                <div class="totals-words-value">{{ $quotation->totalInWords() }}</div>
            </td>
            <td class="totals-right-cell">
                <table class="totals-right-table">
                    <tr>
                        <td class="total-label">Untaxed Amount</td>
                        <td class="total-value">SAR {{ number_format((float) $quotation->untaxed_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="total-label">VAT</td>
                        <td class="total-value">SAR {{ number_format((float) $quotation->vat_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="grand-label">Total</td>
                        <td class="grand-value">SAR {{ number_format((float) $quotation->total_amount, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                <div class="sig-title">SALESMAN</div>
                <div class="sig-label">Name</div>
                <div class="sig-line"></div>
                <div class="sig-label">Signature</div>
                <div class="sig-line"></div>
            </td>
            <td>
                <div class="sig-title">RECEIVER</div>
                <div class="sig-label">Name</div>
                <div class="sig-line"></div>
                <div class="sig-label">Signature</div>
                <div class="sig-line"></div>
            </td>
        </tr>
    </table>

    <div class="footer">
        {{ $company['name'] }}
        <span style="padding: 0 0.35em;">|</span>
        CR: {{ $company['registration'] }}
        <span style="padding: 0 0.35em;">|</span>
        VAT#: {{ $company['vat_number'] }}
        <span style="padding: 0 0.35em;">|</span>
        {{ preg_replace('/\s+/', ' ', $company['phone']) }}
        <span style="padding: 0 0.35em;">|</span>
        {{ $company['email'] }}
    </div>

</div>
</body>
</html>
