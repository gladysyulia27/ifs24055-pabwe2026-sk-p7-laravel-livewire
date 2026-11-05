#!/usr/bin/env sh
set -e

cd /var/www/html

# 1. Ensure writable runtime directories in /tmp and storage
mkdir -p /tmp/storage/app/private/livewire-tmp \
         /tmp/storage/framework/cache/data \
         /tmp/storage/framework/sessions \
         /tmp/storage/framework/views \
         /tmp/storage/logs

# 2. Ensure deterministic APP_KEY if none was set in Vercel Environment Variables
if [ -z "${APP_KEY}" ]; then
    SEED="${VERCEL_PROJECT_ID:-pabwe-p7-laravel-livewire-default-key}"
    B64_KEY=$(printf '%s' "${SEED}" | sha256sum | cut -c1-32 | base64)
    export APP_KEY="base64:${B64_KEY}"
fi

export APP_ENV="${APP_ENV:-production}"
export APP_DEBUG="${APP_DEBUG:-false}"
export LOG_CHANNEL="${LOG_CHANNEL:-stderr}"
export FORCE_HTTPS="${FORCE_HTTPS:-true}"

# 3. Database auto-fallback:
#    If DB_HOST is unset or points to localhost/127.0.0.1 (unreachable inside stateless Vercel Function),
#    fall back to pre-migrated & pre-seeded SQLite in /tmp/database.sqlite.
if [ -z "${DB_HOST}" ] || [ "${DB_HOST}" = "127.0.0.1" ] || [ "${DB_HOST}" = "localhost" ]; then
    export DB_CONNECTION="sqlite"
    export DB_DATABASE="/tmp/database.sqlite"
    if [ ! -f "/tmp/database.sqlite" ]; then
        cp /var/www/html/database/preseeded.sqlite /tmp/database.sqlite
        chmod 666 /tmp/database.sqlite
    fi
    # Use encrypted cookie sessions & array/file cache when running stateless without external DB
    export SESSION_DRIVER="${SESSION_DRIVER:-cookie}"
    export CACHE_STORE="${CACHE_STORE:-file}"
else
    export SESSION_DRIVER="${SESSION_DRIVER:-database}"
    export CACHE_STORE="${CACHE_STORE:-database}"
    if [ "${AUTORUN_MIGRATIONS:-true}" = "true" ]; then
        php artisan migrate --force --no-interaction || true
    fi
fi

# 4. Configure Apache listening port (Vercel Container Functions default to port 80 or $PORT)
LISTEN_PORT="${PORT:-80}"
if [ "${LISTEN_PORT}" != "80" ]; then
    sed -i "s/Listen 80/Listen ${LISTEN_PORT}/g" /etc/apache2/ports.conf
    sed -i "s/:80>/:${LISTEN_PORT}>/g" /etc/apache2/sites-available/000-default.conf
fi

# 5. Cache configuration and routes for fast response times
php artisan config:cache --no-interaction || true
php artisan route:cache --no-interaction || true
php artisan view:cache --no-interaction || true

exec apache2-foreground

