# CLI Commands — 2026-03-11

## PHPStan

- `php -d memory_limit=512M vendor/bin/phpstan analyse --level=5 --no-progress`: static analysis pass

## PHPUnit

- `php -d memory_limit=512M vendor/bin/phpunit --no-coverage`: full suite re-run after fixes

## Git

- `git add -A && git commit -m "fix(phpstan): str_contains 3-param bug, Deal hasOne→belongsTo, Deliverable/Rateable typed property overrides, WorkerSchema import"`: commit 83f934b
- `git add -A && git commit -m "fix(enums): remove duplicate array keys in normalize() maps"`: commit 775d8d4
- `git add -A && git commit -m "fix: PHPStan critical bugs - syntax error, class case, param mismatches, undefined vars, type fixes"`: commit cccada7
- `git add -A && git commit -m "fix(phpunit-v9): add CRUD constants trait (78 controllers), HasFactory (27 models), 3 factories, method aliases, relationship fixes, null guards"`: commit 69e3522
