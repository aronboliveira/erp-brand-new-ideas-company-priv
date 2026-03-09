# Project Management Module — Crash-Prevention Audit

**Scope:** 12 PHP files, 13,033 total lines  
**Date:** 2025-07-10  
**Base path:** `_inc/laravel/app/Http/Controllers/Planning/` (controllers) and `_inc/laravel/app/Exports/` (exports)

---

## Legend

| Symbol | Meaning                                       |
| ------ | --------------------------------------------- |
| ✅     | Fully protected — try/catch with `\Throwable` |
| ⚠️     | Partially protected or minor concern          |
| ❌     | **Missing try/catch or crash-prone**          |

---

## 1. ProjectController.php — 4,057 lines

**Status: EXCELLENT** — All 30+ public methods have try/catch with `\Throwable`.

### Public methods audited

| Method                      | try/catch \Throwable | findOrFail guarded              | ViewFacade check | Notes                      |
| --------------------------- | -------------------- | ------------------------------- | ---------------- | -------------------------- |
| `index()`                   | ✅                   | N/A (route-model)               | ✅               |                            |
| `create()`                  | ✅                   | N/A                             | ✅               |                            |
| `store()`                   | ✅                   | N/A                             | N/A              |                            |
| `show()`                    | ✅                   | N/A (route-model)               | ✅               |                            |
| `edit()`                    | ✅                   | N/A (route-model)               | ✅               |                            |
| `update()`                  | ✅                   | N/A (route-model)               | N/A              |                            |
| `destroy()`                 | ✅                   | N/A (route-model)               | N/A              |                            |
| `inviteMemberView()`        | ✅                   | `findOrFail` in try ✅          | ✅               |                            |
| `inviteProjectUserMember()` | ✅                   | `findOrFail` in try ✅          | N/A              |                            |
| `destroyProjectUser()`      | ✅                   | `findOrFail` in try ✅          | N/A              |                            |
| `loadUser()`                | ✅                   | `findOrFail` in try ✅          | N/A              |                            |
| `milestone()`               | ✅                   | `findOrFail` in try ✅          | ✅               |                            |
| `milestoneStore()`          | ✅                   | `findOrFail` in try ✅          | N/A              |                            |
| `milestoneEdit()`           | ✅                   | `findOrFail` in try ✅          | ✅               |                            |
| `milestoneUpdate()`         | ✅                   | `findOrFail` in try ✅          | N/A              |                            |
| `milestoneDestroy()`        | ✅                   | `findOrFail` in try ✅          | N/A              |                            |
| `milestoneShow()`           | ✅                   | `findOrFail` in try ✅          | ✅               |                            |
| `filterProjectView()`       | ✅                   | N/A                             | ✅               |                            |
| `gantt()`                   | ✅                   | `findOrFail` in try ✅          | ✅               |                            |
| `ganttPost()`               | ✅                   | `findOrFail` in try ✅          | N/A              |                            |
| `bug()`                     | ✅                   | `findOrFail` in try ✅          | ✅               |                            |
| `bugCreate()`               | ✅                   | N/A                             | ✅               |                            |
| `bugStore()`                | ✅                   | N/A                             | N/A              |                            |
| `bugEdit()`                 | ✅                   | `findOrFail` in try ✅          | ✅               |                            |
| `bugUpdate()`               | ✅                   | `findOrFail` in try ✅          | N/A              |                            |
| `bugDestroy()`              | ✅                   | `findOrFail` in try ✅          | N/A              |                            |
| `bugKanban()`               | ✅                   | `findOrFail` in try ✅          | ✅               |                            |
| `bugKanbanOrder()`          | ✅                   | `findOrFail` in loop, in try ✅ | N/A              |                            |
| `bugShow()`                 | ✅                   | `findOrFail` in try ✅          | ✅               |                            |
| `bugCommentStore()`         | ✅                   | `findOrFail` in try ✅          | N/A              | ⚠️ `int $projectId` strict |
| `bugCommentDestroy()`       | ✅                   | `findOrFail` in try ✅          | N/A              |                            |
| `bugCommentStoreFile()`     | ✅                   | N/A                             | N/A              |                            |
| `bugCommentDestroyFile()`   | ✅                   | `findOrFail` in try ✅          | N/A              |                            |
| `tracker()`                 | ✅                   | N/A                             | ✅               | ⚠️ `int $projectId` strict |
| `getProjectChart()`         | ✅                   | N/A                             | N/A              | Takes `array`, not Request |
| `copyProject()`             | ✅                   | `findOrFail` in try ✅          | ✅               | ⚠️ `int $projectId` strict |
| `copyProjectStore()`        | ✅                   | `findOrFail` in try ✅          | N/A              | ⚠️ `int $projectId` strict |
| `copyLinkSettingCreate()`   | ✅                   | `firstOrFail` in try ✅         | ✅               | ⚠️ `int $projectId` strict |
| `copyLinkSetting()`         | ✅                   | `firstOrFail` in try ✅         | N/A              | ⚠️ `int $projectId` strict |
| `projectLink()`             | ✅                   | `findOrFail` in try ✅          | ✅ (2 views)     |                            |
| `projectCopyLink()`         | ✅                   | `findOrFail` in try ✅          | N/A              |                            |
| `shareProject()`            | ✅                   | `findOrFail` in try ✅          | N/A              |                            |
| `userPermission()`          | ✅                   | `findOrFail` in try ✅          | N/A              |                            |

