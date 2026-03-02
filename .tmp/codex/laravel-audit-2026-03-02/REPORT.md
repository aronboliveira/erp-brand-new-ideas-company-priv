# Laravel Audit Report

Date: 2026-03-02
Scope: `_inc/laravel/`
Method: static review of routes, middleware, controllers, helpers, and dependency baseline. No runtime tests were executed.

## Critical Findings

### 1. Unauthenticated arbitrary file upload endpoints exist under the public web root
Evidence:
- `_inc/laravel/public/assets/json/file-upload.php:1-8`
- `_inc/laravel/public/Modules/landingpage/json/file-upload.php:1-8`
- `_inc/laravel/frontend/app/public/assets/json/file-upload.php:1-8`
- `_inc/laravel/public/.htaccess`
- `_inc/laravel/frontend/app/public/.htaccess`

Why this is serious:
- These scripts bypass Laravel completely.
- They accept any uploaded file, preserve the attacker-controlled filename, and write it into a public directory.
- The shipped `.htaccess` files route existing files directly and do not block PHP execution in subdirectories.

Impact:
- If PHP execution is enabled for those directories, this is direct remote code execution via web shell upload.
- Even without PHP execution, it still allows arbitrary public file hosting and storage abuse.

Recommendation:
- Delete these scripts immediately.
- Remove any uploaded files already placed in those `uploads/` folders.
- Block PHP execution in any user-writeable directory.

### 2. Authenticated API path traversal allows arbitrary file writes
Evidence:
- `_inc/laravel/app/Http/Controllers/Configs/ApiController.php:295-306`

Why this is serious:
- `uploadImage()` trusts both `trackerId` and `imgName` from the request.
- The path is built with `storage_path("uploads/trackerImages/{$trackerId}/")` and then written with `file_put_contents(...)`.
- There is no normalization, no allowlist, no generated filename, and no ownership check.

Impact:
- An authenticated user can write attacker-controlled content to attacker-chosen paths using `../`.
- That can overwrite application files or place executable files in web-accessible locations.

Recommendation:
- Reject any path separators in client input.
- Generate server-side directory names and filenames.
- Validate the tracker belongs to the caller before writing.
- Enforce MIME, size, and base64 integrity checks.

### 3. Session, bearer, CSRF, cookie, and header secrets are logged
Evidence:
- `_inc/laravel/app/Http/Middleware/VerifyCsrfToken.php:35-48`
- `_inc/laravel/app/Http/Middleware/VerifyCsrfToken.php:58-72`
- `_inc/laravel/app/Http/Middleware/VerifyCsrfToken.php:155-173`
- `_inc/laravel/app/Exceptions/Handler.php:82-92`
- `_inc/laravel/app/Exceptions/Handler.php:160-170`
- `_inc/laravel/app/Http/Middleware/XSS.php:34-42`

Why this is serious:
- The code logs session tokens, bearer tokens, CSRF tokens, response cookie values, full request headers, and most request payloads.
- Exception paths are especially dangerous because they also log full headers, which usually include `Cookie` and `Authorization`.

Impact:
- Anyone with log access can replay user sessions or API tokens.
- This also increases breach radius if logs are shipped to third-party observability systems.

Recommendation:
- Remove token, cookie, and authorization logging entirely.
- Redact headers by allowlist, not denylist.
- Treat logs as sensitive data and rotate existing secrets after cleanup.

## High Findings

### 4. Company policy edit/update flows lack tenant ownership checks, and delete uses the wrong permission
Evidence:
- `_inc/laravel/app/Http/Controllers/Companies/CompanyPolicyController.php:141-149`
- `_inc/laravel/app/Http/Controllers/Companies/CompanyPolicyController.php:160-177`
- `_inc/laravel/app/Http/Controllers/Companies/CompanyPolicyController.php:198`

Why this is serious:
- `edit()` and `update()` accept route-model-bound `CompanyPolicy $companyPolicy` and never verify `created_by === $u->creatorId()`.
- `destroy()` checks ownership, but it authorizes with `delete document` instead of `delete company policy`.

