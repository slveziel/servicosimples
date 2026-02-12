#!/bin/bash

# Laravel App Engine Entrypoint
# Skip migrations during startup - run them from a separate cron job or manually
echo "Starting Laravel app..."

# Keep entrypoint alive so nginx/PHP-FPM can start
while true; do
    sleep 60
done
