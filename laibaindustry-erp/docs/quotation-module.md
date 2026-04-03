# Quotation Module — Integration Guide
## laibaindustry-erp

---

## Files to copy into your project

```
app/
  Models/
    Quotation.php
    QuotationItem.php
  Http/
    Controllers/
      QuotationController.php

database/
  migrations/
    2024_01_01_000001_create_quotations_table.php

resources/
  views/
    quotations/
      pdf.blade.php          ← DomPDF template (the document)
      _form.blade.php        ← Shared create/edit form partial
      create.blade.php
      edit.blade.php
      show.blade.php
      index.blade.php

routes/
  quotation_routes.php       ← Paste these lines into your web.php
```

---

## Step 1 — Copy the logo

```bash
cp /path/to/laiba_logo_cropped.png public/images/laiba_logo.png
```

DomPDF uses `public_path('images/laiba_logo.png')` (absolute path, no HTTP).
The image must be a local file — `isRemoteEnabled` is set to `false` for security.

---

## Step 2 — Run the migration

```bash
php artisan migrate
```

---

## Step 3 — Add routes to `routes/web.php`

```php
use App\Http\Controllers\QuotationController;

Route::resource('quotations', QuotationController::class);
Route::get('/quotations/{quotation}/pdf',     [QuotationController::class, 'pdf'])->name('quotations.pdf');
Route::get('/quotations/{quotation}/preview', [QuotationController::class, 'preview'])->name('quotations.preview');
```

---

## Step 4 — Add Alpine.js to your layout

The `_form.blade.php` uses Alpine.js for live row calculations.
If your `layouts/app.blade.php` doesn't already have it, add:

```html
<!-- In <head> or before </body> -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
```

---

## Step 5 — Verify DomPDF is installed

```bash
composer require barryvdh/laravel-dompdf
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

Your `composer.json` already lists `barryvdh/laravel-dompdf ^3.1`, so it should already be present.

---

## Step 6 — Optional: publish DomPDF config

```bash
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider" --tag="dompdf-config"
```

In `config/dompdf.php`, set:
```php
'default_font' => 'dejavu sans',   // best Arabic support in DomPDF
'is_html5_parser_enabled' => true,
```

---

## How it works

| URL | What it does |
|-----|-------------|
| `GET  /quotations` | List all quotations |
| `GET  /quotations/create` | New quotation form |
| `POST /quotations` | Save new quotation |
| `GET  /quotations/{id}` | View quotation detail |
| `GET  /quotations/{id}/edit` | Edit form |
| `PUT  /quotations/{id}` | Update quotation |
| `DELETE /quotations/{id}` | Soft-delete |
| `GET  /quotations/{id}/pdf` | **Download PDF** |
| `GET  /quotations/{id}/preview` | **View PDF in browser** |

---

## Auto-numbering

Quotation numbers are auto-generated in the format `QT-2025-0001`.
The year resets the sequence each calendar year.
You can override this by setting `quotation_number` explicitly before saving.

---

## VAT / Tax

Each line item has its own `tax_rate` (default 15% Saudi VAT).
The model auto-calculates `tax_amount` and `amount` on every save.
`recalculateTotals()` is called after every store/update to keep header totals in sync.

---

## Arabic text in PDF

DomPDF's built-in `DejaVu Sans` font covers Arabic Unicode ranges.
If Arabic text appears as boxes, publish the DomPDF config and confirm
`default_font` is set to `'dejavu sans'`.

For full RTL paragraph support you may optionally add:

```php
// In QuotationController::pdf()
$pdf->setOptions(['isPhpEnabled' => false]);
```

And wrap Arabic strings in the Blade template with:
```html
<span dir="rtl" style="unicode-bidi: bidi-override;">اقتباس</span>
```
