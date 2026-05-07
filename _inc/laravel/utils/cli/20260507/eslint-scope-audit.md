# ESLint scope audit — 2026-05-07

## Commands

```bash
# Initial inventory of error-producing top-level dirs
npx eslint . --max-warnings=0 2>&1 | grep -E '^/' | sed 's/:.*//' \
  | sort -u | sed -E 's|.*erp_prestech/_inc/laravel/||' \
  | awk -F'/' '{print $1"/"$2}' | sort | uniq -c | sort -rn | head -20

# Counts (before fix)
npx eslint . --max-warnings=0 2>&1 | tail -5
# → 14913 problems (14867 errors, 46 warnings)

# Counts (after adding "frontend/**" + ".history/**" to ignores)
npx eslint . --max-warnings=0 2>&1 | tail -5
# → 34 problems (0 errors, 34 warnings)
```

## Result
14,867 errors → 0 errors. 34 legitimate warnings remain (unused-var hints).

## Root cause
`frontend/app/` is a Next.js sub-app with its own ESLint config. The root
config was scanning its `.next/` build artifacts.
`.history/` was gitignored but still on disk being scanned.
