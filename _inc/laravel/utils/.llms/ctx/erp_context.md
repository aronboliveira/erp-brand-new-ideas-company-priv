# Finance/Admin Learning ERP — Subagent Context File

# Last updated: 2026-02-18T21:30Z

# Purpose: Structured context for AI subagent coordination

## Project Location

- Workspace root: repository root
- Laravel root: `_inc/laravel/`
- All paths below are relative to Laravel root unless noted

## Stack

- Laravel 10.x, PHP 8.4 local runtime, MySQL (`erp_brand_new_ideas_company_db`)
- PHPUnit 10.5.55, Jest 29.7.0, Playwright 1.58.2, Pytest
- Node.js (for frontend tests), Python 3 (for utility scripts)

## Auth

- Test user email: `u_1ecb6d5a-e2c5-4961-af3b-0ad83f9d259c@test.local`
- Test user password: `Admin@1234`
- User type: `super admin` (bypasses permission checks in `ChecksPermissions::guard()`)
- Session cookie name: `erp_brand_new_ideas_company_session`
- CSRF: `<meta name="csrf-token">` + `<input name="_token">`

## Key Constants Files

- `app/Config/Constants/ViewsConstants.php` — route/view path constants (VW::*)
- `app/Config/Constants/PermissionsConstants.php` — permission name constants (PMC::*)
- `app/Config/Constants/DatabaseConstants.php` — column name constants (DC::*)
- `app/Config/Constants/MiddlewareConstants.php` — middleware alias constants (MWC::*)
- `app/Config/Constants/SeedersTemplating.php` — seeder permission definitions

## Route Architecture

- 1,515 total routes (846 GET, 409 POST, 155 PUT, 143 PATCH, 188 DELETE, 29 OPTIONS)
- 67 LandingPage module routes (Modules/LandingPage/)
- Routes defined in `routes/web.php` using class constants (VW::_, R::, MWC::_)
- Resource routes: `R::resource(VW::CONST, Controller::class)->middleware([...])`

## Controller Patterns

- All controllers extend `App\Http\Controllers\Abstracts\Controller`
- Use traits: `ChecksLogin`, `ChecksPermissions`, `ConsoleOutputs`
- Guard pattern: `if (($redirect = self::guard($req, PMC::PERM, REDIRECT)) !== true) return $redirect;`
- Login check: `if (($userOrRedirect = self::_checkLogin()) instanceof RedirectResponse) return $userOrRedirect;`
- View check: `if (!View::exists($viewPath)) return $this->viewMissingRedirect(...);`
- Performance: `$this->measureProfile($action, function() { ... })`

## Database

- 212 tables total, ~145 with data (67 empty — all system/log/activity tables)
- UUID primary keys on most tables
- `created_by` column pattern for multi-tenancy
- User types: super admin (122), company (364), admin (1), client, customer, hr, vendor, unnamed (113)
- Key row counts: users 612, employees 107, customers 160, leads 104, deals 60, projects ~50+, invoices ~50+

## Seeder Dependency Chains (verified 2026-02-18)

All chains populated and functional:
- users → employees → branches → departments ✓
- job_categories(32) → jobs(11) → job_applications(2) → interview_schedules(2) ✓
- trainers(85) → training_types(40) → trainings(3) ✓
- award_types(15) → awards(2), termination_types(29) → terminations(21) ✓
- goal_types(9) → goal_trackings(3), loan_options(8) → loans(2) ✓
- pipelines(30) → stages(63), leads(104), deals(60) ✓
- tasks(2), task_stages(12), settings(2), warehouses(4), languages(25) ✓

## Test Infrastructure

- PHPUnit Unit: `tests/Unit/` — 485 files (Models 222, Http 168, Exports 20, etc.)
- PHPUnit Feature: `tests/Feature/` — 13 files
- Jest: `tests/frontend/js/` (pages/, core/, generic/ — 199 tests)
- Playwright: `tests/frontend/js/e2e/` (4 spec files: login, views-rendering, dashboard, csr-routes)
- Pytest: `tests/py/` (215 tests)
- Curl: `tests/sh/` (15 scripts, `_common.sh` shared helpers)

### PHPUnit Results (2026-02-18)

| Batch | Filter | Passed | Skipped | Failed |
|-------|--------|--------|---------|--------|
| Small unit dirs | database, Enums, Middleware, Modules | 917 | 0 | 0 |
| Models | Tests\Unit\app\Models | 282 | 28 | 0 |
| Remaining unit | Exports, Imports, Mail, etc. | 1,062 | 1 | 0 |
| Http Middlewares+Requests+Views | Non-Controllers Http | 255 | 2 | 0 |
| Http Controllers A | auth,bugs,charts,configs,contact | 721 | 0 | 0 |
| Http Controllers B | info,ssr,products,LandingPage,companies | 806 | 0 | 0 |
| Http Controllers D | activity,bills,planning | 4,416 | 0 (1 risky) | 0 |
| Http Controllers C | views,shapes,individuals | pending... | | |
| Feature | --testsuite=Feature | pending... | | |

### Curl Results (2026-02-18)

- `tests/sh/13_content_validation.sh`: 101 pass, 0 fail, 99 content-pass, 1 content-skip
- `tests/sh/14_landing_page_content.sh`: 25 pass, 0 fail, 24 content-pass, 0 content-skip

## Seeder Chain

- `DatabaseSeeder::runMocks()` → ~100 seeders in dependency order → `ContentValidationSeeder`
- `ContentValidationSeeder` — idempotent, fills empty tables with mock data, creates missing user types
- Run standalone: `php artisan db:seed --class=ContentValidationSeeder`
- Additional manual seeds: tasks, settings, product_categories, warehouses, languages, schedules, warnings, credit/debit notes, client_deals, user_deals, user_leads, proposal_products

## Dev Server

- `php artisan serve --host=127.0.0.1 --port=8000`
- Required for: Playwright E2E tests, curl test suites

## Known Issues (non-blocking)

- `task_stages/show.blade.php` missing (handled gracefully)
- `ProposalSeeder` uses `where` instead of `whereIn`
- `TrainingTypeSeeder` references non-existent `duration_min` column
- DNS2D facade globally broken (workaround in invoice template1)
- PhpSpreadsheet Borders::getInsideHorizontal() undefined in 2 exports
- `/project_task_stages` redirect FIXED: `ViewsConstants::TSK_STG` changed from singular to plural

## File Organization

- Utility/moment-routine scripts moved to `_inc/utils/.llms/scripts/20260218/`
- CLI references in `_inc/utils/.llms/cli/` (by date)
- Session notes in `_inc/utils/.llms/notes/` (by date)
- Subagent context in `_inc/utils/.llms/ctx/` (this file)
