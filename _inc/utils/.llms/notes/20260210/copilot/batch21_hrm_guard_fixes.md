# Batch 21 — HRM Routes: Guard Pattern Fixes + SA Bypass + View Fixes

**Commit:** `82551bbd`  
**Date:** 2026-02-10  
**Branch:** `agent`  
**Files changed:** 21

## Summary

Fixed all 44 HRM index routes to render without server errors.  
Two distinct guard pattern bugs were identified and fixed across the entire controller codebase.

## Root Cause: Two Guard Patterns

### Pattern A — ChecksPermissions trait `guard()`

- Returns `true` on success, `RedirectResponse|JsonResponse` on denial
- Bug: `if ($r = self::guard(...)) return $r;` — on success, `$r = true` (truthy), so the method returns `true` instead of continuing
- Fix: `if (($c = self::guard(...)) !== true) return $c;`
- **49+ call-sites** across 11 controllers

### Pattern B — Local `guard()` / `authorizePerm()` / `authorizeOwnership()`

- Returns `null` on success, `RedirectResponse|JsonResponse` on denial
- Bug: Some were incorrectly changed to `!== true` (Pattern A fix), making them always pass through
- Fix: `if ($c = self::guard(...)) return $c;` (simple truthiness)
- Also lacked SA bypass — added `PermissionsConstants::SA` type check
- **7 controllers** with local auth methods

## Controllers Modified

### Pattern A (ChecksPermissions trait guard — `!== true`):

| Controller                   | Call-sites fixed |
| ---------------------------- | ---------------- |
| LeaveController              | 9                |
| ReportController             | 8                |
| JobCategoryController        | 7                |
| TimeTrackerController        | 5                |
| JournalEntryController       | 3                |
| EmployeeController           | 2                |
| ProjectTaskController        | 2                |
| EmployeeAttendanceController | 1                |
| TimesheetController          | 1                |
| PurchaseController           | 1                |
| BillController               | 1                |

### Pattern B (Local guard + SA bypass):

| Controller                | Method                                             | SA bypass added |
| ------------------------- | -------------------------------------------------- | --------------- |
| DocumentController        | `guard()` (6 calls)                                | ✅              |
| LabelController           | `guard()` (6 calls)                                | ✅              |
| AllowanceOptionController | `authorizePerm()` (6) + `authorizeOwnership()` (3) | ✅              |
| OtherPaymentController    | `authorizePerm()`                                  | ✅              |
| BenefitPaymentController  | `authorizePerm()`                                  | ✅              |
| CommissionController      | `authorizeOwnership()` + `authorizeRequest()`      | ✅              |
| BankTransferController    | `authorizeOwnership()`                             | ✅              |

## View Fixes

### `resources/views/warnings/index.blade.php`

- `warning.destroy` → `warnings.destroy` (plural route name)
- Added `$lang = Utility::fetchUserLang();` to @php block
- Made `$user->dateFormat()` null-safe with isset check

### `app/Http/Controllers/Info/WarningController.php`

- Changed `compact('warnings')` → `compact('warnings', 'user')`

## Constants Added

### `ViewClassNamesConstants`

- `TI_DRP = self::TD_DOTV` — dropdown toggle icon alias (`ti ti-dots-vertical`)
- `TI_ADJ` — adjustments icon (`ti ti-adjustments-horizontal`)

## Verification Results (44 HRM routes)

All routes verified rendering with proper tables/cards, zero server errors:

```
employees=6, clients=Cards:4, departments=10, designations=6, branches=10,
allowances=10, allowance_options=1, deduction_options=2, saturation_deductions=2,
commissions=2, appraisals=2, awards=3, award_types=3, complaints=3, competencies=2,
leave=2, leave_types=2, holidays=2, employee_attendances=2, payslips=3,
set_salaries=6, overtimes=6, loans=6, loan_options=2, indicators=2,
events=2, meetings=2, zoom_meetings=2, documents=2, document_uploads=2,
supports=2, trainings=1, training_types=1, warnings=6, announcement=1,
promotions=3, resignations=4, terminations=4, terminationtype=30, transfers=3,
travels=3, account_assets=2, warehouse_transfers=2, labels=2
```

- `clients` uses card-based layout (Cards=4, no `<tr>` elements — by design)
- 4 routes with TRs=1 (header only, no data): `allowance_options`, `trainings`, `training_types`, `announcement` — tables render correctly, just empty data

## Key Takeaways

1. **Always check which guard pattern a controller uses** before applying fixes — trait guard (`true`) vs local guard (`null`)
2. **SA bypass pattern:** `if (strtolower((string)($user?->type ?? '')) === PermissionsConstants::SA) { return null; }`
3. **Route naming convention:** Routes use plural names (`warnings.destroy`), some blade views had singular references
