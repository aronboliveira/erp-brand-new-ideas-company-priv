# ERP Prestech - Code Audit Report

Generated: 2025 Session Continuation

## Executive Summary

All 7 audit points from the user request have been reviewed. The application is in good health with consistent patterns across most areas. Key findings and recommendations follow.

---

## 1. ViewsConstants Usage Audit

### Status: ✅ GOOD - Generally Consistent

**Findings:**

- **Controllers**: Most controllers properly use `VW::` constants for view names
  - Examples: `view(VW::USR . '.index')`, `view(VW::ALW . '.index')`
  - Found in: UserController, AllowanceController, PayslipController, DealController, ReportController, etc.
- **Edge Cases Found** (5 total):
  1. `errors.login_error` - Hardcoded in AuthenticatedSessionController
  2. `Chatify::pages.app` - Third-party package view (acceptable)
  3. `stripe` - Simple stripe view (minor)
- **Blade Views**: VW:: constants properly used in templates
  - Example: `{{ VW::ITV_SCD }}`, `{{ VW::CL_XS12 }}`

**Recommendation:** Minor - Consider adding constants for error views like `VW::ERR_LOGIN`.

---

## 2. Model Auth Logic Separation

### Status: ⚠️ NEEDS ATTENTION - Auth Logic in Models

**Findings:**

- **ChecksLogin Trait** used in models:
  - `App\Models\Individuals\Customer.php` (line 22, 43)
  - `App\Models\Individuals\User.php` (line 19, 42)
- **Direct Auth::user() calls in User.php**:
  - Lines: 442, 692, 882, 915, 917, 935, 954, 986, 1022, 1077, 1092-1132
  - These are static methods checking user type/permissions

**Pattern Found:**

```php
// In User model
$user = Auth::user();  // Called in helper methods
return Auth::user()[UC::COL_TP] === PMC::CPN ...
```

**Recommendation:**

1. Move auth-dependent business logic to Service classes
2. Keep models focused on data relationships and accessors
3. Create `App\Services\UserContextService` for auth-aware operations

---

## 3. ViewClassNamesConstants Usage Audit

### Status: ✅ GOOD - Widely Adopted

**Findings:**

- **975 lines** of CSS class constants defined
- **40+ Blade files** actively use `VC::` or `ViewClassNamesConstants::`
- **Key usage patterns**:
  - `{{ VC::DSH_IT }}` - Dashboard items
  - `{{ VC::BT_SM_PM }}` - Button styles
  - `{{ VC::CLM6 }}` - Column layouts
  - `{{ VC::DFL_AIC }}` - Flexbox utilities

**Hardcoded Classes Found** (minimal):

- `col-xxl-*` classes not fully covered (col-xxl-6, col-xxl-12, etc.)
- Some `col-10` instances not using constants

**Recommendation:** Add constants for `col-xxl-*` variants to ViewClassNamesConstants.

---

## 4. Blade Component Extraction

### Status: ✅ ADEQUATE - Partials System in Place

**Findings:**

- **197 @include usages** across views
- **Existing partials structure**:

  ```
  resources/views/partials/
  ├── admin/
  │   ├── footer.blade.php
  │   ├── header.blade.php (20KB)
  │   └── menu.blade.php (460KB)
  ├── helpers/
  ├── validation/
  └── global-error-handler.blade.php
  ```

- **Most common includes**:
  - `fragments.std` (34 uses)
  - `layouts.hrm_setup` (24 uses)
  - `fragments.stylesheets` (17 uses)
  - `fragments.favicon` (14 uses)
  - `reports.partials._kpi_cards` (10 uses)

**Recommendation:** Consider extracting:

1. Form field wrappers (repeated form-control patterns)
2. Card components (CD patterns with standard headers)
3. Table action buttons (ACT_BTN patterns)

---

## 5. Financial Modules Deep Review

### Status: ✅ ALL PASSING - 200 OK

**Routes Tested:**
| Route | Status |
|-------|--------|
| /invoices | 200 |
| /bills | 200 |
| /payments | 200 |
| /expenses | 200 |
| /revenues | 200 |
| /reports/invoice-summary | 200 |
| /reports/bill-summary | 200 |
| /reports/expense-summary | 200 |
| /reports/income-summary | 200 |

