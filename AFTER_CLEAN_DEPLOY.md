# After a clean deploy (if API stops working)

The deploy wipes the server folder and uploads fresh files. Your **api/.env** (database password, etc.) is not in the repo, so it gets removed.

**Do this once after the first clean deploy:**

1. In **cPanel → File Manager**, go to **public_html/api/**
2. If **.env** is missing: copy **.env.example** and rename the copy to **.env**
3. Edit **.env** and set:
   - `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (your MySQL details)
   - `APP_URL=https://laibaindustrysialkot.com`
   - `CORS_ALLOWED_ORIGINS=https://laibaindustrysialkot.com`
   - `JWT_SECRET` (any long random string)
4. Save. The API will work again.

**Tip:** Keep a backup of **api/.env** on your computer so you can paste it back if needed.
