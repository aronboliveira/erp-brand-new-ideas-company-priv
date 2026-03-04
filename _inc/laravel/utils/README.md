# \_inc/laravel/utils/ — Laravel Project Utilities

Pertains to the **Laravel application layer**: frontend, backend/server, and database.

## Structure

```
scripts/
  sh/       — Shell scripts (artisan helpers, test runners, migration tools)
  py/       — Python scripts (code analysis, seeder generation)
  php/      — PHP CLI scripts (route checks, model inspections)
  js/       — JS/Node scripts (asset tooling, refactors, build helpers)
prompts/    — Structured prompts for Laravel-specific agent workflows
regexes/    — Regex/grep patterns for PHP, Blade, JS within the project
logs/       — Runtime logs from Laravel dev scripts (gitignored via *.log)
caches/     — Cached outputs (nohup, PHPUnit results, PHPStan caches)
```

## Scope

- Artisan command wrappers and helpers
- PHPUnit / PHPStan / Larastan running utilities
- Database migration and seeder tools
- Route and model analysis scripts
- Frontend asset compilation helpers
- Blade template analysis
- Laravel-specific grep patterns (Eloquent, middleware, etc.)
