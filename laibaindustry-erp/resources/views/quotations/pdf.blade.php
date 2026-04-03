{{--
    resources/views/quotations/pdf.blade.php
    DomPDF — bilingual (EN/AR). Logo: config company.logo, with sensible fallbacks.
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
        margin: 26mm 24mm 26mm 24mm; /* top, right, bottom, left */
    }

    body {
        font-family: 'DejaVu Sans', 'Arial', sans-serif;
        font-size: 9.5pt;
        line-height: 1.55;
        word-spacing: 0.08em;
        letter-spacing: 0.01em;
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

    .page { width: 100%; padding: 0; }

    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .bold { font-weight: bold; }
    .white { color: #ffffff; }
    .dark-blue { color: #1a237e; }
    .gray { color: #555; }
    .small { font-size: 8pt; line-height: 1.5; }
    .section { margin-bottom: 10px; }

    /* Bilingual label: English LTR, Arabic RTL — avoids DomPDF gluing text */
    .label-en {
        display: block;
        direction: ltr;
        unicode-bidi: isolate;
        font-size: 8pt;
        font-weight: bold;
        color: #1a237e;
        line-height: 1.45;
        margin-bottom: 3px;
        letter-spacing: 0.02em;
    }
    .label-ar {
        display: block;
        direction: rtl;
        unicode-bidi: embed;
        font-size: 8.5pt;
        font-weight: bold;
        color: #2e3a8f;
        line-height: 1.5;
    }
    .value-ltr {
        direction: ltr;
        unicode-bidi: isolate;
        text-align: left;
        padding: 8px 12px;
        font-size: 9pt;
        line-height: 1.5;
        word-spacing: 0.06em;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    td, th { padding: 0; vertical-align: middle; }

    /* ── Header ─────────────────────────────────────────────────────── */
    .header-table {
        border: 2px solid var(--dark-blue);
    }
    .header-logo-cell {
        width: 24%;
        background: #ffffff;
        text-align: center;
        vertical-align: middle;
        padding: 0;
        border-right: 2px solid var(--dark-blue);
        overflow: visible;
    }
    .header-logo-wrap {
        width: 100%;
        text-align: center;
        line-height: 0;
        overflow: visible;
    }
    /* Narrow column + larger pt; no max-width so logo is not shrunk to cell width */
    .header-logo-cell img {
        width: 136pt;
        max-height: 118pt;
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
        width: 46%;
        background: #ffffff;
        padding: 14px 18px;
        vertical-align: middle;
        text-align: center;
    }
    .header-info-cell .company-name-line {
        font-size: 11pt;
        font-weight: bold;
        color: var(--dark-blue);
        line-height: 1.4;
        margin-bottom: 2px;
        letter-spacing: 0.02em;
    }
    .header-info-cell .company-name-line + .company-name-line {
        font-size: 10pt;
        margin-bottom: 6px;
    }
    .header-info-cell .company-detail {
        font-size: 8pt;
        color: var(--muted);
        line-height: 1.65;
        margin-top: 2px;
        word-spacing: 0.1em;
    }
    .header-title-cell {
        width: 30%;
        background-color: var(--dark-blue);
        text-align: center;
        padding: 16px 14px;
        vertical-align: middle;
    }
    .header-title-cell .title-en {
        font-size: 17pt;
        font-weight: bold;
        color: #ffffff;
        line-height: 1.35;
        letter-spacing: 0.04em;
        direction: ltr;
        unicode-bidi: isolate;
    }
    .header-title-cell .title-ar {
        font-size: 15pt;
        font-weight: bold;
        color: #ffffff;
        line-height: 1.45;
        margin-top: 4px;
        direction: rtl;
        unicode-bidi: embed;
    }
    .header-title-cell .title-divider {
        color: rgba(255,255,255,0.45);
        font-size: 7pt;
        margin: 8px 0;
        letter-spacing: 0.15em;
    }
    .header-title-cell .brand-sub {
        font-size: 8pt;
        font-weight: bold;
        color: #d0d4ff;
        letter-spacing: 0.12em;
        direction: ltr;
        unicode-bidi: isolate;
        margin-top: 2px;
    }

    /* ── Details & customer ─────────────────────────────────────────── */
    .details-table td,
    .customer-table td {
        border: 1px solid var(--border);
        vertical-align: top;
    }
    .details-table .label-cell {
        width: 22%;
        background: var(--label-bg);
        padding: 8px 10px;
    }
    .details-table .value-cell {
        width: 28%;
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
    .section-header-row .hdr-en {
        display: block;
        direction: ltr;
        unicode-bidi: isolate;
        letter-spacing: 0.03em;
    }
    .section-header-row .hdr-ar {
        display: block;
        direction: rtl;
        unicode-bidi: embed;
        font-size: 10pt;
        margin-top: 4px;
        opacity: 0.95;
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
        word-spacing: 0.05em;
    }
    .customer-table-compact .label-en {
        font-size: 7pt;
        margin-bottom: 1px;
        line-height: 1.25;
    }
    .customer-table-compact .label-ar {
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
        padding: 8px 8px;
        font-size: 9pt;
        line-height: 1.5;
        vertical-align: middle;
        word-spacing: 0.06em;
    }
    .items-table tr.row-alt td { background: var(--row-alt); }
    .items-table tr.row-white td { background: #ffffff; }
    .col-sno { width: 6%; text-align: center; }
    .col-desc { width: 38%; text-align: left; direction: ltr; unicode-bidi: isolate; }
    .col-qty { width: 10%; text-align: center; }
    .col-price { width: 14%; text-align: right; direction: ltr; unicode-bidi: isolate; }
    .col-tax { width: 14%; text-align: right; direction: ltr; unicode-bidi: isolate; }
    .col-amount { width: 18%; text-align: right; direction: ltr; unicode-bidi: isolate; }

    /* ── Totals ─────────────────────────────────────────────────────── */
    .totals-wrap td { vertical-align: top; }
    .totals-words-cell {
        width: 58%;
        border: 1px solid var(--border);
        padding: 12px 14px;
        vertical-align: middle;
    }
    .totals-words-label .tw-en {
        display: block;
        direction: ltr;
        font-weight: bold;
        font-size: 8.5pt;
        margin-bottom: 4px;
        letter-spacing: 0.02em;
    }
    .totals-words-label .tw-ar {
        display: block;
        direction: rtl;
        font-weight: bold;
        font-size: 9pt;
        color: #2e3a8f;
        margin-bottom: 8px;
    }
    .totals-words-value {
        font-style: italic;
        color: #333;
        line-height: 1.55;
        word-spacing: 0.08em;
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
    .totals-right-table .total-label { background: #f4f4f4; width: 64%; }
    .totals-right-table .total-label .tl-en {
        display: block;
        direction: ltr;
        font-weight: bold;
        font-size: 8pt;
        color: #333;
        margin-bottom: 3px;
    }
    .totals-right-table .total-label .tl-ar {
        display: block;
        direction: rtl;
        font-weight: bold;
        font-size: 8.5pt;
        color: #444;
    }
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
        padding: 10px 8px !important;
    }
    .totals-right-table .grand-label .gl-en {
        display: block;
        direction: ltr;
        unicode-bidi: isolate;
        font-weight: bold;
        font-size: 9.5pt;
        letter-spacing: 0.03em;
        margin-bottom: 3px;
    }
    .totals-right-table .grand-label .gl-ar {
        display: block;
        direction: rtl;
        unicode-bidi: embed;
        font-weight: bold;
        font-size: 10pt;
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
        margin-bottom: 18px;
        letter-spacing: 0.06em;
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
        line-height: 1.65;
        word-spacing: 0.12em;
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
                        <img src="{{ $logoSrc }}" alt="Logo" width="182" style="width: 136pt; max-height: 118pt; height: auto;">
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
                <div class="title-en">Quotation</div>
                <div class="title-ar">اقتباس</div>
                <div class="title-divider">─────────────</div>
                <div class="brand-sub">LAIBA INDUSTRY</div>
            </td>
        </tr>
    </table>

    <table class="details-table section">
        <tr>
            <td class="label-cell">
                <span class="label-en">Quotation No</span>
                <span class="label-ar">رقم الاقتباس</span>
            </td>
            <td class="value-cell value-ltr bold dark-blue">{{ $quotation->quotation_number }}</td>
            <td class="label-cell">
                <span class="label-en">Quotation Date</span>
                <span class="label-ar">تاريخ الاقتباس</span>
            </td>
            <td class="value-cell value-ltr">{{ $quotation->quotation_date->format('d M Y') }}</td>
        </tr>
        <tr>
            <td class="label-cell">
                <span class="label-en">Expiration</span>
                <span class="label-ar">انتهاء</span>
            </td>
            <td class="value-cell value-ltr">
                {{ $quotation->expiration_date ? $quotation->expiration_date->format('d M Y') : '—' }}
            </td>
            <td class="label-cell">
                <span class="label-en">Salesperson</span>
                <span class="label-ar">مندوب مبيعات</span>
            </td>
            <td class="value-cell value-ltr">{{ $quotation->salesperson ?? '—' }}</td>
        </tr>
    </table>

    <table class="customer-table customer-table-compact section">
        <tr class="section-header-row">
            <td colspan="4">
                <span class="hdr-en">Customer Details</span>
                <span class="hdr-ar">بيانات العميل</span>
            </td>
        </tr>
        <tr>
            <td class="customer-label">
                <span class="label-en">Customer Name</span>
                <span class="label-ar">اسم العميل</span>
            </td>
            <td class="customer-value bold">{{ $quotation->customer_name }}</td>
            <td class="customer-label">
                <span class="label-en">VAT Number</span>
                <span class="label-ar">الرقم الضريبي</span>
            </td>
            <td class="customer-value">{{ $quotation->customer_vat_number ?? '—' }}</td>
        </tr>
        <tr>
            <td class="customer-label">
                <span class="label-en">CR Number</span>
                <span class="label-ar">السجل التجاري</span>
            </td>
            <td class="customer-value">{{ $quotation->customer_cr_number ?? '—' }}</td>
            <td class="customer-label">
                <span class="label-en">Phone</span>
                <span class="label-ar">رقم الهاتف</span>
            </td>
            <td class="customer-value">{{ $quotation->customer_phone ?? '—' }}</td>
        </tr>
        <tr>
            <td class="customer-label">
                <span class="label-en">Email</span>
                <span class="label-ar">البريد الإلكتروني</span>
            </td>
            <td class="customer-value">{{ $quotation->customer_email ?? '—' }}</td>
            <td class="customer-label">
                <span class="label-en">Address</span>
                <span class="label-ar">العنوان</span>
            </td>
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
                <th scope="col" class="items-col-h col-sno" style="{{ $ih }} text-align:center; width:6%;">Sr no</th>
                <th scope="col" class="items-col-h col-desc" style="{{ $ih }} text-align:left; width:38%;">Description</th>
                <th scope="col" class="items-col-h col-qty" style="{{ $ih }} text-align:center; width:10%;">QTV</th>
                <th scope="col" class="items-col-h col-price" style="{{ $ih }} text-align:right; width:14%;">Price</th>
                <th scope="col" class="items-col-h col-tax" style="{{ $ih }} text-align:right; width:14%;">Vat</th>
                <th scope="col" class="items-col-h col-amount" style="{{ $ih }} text-align:right; width:18%;">Total</th>
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
                <div class="totals-words-label">
                    <span class="tw-en">Amount in Words</span>
                    <span class="tw-ar">المبلغ كتابةً</span>
                </div>
                <div class="totals-words-value">{{ $quotation->totalInWords() }}</div>
            </td>
            <td class="totals-right-cell">
                <table class="totals-right-table">
                    <tr>
                        <td class="total-label">
                            <span class="tl-en">Untaxed Amount</span>
                            <span class="tl-ar">المبلغ غير الخاضع للضريبة</span>
                        </td>
                        <td class="total-value">SAR {{ number_format((float) $quotation->untaxed_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="total-label">
                            <span class="tl-en">VAT</span>
                            <span class="tl-ar">ضريبة القيمة المضافة</span>
                        </td>
                        <td class="total-value">SAR {{ number_format((float) $quotation->vat_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="grand-label">
                            <span class="gl-en">Total</span>
                            <span class="gl-ar">المجموع</span>
                        </td>
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
