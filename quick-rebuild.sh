#!/bin/bash

echo "🚀 Quick rebuild..."

# Quick rebuild without npm install
docker exec tez-capital bash -c "
    cd /var/www/html && 
    npm run build &&
    rm -f public/hot &&
    echo '✅ Quick rebuild done!'
"

echo "🎉 Changes should be visible now!"