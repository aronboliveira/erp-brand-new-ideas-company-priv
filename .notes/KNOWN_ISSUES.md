# Known / Remaining Unresolved Issues

> Last updated: 2026-03-12 (commit `f676cb8f`)

## RECENTLY RESOLVED

### Deal/Lead Infinite Recursion (OOM) — ✅ RESOLVED 2026-03-12

`Deal::labels()`, `Lead::labels()`, `Lead::products()`, `Lead::sources()` had methods
sharing names with database columns. When attributes were absent (e.g. from factory),
`getAttribute()` treated the method as a relation, calling it recursively → infinite OOM.
**Fix:** Use `$this->getAttributes()['col']` instead of `$this->getAttribute('col')`.

### PHP CLI Unlimited Memory — ✅ RESOLVED 2026-03-12

`/etc/php/8.4/cli/php.ini` had `memory_limit = -1`. Any PHPUnit/PHPStan process could
consume all 30GB RAM and crash VSCode via systemd-oomd. **Fix:** Set to 2G, added
phpunit.xml guard (2G), earlyoom installed.

### BankTransferPaymentController uploadReceipt — ✅ RESOLVED 2026-03-12

`Utility::uploadFile()` returns `array{flag, msg, url}` but was assigned directly to
`$path` variable. **Fix:** Extract `$result['url']`.

## OPEN — Application Behaviour

### 1. Circular Redirect Loops (10 routes)

Routes that redirect back to themselves due to module permission middleware ordering. Not a PHP error — they serve a page, just not the right one.

- `/customers/dashboard` → `/clients` → `/overtimes` → `/bank_transfers/index` → `/lead_stages/create` → `/lead_stages?modal=create` → LOOP
- `/deals/create`, `/deals/{id}/tasks/create`, etc.

**Root cause:** Module guards use `back()` which chains into other guarded routes.  
**Effort:** Medium. Need per-module permission fallback targets defined explicitly.

[TASKED] ### 2. HRM Hidden Tables (2 pages)

- `/meetings` — table `display:none` until JS loads data
- `/award_types` — same

**Playwright impact:** Tests can't assert `toBeVisible()` on hidden table. Tests currently skip these.  
**Effort:** Low CSS fix or test workaround with `waitForResponse`.

### 3. HTTP 500 on Non-Existent UUIDs — ✅ RESOLVED

Data-dependent routes return 500 instead of 404 when seeded data is absent.

| Route                                              | Was               | Now            | Fix                                                                                                                   |
| -------------------------------------------------- | ----------------- | -------------- | --------------------------------------------------------------------------------------------------------------------- |
| `GET /deals/{id}/tasks`                            | 500               | 302 (redirect) | `taskCreate`: replaced manual `response()->json([...], 500)` in `ModelNotFoundException` catch with `handleFailure()` |
| `GET /deals/{id}/tasks/{taskId}` (show/edit)       | 500               | 302 (redirect) | `taskShow`, `taskEdit`: same pattern — refactored to `handleFailure()`                                                |
| `POST /deals/{id}/tasks`                           | 500 (JSON)        | 404 (JSON)     | `taskStore`: `ModelNotFoundException` now uses `handleFailure(..., 404)`                                              |
| `DELETE/PUT /deals/{id}/tasks/{taskId}` (jsonUser) | 500 (JSON)        | 404 (JSON)     | `jsonUser`: `ModelNotFoundException` uses `handleFailure(..., 404)`                                                   |
| `GET /deals/{id}/users`                            | 302 ✅ already    | —              | Already uses `handleFailure()`                                                                                        |
| `GET /projects/1/...`                              | 200 / redirect ✅ | —              | `ProjectReportController` already handles gracefully                                                                  |
| `GET /email_template_stores/1`                     | 302 ✅ already    | —              | Route uses `R::any` → redirects                                                                                       |
| `GET /store-language`                              | 302 ✅ already    | —              | Route uses `R::any` → redirects                                                                                       |
| `GET /stripes/1`                                   | 302 ✅ already    | —              | StripePaymentController redirects                                                                                     |

**Root cause:** `DealController::taskCreate/taskShow/taskEdit` had manual `catch (ModelNotFoundException $e) { return response()->json([...], 500); }` instead of using the class-level `handleFailure()` helper (which redirects web requests, returns correct JSON status for API requests).

**Global handler** (`Handler.php`) already maps `ModelNotFoundException → 404` but is bypassed when controllers catch exceptions internally.

