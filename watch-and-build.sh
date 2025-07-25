#!/bin/bash

echo "👀 Starting file watcher for automatic rebuilds..."
echo "🔍 Watching: www/resources/ directory"
echo "⏹️  Press Ctrl+C to stop"

# Install fswatch if not available (macOS)
if ! command -v fswatch &> /dev/null; then
    echo "Installing fswatch..."
    if command -v brew &> /dev/null; then
        brew install fswatch
    else
        echo "Please install fswatch: brew install fswatch"
        exit 1
    fi
fi

# Function to rebuild assets
rebuild() {
    echo ""
    echo "🔄 Changes detected! Quick rebuilding assets..."
    ./quick-rebuild.sh
    echo "⏳ Waiting for more changes..."
}

# Initial build
./rebuild-assets.sh

# Watch for changes
fswatch -o www/resources/ | while read num ; do
    rebuild
done