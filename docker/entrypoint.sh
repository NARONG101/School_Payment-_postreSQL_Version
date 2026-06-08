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
if [ "${DB_CONNECTION:-pgsql}" = "pgsql" ] && [ -n "$DB_HOST" ] && [ "$DB_HOST" != "127.0.0.1" ]; then
    echo "==> Waiting for PostgreSQL at ${DB_HOST}:${DB_PORT:-5432}..."
    RETRIES=0
    until php -r "new PDO('pgsql:host=${DB_HOST};port=${DB_PORT:-5432};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
        RETRIES=$((RETRIES + 1))
        if [ "$RETRIES" -ge 30 ]; then
            echo "ERROR: PostgreSQL not reachable after 30 attempts. Check DB_HOST env var in Render dashboard."
            exit 1
        fi
        echo "    attempt $RETRIES/30 — retrying in 2s..."
        sleep 2
    done
    echo "==> PostgreSQL is ready."
elif [ "${DB_CONNECTION:-pgsql}" = "pgsql" ] && [ -z "$DB_HOST" ]; then
    echo "ERROR: DB_HOST is not set. Add PostgreSQL environment variables in Render dashboard."
    exit 1
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
