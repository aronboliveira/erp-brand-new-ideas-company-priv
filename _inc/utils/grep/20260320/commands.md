# Grep Commands — 2026-03-20

## Git status filtering
- `git status -s | grep -v "D"`: non-deleted changes
- `git status -s | grep -vE 'R|D'`: non-renamed, non-deleted

## Test output filtering (from session context)
- `grep -E 'FAIL|ERRORS|Tests:' ut_out.txt`: PHPUnit summary
- `grep -rn 'redirect()->back()' app/ --include='*.php' | head -20`: redirect-back loop risk audit
