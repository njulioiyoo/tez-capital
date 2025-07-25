#!/bin/bash

echo "🚀 Starting Docker services..."

# Start Apache in background
/usr/sbin/apache2ctl -D FOREGROUND &
APACHE_PID=$!

# Wait for Apache to start
sleep 2

# Start Vite dev server
cd /var/www/html

echo "📦 Installing npm dependencies..."
npm install

echo "🔥 Starting Vite development server..."
echo "  Frontend dev server: http://localhost:5173"
echo "  Backend: http://localhost:8983"

# Start Vite dev server with proper configuration
VITE_HOST=0.0.0.0 VITE_PORT=5173 npm run dev &
VITE_PID=$!

# Function to cleanup on exit
cleanup() {
    echo "🛑 Stopping services..."
    kill $APACHE_PID $VITE_PID 2>/dev/null
    exit 0
}

# Set trap to cleanup on script exit
trap cleanup SIGINT SIGTERM

echo "✅ Services started!"
echo "🔄 Watching for changes..."

# Keep script running
wait