### Issues found

| #   | Severity  | Line(s) | Method                                                          | Issue                                                                                                                                                     |
| --- | --------- | ------- | --------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | ⚠️ MEDIUM | ~2800   | `bugCommentStore(Request $request, int $projectId, int $bugId)` | Strict `int` type hint instead of `int\|string`. If a UUID string arrives from route, PHP throws `TypeError` **before** method body — bypasses try/catch. |
| 2   | ⚠️ MEDIUM | ~3130   | `tracker(Request $request, int $projectId)`                     | Same strict `int` type hint issue.                                                                                                                        |
| 3   | ⚠️ MEDIUM | ~3240   | `copyProject(Request $request, int $projectId)`                 | Same strict `int` type hint issue.                                                                                                                        |
| 4   | ⚠️ MEDIUM | ~3290   | `copyProjectStore(Request $request, int $projectId)`            | Same strict `int` type hint issue.                                                                                                                        |
| 5   | ⚠️ MEDIUM | ~3500   | `copyLinkSettingCreate(Request $request, int $projectId)`       | Same strict `int` type hint issue.                                                                                                                        |
| 6   | ⚠️ MEDIUM | ~3590   | `copyLinkSetting(Request $request, int $projectId)`             | Same strict `int` type hint issue.                                                                                                                        |

**Fix:** Change `int $projectId` → `int|string $projectId` and `int $bugId` → `int|string $bugId` on these 6 methods to match the pattern used by all other methods in this controller.

---

## 2. ProjectTaskController.php — 3,294 lines

**Status: EXCELLENT** — All 20+ public methods have try/catch with `\Throwable`.

### Public methods audited

