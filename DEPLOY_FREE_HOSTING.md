# 🆓 Deploy to Free Hosting - Complete Guide

## 📋 Overview

This guide will help you deploy your Inventory & Sales Management System to **free hosting services** like InfinityFree, 000webhost, or similar PHP/MySQL hosting providers.

## 🎯 Recommended Free Hosting Providers

### 1. **InfinityFree** (⭐ Recommended)
- ✅ **5 GB disk space** with unlimited bandwidth
- ✅ **PHP 8.3** and **MySQL 8.0/MariaDB 11.4**
- ✅ **400 MySQL databases** allowed
- ✅ **99.9% uptime** guarantee
- ✅ **No ads** on your site
- ✅ **Free SSL** certificates
- ✅ **Free subdomain** (e.g., `yoursite.infinityfreeapp.com`)
- ✅ **Completely free forever** - no credit card required
- 🌐 **Website:** https://www.infinityfree.com/

### 2. **000webhost**
- ✅ Unlimited MySQL databases
- ✅ PHP support
- ⚠️ Free plan has some limitations
- 🌐 **Website:** https://www.000webhost.com/

### 3. **Freehostia**
- ✅ 250 MB disk space
- ✅ PHP and MySQL support
- 🌐 **Website:** https://www.freehostia.com/

---

## 🚀 Quick Start - InfinityFree (Recommended)

### Step 1: Create Account & Website (5 min)

1. **Sign Up:**
   - Go to https://www.infinityfree.com/
   - Click **"Sign Up"** → Create free account
   - Verify your email

2. **Create Website:**
   - Login to **InfinityFree Control Panel**
   - Click **"Create Website"**
   - Choose a subdomain (e.g., `inventory-sales.infinityfreeapp.com`)
   - Select **PHP 8.3** (or latest available)
   - Click **"Create Website"**

---

### Step 2: Database Setup (5 min)

1. **Create Database:**
   - In Control Panel, go to **"MySQL Databases"**
   - Click **"Create Database"**
   - Database name: `inventory_sales` (or your choice)
   - Username: Auto-generated (save it!)
   - Password: Create a strong password (save it!)
   - Click **"Create Database"**

2. **Import Schema:**
   - Go to **"phpMyAdmin"** in Control Panel
   - Select your database from left sidebar
   - Click **"Import"** tab
   - Choose file: `backend-php/database/schema.sql`
   - Click **"Go"** to import

**📝 Save these credentials:**
```
Database Host: sqlXXX.infinityfree.com (or localhost)
Database Name: epiz_XXXXXX_inventory_sales
Database User: epiz_XXXXXX_dbuser
Database Password: your_password
```

---

### Step 3: Upload Backend Files (10 min)

**Option A: Using File Manager (Easiest)**

1. In Control Panel, click **"File Manager"**
2. Navigate to `htdocs/` folder (this is your website root)
3. Create folder: `api`
4. Upload **all files** from `backend-php/` folder to `htdocs/api/`
   - Maintain folder structure:
     ```
     htdocs/api/
     ├── .htaccess
     ├── index.php
     ├── config/
     ├── middleware/
     ├── utils/
     ├── database/
     └── api/
     ```

**Option B: Using FTP**

1. In Control Panel, go to **"FTP Accounts"**
2. Note your FTP credentials:
   - Host: `ftpupload.net` (or provided)
   - Username: Your FTP username
   - Password: Your FTP password
3. Use FTP client (FileZilla, WinSCP, etc.)
4. Connect and upload to `htdocs/api/`

---

### Step 4: Configure Backend (5 min)

1. **Create `.env` file:**
   - In File Manager, navigate to `htdocs/api/`
   - Create new file: `.env`
   - Add this content (replace with YOUR database credentials):

```env
DB_HOST=sqlXXX.infinityfree.com
DB_PORT=3306
DB_DATABASE=epiz_XXXXXX_inventory_sales
DB_USERNAME=epiz_XXXXXX_dbuser
DB_PASSWORD=your_password_here
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yoursite.infinityfreeapp.com
JWT_SECRET=your-random-secret-key-here-min-32-chars
CORS_ALLOWED_ORIGINS=https://yoursite.infinityfreeapp.com
```

