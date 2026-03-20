# CLI Commands — 2026-03-10

## Routes & Controllers
- `php artisan route:list --columns=method,uri,name,action`: list all registered routes
- `php artisan serve --port=8000`: start dev server

## PHPStan
- `php -d memory_limit=512M vendor/bin/phpstan analyse --level=5 --no-progress`: static analysis

## PHPUnit
- `php -d memory_limit=512M vendor/bin/phpunit --no-coverage`: full test suite run

## Git
- `git add -A && git commit -m "fix(routes): fix login pluralization, namespace collision, and 4xx error handling"`: commit 1d79c15
- `git add -A && git commit -m "fix(phpstan): resolve undefined constants and return type mismatches"`: commit f9e7058
- `git add -A && git commit -m "fix(config): phpstan ignoreErrors + phpunit granular sub-suites"`: commit cdb9e75
- `git add -A && git commit -m "fix(models): remove snake_case relation aliases colliding with DB columns"`: commit 02e7d5d
- `git add -A && git commit -m "fix(auth): validate creatorId UUID in login detail and fix Playwright auth setup"`: commit 39d3c6a
- `git add -A && git commit -m "docs: update test results after auth fix and full re-run (session 2)"`: commit 8fbbb66
- `git add -A && git commit -m "fix(routes): correct sub-namespace prefixes for 39 controllers in use block"`: commit 61f03f4
- `git add -A && git commit -m "fix(controllers): add missing use function Helpers imports"`: commit bb5167b
- `git add -A && git commit -m "fix(controllers): fix inverted guard() patterns and other controller bugs"`: commit cff71b6
- `git add -A && git commit -m "fix(controllers): add missing \$action string arg to measureProfile calls"`: commit 9909d28
- `git add -A && git commit -m "fix(routes/views): add missing footer routes and fix leads view"`: commit b1fb209
- `git add -A && git commit -m "fix(controllers/models/views): fix multiple 500 errors from type mismatches"`: commit 80a5e1b
- `git add -A && git commit -m "fix(pytest): add exporter_payloads fixture and path constants"`: commit 31e85ae
- `git push origin develop`: push to remote

## Misc
- `free -m`: memory check
- `ps aux --sort=-%mem | head -10`: top memory processes
