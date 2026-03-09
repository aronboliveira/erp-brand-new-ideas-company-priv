# Batch 25 — HRM, PM, CRM & POS Report Routes

**Date**: 2026-02-10
**Branch**: `agent`
**Code Commit**: `b0efc795`
**Previous Batch**: Batch 24 (`3237f7be` code + `84066243` docs)

---

## Summary

Fixed **13 bugs** (8 original + 5 cascading) across **8 files** affecting HRM, Project Management, CRM, and POS report routes. All 23 tested routes now return non-500 responses.

## Files Modified

| #   | File                                                        | Changes                                           |
| --- | ----------------------------------------------------------- | ------------------------------------------------- |
| 1   | `app/Http/Controllers/Activity/ReportController.php`        | 3 fixes: view name, 2× type hints                 |
| 2   | `app/Http/Controllers/Planning/ProjectReportController.php` | 2 fixes: selectRaw, class name                    |
| 3   | `app/Http/Controllers/Planning/ProjectTaskController.php`   | 2 fixes: view names, default param                |
| 4   | `resources/views/reports/daily_pos.blade.php`               | 3 fixes: ~35 alias swaps, defaults, inverse fixes |
| 5   | `resources/views/reports/monthly_pos.blade.php`             | 1 fix: variable defaults                          |
| 6   | `resources/views/project_reports/show.blade.php`            | 2 fixes: variable name, constant alias            |
| 7   | `resources/views/projects/all_bug_list_view.blade.php`      | 1 fix: route constant                             |
| 8   | `resources/views/projects/all_bug_grid_view.blade.php`      | 1 fix: route name                                 |

## Bugs Fixed

### Original Bugs (8)

#### Bug 1 — daily_pos.blade.php: ~35 VW→VC CSS class aliases

- **Root Cause**: Blade used `VW::RPT_TX_GR`, `VW::CD_POS`, etc. for CSS classes, but these are view/route path constants, not CSS class constants.
- **Fix**: Batch-replaced ~35 occurrences of `VW::` → `VC::` for CSS-related constants.

#### Bug 2 — daily_pos.blade.php: Undefined $warehouse/$customer

- **Root Cause**: Controller passes `$warehouses` and `$customers`, but blade expects `$warehouse` and `$customer`.
- **Fix**: Added `$warehouse ??= $warehouses ?? []; $customer ??= $customers ?? [];` at top.

#### Bug 3 — monthly_pos.blade.php: Undefined $warehouse/$customer

- **Root Cause**: Same as Bug 2.
- **Fix**: Same approach — added fallback defaults.

#### Bug 4 — ProjectTaskController.php L645: camelCase view names

- **Root Cause**: Controller referenced `'projects.allBugListView'` and `'projects.allBugGridView'` but blade files use snake_case naming.
- **Fix**: Changed to `'projects.all_bug_list_view'` and `'projects.all_bug_grid_view'`.

#### Bug 5 — ReportController.php L3405: leaveShow view name

- **Root Cause**: View name was `VW::RPT . '.leaveShow'` but blade file is `leave_show.blade.php`.
- **Fix**: Changed to `VW::RPT . '.leave_show'`.

#### Bug 6 — ProjectReportController.php L196-203: pluck without selectRaw

- **Root Cause**: `pluck('count', ...)` called on query without selecting the `count` column.
- **Fix**: Added `->selectRaw('count(*) as count, ...')` before both `pluck()` calls.

#### Bug 7 — project_reports/show.blade.php: Variable and constant mismatches

- **Root Cause**: Blade used `$last_task` (snake_case) but controller passes `$lastTask` (camelCase). Also used `DB::COL_TABLE_CREATOR` but `DB` is PHP's PDO class, not `DatabaseConstants`.
- **Fix**: Changed `$last_task` → `$lastTask` (2 spots) and `DB::` → `DC::` (2 spots).

#### Bug 8 — all_bug_list_view.blade.php L40: Wrong route constant

- **Root Cause**: Used `ViewsConstants::BUG . '.view'` → `'bugs.view'`, but route is registered as `'projects.bugs.view'`.
- **Fix**: Changed to `ViewsConstants::PRJ_BUG . '.view'`.

### Cascading Bugs (5, discovered during re-verification)

#### Bug 9 — daily_pos.blade.php: 3 inverse VC→VW fixes

- **Root Cause**: The batch sed replacement (Bug 1) incorrectly converted some route/view constants from `VW::` to `VC::`.
- **Fix**: Reverted `VC::RPT` → `VW::RPT` at L27, L43 and `VC::POS` → `VW::POS` at L57.

