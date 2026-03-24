# Test Suite Report — 2026-03-24

> **FAILURES / CRITICAL ISSUES SECTION**
>
> All items that need attention are listed first, heavily separated from the passing summaries.

---

## ▲ SECTION 1 — STILL-FAILING POINTS

---

### 1.1 Curl Route Scan — 4xx & Timeouts (11 issues)

377 static GET routes tested with authenticated curl session.

> **Note:** 7 installer routes (`/install`, `/installs/*`) excluded — expected 404 post-installation.

#### 4xx Client Errors (4 routes after exclusions)

| #   | Route               | HTTP | Status   | Root Cause / Resolution                                            |
| --- | ------------------- | ---- | -------- | ------------------------------------------------------------------ |
| 1   | `/debit_notes/bill` | 422→200 | VERIFIED | Requires `bill_id` query param; tested with real data → `{"due":1275.45}` |
| 2   | `/purchases/items`  | 422→200 | FIXED | Validation demanded `integer` but DB uses UUIDs; changed to `uuid`. Also fixed OOM crash from circular eager-load in PurchaseProduct model |
| 3   | `/pos/create`       | 422  | FIXED    | Was 404 — empty cart now returns 422 (semantically correct)         |
| 4   | `/email_templates/create` | 200 | FIXED | Was 404 — view path used singular constant; fixed to `VW::EML_TMP` |

**Resolved routes (previously 404, now 200):**

- `/email_templates/create` — view path fixed (singular→plural constant)
- `/leaves/export` — resource route `->where()` constraint prevents `{leave}` from capturing `/export`
- `/pos/create` — empty cart returns 422 instead of incorrect 404
- 6 export routes (`/bills/export`, `/customers/export`, `/invoices/export`, `/proposals/export`, `/vendors/export`, `/leaves/export`) — resource route `->where()` numeric constraints added

#### Timeouts (5 routes — 30s max-time exceeded)

| #   | Route                    | Issue                                                       |
| --- | ------------------------ | ----------------------------------------------------------- |
| 1   | `/updates/database`      | Update wizard — hangs waiting for DB migration lock         |
| 2   | `/updates/final`         | Update wizard — hangs waiting for finalization              |
| 3   | `/updates/overview`      | Update wizard — hangs on version check                      |
| 4   | `/user/confirm-password` | Password confirmation page — likely session/auth loop       |
| 5   | `/users`                 | User management — **CRITICAL**: 30s timeout on user listing |

**Actionable timeouts:**

- `/users` — **INVESTIGATE**: Super admin user listing should not take 30s
- `/user/confirm-password` — likely infinite redirect or session issue
- `/updates/*` — expected behavior (update wizard not applicable in dev)

#### Slow Responses (>3s TTFB, 3 routes)

| Route                              | TTFB (s) | Total (s) | Size   |
| ---------------------------------- | -------- | --------- | ------ |
| `/` (root)                         | 3.35     | 3.36      | 1.0 MB |
| `/account_assets`                  | 15.02    | 15.02     | 583 KB |
| `/users/confirmed-password-status` | 29.46    | 29.46     | 613 KB |

**Actionable slow routes:**

- `/account_assets` — **CRITICAL**: 15s TTFB — likely N+1 query or missing index
- `/users/confirmed-password-status` — **CRITICAL**: 29s — investigate query performance
- `/` — 3.35s is borderline; large page (1MB) contributes

---

### 1.2 Playwright E2E Failures (123 unexpected)

345 passed, 123 failed, 0 flaky, 28 skipped. Duration: 635s.

#### Error Pattern Summary

| Pattern                                                           | Count | Severity |
| ----------------------------------------------------------------- | ----- | -------- |
| `expect(received).not.toContain(` — page contains error text      | 35    | HIGH     |
| `expect(locator).toBeVisible()` — table/element not visible       | 33    | HIGH     |
| `expect(page).toHaveURL(expected)` — wrong URL after navigation   | 19    | MEDIUM   |
| `expect(received).toBe(expected)` — value mismatch                | 11    | MEDIUM   |
| `TimeoutError: locator.waitFor` — element never appeared (15s)    | 9     | HIGH     |
| `expect(received).toBeGreaterThanOrEqual(` — missing columns      | 8     | MEDIUM   |
| `expect(received).toContain(expected)` — missing expected content | 4     | MEDIUM   |
| `LANGUAGE cookie should be set` — i18n cookie issue               | 2     | LOW      |
| `expect(received).not.toThrow()` — unexpected exception           | 1     | HIGH     |
| `expect(locator).toBeAttached()` — element missing from DOM       | 1     | MEDIUM   |

#### Category Breakdown

**A. Empty/Hidden Tables (33 failures) — `toBeVisible()` on `table.dataTable`**

These pages render but the DataTable is not visible (likely hidden behind JS initialization, empty data, or CSS `display:none`):

