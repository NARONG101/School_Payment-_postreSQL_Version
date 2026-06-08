#!/bin/sh
# Wait for PHP-FPM to be ready before starting nginx
echo "==> Waiting for PHP-FPM on port 9000..."
until nc -z 127.0.0.1 9000; do
    sleep 0.5
done
echo "==> PHP-FPM ready. Starting nginx..."
exec nginx -g "daemon off;"
