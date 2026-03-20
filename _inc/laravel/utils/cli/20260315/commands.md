# CLI Commands — 2026-03-15

## PHPUnit
- `php -d memory_limit=512M vendor/bin/phpunit --no-coverage`: full suite

## PHPStan
- `php -d memory_limit=512M vendor/bin/phpstan analyse --level=5 --no-progress`: clean run

## Git
- `git add -A && git commit -m "refactor(services): delegate 68 Utility methods to 6 service classes"`: commit 5f89f18
- `git add -A && git commit -m "fix(ide): resolve controller/model import and type errors"`: commit 27a53db
- `git add -A && git commit -m "fix(tests): resolve test assertion failures and import errors"`: commit df9ae60
- `git add -A && git commit -m "chore(archive): move outdated scan/report files to .history"`: commit 65553eb
- `git add -A && git commit -m "docs: update KNOWN_ISSUES, CURRENT_WORKING_ISSUES, NEXT_STEPS"`: commit 8b67e65
- `git add -A && git commit -m "docs(guidelines): create branched subagent guidelines tree"`: commit fa104ae
- `git add -A && git commit -m "chore(prompts): reorganize prompt files into .guidelines"`: commit f709fd8
- `git add -A && git commit -m "chore(cleanup): remove cache files and test artifacts"`: commit dbcea54