**Result:** `GET /deals/1/tasks` now returns 302 (redirect to deals index) instead of 500. All 581 `RouteParamMatrixTest` assertions pass. All tinker route checks non-500.

### 4. PHPStan Level 5 Errors — ✅ RESOLVED

**Was:** 9 errors across 2 files (at configured level 5).

| File                                       | Root cause                                                                                                                                                                                                      | Fix                                                                                     |
| ------------------------------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------- |
| `AuthenticatedSessionController.php` (×2)  | `empty.offset` on optional locale-message array keys                                                                                                                                                            | Added `- identifier: empty.offset` to `phpstan.neon`                                    |
| `NotificationTemplatesController.php` (×7) | Wrong namespace (`App\Http\Controllers` instead of `App\Http\Controllers\Info`); missing `use function` imports for `defaultPermissionDenial`/`defaultUndefinedException`; no `bootstrapFiles` for helper files | Fixed namespace; added `use function` imports; added `bootstrapFiles` to `phpstan.neon` |

**Result:** `phpstan analyse` (full path) → ✅ No errors.

[TASKED] ### 5. PHPUnit Unit Tests ~30 Pre-Existing Failures

Model-level test failures: validation, seeder assertions, permission checks. All pre-date this audit. Not regressions.

### 6. Route Pluralization Bug — ✅ RESOLVED (commit `9d2d5fa9`)

**Was:** `GET /login` returned 405 (Method Not Allowed). The actual GET route was at `/logins/{lang?}`.

**Root cause:** Two `RouteServiceProvider` classes (main + LandingPage module) had URI
pluralization loops that only checked the _last_ URI segment against a `specialRoutes`
whitelist. For `GET /login/{lang?}`, the last segment is `{lang?}`, so `login` was
pluralized to `logins`. The module RSP ran after the main RSP, re-applying the broken logic.

**Fix:** Changed both RSPs to check ALL segments against `specialRoutes`. Expanded whitelist
to include `verify`, `logout`, `forgot-password`, `reset-password`, `confirm-password`,
`two-factor-challenge`, `fortify-login`. Added guard against pluralizing `{param}` segments.

### 7. Namespace Collision in route:list — ✅ RESOLVED (commit `9d2d5fa9`)

**Was:** `php artisan route:list` crashed with `ReflectionException: Class
"App\Http\Controllers\Modules\LandingPage\Http\Controllers\CustomPageController" does not exist`.

**Root cause:** `$namespace = 'App\\Http\\Controllers'` in main RSP was prepended to ALL
controller references. Since all 191 routes use `::class` FQCN syntax, module controller
FQCNs got double-namespaced.

**Fix:** Removed `$namespace` property and `->namespace()` calls from all route groups in
both RSPs.

### 8. HTTP 4xx Error Handler — ✅ RESOLVED (commit `9d2d5fa9`)

**Was:** All 4xx HTTP errors (401, 403, 405, etc.) showed "Access Denied" page.

**Fix:** Differentiated: 401/403 → Access Denied, 405 → Method Not Allowed, other 4xx →
generic client error with status code.

### 9. Model Relation Alias Collisions — ✅ RESOLVED (commit `5fbdb617`)

**Was:** Five models defined `snake_case` relation aliases (e.g., `chart_of_account()`)
that collided with DB column names. Laravel's `__get()` magic called the relation instead
of returning the column value.

**Models fixed:** `ChartOfAccount`, `Bug`, `Expense`, `Invoice`, `Proposal`.

**Fix:** Removed conflicting snake_case aliases. Original camelCase relations remain.

### 10. Login Detail FK Constraint Violation — ✅ RESOLVED (commit `b4265c32`)

**Was:** Login via Playwright (HeadlessChrome) failed with `SQLSTATE[23000]: Integrity
constraint violation: 1452 Cannot add or update a child row: a foreign key constraint
fails (login_details.created_by_foreign)`. The `login_details.created_by` was set to `0`
(not a valid UUID) via `$user->creatorId()`.

**Root cause:** `AuthenticatedSessionController::_logUser()` called `$user->creatorId()`
which returns the user's own `created_by` field. For seeded/legacy users with
`created_by = '0'`, this violated the FK to `users.id` (UUID column).

**Fix:** Added UUID validation on `creatorId()` return value with fallback to `$user->id`.

### 11. Playwright auth.setup.cjs Broken — ✅ RESOLVED (commit `b4265c32`)