| Method                      | try/catch \Throwable | findOrFail guarded          | ViewFacade check           | Notes                            |
| --------------------------- | -------------------- | --------------------------- | -------------------------- | -------------------------------- |
| `index()`                   | ✅                   | `firstOrFail` in try ✅     | ✅ `renderViewChecked`     |                                  |
| `create()`                  | ✅                   | `firstOrFail` in try ✅     | ✅ `renderViewChecked`     |                                  |
| `store()`                   | ✅                   | `firstOrFail` in try ✅     | N/A                        |                                  |
| `taskBoard()`               | ✅                   | `firstOrFail` in try ✅     | ✅ `renderViewChecked`     |                                  |
| `taskBoardView()`           | ✅                   | `firstOrFail` in try ✅     | ✅ `renderJsonViewChecked` |                                  |
| `allBugList()`              | ✅                   | N/A                         | ✅ `renderViewChecked`     |                                  |
| `show()`                    | ✅                   | `findOrFail` in try ✅      | ✅ `renderViewChecked`     |                                  |
| `edit()`                    | ✅                   | `findOrFail` × 2 in try ✅  | ✅ `renderViewChecked`     |                                  |
| `update()`                  | ✅                   | `firstOrFail` in try ✅     | N/A                        |                                  |
| `destroy()`                 | ✅                   | `firstOrFail` × 2 in try ✅ | N/A                        |                                  |
| `getStageTasks()`           | ✅                   | N/A                         | N/A                        |                                  |
| `changeCom()`               | ✅                   | `firstOrFail` × 2 in try ✅ | N/A                        |                                  |
| `changeFav()`               | ✅                   | `firstOrFail` × 2 in try ✅ | N/A                        |                                  |
| `changeProg()`              | ✅                   | `firstOrFail` × 2 in try ✅ | N/A                        |                                  |
| `checkListStore()`          | ✅                   | `firstOrFail` × 2 in try ✅ | N/A                        |                                  |
| `checklistUpdate()`         | ✅                   | `findOrFail` in try ✅      | N/A                        |                                  |
| `checkListDestroy()`        | ✅                   | `findOrFail` in try ✅      | N/A                        |                                  |
| `commentStoreFile()`        | ✅                   | `firstOrFail` × 2 in try ✅ | N/A                        | Cleans uploaded file on error ✅ |
| `commentDestroyFile()`      | ✅                   | `findOrFail` in try ✅      | N/A                        |                                  |
| `commentDestroy()`          | ✅                   | `findOrFail` in try ✅      | N/A                        |                                  |
| `commentStore()`            | ✅                   | `firstOrFail` × 2 in try ✅ | N/A                        |                                  |
| `taskOrderUpdate()`         | ✅                   | `firstOrFail` in try ✅     | N/A                        |                                  |
| `taskGet()`                 | ✅                   | `firstOrFail` × 2 in try ✅ | ✅ `renderJsonViewChecked` |                                  |
| `getDefaultTaskInfo()`      | ✅                   | `firstOrFail` × 2 in try ✅ | N/A                        |                                  |
| `calendarView()`            | ✅                   | N/A                         | ✅ `renderViewChecked`     |                                  |
| `calendarShow()`            | ✅                   | `findOrFail` in try ✅      | ✅ `renderViewChecked`     |                                  |
| `calendarDrag()`            | ✅                   | `findOrFail` in try ✅      | N/A                        |                                  |
| `getTaskData()`             | ✅                   | N/A                         | N/A                        | ⚠️ See below                     |
| `updateTaskPriorityColor()` | ✅                   | `findOrFail` in try ✅      | N/A                        |                                  |

### Issues found

| #   | Severity | Line(s) | Method          | Issue                                                                                                                                                                                                                                                                                                      |
| --- | -------- | ------- | --------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | ⚠️ LOW   | ~3100   | `getTaskData()` | `date_create($val[PJC::COL_E_DT])` — if `end_date` is null or malformed, `date_create()` returns `false`, and `date_add($end, ...)` on `false` emits a `TypeError`. Caught by `\Throwable`, so no 500, but the entire request fails silently. Recommend: `$end = date_create($val[...]) ?: date_create();` |

---

## 3. ProjectReportController.php — 615 lines

**Status: POOR** — 4 public methods completely missing try/catch.

### Issues found

| #   | Severity    | Line(s) | Method                              | Issue                                                                                                                                       |
| --- | ----------- | ------- | ----------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | ❌ **HIGH** | ~296    | `getProjectChart(Request $request)` | **NO try/catch at all.** Runs DB queries (`ProjectTask::select(...)`, `TaskStage::where(...)`) — any `QueryException` will throw a raw 500. |
| 2   | ❌ MEDIUM   | ~451    | `create()`                          | **NO try/catch.** Stub method with only login/guard checks, but still executes framework code that can throw.                               |
| 3   | ❌ MEDIUM   | ~469    | `store()`                           | **NO try/catch.** Stub method.                                                                                                              |
| 4   | ❌ MEDIUM   | ~486    | `edit()`                            | **NO try/catch.** Stub method.                                                                                                              |

### Protected methods (reference)

| Method                | Status                        |
| --------------------- | ----------------------------- |
| `index()`             | ✅                            |
| `show()`              | ✅ (`firstOrFail` in try)     |
| `ajax_data()`         | ✅                            |
| `ajax_tasks_report()` | ✅                            |
| `export()`            | ✅ (`Excel::download` in try) |
| `destroy()`           | ✅                            |

---

## 4. ContractController.php — 1,055 lines

**Status: FAIR** — 2 public methods missing try/catch; additional minor issues.

### Issues found

