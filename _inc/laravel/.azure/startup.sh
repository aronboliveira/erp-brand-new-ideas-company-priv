#!/usr/bin/env bash
###############################################################################
# Azure App Service — custom startup script
# Configured via: az webapp config set --startup-file /home/site/wwwroot/.azure/startup.sh
###############################################################################
set -euo pipefail

APP_DIR="/home/site/wwwroot"
cd "$APP_DIR"

# Ensure storage structure
mkdir -p storage/logs \
         storage/framework/{cache/data,sessions,views} \
         bootstrap/cache

chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# Link storage if needed
php artisan storage:link 2>/dev/null || true

# Run migrations (safe for production — only pending ones)
php artisan migrate --force 2>/dev/null || true

# Cache
php artisan config:cache  || true
php artisan route:cache   || true
php artisan view:cache    || true

# Start PHP-FPM (Azure expects this process to keep running)
exec php-fpm
