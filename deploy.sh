#!/usr/bin/env bash
set -Eeuo pipefail

# Provision shared/.env + shared/storage and point the web root at current/public.
SOURCE_DIR="${DEPLOY_SOURCE:-$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)}"
DEPLOY_ROOT="${DEPLOY_ROOT:-/var/www/pos-saas}"
HEALTH_URL="${DEPLOY_HEALTH_URL:-http://127.0.0.1/up}"
REF="${DEPLOY_REF:-origin/main}"
[[ "$DEPLOY_ROOT" = /* && "$DEPLOY_ROOT" != / ]] || { echo 'DEPLOY_ROOT must be a non-root absolute directory.' >&2; exit 1; }
[[ -f "$DEPLOY_ROOT/shared/.env" && -d "$DEPLOY_ROOT/shared/storage" ]] || { echo 'Provision shared/.env and shared/storage before deploying.' >&2; exit 1; }
mkdir -p "$DEPLOY_ROOT/releases"
exec 9>"$DEPLOY_ROOT/.deploy.lock"
flock -n 9 || { echo 'Another deployment is running.' >&2; exit 1; }
previous="$(readlink "$DEPLOY_ROOT/current" || true)"
release="$(mktemp -d "$DEPLOY_ROOT/releases/release-$(date -u +%Y%m%dT%H%M%SZ)-XXXXXX")"
switched=0
switch_release() {
    local next="$DEPLOY_ROOT/.current-next-$$"
    ln -s "$1" "$next"
    mv -Tf "$next" "$DEPLOY_ROOT/current"
}
reload_fpm() {
    if [[ -n "${PHP_FPM_SERVICE:-}" ]]; then
        [[ "$PHP_FPM_SERVICE" =~ ^[a-zA-Z0-9@._-]+$ ]] || return 1
        systemctl reload "$PHP_FPM_SERVICE"
    fi
}
on_error() {
    local code=$?
    trap - ERR
    if [[ "$switched" = 1 && -n "$previous" ]]; then
        switch_release "$previous"
        reload_fpm || true
        (cd "$previous" && php artisan queue:restart) || true
        echo 'Application symlink restored. Schema was not rolled back; inspect migration compatibility.' >&2
    fi
    echo "Deploy failed at line $1. Release retained for diagnosis: $release" >&2
    exit "$code"
}
trap 'on_error "$LINENO"' ERR

git -C "$SOURCE_DIR" fetch origin main
git -C "$SOURCE_DIR" archive "$REF" | tar -x -C "$release"
# Only the newly-created release's generated storage stub is replaced.
rm -rf "${release:?}/storage"
ln -s "$DEPLOY_ROOT/shared/storage" "$release/storage"
ln -s "$DEPLOY_ROOT/shared/.env" "$release/.env"
mkdir -p "$release/storage/logs" "$release/storage/framework/cache/data" "$release/storage/framework/sessions" "$release/storage/framework/views" "$release/bootstrap/cache"
cd "$release"
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci --ignore-scripts
npm run build
composer audit --locked --no-dev
npm audit
php artisan growpos:backup
php artisan migrate --force
php artisan optimize
php artisan growpos:launch-check
ln -s "$release/storage/app/public" "$release/public/storage"
switch_release "$release"
switched=1
reload_fpm
curl --fail --silent --show-error --max-time 20 "$HEALTH_URL" >/dev/null
php artisan queue:restart
echo "Release healthy: $release"
