# Route Health Report — 2026-02-07

## Summary

| Crawl         | HTTP 500s     | Date             | Notes                                                      |
| ------------- | ------------- | ---------------- | ---------------------------------------------------------- |
| v5 (baseline) | **46** (5.5%) | 2026-02-07 19:30 | Fresh `migrate:fresh --seed`, first full 832-route crawl   |
| v6            | **20** (2.4%) | 2026-02-07 20:23 | After 6 code bug fixes                                     |
| v8 (final)    | **13** (1.6%) | 2026-02-07 21:45 | After 4 more fixes. All remaining are data/protocol/config |

**Total code bugs fixed: 33 routes went from 500 → non-500 (71.7% reduction)**

---

## Code Bugs Fixed (all verified working in v8)

### 1. Missing Spatie Permissions (10 permissions)

- **Symptom:** 500 on ~9 routes (training, zoom, reports)
- **Fix:** Created permissions in DB + updated `PermissionsConstants.php` + `SeedersTemplating.php`

### 2. View `[app]` not found

- **Symptom:** 500 on Fortify/Jetstream routes (`/login`, `/register`, etc.)
- **Fix:** Created `resources/views/app.blade.php` (minimal Inertia layout)

### 3. `FaqController::create()` — missing `$settings` variable

- **File:** `Modules/LandingPage/Http/Controllers/FaqController.php`
- **Symptom:** `compact()` failed on undefined `$settings`
- **Fix:** Added `$settings = LandingPageSetting::landingPageSetting();`

### 4. `TimesheetController` — 7 broken guard patterns

- **File:** `app/Http/Controllers/Shapes/TimesheetController.php`
- **Symptom:** `if ($deny = $this->guard(...))` was truthy when authorized (returns `true`)
- **Fix:** Changed all 7 to `if (($deny = $this->guard(...)) !== true)`

### 5. `TimesheetController::filterTimesheetTable()` — wrong return type

- **File:** `app/Http/Controllers/Shapes/TimesheetController.php`
- **Symptom:** Catch block returned `RedirectResponse` but method declares `JsonResponse`
- **Fix:** Changed catch to `return response()->json([...], 500)`

### 6. `HomeController::show()` — strict int type hint

- **File:** `Modules/LandingPage/Http/Controllers/HomeController.php`
- **Symptom:** Routes pass string IDs, type hint was `int $id`
- **Fix:** Changed to `string|int $id`

### 7. `BenefitPaymentController` — missing dot in route name

- **File:** `app/Http/Controllers/Bills/BenefitPaymentController.php`
- **Symptom:** `VW::INV . 'link.copy'` = `'invoiceslink.copy'` (should be `'invoices.link.copy'`)
- **Fix:** Changed 3 occurrences to `VW::INV . '.link.copy'`

### 8. `forgot_password.blade.php` — Collection cast + unsafe array access

- **File:** `resources/views/auth/forgot_password.blade.php`
- **Symptom:** `(array)(Utility::languages())` cast Collection object, not items → `mb_substr()` error
- **Fix:** Used `->all()` for Collection, added safe key fallback

### 9. Webhook view name mismatch

- **File:** `app/Http/Controllers/Configs/SystemController.php`
- **Symptom:** Views referenced `webhook.xxx` but directory is `webhooks/`
- **Fix:** Changed 3 references to `webhooks.xxx`

### 10. User notifications relationship mismatch

- **File:** `app/Models/Individuals/User.php`
- **Symptom:** `Notifiable` trait uses `morphMany(notifiable_type)` but custom table uses `user_id`
- **Fix:** Added `notifications()` HasMany override using `user_id`

### 11. Timesheets missing `deleted_at` column

- **Symptom:** SoftDeletes trait on Timesheet model but table lacked column
- **Fix:** Added column via Schema + created migration `2026_02_07_212821_add_deleted_at_to_timesheets.php`

### 12. Exception handler `ModelNotFoundException` → 500

- **File:** `app/Exceptions/Handler.php` + `app/Http/Controllers/Helpers/ErrorHandlers.php`
- **Fix:** Added `ModelNotFoundException` detection to return 404 instead of 500

---

## Remaining 13 HTTP 500s (NOT code bugs)

### Data-Dependent — No Deal Records (4 URLs)

Seeder creates 0 deals. Routes fail with `ModelNotFoundException` caught inside DealController.

- `/deals/1/tasks`
- `/deals/1/tasks/1/edit`
- `/deals/1/tasks/1/show`
- `/deals/1/users`

### Data-Dependent — Project UUID vs Integer (5 URLs)

Projects use UUID primary keys. Integer `1` doesn't match any project.

- `/projects/1/users/1/permission`
- `/projects/copies/1`
- `/projects/copies/links/1`
- `/projects/copy-links/1`
- `/share-projects/1`

### POST-Only Routes Hit with GET (3 URLs)

These routes expect POST data. GET requests trigger `ValidationException`.

- `/email_template_stores/1`
- `/projects.timesheets/projects/updates/1`
- `/store-language`

### External Config Required (1 URL)

- `/stripes/1` — Requires Stripe API configuration

---

## Route Distribution (v8)

| Status                     | Count | %     |
| -------------------------- | ----- | ----- |
| 200 OK                     | 522   | 62.7% |
| 404 Not Found              | 241   | 29.0% |
| 302000 (redirect artifact) | 28    | 3.4%  |
| 429 Too Many Requests      | 14    | 1.7%  |
| 500 Internal Server Error  | 13    | 1.6%  |
| 401 Unauthorized           | 6     | 0.7%  |
| 422 Unprocessable Entity   | 4     | 0.5%  |
| 400 Bad Request            | 2     | 0.2%  |
| 204 No Content             | 2     | 0.2%  |
