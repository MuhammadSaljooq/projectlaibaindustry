# Deploy to Render.com

Deploy the app (Node.js backend + Vue frontend + PostgreSQL) on Render.

1. Connect your repo to Render; use the **render.yaml** blueprint.
2. Render will create: Web Service (backend), Static Site (frontend), PostgreSQL database.
3. Set `VITE_API_URL` on the frontend to your backend URL + `/api`.
4. See **render.yaml** in the project root for build/start commands.
