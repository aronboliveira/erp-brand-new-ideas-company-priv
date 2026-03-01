# 05 — DATABASE STATE

> **Last updated:** 2026-02-28

## Current state (SPARSE — needs re-seeding)

The database has been rebuilt with migrations but most entity tables are empty.
Only the SA user and one test user exist.

| Table                         | Count | Status                                                    |
| ----------------------------- | ----- | --------------------------------------------------------- |
| `users`                       | 2     | ✅ SA user `suporte@prestech.com.br` + 1 test user        |
| `settings`                    | ?     | ⚠️ Check with `SELECT COUNT(*) FROM settings;`            |
| `languages`                   | ?     | ⚠️ Verify `created_by` = SA UUID                          |
| `migrations`                  | 211   | ✅ All 211 tables exist                                    |
| `notification_templates`      | ?     | ⚠️ Check — may have been repopulated                      |
| `employees`                   | 0     | ❌ Empty                                                   |
| `customers`                   | 0     | ❌ Empty                                                   |
| `leads`                       | 0     | ❌ Empty                                                   |
| `deals`                       | 0     | ❌ Empty                                                   |
| `projects`                    | 0     | ❌ Empty                                                   |
| `invoices`                    | 0     | ❌ Empty                                                   |

### DB history

1. **2026-02-09:** Wiped by rogue seeder (`ProjectDataFixSeeder` from stash)
2. **~2026-02-18:** Fully re-seeded: 612 users, 107 employees, 160 customers, etc.
3. **~2026-02-28:** Rebuilt again — now sparse with only 2 users

## What needs to be restored

### Priority 1 — Verify SA user

```sql
-- Confirm current SA user exists
SELECT id, name, email, type FROM users WHERE type = 'super admin';
-- Expected: a3e8f4b2-7c1d-4f5a-9b0c-82d6e1f3a5b7 / suporte@prestech.com.br
```

### Priority 2 — Settings table

Run `DashboardSeeder` or manually insert key settings (all with `created_by` = SA UUID):

- `default_language` = `pt-br`
- `company_name` = `Nova Prestech`
- `company_email` = `contato@prestech.com.br`
- `site_currency` = `BRL`
- `site_currency_symbol_position` = `pre`
- `site_date_format` = `d/m/Y`
- `site_time_format` = `H:i`
- Plus ~100 more settings for modules, colors, permissions, etc.

### Priority 3 — Languages table fix

```sql
UPDATE languages SET created_by = 'a3e8f4b2-7c1d-4f5a-9b0c-82d6e1f3a5b7';
```

### Priority 4 — Re-seed entity data

Use `DatabaseSeeder::runMocks()` or the standalone seeders:

```bash
cd _inc/laravel
php artisan db:seed --class=ContentValidationSeeder
```

Reference seeders from batches 7/9/13:
- `DashboardSeeder` — customers, vendors, invoices, bills, goals
- `AccountStatementSeeder` — revenues, payments, bank accounts
- `ProjectDataFixSeeder` (Batch 13 version, NOT the old stashed version)

Or use the composer helper:
```bash
composer serve-sh-soft   # gentle refresh
composer serve-sh-mixed  # medium refresh
composer serve-sh-hard   # full rebuild
```

## Schema notes

- **UUID PKs everywhere** — `id` is `char(36)`, NOT auto-increment
- `created_by` column = FK to `users.id` (the SA UUID)
- `assigned_to` column in tasks = comma-separated user IDs (legacy design)
- Most models use `SoftDeletes` — check `deleted_at` in queries
- The `settings` table uses `name`/`value` columns (not normalized)
- 211 tables total as of 2026-02-28
