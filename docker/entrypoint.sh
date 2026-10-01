#!/bin/sh
set -e

# Update port if PORT env variable is passed by Cloud provider (Render/Railway)
if [ -n "$PORT" ]; then
    sed -i "s/listen 80;/listen $PORT;/g" /etc/nginx/conf.d/default.conf
    sed -i "s/listen \[::\]:80;/listen \[::\]:$PORT;/g" /etc/nginx/conf.d/default.conf
fi

# Ensure storage permissions
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Discover packages at runtime
php artisan package:discover --ansi || true

# Create storage symlink
php artisan storage:link || true

# Run database migrations if DB is accessible
php artisan migrate --force || true

# Optimize cache
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
exec nginx -g "daemon off;"
