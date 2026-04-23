# Session Notes — 2026-02-17 — Modal Routes + Curl Suites

## Session Goals

1. System resource check and mitigation (CPU/memory)
2. Complete modal route redirect feature (middleware + JS)
3. Create extensive curl test suites for all 1,515 routes
4. Update `.llms` documentation

## Completed

### 1. System Resource Mitigation

- Load average peaked at 10.55 (31Gi machine, 24Gi used)
- VSCode zygote at 118% CPU, PHP Language Server at 94% CPU
- **Fix**: Paused PHP LS (`kill -STOP`), reniced zygotes to priority 19, resumed PHP LS at priority 10
- Load dropped from 10.55 → 7.99

### 2. Feature Test Verification

All 8 Feature test files pass with 0 failures:

| File | Tests |
|------|-------|
| AuthAndLandingPageTest | 82 |
| DashboardDataTest | 26 |
| FinancialRouteHardeningTest | 275 |
| HrmProjectRouteTest | 155 |
| RouteParamMatrixTest | 581 |
| ViewRenderingHardeningTest | 133 |
| WriteRouteTest | 87 |
| ExampleTest | 1+ |

### 3. Modal Route Feature — Completed

**Files created/modified:**

- `app/Http/Middleware/RedirectModalRoutes.php` (existed from prior session, 130 lines)
    - Intercepts non-AJAX GET requests to `.create`/`.edit` routes
    - Redirects to `.index` with `?modal=create` or `?modal=edit&modal_id=X`
    - Exclusion list: 16 full-page resources (FULL_PAGE_RESOURCES constant)
    - Bypasses: AJAX, JSON, `_modal_partial` param

- `app/Http/Kernel.php` — RedirectModalRoutes added to web middleware group

- `public/assets/js/core/modal-autoopen.js` (enhanced, 145 lines)
    - Reads `?modal=` params from URL
    - **Strategy 1**: Find `[data-ajax-popup="true"]` trigger matching the action and click it (preserves title, size, guard-msg)
    - **Strategy 2**: Fallback direct AJAX fetch into `#commonModal` (same pattern as custom.js)
    - Cleans URL immediately via `history.replaceState`
    - 300ms delay to allow DataTables/other JS to finish

- `resources/views/layouts/admin.blade.php:275` — script tag referencing the JS

### 4. Curl Test Suites — Created

**Location**: `tests/sh/` (15 files, 5,520+ lines)

| Script | Purpose | Routes |
|--------|---------|--------|
| `_common.sh` | Shared library (colours, CSRF, retries, timing) | — |
| `00_auth.sh` | Login, logout, session validation | — |
| `01_get_routes.sh` | All GET endpoints | 846 |
| `02_post_routes.sh` | All POST endpoints with CSRF | 409 |
| `03_put_patch_routes.sh` | All PUT/PATCH with CSRF | 298 |
| `04_delete_routes.sh` | All DELETE with CSRF | 188 |
| `05_watch_health.sh` | Periodic health monitoring (`watch -n`) | 15 sampled |
| `06_timing_report.sh` | Full `-w` timing for every route | 1,515 |
| `07_stress_quick.sh` | Parallel concurrency (`xargs -P`) | 20 sampled |
| `08_header_variants.sh` | JSON/AJAX/plain header combos | 40 × 4 |
| `09_unauthenticated.sh` | Auth guard verification | 60 |
| `10_csrf_validation.sh` | CSRF rejection without token | 30 |
| `11_json_api.sh` | API endpoint tests | 2 |
| `12_landing_page.sh` | LandingPage module | 7 |
| `run_all.sh` | Orchestrator — runs all or selected suites | — |

**Features of `_common.sh`**:
- Configurable via env vars: `ERP_BASE_URL`, `COOKIE_JAR`, `CURL_TIMEOUT`, `CURL_MAX_RETRIES`, `VERBOSE`
- ANSI colour output (auto-detected)
- Pass/fail/warn/skip counters with summary
- CSRF token extraction from login page
- Authenticated login flow
- `curl_test()` with retries and expected code validation
- `curl_timed()` for detailed `-w` JSON output
- `curl_header_variants()` for 4-way header testing
- `curl_post_csrf()` and `curl_write_csrf()` for CSRF-protected writes
- CSV logging for timing data

**Usage**:
```bash
# Run all suites
ERP_BASE_URL=http://127.0.0.1:8000 bash tests/sh/run_all.sh

# Run specific suites by number
bash tests/sh/run_all.sh 00 01 02

# Run with verbose output
VERBOSE=1 bash tests/sh/run_all.sh

# Run individual suite
bash tests/sh/06_timing_report.sh
```

### 5. Documentation Updated

- `_inc/laravel/utils/.llms/notes/context/10_CURRENT_STATUS.md` — full rewrite with current test counts, route inventory, middleware details, known issues
- This session notes file
- CLI reference for curl test suite commands

## Architecture Notes

### Modal Route Flow

```
User hits /customers/create directly in browser
  → RedirectModalRoutes middleware intercepts (non-AJAX GET, .create suffix)
  → Redirect to /customers?modal=create
  → Index page loads normally
  → modal-autoopen.js reads ?modal=create
  → Finds [data-ajax-popup="true"][data-url*="/create"] button
  → Clicks it → custom.js AJAX fetches /customers/create (with X-Requested-With)
  → Partial HTML loaded into #commonModal
  → Modal shown
  → URL cleaned via replaceState
```

### Full-Page Exclusions

These 16 resources have full `@extends('layouts.admin')` in their create/edit views:
bills, budgets, employees, expenses, invoices, jobs, journal_entries, payslips,
permission, permissions, product_stocks, proposals, purchases, set_salaries,
settings, warehouse
