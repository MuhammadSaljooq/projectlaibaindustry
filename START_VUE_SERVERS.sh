#!/bin/bash
# Start PHP backend + Vue frontend (Vue expects API at http://localhost:8000/api)
ROOT="$(cd "$(dirname "$0")" && pwd)"
echo "Starting PHP backend..."
cd "$ROOT/backend-php"
php -S localhost:8000 -t . > backend.log 2>&1 &
BACKEND_PID=$!
echo "Backend PID: $BACKEND_PID → http://localhost:8000"
echo "Starting Vue frontend..."
cd "$ROOT/frontend-vue"
npm run dev > frontend.log 2>&1 &
FRONTEND_PID=$!
echo "Frontend PID: $FRONTEND_PID → http://localhost:5173"
echo "=========================================="
echo "✓ Servers starting"
echo "  Backend API:  http://localhost:8000/api"
echo "  Frontend App: http://localhost:5173"
echo "Ensure backend-php/.env exists and MySQL schema is imported."
echo "To stop: kill $BACKEND_PID $FRONTEND_PID"
echo "=========================================="
echo "$BACKEND_PID $FRONTEND_PID" > "$ROOT/.server_pids"
