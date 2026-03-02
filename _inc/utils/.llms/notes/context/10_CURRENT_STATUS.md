# 10 — CURRENT STATUS (2026-03-01)

## Branch & HEAD

- **Branch:** `main`
- **HEAD:** `c509faac` — `fix(providers): Correct EmployeeAttendanceController namespace in Blade imports`
- **Previous notable HEAD:** `3cc44472` — `feat(lint): ESLint flat config + zero-error pass on core & route JS`

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
| Playwright (5 browsers) | 1,909 | 1    | 0      |
| Mock pages Jest     | 376     | 0       | 0      |

> ✅ Playwright updated to 1,909 passed across 5 browser projects (2026-02).
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

## Recent commits (post Batch 38)

| Commit     | Summary                                                                         |
| ---------- | ------------------------------------------------------------------------------- |
| `c509faac` | fix(providers): Correct EmployeeAttendanceController namespace in Blade imports |
| `dd6d4ef6` | fix(views): Correct mixed PHP/Blade syntax in template6 invoice template        |
| `c4996052` | fix(middleware): Add missing Str import, remove unused RedirectResponse in XSS  |
| `729620ab` | feat: new migrations, test specs, and tooling from prior sessions (comp sync)   |
| `9bb5e945` | test: update test suites — PHPUnit, Jest, Playwright, Python (162 files)        |
| `b5fb0fcb` | fix: accumulated source code changes — controllers, exporters, views, middleware |
| `423a7585` | feat: add isolated testing CLI scripts to composer.json and package.json        |
| `96ca46c7` | refactor: reorganize _inc/utils/ scripts and reports                            |
| `aca93d90` | refactor: reorganize ctx/agents/ into frontend/backend/infrastructure           |
| `a4fe5515` | docs: update 8 context files — SA user, DB state, status, git history, versions |
| `3cc44472` | ESLint flat config + zero-error pass on core & route JS (Batch 38)              |

## App server

```bash
cd _inc/laravel
php artisan serve --port=8000
# Access: http://127.0.0.1:8000
```
