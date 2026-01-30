#!/bin/bash

# Script to start servers for public IP access
# Usage: ./START_PUBLIC_SERVERS.sh

echo "🚀 Starting Servers for Public IP Access"
echo "=========================================="
echo ""

# Get current public IP
PUBLIC_IP=$(curl -s ifconfig.me)
LOCAL_IP=$(ifconfig | grep "inet " | grep -v 127.0.0.1 | awk '{print $2}' | head -1)

echo "📡 Network Information:"
echo "   Public IP: $PUBLIC_IP"
echo "   Local IP: $LOCAL_IP"
echo ""

# Check if .env file exists and update it
if [ -f "frontend/.env" ]; then
  echo "✅ Frontend .env file exists"
  echo "   Current VITE_API_URL: $(grep VITE_API_URL frontend/.env || echo 'Not set')"
else
  echo "📝 Creating frontend/.env file..."
  echo "VITE_API_URL=http://$PUBLIC_IP:5000/api" > frontend/.env
  echo "✅ Created frontend/.env with public IP"
fi

echo ""
echo "⚠️  IMPORTANT: Before accessing publicly:"
echo "   1. Set up port forwarding on your router:"
echo "      - Forward port 5000 → $LOCAL_IP:5000 (Backend)"
echo "      - Forward port 5173 → $LOCAL_IP:5173 (Frontend)"
echo ""
echo "   2. Or use ngrok (easier, no router config):"
echo "      - Install: brew install ngrok"
echo "      - Run: ngrok http 5000 (for backend)"
echo "      - Run: ngrok http 5173 (for frontend)"
echo ""
echo "🔒 SECURITY WARNING:"
echo "   - Add authentication before exposing publicly"
echo "   - Use HTTPS for production"
echo "   - Configure firewall rules"
echo ""
echo "📋 Starting servers..."
echo ""

# Start backend server
echo "Starting backend server..."
cd backend
npm run dev &
BACKEND_PID=$!
echo "Backend PID: $BACKEND_PID"

# Wait a bit for backend to start
sleep 3

# Start frontend server
echo "Starting frontend server..."
cd ../frontend
npm run dev &
FRONTEND_PID=$!
echo "Frontend PID: $FRONTEND_PID"

echo ""
echo "✅ Servers started!"
echo ""
echo "🌐 Access your application:"
echo "   Local: http://localhost:5173"
echo "   Network: http://$LOCAL_IP:5173"
echo "   Public: http://$PUBLIC_IP:5173 (after port forwarding)"
echo ""
echo "Press Ctrl+C to stop all servers"

# Wait for user interrupt
trap "kill $BACKEND_PID $FRONTEND_PID 2>/dev/null; exit" INT TERM
wait