| #   | Severity    | Line(s)    | Method                                | Issue                                                                                                                                                                                                                                                                                    |
| --- | ----------- | ---------- | ------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | ❌ **HIGH** | ~116       | `create(Request $request)`            | **NO try/catch.** Runs `ContractType::where(...)`, `User::where(...)`, `Project::where(...)` DB queries — any `QueryException` propagates as raw 500.                                                                                                                                    |
| 2   | ❌ **HIGH** | ~530       | `commentStore(Request $request, ...)` | **NO try/catch.** Calls `$c->save()` which can throw `QueryException`.                                                                                                                                                                                                                   |
| 3   | ⚠️ MEDIUM   | ~186, ~803 | `store()`, `copyContractStore()`      | `User::findOrFail($c->{PJC::COL_CLIENT_NAME})` — column named "client_name" used as a User ID lookup. If the column contains a name string instead of an ID, `ModelNotFoundException` is thrown. Both are inside try/catch, so no raw 500, but the semantic mismatch may indicate a bug. |
| 4   | ⚠️ LOW      | ~448       | `fileDownload()`                      | `filesize($path)` called without `file_exists($path)` guard. Emits PHP Warning if file missing. Inside try/catch, so no 500.                                                                                                                                                             |

---

## 5. ContractTypeController.php — 581 lines

**Status: EXCELLENT** — All methods have try/catch with `\Throwable`.

No issues found. Uses route-model binding, avoiding `findOrFail` risks.

---

## 6. TimeTrackerController.php — 320 lines

**Status: POOR** — 3 public methods completely missing try/catch.

### Issues found

| #   | Severity    | Line(s) | Method                        | Issue                                                                                                                                                                         |
| --- | ----------- | ------- | ----------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | ❌ **HIGH** | ~55     | `create(Request $request)`    | **NO try/catch.**                                                                                                                                                             |
| 2   | ❌ **HIGH** | ~120    | `show(Request $request, ...)` | **NO try/catch.**                                                                                                                                                             |
| 3   | ❌ **HIGH** | ~132    | `edit(Request $request, ...)` | **NO try/catch.**                                                                                                                                                             |
| 4   | ⚠️ LOW      | ~293    | `performDestroy()` (private)  | `$photo->img_path` accessed in a loop — low risk since `$photo` comes from a query result, but no explicit null-safe. Called within `destroy()` which has `\Throwable` catch. |

---

## 7. TimesheetController.php — 581 lines

**Status: POOR** — 2 public methods missing try/catch; null-pointer risks.

### Issues found

| #   | Severity    | Line(s) | Method                                      | Issue                                                                                                                                                                                                                                     |
| --- | ----------- | ------- | ------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | ❌ **HIGH** | ~67     | `appendTimesheetTaskHtml(Request $request)` | **NO try/catch.** `$task = ProjectTask::find($request->task_id)` — if `task_id` is null or task is deleted, `$task` is `null`. Line ~76 accesses `$task->name` → **NullPointerException / 500**.                                          |
| 2   | ❌ **HIGH** | ~345    | `timesheetList(Request $request, ...)`      | **NO try/catch.** DB queries that can throw `QueryException`.                                                                                                                                                                             |
| 3   | ⚠️ MEDIUM   | ~201    | `timesheetEdit()`                           | `$timesheet->task->name` — if the `task` Eloquent relationship returns `null` (deleted task), this is a **null pointer crash**. Inside try/catch, so caught, but the error message will be generic. Recommend: `$timesheet->task?->name`. |

---

## 8. BugStatusController.php — 342 lines

**Status: GOOD** — All methods have try/catch with `\Throwable`.

### Issues found

| #   | Severity | Line(s)    | Method               | Issue                                                                                                                                                                         |
| --- | -------- | ---------- | -------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | ⚠️ LOW   | ~149, ~261 | `store()`, `order()` | `$request->user()->creatorId()` — missing null-safe `?->`. If `$request->user()` returns `null` (middleware bypass), this crashes. Low risk because `_checkLogin` runs first. |

---

## 9. TaskStageController.php — 570 lines

**Status: FAIR** — 1 method missing try/catch; 1 findOrFail outside try block.

### Issues found

| #   | Severity        | Line(s) | Method                                      | Issue                                                                                                                                                                      |
| --- | --------------- | ------- | ------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | ❌ **HIGH**     | ~117    | `create(Request $request)`                  | **NO try/catch.** Contains `ViewFacade::exists()` check but the method body is not wrapped — any exception from the view check or subsequent code propagates as a raw 500. |
| 2   | ❌ **CRITICAL** | ~371    | `update(Request $request, int\|string $id)` | `TaskStage::findOrFail($id)` is **OUTSIDE the try block**. If model is not found, `ModelNotFoundException` propagates uncaught → raw 500 to the user.                      |

