# CURRENT WORKING ISSUES

> Last updated: 2026-03-04
> Branch: `main`
> Resolved items archived to `.notes/.llms/.history/`. Guidelines in `.notes/.llms/.guidelines/`.

---

## STATUS OVERVIEW

| Area                        | Status | Notes                                               |
| --------------------------- | ------ | --------------------------------------------------- |
| PHP syntax / PSR-4          | ✅     | All controllers, models, middleware compile cleanly |
| IDE import warnings         | ✅     | All unused imports removed                          |
| Backend security (F1-F8)    | ✅     | All 8 findings fixed (2026-03-03 / 2026-03-04)      |
| Frontend JS security        | ✅     | innerHTML, document.write, eval, XSS — all fixed    |
| Laravel server              | ✅     | Running at 127.0.0.1:8000                           |
| Jest                        | ✅     | 10/10 pass (3 suites)                               |
| PHPUnit (ContractNotesTest) | ✅     | 2/2 pass, 4 assertions                              |
| PhpSpreadsheet exports      | ✅     | Border methods corrected for v1.30+                 |
| Seeders (Proposal/Training) | ✅     | whereIn + PJC constants fixed                       |
| Playwright (frontend mock)  | ✅     | 837 passed, 10 failed (RBAC pre-existing)           |
| Playwright (E2E)            | ✅     | Auth setup project — cookies auto-refresh           |

---

## ACTIVE ISSUES

### 1. Database — Sparse State (HIGH) — ✅ RESOLVED

ContentValidationSeeder created. Idempotent seeder for ~30 catalog tables + user type bootstrap.

```bash
cd _inc/laravel
php artisan db:seed --class=ContentValidationSeeder
```

### 2. Missing Blade View (LOW) — ✅ RESOLVED

`task_stages/show.blade.php` created with full admin layout, breadcrumbs, and all TaskStage attributes.

### 3. Arabic Locale (MEDIUM) — ✅ RESOLVED

`LanguageController::storeLanguage()` now sets `created_by` on new languages. Existing DB data verified correct.

### 4. Playwright Firefox Flaky (COSMETIC)

Firefox render-timing benchmark for `/reports/balance-sheet` skips intermittently. Not a code bug.

### 5. Deferred Security — ✅ RESOLVED

- **D1** (IDOR): Tenant scoping added to 11 `findOrFail` calls across UserController, EmployeeController, ClientController, JobCategoryController, CompanyPolicyController
- **D2** (Passwords): `base64_encode/decode` replaced with `Hash::make/check` in ProjectController, blade, and test
- **D3** (jobApplyData): Converted to public form with input validation; storage quota charged to job owner

---

## REMINDERS

```
⛔ NEVER run `php artisan test`            — wipes production DB
⛔ NEVER run `php artisan migrate:fresh`   — same
⛔ NEVER cast $user->id to (int)           — UUID always returns 0
⛔ NEVER push to comp remote               — push only to origin
⛔ Always use MWC::, VW::, PMC:: constants — no raw strings in routes
```