#### Bug 10 — ProjectTaskController.php L606: Missing default for optional $view

- **Root Cause**: Route defines `{view?}` (optional), but method signature had `string $view` without default.
- **Fix**: Changed to `string $view = 'list'`.

#### Bug 11 — ReportController.php L3367: int type hint for UUID employee_id/year

- **Root Cause**: `_buildEmployeeLeaveView(int $employee_id, ..., int $year, ...)` but receives UUID strings from routes.
- **Fix**: Changed to `int|string $employee_id` and `int|string $year`.

#### Bug 12 — ReportController.php L711: int type hint for UUID branch/department

- **Root Cause**: `exportCsv(string $filter_month, int $branch, int $department)` but receives UUID strings.
- **Fix**: Changed to `int|string $branch, int|string $department`.

#### Bug 13 — ProjectReportController.php L375: Wrong export class name

- **Root Cause**: `new \App\Exports\task_reportExport($id)` but class is `TaskReportExport` (PascalCase).
- **Fix**: Changed to `new \App\Exports\TaskReportExport($id)`.

#### Bug 14 — all_bug_grid_view.blade.php L112: Wrong kanban route name

- **Root Cause**: Used `route("bug.kanban.order")` but route is registered as `"projects.bugs.kanban.order"`.
- **Fix**: Changed to `route("projects.bugs.kanban.order")`.

## Routes Verified (23 total)

| Route                                   | Status | Notes                                             |
| --------------------------------------- | ------ | ------------------------------------------------- |
| `reports-payroll`                       | 200 ✅ |                                                   |
| `reports-leave`                         | 200 ✅ |                                                   |
| `reports/leave`                         | 200 ✅ |                                                   |
| `reports-monthly-attendance`            | 200 ✅ |                                                   |
| `reports/attendances/01/0/0`            | 200 ✅ |                                                   |
| `reports-deal`                          | 200 ✅ |                                                   |
| `reports-lead`                          | 200 ✅ |                                                   |
| `reports-daily-pos`                     | 200 ✅ |                                                   |
| `reports-monthly-pos`                   | 200 ✅ |                                                   |
| `reports-pos-vs-purchase`               | 200 ✅ |                                                   |
| `reports-warehouse`                     | 200 ✅ |                                                   |
| `project_reports`                       | 200 ✅ |                                                   |
| `project_reports/create`                | 302 ⬛ | Stub method redirects to index                    |
| `bugs_reports`                          | 200 ✅ |                                                   |
| `bugs_reports/list`                     | 200 ✅ |                                                   |
| `bugs_reports/grid`                     | 200 ✅ |                                                   |
| `project_reports/{id}`                  | 302 ⬛ | Auth scope — SA can't see projects by other users |
| `project_reports/{id}/edit`             | 302 ⬛ | Stub method redirects to index                    |
| `project_reports/exports/{id}`          | 200 ✅ |                                                   |
| `employees/{id}/leaves/all/all/01/2026` | 200 ✅ |                                                   |
| `reports/attendances/01/{employee}/0`   | 200 ✅ |                                                   |
| `leaves/export`                         | 200 ✅ |                                                   |
| `reports/payrolls/export`               | 200 ✅ |                                                   |

## Edge Cases Tested

- **Grid view toggle**: List↔Grid view switching works correctly
- **UUID parameters**: Employee/branch/department IDs as UUIDs handled properly
- **Boundary months**: Month 00 and 13 handled gracefully (no 500)
- **Nonexistent view params**: `bugs_reports/nonexistent` falls back to list view
- **XSS in params**: Script tags in route params properly escaped

## Non-Bugs Confirmed

1. **project_reports/{id} → 302**: `buildProjectQueryForShow()` scopes by `created_by = $user->id`. SA user (`a3e8f4b2...`) didn't create the test project (`712ce293...`, created by `fc088c94...`). This is intentional auth scoping.
2. **project_reports/create → 302**: Stub `create()` method that immediately redirects to index.
3. **project_reports/{id}/edit → 302**: Stub `edit()` method that immediately redirects to index.

## Technical Notes

- **BladeImportsServiceProvider aliases**: `VC` = CSS classes, `VW` = view/route paths, `DC` = database constants
- **Key constants**: `VW::BUG = 'bugs'`, `VW::PRJ_BUG = 'projects.bugs'`, `VW::RPT = 'reports'`
- **UUID columns**: This ERP uses UUID primary keys; PHP `int` type hints fail on route model binding