---

## 10. ProposalController.php — 1,282 lines

**Status: POOR** — 4 public methods missing try/catch; multiple null-pointer risks; dead code.

### Issues found

| #   | Severity        | Line(s) | Method                                            | Issue                                                                                                                                                                                                                                                                                                 |
| --- | --------------- | ------- | ------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | ❌ **CRITICAL** | ~192    | `product(Request $request)`                       | **NO try/catch.** `ProductService::findOrFail($request->product_id)` — if `product_id` is missing or invalid, bare `ModelNotFoundException` → raw 500.                                                                                                                                                |
| 2   | ❌ **CRITICAL** | ~933    | `statusChange(Request $request, int\|string $id)` | **NO try/catch.** `Proposal::findOrFail($id)` completely bare → raw 500 if not found.                                                                                                                                                                                                                 |
| 3   | ❌ **HIGH**     | ~955    | `previewProposal(Request $request)`               | **NO try/catch.** References undefined variable `$preview` in `compact()` → `ErrorException: Undefined variable $preview` → 500.                                                                                                                                                                      |
| 4   | ❌ **HIGH**     | ~551    | `destroy(Request $request, int\|string $id)`      | `DB::transaction()` with **NO outer try/catch**. Transaction errors (query failure, deadlock) propagate uncaught.                                                                                                                                                                                     |
| 5   | ❌ **HIGH**     | ~588    | `sent(Request $request, int\|string $id)`         | `Customer::find($proposal->customer_id)` → then accesses `$customer->name`, `$customer->id`, `$customer->email` without null check. If customer was deleted → **NullPointerException / 500**. This IS inside try/catch, but will fail with a confusing generic error instead of "Customer not found". |
| 6   | ❌ **HIGH**     | ~700    | `resent(Request $request, int\|string $id)`       | Same `Customer::find()` null-pointer risk as `sent()`.                                                                                                                                                                                                                                                |
| 7   | ⚠️ LOW          | ~444    | `update()`                                        | `$this->logExecutionTime(...)` placed after catch block — **dead code** (unreachable after return).                                                                                                                                                                                                   |
| 8   | ⚠️ LOW          | ~847    | `convert()`                                       | `$this->logExecutionTime(...)` placed after `return` — **dead code**.                                                                                                                                                                                                                                 |

---

## 11. TaskReportExport.php — 191 lines

**Status: EXCELLENT** — All methods have try/catch with `\Throwable`.

No issues found.

---

## 12. ProposalExport.php — 157 lines

**Status: GOOD** — Core methods protected.

### Issues found

| #   | Severity | Line(s) | Method       | Issue                                                                |
| --- | -------- | ------- | ------------ | -------------------------------------------------------------------- |
| 1   | ⚠️ LOW   | ~107    | `headings()` | **NO try/catch.** Returns a hardcoded array — negligible crash risk. |

---

## Summary Statistics

| File                        | Lines      | Public Methods | Missing try/catch | Critical Issues            | Total Issues   |
| --------------------------- | ---------- | -------------- | ----------------- | -------------------------- | -------------- |
| ProjectController.php       | 4,057      | 33             | 0                 | 0                          | 6 (type hints) |
| ProjectTaskController.php   | 3,294      | 29             | 0                 | 0                          | 1              |
| ProjectReportController.php | 615        | 10             | **4**             | 1                          | 4              |
| ContractController.php      | 1,055      | 20             | **2**             | 2                          | 4              |
| ContractTypeController.php  | 581        | 8              | 0                 | 0                          | 0              |
| TimeTrackerController.php   | 320        | 7              | **3**             | 3                          | 4              |
| TimesheetController.php     | 581        | 8              | **2**             | 2                          | 3              |
| BugStatusController.php     | 342        | 6              | 0                 | 0                          | 1              |
| TaskStageController.php     | 570        | 8              | **1**             | 1 (findOrFail outside try) | 2              |
| ProposalController.php      | 1,282      | 18             | **4**             | 2                          | 8              |
| TaskReportExport.php        | 191        | 4              | 0                 | 0                          | 0              |
| ProposalExport.php          | 157        | 3              | 0                 | 0                          | 1              |
| **TOTALS**                  | **13,033** | **154**        | **16**            | **11**                     | **34**         |

