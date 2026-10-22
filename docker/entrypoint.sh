#!/bin/sh
set -eu

cd /var/www/html

mkdir -p \
    storage/app/public \
    storage/app/private \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache

if [ ! -L public/storage ]; then
    ln -sfn /var/www/html/storage/app/public /var/www/html/public/storage
fi

# Opt-in only. The Compose default is false, and only the app service may enable it.
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    setpriv --reuid=www-data --regid=www-data --init-groups \
        php artisan migrate --force --no-interaction
fi

exec setpriv --reuid=www-data --regid=www-data --init-groups "$@"
