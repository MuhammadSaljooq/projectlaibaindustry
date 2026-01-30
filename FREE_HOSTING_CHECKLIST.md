# ✅ Free Hosting Deployment Checklist

Use this checklist to ensure a smooth deployment to free hosting.

## 📋 Pre-Deployment

- [ ] Choose free hosting provider (InfinityFree recommended)
- [ ] Create account and verify email
- [ ] Create website/subdomain
- [ ] Note your website URL

## 🗄️ Database Setup

- [ ] Create MySQL database in control panel
- [ ] Note database credentials:
  - [ ] Database host
  - [ ] Database name
  - [ ] Database username
  - [ ] Database password
- [ ] Import `backend-php/database/schema.sql` via phpMyAdmin
- [ ] Verify tables were created

## 🔧 Backend Configuration

- [ ] Upload backend files to `api/` folder
- [ ] Create `.env` file in `api/` folder
- [ ] Configure database credentials in `.env`
- [ ] Set `APP_URL` to your website URL
- [ ] Generate `JWT_SECRET` (32+ characters)
- [ ] Set `CORS_ALLOWED_ORIGINS` to your website URL
- [ ] Verify `.htaccess` exists in `api/` folder

## 🎨 Frontend Configuration

- [ ] Run deployment script: `./DEPLOY_FREE_HOSTING.sh`
- [ ] Or manually:
  - [ ] Create `frontend-vue/.env.production` with API URL
  - [ ] Run `npm run build` in `frontend-vue/`
- [ ] Upload `dist/` contents to website root
- [ ] Verify `.htaccess` exists in root

## ✅ Testing

- [ ] Test API health: `https://yoursite.com/api/health`
- [ ] Test frontend loads: `https://yoursite.com`
- [ ] Test creating a product
- [ ] Test creating a sale
- [ ] Test receivables page
- [ ] Check browser console for errors (F12)
- [ ] Verify data persists after refresh

## 🔍 Verification

- [ ] No 404 errors
- [ ] No CORS errors
- [ ] No database connection errors
- [ ] All features working
- [ ] Data saving correctly
- [ ] Search/filter working
- [ ] Calculations correct (VAT, subtotals)

## 📝 Post-Deployment

- [ ] Bookmark your website URL
- [ ] Save database credentials securely
- [ ] Document any custom configurations
- [ ] Set up regular backups (download database via phpMyAdmin)

---

## 🆘 If Something Goes Wrong

1. Check error logs in hosting control panel
2. Verify all configuration files are correct
3. Check browser console (F12) for frontend errors
4. Test API endpoints directly
5. Review `DEPLOY_FREE_HOSTING.md` troubleshooting section

---

**Deployment Date:** _______________
**Website URL:** _______________
**Database Name:** _______________
