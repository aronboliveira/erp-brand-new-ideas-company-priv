# Super Admin Full Module Access — CLI Commands

## Date: 2025-06-09

## Task

Ensure super admin (SA) users always have full access to all modules the app can mount on the client side.

---

## Syntax Checks (all modified files)

```bash
cd /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel

# New files modified in this session
php -l app/Http/Controllers/Activity/PerformanceTypeController.php
php -l app/Http/Controllers/Activity/DealController.php
php -l app/Services/TaskRequestService.php
php -l app/Http/Controllers/Planning/ProjectController.php

# Files modified in previous session
php -l app/Http/Controllers/Helpers/security.php
php -l app/Http/Controllers/Shapes/FormBuilderController.php
php -l app/Http/Controllers/Activity/SupportController.php
php -l app/Http/Controllers/Planning/ContractTypeController.php
php -l app/Services/BugReportService.php
php -l app/Services/ProjectRequestService.php
php -l app/Services/LeadRequestService.php
php -l app/Http/Controllers/Contact/MessagesController.php
php -l app/Http/Controllers/Planning/ResignationController.php
php -l app/Http/Controllers/Planning/ProjectTaskController.php
php -l app/Http/Controllers/Shapes/DashboardController.php
php -l app/Http/Controllers/Planning/ProjectReportController.php
php -l app/Http/Controllers/Shapes/TimesheetController.php
php -l app/Http/Controllers/Planning/IndicatorController.php
php -l app/Http/Controllers/Ssr/DocumentUploadController.php
```

Result: **All 19 files — No syntax errors detected**

---

## PHPStan Static Analysis

```bash
cd /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel

vendor/bin/phpstan analyse --memory-limit=2G --no-progress
```

Result: **7 pre-existing errors (PosController + AuthenticatedSessionController), 0 errors from modified files**

### Verification (filter for modified files only)

```bash
vendor/bin/phpstan analyse --memory-limit=2G --no-progress --error-format=raw 2>&1 | \
  grep -E 'DealController|PerformanceType|TaskRequest|ProjectController|security\.php|FormBuilder|Support|ContractType|BugReport|ProjectRequest|LeadRequest|Messages|Resignation|ProjectTask|Dashboard|ProjectReport|Timesheet|Indicator|DocumentUpload'
```

Result: **No output (zero errors in modified files)**

---

## PHPUnit Tests

```bash
cd /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel

vendor/bin/phpunit --no-coverage --filter="DealControllerTest|ProjectControllerTest|ProjectTaskControllerTest|ProjectReportControllerTest|PermissionControllerTest|AuthenticatedSessionControllerTest"
```

Result: **828 tests, 976 assertions, 0 failures** (51 pre-existing warnings about class name mismatches)

---

## Jest Tests

```bash
cd /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel

npx jest --passWithNoTests
```

Result: **8 suites, 87 tests, 0 failures**

---

## Modified Files Summary

| #   | File                                                          | Changes                                                                                                           |
| --- | ------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------- |
| 1   | `app/Http/Controllers/Helpers/security.php`                   | SA bypass in `permissionRequiredCustom()`                                                                         |
| 2   | `app/Http/Controllers/Shapes/FormBuilderController.php`       | SA access in `formFieldBind()` + `bindStore()`                                                                    |
| 3   | `app/Http/Controllers/Activity/SupportController.php`         | SA sees all tickets in `index()`                                                                                  |
| 4   | `app/Http/Controllers/Planning/ContractTypeController.php`    | SA sees all contract types in `index()`                                                                           |
| 5   | `app/Services/BugReportService.php`                           | SA in `userCanSeeAllBugs()`                                                                                       |
| 6   | `app/Services/ProjectRequestService.php`                      | SA in `getProjectStatus()`, `getUserProjects()`, `shouldRestrictTasksToAssignee()`, `projectStageChartData()`     |
| 7   | `app/Services/LeadRequestService.php`                         | SA in `getLeadsForStage()`, `getUserLeads()`, `userCanAccessLead()`, `userCanEditLead()`, `getLeadCountByStage()` |
| 8   | `app/Http/Controllers/Contact/MessagesController.php`         | SA unblocked from messaging; member list includes SA                                                              |
| 9   | `app/Http/Controllers/Planning/ResignationController.php`     | SA sees all employees in `create()`/`edit()`                                                                      |
| 10  | `app/Http/Controllers/Planning/ProjectTaskController.php`     | SA in calendar views, bugs views, task filtering                                                                  |
| 11  | `app/Http/Controllers/Shapes/DashboardController.php`         | SA skips employee block in `hrmDashboardIndex()`                                                                  |
| 12  | `app/Http/Controllers/Planning/ProjectReportController.php`   | SA in `statusList` + `buildProjectQuery()`                                                                        |
| 13  | `app/Http/Controllers/Shapes/TimesheetController.php`         | SA gets all projects                                                                                              |
| 14  | `app/Http/Controllers/Planning/IndicatorController.php`       | SA treated like company for `created_user`                                                                        |
| 15  | `app/Http/Controllers/Ssr/DocumentUploadController.php`       | SA sees all documents like CPN                                                                                    |
| 16  | `app/Http/Controllers/Planning/ProjectController.php`         | SA in `store()`, `show()`, and list operations                                                                    |
| 17  | `app/Http/Controllers/Activity/PerformanceTypeController.php` | SA bypass in `authorizeCompany()`                                                                                 |
| 18  | `app/Http/Controllers/Activity/DealController.php`            | SA sees all deals in `index()`, `dealList()`, `store()`                                                           |
| 19  | `app/Services/TaskRequestService.php`                         | SA unscoped chart data in `getChartData()`                                                                        |