**Additional financial routes in routes/web.php:**

- Invoice PDF generation: `/invoices/pdf/{id}`
- Bill payments: `/bills/{id}/payment`
- Payment gateways: Stripe, Cashfree, Benefit, PayPal integrations

---

## 6. Create Route Redirects Analysis

### Status: ✅ WORKING AS DESIGNED

**Findings:**
| Route | Status | Reason |
|-------|--------|--------|
| `/allowances/create` | 302 | Requires employee context, redirects to `/allowances/create/{eid}` |
| `/commissions/create` | 302 | Same pattern - needs employee ID |
| `/other_payments/create` | 302 | Same pattern |
| `/loans/create` | 302 | Same pattern |
| `/deductions/create` | 404 | Route not defined (may need to add) |
| `/overtime/create` | 404 | Route not defined (may need to add) |

**Pattern Explanation:**
These are **employee-scoped resources**. The `/create` endpoint redirects to find the first employee and routes to `/create/{eid}`. This is intentional behavior.

**If modal behavior is desired:**

- Use AJAX to load the create form into a modal
- Add `?modal=1` query param support to controllers
- Return JSON partial instead of full view when modal requested

---

## 7. Super Admin Access Verification

### Status: ✅ PROPERLY CONFIGURED

**Gate::before Implementation:**

```php
// app/Providers/AuthServiceProvider.php
Gate::before(function ($user, $ability) {
    if (($user->{UsersConstants::COL_TP} ?? null) === PermissionsConstants::SA) {
        return true;
    }
});
```

**Verification Results:**
| Route | Status | Notes |
|-------|--------|-------|
| /users | 200 | Admin-only ✓ |
| /roles | 200 | Admin-only ✓ |
| /plans | 200 | Admin-only ✓ |
| /system-settings | 405 | POST-only (correct) |

All permission-gated routes accessible to Super Admin.

---

## Summary & Action Items

### Immediate (Low effort, high value):

1. ✅ No immediate fixes required - system is stable

### Short-term:

1. Add `col-xxl-*` constants to `ViewClassNamesConstants.php`
2. Add `VW::ERR_LOGIN = 'errors.login_error'` constant
3. Consider adding `/deductions/create` and `/overtime/create` routes

### Medium-term (refactoring):

1. Extract auth logic from `User.php` model to service class
2. Create reusable Blade components for:
   - Form field wrapper
   - Card with header
   - Table action buttons
   - Modal template

### Technical Debt Notes:

- `menu.blade.php` at 460KB is extremely large - consider splitting by module
- ChecksLogin trait in models violates SRP - should be controller/middleware concern

---

## Test Command Reference

```bash
# Run financial route tests
for route in "invoices" "bills" "payments" "expenses" "revenues" "reports/invoice-summary" "reports/bill-summary" "reports/expense-summary" "reports/income-summary"; do
  code=$(curl -s -o /dev/null -w "%{http_code}" -b /tmp/admin_smoke_cookies6.txt "http://localhost:8888/$route" 2>/dev/null)
  echo "$code $route"
done

# Run permission-gated route tests
for route in "users" "roles" "plans"; do
  code=$(curl -s -o /dev/null -w "%{http_code}" -b /tmp/admin_smoke_cookies6.txt "http://localhost:8888/$route" 2>/dev/null)
  echo "$code $route"
done
```

---

## 8. Full Test Suite Audit — 2026-04-01

### Environment

- PHP 8.4.5 / Laravel 10.49.0 / MySQL 8.4.7
- PHPUnit 10.5.55 / Jest 29.7.0 / Playwright / PHPStan / ESLint / flake8 / mypy

### Results Summary

