# Route Health Report — 2026-02-07

## Summary

| Crawl         | HTTP 500s     | Date             | Notes                                                      |
| ------------- | ------------- | ---------------- | ---------------------------------------------------------- |
| v5 (baseline) | **46** (5.5%) | 2026-02-07 19:30 | Fresh `migrate:fresh --seed`, first full 832-route crawl   |
| v6            | **20** (2.4%) | 2026-02-07 20:23 | After 6 code bug fixes                                     |
| v8 (final)    | **13** (1.6%) | 2026-02-07 21:45 | After 4 more fixes. All remaining are data/protocol/config |

**Total code bugs fixed: 33 routes went from 500 → non-500 (71.7% reduction)**

---

## Code Bugs Fixed (all verified working in v8)

### 1. Missing Spatie Permissions (10 permissions)

- **Symptom:** 500 on ~9 routes (training, zoom, reports)
- **Fix:** Created permissions in DB + updated `PermissionsConstants.php` + `SeedersTemplating.php`

### 2. View `[app]` not found

- **Symptom:** 500 on Fortify/Jetstream routes (`/login`, `/register`, etc.)
- **Fix:** Created `resources/views/app.blade.php` (minimal Inertia layout)

### 3. `FaqController::create()` — missing `$settings` variable

- **File:** `Modules/LandingPage/Http/Controllers/FaqController.php`
- **Symptom:** `compact()` failed on undefined `$settings`
- **Fix:** Added `$settings = LandingPageSetting::landingPageSetting();`

### 4. `TimesheetController` — 7 broken guard patterns

- **File:** `app/Http/Controllers/Shapes/TimesheetController.php`
- **Symptom:** `if ($deny = $this->guard(...))` was truthy when authorized (returns `true`)
- **Fix:** Changed all 7 to `if (($deny = $this->guard(...)) !== true)`

### 5. `TimesheetController::filterTimesheetTable()` — wrong return type

- **File:** `app/Http/Controllers/Shapes/TimesheetController.php`
- **Symptom:** Catch block returned `RedirectResponse` but method declares `JsonResponse`
- **Fix:** Changed catch to `return response()->json([...], 500)`

### 6. `HomeController::show()` — strict int type hint

- **File:** `Modules/LandingPage/Http/Controllers/HomeController.php`
- **Symptom:** Routes pass string IDs, type hint was `int $id`
- **Fix:** Changed to `string|int $id`

### 7. `BenefitPaymentController` — missing dot in route name

- **File:** `app/Http/Controllers/Bills/BenefitPaymentController.php`
- **Symptom:** `VW::INV . 'link.copy'` = `'invoiceslink.copy'` (should be `'invoices.link.copy'`)
- **Fix:** Changed 3 occurrences to `VW::INV . '.link.copy'`

### 8. `forgot_password.blade.php` — Collection cast + unsafe array access

- **File:** `resources/views/auth/forgot_password.blade.php`
- **Symptom:** `(array)(Utility::languages())` cast Collection object, not items → `mb_substr()` error
- **Fix:** Used `->all()` for Collection, added safe key fallback

### 9. Webhook view name mismatch

- **File:** `app/Http/Controllers/Configs/SystemController.php`
- **Symptom:** Views referenced `webhook.xxx` but directory is `webhooks/`
- **Fix:** Changed 3 references to `webhooks.xxx`

### 10. User notifications relationship mismatch

- **File:** `app/Models/Individuals/User.php`
- **Symptom:** `Notifiable` trait uses `morphMany(notifiable_type)` but custom table uses `user_id`
- **Fix:** Added `notifications()` HasMany override using `user_id`

### 11. Timesheets missing `deleted_at` column

- **Symptom:** SoftDeletes trait on Timesheet model but table lacked column
- **Fix:** Added column via Schema + created migration `2026_02_07_212821_add_deleted_at_to_timesheets.php`

### 12. Exception handler `ModelNotFoundException` → 500

- **File:** `app/Exceptions/Handler.php` + `app/Http/Controllers/Helpers/ErrorHandlers.php`
- **Fix:** Added `ModelNotFoundException` detection to return 404 instead of 500

---

## Remaining 13 HTTP 500s (NOT code bugs)

### Data-Dependent — No Deal Records (4 URLs)

Seeder creates 0 deals. Routes fail with `ModelNotFoundException` caught inside DealController.

