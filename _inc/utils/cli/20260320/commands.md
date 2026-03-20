# CLI Commands — 2026-03-20

## PHPUnit
- `php -d memory_limit=512M vendor/bin/phpunit --no-coverage`: full suite run

## Playwright
- `npx playwright test`: e2e suite

## PHPStan
- `php -d memory_limit=512M vendor/bin/phpstan analyse --level=5 --no-progress`: clean run

## ESLint
- `npx eslint .`: lint check

## tsc
- `npx tsc --noEmit`: TypeScript check

## Jest
- `npx jest`: Jest suites

## Server
- `sleep 3 && curl -sI http://localhost:8000/login | head -3`: check server up
- `sleep 3 && curl -sI http://localhost:8000/ | head -3`: root health

## Git
- `git add -A && git commit -m "fix(LanguageController): use => instead of > in lang array assignment"`: commit 01e5099
- `git add -A && git commit -m "fix(Kernel): register SetGuestLocale middleware in web group"`: commit 1ffaee8
- `git add -A && git commit -m "fix(Auth): fix locale override, _updateLastLogin, and _setLocale validation"`: commit 6200542
- `git add -A && git commit -m "test(e2e/i18n): fix locale route URLs and cookie persistence tests"`: commit 7cca931
- `git add -A && git commit -m "test(e2e/financial): fix modal, invoice, and expense test expectations"`: commit 62600c7
- `git add -A && git commit -m "test(e2e): fix rendering assertions across crm, hrm, pm, reports, security"`: commit a494e69
- `git add -A && git commit -m "fix(ide): resolve Intelephense P1006/P1009 type errors + update test comment"`: commit 6d9a00b
- `git add -A && git commit -m "fix(ide): remove unused imports + fix remaining P1006 on app('request')"`: commit 92c8ee9
- `git push origin develop`: push all
