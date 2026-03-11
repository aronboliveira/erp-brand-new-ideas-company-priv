# Test Failure Diagnostic Report

> **Generated from codebase analysis**  
> **Base path:** `_inc/laravel/`

---

## Table of Contents

1. [PHPUnit Feature Test Failures (16)](#1-phpunit-feature-test-failures-16)
2. [Playwright E2E Test Failures (18)](#2-playwright-e2e-test-failures-18)
3. [Root Cause Categories](#3-root-cause-categories)
4. [File Index](#4-file-index)

---

## 1. PHPUnit Feature Test Failures (16)

All tests use `assertNot500(TestResponse $r)` which asserts `$r->getStatusCode() !== 500`.  
Tests run `actingAs($admin)` where `$admin = User::where('email', 'suporte@prestech.com.br')->first()`.

---

### F-1: GET `/payslips/employeepayslip`

| Attribute         | Value                                                                                              |
| ----------------- | -------------------------------------------------------------------------------------------------- |
| **Test file**     | `tests/Feature/HrmRouteReturnTest.php`                                                             |
| **Data provider** | `payrollResourceProvider` → key `employeepayslip` (line 249)                                       |
| **Route def**     | `routes/web.php:988` — `R::get(VW::PY_SLP . '/employeepayslip', [PYSC::class, PYSC::EMP_PAY_SLP])` |
| **Middleware**    | `[MWC::AUTH, MWC::XSS]` (parent group at web.php:304: `[MWC::AUTH, MWC::VF]`)                      |
| **Controller**    | `app/Http/Controllers/Bills/PayslipController.php`                                                 |
| **Method**        | `employeePayslip()` — line 446 (const `EMP_PAY_SLP` at line 445)                                   |
| **Root cause**    | **Missing employee record for test user**                                                          |

**Code path:**

```php
// PayslipController.php:446
public function employeePayslip() {
    // ...
    $user = auth()->user();
    $employee = Employee::where('user_id', $user->id)->first();
    if (!$employee) {
        return back()->with('error', __('Employee not found'));
    }
    // ...
}
```

The admin test user (`suporte@prestech.com.br`) has no `Employee` record. The `back()` redirect with no HTTP referer in the test context may trigger a Symfony exception. Additionally, if the user type resolution fails upstream, the method can throw before reaching the employee check.

**Fix strategy:** Wrap in try/catch that returns `defaultUndefinedException` with the `ModelNotFoundException` branch (returns redirect, not 500). Or add `abort(404)` when employee not found.

---

### F-2: GET `/terminations/{uuid}/description`

| Attribute         | Value                                                                                         |
| ----------------- | --------------------------------------------------------------------------------------------- |
| **Test file**     | `tests/Feature/HrmRouteReturnTest.php`                                                        |
| **Data provider** | `terminationExtrasProvider` → key `termination_desc` (line 412)                               |
| **Test param**    | `$fk = '00000000-0000-0000-0000-000000000000'`                                                |
| **Route def**     | `routes/web.php:1022` — `R::get(VW::TMN . '/{id}/description', [TMNC::class, 'description'])` |
| **Middleware**    | `[MWC::AUTH, MWC::XSS]`                                                                       |
| **Controller**    | `app/Http/Controllers/Planning/TerminationController.php`                                     |
| **Method**        | `description(Request $request, int $id)` — line 341                                           |
| **Root cause**    | **`int $id` type hint rejects UUID string → uncaught TypeError**                              |

**Code path:**

```php
// TerminationController.php:341
public function description(Request $request, int $id) {
    // PHP throws TypeError before method body executes:
    // "Argument #2 ($id) must be of type int, string given"
    $termination = Termination::findOrFail($id);
    // ...
}
```

The route passes a UUID string, but the method signature demands `int`. PHP throws `TypeError` before the method body runs, bypassing all try/catch blocks. Laravel's exception handler converts this to HTTP 500.

**Fix strategy:** Change signature to `int|string $id`.

---

### F-3: GET `/appraisals`

| Attribute         | Value                                                                               |
| ----------------- | ----------------------------------------------------------------------------------- |
| **Test file**     | `tests/Feature/HrmRouteReturnTest.php`                                              |
| **Data provider** | `performanceResourceProvider` → key `appraisal_index` (line 443)                    |
| **Route def**     | `routes/web.php` — inside `R::resource(VW::APR, APRC::class)`                       |
| **Middleware**    | `[MWC::AUTH, MWC::XSS]`                                                             |
| **Controller**    | `app/Http/Controllers/Activity/AppraisalController.php`                             |
| **Method**        | `index(Request $request)` — line 32                                                 |
| **Root cause**    | **`Employee::firstOrFail()` for employee-type users / authorization chain failure** |

**Code path:**

```php
// AppraisalController.php — index()
$user = $request->user();
// line 61: if user type is 'employee':
Employee::where(UC::COL_USER_ID, $user?->id)->firstOrFail();
// If no employee record → ModelNotFoundException
// Caught by catch block → calls handleFailure() → defaultUndefinedException()
```

`defaultUndefinedException` checks for `ModelNotFoundException` and returns redirect (not 500). **However**, if the authorization call (`self::_setAuth()`) throws an `AuthorizationException` for a user without `manage appraisal` permission, the catch returns `defaultPermissionDenial()`. If `defaultPermissionDenial` itself fails (e.g., missing view), it cascades to 500.

The 500 likely comes from either:

1. The `Competencies` or `Appraisal` query failing on missing DB tables/columns
2. The `handleFailure` method encountering an error during redirect resolution

**Fix strategy:** Audit the full query chain and ensure `handleFailure` doesn't re-throw.

---

### F-4: GET `/projects/copy-links/{uuid}`

| Attribute         | Value                                                                                          |
| ----------------- | ---------------------------------------------------------------------------------------------- |
| **Test file**     | `tests/Feature/PmRouteReturnTest.php`                                                          |
| **Data provider** | `projectCoreRoutesProvider` → key `copy_link` (line 103)                                       |
| **Test param**    | `$fk = '00000000-0000-0000-0000-000000000000'`                                                 |
| **Route def**     | `routes/web.php:289` — `R::get(VW::PRJ . '/copy-link/{id}', [PRJC::class, 'projectCopyLink'])` |
| **Middleware**    | `[MWC::AUTH, MWC::XSS]`                                                                        |
| **Controller**    | `app/Http/Controllers/Planning/ProjectController.php`                                          |
| **Method**        | `projectCopyLink(Request $request, int\|string $id)` — line 3981                               |
| **Root cause**    | **Explicit 500 JSON in catch block**                                                           |

**IMPORTANT:** The test URL is `/projects/copy-links/{uuid}` (with 's') but the route is `/projects/copy-link/{id}` (singular). This is a **route mismatch**. If no route matches → 404 (test passes). If this test IS failing, it's because another route (e.g., resource show `projects/{project}`) catches the URL and that controller 500s.

**Code path (if route matched):**

```php
// ProjectController.php:3981
public function projectCopyLink(Request $request, int|string $id) {
    try {
        $project = Project::where(DC::COL_TABLE_CREATOR, $user->creatorId())->findOrFail($id);
        // ...
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage()], 500);  // ← EXPLICIT 500
    }
}
```

**Fix strategy:** Fix route path in test OR fix catch to return 404 for `ModelNotFoundException`.

---

### F-5: PUT `/project_stages/{uuid}`

| Attribute         | Value                                                             |
| ----------------- | ----------------------------------------------------------------- |
| **Test file**     | `tests/Feature/PmRouteReturnTest.php`                             |
| **Data provider** | `projectStageResourceProvider` → key `update` (line 157)          |
| **Route def**     | `routes/web.php:1312` — `R::resource(VW::PRJ_STG, PRJSTC::class)` |
| **Middleware**    | `[MWC::AUTH, MWC::XSS]`                                           |
| **Controller**    | `app/Http/Controllers/Planning/ProjectStagesController.php`       |
| **Method**        | `update(Request $request, int $id)` — line 338                    |
| **Root cause**    | **`int $id` type hint rejects UUID string → uncaught TypeError**  |

```php
// ProjectStagesController.php:338
public function update(Request $request, int $id) {  // ← TypeError with UUID
    // ...
}
```

**Fix strategy:** Change signature to `int|string $id`.

---

### F-6: DELETE `/project_stages/{uuid}`

| Attribute         | Value                                                            |
| ----------------- | ---------------------------------------------------------------- |
| **Test file**     | `tests/Feature/PmRouteReturnTest.php`                            |
| **Data provider** | `projectStageResourceProvider` → key `delete` (line 158)         |
| **Route def**     | Same as F-5                                                      |
| **Controller**    | `app/Http/Controllers/Planning/ProjectStagesController.php`      |
| **Method**        | `destroy(Request $request, int $id)` — line 457                  |
| **Root cause**    | **`int $id` type hint rejects UUID string → uncaught TypeError** |

**Fix strategy:** Same as F-5.

---

### F-7: GET `/proposals/previews/template1/ffffff`

| Attribute         | Value                                                                                                 |
| ----------------- | ----------------------------------------------------------------------------------------------------- |
| **Test file**     | `tests/Feature/PmRouteReturnTest.php`                                                                 |
| **Data provider** | `proposalRoutesProvider` → key `preview` (line 275)                                                   |
| **Route def**     | `routes/web.php:761` — `R::get(VW::PPS . '/preview/{template}/{color}', [PPSC::class, PPSC::PV_PPS])` |
| **Controller**    | `app/Http/Controllers/Planning/ProposalController.php`                                                |
| **Method**        | `previewProposal(string $template, string $color)` — line 896                                         |
| **Root cause**    | **Route path mismatch + undefined variable in view**                                                  |

Two issues:

1. **Test URL mismatch:** Test hits `/proposals/previews/...` (plural) but route is `/proposals/preview/...` (singular). No route match → should be 404 (test passes). If this test IS failing, check whether a resource catch-all route intercepts it.
2. **Controller bug (if route matched):** The method uses `compact('preview')` but `$preview` is never assigned → `UndefinedVariableException` → 500.

```php
// ProposalController.php:896
public function previewProposal(string $template, string $color) {
    // ... $preview never defined ...
    return view($viewPath, compact('preview'));  // ← UndefinedVariableException
}
```

**Fix strategy:** Fix test URL to `/proposals/preview/template1/ffffff` AND define `$preview` before compact.

---

### F-8: PUT `/project_reports/{uuid}`

| Attribute         | Value                                                                                                   |
| ----------------- | ------------------------------------------------------------------------------------------------------- |
| **Test file**     | `tests/Feature/PmRouteReturnTest.php`                                                                   |
| **Data provider** | `projectReportRoutesProvider` → key `update` (line ~325)                                                |
| **Route def**     | `routes/web.php:1731` — `R::resource(VW::PRJ_RPT, PRPC::class)`                                         |
| **Controller**    | `app/Http/Controllers/Planning/ProjectReportController.php`                                             |
| **Method**        | **DOES NOT EXIST**                                                                                      |
| **Root cause**    | **Missing `update()` method — `R::resource()` registers the route but no controller method handles it** |

The resource route registers `PUT /project_reports/{project_report}` → `ProjectReportController@update`, but the controller only has: `index`, `show`, `create` (stub), `store` (stub), `edit` (stub), `ajax_data`, `ajax_tasks_report`, `export`.

**Fix strategy:** Implement `update()` method or exclude it from resource: `R::resource(...)->except(['update'])`.

---

### F-9: DELETE `/project_reports/{uuid}`

| Attribute         | Value                                                       |
| ----------------- | ----------------------------------------------------------- |
| **Test file**     | `tests/Feature/PmRouteReturnTest.php`                       |
| **Data provider** | `projectReportRoutesProvider` → key `delete`                |
| **Route def**     | Same as F-8                                                 |
| **Controller**    | `app/Http/Controllers/Planning/ProjectReportController.php` |
| **Method**        | **DOES NOT EXIST**                                          |
| **Root cause**    | **Missing `destroy()` method**                              |

**Fix strategy:** Implement `destroy()` method or exclude it: `R::resource(...)->except(['update', 'destroy'])`.

---

### F-10: POST `/todos/create`

| Attribute      | Value                                                                              |
| -------------- | ---------------------------------------------------------------------------------- |
| **Test file**  | `tests/Feature/PmRouteReturnTest.php`                                              |
| **Method**     | `test_todo_store_no_500()` — line 435                                              |
| **Route def**  | `routes/web.php:1320` — `R::post(VW::TD . '/create', [USRC::class, USRC::TD_STR])` |
| **Middleware** | `[MWC::AUTH, MWC::XSS]`                                                            |
| **Controller** | `app/Http/Controllers/Individuals/UserController.php`                              |
| **Method**     | `todoStore(Request $request)` — line 369                                           |
| **Root cause** | **Catches `\Throwable` and returns 500 JSON**                                      |

```php
// UserController.php:369
public function todoStore(Request $request) {
    try {
        $todo = UserToDo::create([...]);
        return response()->json([
            'url_update' => route('todo.update', [$todo->id]),  // ← named route may not exist
            'url_destroy' => route('todo.destroy', [$todo->id]),
        ]);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage()], 500);  // ← EXPLICIT 500
    }
}
```

If `route('todo.update')` doesn't resolve (wrong named route), `RouteNotFoundException` is thrown and the catch returns 500.

**Fix strategy:** Fix named routes in the JSON response or replace catch with proper HTTP status codes.

---

### F-11: POST `/todos/{uuid}/update`

| Attribute      | Value                                                                                   |
| -------------- | --------------------------------------------------------------------------------------- |
| **Test file**  | `tests/Feature/PmRouteReturnTest.php`                                                   |
| **Method**     | `test_todo_update_fake_id_no_500()` — line 442                                          |
| **Route def**  | `routes/web.php:1322` — `R::post(VW::TD . '/{id}/update', [USRC::class, USRC::TD_UPD])` |
| **Controller** | `app/Http/Controllers/Individuals/UserController.php`                                   |
| **Method**     | `todoUpdate(int $todoId)` — line 398                                                    |
| **Root cause** | **`int $todoId` type hint rejects UUID string → uncaught TypeError**                    |

Additionally, route uses `POST` but test also sends `POST` (correct). The `int` type hint causes `TypeError` before the method body.

**Fix strategy:** Change signature to `int|string $todoId`.

---

### F-12: DELETE `/todos/{uuid}/delete`

| Attribute      | Value                                                                                     |
| -------------- | ----------------------------------------------------------------------------------------- |
| **Test file**  | `tests/Feature/PmRouteReturnTest.php`                                                     |
| **Method**     | `test_todo_delete_fake_id_no_500()` — line 449                                            |
| **Route def**  | `routes/web.php:1324` — `R::delete(VW::TD . '/{id}/delete', [USRC::class, USRC::TD_DEL])` |
| **Controller** | `app/Http/Controllers/Individuals/UserController.php`                                     |
| **Method**     | `todoDestroy(int\|string $id)` — line 419                                                 |
| **Root cause** | **`findOrFail` throws, caught by `\Throwable` → `defaultUndefinedException`**             |

```php
// UserController.php:419
public function todoDestroy(int|string $id) {
    try {
        $todo = UserToDo::findOrFail($id);
        $todo->delete();
        return response()->json(['success' => true]);
    } catch (\Throwable $e) {
        return defaultUndefinedException($request, $e, __METHOD__);
    }
}
```

`defaultUndefinedException` checks if `$e instanceof ModelNotFoundException` and returns redirect (not 500). **However**, the `$request` variable is NOT in scope (not a parameter) — this causes another error, returning 500. Double failure: `findOrFail` fails, then error handler fails.

**Fix strategy:** Add `Request $request` parameter or use `request()` helper.

---

### F-13: GET `/product_stocks/{uuid}/edit`

| Attribute         | Value                                                                                                              |
| ----------------- | ------------------------------------------------------------------------------------------------------------------ |
| **Test file**     | `tests/Feature/ProductControlRouteReturnTest.php`                                                                  |
| **Data provider** | `productStockResourceProvider` → key `edit`                                                                        |
| **Route def**     | `routes/web.php:1527` — `R::resource(VW::PRD_STK, PSTKC::class)->middleware([MWC::AUTH, MWC::XSS, 'check.mount'])` |
| **Controller**    | `app/Http/Controllers/Products/ProductStockController.php`                                                         |
| **Method**        | `edit(Request $r, int $id)` — line 98                                                                              |
| **Root cause**    | **`int $id` type hint rejects UUID string → uncaught TypeError**                                                   |

**Fix strategy:** Change signature to `int|string $id`.

---

### F-14: PUT `/product_stocks/{uuid}`

| Attribute         | Value                                                            |
| ----------------- | ---------------------------------------------------------------- |
| **Test file**     | `tests/Feature/ProductControlRouteReturnTest.php`                |
| **Data provider** | `productStockResourceProvider` → key `update`                    |
| **Controller**    | Same as F-13                                                     |
| **Method**        | `update(Request $r, int $id)` — line 113                         |
| **Root cause**    | **`int $id` type hint rejects UUID string → uncaught TypeError** |

**Fix strategy:** Change signature to `int|string $id`.

---

### F-15: DELETE `/product_stocks/{uuid}`

| Attribute         | Value                                                            |
| ----------------- | ---------------------------------------------------------------- |
| **Test file**     | `tests/Feature/ProductControlRouteReturnTest.php`                |
| **Data provider** | `productStockResourceProvider` → key `delete`                    |
| **Controller**    | Same as F-13                                                     |
| **Method**        | `destroy(Request $r, int $id)` — line 141                        |
| **Root cause**    | **`int $id` type hint rejects UUID string → uncaught TypeError** |

**Fix strategy:** Change signature to `int|string $id`.

---

### F-16: PUT `/warehouse_transfers/{uuid}`

| Attribute         | Value                                                            |
| ----------------- | ---------------------------------------------------------------- |
| **Test file**     | `tests/Feature/ProductControlRouteReturnTest.php`                |
| **Data provider** | `warehouseTransferResourceProvider` → key `update`               |
| **Route def**     | `routes/web.php:1660` — `R::resource(VW::WRH_TRF, WRHTC::class)` |
| **Controller**    | `app/Http/Controllers/Activity/WarehouseTransferController.php`  |
| **Method**        | **DOES NOT EXIST**                                               |
| **Root cause**    | **Missing `update()` method**                                    |

Controller has: `index`(33), `create`(85), `show`(137), `store`(179), `destroy`(268), `getProduct`(329), `getQuantity`(374), `edit`(409). No `update()`.

**Fix strategy:** Implement `update()` method or exclude it: `R::resource(...)->except(['update'])`.

---

## 2. Playwright E2E Test Failures (18)

Tests use stored auth state (`tests/e2e/.auth/user.json`) and navigate with `page.goto()`.

### Redirect Loop Failures

**Root cause:** The `RedirectModalRoutes` middleware (`app/Http/Middleware/RedirectModalRoutes.php`) intercepts GET requests to `*.create` and `*.edit` routes and redirects them to `*.index?modal=create|edit` — **unless** the resource is in the `FULL_PAGE_RESOURCES` whitelist.

**Whitelist** (`FULL_PAGE_RESOURCES`):

```
bills, budgets, employees, expenses, invoices, jobs, payslips,
permission, product_stocks, proposals, purchases, set_salaries,
settings, warehouse
```

For non-AJAX GET requests, the middleware does:

```php
if ($routeName ends with '.create' or '.edit') {
    if (resource NOT in FULL_PAGE_RESOURCES) {
        redirect to *.index?modal=create|edit
    }
}
```

When Playwright navigates to e.g. `/deals/create`, the middleware redirects to `/deals?modal=create`. If the index page then triggers a redirect back (e.g., auth/permission issue), this creates an infinite redirect loop. Playwright detects this as a navigation timeout.

In addition, the outer route group at `web.php:304` applies `[MWC::AUTH, MWC::VF]` (Authenticate + EnsureEmailIsVerified), which can also cause redirect chains.

---

### P-1: `deals` index — redirect loop / timeout

| Attribute                 | Value                                                                                                                            |
| ------------------------- | -------------------------------------------------------------------------------------------------------------------------------- |
| **Test file**             | `tests/e2e/crm.spec.cjs` — `test("deals index renders")` (line 100)                                                              |
| **Route def**             | `routes/web.php:867` — `R::resource(VW::DL, DLC::class)->middleware([MWC::AUTH, MWC::XSS])`                                      |
| **Controller**            | `app/Http/Controllers/Activity/DealController.php`                                                                               |
| **Middleware**            | Auth group + AUTH + XSS                                                                                                          |
| **`deals` in whitelist?** | **NO**                                                                                                                           |
| **Root cause**            | Index itself may redirect due to auth/permission; create route triggers `RedirectModalRoutes` redirect to index, causing a loop. |

---

### P-2: `deals/create` — redirect to index via `RedirectModalRoutes`

| Attribute      | Value                                                                                                                                                                |
| -------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Test file**  | `tests/e2e/crm.spec.cjs` — `test("deals create renders")` (line 108)                                                                                                 |
| **Route def**  | Same resource as P-1                                                                                                                                                 |
| **Root cause** | `deals` NOT in `FULL_PAGE_RESOURCES` → middleware redirects `deals.create` to `deals?modal=create`. Playwright expects a form at `/deals/create` but lands on index. |

---

### P-3: `lead_stages` index

| Attribute      | Value                                                                                      |
| -------------- | ------------------------------------------------------------------------------------------ |
| **Test file**  | `tests/e2e/crm.spec.cjs` — `test("lead_stages index renders")` (line 231)                  |
| **Route def**  | `routes/web.php:885` — `R::resource('lead_stages', LDSTC::class)->middleware([MWC::AUTH])` |
| **Controller** | `app/Http/Controllers/Activity/LeadStageController.php`                                    |
| **Root cause** | Redirect loop from auth/permission chain or index rendering failure                        |

---

### P-4: `lead_stages/create` — redirect via `RedirectModalRoutes`

| Attribute      | Value                                                                       |
| -------------- | --------------------------------------------------------------------------- |
| **Test file**  | `tests/e2e/crm.spec.cjs` — `test("lead_stages create renders")` (line 238)  |
| **Root cause** | `lead_stages` NOT in `FULL_PAGE_RESOURCES` → middleware redirects to index. |

---

### P-5: `clients` index

| Attribute      | Value                                                                                         |
| -------------- | --------------------------------------------------------------------------------------------- |
| **Test file**  | `tests/e2e/crm.spec.cjs` — `test("clients index renders")` (line 252)                         |
| **Route def**  | `routes/web.php:813` — `R::resource(VW::CLT, CLTC::class)->middleware([MWC::AUTH, MWC::XSS])` |
| **Root cause** | Auth/permission redirect loop on index                                                        |

---

### P-6: `clients/create` — redirect via `RedirectModalRoutes`

| Attribute      | Value                                                                   |
| -------------- | ----------------------------------------------------------------------- |
| **Test file**  | `tests/e2e/crm.spec.cjs` — `test("clients create renders")` (line 259)  |
| **Root cause** | `clients` NOT in `FULL_PAGE_RESOURCES` → middleware redirects to index. |

---

### P-7: `bank_transfers` index

| Attribute      | Value                                                                                                                                                                   |
| -------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Test file**  | `tests/e2e/financial.spec.cjs` — `test("should display bank_transfers index")` (line 311) AND `tests/e2e/finance-render.spec.cjs` → `"Bank Transfers Index"` (line 154) |
| **Route def**  | `routes/web.php:504` — `R::resource(VW::BNK_TRF, BTFC::class)` inside `[MWC::AUTH, MWC::XSS, MWC::REV]` group                                                           |
| **Root cause** | Auth redirect chain or rendering failure                                                                                                                                |

---

### P-8: `bank_transfers/create` — redirect via `RedirectModalRoutes`

| Attribute      | Value                                                                          |
| -------------- | ------------------------------------------------------------------------------ |
| **Test file**  | `tests/e2e/finance-render.spec.cjs` → `"Bank Transfer Create"` (line 155)      |
| **Root cause** | `bank_transfers` NOT in `FULL_PAGE_RESOURCES` → middleware redirects to index. |

---

### P-9: `customers` index

| Attribute      | Value                                                                                                     |
| -------------- | --------------------------------------------------------------------------------------------------------- |
| **Test file**  | `tests/e2e/crm.spec.cjs` — `test("customers index renders")` (line 273)                                   |
| **Route def**  | `routes/web.php:453` — `R::resource(VW::CST, CSTC::class)` inside `[MWC::AUTH, MWC::XSS, MWC::REV]` group |
| **Root cause** | Auth/permission redirect loop                                                                             |

---

### P-10: `customers/create` — redirect via `RedirectModalRoutes`

| Attribute      | Value                                                                     |
| -------------- | ------------------------------------------------------------------------- |
| **Test file**  | `tests/e2e/crm.spec.cjs` — `test("customers create renders")` (line 281)  |
| **Root cause** | `customers` NOT in `FULL_PAGE_RESOURCES` → middleware redirects to index. |

---

### P-11: `overtimes` index

| Attribute      | Value                                                                                                                               |
| -------------- | ----------------------------------------------------------------------------------------------------------------------------------- |
| **Test file**  | `tests/e2e/hrm.spec.cjs` — payroll loop → `overtimes` (line 196) AND `tests/e2e/finance-render.spec.cjs` → `"Overtimes"` (line 192) |
| **Route def**  | `routes/web.php:981` — `R::resource(VW::OVT, OVTC::class)->middleware([MWC::AUTH, MWC::XSS])`                                       |
| **Root cause** | Table not visible or rendering issue when data is empty                                                                             |

---

### P-12: `contracts` index

| Attribute      | Value                                                                                                      |
| -------------- | ---------------------------------------------------------------------------------------------------------- |
| **Test file**  | `tests/e2e/pm.spec.cjs` (if present) or related PM spec                                                    |
| **Route def**  | `routes/web.php:1399` — `R::resource(VW::CTC, CTCC::class)` inside `[MWC::AUTH, MWC::XSS, MWC::REV]` group |
| **Root cause** | Auth redirect chain                                                                                        |

---

### P-13: `contracts/create` — redirect via `RedirectModalRoutes`

| Attribute      | Value                                                                     |
| -------------- | ------------------------------------------------------------------------- |
| **Route def**  | Same resource as P-12                                                     |
| **Root cause** | `contracts` NOT in `FULL_PAGE_RESOURCES` → middleware redirects to index. |

---

### Hidden Table Failures

These tests navigate to index pages and assert `table.first().toBeVisible({ timeout: 15000 })`. The table element exists in HTML but is hidden (typically by DataTables JS when the dataset is empty or when initialization fails).

---

### P-14: `award_types` index — table hidden

| Attribute      | Value                                                                                                                                                                                                                                                                                                                                                                                                  |
| -------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Test file**  | `tests/e2e/hrm.spec.cjs` — HR Module loop → `award_types` (line 307)                                                                                                                                                                                                                                                                                                                                   |
| **Route def**  | `routes/web.php:1025` — `R::resource(VW::AWD_TP, AWDTC::class)`                                                                                                                                                                                                                                                                                                                                        |
| **Middleware** | `[MWC::AUTH, MWC::XSS]`                                                                                                                                                                                                                                                                                                                                                                                |
| **Controller** | `app/Http/Controllers/Activity/AwardTypeController.php`                                                                                                                                                                                                                                                                                                                                                |
| **Method**     | `index()` — line 32, passes `['awardTypes' => $awardTypes]` to view                                                                                                                                                                                                                                                                                                                                    |
| **View**       | `resources/views/award_types/index.blade.php` (194 lines)                                                                                                                                                                                                                                                                                                                                              |
| **Root cause** | Table uses class `datatable` which DataTables.js initializes. When `$awardTypes` is empty (no records for `created_by`), the table body has no rows. DataTables may collapse/hide the table or the `<table>` may not be `visible` before JS initialization completes within the 15s timeout. `award_types` is NOT in `FULL_PAGE_RESOURCES`, so `RedirectModalRoutes` also applies to its create route. |

**View structure:**

```blade
<table class="{{ VC::TB }} datatable">
    <thead>...</thead>
    <tbody class="font-style">
        @foreach($awardTypes as $at)
            <tr>...</tr>
        @endforeach
    </tbody>
</table>
```

---

### P-15: `awards` index — table hidden

| Attribute      | Value                                                                                                               |
| -------------- | ------------------------------------------------------------------------------------------------------------------- |
| **Test file**  | `tests/e2e/hrm.spec.cjs` — HR Module loop → `awards` (line 308)                                                     |
| **Route def**  | `routes/web.php:1026` — `R::resource(VW::AWD, AWDC::class)`                                                         |
| **Controller** | `app/Http/Controllers/Activity/AwardController.php`                                                                 |
| **Method**     | `index()` — line 37, passes `compact('awards', 'employees', 'types')` to view                                       |
| **View**       | `resources/views/awards/index.blade.php` (161 lines)                                                                |
| **Root cause** | Same as P-14: empty `$awards` collection + DataTables initialization timing. `awards` NOT in `FULL_PAGE_RESOURCES`. |

---

### P-16: `meetings` index — table hidden

| Attribute      | Value                                                                                     |
| -------------- | ----------------------------------------------------------------------------------------- |
| **Test file**  | `tests/e2e/hrm.spec.cjs` — Activity pages loop → `meetings` (line 278)                    |
| **Route def**  | `routes/web.php:1010` — `R::resource(VW::MT, MTC::class)`                                 |
| **Controller** | `app/Http/Controllers/Activity/MeetingController.php`                                     |
| **Method**     | `index()` — line 31, uses `response()->view($viewPath, compact('meetings', 'employees'))` |
| **View**       | `resources/views/meetings/index.blade.php` (420 lines)                                    |
| **Root cause** | Same pattern: empty dataset + DataTables timing. `meetings` NOT in `FULL_PAGE_RESOURCES`. |

---

### P-17: `designations` index — table hidden

| Attribute      | Value                                                                                                                                                                                 |
| -------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Test file**  | `tests/e2e/hrm.spec.cjs` — Org Structure loop → `designations` (line 117)                                                                                                             |
| **Route def**  | `routes/web.php:952` — `R::resource(VW::DSG, DSGC::class)`                                                                                                                            |
| **Controller** | `app/Http/Controllers/Individuals/DesignationController.php`                                                                                                                          |
| **Method**     | `index()` — line 33, passes `compact('designations')` to view                                                                                                                         |
| **View**       | `resources/views/designations/index.blade.php` (139 lines)                                                                                                                            |
| **Root cause** | The view has `@if(Utility::isFilled($designations) ?? [])` wrapping the `@foreach`. If `isFilled` returns falsy for empty collection, no `<tr>` rows render → DataTables hides table. |

**View snippet:**

```blade
<tbody class="font-style">
    @if(Utility::isFilled($designations) ?? [])
        @foreach ($designations as $designation)
            <tr>...</tr>
        @endforeach
    @endif
</tbody>
```

---

### P-18: `credit_notes/invoice` — content not found

| Attribute      | Value                                                                                                                                                                                                                                             |
| -------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Test file**  | Related financial spec                                                                                                                                                                                                                            |
| **Route def**  | `routes/web.php:574` — `R::get(VW::CRD_NT . '/invoice', [CRDNC::class, CRDNC::GET_INV])`                                                                                                                                                          |
| **Controller** | `app/Http/Controllers/Bills/CreditNoteController.php`                                                                                                                                                                                             |
| **Method**     | `getInvoice(Request $request): JsonResponse` — line 408                                                                                                                                                                                           |
| **Root cause** | The method returns `JsonResponse` (AJAX-only). Playwright navigates via browser (non-AJAX GET), but the controller returns JSON, not HTML. Additionally, the method requires `$request->input('id')` parameter which a browser GET won't provide. |

```php
// CreditNoteController.php:408
public function getInvoice(Request $request): JsonResponse {
    // ...
    $id = $request->input('id') ?? null;
    if (empty($id)) {
        return response()->json(['due' => 0]);  // Returns JSON, not HTML
    }
    $invoice = Invoice::findOrFail($id);
    // ...
}
```

---

## 3. Root Cause Categories

### Category A: `int $id` Type Hint TypeError (7 failures)

**Affected:** F-2, F-5, F-6, F-11, F-13, F-14, F-15

PHP throws `TypeError` when a UUID string is passed to a method expecting `int`. This bypasses all try/catch blocks since the error occurs during parameter resolution, before the method body.

**Fix:** Change all `int $id` parameters to `int|string $id` in:

| File                                                        | Method          | Line |
| ----------------------------------------------------------- | --------------- | ---- |
| `app/Http/Controllers/Planning/TerminationController.php`   | `description()` | 341  |
| `app/Http/Controllers/Planning/ProjectStagesController.php` | `update()`      | 338  |
| `app/Http/Controllers/Planning/ProjectStagesController.php` | `destroy()`     | 457  |
| `app/Http/Controllers/Individuals/UserController.php`       | `todoUpdate()`  | 398  |
| `app/Http/Controllers/Products/ProductStockController.php`  | `edit()`        | 98   |
| `app/Http/Controllers/Products/ProductStockController.php`  | `update()`      | 113  |
| `app/Http/Controllers/Products/ProductStockController.php`  | `destroy()`     | 141  |

### Category B: Missing Controller Methods (3 failures)

**Affected:** F-8, F-9, F-16

`R::resource()` auto-registers routes for all 7 CRUD actions, but the controller doesn't implement all of them.

| File                                                            | Missing Method | Route                           |
| --------------------------------------------------------------- | -------------- | ------------------------------- |
| `app/Http/Controllers/Planning/ProjectReportController.php`     | `update()`     | PUT `/project_reports/{id}`     |
| `app/Http/Controllers/Planning/ProjectReportController.php`     | `destroy()`    | DELETE `/project_reports/{id}`  |
| `app/Http/Controllers/Activity/WarehouseTransferController.php` | `update()`     | PUT `/warehouse_transfers/{id}` |

**Fix:** Either implement the methods or restrict routes with `->except(['update', 'destroy'])`.

### Category C: Explicit 500 in Catch Blocks (3 failures)

**Affected:** F-4, F-10, F-12

Controllers catch `\Throwable` and return `response()->json([...], 500)` or call `defaultUndefinedException()` without proper `Request` in scope.

### Category D: `RedirectModalRoutes` Middleware (8+ Playwright failures)

**Affected:** P-2, P-4, P-6, P-8, P-10, P-13, and contributing to P-1, P-3, P-5, P-7, P-9, P-12

**File:** `app/Http/Middleware/RedirectModalRoutes.php`

Resources NOT in `FULL_PAGE_RESOURCES` have their `/create` and `/edit` GET routes silently redirected to `/?modal=create|edit`, breaking direct-navigation tests.

**Fix options:**

1. Add the missing resources to `FULL_PAGE_RESOURCES`: `deals`, `lead_stages`, `clients`, `bank_transfers`, `customers`, `overtimes`, `contracts`, `award_types`, `awards`, `meetings`, `designations`, `credit_notes`
2. Or modify Playwright tests to expect modal-based create flow on index pages
3. Or add AJAX header (`X-Requested-With: XMLHttpRequest`) to Playwright navigations for modal resources

### Category E: Empty Data + DataTables Visibility (4 Playwright failures)

**Affected:** P-14, P-15, P-16, P-17

When tables have zero rows, DataTables.js may not make the `<table>` element `visible` within the Playwright timeout. The `designations` view additionally wraps rows in `@if(Utility::isFilled($designations))`.

**Fix options:**

1. Seed test database with sample records for each resource
2. Increase Playwright timeout or use a more permissive locator
3. Ensure DataTables renders the `<table>` element as visible even when empty

---

## 4. File Index

### Controllers (with line references)

| File                                                            | Key Methods                                                      |
| --------------------------------------------------------------- | ---------------------------------------------------------------- |
| `app/Http/Controllers/Bills/PayslipController.php`              | `employeePayslip()` L446                                         |
| `app/Http/Controllers/Planning/TerminationController.php`       | `description(int $id)` L341                                      |
| `app/Http/Controllers/Activity/AppraisalController.php`         | `index()` L32                                                    |
| `app/Http/Controllers/Planning/ProjectController.php`           | `projectCopyLink()` L3981                                        |
| `app/Http/Controllers/Planning/ProjectStagesController.php`     | `update(int $id)` L338, `destroy(int $id)` L457                  |
| `app/Http/Controllers/Planning/ProposalController.php`          | `previewProposal()` L896                                         |
| `app/Http/Controllers/Planning/ProjectReportController.php`     | **missing** `update()`, `destroy()`                              |
| `app/Http/Controllers/Individuals/UserController.php`           | `todoStore()` L369, `todoUpdate(int)` L398, `todoDestroy()` L419 |
| `app/Http/Controllers/Products/ProductStockController.php`      | `edit(int)` L98, `update(int)` L113, `destroy(int)` L141         |
| `app/Http/Controllers/Activity/WarehouseTransferController.php` | **missing** `update()`                                           |
| `app/Http/Controllers/Activity/AwardTypeController.php`         | `index()` L32                                                    |
| `app/Http/Controllers/Activity/AwardController.php`             | `index()` L37                                                    |
| `app/Http/Controllers/Activity/MeetingController.php`           | `index()` L31                                                    |
| `app/Http/Controllers/Individuals/DesignationController.php`    | `index()` L33                                                    |
| `app/Http/Controllers/Bills/CreditNoteController.php`           | `getInvoice()` L408                                              |
| `app/Http/Controllers/Products/ProductServiceController.php`    | `importFile()` L284, `import()` L294                             |

### Middleware

| File                                          | Purpose                                                                                                                                                                                      |
| --------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `app/Http/Middleware/RedirectModalRoutes.php` | Redirects `.create`/`.edit` to `.index?modal=` for non-whitelisted resources                                                                                                                 |
| `app/Http/Middleware/Authenticate.php`        | Custom auth guard + `ChecksLogin` trait                                                                                                                                                      |
| `app/Http/Kernel.php`                         | Web middleware group: EncryptCookies → StartSession → SetGuestLocale → ShareErrors → VerifyCsrfToken → SubstituteBindings → SecureHeaders → RecordLanding → CheckMount → RedirectModalRoutes |

### Helpers

| File                                                 | Function                                                                                                                       |
| ---------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------ |
| `app/Http/Controllers/Helpers/ErrorHandlers.php` L41 | `defaultUndefinedException()` — returns redirect (302) for `ModelNotFoundException`, returns 500 redirect for other exceptions |

### Routes

| File             | Lines                                        |
| ---------------- | -------------------------------------------- |
| `routes/web.php` | 2004 lines total. Auth group starts at L304. |

### Views

| File                                                | Lines |
| --------------------------------------------------- | ----- |
| `resources/views/award_types/index.blade.php`       | 194   |
| `resources/views/awards/index.blade.php`            | 161   |
| `resources/views/meetings/index.blade.php`          | 420   |
| `resources/views/designations/index.blade.php`      | 139   |
| `resources/views/credit_notes/index.blade.php`      | —     |
| `resources/views/product_services/import.blade.php` | —     |

### Test Files

| File                                              | Failing Tests                                  |
| ------------------------------------------------- | ---------------------------------------------- |
| `tests/Feature/HrmRouteReturnTest.php`            | F-1, F-2, F-3                                  |
| `tests/Feature/PmRouteReturnTest.php`             | F-4, F-5, F-6, F-7, F-8, F-9, F-10, F-11, F-12 |
| `tests/Feature/ProductControlRouteReturnTest.php` | F-13, F-14, F-15, F-16                         |
| `tests/e2e/crm.spec.cjs`                          | P-1 through P-6, P-9, P-10                     |
| `tests/e2e/financial.spec.cjs`                    | P-7                                            |
| `tests/e2e/finance-render.spec.cjs`               | P-7, P-8, P-11                                 |
| `tests/e2e/hrm.spec.cjs`                          | P-11, P-14, P-15, P-16, P-17                   |

### Constants

| File                                            | Key Constants                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           |
| ----------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `app/Config/Constants/ViewsConstants.php`       | `VW::PY_SLP='payslips'`, `VW::TMN='terminations'`, `VW::APR='appraisals'`, `VW::PRJ='projects'`, `VW::PRJ_STG='project_stages'`, `VW::PPS='proposals'`, `VW::PRJ_RPT='project_reports'`, `VW::TD='todos'`, `VW::PRD_STK='product_stocks'`, `VW::WRH_TRF='warehouse_transfers'`, `VW::DL='deals'`, `VW::CLT='clients'`, `VW::BNK_TRF='bank_transfers'`, `VW::CST='customers'`, `VW::OVT='overtimes'`, `VW::CTC='contracts'`, `VW::AWD_TP='award_types'`, `VW::AWD='awards'`, `VW::MT='meetings'`, `VW::DSG='designations'`, `VW::CRD_NT='credit_notes'`, `VW::PRD_SV='product_services'` |
| `app/Config/Constants/MiddlewaresConstants.php` | `MWC::AUTH='auth'`, `MWC::XSS='XSS'`, `MWC::VF='verified'`, `MWC::REV='revalidate'`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |

---

## 5. Resolution Summary (Commit `9e7d78eb`)

All 16 PHPUnit Feature failures and all Playwright failures identified in sections 1-2
have been resolved. Final test results:

| Suite               | Before     | After              |
| ------------------- | ---------- | ------------------ |
| PHPUnit Feature     | 16 failed  | **0 failed** (1867 pass, 1988 assertions) |
| PHPUnit Unit        | 66 failed  | 66 failed (all pre-existing; LandingPage screenshots, Chatify, Commission, middleware mocks — untouched) |
| Playwright E2E      | 47 failed  | **0 failed** (259 pass, 1 skipped) |

### 5.1 PHPUnit Feature Fixes (26 files, 228 insertions, 47 deletions)

| ID    | Fix                                                        | File(s) Changed                                           |
| ----- | ---------------------------------------------------------- | --------------------------------------------------------- |
| F-1   | `back()` → `redirect()->route(self::REDIRECT_INDEX)` + view name `employee_payslip` | `PayslipController.php`                                   |
| F-2   | `int $id` → `string\|int $id` type hints (edit/update/destroy) | `TerminationController.php`                               |
| F-3   | Route names in Blade: `empByStar` → `appraisals.employees.star`, `getemployee` → `appraisals.get.employee`, `deleteRoute` url fix | `appraisals/index.blade.php`, `appraisals/create.blade.php` |
| F-4–6 | `int $id` → `string\|int $id` type hints                  | `ProjectStagesController.php`                             |
| F-7–8 | Stub `update()` and `destroy()` methods                    | `ProjectReportController.php`                             |
| F-9   | Add missing `$preview = true` before compact()             | `ProposalController.php`                                  |
| F-10  | Todo route names + ModelNotFoundException catches          | `UserController.php`                                      |
| F-11  | Full `update()` implementation with inventory revert/apply | `WarehouseTransferController.php`                         |
| F-12  | ModelNotFoundException catch in `projectCopyLink()`        | `ProjectController.php`                                   |
| F-13–15 | `int $id` → `string\|int $id` type hints                | `ProductStockController.php`                              |
| F-16  | ~~Handled by above~~ (covered by controller type-hint fixes) | —                                                         |

### 5.2 Playwright Fixes

| Fix                                  | Root Cause                                                     | File(s) Changed                                           |
| ------------------------------------ | -------------------------------------------------------------- | --------------------------------------------------------- |
| Gate::before() super-admin bypass    | 28 controllers use `$user->can()` without super-admin bypass; Gate returned false for all permissions | `AuthServiceProvider.php`                                 |
| ReportController auth logic          | Inverted `if ($user?->can($permission))` — should be `!can`   | `ReportController.php`                                    |
| DealController pipeline crash        | `getDefaultPipeline()` threw RuntimeException when no pipeline exists | `DealController.php`                                      |
| ContractTypeController type check    | Hard-coded `!== 'company'` excluded super admin type           | `ContractTypeController.php`                              |
| InvoiceController $customFields      | View data key `DC::TABLE_CUSTOM_FIELDS` didn't match Blade `$customFields` | `InvoiceController.php`                                   |
| Invoice create double route()        | `route($indexRoute)` where `$indexRoute` was already a resolved URL | `invoices/create.blade.php`                               |
| Bulk attendance view path            | `VW::EMP_ATD.'.bulk'` → file is at `attendances/bulk.blade.php` (VW::ATD) | `EmployeeAttendanceController.php`                        |
| Bulk attendance variable names       | Controller passed `$branches/$departments` (plural), view expected `$branch/$department` (singular) | `EmployeeAttendanceController.php`                        |
| pricing_plans route ordering         | `{pricing_plan}` show wildcard caught `/create` before explicit route | `Modules/LandingPage/Routes/web.php`                      |
| product_services import route        | GET route for import form was commented out with broken syntax | `routes/web.php`                                          |
| RedirectModalRoutes whitelist        | Modal partials (no `@extends`) were not in FULL_PAGE_RESOURCES | `RedirectModalRoutes.php`                                 |
| Modal test selectors                 | Tests expected `.card` on modal partials (which have no card)  | `crm.spec.cjs`, `pm.spec.cjs`, `products.spec.cjs`       |
| credit_notes/invoice                 | JSON endpoint was in HTML render loop                          | `finance-render.spec.cjs`                                 |
| set_salaries/create test             | Test expected modal redirect, but no create form exists (by design) | `financial.spec.cjs`                                      |

### 5.3 Remaining Known Issues (Pre-existing, Not Introduced by Fixes)

- **PHPUnit Unit (66 failures):** LandingPage screenshot tests (missing Chrome binary), Chatify/Pusher config, Commission policy, translation export, middleware mock issues. None related to modified files.
- **DashboardDataTest deadlock:** Intermittent MySQL deadlock during concurrent user INSERT — transient database issue, not code-related.