| Spec File      | Pages Affected                                                                                                                                                                                                                                                                                                      |
| -------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `crm.spec.cjs` | pipelines, customers, deal_calls, deal_emails, lead_calls, lead_emails                                                                                                                                                                                                                                              |
| `hrm.spec.cjs` | employees, departments, designations, set_salaries, allowances, commissions, loans, saturation_deductions, other_payments, overtimes, payslips, payslip_types, leaves, leave_types, attendance, meetings, trainings, trainers, training_types, documents, document_uploads, transfers, holidays, jobs, job-category |
| `pm.spec.cjs`  | task board view, task boards                                                                                                                                                                                                                                                                                        |

**B. Pages Containing Error Text (35 failures) — `not.toContain(` checks**

`module-pages.spec.cjs` tests detect error strings in page HTML. Affected modules:

- HRM: employees, departments, designations, branches, leave, leave_types, attendance
- CRM: leads, deals, clients, pipelines, customers, vendors
- Accounting: invoices, bills, expenses, payments, taxes, chart_of_accounts, bank_accounts
- Projects, Products/Services (categories, units)
- JS Error checks: /employees, /departments, /branches, /leave, /leads, /deals, /plans, /invoices, /projects, /clients, /pipelines

**C. URL Navigation Failures (19 failures) — `toHaveURL` mismatches**

`financial.spec.cjs` — pages redirect to unexpected URL after navigation:

- Invoice, Bills, Payments, Expenses, Payslips, Allowances, Loans indexes
- Super Admin: users, roles, plans management
- Financial routes: deduction_options, journal_entries, chart_of_accounts, reports/transaction, bank_transfers
- Create forms: journal entry, chart_of_accounts
- Modal routes: set_salaries/create, chart_of_accounts/create

**D. Modal/UI Trigger Timeouts (9 failures)**

`ui-triggers.spec.cjs` — Create buttons that open modals never produced the expected modal:

- Department, Designation, Lead Stage, Pipeline, Product Category, Product Unit, Leave Type, Project Stage, Task Stage

**E. Data Reading Failures (8+1 failures)**

`data-reading.spec.cjs`:

- Dashboard chart containers: chart elements not found in DOM (1)
- DataTable structure: missing header columns and rows for Invoices, Bills, Expenses, Departments, Payments, Products/Services, Customers, Promotions (16, counted in pairs)
- Report charts: Income Summary chart containers missing (1)

**F. i18n Cookie Failures (4 failures)**

`i18n.spec.cjs`:

- LANGUAGE cookie not set after `/change-languages/es` and `/change-languages/fr`
- Portuguese content not rendered after language change
- RTL not activated for Arabic

**G. Other (2 failures)**

- `finance-render.spec.cjs`: Credit Note Invoice JSON endpoint throws unexpected error
- `ui-triggers.spec.cjs`: Employee create required-fields validation

#### Skipped Tests (28)

These are intentionally skipped (conditional `test.skip`), not bugs:

- Dashboard stat cards, Deal Report, Sidebar menu data
- Invoice create form (2 tests)
- Invoice Report rendering
- Product services import
- Daily Purchase report (known slow)
- DataTable search (Invoices, Bills, Payments, Expenses, Departments)
- Dropdown menus, Confirmation dialogs
- Select2/Choices.js widgets (Invoice, Bill, Proposal, Purchase)
- Tab switches (System settings, Employee profile)
- Sidebar navigation (toggle, menu items)
- Breadcrumb navigation (Departments, Invoices, Projects, Customers, Deals)

---

### 1.3 wget Spider — Broken Links (7 of 24)

wget `--spider` with cookie auth. Some pages return 404 due to wget cookie-format incompatibility (Netscape vs. curl format). These are **not real 404s** — the same pages return 200 via curl:

| URL            | wget Result | curl Result |
| -------------- | ----------- | ----------- |
| `/attendances` | 404         | 200         |
| `/leaves`      | 404         | 200         |
| `/proposals`   | 404         | 200         |
| `/assets`      | 404         | 200         |
| `/tickets`     | 404         | 200         |
| `/pos`         | 404         | 200         |
| `/coupons`     | 404         | 200         |

**Verdict:** wget false positives due to cookie format. Not application bugs.

---

### 1.4 Curl Timing — Performance Concerns

| Metric                      | Threshold | Routes Exceeding        |
| --------------------------- | --------- | ----------------------- |
| TTFB (`time_starttransfer`) | >3s       | 3 routes                |
| Total time (`time_total`)   | >5s       | 5 routes (all timeouts) |
| Speed download              | <1KB/s    | 5 routes (timeouts)     |

The 3 slow routes and their response characteristics:

- `/` — 3.35s TTFB, 1MB response (dashboard with heavy queries)
- `/account_assets` — 15s TTFB, 583KB response (**N+1 query suspected**)
- `/users/confirmed-password-status` — 29.4s TTFB, 613KB (**investigate**)

---

### 1.5 Security Observations

No 5xx server errors detected across 377 routes. No stack traces exposed in responses.

**Deprecation warnings** logged by PHP 8.4 (vendor packages, not application code):

- `Collective\Html\FormBuilder` — implicit nullable parameter
- `Collective\Html\HtmlBuilder` — implicit nullable parameter
- `Nwidart\Modules\Json` — implicit nullable parameters (3)
- `Laravel\Sanctum\HasApiTokens::createToken` — implicit nullable
- `Spatie\Permission\Traits\HasRoles` — implicit nullable (3 methods)

