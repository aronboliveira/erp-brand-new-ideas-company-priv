# Code Flag And Clean-Tree Audit Commands — 2026-05-09

Run from `_inc/laravel/` unless a command says repo root.

## Code-Flag Search

```bash
rg -n -i \
  'todo|to[-_ ]?do|fix[-_ ]?me|hack|xxx|deferred|follow[-_ ]?up|temporary|work[-_ ]?around|mock|fake|stub' \
  app Modules resources routes tests utils \
  -g '!vendor/**' -g '!node_modules/**'
```

```bash
rg -n \
  'markTest(Skipped|Incomplete)\s*\(|aliasMock|overload:|resetTestSeams|Mock[A-Za-z0-9_]*Gateway|setGateway\s*\(' \
  app Modules tests \
  -g '*.php'
```

```bash
rg -n -i \
  'prod(uction)?|go[-_ ]?live|release|hardening|remove before|replace before' \
  app Modules resources routes tests utils \
  -g '!vendor/**' -g '!node_modules/**'
```

## Settings / Upload Fixture Leaks

```bash
rg -n \
  'storage_setting|local_storage_validation|local_max_upload_size|resetSettingsCache|updateOrInsert\s*\(' \
  app Modules tests \
  -g '*.php'
```

## Clean-Tree Gate

```bash
# repo root
git status --short
git diff --check
git diff --name-only -- _inc/laravel/database/migrations _inc/.seeders
```

## Cache / Log Cleanup

```bash
php artisan optimize:clear
find storage/logs -type f -name '*.log' -exec truncate -s 0 {} +
```

Do not run `php artisan test`; use `php vendor/bin/phpunit ... --no-coverage`.
