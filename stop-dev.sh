#!/bin/bash

echo "🛑 Stopping development servers..."

# Stop Vite dev server
pkill -f "npm run dev" 2>/dev/null
pkill -f "vite" 2>/dev/null

# Stop file watcher
pkill -f "watch-and-build.sh" 2>/dev/null
pkill -f "fswatch" 2>/dev/null

# Clean up hot file
rm -f www/public/hot

echo "✅ All development servers stopped!"
echo ""
echo "💡 To restart:"
echo "  Hot reload: ./dev-hot-reload.sh"
echo "  File watcher: ./watch-and-build.sh"