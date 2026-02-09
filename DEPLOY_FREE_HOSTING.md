# Deploy to Free Hosting (PHP + MySQL)

1. Run `./DEPLOY_FREE_HOSTING.sh` from the project root and enter your site URL.
2. Upload the contents of the generated folder to your hosting (htdocs/public_html).
3. In cPanel: create MySQL database and user; import **api/database/schema.sql**.
4. In **api/** copy `.env.example` to `.env` and set DB_* and APP_URL.
5. Test: `https://yoursite.com/api/health` and open your site URL.
