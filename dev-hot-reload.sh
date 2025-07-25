#!/bin/bash

echo "🔥 Starting Hot Reload Development Server..."

# Check if already running
if lsof -Pi :5173 -sTCP:LISTEN -t >/dev/null ; then
    echo "⚠️  Vite dev server already running on port 5173"
    echo "Stop it first with: pkill -f 'npm run dev'"
    exit 1
fi

cd www

echo "🚀 Starting Vite development server..."
echo "📱 Frontend: http://localhost:5173"
echo "🌐 Backend: http://localhost:8983"
echo ""
echo "💡 Tips:"
echo "  - Edit files in www/resources/"
echo "  - Changes will auto-reload in browser"
echo "  - Press Ctrl+C to stop"
echo ""

npm run dev