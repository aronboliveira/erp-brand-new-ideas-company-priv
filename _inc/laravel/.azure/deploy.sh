#!/usr/bin/env bash
###############################################################################
# Azure App Service — custom deployment script
# This script runs inside the Azure App Service container after code is pushed.
# Reference: https://learn.microsoft.com/en-us/azure/app-service/configure-language-php
###############################################################################
set -euo pipefail

APP_DIR="${DEPLOYMENT_TARGET:-/home/site/wwwroot}"
cd "$APP_DIR"

echo "==> Installing PHP dependencies..."
composer install --prefer-dist --no-dev --no-interaction --optimize-autoloader

echo "==> Installing Node dependencies & building assets..."
if [ -f package.json ]; then
    npm ci --ignore-scripts
    npm run production || echo "WARN: frontend build returned non-zero (CSS/JS may be stale)"
fi

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Caching configuration..."
php artisan config:cache  || true
php artisan route:cache   || true
php artisan view:cache    || true
php artisan event:cache   || true

echo "==> Setting permissions..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

echo "==> Deployment complete."
