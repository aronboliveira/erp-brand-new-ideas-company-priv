# Known / Open Issues

> Open issues only. Resolved issues are archived in `.notes/RESOLVED_ISSUES.md`.
> Last updated: 2026-05-09
> **Cross-references:** [`CURRENT_WORKING_ISSUES.md`](CURRENT_WORKING_ISSUES.md) (bug-fix sessions) · [`CURRENT_WORKING_ISSUES_WORK.md`](CURRENT_WORKING_ISSUES_WORK.md) (try/fail journal) · [`NEXT_STEPS.md`](NEXT_STEPS.md) (tasks remaining) · [`RESOLVED_ISSUES.md`](RESOLVED_ISSUES.md) (resolved archive) · [`README.md`](README.md) (notes overview)

---

## OPEN — Test Suite & Build (2026-05-09)

The current continuation handoff is
`.tmp/codex/20260509/handsoff.md`, written after finishing the failing
PHPUnit work left by the recovered Claude session
`d9570c96-1845-40c3-b642-65229ebd51b9`.

Latest local `tests/Unit/` result after the Codex fix pass:

```text
Tests: 10575, Assertions: 20354, Errors: 0, Failures: 0,
Deprecations: 38, Skipped: 8, Incomplete: 4.
```

Open PHPUnit items:

| ID | Item | File | Notes |
|---|---|---|---|
| RT-010 | 8 aliasMock skips | `ProjectTaskTest`, `ProjectReportTest`, `ProductServiceCategoryTest` | Needs real fixtures instead of alias mocks. |
| RT-011 | 4 incomplete markers | mixed | Re-enumerate before editing. |

Recently closed:

- RT-007..RT-009 — fixed by Codex on 2026-05-09; see
  `RESOLVED_ISSUES.md` and `.tmp/codex/20260509/handsoff.md`.
- PHPStan stale `App\Models\Bills\BillProduct` namespace errors — fixed by
  `1e73c3e13`.
- ESLint warning blockers — fixed by `1e73c3e13`.
- UtilityTest settings/template failures — final `tests/Unit/app/Models/utils`
  per-suite tally is clean at `157145304`.

---

## BLOCKED — Infrastructure (2026-05-07)

### Task G — `serve-k8s--hard` k8s deployment

**Status:** BLOCKED — not actionable from a code agent.
**Symptom:** Docker Hub TLS handshake timeout when pulling base images.
**Environmental causes:** IPv6 disabled and ICMP blocked network-wide on the
build host; the Docker daemon retries through fallback paths but the TLS
handshake never completes.
**Action required:** Network admin must restore Docker Hub connectivity
(re-enable IPv6 or whitelist the relevant Docker Hub CDN endpoints over IPv4).
**Workaround:** None inside the repo.
**Reference:** `.tmp/opencode/ds/20260430_fix-status.md`,
`AGENTS.md` Task G section.

---

## OPEN — Performance (N+1 Query Problems)

### 1. Dashboard Chart Data — 108+ Queries (MITIGATED)

**Routes:** `/account-dashboard` (was 3.35s TTFB with data, 15-30s under load)

**Root cause:** `User::getIncExpBarChartData()` runs 48+ queries (12-month loop × 4 queries),
`User::getIncExpLineChartDate()` runs 60+ queries (15-day loop × 4 queries),
`weeklyInvoice()`/`monthlyInvoice()`/`weeklyBill()`/`monthlyBill()` each load full collections
and iterate with `getTotal()`/`getDue()`.

**Fix applied:** Wrapped all 6 calls in `Cache::remember()` with 2-minute TTL in `DashboardController`.
First load still runs all queries; subsequent loads within TTL are instant.

**Further optimization (suggested, not applied):**

- Rewrite `getIncExpBarChartData()` to use 2 aggregate SQL queries instead of 24 individual ones
- Rewrite `getIncExpLineChartDate()` to use 2 aggregate queries instead of 30
- Add DB indexes on `(created_by, date)` and `(created_by, send_date)` — **requires approval before applying**
- Move chart data to async AJAX endpoint for lazy loading

### 2. Asset `users()` Dead Code (FIXED)

**Route:** `/account_assets`

**Root cause:** Blade called `$asset->users()` but `Asset` model has no `users()` method —
`method_exists` always returned false, "No users available" always shown.
The actual relationship is `employees()` (BelongsToMany).

**Fix applied:**

- Changed blade from `$asset->users(...)` to `$asset->employees`
- Added `->with('employees')` eager loading in `AssetController::index()`

### 3. User List N+1 — 3 Queries Per User Card (FIXED)

**Route:** `/users` (was 30s TTFB with many users)

**Root cause:** Blade called `$user->totalCompanyUser($user->id)`, `totalCompanyCustomer()`,
`totalCompanyVendor()` per user — each fires a separate COUNT query.
With 20 users = 60 extra queries.

**Fix applied:** Pre-compute all counts in 3 batch GROUP BY queries in `UserController::index()`,
pass as `$userCounts`, `$customerCounts`, `$vendorCounts` maps to the view.
N×3 queries → 3 queries total.

### 4. `/users/confirmed-password-status` — 29.46s TTFB (EXPLAINED)

**Root cause:** The endpoint itself is fast (0.08s). It redirects 302 → `/users`,
and the 29.46s was the full redirect chain bottlenecked by the N+1 users page.
Fixed by item #3 above.