Impact:
- A user with the generic company-policy permission can edit another tenant's policy by ID.
- A user with document-delete permission may be able to delete company policies unintentionally.

Recommendation:
- Add ownership checks to `edit()` and `update()`.
- Replace `delete document` with the correct policy permission.
- Apply scoped route-model binding where possible.

### 5. Task and tracker mutation endpoints trust attacker-supplied IDs without ownership checks
Evidence:
- `_inc/laravel/app/Http/Controllers/Planning/ProjectController.php:972-982`
- `_inc/laravel/app/Http/Controllers/Configs/ApiController.php:215-233`
- `_inc/laravel/app/Http/Controllers/Configs/ApiController.php:244-259`

Why this is serious:
- `ganttPost()` updates whatever `task_id` resolves to and ignores whether that task belongs to `projectId` or to the caller.
- `addTracker()` can start tracking on any task ID and stop any tracker ID, again without checking ownership.

Impact:
- Users with broad task permissions can modify or stop work items they do not own.
- This is a direct integrity issue for time tracking and scheduling data.

Recommendation:
- Scope every lookup by both tenant and ownership/project membership.
- Verify the task belongs to the route project before updating.
- Verify the tracker belongs to the current user before stopping it.

### 6. Shared project links use reversible base64 passwords and no rate limiting
Evidence:
- `_inc/laravel/app/Http/Controllers/Planning/ProjectController.php:1735-1738`
- `_inc/laravel/app/Http/Controllers/Planning/ProjectController.php:1768-1779`
- `_inc/laravel/routes/web.php:226`

Why this is serious:
- The password is stored with `base64_encode`, not a password hash.
- Validation compares the incoming plaintext directly to the decoded database value.
- The public route is `Route::any(...)` with no throttle.

Impact:
- Anyone with DB access can recover shared-link passwords instantly.
- Passwords are brute-forceable online.

Recommendation:
- Hash shared-link passwords with `Hash::make()` and `Hash::check()`.
- Add rate limiting and audit logging for failed shared-link attempts.
- Restrict the route to the minimum necessary HTTP verbs.

## Stability / Crash Findings

### 7. Public job application route is wired as guest-facing, but the controller requires authentication
Evidence:
- `_inc/laravel/routes/web.php:220-221`
- `_inc/laravel/app/Http/Controllers/Companies/JobController.php:351-353`

Impact:
- Guest applicants are redirected to login before submission.
- This breaks the advertised public career flow.

Recommendation:
- Remove `_checkLogin()` from `jobApplyData()` or move the route behind auth if that is truly intended.

### 8. Dead routes are registered for methods that do not exist
Evidence:
- `_inc/laravel/routes/api.php:35-36`
- `_inc/laravel/routes/web.php:225`

Notes:
- `stop-tracker` is explicitly marked `TODO THIS METHOD DOESN'T EXIST`.
- I could not find an implementation of `ProjectController@projectCopyLink` in `_inc/laravel/app/Http/Controllers/Planning/ProjectController.php`.

Impact:
- These routes will fail at runtime when invoked.

Recommendation:
- Remove or fix the routes before exposing them.

### 9. Middleware is echoing HTML and calling `exit`, which can corrupt response handling
Evidence:
- `_inc/laravel/app/Http/Middleware/XSS.php:56-72`

Impact:
- API and AJAX requests can receive partial HTML/JS instead of valid JSON.
- The hard `exit` bypasses normal Laravel exception handling and middleware cleanup.

Recommendation:
- Return normal `Response` objects.
- Never `echo` or `exit` inside middleware.

## Performance Findings

### 10. The request lifecycle performs synchronous console/log I/O on nearly every request
Evidence:
- `_inc/laravel/app/Http/Kernel.php:105-130`
- `_inc/laravel/app/Http/Middleware/DebugRouteToConsole.php:15-18`
- `_inc/laravel/routes/web.php:162-165`
- `_inc/laravel/app/Traits/ChecksLogin.php:31-37`

Impact:
- Significant log-volume amplification.
- Extra blocking I/O on the hot path of every request.
- Risk of noisy stdout/stderr output in production workers.

