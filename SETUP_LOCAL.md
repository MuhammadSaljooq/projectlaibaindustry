# Local setup (Vue + PHP backend)

## 1. Backend env
```bash
cp backend-php/.env.example backend-php/.env
```
Edit **backend-php/.env** and set your MySQL credentials (DB_USERNAME, DB_PASSWORD, etc.).

## 2. Database
Create the database and import the schema (uses credentials from **backend-php/.env**):
```bash
./scripts/setup-mysql-db.sh
```

## 3. Start servers
From the project root:
```bash
./START_VUE_SERVERS.sh
```
- Backend API: http://localhost:8000/api
- Frontend: http://localhost:5173
