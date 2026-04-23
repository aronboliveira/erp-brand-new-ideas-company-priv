# Session 2026-02-09 — Batch 15 & 16

## Context

Branch: `agent`
Previous HEAD: `290c76cc` (Batch 14)
Commits made: Batch 15 (`91a8eb36`), Batch 16 (`80297b99`)

## Root Cause Analysis

**Dashboard empty / task-board infinite spinner**: SA user was created with random UUID,
but all seeders used `DC::DEFAULT_UUID` for `created_by`. Dashboard queries filter by
`$user->creatorId()` → UUID mismatch → SA sees zero data.

## Key CLIs Used

### Database Verification

```bash
# Verify SA user
mysql -u test -ptest erp_prestech_db -e "SELECT id, email, type FROM users WHERE email='suporte@prestech.com.br';"

# Count SA-owned data
mysql -u test -ptest erp_prestech_db -e "
SELECT 'Goals' as e, COUNT(*) as c FROM goals WHERE created_by='a3e8f4b2-7c1d-4f5a-9b0c-82d6e1f3a5b7'
UNION ALL SELECT 'Bills', COUNT(*) FROM bills WHERE created_by='a3e8f4b2-7c1d-4f5a-9b0c-82d6e1f3a5b7'
UNION ALL SELECT 'Projects', COUNT(*) FROM projects WHERE created_by='a3e8f4b2-7c1d-4f5a-9b0c-82d6e1f3a5b7'
UNION ALL SELECT 'Leads', COUNT(*) FROM leads WHERE created_by='a3e8f4b2-7c1d-4f5a-9b0c-82d6e1f3a5b7'
UNION ALL SELECT 'Deals', COUNT(*) FROM deals WHERE created_by='a3e8f4b2-7c1d-4f5a-9b0c-82d6e1f3a5b7'
UNION ALL SELECT 'Contracts', COUNT(*) FROM contracts WHERE created_by='a3e8f4b2-7c1d-4f5a-9b0c-82d6e1f3a5b7'
UNION ALL SELECT 'Tasks', COUNT(*) FROM project_tasks WHERE created_by='a3e8f4b2-7c1d-4f5a-9b0c-82d6e1f3a5b7';"
```

### Seeding

```bash
# Full re-seed
cd _inc/laravel && php artisan migrate:fresh --seed --force

# Standalone lead seeding (workaround for lead disappearance bug)
php artisan db:seed --class=LeadSeeder --force
```

### PHPUnit

```bash
# Safe: Unit tests only (no DB mutation)
php vendor/bin/phpunit --testsuite=Unit --no-coverage

# Targeted suites
php vendor/bin/phpunit tests/Unit/Enums --no-coverage     # 587 tests ✅
php vendor/bin/phpunit tests/Unit/Middleware --no-coverage # 79/87 pass (8 pre-existing)

# ⛔ NEVER: php artisan test — uses same DB, wipes data!
```

### Log Cleanup

```bash
composer clear-logs
```

### Server

```bash
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000  # 302 = running
```

## Files Modified

### Batch 15 (Locale Fixes)

- `app/Http/Middleware/SetGuestLocale.php`
- `app/Config/Constants/SettingsConstants.php`
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- `resources/views/auth/login.blade.php`
- `public/assets/css/routes/auth/login/toggle.css`

### Batch 16 (Seeder UUID Fixes)

- `database/seeders/UsersTableSeeder.php` — find-or-create pattern for SA
- `database/seeders/GoalsSeeder.php` — add created_by
- `database/seeders/BillSeeder.php` — add created_by
- `database/seeders/InvoiceSeeder.php` — add created_by
- `database/seeders/RevenueSeeder.php` — add created_by
- `database/seeders/LeadSeeder.php` — always set created_by, HARD_CAP=12
- `database/seeders/DatabaseSeeder.php` — unconditional LeadSeeder re-run

## Known Issues

- `VerifyCsrfTokenTest`: 8 pre-existing failures (tests nonexistent `parseCookies()` method)
- Lead disappearance during full seeding: root cause unknown, workaround in place
- CRM "Meus Clientes" tabs & "Novos Clientes por Mês" chart: views don't exist in codebase (new features)
- Invoices = 0, Revenues = 0 from seeding (may need seeder fixes for dependencies)

## Test Results

| Suite      | Tests | Pass    | Fail | Errors |
| ---------- | ----- | ------- | ---- | ------ |
| Enums      | 587   | 587     | 0    | 0      |
| Middleware | 87    | 79      | 1    | 7      |
| Database   | ~200+ | timeout | -    | -      |
| App        | ~100+ | timeout | -    | -      |
