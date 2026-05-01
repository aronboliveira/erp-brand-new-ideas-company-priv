# ERP Brand New Ideas Company — Project Constraints & Coding Standards

> These are mandatory rules. Violating them causes data loss or CI failure.
> Last updated: 2026-03-04

---

## Critical Constraints

### Database Safety

- **NEVER** run `php artisan test` — it wipes the production database.
- **NEVER** run `php artisan migrate:fresh` — same effect.
- Use `php -l` for syntax checks.
- Use `APP_ENV=testing php vendor/bin/phpunit --filter=TestName --no-coverage` for individual tests.
- PHPUnit is configured with MySQL test DB (`erp_brand_new_ideas_company_test`) via `phpunit.xml` and `.env.testing`.
- Use `composer run test:feature` or `composer run test:unit` as safe shortcuts.

### UUID Identity

- **NEVER** cast `$user->id` to `(int)` — UUIDs always return `0`.
- All user identity comparisons must use string comparison.
- Foreign keys referencing `users.id` are `char(36)`.

### Git Remotes

- **NEVER** push to `comp` remote.
- Push only to `origin` (`erp-brand-new-ideas-company-priv`).
- The `.tmp/`, `.deprecated/`, and `.backup/` directories are in `.gitignore` — never track them.

### PHPStan

- Master config: `phpstan.neon` (level 3, full `app/` path).
- Module config: `phpstan-module.neon` (single-process, for heavy analysis).
- Module runner: `bash scripts/phpstan-modules.sh [level] [module ...]`.
- BillController (1582 lines) requires single-process mode — times out with parallel workers.
- After adding model properties, always re-run PHPStan to verify: `composer run phpstan`.

### Seeders

- **Original seeders** are preserved in `.backup/database/seeders/` (184 files, read-only reference).
- **Working seeders** in `database/seeders/` can be edited/pruned for current test needs.
- Never modify `.backup/` copies — create new seeders or edit the `database/seeders/` copies.

---

## Coding Standards

### PHP / Laravel

- **Route constants**: Always use `MWC::`, `VW::`, `PMC::` constants. No raw strings in route definitions.
- **Method naming**: camelCase exclusively (original fork had snake_case/run-together — all renamed).
- **File permissions**: `mkdir(..., 0755)`. Never `0777`. Apply `chmod($file, 0644)` after writing.
- **Upload filenames**: Always sanitize with `preg_replace('/[^a-zA-Z0-9._-]/', '_', ...)`.
- **SQL**: Never interpolate variables into `whereRaw()`. Use `whereRaw("FIND_IN_SET(?, col)", [$var])`.
- **Logging**: Never log bearer tokens, auth headers, or cookies. Use `bearer_present => (bool)`.
- **Console output**: In HTTP code paths, use `SafeConsoleOutput::make()` (returns `NullOutput`).
- **Eager loading**: Always `->with([...])` for relationships shown in views. No N+1 loops.
- **Model annotations**: Add `@property` PHPDoc annotations for all dynamic DB column access. Required for PHPStan level 2+.

### JavaScript / Frontend

- **No `innerHTML` with user data.** Use `textContent` + `createElement`.
- **No `document.write`.** Use DOM API (`document.head.innerHTML` for full-page rewrites only).
- **No `eval()` or `new Function()`.** These fail the Jest security audit.
- **Form actions**: Use `safeFormAction()` helper for URL validation on dynamic forms.

### Blade Views

- **DNS2D**: Use instance calls: `(new \Milon\Barcode\DNS2D)->getBarcodeHTML(...)`.
  Never use the static facade `DNS2D::`.

### Imports

- Remove unused imports immediately. The codebase already had 30+ unused imports cleaned.
- Use fully-qualified class constants (`PJC::COL_MIN_DR`, `UC::COL_USER_ID`) — never raw column strings.

---

## Testing Rules

- **PHPUnit**: 26/26 Feature tests passing (DashboardDataTest). Use `composer run test:feature` for safe runs.
- **Jest**: Must pass 10/10 (3 suites: audit, performance, integrity). Run `npx jest --no-cache`.
- **Playwright**: E2E auth uses a setup project (`auth.setup.cjs`). Cookies auto-refresh. Requires running server.
- **Pytest**: 6 test files for Python export/import modules. Run `npm run test:pytest`.
- **curl timing**: `bash tests/curl_timing.sh` — benchmarks 18+ routes (requires running server).
- **Mock page budget**: No mock HTML file over 200KB. Minify table rows if needed.

## Test Commands Quick Reference

| Runner     | Safe Command                                             |
| ---------- | -------------------------------------------------------- |
| PHPUnit    | `composer run test:feature` or `composer run test:unit`  |
| PHPStan    | `composer run phpstan` or `composer run phpstan:modules` |
| Jest       | `npx jest --config jest.config.cjs --verbose`            |
| Playwright | `npm run test:playwright` (server must be running)       |
| Pytest     | `npm run test:pytest`                                    |
| curl       | `bash tests/curl_timing.sh` (server must be running)     |
| All JS     | `npm run test:all`                                       |
| Full stack | `npm run test:fullstack`                                 |
