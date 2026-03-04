# 02 — PROJECT OVERVIEW

## Tech Stack

- **Framework:** Laravel 10.49.0
- **PHP:** 8.4.5
- **Database:** MySQL 8 — single DB `erp_prestech_db`
- **Frontend:** Blade + jQuery + Bootstrap 5 + DataTables
- **JS bundler:** Laravel Mix (webpack.mix.js)
- **Node:** present but not critical for backend work
- **OS:** Ubuntu Linux (dev machine)

## Paths (from repo root)

```
repo root:  erp_prestech/
laravel:    _inc/laravel/                    ← artisan lives here
app:        _inc/laravel/app/
views:      _inc/laravel/resources/views/
routes:     _inc/laravel/routes/web.php
config:     _inc/laravel/config/
public:     _inc/laravel/public/
storage:    _inc/laravel/storage/
old code:   _old/                             ← reference only, do NOT edit
notes:      notes/
llm notes:  _inc/utils/.llms/notes/
```

## .env (key values)

```env
APP_NAME='ERP Nova Prestech'
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/
APP_TIMEZONE=America/Sao_Paulo
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erp_prestech_db
DB_USERNAME=test
DB_PASSWORD=test
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_DRIVER=sync
SESSION_SECURE_COOKIE=false
```

## Running the app

```bash
cd _inc/laravel
php artisan serve --port=8000
# Access: http://127.0.0.1:8000
```

## Login credentials (SA)

```
email:    suporte@prestech.com.br
password: test1234
```

## Clearing caches (do this after any view/route changes)

```bash
cd _inc/laravel
php artisan view:clear
php artisan route:clear
php artisan config:clear
# OR all at once:
php artisan optimize:clear
```

## Log files

```
storage/logs/laravel.log          ← main log
storage/logs/volatile-*.log       ← high-frequency, can be truncated
storage/logs/short_lived-*.log    ← medium-frequency
storage/logs/warning_trace-*.log  ← warnings only
```

Custom composer command: `composer clear-logs` truncates all volatile logs.
