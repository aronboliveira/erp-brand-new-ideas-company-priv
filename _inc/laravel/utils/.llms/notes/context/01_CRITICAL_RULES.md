# 01 — CRITICAL RULES

> Non-negotiable constraints for any agent working on this codebase.
> Violating ANY of these will break the app.

## ⛔ NEVER do these

1. **NEVER run `php artisan test`** — PHPUnit uses the SAME database
   (`erp_brand_new_ideas_company_db`), `migrate:fresh` inside test setup wipes ALL production
   data. There is no separate test DB configured.

2. **NEVER run `php artisan migrate:fresh`** or `migrate:refresh` — same
   destructive effect on the only database.

3. **NEVER replace or delete the super admin user** — the entire app's
   `created_by` foreign key chain depends on the SA user's UUID.

4. **NEVER create fake/synthetic UUIDs** (like `a3e8f4b2-7c1d-...`) and
   assign them as `created_by` — every entity in the DB references the
   real SA UUID. Breaking this chain makes the entire app show empty data or
   crash.

5. **NEVER cast a UUID `$user->id` to `(int)`** — UUIDs are strings; casting
   gives `0`, which breaks `find_in_set()`, `whereRaw()`, and FK lookups.

6. **NEVER run the seeder `ProjectDataFixSeeder`** without reading it first —
   a previous agent put destructive truncate/replace logic in it.

7. **DO NOT change the app locale** to anything other than `pt-br` for
   production views. The SA user's `lang` column should be `pt-br`.

8. **DO NOT modify `RouteServiceProvider`** without understanding that it
   **pluralizes** all non-last URI segments (e.g., `task-board/{view?}` →
   `task-boards/{view?}`).

## ✅ ALWAYS do these

1. After modifying PHP files: `php -l <file>` to syntax-check.
2. After modifying views/routes: `php artisan view:clear && php artisan route:clear`.
3. When querying data in tinker: use `DB::table('...')`, NOT Eloquent models
   that might trigger duplicate class errors.
4. Check `git diff HEAD` before committing to avoid stale changes.
5. The app server runs on **port 8000**: `php artisan serve --port=8000`.
6. Working directory for artisan: `_inc/laravel/`.
7. When fixing SA user access bugs: ensure the type check includes
   `PMC::SA` alongside `PMC::CPN` (company).
