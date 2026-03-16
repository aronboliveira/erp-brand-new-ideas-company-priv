# Known / Remaining Unresolved Issues

> Last updated: 2026-03-15

## RECENTLY RESOLVED (2026-03-15)

### Utility Class Delegation — ✅ RESOLVED 2026-03-15

Extracted 68 methods from `Utility.php` (4,282 → 1,828 lines) into 6 service classes under `app/Services/Utility/`:

- `AccountingService` — chart of accounts, journal, trial balance, balance sheet
- `FileStorageService` — file upload/download, storage settings, S3/Wasabi
- `FinanceBillingService` — invoices, bills, taxes, payments, proposals
- `LocalizationService` — languages, currency, phone formatting, date/time
- `ModelLookupService` — settings lookups, plan checks, model finders
- `NotificationService` — email templates, Twilio SMS, Pusher, notifications

All original method signatures preserved as delegation stubs in `Utility.php`.

### Problems Panel Cleanup — ✅ RESOLVED 2026-03-15

Reduced VS Code Problems Panel from 895+ errors to **0 errors** across all PHP files:

- Removed 30+ unused imports from `Utility.php` (49 intelephense errors → 0)
- Fixed `LocalizationService.php`: removed unused `DB` import, cast `(int)$areaCode`, `(string) rand()` for `str_pad`
- Fixed `FinanceBillingService.php`: removed unused `UC`, `Product`, `Auth` imports
- Fixed `UtilityTest.php`: removed unused `GoogleEvent`, added `@var` annotations, extracted `Storage::disk()` to typed variable
- Fixed `ProductServiceCategoryTest.php`: added `@var` annotations for Mockery casts
- Fixed `ProjectTaskTest.php`, `GeneratedOfferLetterTest.php`: added missing imports/args
- Fixed `ReportController.php`: added `BillsConstants as BC` import
- Fixed `SetSalaryController.php`: added `JsonResponse` import
- Fixed `Proposal.php`: `\Utility::` → `Utility::` (same namespace), removed unused imports
- Suppressed `mysql-schema.sql` false positives via `.vscode/settings.json` file association → `plaintext`

### CalendarService + MockCalendarGateway — ✅ RESOLVED 2026-03-14

Calendar testing infrastructure fully rebuilt:

- Created `CalendarGateway` interface, `GoogleCalendarGateway`, `MockCalendarGateway`
- `CalendarService` refactored to use DI via `App::bound()` / `App::instance()`
- `MockCalendarGateway::configure()` now reads DB settings and sets `Config` values (was no-op)
- All 14 calendar tests in `UtilityTest` rewritten and passing (was: 8 failing, 1 skipped, 1 risky)
- Test baseline: 395/422 passed (93.6%), 0 risky

### IDE Error Fixes — ✅ RESOLVED 2026-03-14

`AllowanceController` missing return type, unused imports across several files, missing `DB` imports — all fixed.

## PREVIOUSLY RESOLVED (2026-03-12)

- **Deal/Lead Infinite Recursion (OOM)** — `__get()` collision with relation names → `$this->getAttributes()['col']`
- **PHP CLI Unlimited Memory** — `memory_limit = -1` → set to 2G + phpunit.xml guard + earlyoom
- **BankTransferPaymentController uploadReceipt** — `Utility::uploadFile()` result extraction

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

[TASKED] ### 3. PHPUnit Unit Tests ~21 Pre-Existing Failures

Accounting/financial test failures (CoA seeding, balance sheet, trial balance). All pre-date current audit. Not regressions. Test baseline: 395/422 passed (93.6%).

---

## PREVIOUSLY RESOLVED (archive — see git history for details)

| #   | Issue                                | Commit     | Status      |
| --- | ------------------------------------ | ---------- | ----------- |
| 3   | HTTP 500 on Non-Existent UUIDs       | `9d2d5fa9` | ✅ RESOLVED |
| 4   | PHPStan Level 5 Errors               | —          | ✅ RESOLVED |
| 6   | Route Pluralization Bug              | `9d2d5fa9` | ✅ RESOLVED |
| 7   | Namespace Collision in route:list    | `9d2d5fa9` | ✅ RESOLVED |
| 8   | HTTP 4xx Error Handler               | `9d2d5fa9` | ✅ RESOLVED |
| 9   | Model Relation Alias Collisions      | `5fbdb617` | ✅ RESOLVED |
| 10  | Login Detail FK Constraint Violation | `b4265c32` | ✅ RESOLVED |
| 11  | Playwright auth.setup.cjs Broken     | `b4265c32` | ✅ RESOLVED |

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
