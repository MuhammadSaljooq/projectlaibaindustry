# cPanel Deployment - Backend PHP

## Quick Setup for cPanel

### 1. Upload Files

Upload all files from `backend-php/` to:
- `public_html/api/` (main domain)
- OR `public_html/api/` (subdomain like `api.yourdomain.com`)

### 2. Database Setup

1. **Create Database in cPanel:**
   - Go to MySQL Databases
   - Create: `inventory_sales`
   - Create user
   - Add user to database

2. **Import Schema:**
   - Go to phpMyAdmin
   - Select database
   - Import: `database/schema.sql`

### 3. Configure Database Connection

**Option A: Using .env (if supported)**
```env
DB_HOST=localhost
DB_DATABASE=yourusername_inventory_sales
DB_USERNAME=yourusername_dbuser
DB_PASSWORD=your_password
```

**Option B: Direct Configuration**
- Edit `config/database-cpanel.php`
- Update database credentials
- Rename to `database.php` or update `database.php` to use constants

### 4. Set Permissions

- Directories: 755
- Files: 644
- `.env`: 600 (if using)

### 5. Configure PHP

- PHP 8.1 or higher
- Enable: PDO, PDO_MySQL, OpenSSL, JSON, mbstring

### 6. Test

Visit: `https://yourdomain.com/api/health`

Should return: `{"status":"ok","message":"Server is running"}`

## File Structure in cPanel

```
public_html/api/
├── index.php          # Entry point
├── .htaccess         # Routing
├── .env              # Configuration (create this)
├── config/
│   ├── database.php
│   └── config.php
├── middleware/
│   └── cors.php
├── utils/
│   ├── response.php
│   ├── validation.php
│   └── products_helper.php
├── database/
│   └── schema.sql
└── api/
    ├── products.php
    ├── categories.php
    ├── sales.php
    ├── receivables.php
    ├── tax.php
    ├── currencies.php
    └── analytics.php
```

## .htaccess for API

Create `public_html/api/.htaccess`:
```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /api/
  
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule ^(.*)$ index.php [L,QSA]
</IfModule>
```

## Troubleshooting

- **500 Error:** Check PHP version, .htaccess, error logs
- **404 Error:** Check .htaccess, routing in index.php
- **Database Error:** Check credentials, user permissions
- **CORS Error:** Update CORS_ALLOWED_ORIGINS in config
