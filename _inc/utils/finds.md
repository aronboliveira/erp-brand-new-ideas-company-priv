# Finds — ERP Prestech Debugging

## Project structure

`find app/Models -name '*.php' -type f | sort` — List all model files

`find app/Http/Controllers -name '*.php' -type f | sort` — List all controllers

`find resources/views -name '*.blade.php' -type f | sort` — List all Blade templates

`find database/migrations -name '*.php' -type f | sort` — List all migrations

`find routes -name '*.php' -type f` — List all route files

`find Modules -name '*.php' -type f | head -50` — List module PHP files

`find config -name '*.php' -type f | sort` — List all config files

`find app/Http/Middleware -name '*.php' -type f | sort` — List all middleware

`find app/Providers -name '*.php' -type f | sort` — List all service providers

`find app/Traits -name '*.php' -type f | sort` — List all traits

## File size / weight

`find . -name '*.php' -type f -size +100k | sort` — Large PHP files (>100KB)

`find . -name '*.js' -type f -size +50k -not -path '*/node_modules/*' -not -path '*/vendor/*' | sort` — Large JS files

`find . -name '*.blade.php' -type f -size +20k | sort` — Large Blade templates

`find storage/logs -name '*.log' -type f -exec du -sh {} + | sort -rh | head -10` — Largest log files

`find . -name '*.php' -type f -not -path '*/vendor/*' | xargs wc -l | sort -rn | head -20` — PHP files by LOC

## Stale / suspicious files

`find . -name '*.php.bak' -o -name '*.php.old' -o -name '*.orig' -type f` — Backup files left behind

`find . -name '.env*' -type f -not -path '*/vendor/*'` — All environment files

`find . -name '*.sql' -type f -not -path '*/vendor/*'` — SQL dump files

`find . -name '*.lock' -type f -not -path '*/vendor/*'` — Lock files

`find . -empty -type d -not -path '*/.git/*' -not -path '*/vendor/*' -not -path '*/node_modules/*'` — Empty directories

`find . -name '*.tmp' -o -name '*.swp' -o -name '*~' -type f` — Temp/swap files

## Permissions and ownership

`find storage bootstrap/cache -not -writable -type f` — Non-writable storage files

`find storage -name '*.php' -type f` — PHP files in storage (compiled views, suspicious)

`find public -name '*.php' -type f -not -name 'index.php'` — PHP files in public (besides index)

## Recent changes

`find app/ resources/ routes/ -name '*.php' -newer artisan -type f | sort` — PHP files modified after artisan

`find . -name '*.php' -mmin -60 -not -path '*/vendor/*' -not -path '*/storage/*' -type f` — PHP files changed in last hour

`find . -name '*.blade.php' -mmin -60 -type f` — Blade files changed in last hour

`find . -name '*.js' -mmin -60 -not -path '*/node_modules/*' -type f` — JS files changed in last hour

## Assets and uploads

`find public/assets -type f | wc -l` — Count asset files

`find public/uploads -type f 2>/dev/null | wc -l` — Count uploaded files

`find storage/uploads -type f 2>/dev/null | wc -l` — Count storage uploads

`find public -name '*.css' -type f -not -path '*/vendor/*' | sort` — CSS files in public

`find public -name '*.js' -type f -not -path '*/vendor/*' -not -path '*/node_modules/*' | sort` — JS files in public

## Tests

`find tests -name '*.php' -type f | sort` — PHP test files

`find tests -name '*.test.js' -o -name '*.spec.js' -o -name '*.test.ts' -type f | sort` — JS/TS test files

## Modules

`find Modules -maxdepth 1 -type d` — List all modules

`find Modules -name 'routes' -type d` — Module route directories

`find Modules -name '*.blade.php' -type f | sort` — Module Blade templates

`find Modules -name 'ServiceProvider.php' -o -name '*ServiceProvider.php' -type f` — Module service providers

## Cleanup candidates

`find . -name 'node_modules' -type d -not -path '*/vendor/*'` — All node_modules directories

`find . -name '__pycache__' -type d` — Python cache directories

`find . -name '.DS_Store' -type f` — macOS metadata files

`find storage/framework/sessions -type f | wc -l` — Session file count

`find storage/framework/views -name '*.php' -type f | wc -l` — Compiled view count

## Duplicates and conflicts

`find . -name '*.php' -path '*/Models/*' -type f -exec basename {} \; | sort | uniq -d` — Duplicate model filenames

`find . -name '*.blade.php' -type f -exec basename {} \; | sort | uniq -cd | sort -rn | head -10` — Most duplicated Blade names
