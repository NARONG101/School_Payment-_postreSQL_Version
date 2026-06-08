#!/bin/sh
# Wait for PHP-FPM unix socket to be ready before starting nginx
echo "==> Waiting for PHP-FPM socket..."
until [ -S /var/run/php-fpm.sock ]; do
    sleep 0.5
done
echo "==> PHP-FPM socket ready. Starting nginx..."
exec nginx -g "daemon off;"
