#!/bin/bash

echo "Starting development environment with auto-reload..."

# Install dependencies if node_modules doesn't exist
if [ ! -d "node_modules" ]; then
    echo "Installing npm dependencies..."
    npm install
fi

# Start Vite development server in background
echo "Starting Vite development server..."
npm run dev &
VITE_PID=$!

echo "Vite started with PID: $VITE_PID"
echo "Frontend development server running on http://localhost:5173"
echo "Press Ctrl+C to stop..."

# Function to cleanup on exit
cleanup() {
    echo "Stopping Vite development server..."
    kill $VITE_PID 2>/dev/null
    exit 0
}

# Set trap to cleanup on script exit
trap cleanup SIGINT SIGTERM

# Keep script running and show logs
wait $VITE_PID