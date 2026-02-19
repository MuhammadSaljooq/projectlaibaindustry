# Laiba Industry ERP

A full-stack Laravel ERP application for product management, sales, receivables, customers, and user administration.

## Tech Stack

- **Backend:** Laravel 11, PHP 8.3
- **Frontend:** Blade templates, Tailwind CSS (CDN), Material Symbols icons
- **Database:** SQLite (default) or MySQL (production via cPanel deploy)

## Features

### Dashboard

- **Total Revenue** – Sum of all sale amounts
- **Open Invoices** – Count of receivables with outstanding balance
- **Total Customers** – Customer count
- **Net Profit** – Sum of sale item profits
- **Sales Overview** – 6‑month sales chart
- **Low Stock Alerts** – Products at or below reorder level
- **Recent Activity** – Recent sales, new customers, low stock items

### Products

- CRUD: add, edit, delete products
- Search and filters: name/SKU, price range, category
- Fields: name, SKU, category, cost price, selling price, currency, description, stock quantity, reorder level

### Sales

- Create sales with multiple line items
- Header: date, customer code, customer name, invoice number
- Line items: product (dropdown), price, quantity, amount, VAT 15%
- Subtotal, tax, and total computed automatically
- Stock deducted on save
- Receivable and customer auto-created from sale

### Receivables

- Synced from sales
- Table: Date, Invoice #, Customer, Amount, Received, Remaining, Received date, Remaining date
- Record payments via edit form

### Customers

- CRUD: Name, Contact, Email
- Customer statement page for outstanding balances
- Customers auto-created from sales; can autofill on sale form when selecting existing customer

### Users (IAM)

- Admin/Manager can manage users (Admin and Manager roles only)
- CRUD: name, email, password, role (admin, manager, viewer)
- Policies: managers cannot edit admins; last admin cannot be deleted or demoted

## Project Structure

```
laibaindustry-erp/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php
│   │   │   ├── SaleController.php
│   │   │   ├── ReceivableController.php
│   │   │   ├── CustomerController.php
│   │   │   ├── ProductController.php
│   │   │   ├── UserController.php
│   │   │   └── ...
│   │   ├── Middleware/
│   │   │   ├── EnsureAdminOrManager.php
│   │   │   └── Authenticate.php
│   │   └── Requests/
│   ├── Models/
│   └── Policies/
│       └── UserPolicy.php
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
│   ├── scripts/
│   └── styles/
│       └── frontend-common.css
├── resources/
│   └── views/
│       ├── dashboard.blade.php
│       ├── products/
│       ├── sales/
│       ├── receivables/
│       ├── customers/
│       ├── users/
│       └── products/partials/sidebar.blade.php
└── routes/
    └── web.php
```

## Routes

| Route | Description |
|-------|-------------|
| `/` | Redirect to dashboard or login |
| `/login` | Login page |
| `/dashboard` | Dashboard overview |
| `/products` | Product management (inventory) |
| `/products/create` | Add product |
| `/products/{id}/edit` | Edit product |
| `/sales` | Sales list |
| `/sales/create` | New sale |
| `/receivables` | Receivables list |
| `/receivables/{id}/edit` | Record payment |
| `/customers` | Customers list |
| `/customers/create` | Add customer |
| `/customers/{id}/statement` | Customer statement |
| `/users` | User management (admin/manager only) |

## Quick Start

```bash
cd laibaindustry-erp
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Visit `http://127.0.0.1:8000` and log in with seeded credentials (check `database/seeders/DatabaseSeeder.php`).

## Database

- **Default:** SQLite at `database/database.sqlite`
- **Production:** MySQL (configured via `.env` in deploy workflow)

### Main Tables

- `users` – Auth with roles (admin, manager, viewer)
- `products` – SKU, prices, stock, reorder level
- `categories`, `currencies`
- `sales`, `sales_items`
- `receivables`
- `customers`

## Deployment (cPanel)

The project includes a GitHub Actions workflow (`.github/workflows/deploy-cpanel.yml`) that deploys to cPanel via FTP.

**To deploy:** Go to Actions → Deploy to cPanel → Run workflow.

### Required GitHub Secrets

| Secret | Description |
|--------|-------------|
| `APP_NAME` | Laravel app name |
| `APP_KEY` | Laravel encryption key (`php artisan key:generate --show`) |
| `APP_URL` | Full site URL (e.g. `https://yourdomain.com`) |
| `DB_HOST` | MySQL host (often `localhost` on cPanel) |
| `SQL_DB` | Database name |
| `SQL_USER` | Database user |
| `SQL_PWD` | Database password |
| `FTP_SERVER` | FTP hostname (e.g. `ftp.yourdomain.com`) |
| `FTP_USERNAME` | cPanel FTP username |
| `FTP_PASSWORD` | FTP password |
| `FTP_SERVER_DIR` | Target directory (e.g. `/public_html`) |

### What the workflow does

1. Installs Composer dependencies (production only)
2. Builds deploy package (excludes .git, tests, .env)
3. Generates `.env` from secrets with MySQL config
4. Flattens `public` into project root for cPanel document root
5. Uploads all files via FTP-Deploy-Action

## UI Notes

- Layout: sidebar navigation, card-based content
- Background: light `#f6f7f8`, dark `#101922`
- Tailwind CDN with custom primary color `#137fec`
- Auth pages: split layout with hero image on the left

## Implementation Notes

- **Dashboard:** `DashboardController` fetches real data from Sales, Receivables, Customers, Products
- **AuthorizesRequests:** Base `Controller` uses Laravel’s `AuthorizesRequests` trait for policy checks
- **Receivables:** Created from `SaleController::store` when a sale is saved
- **Customers:** Created from sale form when a new customer is used; existing customers autofill on select
