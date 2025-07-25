#!/bin/bash

# Create log directories
mkdir -p /var/log/apache2 /var/log/vite-dev

# Start supervisor in background
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf &

# Wait for supervisor to start
sleep 2

# Keep container running
tail -f /var/log/supervisor/supervisord.log