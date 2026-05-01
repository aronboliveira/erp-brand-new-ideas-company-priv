#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────
# Model-hardening CLI commands used across Batches 1–6
# (~273 files: Abstracts, Activity, Bills, Bugs, Charts,
#  Companies, Configs, Contact, Helpers, Individuals, Info,
#  Planning, Products, Shapes, Ssr, Traits, utils,
#  Modules/LandingPage/Entities)
# ─────────────────────────────────────────────────────────────
set -euo pipefail
LARAVEL="/workspace/erp/_inc/laravel"

# ── 1. Mechanical hardening ────────────────────────────────
#    (sorts imports w/ {} spreading, removes comments,
#     normalises newlines, backslash \Error/\Exception,
#     trims trailing whitespace)
#    Before running: update DIRS list in the script.
python3 /tmp/harden_models.py

# ── 2. Deep semantic hardening ─────────────────────────────
#    (try/catch wrapping, Log::error with class/method/exception,
#     Log facade import, debug output removal)
#    Before running: update DIRS list in the script.
python3 /tmp/harden_deep_v2.py

# ── 3. Syntax check — all PHP files in target dirs ────────
#    Replace DIRS with space-separated directory list.
#    Example for Batch 6:
#      DIRS="app/Models/Traits app/Models/utils Modules/LandingPage/Entities"
cd "$LARAVEL"
DIRS="app/Models/Traits app/Models/utils Modules/LandingPage/Entities"
TOTAL=0; FAIL=0
for d in $DIRS; do
  while IFS= read -r -d '' f; do
    TOTAL=$((TOTAL + 1))
    if ! php -l "$f" > /dev/null 2>&1; then
      FAIL=$((FAIL + 1))
      echo "FAIL: $f"
    fi
  done < <(find "$d" -name '*.php' -print0)
done
echo "Failures: $FAIL/$TOTAL"

# ── 4. Post-script safety checks ──────────────────────────
# 4a. Duplicate Log imports
cd "$LARAVEL"
for d in $DIRS; do
  grep -rnP '(?i)^use Illuminate\\Support\\Facades\\Log;' "$d" \
    | awk -F: '{print $1}' | sort | uniq -d
done

# 4b. Stray echo/print/var_dump/dd
for d in $DIRS; do
  grep -rnP '^\s*(echo |print |var_dump|dd\()' "$d" || true
done

# ── 5. PHPStan — batch level ──────────────────────────────
cd "$LARAVEL"
php vendor/bin/phpstan analyse \
  --memory-limit=512M --no-progress \
  $DIRS 2>&1 | tail -20

# ── 6. PHPStan — full project ─────────────────────────────
cd "$LARAVEL"
php vendor/bin/phpstan analyse \
  --memory-limit=1G --no-progress 2>&1 | tail -3

# ── 7. Pre-existing error comparison ──────────────────────
#    Stash hardened changes, run PHPStan, pop back, compare.
cd "$LARAVEL"
git stash
php vendor/bin/phpstan analyse \
  --memory-limit=512M --no-progress \
  $DIRS 2>&1 | tail -5
git stash pop

# ── 8. PHPUnit regression (run after ALL models are done) ─
cd "$LARAVEL"
php artisan test --parallel --processes=4 2>&1 | tail -20
