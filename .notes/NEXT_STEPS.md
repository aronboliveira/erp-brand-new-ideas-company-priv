# NEXT STEPS

> Last updated: 2026-03-11
> Full resolution history in `.notes/.llms/.history/`. Coding patterns in `.notes/.llms/.guidelines/`.

---

## IMMEDIATE

1. **Review PHPStan L3 fresh result** — `cat /tmp/phpstan_fresh.txt` (run in progress with `--memory-limit=2G`). Fix high-value errors once complete; BillController and DashboardController are primary targets.
2. **Fix `npm run test:pytest` `/bin/sh` failure** — Change `package.json` `test:pytest` script: replace `source .venv/bin/activate` with `. .venv/bin/activate` so it works under POSIX sh.
3. **Run Playwright E2E** — Requires `php artisan serve` running. Then `npm run test:playwright` (9 specs, auth setup must succeed)
4. **Run curl timing** — `bash tests/curl_timing.sh` (requires running server, benchmarks 18+ routes)
5. **Test shared-link password flow** — existing shared links require password re-entry (base64→bcrypt migration)
6. **Replace JS route files** — Use `ts/dist-iife/` output to replace `public/assets/js/routes/` (1,097 files). Core singleton `erp-core.js` should be loaded in Blade footer before route scripts.

---

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
