#!/bin/sh
set -e

# ─── Port Configuration ───────────────────────────────────────────────────────
# Render injects $PORT; update Nginx to listen on it
LISTEN_PORT="${PORT:-10000}"
sed -i "s/listen 80;/listen ${LISTEN_PORT};/g" /etc/nginx/nginx.conf
sed -i "s/listen \[::\]:80;/listen [::]:${LISTEN_PORT};/g" /etc/nginx/nginx.conf

# ─── Ensure Valid Laravel APP_KEY ─────────────────────────────────────────────
# Laravel requires APP_KEY to be either 32 raw bytes or 'base64:' + 44-char base64 string.
# Render's `generateValue: true` generates a 44-char base64 string WITHOUT 'base64:' prefix.
# If missing, wrong length, or lacks the prefix, fix/generate it before PHP-FPM starts.
export APP_KEY=$(php -r '
    $key = getenv("APP_KEY") ?: "";
    if (empty($key)) {
        echo "base64:" . base64_encode(random_bytes(32));
        exit(0);
    }
    if (str_starts_with($key, "base64:")) {
        $decoded = base64_decode(substr($key, 7), true);
        if ($decoded !== false && strlen($decoded) === 32) {
            echo $key;
            exit(0);
        }
    }
    if (strlen($key) === 44 && ($decoded = base64_decode($key, true)) !== false && strlen($decoded) === 32) {
        echo "base64:" . $key;
        exit(0);
    }
    if (strlen($key) === 64 && ctype_xdigit($key)) {
        echo "base64:" . base64_encode(hex2bin($key));
        exit(0);
    }
    if (strlen($key) === 32) {
        echo $key;
        exit(0);
    }
    // Fallback: generate a fresh valid 32-byte key
    echo "base64:" . base64_encode(random_bytes(32));
')

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
    echo "Running database migrations (attempt $i)..."
    if php artisan migrate --force; then
        echo "Migrations completed successfully."
        break
    fi
    echo "Migration attempt $i failed, retrying in 5s..."
    sleep 5
done

# Seed initial data if requested via DB_SEED=true
if [ "${DB_SEED:-false}" = "true" ]; then
    echo "Running database seeder..."
    php artisan db:seed --force || true
fi

# Cache config/routes/views for production performance
echo "Caching Laravel configuration, routes, and views..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# ─── Start Nginx in foreground ───────────────────────────────────────────────
exec nginx -g "daemon off;"
