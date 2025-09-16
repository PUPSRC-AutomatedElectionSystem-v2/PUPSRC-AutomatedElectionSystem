#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

# If vendor is missing, install deps (prefer building image with deps instead)
if [ ! -d "vendor" ]; then
    composer install --no-interaction --prefer-dist --no-progress
fi

# Optionally run composer update for a single package once on first container start.
# Control with COMPOSER_UPDATE_ON_START (set to "true" to enable).
MARKER_FILE="/var/www/html/.composer_update_done"
if [ "${COMPOSER_UPDATE_ON_START:-false}" = "true" ] && [ ! -f "${MARKER_FILE}" ]; then
    echo "Running composer update modules/organization-admin (first start)..."
    composer update modules/organization-admin --no-interaction --prefer-dist --no-progress --with-dependencies || true
    # create marker so this runs only once
    touch "${MARKER_FILE}"
fi

# Clear cached config so new connections are picked up
php artisan config:clear || true

# Optionally run migrations on container start
if [ "${RUN_MIGRATIONS_ON_START:-false}" = "true" ]; then
    echo "Running migrate:fresh..."
    php artisan migrate:fresh --seed || true
fi

# Ensure the default Nginx site from the package is disabled to prevent duplicate default_server
if [ -e "/etc/nginx/sites-enabled/default" ]; then
    rm -f /etc/nginx/sites-enabled/default || true
fi

# Normalize Supervisor configuration paths so supervisorctl works without -c
# Remove Sail's default conf that may shadow our settings and lacks [supervisorctl]
if [ -f "/etc/supervisor/conf.d/supervisord.conf" ]; then
    rm -f "/etc/supervisor/conf.d/supervisord.conf" || true
fi
# Symlink the expected default path used by supervisorctl
ln -sf /etc/supervisor/supervisord.conf /etc/supervisord.conf

# If enabled, run supervisord in foreground (so it's PID 1 and manages processes)
if [ "${USE_SUPERVISOR:-false}" = "true" ]; then
    echo "Starting supervisord..."
    exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf
fi

# Exec container CMD
exec "$@"