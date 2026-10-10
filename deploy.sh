#!/usr/bin/env bash
set -Eeuo pipefail

APP_DIR="/var/www/pos-saas"
cd "$APP_DIR"

on_error() {
  local exit_code=$?
  echo "DEPLOY FAILED at line $1 (exit $exit_code). Check the error before retrying." >&2
  exit "$exit_code"
}
trap 'on_error "$LINENO"' ERR

echo "=== DEPLOY START ==="
echo "Pull latest code..."
git pull --ff-only origin main

echo "Install composer dependencies..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo "Prepare Laravel runtime directories..."
mkdir -p storage/logs storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache
# fdev and the web server must both be able to write to storage and bootstrap/cache.
# Configure ownership/ACLs during initial server provisioning (do not chown runtime files on each deploy).
if [[ ! -w storage/logs || ! -w bootstrap/cache ]]; then
  echo 'Laravel runtime paths are not writable. Fix fdev/www-data permissions before deploying.' >&2
  exit 1
fi

echo "Build assets..."
npm ci
npm run build

echo "Clear cache..."
php artisan optimize:clear

echo "Run migrations..."
php artisan migrate --force

echo "Optimize Laravel..."
php artisan optimize

echo "Restart queue..."
php artisan queue:restart

echo "=== DEPLOY DONE ==="
