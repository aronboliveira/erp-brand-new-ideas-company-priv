# CLI Commands — 2026-03-17

## PHPUnit
- `php -d memory_limit=512M vendor/bin/phpunit --no-coverage`: full suite (395/422 pass)

## Pytest
- `python3 -m pytest -x -v`: pytest suite
- `python3 -m mypy app/Exports/py/ app/Imports/py/ utils/scripts/py/ 2>&1 | grep "error:" | grep -oP '\[.*?\]' | sort | uniq -c | sort -rn`: mypy error summary

## Playwright
- `npx playwright test`: full e2e suite
- `npx playwright test --reporter=list`: verbose reporter

## curl verification
- `curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8000`: server health check
- `curl -s -c "$CJ" -b "$CJ" http://127.0.0.1:8000/login > /dev/null`: get login page
- `curl -s -b "$CJ" http://127.0.0.1:8000/login | grep -oP 'name="_token"[^>]*value="\K[^"]+'`: extract CSRF
- `curl -s -L -c "$CJ" -b "$CJ" -X POST 'http://127.0.0.1:8000/login' -H 'Content-Type: application/x-www-form-urlencoded' --data-urlencode "_token=${TOKEN}" --data-urlencode "email=...@test.local" --data-urlencode "password=Admin@1234" -o /dev/null -w "Login: %{http_code}\n"`: curl login
- `for route in / /invoices /proposals ... ; do CODE=$(curl -s -b "$CJ" -c "$CJ" -o /dev/null -w "%{http_code}" -L --max-time 15 "http://localhost:8000$route"); printf "%-30s %s\n" "$route" "$CODE"; done`: route sweep

## npm
- `npm audit fix`: fix npm vulnerabilities

## Git
- `git add -A && git commit -m "fix: resolve 61 PHPUnit failures + Playwright RBAC"`: commit 64c9305
- `git add -A && git commit -m "fix: register pytest timeout marker to silence PytestUnknownMarkWarning"`: commit d594b22
- `git add -A && git commit -m "fix(tests): curl/MySQL suite — all 15 suites green"`: commit 24e0ef4
