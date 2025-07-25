#!/bin/bash

echo "Setting up simple build watcher..."

# Install inotify-tools if not available
if ! command -v inotifywait &> /dev/null; then
    echo "Installing inotify-tools..."
    apt-get update && apt-get install -y inotify-tools
fi

# Function to build assets
build_assets() {
    echo "Building assets..."
    npm run build
    echo "Assets built successfully!"
}

# Initial build
build_assets

echo "Watching for changes in resources/ directory..."
echo "Press Ctrl+C to stop..."

# Watch for changes and rebuild
while inotifywait -r -e modify,create,delete,move resources/ --exclude '\.git|node_modules|\.DS_Store'; do
    echo "Changes detected, rebuilding..."
    build_assets
    sleep 1
done