# NEXT STEPS

> Last updated: 2026-03-06
> Full resolution history in `.notes/.llms/.history/`. Coding patterns in `.notes/.llms/.guidelines/`.

---

## IMMEDIATE

1. **Run ContentValidationSeeder** — `cd _inc/laravel && php artisan db:seed --class=ContentValidationSeeder` to populate empty catalog tables.
2. **Verify Playwright E2E** — `npm run test:playwright` to confirm auth refresh still works.
3. **Test shared-link password flow** — existing shared links now require password re-entry (base64→bcrypt migration).

---

## DEFERRED (monitoring only)

| Item                                        | Effort  | Notes                                                       |
| ------------------------------------------- | ------- | ----------------------------------------------------------- |
| RoleController Permission scoping           | Trivial | Spatie permissions are global; not a true IDOR              |
| ProjectController::projectLink tenant scope | N/A     | Public endpoint by design — encrypted URL is access control |

## RECENTLY COMPLETED (2026-03-06)

### Intelephense / VS Code Problems Panel — 14 fixes across 12 files

| File                               | Fix                                                                      |
| ---------------------------------- | ------------------------------------------------------------------------ |
| BillController.php                 | Added `as UC` alias to `UsersConstants` import (fixed 37 errors)         |
| ProjectTaskController.php          | Added `as PJC` alias to `ProjectsConstants` import                       |
| ExpenseController.php              | Changed `BillsConstants::COL_BIL_TMP` → `BC::COL_BIL_TMP`                |
| CommissionController.php           | Fixed `$commissiontype` → `$commissionType` (3 locations)                |
| ComissionControllerTest.php        | Fixed `$commissiontype` → `$commissionType`                              |
| NotificationTemplateController.php | Removed wrong `use function` import (same namespace)                     |
| Bill.php (model)                   | Added `public static array $statuses` property                           |
| Job.php (model)                    | Added `public static array $status` property                             |
| DashboardController.php            | Fixed return types for `handleLandingOrInstall` and `buildPipelineStats` |
| XSS.php                            | Fixed `ConsoleOutput` → `SafeConsoleOutput` type hint                    |
| AuthenticatedSessionController.php | Fixed 2 deprecated implicit nullable params                              |
| ProjectStagesTest.php              | Added missing `$expectedCollection` variable + fixed class name          |

---
