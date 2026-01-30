# 🚀 Deploy to Render.com - Complete Guide

## 📋 Overview

This guide will help you deploy your Inventory & Sales Management System to **Render.com**, a modern cloud platform that offers:
- ✅ **Free tier** for static sites and web services
- ✅ **PostgreSQL database** (free tier available)
- ✅ **Automatic SSL** certificates
- ✅ **Git-based deployments**
- ✅ **Zero-downtime deployments**

## 🎯 Architecture on Render

Your application will be deployed as:

1. **Frontend (Vue.js)** → Render **Static Site** (free)
2. **Backend (Node.js/Express)** → Render **Web Service** (free tier available)
3. **Database** → Render **PostgreSQL** (free tier available)

---

## 🚀 Quick Start (15 Minutes)

### Step 1: Prepare Your Repository (2 min)

1. **Push to GitHub:**
   ```bash
   git init
   git add .
   git commit -m "Initial commit"
   git remote add origin https://github.com/yourusername/your-repo.git
   git push -u origin main
   ```

2. **Ensure your repo structure:**
   ```
   .
   ├── backend/          # Node.js backend
   ├── frontend-vue/     # Vue.js frontend
   └── render.yaml       # Render configuration (we'll create this)
   ```

---

### Step 2: Create Render Account (1 min)

1. Go to https://render.com/
2. Sign up with **GitHub** (recommended) or email
3. Verify your email

---

### Step 3: Create PostgreSQL Database (3 min)

1. **Dashboard → New → PostgreSQL**
2. **Configuration:**
   - **Name:** `inventory-sales-db`
   - **Database:** `inventory_sales_db`
   - **User:** Auto-generated
   - **Region:** Choose closest to you
   - **PostgreSQL Version:** 16 (or latest)
   - **Plan:** **Free** (or Starter for production)
3. Click **"Create Database"**
4. **Save the connection string** (Internal Database URL):
   ```
   postgresql://user:password@dpg-xxxxx-a/inventory_sales_db
   ```

---

### Step 4: Deploy Backend (5 min)

1. **Dashboard → New → Web Service**
2. **Connect Repository:**
   - Select your GitHub repository
   - Or connect via GitHub integration
3. **Configuration:**
   - **Name:** `inventory-sales-api`
   - **Region:** Same as database
   - **Branch:** `main` (or your default branch)
   - **Root Directory:** `backend`
   - **Runtime:** `Node`
   - **Build Command:** `npm install && npx prisma generate && npx prisma migrate deploy`
   - **Start Command:** `npm start`
   - **Plan:** **Free** (or Starter for production)
4. **Environment Variables:**
   - Click **"Advanced"** → **"Add Environment Variable"**
   - Add these variables:
     ```
     DATABASE_URL=<Internal Database URL from Step 3>
     JWT_SECRET=<generate with: openssl rand -base64 32>
     NODE_ENV=production
     PORT=10000
     ```
   - **Important:** Use the **Internal Database URL** (starts with `postgresql://`) from your PostgreSQL service
5. Click **"Create Web Service"**

**Wait for deployment** (takes 2-5 minutes)

---

### Step 5: Deploy Frontend (4 min)

1. **Dashboard → New → Static Site**
2. **Connect Repository:**
   - Select same GitHub repository
3. **Configuration:**
   - **Name:** `inventory-sales-frontend`
   - **Branch:** `main`
   - **Root Directory:** `frontend-vue`
   - **Build Command:** `npm install && npm run build`
   - **Publish Directory:** `dist`
4. **Environment Variables:**
   - Click **"Add Environment Variable"**
   - Add:
     ```
     VITE_API_URL=https://inventory-sales-api.onrender.com/api
     ```
   - **Note:** Replace `inventory-sales-api` with your actual backend service name
5. Click **"Create Static Site"**

**Wait for deployment** (takes 2-3 minutes)

---

### Step 6: Update Frontend API URL (2 min)

