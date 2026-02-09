# Fix: Website not updating after deploy

If the live site (e.g. laibaindustrysialkot.com) still shows the old version after deploy, the files are likely going to a different folder than the one your domain uses.

## 1. Find your domain’s document root in cPanel

1. Log in to **cPanel** (hoster.pk).
2. Open **Domains** (or **Addon Domains** if this is an addon).
3. Find **laibaindustrysialkot.com** and note the **Document Root** (e.g. `public_html` or `public_html/laibaindustrysialkot.com`).

## 2. Set the deploy path in GitHub

1. On GitHub: repo **Settings** → **Secrets and variables** → **Actions**.
2. Add or edit secret:
   - **Name:** `FTP_SERVER_DIR`
   - **Value:** the path **relative to your FTP login root**, **ending with `/`**:
     - If your FTP account’s root is your **home** and the domain’s doc root is **public_html** → use: `/public_html/`
     - If the domain’s doc root is **public_html/laibaindustrysialkot.com** → use: `/public_html/laibaindustrysialkot.com/`
     - If your FTP account’s root is **already public_html** (so “/” is public_html) → leave `FTP_SERVER_DIR` **unset** (we use `/`).
   - Always use a **leading and trailing slash** (e.g. `/public_html/`).

## 3. Check what the site is actually serving

1. Open **https://laibaindustrysialkot.com**
2. **Right‑click** → **View Page Source** (or Ctrl+U / Cmd+Option+U).
3. In the first lines you should see a comment like:  
   `<!-- build-20260209-121500Z -->`  
   If you see that, the **new** build is being served (try a hard refresh: Ctrl+Shift+R).  
   If you **don’t** see that comment, the server is still serving an old file or a different folder.

## 4. Confirm files in cPanel

1. In cPanel → **File Manager**, go to the **document root** from step 1.
2. Check **index.html** “Last modified” time – it should be from the last deploy.
3. If it’s old, set **FTP_SERVER_DIR** as in step 2, then run **Actions** → **Deploy to hoster.pk** again.
