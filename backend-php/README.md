# PHP Backend - Inventory & Sales Management System

This is the PHP/MySQL version of the backend API.

## Prerequisites

- PHP 8.1 or higher
- MySQL 8.0 or MariaDB 10.5
- Composer
- Apache or Nginx (or PHP built-in server)

## Installation

### 1. Install Dependencies

```bash
cd backend-php
composer install
```

### 2. Database Setup

1. Create MySQL database:
```sql
CREATE DATABASE inventory_sales_db;
```

2. Import schema:
```bash
mysql -u root -p inventory_sales_db < database/schema.sql
```

### 3. Environment Configuration

1. Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

2. Edit `.env` with your database credentials:
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_sales_db
DB_USERNAME=root
DB_PASSWORD=your_password

APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000

JWT_SECRET=your-secret-key-change-this

CORS_ALLOWED_ORIGINS=http://localhost:5173
```

## Running the Server

### Option 1: PHP Built-in Server (Development)

```bash
php -S localhost:8000 -t .
```

### Option 2: Apache

1. Configure Apache virtual host pointing to `backend-php` directory
2. Enable mod_rewrite
3. Access via `http://localhost/api/health`

### Option 3: Nginx

Configure Nginx to point to `backend-php` directory with PHP-FPM.

## API Endpoints

All endpoints are prefixed with `/api`

### Products
- `GET /api/products` - List all products
- `GET /api/products/:id` - Get single product
- `POST /api/products` - Create product
- `PUT /api/products/:id` - Update product
- `DELETE /api/products/:id` - Delete product
- `POST /api/products/auto-create` - Auto-create product from name
- `GET /api/products/metrics/inventory` - Get inventory metrics

### Categories
- `GET /api/categories` - List all categories
- `GET /api/categories/:id` - Get single category
- `POST /api/categories` - Create category
- `PUT /api/categories/:id` - Update category
- `DELETE /api/categories/:id` - Delete category

### Sales
- `GET /api/sales` - List all sales (with filters)
- `GET /api/sales/:id` - Get single sale
- `POST /api/sales` - Create sale
- `DELETE /api/sales/:id` - Delete sale

### Receivables
- `GET /api/receivables` - List all receivables (with filters)
- `GET /api/receivables/:id` - Get single receivable
- `POST /api/receivables` - Create receivables (bulk)
- `DELETE /api/receivables/:id` - Delete receivable

### Tax Settings
- `GET /api/tax` - Get tax settings
- `PUT /api/tax/:id` - Update tax settings

### Currencies
- `GET /api/currencies` - List all currencies
- `GET /api/currencies/active` - Get active currencies
- `GET /api/currencies/default` - Get default currency
- `GET /api/currencies/:id` - Get single currency
- `POST /api/currencies` - Create currency
- `PUT /api/currencies/:id` - Update currency
- `DELETE /api/currencies/:id` - Delete currency
- `GET /api/currencies/exchange-rate/:fromCode/:toCode` - Get exchange rate
- `POST /api/currencies/exchange-rate` - Create exchange rate
- `GET /api/currencies/exchange-rates/all` - Get all exchange rates

### Analytics
- `GET /api/analytics/dashboard` - Dashboard summary
- `GET /api/analytics/sales-summary` - Sales summary by period
- `GET /api/analytics/top-products` - Top selling products
- `GET /api/analytics/profit-margins` - Profit margins by category

## Project Structure

```
backend-php/
├── api/              # API route handlers
│   ├── products.php
│   ├── categories.php
│   ├── sales.php
│   ├── receivables.php
│   ├── tax.php
│   ├── currencies.php
│   └── analytics.php
├── config/           # Configuration files
│   ├── database.php
│   └── config.php
├── middleware/       # Middleware
│   └── cors.php
├── utils/            # Utility functions
│   ├── response.php
│   ├── validation.php
│   └── products_helper.php
├── database/         # Database files
│   └── schema.sql
├── vendor/           # Composer dependencies
├── index.php         # Entry point
├── .htaccess         # Apache rewrite rules
├── composer.json     # PHP dependencies
└── README.md         # This file
```

## Features

- ✅ RESTful API
- ✅ MySQL database with proper schema
- ✅ Input validation
- ✅ Error handling
- ✅ CORS support
- ✅ Transaction support for data integrity
- ✅ Stock management
- ✅ Auto-product creation from sales
- ✅ Comprehensive analytics

## Development

The backend runs on `http://localhost:8000` by default.

Update the frontend `.env` file:
```
VITE_API_URL=http://localhost:8000/api
```

## Notes

- The frontend remains unchanged (React)
- All API endpoints maintain the same structure as the Node.js version
- Database uses MySQL instead of PostgreSQL
- Uses PDO for database access instead of Prisma

## Troubleshooting

### Database Connection Issues
- Check MySQL is running
- Verify database credentials in `.env`
- Ensure database exists

### CORS Issues
- Update `CORS_ALLOWED_ORIGINS` in `.env`
- Check Apache/Nginx CORS headers

### 404 Errors
- Ensure mod_rewrite is enabled (Apache)
- Check `.htaccess` file exists
- Verify server configuration

## License

MIT
