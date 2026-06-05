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

# ── Ensure SQLite database file exists ───────────────────────
DB_PATH="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
DB_DIR=$(dirname "$DB_PATH")

if [ ! -d "$DB_DIR" ]; then
    echo "==> Creating database directory: $DB_DIR"
    mkdir -p "$DB_DIR"
    chown appuser:appgroup "$DB_DIR"
fi

if [ ! -f "$DB_PATH" ]; then
    echo "==> Creating SQLite database file: $DB_PATH"
    touch "$DB_PATH"
    chown appuser:appgroup "$DB_PATH"
    chmod 664 "$DB_PATH"
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
