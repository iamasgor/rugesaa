#!/usr/bin/env bash
#
# Deploys the latest code on the server. Run from the project root on the server:
#   ./deploy.sh            # deploys origin/main
#   BRANCH=staging ./deploy.sh
#
# Requirements on the server: git, PHP 8.5, Composer 2, Node 22 + npm, and a
# configured .env (APP_ENV=production, APP_DEBUG=false, APP_KEY set).

set -euo pipefail

BRANCH="${BRANCH:-main}"
PHP="${PHP:-php}"
COMPOSER="${COMPOSER:-composer}"

cd "$(dirname "$0")"

log() { printf '\n\033[1;34m==> %s\033[0m\n' "$1"; }

if [[ ! -f .env ]]; then
    echo "Missing .env — copy .env.example to .env and configure it before deploying." >&2
    exit 1
fi

log "Entering maintenance mode"
$PHP artisan down --retry=15 || true
trap '$PHP artisan up; echo "Deploy failed — site brought back up on the previous build." >&2' ERR

log "Pulling $BRANCH"
git fetch origin "$BRANCH"
git reset --hard "origin/$BRANCH"

log "Installing PHP dependencies"
$COMPOSER install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress

log "Building frontend assets"
npm ci --no-audit --no-fund
npm run build

if grep -qE '^DB_CONNECTION=sqlite' .env; then
    touch database/database.sqlite
fi

log "Running migrations"
$PHP artisan migrate --force

log "Linking storage"
$PHP artisan storage:link 2>/dev/null || true

log "Caching config, routes, views, events and Filament"
$PHP artisan optimize:clear
$PHP artisan optimize
$PHP artisan filament:optimize

log "Restarting queue workers"
$PHP artisan queue:restart

trap - ERR
log "Leaving maintenance mode"
$PHP artisan up

log "Deployed $(git rev-parse --short HEAD) from $BRANCH"
