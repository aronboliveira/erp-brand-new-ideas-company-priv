# CLI Commands — 2026-03-12

## PHPUnit

- `php -d memory_limit=512M vendor/bin/phpunit --no-coverage`: full suite after OOM guardrails
- `timeout 30 php -d memory_limit=512M vendor/bin/phpunit --no-coverage tests/Feature/Debug500Test.php 2>&1 | head -20`: targeted debug test
- `timeout 60 php -d memory_limit=512M vendor/bin/phpunit --no-coverage tests/Feature/Debug500Test.php 2>&1 | grep -E '1\)|2\)|3\)|4\)|Exception|Error' | head -20`: filtered debug test
- `timeout 120 php -d memory_limit=512M vendor/bin/phpunit --no-coverage tests/Feature/Debug500Test.php 2>&1 | tail -30`: extended debug test

## OOM Monitoring

- `free -m`: memory check
- `ps aux --sort=-%mem | head -10`: top memory consumers
- `sudo dmesg | grep -i oom`: OOM kernel messages

## Git

- `git add -A && git commit -m "chore: add OOM memory guardrails to PHPUnit and composer scripts"`: commit 7ffc59c
- `git add -A && git commit -m "fix: BankTransferPaymentController::uploadReceipt type mismatch"`: commit b5d389c
- `git add -A && git commit -m "fix: resolve infinite recursion in Deal::labels() and Lead::labels/products/sources"`: commit e63eaff
- `git add -A && git commit -m "refactor: add DefinesResourceActions trait to controllers"`: commit c40285c
- `git add -A && git commit -m "chore: expand PHPStan ignoreErrors for Laravel patterns"`: commit d98fb68
- `git add -A && git commit -m "chore: move loose scripts from laravel root to scripts/"`: commit 20bb004
- `git add -A && git commit -m "test: update TimesheetControllerTest"`: commit 1e36349
- `git add -A && git commit -m "chore: add .phpunit.result.cache to .gitignore"`: commit 79e5424
- `git add -A && git commit -m "docs: update KNOWN_ISSUES and add OOM fixes session notes"`: commit 771c267
