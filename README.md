# Laiba Industry ERP

Laiba Industry ERP is a full-stack Laravel system for inventory, sales, receivables, purchases, payables, ledgers, quotations, VAT, banking, and international vendor workflows.

This README is written for GitHub collaborators and includes:

- product overview and setup
- deployment details
- **complete branch inventory (local + remote)**
- **major update timeline from recent commits**

---

## 1) Technology Stack

- **Backend:** Laravel 12, PHP 8.x
- **Frontend:** Blade templates, Tailwind (CDN utility usage), custom shared CSS, Material Symbols
- **Database:** SQLite (local default) or MySQL (production)
- **Build tooling:** Vite (`npm run dev`)
- **Auth/roles:** Admin, Manager, Viewer

---

## 2) Core Modules

- Dashboard and KPIs
- Inventory / Products
- Sales
- Receivables (including grouped payments)
- Customers + Statements (web/PDF/email)
- Purchases
- Payables (including grouped payments and offsets)
- International Purchases + International Payables
- Supplier/Vendor Ledger + PDF statement
- Quotations
- VAT
- Expenses
- Bank Statement
- User management (IAM)

---

## 3) Project Structure

```text
laibaindustry-erp/
├── app/
│   ├── Http/Controllers/
│   ├── Models/
│   ├── Services/
│   └── Support/
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── schema/
├── public/
│   ├── images/
│   ├── scripts/
│   └── styles/
├── resources/
│   └── views/
├── routes/
│   └── web.php
└── README.md
```

---

## 4) Local Setup

```bash
cd laibaindustry-erp
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
npm run dev
```

Open:

- Laravel: `http://127.0.0.1:8000`
- Vite: `http://127.0.0.1:5173`

### Mobile testing on same Wi-Fi

```bash
php artisan serve --host=0.0.0.0 --port=8001
npm run dev -- --host 0.0.0.0 --port 5174
```

Then open on phone:

- `http://<YOUR_LAN_IP>:8001`

---

## 5) Key Environment Variables

- `APP_NAME`
- `APP_ENV`
- `APP_KEY`
- `APP_URL`
- `DB_CONNECTION`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `MAIL_*` for statement email delivery

---

## 6) Deployment (cPanel via GitHub Actions)

Workflow: `.github/workflows/deploy-cpanel.yml`

Required GitHub secrets:

- `APP_NAME`
- `APP_KEY`
- `APP_URL`
- `DB_HOST`
- `SQL_DB`
- `SQL_USER`
- `SQL_PWD`
- `FTP_SERVER`
- `FTP_USERNAME`
- `FTP_PASSWORD`
- `FTP_SERVER_DIR`

Pipeline behavior (high level):

1. Install production dependencies
2. Build deploy package
3. Generate `.env` from secrets
4. Prepare cPanel-compatible output
5. Upload via FTP action

---

## 7) Branches (Complete Inventory)

### Local branches

- `main`
- `mobile-optimization`
- `payble-offset`
- `pdf-updated`

### Remote branches

- `origin/2026-Laiba-Safety`
- `origin/VAT`
- `origin/account-ledger`
- `origin/bug-fixes-30-march`
- `origin/cursor/development-environment-setup-b1dd`
- `origin/customer-statement`
- `origin/customerr-fix`
- `origin/date-fix`
- `origin/dev-feature-theme`
- `origin/final-update`
- `origin/final-update-25-march`
- `origin/frontend`
- `origin/international-fix-2`
- `origin/international-purchases`
- `origin/ledger`
- `origin/ledger-enetry-delete`
- `origin/main`
- `origin/mian-ali-23-march`
- `origin/mobile-optimization`
- `origin/navbar-changes`
- `origin/opening-balance-on-recievables`
- `origin/payable-fix`
- `origin/payble-offset`
- `origin/pdf-complete`
- `origin/pdf-updated`
- `origin/purchase_offset`
- `origin/purchases`
- `origin/quotation`
- `origin/recievabale-fix`
- `origin/recievable-fix-1`
- `origin/recievable-last-update`
- `origin/recievable-update-31`
- `origin/record-internationalpayment`
- `origin/vat-delete`

> Note: Some branch names include legacy spelling variants (`payble`, `recievable`, etc.) because they are historical branch names in the repository.

---

## 8) Branch Purpose Summary (Current and Recent)

### `mobile-optimization` (current working branch)

Recent focus:

- mobile responsiveness hardening
- touch-target standards
- sticky action columns / dense table usability
- sidebar compatibility fixes for mobile browsers

### `payble-offset`

Recent focus:

- payable sales-offset logic
- ledger/payable consistency
- vendor aging / outstanding-day visibility

### `pdf-updated`

Recent focus:

- vendor statement PDF currency and formatting updates

### `main`

Primary integration branch for stable production-ready changes.

---

## 9) Major Updates

Recent updates delivered across active branches:

- Mobile responsiveness hardening across core pages (sidebar behavior, touch-target sizing, dense table usability, sticky action columns).
- Payables enhancement with sales-offset synchronization and improved ledger consistency.
- Vendor ledger improvements including invoice aging and days-outstanding visibility in web and PDF statements.
- Statement and PDF formatting improvements for customer/vendor readability and currency consistency.
- Grouped payment workflow alignment across receivables, payables, and international payables.
- International purchases/payables grouping and payment flow refinements.
- Search and filtering UX enhancements in key listing pages.
- Favicon and UI polish updates for improved branding and presentation.

---

## 10) Notes for Contributors

- Use feature branches for isolated changes.
- Keep migrations forward-only and production-safe.
- For UI changes, test at:
  - desktop
  - tablet
  - small phone widths
- For financial logic changes, validate:
  - ledger totals
  - offsets
  - grouped payments
  - PDF output

---

## 11) License

Private/internal project unless explicitly relicensed by repository owners.
