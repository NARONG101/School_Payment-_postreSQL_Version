#!/bin/sh
set -e

echo "==> Starting Student Payment System..."

# ── Validate required env vars ───────────────────────────────
if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY is not set."
    echo "       Generate one with: php artisan key:generate --show"
    echo "       Then set it as APP_KEY in your environment."
    exit 1
fi

# ── Wait for PostgreSQL to be ready ─────────────────────────
if [ "${DB_CONNECTION:-pgsql}" = "pgsql" ]; then
    echo "==> Waiting for PostgreSQL at ${DB_HOST}:${DB_PORT:-5432}..."
    for i in $(seq 1 30); do
        php -r "new PDO('pgsql:host=${DB_HOST};port=${DB_PORT:-5432};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null && break
        echo "    attempt $i/30 — retrying in 2s..."
        sleep 2
    done
fi

# ── Storage symlink ──────────────────────────────────────────
if [ ! -L /var/www/html/public/storage ]; then
    echo "==> Creating storage symlink..."
    php artisan storage:link --force
fi

# ── Ensure storage subdirectories exist ──────────────────────
mkdir -p \
    /var/www/html/storage/app/public/students/photos \
    /var/www/html/storage/app/public/payments/photos \
    /var/www/html/storage/framework/cache/data \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs

chown -R appuser:appgroup /var/www/html/storage /var/www/html/bootstrap/cache

# ── Run migrations ───────────────────────────────────────────
echo "==> Running migrations..."
php artisan migrate --force --no-interaction

# ── Seed database (idempotent — uses firstOrCreate) ──────────
echo "==> Seeding database..."
php artisan db:seed --force --no-interaction

# ── Cache config/routes/views for production ─────────────────
echo "==> Caching for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "==> All done. Launching supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
