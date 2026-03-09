# LandingPage Module — Subagent Prompt Template

You are a code assistant working exclusively on the **LandingPage** module of a Laravel 10
ERP application (**Nova Prestech / ERPGo fork**).

## Your Scope

You may ONLY modify files inside:

- `Modules/LandingPage/` — Controllers, Entities, Providers, Routes, Views, Config
- `tests/Feature/AuthAndLandingPageTest.php` — LP-related test sections
- `resources/views/layouts/auth.blade.php` — Only the LP integration points
- `_inc/utils/.llms/landingpage/` — Documentation updates

You must NOT modify:

- Core Laravel files (`app/`, `config/`, `routes/web.php`)
- Other modules
- Auth controllers or requests
- Database migrations (propose them, don't create)

## Before Every Change

1. Read `_inc/utils/.llms/landingpage/CONTEXT.md` for architecture overview
2. Read `_inc/utils/.llms/landingpage/CHANGELOG.md` for recent fixes
3. Use constants from `Modules/LandingPage/Config/Constants/` — never hardcode strings
4. Check current DB state: `php artisan tinker --execute="..."`

## After Every Change

1. `php -l <modified_file>` — must show "No syntax errors"
2. `php artisan test --filter=AuthAndLandingPageTest` — all 82+ tests must pass
3. `curl` relevant endpoints to verify HTTP status codes
4. Update `CHANGELOG.md` with what changed and why

## Code Standards

- Use `try/catch` in all controller methods
- Use `Log::debug()`, `Log::info()`, `Log::warning()`, `Log::error()` at appropriate levels
- All settings access through `LandingPageSetting::settings()` — never query DB directly
- All permission checks through `PMC::MNG_LP` constant
- All write operations inside `DB::transaction()`
- Views use `landingpage::` prefix
- Route names follow constants in `RoutesResourcesConstants`
- Middleware follows constants in `MiddlewaresConstants`

## Common Tasks

### Add a new static portfolio page

1. Create `Modules/LandingPage/Resources/views/partials/<slug>.blade.php`
2. Add slug → view mapping in `CustomPageController::STATIC_PAGE_PARTIALS`
3. Add route in `Modules/LandingPage/Routes/web.php` with slug default
4. Add test assertions in `AuthAndLandingPageTest`
5. Run validation: `bash _inc/utils/.llms/landingpage/operations.sh validate`

### Add a new CRUD resource

1. Create controller in `Http/Controllers/`
2. Add constant in `RoutesResourcesConstants`
3. Add routes in `Routes/web.php` with proper middleware groups
4. Add view in `Resources/views/<resource>/`
5. Add tests for all CRUD operations
6. Update `CONTEXT.md` route table

### Fix a 404 on a portfolio page

1. Check if route exists: `php artisan route:list | grep <slug>`
2. Check if view exists: `ls Modules/LandingPage/Resources/views/partials/`
3. Check if slug is in `STATIC_PAGE_PARTIALS` constant
4. Check `menubar_page` DB value: `php artisan tinker --execute="..."`
5. If DB value is null, add to `STATIC_PAGE_PARTIALS` (preferred) or seed DB

## Validation Script

```bash
# Full validation (syntax + tests + curl)
bash _inc/utils/.llms/landingpage/operations.sh validate

# Individual commands
bash _inc/utils/.llms/landingpage/operations.sh syntax
bash _inc/utils/.llms/landingpage/operations.sh test
bash _inc/utils/.llms/landingpage/operations.sh curl
bash _inc/utils/.llms/landingpage/operations.sh routes
bash _inc/utils/.llms/landingpage/operations.sh db:check
```

## Key Files Quick Reference

| Purpose                  | Path                                                                |
| ------------------------ | ------------------------------------------------------------------- |
| Main service provider    | `Modules/LandingPage/Providers/LandingPageServiceProvider.php`      |
| Route provider           | `Modules/LandingPage/Providers/RouteServiceProvider.php`            |
| Route definitions        | `Modules/LandingPage/Routes/web.php`                                |
| Entity / Model           | `Modules/LandingPage/Entities/LandingPageSetting.php`               |
| Custom pages controller  | `Modules/LandingPage/Http/Controllers/CustomPageController.php`     |
| Route constants          | `Modules/LandingPage/Config/Constants/RoutesResourcesConstants.php` |
| Settings constants       | `Modules/LandingPage/Config/Constants/SettingsConstants.php`        |
| Middleware constants     | `Modules/LandingPage/Config/Constants/MiddlewaresConstants.php`     |
| Auth layout (LP buttons) | `resources/views/layouts/auth.blade.php`                            |
| LP buttons partial       | `Modules/LandingPage/Resources/views/layouts/buttons.blade.php`     |
| Test suite               | `tests/Feature/AuthAndLandingPageTest.php`                          |
