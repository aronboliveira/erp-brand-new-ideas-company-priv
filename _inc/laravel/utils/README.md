# \_inc/laravel/utils/ — Laravel Project Utilities

Pertains to the **Laravel application layer**: frontend, backend/server, and database.

## Structure

```
scripts/
  sh/         — Shell scripts (artisan helpers, test runners, migration tools)
  py/         — Python scripts (code analysis, seeder generation)
  php/        — PHP CLI scripts (route checks, model inspections)
  js/         — JS/Node scripts (asset tooling, refactors, build helpers)
  ts-harness/ — TypeScript test harness generators (see below)
prompts/      — Structured prompts for Laravel-specific agent workflows
regexes/      — Regex/grep patterns for PHP, Blade, JS within the project
logs/         — Runtime logs from Laravel dev scripts (gitignored via *.log)
caches/       — Cached outputs (nohup, PHPUnit results, PHPStan caches)
```

## TypeScript Test Harness Scripts

Located in `scripts/ts-harness/`:

| Script | Purpose |
|--------|---------|
| `scan-views.php` | Extract JS dependencies from Laravel blade views |
| `generate-harness.cjs` | Generate mock HTML pages from view mapping |
| `generate-playwright-tests.cjs` | Generate Playwright e2e test specs |
| `generate-jest-tests.cjs` | Generate Jest unit test files |
| `update-harness-index.cjs` | Update harness index with all pages |

### Usage

```bash
# From project root:
php _inc/laravel/utils/scripts/ts-harness/scan-views.php .tmp/copilot/view-js-map.json
node _inc/laravel/utils/scripts/ts-harness/generate-harness.cjs
node _inc/laravel/utils/scripts/ts-harness/generate-playwright-tests.cjs
node _inc/laravel/utils/scripts/ts-harness/generate-jest-tests.cjs
node _inc/laravel/utils/scripts/ts-harness/update-harness-index.cjs
```

## Scope

- Artisan command wrappers and helpers
- PHPUnit / PHPStan / Larastan running utilities
- Database migration and seeder tools
- Route and model analysis scripts
- Frontend asset compilation helpers
- Blade template analysis
- Laravel-specific grep patterns (Eloquent, middleware, etc.)