Recommendation:
- Remove `ConsoleOutput` from HTTP request code paths.
- Keep request logging behind targeted debug flags only.

### 11. `projectLink()` is a DB-heavy public endpoint with repeated per-day and per-stage queries
Evidence:
- `_inc/laravel/app/Http/Controllers/Planning/ProjectController.php:1784-1884`

Why this is heavy:
- Repeated relationship counts and sums.
- A loop over seven days that performs fresh task/timesheet queries each iteration.
- Another loop over every stage that fetches tasks again.
- Additional full `Bug` and `ProjectTask` fetches before render.

Impact:
- Shared links can be used to trigger expensive query bursts against project data.

Recommendation:
- Pre-aggregate counts in grouped queries.
- Eager load once, then compute in memory where appropriate.
- Cache public/shared-link payloads.

### 12. The API returns full project graphs and logs the whole payload
Evidence:
- `_inc/laravel/app/Http/Controllers/Configs/ApiController.php:164-175`

Impact:
- `getProjects()` loads every project plus all tasks for the caller.
- The result is then logged in full, multiplying memory and log cost.

Recommendation:
- Paginate or slim the response.
- Remove payload-level logging.

### 13. `Utility::getSettingsById()` caches a single user's settings globally, without keying by user ID
Evidence:
- `_inc/laravel/app/Models/utils/Utility.php:1062-1103`

Why this is risky:
- The first call fills `self::$getSettingsId`.
- Later calls for different user IDs return the same cached array.

Impact:
- Wrong settings can bleed between users in the same request/process.
- This can produce incorrect branding, mail, storage, or integration behavior.

Recommendation:
- Cache by user ID, e.g. `self::$getSettingsId[$id]`.
- Reset per-request static caches if long-lived workers are used.

## Dependency Risk

### 14. The application is pinned to Laravel 10, which is out of security support
Evidence:
- `_inc/laravel/composer.json:20-23`
- Official Laravel support policy: https://laravel.com/docs/11.x/releases

Note:
- Laravel 10 security support ended on 2025-02-04. As of 2026-03-02, this codebase is past the supported window.

Recommendation:
- Plan an upgrade to a supported Laravel release before addressing lower-priority feature work.

## Recommended Remediation Order

1. Remove the three public upload scripts and audit any files already uploaded there.
2. Fix `ApiController::uploadImage()` path traversal and add ownership checks to tracker endpoints.
3. Strip tokens, cookies, and authorization headers from middleware and exception logs; rotate exposed secrets.
4. Add tenant ownership checks to `CompanyPolicyController` and similar route-model-bound controllers.
5. Fix dead routes and the public job-application auth mismatch.
6. Rework shared-link password storage and add throttling.
7. Remove request-path `ConsoleOutput` usage and reduce payload logging.
8. Optimize `projectLink()` and large list APIs.
9. Upgrade off Laravel 10.

## Addendum: Multi-Tenant Authorization Second Pass

Date: 2026-03-02
Scope extension: controller-by-controller review for unscoped route-model binding, raw `findOrFail()` lookups, and custom endpoints that bypass tenant ownership checks.

This pass is additive to findings 4 and 5 above. The original report already confirmed multi-tenant authorization failures in `CompanyPolicyController`, `ProjectController`, and `ApiController`. The review below shows the same pattern recurs in other controllers, but not uniformly.

### 15. `UserController` has multiple IDOR-style paths without tenant or record ownership checks
Evidence:
- `_inc/laravel/routes/web.php:266-272`
- `_inc/laravel/routes/web.php:1208-1213`
- `_inc/laravel/app/Http/Controllers/Individuals/UserController.php:194-197`
- `_inc/laravel/app/Http/Controllers/Individuals/UserController.php:216-234`
- `_inc/laravel/app/Http/Controllers/Individuals/UserController.php:251-259`
- `_inc/laravel/app/Http/Controllers/Individuals/UserController.php:402-426`
- `_inc/laravel/app/Http/Controllers/Individuals/UserController.php:524-552`