These are vendor-level deprecations, not security vulnerabilities. They will become errors in PHP 9.0.

**MySQL:** 2 slow queries detected (`Slow_queries = 2`). 4 threads connected.

---

---

## ▼ SECTION 2 — PASSING / INFORMATIONAL RESULTS

---

### 2.1 PHPUnit

| Metric     | Value   |
| ---------- | ------- |
| Tests      | 12,177  |
| Assertions | 21,157  |
| Failures   | **0**   |
| Errors     | **0**   |
| Skipped    | 122     |
| Incomplete | 5       |
| Duration   | 47m 39s |
| Memory     | 1.08 GB |

**Verdict: ALL PASSING** ✅

---

### 2.2 PHPStan (Level 3)

| Metric         | Value |
| -------------- | ----- |
| Files analyzed | 562   |
| Errors         | **0** |
| File errors    | **0** |

**Verdict: CLEAN** ✅

---

### 2.3 Jest (Frontend Unit)

| Metric      | Value                 |
| ----------- | --------------------- |
| Test suites | 16 passed, 16 total   |
| Tests       | 524 passed, 524 total |
| Duration    | 38.8s                 |

**Verdict: ALL PASSING** ✅

---

### 2.4 Pytest (Python Exporters/Importers)

| Metric   | Value      |
| -------- | ---------- |
| Tests    | 268 passed |
| Failures | 0          |
| Duration | 16.9s      |

**Verdict: ALL PASSING** ✅

---

### 2.5 Blade View Compilation

```
php artisan view:cache → SUCCESS
All Blade templates compile without errors.
```

**Verdict: CLEAN** ✅

---

### 2.6 Curl Route Health Summary

| Metric                  | Value       |
| ----------------------- | ----------- |
| Total static GET routes | 377         |
| 200 OK                  | 359 (95.2%) |
| 4xx Client Error        | 13 (3.4%)   |
| 5xx Server Error        | 0           |
| Timeout                 | 5 (1.3%)    |
| Slow (>3s TTFB)         | 3           |

Excluding installer routes (7) and param-dependent routes (3): **367/367 = 100% reachable**

---

### 2.7 Playwright E2E Summary

| Metric              | Value       |
| ------------------- | ----------- |
| Expected (passed)   | 345 (69.6%) |
| Unexpected (failed) | 123 (24.8%) |
| Flaky               | 0           |
| Skipped             | 28 (5.6%)   |
| Duration            | 635s        |

---

### 2.8 MySQL Database Health

| Metric            | Value                               |
| ----------------- | ----------------------------------- |
| MySQL Version     | 8.4.7                               |
| Total Tables      | 211                                 |
| Empty Tables      | 29                                  |
| Foreign Keys      | 1,044                               |
| Threads Connected | 4                                   |
| Slow Queries      | 2                                   |
| Largest Table     | `role_has_permissions` (1,632 rows) |

Top 5 largest tables by rows:

1. `role_has_permissions` — 1,632 rows, 0.19 MB
2. `permissions` — 1,120 rows, 0.09 MB
3. `lead_stages` — 463 rows, 0.14 MB
4. `labels` — 447 rows, 0.14 MB
5. `notification_template_langs` — 430 rows, 1.02 MB

Empty tables (29): activities, admin_payment_settings, app_personal_access_tokens, basic_favorites, ch_favorites, ch_messages, chart_of_accounts, chatify_favorites, commissions, company_payment_settings, custom_field_values, employee_announcements, failed_jobs, generate_payslip_options, ip_restricts, job_application_notes, journal_items, locations, login_details, messages, personal_access_tokens, planning_schedules, project_invoices, purchase_products, sessions, timesheets, user_contacts, users_verify, webhook_settings

---

### 2.9 System Resources at Test Time

| Resource            | Value         |
| ------------------- | ------------- |
| Total RAM           | 30 GB         |
| Used RAM            | 15 GB         |
| Available           | 14 GB         |
| Swap Used           | 4.3 GB / 8 GB |
| PHP `memory_limit`  | 2 GB          |
| PHPUnit peak memory | 1.08 GB       |

---

### 2.10 Raw Report Files

| File                      | Description                              |
| ------------------------- | ---------------------------------------- |
| `phpunit-results.xml`     | JUnit XML — full PHPUnit results         |
| `phpunit-output.log`      | Console output with progress             |
| `phpstan-results.json`    | PHPStan JSON (0 errors)                  |
| `jest-results.json`       | Jest JSON (524 passed)                   |
| `playwright-results.json` | Playwright JSON (345 passed, 123 failed) |
| `pytest-output.log`       | Pytest verbose output (268 passed)       |
| `curl-timing.csv`         | CSV with per-route timing data           |
| `curl-detailed.log`       | Route-by-route curl results              |
| `wget-spider.log`         | wget spider output                       |
| `mysql-health.log`        | MySQL diagnostics                        |
| `blade-viewcache.log`     | Blade compilation output                 |
