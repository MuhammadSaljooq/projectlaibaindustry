# Auto-deploy to hoster.pk with GitHub

This guide sets up **automatic deployment** from GitHub to **hoster.pk** (or any cPanel/FTP host). Every push to the `main` branch will build the app and upload it via FTP.

## How it works

1. You push code to GitHub (e.g. `main` branch).
2. GitHub Actions runs: builds the Vue frontend and prepares the cPanel package.
3. The workflow uploads the built files to your hoster.pk account via FTP.

## One-time setup

### 1. Get FTP details from hoster.pk

In your **hoster.pk** control panel (cPanel):

- Go to **FTP Accounts** (or **FTP** section).
- Use an existing FTP user or create one:
  - **Login:** often `yourusername@yourdomain.com` or the username hoster gave you.
  - **Password:** set a strong password.
  - **Directory:** usually `public_html` (this is your site root).
- Note the **FTP server** address. It is often one of:
  - `ftp.yourdomain.com`
  - `yourdomain.com`
  - Or the hostname shown in cPanel (e.g. `server123.hoster.pk`).

### 2. Add GitHub secrets

In your **GitHub repository**:

1. Go to **Settings → Secrets and variables → Actions**.
2. Click **New repository secret** and add:

| Secret name       | Value                          | Example                    |
|-------------------|--------------------------------|----------------------------|
| `FTP_SERVER`      | FTP host (no `ftp://`)         | `ftp.yourdomain.com`       |
| `FTP_USERNAME`    | FTP login                      | `youruser@yourdomain.com`  |
| `FTP_PASSWORD`    | FTP password                  | (your FTP password)        |
- The workflow uploads to `/public_html/` by default. If your FTP account uses a different path (e.g. you’re in a subfolder), edit `.github/workflows/deploy-hosterpk.yml` and set `server-dir` to that path (e.g. `/` or `/public_html/mysite/`).

### 3. Push the workflow

The workflow file is already in the repo:

- `.github/workflows/deploy-hosterpk.yml`

Commit and push to `main` (if you haven’t already). The first deployment will run automatically.

## Deployments

- **Automatic:** Every push to `main` triggers a deploy.
- **Manual:** In GitHub go to **Actions → Deploy to hoster.pk → Run workflow**.

## First-time server setup (hoster.pk)

Do this once on the server (same as a manual cPanel deploy):

1. **Database**
   - cPanel → **MySQL Databases**: create database and user, add user to database.
   - **phpMyAdmin**: import `api/database/schema.sql` into that database.

2. **Environment**
   - In the deployed files, go to `api/` on the server.
   - Copy `api/.env.example` to `api/.env`.
   - Edit `api/.env`: set `DB_*`, `APP_URL`, `CORS_ALLOWED_ORIGINS`, and `JWT_SECRET`.

3. **PHP**
   - cPanel → **Select PHP Version**: use PHP 8.1+ and enable PDO, PDO_MySQL, OpenSSL, JSON, mbstring.

After that, future deploys only update files; they don’t overwrite `api/.env` if you don’t put it in the repo (and you shouldn’t).

## If you use a different branch

Edit `.github/workflows/deploy-hosterpk.yml` and change:

```yaml
on:
  push:
    branches:
      - main   # change to your branch, e.g. production
```

## Troubleshooting

- **FTP connection failed:** Check `FTP_SERVER` (no `ftp://`), `FTP_USERNAME`, and `FTP_PASSWORD`. Try the same credentials in an FTP client (e.g. FileZilla).
- **Files in wrong place:** Set `FTP_SERVER_DIR` to the exact remote path (e.g. `/public_html/` or `/`).
- **Site not updating:** Confirm the workflow run succeeded in the **Actions** tab and that the FTP path is the same as your site root (e.g. `public_html`).
- **API or DB errors:** Check `api/.env` and database setup on hoster.pk; see `cpanel-deploy/DEPLOY_INSTRUCTIONS.txt` for details.

## Optional: cPanel Git (if hoster.pk offers it)

If hoster.pk has **Git Version Control** in cPanel:

1. In cPanel open **Files → Git Version Control**.
2. Create a repository and clone from your GitHub repo (you may need to add a deploy key in GitHub).
3. Set the deployment path to `public_html`.
4. Note: the server must have **Node.js** to run `npm run build`. Many shared hosts don’t, so **FTP deploy from GitHub Actions** (above) is usually easier and is the recommended method.

---

Summary: add `FTP_SERVER`, `FTP_USERNAME`, and `FTP_PASSWORD` (and optionally `FTP_SERVER_DIR`) in GitHub Actions secrets, then push to `main` to auto-deploy to hoster.pk.
