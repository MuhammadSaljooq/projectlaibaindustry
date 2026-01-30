# 🚀 Deploy to cPanel - Complete Guide

## 📋 Overview

This guide will help you deploy your Inventory & Sales Management System to cPanel hosting.

## 🎯 Quick Start (5 Minutes)

### 1️⃣ Database Setup (2 min)

1. **cPanel → MySQL Databases**
   - Create database: `inventory_sales`
   - Create user with password
   - Add user to database → **ALL PRIVILEGES**

2. **cPanel → phpMyAdmin**
   - Select your database
   - Import: `backend-php/database/schema.sql`

### 2️⃣ Upload Backend (2 min)

**Using File Manager:**
- Upload `backend-php/` folder contents to `public_html/api/`
- Maintain folder structure

**Using FTP:**
- Connect to your cPanel via FTP
- Upload to `public_html/api/`

### 3️⃣ Configure Backend (1 min)

Create `public_html/api/.env`:
```env
DB_HOST=localhost
DB_DATABASE=yourusername_inventory_sales
DB_USERNAME=yourusername_dbuser
DB_PASSWORD=your_password
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
JWT_SECRET=random-secret-key-here
CORS_ALLOWED_ORIGINS=https://yourdomain.com
```

### 4️⃣ Upload Frontend (2 min)

**Build Frontend:**
```bash
# For React
cd frontend
npm run build

# OR For Vue.js
cd frontend-vue
npm run build
```

**Upload:**
- Upload `dist/` folder contents to `public_html/`
- Upload `public_html/.htaccess` (provided file)

### 5️⃣ Configure PHP (1 min)

- **cPanel → Select PHP Version**
- Select **PHP 8.1+**
- Enable: **PDO, PDO_MySQL, OpenSSL, JSON, mbstring**

### 6️⃣ Test

- API: `https://yourdomain.com/api/health`
- Frontend: `https://yourdomain.com`

---

## 📁 File Structure in cPanel

```
public_html/
├── .htaccess              ← Frontend routing
├── index.html             ← Frontend entry
├── assets/                ← Frontend JS/CSS
│
└── api/                   ← Backend
    ├── .htaccess          ← API routing
    ├── index.php          ← API entry
    ├── .env               ← Config (create this)
    ├── config/
    ├── middleware/
    ├── utils/
    ├── database/
    └── api/
        ├── products.php
        ├── categories.php
        ├── sales.php
        ├── receivables.php
        ├── tax.php
        ├── currencies.php
        └── analytics.php
```

---

## 🔧 Configuration Files

### `public_html/.htaccess` (Frontend)
```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /
  
  # API requests
  RewriteCond %{REQUEST_URI} ^/api/
  RewriteRule ^api/(.*)$ api/index.php [L,QSA]
  
  # Frontend SPA routing
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule ^(.*)$ index.html [L]
</IfModule>
```

### `public_html/api/.htaccess` (Backend)
```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /api/
  
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule ^(.*)$ index.php [L,QSA]
</IfModule>
```

---

## ✅ Verification Checklist

After deployment, verify:

- [ ] `https://yourdomain.com/api/health` returns JSON
- [ ] `https://yourdomain.com` loads frontend
- [ ] Can create products
- [ ] Can create sales
- [ ] Database saves data
- [ ] No errors in browser console
- [ ] No errors in cPanel error logs

---

## 🆘 Common Issues

### API Returns 404
- Check `.htaccess` in `api/` folder
- Verify `mod_rewrite` is enabled
- Check file paths in `index.php`

### Database Connection Failed
- Verify credentials in `.env`
- Check database user permissions
- Ensure database exists

### Frontend Blank Page
- Check `.htaccess` in `public_html/`
- Verify `index.html` exists
- Check browser console for errors

### 500 Internal Server Error
- Check PHP version (8.1+)
- Check error logs in cPanel
- Verify file permissions

---

## 📞 Need Help?

1. Check `CPANEL_DEPLOYMENT.md` for detailed guide
2. Check `CPANEL_QUICK_START.md` for quick reference
3. Check `cpanel-setup-guide.md` for step-by-step
4. Review error logs in cPanel

---

## 🎉 Success!

Once deployed, your app will be live at:
- **Frontend:** `https://yourdomain.com`
- **API:** `https://yourdomain.com/api`

Good luck with your deployment! 🚀