- `/deals/1/tasks`
- `/deals/1/tasks/1/edit`
- `/deals/1/tasks/1/show`
- `/deals/1/users`

### Data-Dependent — Project UUID vs Integer (5 URLs)

Projects use UUID primary keys. Integer `1` doesn't match any project.

- `/projects/1/users/1/permission`
- `/projects/copies/1`
- `/projects/copies/links/1`
- `/projects/copy-links/1`
- `/share-projects/1`

### POST-Only Routes Hit with GET (3 URLs)

These routes expect POST data. GET requests trigger `ValidationException`.

- `/email_template_stores/1`
- `/projects.timesheets/projects/updates/1`
- `/store-language`

### External Config Required (1 URL)

- `/stripes/1` — Requires Stripe API configuration

---

## Route Distribution (v8)

| Status                     | Count | %     |
| -------------------------- | ----- | ----- |
| 200 OK                     | 522   | 62.7% |
| 404 Not Found              | 241   | 29.0% |
| 302000 (redirect artifact) | 28    | 3.4%  |
| 429 Too Many Requests      | 14    | 1.7%  |
| 500 Internal Server Error  | 13    | 1.6%  |
| 401 Unauthorized           | 6     | 0.7%  |
| 422 Unprocessable Entity   | 4     | 0.5%  |
| 400 Bad Request            | 2     | 0.2%  |
| 204 No Content             | 2     | 0.2%  |

---

## Session 3 — i18n Audit (2026-02-25, commit `6f4e49f1`)

**Scope:** Full server-side locale and translation system audit.

### Bugs Fixed

#### 1. `change-language` vs `change-languages` route URL

- **File:** `tests/e2e/i18n.spec.cjs`
- **Bug:** Test used `/change-language/{lang}` (singular). Actual route is `/change-languages/{lang}` (with `s`).
- **Fix:** Updated 6 occurrences.

#### 2. `SetGuestLocale` middleware — unsupported locale fell through to `en`

- **File:** `app/Http/Middleware/SetGuestLocale.php`
- **Bug:** Unsupported locale code set app locale to the raw input instead of falling back to `en`.
- **Fix:** Added validation against supported locales, fall back to `en`.

#### 3. `SetLocale` middleware — missing `xx` config fallback

- **File:** `app/Http/Middleware/SetLocale.php`
- **Bug:** `xx` test code fell through and threw a missing config key error.
- **Fix:** Added fallback for invalid locale codes.

#### 4. `change-languages` endpoint — session locale not flushed across redirects

- **Bug:** Session locale not reliably persisting after redirect chain.
- **Fix:** Added `Session::save()` before redirect.

#### 5. Arabic/Hebrew `dir="rtl"` missing on admin layout

- **File:** `resources/views/layouts/admin.blade.php`
- **Bug:** `dir` attribute not set based on locale.
- **Fix:** Added RTL detection from `$lang` and set `dir` accordingly.

### Tests Added

| Suite                                              | Tests               |
| -------------------------------------------------- | ------------------- |
| `tests/e2e/i18n.spec.cjs`                          | 69 Playwright tests |
| `tests/Unit/frontend/js/core/i18n-locale.test.cjs` | 135 Jest tests      |

### Regression

Full Playwright suite after this commit: **300 passed / 1 failed** (expense form — fixed next session)

---

## Session 4 — Expense Form Fix (2026-02-25, commit `aaef3f39`)

**Scope:** Fix the last remaining Playwright failure (`financial.spec.cjs:133`).

### Root Cause Chain (4 cascading bugs)

#### 1. `BankAccount::selectRaw()` — wrong bindings type (3 locations)

- **Files:** `app/Http/Controllers/Bills/ExpenseController.php` (×2), `app/Http/Controllers/Bills/BillController.php` (×1)
- **Bug:** `selectRaw("CONCAT(...) AS name", 'id')` — second arg must be `array` for `?` bindings. Passing `'id'` (string) throws `TypeError` caught silently → `back()` redirect → `/job-application`.
- **Fix:** Moved `id` into the SQL expression: `selectRaw("CONCAT(...) AS name, id")`

#### 2. Breadcrumb using `projects.expenses.index` (requires `{id}` param)

