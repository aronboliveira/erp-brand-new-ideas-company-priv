# Shell Commands — 2026-02-08

## Migration verification

```bash
# Syntax-check all migration files
for f in database/migrations/*.php; do php -l "$f"; done

# Wipe DB and run fresh migration
php artisan db:wipe --drop-views --force
php artisan migrate

# Full fresh + seed (used by composer serve-sh--hard)
php artisan migrate:fresh --seed --force
```

## Translation audit

```bash
# Count keys in locale JSON files
for f in resources/lang/*.json; do
  echo "$(basename $f): $(python3 -c "import json; print(len(json.load(open('$f'))))")";
done

# Find keys in en.json not in another locale
python3 -c "
import json
en = set(json.load(open('resources/lang/en.json')).keys())
other = set(json.load(open('resources/lang/es.json')).keys())
diff = en - other
print(f'{len(diff)} missing keys')
for k in sorted(diff): print(f'  - {k}')
"
```

## Nginx / Azure deploy

```bash
# Test nginx config syntax
docker compose exec nginx nginx -t

# Azure startup sequence (in .azure/startup.sh)
php artisan storage:link
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
exec php-fpm
```

## Auth controller verification

```bash
# Check remaining JSON returns in auth controller
grep -n 'response()->json' app/Http/Controllers/Auth/AuthenticatedSessionController.php

# Verify body.textContent fix
grep -n 'body.textContent' app/Http/Controllers/Auth/AuthenticatedSessionController.php
```
