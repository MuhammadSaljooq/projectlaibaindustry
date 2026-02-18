# Laiba Industry ERP (Laravel MVC)

Simple Laravel MVC backend project generated from the provided SQL schema.

## Access URLs

When running in Codespaces (current setup):

- Base app (frontend entry for this project): `http://127.0.0.1:8080/`
- Root response: JSON health-style message

Main resource URLs:

- `/categories`
- `/currencies`
- `/exchange-rates`
- `/products`
- `/tax-settings`
- `/sales`
- `/sale-items`
- `/receivables`
- `/purchases`
- `/purchase-items`
- `/customers`
- `/payables`

## Quick Start

From this directory (`laibaindustry-erp`):

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve --host=0.0.0.0 --port=8080
```

## Database

- Current default connection: `sqlite` (see `.env`)
- SQLite file: `database/database.sqlite`
- Session driver: `database` (requires `sessions` table, already migrated)

Schema reference used for this Laravel setup exists in repository root:

- `../database/schema.sql`

## Project Structure (Important Folders)

```text
laibaindustry-erp/
├── app/
│   ├── Http/Controllers/   # MVC Controllers
│   └── Models/             # Eloquent Models
├── database/
│   ├── migrations/         # Table definitions/migrations
│   └── seeders/            # Default data
├── routes/
│   └── web.php             # Application routes
├── resources/              # Views/assets (if needed)
├── public/                 # Web root
└── README.md
```

## Notes

- This project currently behaves as a backend/API-style Laravel app.
- Controllers are generated as resource controllers and can be expanded with validation/business logic as needed.
