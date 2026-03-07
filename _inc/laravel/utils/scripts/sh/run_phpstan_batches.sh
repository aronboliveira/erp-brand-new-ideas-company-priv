#!/bin/bash
# PHPStan batch analysis v2 — runs on each subdirectory separately.
# Keeps phpstan cache between runs for speed. Uses parallel workers.

set -o pipefail

LARAVEL_DIR="/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel"
cd "$LARAVEL_DIR"

OUTDIR="storage/phpstan_results"
mkdir -p "$OUTDIR"
# Clear previous result files
rm -f "$OUTDIR"/*.txt

TIMEOUT_SEC=1200  # 20 minutes per subdirectory
TOTAL_ERRORS=0
FAILED_DIRS=()
PASSED_DIRS=()

# All directories to scan
DIRS=(
  # Small/fast dirs
  "app/Console"
  "app/Exceptions"
  "app/Helpers"
  "app/Imports"
  "app/Jobs"
  "app/Logging"
  "app/Mail"
  "app/Providers"
  "app/Traits"
  "app/View"
  "app/Config"
  "app/Enums"
  # Http
  "app/Http/Middleware"
  "app/Http/Requests"
  # Controllers by subdirectory
  "app/Http/Controllers/Activity"
  "app/Http/Controllers/Auth"
  "app/Http/Controllers/Bills"
  "app/Http/Controllers/Bugs"
  "app/Http/Controllers/Charts"
  "app/Http/Controllers/Companies"
  "app/Http/Controllers/Configs"
  "app/Http/Controllers/Contact"
  "app/Http/Controllers/Helpers"
  "app/Http/Controllers/Individuals"
  "app/Http/Controllers/Info"
  "app/Http/Controllers/Planning"
  "app/Http/Controllers/Products"
  "app/Http/Controllers/Shapes"
  "app/Http/Controllers/Ssr"
  # Models by subdirectory
  "app/Models/Abstracts"
  "app/Models/Activity"
  "app/Models/Bills"
  "app/Models/Bugs"
  "app/Models/Charts"
  "app/Models/Companies"
  "app/Models/Configs"
  "app/Models/Contact"
  "app/Models/Helpers"
  "app/Models/Individuals"
  "app/Models/Info"
  "app/Models/Planning"
  "app/Models/Products"
  "app/Models/Shapes"
  "app/Models/Ssr"
  "app/Models/Traits"
  "app/Models/utils"
  # Services & Exports
  "app/Services"
  "app/Exports"
)

echo "================================================================"
echo "PHPStan Batch Analysis v2 — $(date)"
echo "Workers: 8, Timeout/batch: ${TIMEOUT_SEC}s, Cache: KEPT"
echo "================================================================"
echo ""

for dir in "${DIRS[@]}"; do
  if [ ! -d "$dir" ]; then
    echo "SKIP (not found): $dir"
    continue
  fi

  NFILES=$(find "$dir" -name '*.php' -type f | wc -l)
  if [ "$NFILES" -eq 0 ]; then
    echo "SKIP (no PHP files): $dir"
    continue
  fi

  SAFE_NAME=$(echo "$dir" | tr '/' '_')
  OUTFILE="$OUTDIR/${SAFE_NAME}.txt"

  printf "%-55s %3d files ... " "$dir" "$NFILES"
  TIME_START=$(date +%s)

  timeout "$TIMEOUT_SEC" php vendor/bin/phpstan analyse \
    --level=3 \
    --memory-limit=2G \
    --no-progress \
    --error-format=table \
    "$dir" > "$OUTFILE" 2>&1
  EXIT_CODE=$?

  TIME_END=$(date +%s)
  DUR=$((TIME_END - TIME_START))

  if [ "$EXIT_CODE" -eq 124 ]; then
    echo "TIMEOUT (${DUR}s)"
    FAILED_DIRS+=("$dir")
  elif [ "$EXIT_CODE" -eq 0 ]; then
    echo "OK (${DUR}s, 0 errors)"
    PASSED_DIRS+=("$dir:0:${DUR}s")
  else
    # Extract error count
    ERRS=$(grep -oP 'Found \K\d+' "$OUTFILE" | tail -1)
    [ -z "$ERRS" ] && ERRS="?"
    echo "DONE (${DUR}s, ${ERRS} errors)"
    if [ "$ERRS" != "?" ]; then
      TOTAL_ERRORS=$((TOTAL_ERRORS + ERRS))
      PASSED_DIRS+=("$dir:${ERRS}:${DUR}s")
    fi
  fi
done

# Root-level PHP files
for rootfile in app/Http/Controllers/*.php app/Http/Kernel.php; do
  if [ -f "$rootfile" ]; then
    SAFE_NAME=$(echo "$rootfile" | tr '/' '_' | tr '.' '_')
    OUTFILE="$OUTDIR/${SAFE_NAME}.txt"
    printf "%-55s          ... " "$rootfile"
    TIME_START=$(date +%s)
    timeout 120 php vendor/bin/phpstan analyse --level=3 --memory-limit=2G --no-progress "$rootfile" > "$OUTFILE" 2>&1
    EXIT_CODE=$?
    TIME_END=$(date +%s)
    DUR=$((TIME_END - TIME_START))
    if [ "$EXIT_CODE" -eq 124 ]; then
      echo "TIMEOUT (${DUR}s)"
    else
      ERRS=$(grep -oP 'Found \K\d+' "$OUTFILE" | tail -1)
      [ -z "$ERRS" ] && ERRS=0
      echo "DONE (${DUR}s, ${ERRS} errors)"
      [ "$ERRS" != "0" ] && TOTAL_ERRORS=$((TOTAL_ERRORS + ERRS))
    fi
  fi
done

echo ""
echo "================================================================"
echo "SUMMARY — $(date)"
echo "================================================================"
echo "Total errors: $TOTAL_ERRORS"
echo "Passed batches: ${#PASSED_DIRS[@]}"
echo "Failed (timeout) batches: ${#FAILED_DIRS[@]}"
if [ ${#FAILED_DIRS[@]} -gt 0 ]; then
  echo ""
  echo "TIMED OUT directories:"
  for d in "${FAILED_DIRS[@]}"; do
    echo "  - $d"
  done
fi
echo ""
echo "Results in: $OUTDIR/"
echo "================================================================"
