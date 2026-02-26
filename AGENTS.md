# Laiba Industry ERP

## Cursor Cloud specific instructions

### Project layout

The Laravel application lives in `laibaindustry-erp/`. All commands below should be run from that directory.

### Prerequisites (system-level, already installed in the VM snapshot)

- PHP 8.3 with extensions: cli, common, curl, mbstring, xml, zip, sqlite3, bcmath, intl
- Composer (global at `/usr/local/bin/composer`)
- Node.js 22 + npm

### Quick reference

| Task | Command |
|------|---------|
| Install PHP deps | `composer install` |
| Install JS deps | `npm install` |
| Run dev servers | `composer dev` (starts Laravel, queue, Pail, and Vite concurrently) |
| Laravel server only | `php artisan serve` |
| Vite dev server only | `npm run dev` |
| Build assets | `npm run build` |
| Lint (code style) | `./vendor/bin/pint --test` |
| Fix lint | `./vendor/bin/pint` |
| Run tests | `php artisan test` |
| Full setup from scratch | See README.md "Quick Start" section |

### Non-obvious caveats

- **Missing `resources/js/inventory-grid.js`**: The Vite config references this entry point but the repo does not include it. Create it as an empty file (e.g. `// Inventory grid module`) to unblock `npm run build` and `npm run dev`.
- **SQLite database**: Default dev DB is `database/database.sqlite`. Run `touch database/database.sqlite` before `php artisan migrate --seed` if the file doesn't exist.
- **Seeded admin credentials**: `admin@example.com` / `admin123` (see `database/seeders/DatabaseSeeder.php`).
- **Pre-existing test failure**: The default `tests/Feature/ExampleTest.php` expects HTTP 200 on `/` but the app redirects to `/login` (302). This is a pre-existing issue, not caused by setup.
- **Pre-existing lint issues**: `./vendor/bin/pint --test` reports 14 style issues across the codebase. These are pre-existing.
- **Auth column name**: The `users` table uses `password_hash` instead of Laravel's default `password` column.
