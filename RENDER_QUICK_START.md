# 🚀 Render.com Quick Start Guide

## ⚡ 5-Minute Deployment

### Prerequisites
- GitHub account
- Code pushed to GitHub repository

### Step 1: Create Database (1 min)
1. Render Dashboard → **New → PostgreSQL**
2. Name: `inventory-sales-db`
3. Plan: **Free**
4. **Save Internal Database URL**

### Step 2: Deploy Backend (2 min)
1. **New → Web Service**
2. Connect GitHub repo
3. **Settings:**
   - Root Directory: `backend`
   - Build: `npm install && npx prisma generate && npx prisma migrate deploy`
   - Start: `npm start`
4. **Environment Variables:**
   ```
   DATABASE_URL=<Internal Database URL>
   JWT_SECRET=<generate: openssl rand -base64 32>
   NODE_ENV=production
   ```
5. **Create** → Wait for deployment

### Step 3: Deploy Frontend (2 min)
1. **New → Static Site**
2. Connect same repo
3. **Settings:**
   - Root Directory: `frontend-vue`
   - Build: `npm install && npm run build`
   - Publish: `dist`
4. **Environment Variables:**
   ```
   VITE_API_URL=https://your-backend.onrender.com/api
   ```
5. **Create** → Done!

### Step 4: Run Migrations
1. Backend → **Shell**
2. Run: `npx prisma migrate deploy`

## ✅ Test
- Backend: `https://your-backend.onrender.com/api/health`
- Frontend: `https://your-frontend.onrender.com`

## 📝 Using render.yaml (Easier!)

1. Push `render.yaml` to your repo
2. Render Dashboard → **New → Blueprint**
3. Connect repo → **Apply**
4. All services created automatically!

## 🆘 Troubleshooting

**Backend won't start?**
- Check Logs tab
- Verify DATABASE_URL uses Internal URL
- Ensure migrations ran

**Frontend blank?**
- Check VITE_API_URL matches backend URL
- Verify build succeeded
- Check browser console (F12)

**Database errors?**
- Use Internal Database URL (not External)
- Run migrations: `npx prisma migrate deploy`

## 💡 Pro Tips

- **Free tier services sleep** after 15 min inactivity
- First request after sleep takes ~30 seconds
- Upgrade to Starter ($7/mo) for always-on
- Use **Internal Database URL** for better performance

---

**Full Guide:** See `DEPLOY_RENDER.md` for detailed instructions.
