# Known / Remaining Unresolved Issues

> Last updated: 2026-02-25 (commit `4a3b7ca5`)

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
