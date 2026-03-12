#!/bin/sh
set -eu

max_attempts="${MIGRATION_MAX_ATTEMPTS:-30}"
sleep_seconds="${MIGRATION_SLEEP_SECONDS:-3}"
attempt=1

echo "Preparing Laravel application..."

mkdir -p /var/www/storage/framework/cache /var/www/storage/framework/sessions /var/www/storage/framework/views /var/www/bootstrap/cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

while [ "$attempt" -le "$max_attempts" ]; do
    echo "Running migrations (attempt $attempt/$max_attempts)..."

    if php artisan migrate --force; then
        echo "Migrations completed successfully."
        exec php-fpm
    fi

    echo "Migration failed, waiting ${sleep_seconds}s before retry..."
    attempt=$((attempt + 1))
    sleep "$sleep_seconds"
done

echo "Migrations failed after $max_attempts attempts."
exit 1
