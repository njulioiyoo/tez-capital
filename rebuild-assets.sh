#!/bin/bash

echo "🔥 Rebuilding frontend assets..."

# Enter container and rebuild assets
docker exec tez-capital bash -c "
    cd /var/www/html && 
    echo '📦 Installing/updating npm dependencies...' &&
    npm install &&
    echo '🏗️  Building assets...' &&
    npm run build &&
    echo '🧹 Cleaning up development files...' &&
    rm -f public/hot &&
    echo '✅ Assets rebuilt successfully!'
"

echo "🎉 Done! Your changes should now be visible."