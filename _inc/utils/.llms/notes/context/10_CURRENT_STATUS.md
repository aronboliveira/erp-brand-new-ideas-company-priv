# 10 — CURRENT STATUS (2026-02-28)

## Branch & HEAD

- **Branch:** `agent`
- **HEAD:** `3cc44472` — `feat(lint): ESLint flat config + zero-error pass on core & route JS`

## What is working (code-level)

- ✅ All 1,542 route definitions compile and resolve correctly
- ✅ PHP syntax clean — **PHPStan level 5: 0 errors**
- ✅ ESLint flat config: 0 errors across all core & route JS
- ✅ SA user `suporte@prestech.com.br` (id `a3e8f4b2-7c1d-4f5a-9b0c-82d6e1f3a5b7`) confirmed in DB
- ✅ 28 mock HTML+JS route test pages covering all 1,532 route groups
- ✅ Singleton bootstrap architecture for frontend JS (27 route files refactored)
- ✅ 7 security fixes applied (unserialize RCE, flash XSS, payment IPN, eval(), stored XSS, rich-text purifier, raw SQL injection)
- ✅ 204 i18n translation tests (69 Playwright + 135 Jest)
- ✅ 96 raw route string literals migrated to VW:: constants
- ✅ 35 missing constants added across 8 controllers
- ✅ PurchaseController: 12× ModelNotFoundException → 404 handling

## Test Results Summary

| Suite              | Passed  | Skipped | Failed |
| ------------------ | ------- | ------- | ------ |
| PHPStan (level 5)  | 0 errs  | —       | 0      |
| PHPUnit (unit)     | 8,459+  | 31      | 0      |
| Root Jest           | 524     | 0       | 0      |
| Frontend Jest       | 949     | 0       | 0      |
| Playwright          | 223     | 0       | 0      |
| Mock pages Jest     | 376     | 0       | 0      |

> ⚠️ PHPUnit Controllers C and Feature suite results may be stale — rerun needed.

## What is BROKEN / Known Issues

- ⚠️ **Database mostly empty** — only 2 users, 0 employees/customers/leads. Needs re-seeding.
- ⚠️ **DNS2D facade**: Globally broken — only fixed in invoice template1 via instance workaround
- ⚠️ **PhpSpreadsheet**: `Borders::getInsideHorizontal()` undefined in LeaveReportExport/ProductStockExport (non-blocking)
- ⚠️ **Expense PDF**: Data integrity (bill FK issue) — not a code bug
- ⚠️ `task_stages/show.blade.php` missing (handled gracefully)
- ⚠️ `ProposalSeeder` uses `where` instead of `whereIn`
- ⚠️ `TrainingTypeSeeder` references non-existent `duration_min` column

## What needs to happen next

### 1. Re-seed the database

Entity tables are empty. Run:
```bash
cd _inc/laravel
php artisan db:seed --class=ContentValidationSeeder
# Or: composer serve-sh-soft
```

### 2. Apply DNS2D instance workaround globally

Currently only patched in invoice template1. Other PDF templates (bill, payslip, etc.) still break.

### 3. Complete remaining PHPUnit coverage

- Finish Http Controllers C batch (`views,shapes,individuals`)
- Run Feature test suite

### 4. Clean up project documentation

- Update `notes/` directory (severely outdated)
- Reorganize `_inc/utils/` scripts
- Structure agent context files by frontend/backend/infra

## Recent commits (post Batch 23)

| Commit     | Summary                                                                |
| ---------- | ---------------------------------------------------------------------- |
| `3cc44472` | ESLint flat config + zero-error pass on core & route JS                |
| `557aa8ae` | Singleton bootstrap architecture + refactor 27 route files             |
| `c10f971d` | 3 orphan ProjectController routes + 35 missing consts + 96 const refs  |
| `407b1bb6` | DealController ModelNotFoundException → 500 fixed                      |
| `faec8ce3` | All 9 PHPStan level-5 errors resolved                                  |
| `4a3b7ca4` | Missing public consts + snake_case methods + route constants            |
| `aaef3f39` | Expense create page failures (3 bugs)                                  |
| `6f4e49f1` | i18n locale bugs + 204 translation tests                               |
| `9e7d78eb` | Pre-existing PHPUnit Feature + Playwright failures resolved             |
| `6ea8febc` | PHP 8 nested ternary, ContractNotes collision, finance selector         |
| `3d67d687` | budgets/create 500 and debit_notes SQL error                            |
| `d19468a3` | Comprehensive route smoke test framework                                |

## App server

```bash
cd _inc/laravel
php artisan serve --port=8000
# Access: http://127.0.0.1:8000
```