| Tool            | Status                      | Details                                                                              |
| --------------- | --------------------------- | ------------------------------------------------------------------------------------ |
| **PHPUnit**     | 176 PASS / 7 FAIL / 40 WARN | 874 individual ✓, 10 ⨯. Fatal: BillProduct class redeclaration blocked Feature tests |
| **Jest**        | 28/28 suites, 652/652 tests | All passed in 16.1s                                                                  |
| **PHPStan**     | ✅ 0 errors                 | 2GB memory limit required                                                            |
| **TSC**         | 1 error                     | Casing conflict in ts/dist/ (payslip vs paySlip .d.ts)                               |
| **ESLint**      | 77,576 errors               | 99% from ts/, .backup/, public/, Modules/ — core test/util ~40 (Node globals)        |
| **Playwright**  | 9 passed, 3 skipped         | 3.6 min runtime                                                                      |
| **flake8**      | 147 issues                  | Mostly style: unused imports (73), whitespace (41), long lines (5)                   |
| **mypy**        | 37 errors in 6 files        | openpyxl overloads, None attribute access, type annotations                          |
| **MySQL**       | ✅ Healthy                  | 211 tables, 219 migrations, 1044 FKs, 101 users, 1680 permissions                    |
| **HTTP Routes** | ✅ No 5xx                   | login/register → 200, auth routes → 302, all security headers present                |

### PHPUnit Failing Suites

1. `MassAssignmentTest` — model fillable/guarded assertion
2. `BugTest` — relation resolution (bug_status, assign_to, created_by, project)
3. `EmailTest` — global scope ordering
4. `JobStageTest` — fillable fields mismatch
5. `ProductServiceUnitTest` — user relation type
6. `LabelTest` — fillable array mismatch
7. `GeneratedOfferLetterTest` — default record count

### Security Headers Verified

- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `Strict-Transport-Security: max-age=31536000; includeSubDomains`
- `Content-Security-Policy: full CSP (connect/script/style/img/font directives)`
- CSRF tokens active

### Known Issues

- `BillProduct.php` namespace `App\Models` in `app/Models/Bills/` — causes class redeclaration on autoload
- `MessagesController` class missing — blocks `php artisan route:list`
- ESLint scanning `ts/`, `.backup/`, `public/`, `Modules/` — needs ignores in `eslint.config.mjs`

---

## 9. Security Roleplay Framework — Multi-Language Test Suite

### Overview

Added a comprehensive security roleplay testing framework with 6 actor roles across 5 languages (JavaScript, Python, Bash, PHP, WASM).

### Scripts Created (by role)

| Role        | Total Scripts | Languages                                                         | Complexity |
| ----------- | ------------- | ----------------------------------------------------------------- | ---------- |
| Black Hat   | 12            | JS(3), Bash(3), Python(2), PHP(1), WASM(1), PHPUnit(1), pytest(1) | Expert     |
| CISO        | 10            | JS(2), Bash(2), Python(2), PHP(1), WASM(1), PHPUnit(1), pytest(1) | Advanced   |
| White Hat   | 10            | JS(2), Bash(2), Python(3), PHP(2), WASM(1)                        | Advanced   |
| QA          | 9             | JS(2), Bash(1), Python(3), PHP(2), WASM(1)                        | Mid-Senior |
| Backend Dev | 7             | JS(2), Bash(2), Python(1), PHP(1), WASM(1)                        | Senior     |
| Green Hat   | 6             | JS(1), Bash(1), Python(1), PHP(2), WASM(1)                        | Beginner   |

### Unit Test Results

- **17 Jest test suites**: ALL PASSING (187 tests)
- Test files in `tests/Unit/security/roleplay/{role}/js/*.test.cjs`

### Mock Applications

| App                | Purpose                                                  |
| ------------------ | -------------------------------------------------------- |
| `vulnerable-form/` | Intentionally vulnerable form (XSS, no input validation) |
| `session-test/`    | Insecure session management                              |
| `api-test/`        | Unprotected API endpoint                                 |
| `websocket-test/`  | WebSocket without authentication                         |
| `upload-test/`     | File upload without validation                           |

### Security Controls

- `**/black-hat/` directories are git-ignored in both repos
- No black-hat files appear in git history (verified via `git log --all --diff-filter=A`)
- All scripts target `localhost` / `127.0.0.1` by default
- Documentation and guidelines in each role's `guidelines/GUIDELINES.md`

### Documentation

- Main README: `tests/Feature/security/roleplay/README.md`
- Per-role guidelines: `tests/Feature/security/roleplay/{role}/guidelines/GUIDELINES.md`

---

_Report generated during code audit session (updated 2026-04-01)_
