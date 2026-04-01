# 10 — CURRENT STATUS (2026-04-01)

## Branch & HEAD

- **Outer repo:** `develop` — HEAD: `db6f5b947` — `chore(gitignore): add **/black-hat/ exclusion`
- **Inner repo (_inc/):** `master` — HEAD: `4d57ec70` (rewritten by filter-branch to purge black-hat history)
- **Previous notable HEAD:** `98b9cdf18` — last pre-filter-branch commit

## What is working (code-level)

- ✅ PHPStan: **0 errors** (2GB memory limit)
- ✅ Jest: **28/28 suites, 652/652 tests passed** (16.1s)
- ✅ PHPUnit: **176 PASS suites, 874 individual tests passed** (7 failing, 40 warnings)
- ✅ Playwright E2E: **9 passed, 3 skipped** (3.6 min)
- ✅ All security headers present: CSP, HSTS, X-Frame-Options DENY, X-Content-Type-Options nosniff
- ✅ CSRF tokens active on all forms
- ✅ SQL injection fixes applied (EventController, DashboardController, Activity model)
- ✅ Black-hat test files purged from git history via filter-branch
- ✅ MySQL healthy: 211 tables, 219 migrations, 1044 FKs, 101 users, 1680 permissions, 8 roles
- ✅ Login/register pages render correctly with all assets
- ✅ 30 roleplay test files + 30 multi-language attack scripts created
- ✅ 23 granular commits applied to develop branch

## Test Results Summary (2026-04-01)

| Suite | Passed | Skipped/Warn | Failed |
|-------|--------|-------------|--------|
| PHPStan | 0 errors | — | 0 |
| PHPUnit Unit | 874 ✓ | 40 warn | 10 ⨯ (7 suites) |
| PHPUnit Feature | — | — | Blocked (BillProduct fatal) |
| Jest | 652 | 0 | 0 |
| Playwright | 9 | 3 | 0 |
| TSC | — | — | 1 (casing) |
| ESLint | — | — | 77,576 (99% from ts/.backup/public/Modules) |
| flake8 | — | — | 147 (style) |
| mypy | — | — | 37 in 6 files |

## What is BROKEN / Known Issues

- ⚠️ **BillProduct class redeclaration** — `app/Models/Bills/BillProduct.php` uses `namespace App\Models` (wrong). Crashes Feature test autoload.
- ⚠️ **MessagesController missing** — Blocks `php artisan route:list`
- ⚠️ **7 PHPUnit failures** — BugTest relations, EmailTest scope, JobStageTest/LabelTest fillable, ProductServiceUnitTest relation, GeneratedOfferLetterTest count, MassAssignmentTest guarded
- ⚠️ **ESLint scope too wide** — Scanning `ts/`, `.backup/`, `public/`, `Modules/` (77K+ false errors)
- ⚠️ **DNS2D facade**: Globally broken — only fixed in invoice template1 via instance workaround
- ⚠️ **PhpSpreadsheet**: `Borders::getInsideHorizontal()` undefined in LeaveReportExport/ProductStockExport (non-blocking)
- ⚠️ **mypy**: 37 type errors in openpyxl Reference calls and None attribute access

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
