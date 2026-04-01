# Security Testing Audit — SQL Injection

> Reference: `.notes/.llms/.guidelines/security-roleplay-profiles.xml`
> Generated: 2026-03-31

---

## 1. Scope

Full SQL injection vulnerability audit of the ERP Laravel 10 application.  
Covers: HTTP routes, raw queries, Blade views, mass assignment, Eloquent models.

## 2. Vulnerabilities Found & Fixed

| #   | File                                                      | Vulnerability                                                         | Severity | Fix                       |
| --- | --------------------------------------------------------- | --------------------------------------------------------------------- | -------- | ------------------------- |
| 1   | `app/Http/Controllers/Activity/EventController.php:62`    | `whereRaw('MONTH(start_date)=' . $todayMonth)` — string concatenation | HIGH     | Parameterized binding `?` |
| 2   | `app/Http/Controllers/Shapes/DashboardController.php:314` | `find_in_set('{$user?->id}',...)` — string interpolation              | HIGH     | Parameterized binding `?` |
| 3   | `app/Models/Activity/Activity.php`                        | Missing `$guarded` — mass assignment vulnerable                       | MEDIUM   | Added `$guarded = ['id']` |

### Informational Findings (not fixed — medium/low risk)

- **66 `$_GET` usages in Blade views**: All inside `{{ }}` (auto-escaped), but bypasses `$request` abstraction.
- **2 `{!! Form::date() !!}` with `$_GET`**: `holidays/calendar.blade.php:105,111` — medium XSS risk.
- **20 `env()` calls in non-config files**: Anti-pattern (cached config won't resolve them), not a security bug.
- `DB::raw()` with table/column names from **constants** (not user input): Low risk, acceptable pattern.

## 3. Test Architecture

### 3.1 Non-roleplay tests (generic security validation)

Located in `tests/{e2e,Unit}/security/{php,js,py}/`:

| Test File                                              | Framework | Tests | Purpose                                                |
| ------------------------------------------------------ | --------- | ----- | ------------------------------------------------------ |
| `e2e/security/php/SqlInjectionTest.php`                | PHPUnit   | 217   | HTTP SQLi pen-test (GET, POST, path, headers, JSON)    |
| `Unit/security/php/MassAssignmentTest.php`             | PHPUnit   | 190   | Dynamic Eloquent model scan for `$fillable`/`$guarded` |
| `Unit/security/php/RawQueryAuditTest.php`              | PHPUnit   | 4     | Static SAST for raw SQL patterns                       |
| `python/security/test_sqli_payloads.py`                | pytest    | 171   | HTTP SQLi via requests (login, search, path, blind)    |
| `Unit/frontend/js/security/sqli-sanitization.test.cjs` | Jest      | 13    | Client-side sanitization, DOM, Blade scan              |

### 3.2 Roleplay tests (actor-specific simulation)

Located in `tests/{e2e,Feature,Unit}/security/roleplay/<actor>/{php,js,py}/`:

Each actor folder contains language subfolders with framework-specific tests.
See `.notes/.llms/.guidelines/security-roleplay-profiles.xml` for role definitions.

**Actors:**

| Actor                 | Mindset                           | Test Style                                        | Risk Level |
| --------------------- | --------------------------------- | ------------------------------------------------- | ---------- |
| **green-hat**         | Beginner, curious, learning       | Simple smoke tests, naive inputs, quick runs      | LOW        |
| **white-hat**         | Ethical pentester, methodical     | Structured OWASP payloads, proper reporting       | MEDIUM     |
| **black-hat**         | Malicious, sophisticated          | Obfuscated, evasive, destructive; **GIT-IGNORED** | CRITICAL   |
| **ciso**              | Executive oversight, compliance   | Policy validation, audit checklists, KPI metrics  | MEDIUM     |
| **qa**                | Quality tester, client-side focus | UI interaction, form validation, edge cases       | LOW-MEDIUM |
| **backend-developer** | Internal dev, code review         | Query patterns, ORM misuse, config checks         | MEDIUM     |

## 4. File Naming Conventions

- PHPUnit: `<Actor><Topic>Test.php` (e.g., `GreenHatSmokeTest.php`)
- pytest: `test_<actor>_<topic>.py` (e.g., `test_green_hat_smoke.py`)
- Jest: `<actor>-<topic>.test.cjs` (e.g., `green-hat-smoke.test.cjs`)
- Playwright: `<actor>-<topic>.spec.cjs` (e.g., `green-hat-smoke.spec.cjs`)

## 5. Git Strategy

- Black-hat folders are **always** `.gitignore`d (see pattern `**/black-hat/`).
- All other roleplay tests are committed normally.
- Commits follow: `test(security/<actor>): <description>`

## 6. Running

```bash
# PHPUnit (all security)
php artisan test --testsuite="Security-Feature,Security-Unit"

# pytest
python3 -m pytest tests/python/security/ -v

# Jest
npx jest tests/Unit/frontend/js/security/ --config jest.config.cjs

# Playwright
npx playwright test tests/e2e/security/

# curl (direct)
# See scripts in _inc/utils/scripts/
```
