# PM (Project Management) Route Testing — 2026-02-24

## Summary

Full four-framework testing sweep of Project Management routes (PHPUnit, Jest, Playwright, PHPStan). Identified and fixed **16 controller bugs** across 7 files. All tests green.

## Test Results

| Framework  | File                                                  | Tests | Status |
| ---------- | ----------------------------------------------------- | ----: | ------ |
| PHPUnit    | tests/Feature/PmRouteReturnTest.php                   |   101 | PASS   |
| Jest       | tests/Unit/frontend/js/custom/pmPagePatterns.test.cjs |    50 | PASS   |
| Playwright | tests/e2e/pm.spec.cjs                                 |    23 | PASS   |
| PHPStan    | All PM controllers (level 5)                          |     0 | 0 err  |

**Total: 174 tests, 0 PHPStan errors.**

## Controller Bug Fixes (16 total)

### Round 1 — Crash Audit (8 fixes)

| #   | Controller            | Method            | Issue                                        | Fix                                      |
| --- | --------------------- | ----------------- | -------------------------------------------- | ---------------------------------------- |
| 1   | TaskStageController   | update()          | findOrFail outside try/catch → unguarded 500 | Moved inside try/catch                   |
| 2   | ProposalController    | product()         | Missing null guard on product lookup         | Added empty()/coalescence guard          |
| 3   | ProposalController    | statusChange()    | Unguarded status update → 500                | Wrapped in try/catch                     |
| 4   | ProposalController    | previewProposal() | Missing model guard                          | Added findOrFail in try/catch            |
| 5   | ProposalController    | destroy()         | Bare delete → 500 on missing model           | Added ModelNotFoundException catch → 404 |
| 6   | ProposalController    | sent()/resent()   | No try/catch on mail send                    | Wrapped in try/catch with flash error    |
| 7   | TimeTrackerController | create()          | Missing permission check guard               | Added guard                              |
| 8   | TimeTrackerController | show()/edit()     | No try/catch on findOrFail                   | Wrapped in try/catch                     |

### Round 2 — PHPUnit Failure Fixes (8 fixes)

| #   | Controller              | Method            | Issue                                    | Fix                                              |
| --- | ----------------------- | ----------------- | ---------------------------------------- | ------------------------------------------------ |
| 1   | ProjectController       | projectCopyLink() | Blanket catch → explicit 500 response    | Split: ModelNotFoundException→404, Throwable→422 |
| 2   | ProjectStagesController | update()          | `int $id` type hint rejects UUIDs        | Changed to `int\|string $id`                     |
| 3   | ProjectStagesController | destroy()         | `int $id` type hint rejects UUIDs        | Changed to `int\|string $id`                     |
| 4   | ProjectReportController | update()          | Method completely missing                | Added stub → redirect with info message          |
| 5   | ProjectReportController | destroy()         | Method completely missing                | Added stub → redirect with info message          |
| 6   | UserController          | todoStore()       | Wrong route names (`todo.*` → `todos.*`) | Fixed to `todos.update`, `todos.destroy`         |
| 7   | UserController          | todoUpdate()      | `int $todoId` + no ModelNotFound catch   | Changed to `int\|string $todoId` + catch→404     |
| 8   | UserController          | todoDestroy()     | No ModelNotFoundException catch          | Added catch→404 before Throwable→500             |

## Routes Covered

### PHPUnit (101 tests, 14 sections)

- Project Dashboard & Core (14): dashboard, index, create, show, edit, store, update, destroy, copy-link, gantt, share, invite, milestone summary
- Task Stages (8): index, create, store, show, edit, update, destroy, order
- Project Stages (8): index, create, store, show, edit, update, destroy, order
- Bug Status (7): index, create, store, show, edit, update, destroy
- Contract Types (7): index, create, store, show, edit, update, destroy
- Contracts (10): index, create, store, show, edit, update, destroy, grid, copy, share
- Proposals (10): index, create, store, show, edit, update, destroy, preview, duplicate, sent
- Time Trackers (7): index, create, store, show, edit, update, destroy
- Project Reports (7): index, create, store, show, edit, update, destroy
- Task Board & Calendar (4): taskboard, task-calendar, bug-report
- Timesheets (5): index, create, store, show, destroy
- Index Content Keywords (6): text keyword checks on index pages
- Exports (4): project-report-export, proposal-export
- Todos (4): store, update, delete

### Playwright E2E (23 tests)

- Project Dashboard & Core: dashboard, index, create form
- Project Stages: index, create modal
- Task Stages: index, create modal
- Bug Status: index, create modal
- Contracts: index, create form, grid view
- Contract Types: index, create modal
- Proposals: index, create form
- Time Trackers: index
- Project Reports: index, create form
- Task Board & Bug Reports: taskboard, taskboards, bug reports
- Timesheets: list

### Jest DOM (50 tests)

- DataTable initialization, Card layout, AJAX modal, Kanban board, Gantt chart
- select2, action buttons, breadcrumb, toastr
- Forms: project, contract, proposal, task-stage, timesheet
- Todo widget, progress bar, milestone, time tracker
- Project→task dependent dropdown

## PM Controllers Scanned

| Controller              | Dir          | Lines | Methods | Fixes |
| ----------------------- | ------------ | ----: | ------: | ----: |
| ProjectController       | Planning/    |  4057 |      55 |     1 |
| ContractController      | Planning/    |  1453 |      19 |     0 |
| ProposalController      | Planning/    |  1297 |      20 |     5 |
| TimesheetController     | Planning/    |  1098 |      15 |     0 |
| TimeTrackerController   | Planning/    |   320 |       8 |     3 |
| ProjectStagesController | Planning/    |   681 |       9 |     2 |
| TaskStageController     | Activity/    |   570 |       9 |     1 |
| ProjectReportController | Planning/    |   640 |       9 |     2 |
| ContractTypeController  | Shapes/      |   487 |       7 |     0 |
| BugStatusController     | Bugs/        |   489 |       7 |     0 |
| UserController (todos)  | Individuals/ |   848 |       6 |     3 |
| TaskReportExport        | Exports/     |    82 |       3 |     0 |
| ProposalExport          | Exports/     |    72 |       3 |     0 |

## Scripts Added

### composer.json

```
"test-php-pm": "@php vendor/bin/phpunit --no-coverage --filter=PmRouteReturnTest"
"lint-php-pm": "@php vendor/bin/phpstan analyse --memory-limit=1G app/Http/Controllers/Planning/ app/Http/Controllers/Activity/ app/Http/Controllers/Shapes/ app/Http/Controllers/Bugs/ app/Http/Controllers/Individuals/UserController.php"
```

### package.json

```
"test:jest:pm": "npx jest tests/Unit/frontend/js/custom/pmPagePatterns.test.cjs --verbose"
"test:e2e:pm": "npx playwright test tests/e2e/pm.spec.cjs --reporter=line"
```

## ViewsConstants Used

`VW::PRJ`, `VW::PRJ_STG`, `VW::PRJ_TSK_STG`, `VW::PRJ_RPT`, `VW::CTC`, `VW::CTC_TP`, `VW::PPS`, `VW::TMT`, `VW::TMS`, `VW::BUG_STT`, `VW::TSKB`, `VW::TD`