---

## OPEN — Route Failures (Data/Config-Dependent)

### 5. Export Routes — 6 Previously Colliding Routes (FIXED)

All 6 export routes previously collided with resource `{param}` routes and returned 404.
Fixed with `->where()` regex constraints in prior session (commit `f46e23c48`).

**Current status:** All verified HTTP 200:

- `/bills/export` (0.08s), `/customers/export` (0.13s), `/invoices/export` (0.10s)
- `/proposals/export`, `/vendors/export`, `/leaves/export` (2.73s — leave data volume)

### 6. `/pos/create` — HTTP 422 (FIXED)

Previously returned 404. Fixed to return 422 JSON `{"error":"Add some products to cart!"}` —
intentional validation when cart is empty. Not a page render route.

### 7. `/email_templates/create` — View Path (FIXED)

View path `email_template.create` → `email_templates.create`. Fixed in prior session.

### 8. `/debit_notes/{id}/bill` — Requires Valid ID (NOT A BUG)

Returns 404 with invalid debit note ID. Working as intended with valid data.

---

## OPEN — Test Gaps

### 9. Playwright Conditional Skips (13 tests)

Conditional skips in `ui-triggers.spec.cjs` (10 tests) for optional UI elements that
may not be visible depending on page state/config. Plus 2 invoice create form skips
(permission guard redirect) and 1 Daily Purchase report skip (known browser hang).

These are not bugs — they're test design patterns for handling optional UI. No code fix needed.

### 10. ZoomMeetingTrait Constants (deferred)

~14 routes in `web.php` still use raw string literals (all functional; PHP dispatch is
case-insensitive). `ZoomMeetingTrait` constants deferred — trait constants require PHP 8.2+,
project minimum is 8.1.

### 11. No E2E Tests for Export Routes, POS, Debit Notes

Playwright specs (`financial.spec.cjs`, `module-pages.spec.cjs`) do not cover:

- Any of the 6 export routes (file download responses)
- `/pos/*` routes (cart-dependent, returns JSON)
- `/debit_notes/{id}/bill` (requires valid debit note data)

Mock page tests created in `tests/frontend/js/e2e/rendered-pages.spec.ts` (41 tests, all passing)
cover structural validation of these pages but not live backend interaction.

---

## OPEN — Baselines (as of 2026-04-24)

| Suite                   | Result                                                                     |
| ----------------------- | -------------------------------------------------------------------------- |
| PHPUnit                 | 12,177 tests, 21,156 assertions, **0 failures**, 122 skipped, 5 incomplete |
| Playwright (E2E)        | **478 passed**, 13 skipped, 0 failed, 0 flaky                              |
| Playwright (mock pages) | **41 passed**, 0 failed (rendered-pages.spec.ts)                           |
| curl (287 routes)       | **240 × 200**, 43 × 302, 0 × fail after fixes                              |
| wget spider (18 routes) | **18/18 OK**                                                               |
| Blade view:cache        | **all templates compile**                                                  |
| MySQL                   | 211 tables, all key tables verified                                        |
| PHPStan L5              | clean                                                                      |
| ESLint                  | clean                                                                      |
| tsc                     | clean                                                                      |
| Jest                    | 319/322 suites                                                             |
| pytest                  | 53/53                                                                      |

### TTFB Benchmarks (empty data, post-optimization)

| Route                              | TTFB (1st) | TTFB (cached) |
| ---------------------------------- | ---------- | ------------- |
| `/users`                           | 0.40s      | —             |
| `/account_assets`                  | 0.23s      | —             |
| `/account-dashboard`               | 0.50s      | 0.24s         |
| `/users/confirmed-password-status` | 0.08s      | —             |

---

## § Resolved Issues (moved from above)

> Prior route failures from the 4xx audit that are now fully resolved.
> See `.notes/RESOLVED_ISSUES.md` for the full archive.

### Resolved — Route Health

| Issue                             | Status         | How Fixed                                        | Commit      |
| --------------------------------- | -------------- | ------------------------------------------------ | ----------- |
| 6 export route collisions         | ✅ Fixed (200) | `->where()` regex constraints on resource routes | `f46e23c48` |
| `/pos/create` 404                 | ✅ Fixed (422) | Changed 404 → 422 JSON with validation message   | `f46e23c48` |
| `/email_templates/create` 404     | ✅ Fixed (200) | View path `email_template` → `email_templates`   | `f46e23c48` |
| Purchase UUID validation          | ✅ Fixed       | Added UUID format check before DB query          | `51724bb86` |
| PurchaseProduct OOM circular load | ✅ Fixed       | Removed circular eager-load in ORM relationship  | `51724bb86` |

### Resolved — Performance

| Issue                              | Status       | How Fixed                                           |
| ---------------------------------- | ------------ | --------------------------------------------------- |
| `/users` N+1 (3 queries/user)      | ✅ Fixed     | Batch GROUP BY in UserController, no blade N+1      |
| `/account_assets` dead `users()`   | ✅ Fixed     | Changed to `employees()` + eager loading            |
| Dashboard 108+ uncached queries    | ✅ Mitigated | `Cache::remember()` on 6 heavy data calls (2m TTL)  |
| `/users/confirmed-password-status` | ✅ Not a bug | Fast endpoint (0.08s), was bottlenecked by `/users` |
