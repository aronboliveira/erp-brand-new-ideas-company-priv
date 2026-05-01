# Laravel Artisan Commands - ERP Brand New Ideas Company

## Development Server

### Start Development Server

```bash
cd _inc/laravel
php artisan serve
```

Default: http://localhost:8000

### Custom Port

```bash
php artisan serve --port=8080
```

### Custom Host

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

## Database Commands

### Migrations

```bash
# Run all pending migrations
php artisan migrate

# Migrate with seed
php artisan migrate --seed

# Fresh migration (drops all tables)
php artisan migrate:fresh

# Fresh migration with seed
php artisan migrate:fresh --seed

# Reset all migrations
php artisan migrate:reset

# Rollback last migration
php artisan migrate:rollback

# Rollback specific steps
php artisan migrate:rollback --step=2

# Check migration status
php artisan migrate:status
```

### Seeders

```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=UsersTableSeeder

# Wipe database (no migration drop)
php artisan db:wipe
```

## Cache Management

### Clear All Caches

```bash
php artisan optimize:clear
```

### Individual Cache Clear

```bash
# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache (Blade)
php artisan view:clear

# Clear compiled classes
php artisan clear-compiled
```

### Create Caches (Production)

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize for production
php artisan optimize
```

## Permission Commands

### Clear Permission Cache

```bash
php artisan permission:cache-reset
# or
php artisan permission:clear-cache
```

### Create Permission

```bash
php artisan permission:create-permission "edit posts"
```

### Show Permissions

```bash
php artisan permission:show
```

## Route Commands

### List All Routes

```bash
php artisan route:list
```

### Sort Routes by URI

```bash
php artisan route:list --sort=uri
```

### Filter Routes by Name

```bash
php artisan route:list --name=api
```

### Filter Routes by Method

```bash
php artisan route:list --method=GET
```

### Filter Routes by Path

```bash
php artisan route:list --path=admin
```

## Queue Commands

### Run Queue Worker

```bash
php artisan queue:work
```

### Run Single Job

```bash
php artisan queue:work --once
```

### Monitor Failed Jobs

```bash
php artisan queue:failed
```

### Retry Failed Job

```bash
php artisan queue:retry 1
```

### Retry All Failed Jobs

```bash
php artisan queue:retry all
```

### Clear Failed Jobs

```bash
php artisan queue:flush
```

## Composer Integration

### Update Dependencies

```bash
composer update --with-all-dependencies
```

### Install Dependencies

```bash
composer install
```

### Dump Autoloader

```bash
composer dump-autoload -o
```

### Clear Composer Cache

```bash
composer clear-cache
```

## Testing Commands

### Run PHPUnit Tests

```bash
php artisan test
```

### Run Specific Test Suite

```bash
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

### Run Tests with Coverage

```bash
php artisan test --coverage
```

### Run Specific Test File

```bash
php artisan test tests/Unit/ExampleTest.php
```

## Make Commands

### Create Controller

```bash
php artisan make:controller UserController
php artisan make:controller UserController --resource
php artisan make:controller Api/UserController --api
```

### Create Model

```bash
php artisan make:model User
php artisan make:model User -m  # with migration
php artisan make:model User -c  # with controller
php artisan make:model User -mcr  # with migration, controller, resource
```

### Create Migration

```bash
php artisan make:migration create_users_table
php artisan make:migration add_status_to_users_table --table=users
```

### Create Seeder

```bash
php artisan make:seeder UsersTableSeeder
```

### Create Middleware

```bash
php artisan make:middleware CheckAge
```

### Create Request

```bash
php artisan make:request StoreUserRequest
```

### Create Resource

```bash
php artisan make:resource UserResource
php artisan make:resource UserCollection
```

### Create Policy

```bash
php artisan make:policy UserPolicy
php artisan make:policy UserPolicy --model=User
```

## Maintenance Mode

### Enable Maintenance Mode

```bash
php artisan down
```

### Enable with Secret

```bash
php artisan down --secret="maintenance-token"
# Access: http://yoursite.com/maintenance-token
```

### Disable Maintenance Mode

```bash
php artisan up
```

## Environment Commands

### Generate Application Key

```bash
php artisan key:generate
```

### Show Environment Info

```bash
php artisan env
```

### Tinker (REPL)

```bash
php artisan tinker
```

## Storage Commands

### Link Public Storage

```bash
php artisan storage:link
```

## Custom Commands (ERP Brand New Ideas Company)

### Clear Logs

```bash
composer clear-logs
```

### Combined Clear (Experimental)

```bash
# PowerShell
composer artclrs-ps

# Unix/Linux
composer artclrs-sh
```

## Module Commands (LandingPage)

### List Modules

```bash
php artisan module:list
```

### Enable Module

```bash
php artisan module:enable LandingPage
```

### Disable Module

```bash
php artisan module:disable LandingPage
```

## Debugging Commands

### Dump Server

```bash
php artisan serve --debug
```

### Show Application Routes

```bash
php artisan route:list --columns=method,uri,name,action
```

### Show Events

```bash
php artisan event:list
```

### Show Jobs

```bash
php artisan queue:monitor
```

## Production Deployment Checklist

```bash
# 1. Update dependencies
composer install --no-dev --optimize-autoloader

# 2. Generate key (if needed)
php artisan key:generate

# 3. Run migrations
php artisan migrate --force

# 4. Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 5. Link storage
php artisan storage:link

# 6. Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Common Troubleshooting

### "Class not found"

```bash
composer dump-autoload -o
php artisan clear-compiled
php artisan optimize
```

### "Permission denied"

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### "Route not found"

```bash
php artisan route:clear
php artisan route:cache
php artisan route:list
```

### "Config cached" errors

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```
