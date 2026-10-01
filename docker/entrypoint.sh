#!/bin/sh
set -e

# ─── Port Configuration ───────────────────────────────────────────────────────
# Render injects $PORT; update Nginx to listen on it
LISTEN_PORT="${PORT:-10000}"
sed -i "s/listen 80;/listen ${LISTEN_PORT};/g" /etc/nginx/nginx.conf
sed -i "s/listen \[::\]:80;/listen [::]:${LISTEN_PORT};/g" /etc/nginx/nginx.conf

# ─── Storage Directories & Permissions ───────────────────────────────────────
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/app/public \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# ─── Start PHP-FPM first, then wait for it to be ready ───────────────────────
php-fpm -D

# Wait for PHP-FPM to bind on port 9000 (max 10 seconds)
WAIT=0
until nc -z 127.0.0.1 9000 2>/dev/null; do
    WAIT=$((WAIT + 1))
    if [ "$WAIT" -ge 10 ]; then
        echo "ERROR: PHP-FPM did not start in time."
        exit 1
    fi
    sleep 1
done

# ─── Laravel Bootstrap ───────────────────────────────────────────────────────
php artisan package:discover --ansi 2>/dev/null || true
php artisan storage:link --force 2>/dev/null || true

# Run migrations (retry up to 3 times to handle DB cold-start on Render free tier)
for i in 1 2 3; do
    php artisan migrate --force 2>/dev/null && break || {
        echo "Migration attempt $i failed, retrying in 5s..."
        sleep 5
    }
done

# Cache config/routes/views for production performance
php artisan config:cache  2>/dev/null || true
php artisan route:cache   2>/dev/null || true
php artisan view:cache    2>/dev/null || true

# ─── Start Nginx in foreground ───────────────────────────────────────────────
exec nginx -g "daemon off;"
