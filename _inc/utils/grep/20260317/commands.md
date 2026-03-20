# Grep Commands — 2026-03-17

## Mypy error analysis
- `python3 -m mypy app/Exports/py/ app/Imports/py/ utils/scripts/py/ 2>&1 | grep "error:" | grep -oP '\[.*?\]' | sort | uniq -c | sort -rn`: mypy error categories

## curl CSRF extraction
- `curl -s -b "$CJ" http://127.0.0.1:8000/login | grep -oP 'name="_token"[^>]*value="\K[^"]+'`: extract CSRF token from login page
