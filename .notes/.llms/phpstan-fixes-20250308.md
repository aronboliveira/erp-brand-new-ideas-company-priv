# PHPStan Error Fix Session — 2025-03-08

## Overview

Multi-session effort to bring PHPStan Level 3 errors to zero across this Laravel ERP codebase.
Starting count: ~2,287 errors (Phase 1 batch). This session continued Phase 4+ fixes.

## Changes Made This Session

### 1. @property Annotations (59 new + 309 prior = 368 total)

- **Script:** `utils/add_missing_properties.php`
- Scanned `storage/phpstan_results/*.txt` for `App\Models\X::$y` patterns
- Added missing `@property` PHPDoc to 36 model files (59 annotations)
- Type inference based on naming conventions (e.g., `*_id` → `int|null`, amounts → `float|null`)

### 2. Missing Model Relation Aliases (17 methods across 14 files)

Added snake_case relation aliases for existing camelCase relations:

| Model          | Methods Added                                                  |
| -------------- | -------------------------------------------------------------- |
| Support        | `assign_to()`, `created_by()`                                  |
| Bug            | `created_by()`, `project_bug()`                                |
| Job            | `created_by()`                                                 |
| SupportReply   | `users()`                                                      |
| Training       | `branches()`, `types()`                                        |
| Appraisal      | `branches()`, `employees()`                                    |
| CompanyPolicy  | `branches()`                                                   |
| Trainer        | `branches()`                                                   |
| UserCoupon     | `userDetail()`                                                 |
| ChartOfAccount | `sub_type()`                                                   |
| User           | `current_plan()`                                               |
| Termination    | `termination_type()`                                           |
| ProductService | `unit()`                                                       |
| JournalEntry   | `@property mixed $items` (docblock only — column is JSON cast) |

### 3. Caller Bug Fixes

| File                             | Fix                                                                         |
| -------------------------------- | --------------------------------------------------------------------------- |
| ApiController.php:255            | `diffanceToTime` → `differenceToTime`                                       |
| SupportController.php:422        | `markAsRead()` → `markRead()`                                               |
| SystemController.php:202         | `Utility::adminPaymentSettings($req)` → `$this->adminPaymentSettings($req)` |
| ZoomMeetingTrait.php:209         | `Utility::settings(Auth::id())` → `Utility::settingsById(Auth::id())`       |
| GoalTrackingController.php:50,54 | `'branches'` → `'branch'`, `'goal_type'` → `'goalType'`                     |

### 4. Missing Utility Methods (4 added)

Added to `app/Models/utils/Utility.php`:

- `deleteFile(string $path): array` — deletes from configured storage
- `uploadFileGeneric(UploadedFile $file, string $dir, string $base): string` — stores file
- `notifyNewBudget(Budget $budget): void` — email + webhook notification
- `getTaskCalendarArray($tasks): array` — task collection → calendar array

### 5. Static Property Fixes

| Model           | Fix                                                                         |
| --------------- | --------------------------------------------------------------------------- |
| Allowance       | Added `$Allowancetype` alias (case sensitivity)                             |
| Budget          | Added `$frequency` static array with Frequency enum values                  |
| CustomQuestion  | Added `$is_required` alias (snake_case for `$isRequired`)                   |
| FormBuilder     | Added `$fieldTypes` static array                                            |
| Utility.php:206 | Changed `(new Client)->getTable()` → `(new \App\Models\Client)->getTable()` |

### 6. Controller Fixes

| File                                | Fix                                                                 |
| ----------------------------------- | ------------------------------------------------------------------- |
| SystemController:1356,1404          | `View::exists()` → `ViewFacade::exists()` (facade alias resolution) |
| BenefitPaymentController:340        | Cast `(float)$amount` for arithmetic                                |
| ProjectReportController:313         | `task_reportExport` → `TaskReportExport` class name                 |
| EmployeeController:create,edit,show | `$employeesId` renamed to match `compact()` key                     |
| ProjectTaskController:790           | Added `${PJC::COL_PJ_ID} = $projectId;` before compact              |

### 7. Unused Closure Variable Cleanup (110 vars from 11 files)

- **Script:** `utils/clean_closure_vars.php`
- Removed profiling vars (`$method`, `$class`, `$cls`, `$base`, `$meth`, `$func`) from closure `use()` clauses where they were captured but never referenced in the body
- Body-aware detection: only removes vars confirmed unused inside the closure

## Prior Session Fixes Still Active

- MeasuresPerformance trait: opt-in via `PERF_ENABLED`
- Controller.php measureProfile/logExecutionTime: pass-through/no-op
- Authenticate.php, XSS.php: `PERF_ENABLED = true`
- CommissionController, DesignationController: `_authorize()` added
- AppraisalController: `ROute` → `Route` typo
- 309 @property annotations from Phase 4 script
- Config/, Enums/, Middleware/: 0 errors confirmed

## Utility Scripts Created

| Script                             | Purpose                                            |
| ---------------------------------- | -------------------------------------------------- |
| `utils/add_missing_properties.php` | Scans PHPStan results → adds @property annotations |
| `utils/clean_closure_vars.php`     | Removes unused profiling vars from closure use()   |

## Remaining Known Issues

### Low Priority (~423 errors)

- Unused closure `use $method` vars — most are in closures where `$method` IS used (for logging patterns)
- Duplicate array keys in LangsConstants.php, enum files
- Return type mismatches in Export/Import classes

### Medium Priority

- Type narrowing needed for `getUser()`-style union returns (~14 errors)
- Chatify void return used as value (~6 errors)
- Various model property accessors on union types

### Deprecated / Ignorable

- Errors in `_DEPRECATED_Controllers_old/` directory
