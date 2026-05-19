#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

# -----------------------------------------------------------------------------
# Wait for dependencies (Compose healthchecks should pass first; this is backup)
# -----------------------------------------------------------------------------
if [[ -x /usr/local/bin/wait-for-services.sh ]]; then
  /usr/local/bin/wait-for-services.sh
fi

# -----------------------------------------------------------------------------
# Development: install dependencies when vendor is missing (bind mount)
# -----------------------------------------------------------------------------
if [[ "${APP_ENV:-local}" == "local" ]] && [[ ! -f vendor/autoload.php ]]; then
  echo "[entrypoint] Installing Composer dependencies..."
  composer install --no-interaction --prefer-dist
fi

# -----------------------------------------------------------------------------
# Laravel bootstrap
# -----------------------------------------------------------------------------
if [[ -f artisan ]]; then
  if [[ -z "${APP_KEY:-}" ]] || [[ "${APP_KEY}" == "" ]]; then
    php artisan key:generate --force --no-interaction || true
  fi

  php artisan config:clear --no-interaction || true
  php artisan route:clear --no-interaction || true
  php artisan view:clear --no-interaction || true

  if [[ "${RUN_MIGRATIONS:-true}" == "true" ]]; then
    echo "[entrypoint] Running migrations..."
    php artisan migrate --force --no-interaction
  fi

  if [[ -f database/migrations/pgvector ]] && [[ "${RUN_PGVECTOR_MIGRATIONS:-false}" == "true" ]]; then
    echo "[entrypoint] Running pgvector migrations..."
    php artisan migrate --force --no-interaction --path=database/migrations/pgvector
  fi
fi

# Fix permissions for storage (dev bind mounts)
if [[ "${APP_ENV:-local}" == "local" ]]; then
  mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
  chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || chmod -R 775 storage bootstrap/cache
fi

echo "[entrypoint] Starting: $*"
exec "$@"