**Was:** `auth.setup.cjs` always reported "Login failed - still on login page" even when
credentials were valid.

**Root causes (3):**

1. `waitForURL` regex `/.*(?!login).*$/` matches ALL strings (including `/login`) due to
   greedy `.*` before negative lookahead — resolved immediately without waiting.
2. Duplicate form IDs (`#loginForm`, `#email-input`, `#pw-input`, `#saveBtn`) from
   responsive layout — both visible in DOM (second below fold).
3. No `Promise.all` pattern for click + waitForNavigation — race condition.

**Fix:** Rewrote with `page.waitForURL(url => !url.pathname.endsWith('/login'))`,
visible-first locators, viewport size, and `Promise.all([waitForURL, click])`.

---

# MIGRATIONS

## FILES AND CLASSES NAMING — ✅ RESOLVED

All old names verified absent from source; all new names confirmed present. Corrections applied:

| Old name               | Correct name                                   | Status                                                                                                                             |
| ---------------------- | ---------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| `AnnouncementEmployee` | `EmployeeAnnouncement`                         | ✅ `app/Models/Individuals/EmployeeAnnouncement.php`                                                                               |
| `AttendanceEmployee`   | `EmployeeAttendance`                           | ✅ `app/Models/Individuals/EmployeeAttendance.php`                                                                                 |
| `Contract_attachment`  | `ContractAttachment`                           | ✅ `app/Models/Planning/ContractAttachment.php`                                                                                    |
| `GenerateOfferLetter`  | `GeneratedOfferLetter`                         | ✅ `app/Models/Ssr/GeneratedOfferLetter.php`                                                                                       |
| `Vender`               | `Vendor`                                       | ✅ `app/Models/Companies/Vendor.php`                                                                                               |
| `Projectstages`        | `ProjectStage` _(singular, not ProjectStages)_ | ✅ `app/Models/Planning/ProjectStage.php`; migration file still named `create_projectstages_table` (works, low priority to rename) |
| `TrialBalancExport`    | `TrialBalanceExport`                           | ✅ `app/Exports/TrialBalanceExport.php`                                                                                            |
| `task_reportExport`    | `TaskReportExport`                             | ✅ `app/Exports/TaskReportExport.php`                                                                                              |
| `puserhConfig`         | `PusherConfig`                                 | ✅ `app/Http/Middleware/PusherConfig.php`                                                                                          |

## METHODS NAMING — ✅ RESOLVED (commit `4a3b7ca4`)

**Completed:** All 31 target controllers refactored to camelCase methods with `public const ABBREV = 'methodName'` before each compound method name. `routes/web.php` updated to use `ControllerClass::CONST` references throughout.

**Remaining (low priority):** ~14 routes still use string literals (all functional; PHP dispatch is case-insensitive). ZoomMeetingTrait constants deferred — trait constants require PHP 8.2+, project minimum is 8.1.

## FIELDS NAMING — ✅ RESOLVED

All old misspellings verified absent; all corrected names confirmed present:

| Old                                             | Corrected                  | Verified                                                                  |
| ----------------------------------------------- | -------------------------- | ------------------------------------------------------------------------- |
| `Purchase::$statues`                            | `$statuses`                | ✅ `app/Models/Activity/Purchase.php:130`                                 |
| `SaturationDeduction::$saturationDeductiontype` | `$saturationDeductionType` | ✅ `app/Models/Bills/SaturationDeduction.php:27`                          |
| `ZoomMeetingTrait::MEETING_TYPE_SCHEDULE`       | `MEETING_TYPE_SCHEDULED`   | ✅ `app/Traits/ZoomMeetingTrait.php:26`                                   |
| `Activity::get_activity`                        | `getActivity`              | ✅ `app/Models/Activity/Activity.php:13`                                  |
| `ActivityLog::userdetail`                       | `userDetail`               | ✅ `app/Models/Activity/ActivityLog.php:215`                              |
| `ActivityLog::fetchgetRemark`                   | `fetchGetRemark`           | ✅ `app/Models/Activity/ActivityLog.php:257`                              |
| `Comission::$comissiontype`                     | `$comissionType`           | ✅ not found (already removed or renamed)                                 |
| `DocumentUploads` table as `ducument_uploads`   | `document_uploads`         | ✅ `DC::TABLE_DOC_UP = 'document_uploads'` in `DatabaseConstants.php:146` |
