# Manual deploy (guaranteed update for live site)

If the automatic FTP deploy still doesn’t show updates on **laibaindustrysialkot.com**, use this **manual upload** so the correct folder gets the new files.

---

## Option A: Use the build from GitHub Actions (recommended)

1. **Get the latest build**
   - Open: **https://github.com/MuhammadSaljooq/projectlaibaindustry/actions**
   - Click the latest **“Deploy to hoster.pk”** run (green check).
   - At the bottom under **Artifacts**, download **cpanel-deploy-zip**.
   - Unzip it on your computer. You should see: `index.html`, `assets/`, `api/`, `.htaccess`, etc.

2. **Find your site’s folder in cPanel**
   - Log in to **cPanel** (hoster.pk).
   - Go to **Domains** (or **Addon Domains**).
   - Find **laibaindustrysialkot.com** and note the **Document Root** (e.g. `public_html` or `public_html/laibaindustrysialkot.com`).

3. **Replace files in that folder**
   - In cPanel open **File Manager**.
   - Go to the **Document Root** from step 2 (e.g. `public_html` or `public_html/laibaindustrysialkot.com`).
   - **Back up `api/.env`** (if it exists): download it or copy its contents somewhere safe. You’ll need it for DB password, etc.
   - **Select all files and folders** in that directory (except you can leave `api/.env` if you want to keep it).
   - **Delete** the selected files (or move to a backup folder). Do **not** delete the folder itself.
   - **Upload** the contents of the unzipped **cpanel-deploy** folder into this same directory (drag and drop or Upload).
   - If you deleted `api/.env`, copy `api/.env.example` to `api/.env` and fill in your database details and `APP_URL` / `CORS_ALLOWED_ORIGINS` (see `AFTER_CLEAN_DEPLOY.md`).

4. **Check the site**
   - Open **https://laibaindustrysialkot.com** in a **private/incognito** window (or hard refresh: Ctrl+Shift+R / Cmd+Shift+R).
   - You should see the new version (Inventory Sheet, Purchase Entry, Payables in the menu).

---

## Option B: Build and upload from your computer

1. **Build the deploy package**
   ```bash
   cd "/Users/shireenafzal/Desktop/mian ali project "
   ./DEPLOY_TO_CPANEL.sh
   ```
   This creates the **cpanel-deploy** folder.

2. **Zip it**
   - Zip the **contents** of the `cpanel-deploy` folder (so the zip contains `index.html`, `api/`, `assets/`, `.htaccess`, etc., not a single “cpanel-deploy” folder).

3. **Upload in cPanel**
   - Follow steps 2–4 from **Option A** above: find Document Root, back up `api/.env`, delete old files, upload the new zip (cPanel can unzip on upload) or upload the extracted files.

---

## If the API stops working after manual deploy

- Restore **api/.env** with your database credentials and `APP_URL` / `CORS_ALLOWED_ORIGINS` (see **AFTER_CLEAN_DEPLOY.md**).