**🔐 Generate JWT_SECRET:**
```bash
# Run this locally to generate a secure secret
openssl rand -base64 32
```

2. **Update `.htaccess` in `api/` folder:**
   - Ensure it exists and contains:
   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php [QSA,L]
   ```

---

### Step 5: Build & Upload Frontend (10 min)

1. **Build Frontend Locally:**
   ```bash
   cd frontend-vue
   npm install
   npm run build
   ```
   This creates a `dist/` folder with production files.

2. **Configure API URL:**
   - Before building, create/update `frontend-vue/.env.production`:
   ```env
   VITE_API_URL=https://yoursite.infinityfreeapp.com/api
   ```
   - Then rebuild: `npm run build`

3. **Upload Frontend:**
   - In File Manager, go to `htdocs/`
   - Upload **all contents** of `frontend-vue/dist/` to `htdocs/`
   - Create/update `.htaccess` in `htdocs/`:

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

---

### Step 6: Test Your Deployment (5 min)

1. **Test API:**
   - Visit: `https://yoursite.infinityfreeapp.com/api/health`
   - Should return: `{"status":"ok","message":"Server is running"}`

2. **Test Frontend:**
   - Visit: `https://yoursite.infinityfreeapp.com`
   - Should load your Vue.js application

3. **Test Database Connection:**
   - Try creating a product in the app
   - Check phpMyAdmin to verify data was saved

---

## 📁 File Structure on Free Hosting

```
htdocs/                          (or public_html/ on some hosts)
├── .htaccess                    ← Frontend routing
├── index.html                   ← Frontend entry
├── assets/                      ← Frontend JS/CSS
│   ├── index-xxxxx.js
│   └── index-xxxxx.css
│
└── api/                         ← Backend
    ├── .htaccess                ← API routing
    ├── index.php                ← API entry
    ├── .env                     ← Config (create this)
    ├── config/
    │   ├── config.php
    │   └── database.php
    ├── middleware/
    │   └── cors.php
    ├── utils/
    │   ├── response.php
    │   └── validation.php
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

---

## 🔧 Configuration Details

### Backend Configuration (`api/.env`)

```env
# Database (from your hosting provider)
DB_HOST=sqlXXX.infinityfree.com
DB_PORT=3306
DB_DATABASE=epiz_XXXXXX_inventory_sales
DB_USERNAME=epiz_XXXXXX_dbuser
DB_PASSWORD=your_password

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yoursite.infinityfreeapp.com

# Security
JWT_SECRET=your-random-secret-key-min-32-characters-long

# CORS (allow your frontend domain)
CORS_ALLOWED_ORIGINS=https://yoursite.infinityfreeapp.com
```

### Frontend Configuration

**Before building, set API URL:**

1. Create `frontend-vue/.env.production`:
```env
VITE_API_URL=https://yoursite.infinityfreeapp.com/api
```

2. Or update `frontend-vue/src/utils/api.js` to auto-detect:
```javascript
const getApiUrl = () => {
  if (import.meta.env.VITE_API_URL) {
    return import.meta.env.VITE_API_URL
  }
  
  // Auto-detect from current domain
  if (typeof window !== 'undefined') {
    const origin = window.location.origin
    return `${origin}/api`
  }
  
  return '/api' // Fallback to relative path
}
```

---

## ✅ Verification Checklist

After deployment, verify:

- [ ] `https://yoursite.infinityfreeapp.com/api/health` returns JSON
- [ ] `https://yoursite.infinityfreeapp.com` loads frontend
- [ ] Can create products (database working)
- [ ] Can create sales entries
- [ ] Can view receivables
- [ ] No errors in browser console (F12)
- [ ] No CORS errors
- [ ] Data persists after refresh

---

## 🆘 Troubleshooting

### API Returns 404

**Problem:** `https://yoursite.infinityfreeapp.com/api/health` returns 404