After backend deploys, you'll get a URL like:
```
https://inventory-sales-api.onrender.com
```

1. **Go to Frontend service → Environment**
2. **Update `VITE_API_URL`:**
   ```
   VITE_API_URL=https://inventory-sales-api.onrender.com/api
   ```
3. **Redeploy** frontend (or it will auto-redeploy)

---

### Step 7: Run Database Migrations (2 min)

1. **Go to Backend service → Shell**
2. **Run migrations:**
   ```bash
   npx prisma migrate deploy
   ```
3. **Verify tables created:**
   ```bash
   npx prisma studio
   ```
   (This opens Prisma Studio in your browser)

---

## ✅ Testing Your Deployment

1. **Test Backend:**
   - Visit: `https://inventory-sales-api.onrender.com/api/health`
   - Should return: `{"status":"ok","message":"Server is running"}`

2. **Test Frontend:**
   - Visit: `https://inventory-sales-frontend.onrender.com`
   - Should load your Vue.js application

3. **Test Database:**
   - Create a product in the app
   - Check Prisma Studio or Render database dashboard

---

## 📁 Project Structure for Render

```
.
├── backend/
│   ├── server.js
│   ├── package.json
│   ├── prisma/
│   │   └── schema.prisma
│   └── .env.example
│
├── frontend-vue/
│   ├── src/
│   ├── package.json
│   ├── vite.config.js
│   └── .env.production.example
│
└── render.yaml          # Optional: Infrastructure as Code
```

---

## 🔧 Configuration Files

### `render.yaml` (Optional - Infrastructure as Code)

Create this file in your repo root for easier management:

```yaml
services:
  # Backend API
  - type: web
    name: inventory-sales-api
    env: node
    plan: free
    buildCommand: npm install && npx prisma generate && npx prisma migrate deploy
    startCommand: npm start
    envVars:
      - key: DATABASE_URL
        fromDatabase:
          name: inventory-sales-db
          property: connectionString
      - key: JWT_SECRET
        generateValue: true
      - key: NODE_ENV
        value: production
      - key: PORT
        value: 10000

  # Frontend Static Site
  - type: web
    name: inventory-sales-frontend
    env: static
    buildCommand: npm install && npm run build
    staticPublishPath: ./dist
    envVars:
      - key: VITE_API_URL
        fromService:
          type: web
          name: inventory-sales-api
          property: host
        suffix: /api

databases:
  - name: inventory-sales-db
    plan: free
    databaseName: inventory_sales_db
    user: inventory_user
```

**To use `render.yaml`:**
1. Create the file in your repo root
2. Push to GitHub
3. **Dashboard → New → Blueprint**
4. Connect your repo
5. Render will create all services automatically

---

### Backend Environment Variables

Required in Render backend service:

```env
DATABASE_URL=postgresql://user:pass@host:5432/dbname
JWT_SECRET=your-secret-key-min-32-chars
NODE_ENV=production
PORT=10000
```

**Note:** Render automatically provides `PORT` environment variable. Your `server.js` should use:
```javascript
const PORT = process.env.PORT || 5000
```

---

### Frontend Environment Variables

Required in Render frontend service:

```env
VITE_API_URL=https://your-backend-service.onrender.com/api
```

**Important:** Update this after backend deploys to use the actual backend URL.

---

## 🔄 Updating Your Application

### Automatic Deployments

Render automatically deploys when you push to your connected branch:
```bash
git add .
git commit -m "Your changes"
git push origin main
```

### Manual Deployments

1. Go to your service in Render dashboard
2. Click **"Manual Deploy"**
3. Select branch/commit

---

## 🆘 Troubleshooting

### Backend Won't Start

**Problem:** Backend service shows "Failed" status

**Solutions:**
1. Check **Logs** tab in Render dashboard
2. Verify `DATABASE_URL` is correct (use Internal Database URL)
3. Ensure `package.json` has `"start": "node server.js"`
4. Check Prisma migrations ran successfully
5. Verify `PORT` environment variable (Render uses dynamic ports)