Why this matters:
- Resource edit/update/delete load arbitrary users with `User::findOrFail($id)` after permission checks, but never verify the target belongs to `request()->user()->creatorId()`.
- The password reset POST route also writes directly to `User::findOrFail($id)` with no tenant check.
- The todo update/delete endpoints load `UserToDo::findOrFail(...)` and mutate/delete it without verifying `user_id === request()->user()->id`.

Impact:
- A tenant admin with user-management permission can edit, delete, or reset passwords for users outside their tenant if they know or can discover IDs.
- Any authenticated user can toggle or delete another user's todo item by ID, including across tenants.

Recommendation:
- Scope user lookups by tenant, e.g. `where('created_by', $request->user()->creatorId())`.
- Scope todo lookups by the authenticated user's own `user_id`.
- Prefer scoped route-model binding or dedicated policy checks over raw `findOrFail()`.

### 16. `EmployeeController` mixes correctly scoped profile views with unscoped CRUD, lookup, and letter-download endpoints
Evidence:
- `_inc/laravel/routes/web.php:871-878`
- `_inc/laravel/routes/web.php:1593-1610`
- `_inc/laravel/app/Http/Controllers/Individuals/EmployeeController.php:188-196`
- `_inc/laravel/app/Http/Controllers/Individuals/EmployeeController.php:233-240`
- `_inc/laravel/app/Http/Controllers/Individuals/EmployeeController.php:258-264`
- `_inc/laravel/app/Http/Controllers/Individuals/EmployeeController.php:284-289`
- `_inc/laravel/app/Http/Controllers/Individuals/EmployeeController.php:307-314`
- `_inc/laravel/app/Http/Controllers/Individuals/EmployeeController.php:346-359`
- `_inc/laravel/app/Http/Controllers/Individuals/EmployeeController.php:391-399`
- `_inc/laravel/app/Http/Controllers/Individuals/EmployeeController.php:423-455`
- `_inc/laravel/app/Http/Controllers/Individuals/EmployeeController.php:463-470`
- `_inc/laravel/app/Http/Controllers/Individuals/EmployeeController.php:640-645`

Why this matters:
- `profileShow()` is correctly scoped with `where(created_by, $u->creatorId())`, which shows the intended tenant boundary.
- The main CRUD actions (`edit`, `update`, `destroy`, `show`) instead use bare `Employee::findOrFail($id)` with no tenant check.
- `json()` and `employeeJson()` return designations/employees by department or branch ID without `created_by` filtering.
- The joining-letter, experience-certificate, and NOC endpoints render tenant-scoped templates against `Employee::find($empId)` without verifying the employee belongs to the current tenant; the experience endpoints also query `Termination::where(employee_id, $id)` without tenant scope.

Impact:
- Authorized users can read or mutate employee records from other tenants if IDs are known.
- Authenticated users can enumerate employee names/IDs and designation data across tenants by probing branch/department IDs.
- HR document download routes can disclose cross-tenant employee data and render official letters for the wrong tenant's staff.

Recommendation:
- Align all employee record lookups with the scoped pattern already used in `profileShow()`.
- Add tenant filtering to department/branch JSON endpoints.
- Scope document-generation routes by both tenant ownership and an explicit HR permission.

### 17. `ClientController` resets passwords on raw user IDs without re-checking tenant ownership
Evidence:
- `_inc/laravel/routes/web.php:745-746`
- `_inc/laravel/app/Http/Controllers/Individuals/ClientController.php:416-420`
- `_inc/laravel/app/Http/Controllers/Individuals/ClientController.php:445-453`

Why this matters:
- The GET reset form correctly decrypts the client ID and verifies the target belongs to the caller's tenant.
- The POST reset handler then accepts a plain `{id}` route parameter and updates `User::findOrFail($id)` directly, with no `created_by` or `type === client` validation.

Impact:
- A user with `edit client` permission can reset the password of an arbitrary `users` row by posting a different numeric ID.
- Because the router entry is separate from the resource routes, this bypasses the stronger owner checks present in the controller's normal edit/update/delete flows.

