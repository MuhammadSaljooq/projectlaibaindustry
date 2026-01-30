#!/bin/bash

# Start Backend (PHP)
echo "Starting PHP backend server..."
cd "$(dirname "$0")/backend-php"
php -S localhost:8000 -t . > backend.log 2>&1 &
BACKEND_PID=$!
echo "Backend started with PID: $BACKEND_PID"
echo "Backend running on: http://localhost:8000"
echo "Backend logs: backend-php/backend.log"
echo ""

# Start Frontend (React)
echo "Starting React frontend server..."
cd "$(dirname "$0")/frontend"
npm run dev > frontend.log 2>&1 &
FRONTEND_PID=$!
echo "Frontend started with PID: $FRONTEND_PID"
echo "Frontend running on: http://localhost:5173"
echo "Frontend logs: frontend/frontend.log"
echo ""

echo "=========================================="
echo "✓ Servers are starting..."
echo ""
echo "Backend API:  http://localhost:8000/api"
echo "Frontend App: http://localhost:5173"
echo ""
echo "To stop servers, run:"
echo "  kill $BACKEND_PID $FRONTEND_PID"
echo "=========================================="

# Save PIDs to file for easy stopping
echo "$BACKEND_PID $FRONTEND_PID" > .server_pids