### Database Connection Failed

**Problem:** API returns database connection errors

**Solutions:**
1. Use **Internal Database URL** (not External)
2. Verify database service is running
3. Check database credentials in Render dashboard
4. Ensure migrations ran: `npx prisma migrate deploy`

### Frontend Shows Blank Page

**Problem:** Frontend loads but shows white/blank page

**Solutions:**
1. Check browser console (F12) for errors
2. Verify `VITE_API_URL` points to correct backend URL
3. Check **Build Logs** in Render dashboard
4. Ensure `dist/` folder contains built files
5. Verify `Publish Directory` is set to `dist`

### CORS Errors

**Problem:** Browser console shows CORS errors

**Solutions:**
1. Update backend CORS to allow frontend domain:
   ```javascript
   app.use(cors({
     origin: ['https://your-frontend.onrender.com', 'http://localhost:5173']
   }))
   ```
2. Or use environment variable:
   ```env
   CORS_ORIGIN=https://your-frontend.onrender.com
   ```

### Build Fails

**Problem:** Build command fails

**Solutions:**
1. Check **Build Logs** in Render dashboard
2. Verify all dependencies in `package.json`
3. Ensure Node.js version is compatible (Render uses Node 18+)
4. Check for syntax errors in code
5. Verify Prisma schema is valid

### Slow Cold Starts (Free Tier)

**Problem:** First request after inactivity is slow

**Solutions:**
- This is normal on free tier (services sleep after 15 min inactivity)
- Consider upgrading to **Starter** plan ($7/month) for always-on
- Or use a cron job to ping your service every 10 minutes

---

## 💰 Pricing & Limits

### Free Tier Limits

**Web Services:**
- ✅ 750 hours/month (enough for 24/7 if only one service)
- ⚠️ Services **sleep after 15 minutes** of inactivity
- ⚠️ Cold start takes ~30 seconds after sleep

**PostgreSQL Database:**
- ✅ 1 GB storage
- ✅ 90 days retention
- ✅ Shared CPU/RAM

**Static Sites:**
- ✅ Unlimited
- ✅ Always on
- ✅ No sleep

### Recommended for Production

**Starter Plan ($7/month per service):**
- ✅ Always on (no sleep)
- ✅ Faster cold starts
- ✅ Better performance

---

## 🔐 Security Best Practices

1. **Environment Variables:**
   - Never commit `.env` files
   - Use Render's environment variables
   - Rotate `JWT_SECRET` regularly

2. **Database:**
   - Use Internal Database URL (not External)
   - Enable SSL connections
   - Regular backups

3. **API:**
   - Add authentication/authorization
   - Rate limiting
   - Input validation

4. **CORS:**
   - Only allow specific origins
   - Don't use `*` in production

---

## 📝 Post-Deployment Checklist

- [ ] Backend health check works
- [ ] Frontend loads correctly
- [ ] Database migrations completed
- [ ] Can create products
- [ ] Can create sales
- [ ] Can view receivables
- [ ] Data persists after refresh
- [ ] No CORS errors
- [ ] SSL certificates active (automatic on Render)
- [ ] Environment variables configured
- [ ] Custom domain configured (optional)

---

## 🎉 Success!

Your app is now live at:
- **Frontend:** `https://inventory-sales-frontend.onrender.com`
- **Backend:** `https://inventory-sales-api.onrender.com/api`
- **Database:** Managed by Render

### Next Steps

1. ✅ Test all features
2. ✅ Set up custom domain (optional)
3. ✅ Configure backups
4. ✅ Monitor usage
5. ✅ Consider upgrading for production use

---

## 📞 Need Help?

1. **Render Documentation:** https://docs.render.com/
2. **Render Community:** https://community.render.com/
3. **Check Logs:** Render Dashboard → Your Service → Logs
4. **Support:** Available on paid plans

Good luck with your Render deployment! 🚀