---

## Priority Fix List (ordered by severity)

### CRITICAL (will crash in production with common inputs)

1. **TaskStageController::update()** ~L371 — `findOrFail` outside try block
2. **ProposalController::product()** ~L192 — bare `findOrFail` with no try/catch
3. **ProposalController::statusChange()** ~L933 — bare `findOrFail` with no try/catch
4. **ProposalController::previewProposal()** ~L955 — undefined `$preview` variable in `compact()`

### HIGH (crash on edge cases or DB failures)

5. **ProposalController::destroy()** ~L551 — `DB::transaction` without outer try/catch
6. **ProposalController::sent()** ~L588 — null pointer on `Customer::find()` result
7. **ProposalController::resent()** ~L700 — null pointer on `Customer::find()` result
8. **ContractController::create()** ~L116 — no try/catch around DB queries
9. **ContractController::commentStore()** ~L530 — no try/catch around `save()`
10. **ProjectReportController::getProjectChart()** ~L296 — no try/catch around DB queries
11. **TimeTrackerController::create()** ~L55 — no try/catch
12. **TimeTrackerController::show()** ~L120 — no try/catch
13. **TimeTrackerController::edit()** ~L132 — no try/catch
14. **TimesheetController::appendTimesheetTaskHtml()** ~L67 — no try/catch, bare `$task->name` NPE risk
15. **TimesheetController::timesheetList()** ~L345 — no try/catch
16. **TaskStageController::create()** ~L117 — no try/catch

### MEDIUM (type-system bypass or semantic concern)

17–22. **ProjectController** 6 methods with strict `int` instead of `int|string`:

- `bugCommentStore()` ~L2800
- `tracker()` ~L3130
- `copyProject()` ~L3240
- `copyProjectStore()` ~L3290
- `copyLinkSettingCreate()` ~L3500
- `copyLinkSetting()` ~L3590

23–24. **ContractController::store()** ~L186 / **copyContractStore()** ~L803 — `User::findOrFail($c->{PJC::COL_CLIENT_NAME})` semantic mismatch

### LOW (warnings, dead code, cosmetic)

25. **ContractController::fileDownload()** ~L448 — `filesize()` without `file_exists()`
26. **BugStatusController::store()/order()** ~L149/~L261 — `$request->user()->creatorId()` without null-safe
27. **TimesheetController::timesheetEdit()** ~L201 — `$timesheet->task->name` null chain
28. **ProjectTaskController::getTaskData()** ~L3100 — `date_create()` on potentially null date
29. **ProposalController::update()** ~L444 — dead code after catch
30. **ProposalController::convert()** ~L847 — dead code after return
31. **ProposalExport::headings()** ~L107 — no try/catch on constant array return

---

## Recommended Global Fixes

### 1. Wrap all unprotected public methods

Every public controller method reachable via route **must** have:

```php
try {
    // ... method body ...
} catch (ValidationException $e) {
    // ... specific handling ...
} catch (ModelNotFoundException $e) {
    // ... 404 handling ...
} catch (QueryException $e) {
    // ... DB error handling ...
} catch (\Exception $e) {
    // ... general handling ...
} catch (\Throwable $e) {
    // ... fatal handling ...
}
```

### 2. Replace `findOrFail` outside try blocks

```php
// ❌ BAD (TaskStageController::update ~L371)
$stage = TaskStage::findOrFail($id);
try { ... }

// ✅ GOOD
try {
    $stage = TaskStage::findOrFail($id);
    ...
}
```

### 3. Null-guard `find()` results before property access

```php
// ❌ BAD (ProposalController::sent)
$customer = Customer::find($proposal->customer_id);
$customer->name; // NPE if customer deleted

// ✅ GOOD
$customer = Customer::find($proposal->customer_id);
if (!$customer) return redirect()->back()->with('error', __('Customer not found.'));
$customer->name;
```

### 4. Standardize `int|string` on all ID parameters

```php
// ❌ BAD
public function tracker(Request $request, int $projectId)

// ✅ GOOD
public function tracker(Request $request, int|string $projectId)
```

### 5. Remove dead code

```php
// ❌ BAD (ProposalController::convert ~L847)
return $result;
$this->logExecutionTime(...); // unreachable

// ✅ GOOD
$this->logExecutionTime(...);
return $result;
```