**Solutions:**
1. Check `.htaccess` exists in `api/` folder
2. Verify `mod_rewrite` is enabled (usually is on free hosts)
3. Check file paths in `index.php` are correct
4. Try accessing: `https://yoursite.infinityfreeapp.com/api/index.php` directly

### Database Connection Failed

**Problem:** API returns "Database connection failed"

**Solutions:**
1. Verify database credentials in `.env`
2. Check database host (might be `localhost` instead of `sqlXXX.infinityfree.com`)
3. Ensure database user has proper permissions
4. Verify database exists in phpMyAdmin
5. Check if database host allows remote connections (some hosts require `localhost`)

### Frontend Shows Blank Page

**Problem:** Frontend loads but shows blank/white page

**Solutions:**
1. Check browser console (F12) for errors
2. Verify `index.html` exists in `htdocs/`
3. Check `.htaccess` in `htdocs/` is correct
4. Verify API URL in frontend matches your domain
5. Check if assets are loading (Network tab in DevTools)

### CORS Errors

**Problem:** Browser console shows CORS errors

**Solutions:**
1. Update `CORS_ALLOWED_ORIGINS` in `api/.env` to match your domain
2. Check `middleware/cors.php` is being loaded
3. Verify `.htaccess` allows CORS headers
4. Some free hosts may restrict CORS - contact support if needed

### 500 Internal Server Error

**Problem:** API returns 500 error

**Solutions:**
1. Check error logs in hosting control panel
2. Verify PHP version is 8.1+ (check in Control Panel)
3. Ensure required PHP extensions are enabled:
   - PDO
   - PDO_MySQL
   - OpenSSL
   - JSON
   - mbstring
4. Check file permissions (usually 644 for files, 755 for folders)
5. Verify `.env` file syntax is correct (no extra spaces, quotes)

### Assets Not Loading (404 for JS/CSS)

**Problem:** Frontend loads but styles/scripts don't work

**Solutions:**
1. Verify `assets/` folder was uploaded correctly
2. Check `index.html` references correct asset paths
3. Ensure `.htaccess` doesn't block asset files
4. Rebuild frontend and re-upload if needed

---

## 🔄 Updating Your Application

When you make changes:

1. **Update Backend:**
   - Upload changed PHP files via File Manager or FTP
   - No rebuild needed for PHP

2. **Update Frontend:**
   ```bash
   cd frontend-vue
   npm run build
   ```
   - Upload new `dist/` contents to `htdocs/`
   - Clear browser cache (Ctrl+Shift+R)

3. **Update Database:**
   - Run SQL migrations via phpMyAdmin
   - Or update `schema.sql` and re-import

---

## 📝 Important Notes

### Free Hosting Limitations

- ⚠️ **No SSH access** - Use File Manager or FTP
- ⚠️ **Limited resources** - May be slower than paid hosting
- ⚠️ **No guaranteed uptime** - May have occasional downtime
- ⚠️ **Limited support** - Usually forums/documentation only
- ⚠️ **Subdomain only** - Can't use custom domain on free plan (usually)

### Best Practices

1. **Backup regularly** - Download database via phpMyAdmin
2. **Keep credentials secure** - Don't share `.env` file
3. **Monitor usage** - Free plans have resource limits
4. **Test thoroughly** - Free hosts may have quirks
5. **Consider paid hosting** - For production/business use

---

## 🎉 Success!

Once deployed, your app will be live at:
- **Frontend:** `https://yoursite.infinityfreeapp.com`
- **API:** `https://yoursite.infinityfreeapp.com/api`

### Next Steps

1. ✅ Test all features (products, sales, receivables)
2. ✅ Share your app URL with users
3. ✅ Monitor for any errors
4. ✅ Consider upgrading to paid hosting for better performance

---

## 📞 Need Help?

1. Check hosting provider's documentation
2. Review error logs in control panel
3. Check browser console (F12) for frontend errors
4. Verify all configuration files are correct
5. Test API endpoints directly (e.g., `/api/health`)

Good luck with your free hosting deployment! 🚀