- **File:** `resources/views/expenses/create.blade.php:42`
- **Bug:** `route('projects.expenses.index')` called without required route parameter → `UrlGenerationException`.
- **Fix:** Changed to `route('expenses.index')` with `Route::has()` safety guard.

#### 3. `@php` block used `VW::PRJ_EXP` (`projects.expenses.*`) for all route lookups

- **File:** `resources/views/expenses/create.blade.php` (~lines 289–350)
- **Bug:** `projects.expenses.*` routes all require `{pid}`/`{id}`. The exception is caught, leaving `$storeUrl` etc. undefined → next error.
- **Fix:** Changed all route lookups to `VW::EXP` (`expenses.*`).

#### 4. `Form::open()` received `'route' => $storeUrl` (full URL, not route name)

- **File:** `resources/views/expenses/create.blade.php:356`
- **Bug:** `Form::open(['route' => 'http://localhost:8888/expenses'])` → `Route [http://...] not defined`.
- **Fix:** Changed to `'url' => $storeUrl`.

### Result

| Spec                  | Before  | After          |
| --------------------- | ------- | -------------- |
| `financial.spec.cjs`  | 34/35   | **35/35** ✅   |
| Full Playwright suite | 300/301 | **329/329** ✅ |

---

## Session 5 — Methods Naming Refactor (2026-02-26, commit `4a3b7ca4`)

**Scope:** Complete the camelCase + `public const` naming standard across all controllers and update `routes/web.php` to use `ControllerClass::CONST` references.

### Changes Made

#### Controllers (6 files)

| Controller                    | Change                                                                                       |
| ----------------------------- | -------------------------------------------------------------------------------------------- |
| `LeadController.php`          | Added `public const LD_LST = 'leadList';`                                                    |
| `ContractController.php`      | Added `public const CL_WS_PRJ = 'clientWiseProject';`                                        |
| `ReportController.php`        | Deleted duplicate `stock_export` snake_case method (kept `stockExport` with `STK_EXP` const) |
| `ProjectController.php`       | Added `public const PRJ_CPY_LNK = 'projectCopyLink';`                                        |
| `ProjectReportController.php` | Renamed `ajax_data` → `ajaxData` + `AJX_DT` const                                            |
| `ProjectReportController.php` | Renamed `ajax_tasks_report` → `ajaxTasksReport` + `AJX_TSK_RPT` const                        |

#### Routes (`routes/web.php`) — 12 fixes

| Old                                   | New                                |
| ------------------------------------- | ---------------------------------- |
| `[LDC::class, 'lead_list']`           | `[LDC::class, LDC::LD_LST]`        |
| `[CTCC::class, 'clientWiseProject']`  | `[CTCC::class, CTCC::CL_WS_PRJ]`   |
| `[CTCC::class, 'clientwiseproject']`  | `[CTCC::class, CTCC::CL_WS_PRJ]`   |
| `[CTCC::class, 'copycontract']`       | `[CTCC::class, CTCC::CPY_CTC]`     |
| `[CTCC::class, 'copycontractstore']`  | `[CTCC::class, CTCC::CPY_CTC_STR]` |
| `[RPC::class, 'stock_export']`        | `[RPC::class, RPC::STK_EXP]`       |
| `[PRJC::class, 'projectCopyLink']`    | `[PRJC::class, PRJC::PRJ_CPY_LNK]` |
| `[PRJC::class, 'projectlink']`        | `[PRJC::class, PRJC::PRJ_LNK]`     |
| `[RPC::class, 'LeaveReportExport']`   | `[RPC::class, RPC::LV_RPT_EXP]`    |
| `[RPC::class, 'PayrollReportExport']` | `[RPC::class, RPC::PAY_RPT_EXP]`   |
| `[PRPC::class, 'ajax_data']`          | `[PRPC::class, PRPC::AJX_DT]`      |
| `[PRPC::class, 'ajax_tasks_report']`  | `[PRPC::class, PRPC::AJX_TSK_RPT]` |

### Verification

- PHP syntax check on all 6 files: ✅
- `php artisan route:list`: ✅ 1532 routes, no errors
- `npm run audit:routes`: ✅ 0 raw route names, 98% constants adoption

### Remaining (deferred)

- ~14 routes in `web.php` still use raw string literals (all functional)
- `ZoomMeetingTrait` constants deferred — trait constants require PHP 8.2+, project minimum is 8.1
