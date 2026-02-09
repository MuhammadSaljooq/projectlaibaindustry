# Deploy to cPanel (PHP + MySQL)

1. Run `./DEPLOY_TO_CPANEL.sh` to create the **cpanel-deploy/** package.
2. In cPanel: create MySQL database and user; in phpMyAdmin import **api/database/schema.sql**.
3. Upload all contents of **cpanel-deploy/** to **public_html/**.
4. In **public_html/api/** rename `.env.example` to `.env` and set DB_*, APP_URL, CORS_ALLOWED_ORIGINS.
5. Use PHP 8.1+ and enable PDO, PDO_MySQL. Test: `https://yourdomain.com/api/health`.
