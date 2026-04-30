# NEXT STEPS

> Last updated: 2026-04-01
> Full resolution history in `.notes/.llms/.history/`. Coding patterns in `.notes/.llms/.guidelines/`.

---

## IMMEDIATE

1. ~~**Run Playwright E2E**~~ — ✅ DONE (9 passed, 3 skipped, 3.6 min)
2. ~~**Run curl timing**~~ — ✅ DONE (40+ routes tested, no 5xx, all security headers present)
3. **Fix BillProduct class redeclaration** — `app/Models/Bills/BillProduct.php` uses namespace `App\Models` instead of `App\Models\Bills`. Feature tests crash on autoload.
4. **Fix MessagesController missing** — Blocks `php artisan route:list`. Remove route or create controller.
5. **Test shared-link password flow** — existing shared links require password re-entry (base64→bcrypt migration)
6. **Replace JS route files** — Use `ts/dist-iife/` output to replace `public/assets/js/routes/` (1,097 files). Core singleton `erp-core.js` should be loaded in Blade footer before route scripts.
7. **ESLint ignores** — Add `ts/`, `.backup/`, `public/`, `Modules/` to ESLint ignores in `eslint.config.mjs`
8. **Fix 7 PHPUnit failures** — BugTest relations, EmailTest scope, JobStageTest fillable, LabelTest fillable, ProductServiceUnitTest relation, GeneratedOfferLetterTest record count, MassAssignmentTest guarded
9. **3-way merge of 531 overlapping files** — PHPStan annotations + agent crash-prevention patterns. See `AGENT_BRANCH_MERGE_LOG.md`.
10. **Review and apply agent's 2,832 file deletions** — Mainly TS rollback from agent branch.

---

## RECENTLY COMPLETED (2026-03-15)

### Utility Delegation + Problems Panel Cleanup + Import DRYing

- **68 methods** extracted from `Utility.php` into 6 service classes under `app/Services/Utility/`
- **Utility.php** reduced from 4,282 to 1,828 lines; all stubs preserved with `@see` references
- **Problems Panel**: 895+ errors → **0 errors** across all PHP files
- **30+ unused imports** removed from `Utility.php`, `FinanceBillingService.php`, `UtilityTest.php`
- **Type fixes**: `(int)$areaCode`, `(string) rand()` for `str_pad`, `@var` annotations for Mockery/Storage
- **IDE fixes**: 10+ files — missing imports, unused imports, wrong namespace references
- **mysql-schema.sql**: Suppressed 72 false-positive SQL linter errors via `.vscode/settings.json`
- **Chart of Account seeding**: `ChartOfAccountType` UUID-guarded ID fix (`$rec->id = $id; $rec->saveQuietly()`)
- **Test assertion fixes**: 13+ number format prefix mismatches (`#` → `INV-`, `BILL-`, etc.)
- **Cache/logs**: Full clear (composer, artisan, PHPStan, npm, view, bootstrap, debugbar, storage/tmp)
- **File archival**: 9 outdated scan files moved to `.notes/.history/` and `.notes/.llms/.history/reports/`

## RECENTLY COMPLETED (2026-03-14)

### Calendar Mock Infrastructure + Test Rewrites

- **CalendarGateway pattern**: Interface + `GoogleCalendarGateway` + `MockCalendarGateway` + `CalendarService` with DI
- **14/14 calendar tests passing**: All rewritten to use `CalendarService::setGateway()`, `MockCalendarGateway` fixtures, `updateOrInsert()` + `resetSettingsCache()`
- **IDE error fixes**: AllowanceController, unused imports, DB imports
- **Test suite**: 395/422 passed (93.6%), 0 risky, 21 accounting failures (pre-existing)
- **Notes/docs**: 14 files moved to `.history/`, 4 files updated (KNOWN_ISSUES, CURRENT_WORKING_ISSUES, NEXT_STEPS, typescript-migration)

## RECENTLY COMPLETED (2026-03-11)

### TypeScript Migration — Gap Closure (7-point plan)

- **1,097/1,097 TS routes** — Full parity with JS routes, 0 tsc errors
- **Core singletons:** `erp-bootstrap.ts`, `erp-guard.ts`, `erp-utils.ts` + barrel `index.ts` (dedup 601 toast, 420 guard, 204 Window augmentation patterns)
- **ESM→IIFE script:** `ts/scripts/esm-to-iife.cjs` — converts 1,102 ESM files to IIFE; output in `ts/dist-iife/`
- **Guidelines:** `.notes/.llms/.guidelines/frontend/esm-iife-strategy.md`, `template-literal-testing.md`
- **Integration tests:** Mock API server (22 endpoints) + 20 Playwright specs — all passing
- **Rollback scripts:** `.backup/scripts/{bash,python,node,php}/20260309_014820/`
- **Harness + tests:** 204 new harness HTML pages, 204 Playwright specs, 204 Jest unit tests
- **Jest totals:** 319/322 suites, 1,458/1,458 tests (3 pre-existing failures unrelated to migration)
- **Build:** 1,127 JS files in `ts/dist/`, 1,102 IIFE files in `ts/dist-iife/`

---

## DEFERRED (monitoring only)

| Item                                        | Effort  | Notes                                                                                      |
| ------------------------------------------- | ------- | ------------------------------------------------------------------------------------------ |
| RoleController Permission scoping           | Trivial | Spatie permissions are global; not a true IDOR                                             |
| ProjectController::projectLink tenant scope | N/A     | Public endpoint by design — encrypted URL is access control                                |
| Individual seeder testing (184 seeders)     | Medium  | Originals in `.backup/database/seeders/`. Live copies can be edited for current test needs |
| CSP nonce-based implementation (D-1)        | Major   | Requires nonce injection in all Blade views                                                |

## RECENTLY COMPLETED (2026-03-07)

### Readonly Scan + Log Archival

- **PHP lint:** 0 errors / 1,417 files
- **ESLint:** 0 errors 0 warnings ✅
- **Jest:** 10 / 10 ✅
- **Pytest (bash):** 53 / 53 ✅
- **HTTP smoke (20 routes):** 0 × 500 ✅
- **Log archival:** `.notes/*.txt/log` → `.notes/.llms/.history/reports/`; `_inc/laravel/.notes/` all dated logs → `_inc/laravel/.notes/.history/` (created)
- **Codex report integrated:** see `.tmp/codex/report-20260305-2/` and CURRENT_WORKING_ISSUES.md

## RECENTLY COMPLETED (2026-03-07 — ESLint + RBAC + HTTP 500 fix batch)

- ESLint 758 warnings → 0; Playwright RBAC 5 tests fixed; HTTP 500s fixed (4 routes); `.gitignore` cleanup

## RECENTLY COMPLETED (2026-03-04)

### PHPUnit & PHPStan Stabilization

- **PHPUnit Feature (DashboardDataTest):** 26/26 tests, 88 assertions, 0 failures
- **PHPStan Level 2:** 0 errors on BillController (down from 80 — @property annotations on 8 models)
- **PHPStan Level 3:** Module-by-module runner created (`scripts/phpstan-modules.sh`)
- **Test DB:** `erp_prestech_test` — 210 tables, 215 migrations, all passing
- **Factory files:** 7 created (Bill, Customer, Vendor, Employee, Invoice, Revenue, BankAccount)
- **Scripts:** Added PHPStan/PHPUnit/pytest/curl commands to `composer.json` and `package.json`

### Prior: Intelephense / VS Code (2026-03-06)

14 fixes across 12 files — import aliases, static properties, case fixes, types, test bugs.

---