Recommendation:
- Re-derive the target client from a tenant-scoped query in the POST handler.
- Keep the reset route consistent with the encrypted-ID GET flow or replace both with scoped route-model binding.

### 18. `RoleController` uses tenant-scoped creation and listing, but unscoped route-model binding for edits and deletes
Evidence:
- `_inc/laravel/routes/web.php:271-275`
- `_inc/laravel/app/Http/Controllers/Individuals/RoleController.php:36-40`
- `_inc/laravel/app/Http/Controllers/Individuals/RoleController.php:84-87`
- `_inc/laravel/app/Http/Controllers/Individuals/RoleController.php:102-121`
- `_inc/laravel/app/Http/Controllers/Individuals/RoleController.php:124-148`
- `_inc/laravel/app/Http/Controllers/Individuals/RoleController.php:151-169`

Why this matters:
- `index()` lists only roles where `created_by = $user->creatorId()`.
- `store()` writes the same tenant marker on creation.
- `edit(Request $request, Role $role)`, `update(...)`, and `destroy(...)` trust implicit route-model binding and never verify that the bound role belongs to the current tenant.

Impact:
- Users with role-management permissions can modify or delete another tenant's role definition if they obtain its identifier.
- Because roles are authorization primitives, this can cascade into permission confusion or privilege disruption across tenants.

Recommendation:
- Add explicit ownership checks before rendering, updating, or deleting bound roles.
- Consider scoped bindings or repository methods that always include `created_by`.

### 19. `JobCategoryController` leaves edit and update unscoped even though delete enforces `created_by`
Evidence:
- `_inc/laravel/routes/web.php:1005`
- `_inc/laravel/app/Http/Controllers/Companies/JobCategoryController.php:144-149`
- `_inc/laravel/app/Http/Controllers/Companies/JobCategoryController.php:176-180`
- `_inc/laravel/app/Http/Controllers/Companies/JobCategoryController.php:203-205`
- `_inc/laravel/app/Models/Companies/JobCategory.php:23-27`

Why this matters:
- `edit()` and `update()` load `JobCategory::findOrFail($id)` and never confirm tenant ownership.
- `destroy()` does perform `created_by` validation, proving the model is intended to be tenant-scoped.

Impact:
- Users with job-category edit permission can alter another tenant's recruitment taxonomy if they know the record ID.
- The partial enforcement makes this easy to miss in review because delete is protected while edit/update are not.

Recommendation:
- Reuse the same `created_by` guard in `edit()` and `update()`.
- Treat partial CRUD ownership checks as a code smell and audit sibling actions together.

### Positive control examples observed during the second pass

These controllers did perform tenant ownership checks before mutating or rendering bound records, which reduces false positives in this review and shows the intended pattern already exists in the codebase:

- `_inc/laravel/app/Http/Controllers/Bills/LoanController.php:129-133`
- `_inc/laravel/app/Http/Controllers/Bills/AllowanceController.php:152-155`
- `_inc/laravel/app/Http/Controllers/Bills/OtherPaymentController.php:78-83`
- `_inc/laravel/app/Http/Controllers/Planning/GoalTrackingController.php:170-172`
- `_inc/laravel/app/Http/Controllers/Planning/ProjectStagesController.php:118-124`
- `_inc/laravel/app/Http/Controllers/Activity/TaskStageController.php:257-260`
- `_inc/laravel/app/Http/Controllers/Activity/CommissionController.php:183-184`

Interpretation:
- The application does have an implicit tenant model based on `created_by` / `creatorId()`.
- The main issue is inconsistent enforcement across controllers and even within the same controller, not a complete absence of tenancy concepts.

### Updated remediation priority for authorization

Insert these actions near the top of the original remediation plan:

1. Fix cross-tenant write paths in `UserController`, `ClientController`, `RoleController`, `CompanyPolicyController`, `JobCategoryController`, `ProjectController`, and `ApiController`.
2. Fix cross-tenant read/disclosure paths in `EmployeeController`, especially the JSON lookup and letter-download routes.
3. Standardize controller lookups behind tenant-scoped query helpers or policies so CRUD actions cannot diverge silently.
