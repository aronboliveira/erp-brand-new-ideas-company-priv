#!/usr/bin/env bash
# scripts/phpstan-modules.sh — Run PHPStan level 3 per-module
# Usage: bash scripts/phpstan-modules.sh [level] [module ...]
# Examples:
#   bash scripts/phpstan-modules.sh                    # all modules, level 3
#   bash scripts/phpstan-modules.sh 2                  # all modules, level 2
#   bash scripts/phpstan-modules.sh 3 Bills Planning   # only Bills & Planning
#   bash scripts/phpstan-modules.sh 3 Models/Bills     # Models subdir
set -euo pipefail

LARAVEL_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$LARAVEL_DIR"

LEVEL="${1:-3}"
shift 2>/dev/null || true

# Module definitions: controllers + their models (analysed together per domain)
declare -A MODULES=(
  [Activity]="app/Http/Controllers/Activity app/Models/Activity"
  [Auth]="app/Http/Controllers/Auth"
  [Bills]="app/Http/Controllers/Bills app/Models/Bills"
  [Bugs]="app/Http/Controllers/Bugs app/Models/Bugs"
  [Charts]="app/Http/Controllers/Charts app/Models/Charts"
  [Companies]="app/Http/Controllers/Companies app/Models/Companies"
  [Configs]="app/Http/Controllers/Configs app/Models/Configs"
  [Contact]="app/Http/Controllers/Contact app/Models/Contact"
  [Individuals]="app/Http/Controllers/Individuals app/Models/Individuals"
  [Info]="app/Http/Controllers/Info app/Models/Info"
  [Planning]="app/Http/Controllers/Planning app/Models/Planning"
  [Products]="app/Http/Controllers/Products app/Models/Products"
  [Shapes]="app/Http/Controllers/Shapes app/Models/Shapes"
  [Ssr]="app/Http/Controllers/Ssr app/Models/Ssr"
  [Helpers]="app/Http/Controllers/Helpers app/Models/Helpers"
  [Abstracts]="app/Models/Abstracts"
  [Traits]="app/Models/Traits"
  [Services]="app/Services"
  [Exports]="app/Exports"
  [Imports]="app/Imports"
  [Jobs]="app/Jobs"
  [Mail]="app/Mail"
  [Providers]="app/Providers"
  [Console]="app/Console"
  [Middleware]="app/Http/Middleware app/Http/Requests"
)

# If specific modules requested, filter
REQUESTED_MODULES=("$@")

REPORT_DIR=".notes"
REPORT_FILE="$REPORT_DIR/phpstan_modules_l${LEVEL}_$(date +%Y%m%d).txt"
SUMMARY_FILE="$REPORT_DIR/phpstan_modules_summary_l${LEVEL}_$(date +%Y%m%d).txt"

mkdir -p "$REPORT_DIR"
: > "$REPORT_FILE"
: > "$SUMMARY_FILE"

TOTAL_PASS=0
TOTAL_FAIL=0
TOTAL_ERRORS=0
TOTAL_SKIP=0
declare -A MODULE_RESULTS

echo "================================================================="
echo " PHPStan Level $LEVEL — Module-by-Module Analysis"
echo " $(date '+%Y-%m-%d %H:%M:%S')"
echo "================================================================="
echo ""

# Sort module names for consistent output
IFS=$'\n' SORTED_MODULES=($(echo "${!MODULES[@]}" | tr ' ' '\n' | sort)); unset IFS

for MODULE in "${SORTED_MODULES[@]}"; do
  # Filter if specific modules requested
  if [[ ${#REQUESTED_MODULES[@]} -gt 0 ]]; then
    SKIP=1
    for REQ in "${REQUESTED_MODULES[@]}"; do
      if [[ "$MODULE" == "$REQ" ]] || [[ "$MODULE" == *"$REQ"* ]]; then
        SKIP=0; break
      fi
    done
    [[ $SKIP -eq 1 ]] && continue
  fi

  PATHS="${MODULES[$MODULE]}"

  # Verify at least one path exists
  VALID_PATHS=""
  for P in $PATHS; do
    [[ -d "$P" ]] && VALID_PATHS="$VALID_PATHS $P"
  done
  VALID_PATHS="$(echo "$VALID_PATHS" | xargs)"

  if [[ -z "$VALID_PATHS" ]]; then
    echo "  ⊘ $MODULE — paths not found, skipping"
    MODULE_RESULTS[$MODULE]="SKIP"
    TOTAL_SKIP=$((TOTAL_SKIP + 1))
    continue
  fi

  printf "  ▸ %-15s " "$MODULE"

  # Build PHPStan command — pass paths as arguments
  # Use the single-process config to avoid worker timeouts
  RESULT=$(php vendor/bin/phpstan analyse \
    --configuration=phpstan-module.neon \
    --level="$LEVEL" \
    --memory-limit=4G \
    --no-progress \
    $VALID_PATHS 2>&1) || true

  # Parse result
  if echo "$RESULT" | grep -q '\[OK\] No errors'; then
    echo "✓ PASS (0 errors)"
    MODULE_RESULTS[$MODULE]="PASS:0"
    TOTAL_PASS=$((TOTAL_PASS + 1))
  else
    ERROR_COUNT=$(echo "$RESULT" | grep -oP 'Found \K\d+' | head -1)
    ERROR_COUNT="${ERROR_COUNT:-?}"
    echo "✗ FAIL ($ERROR_COUNT errors)"
    MODULE_RESULTS[$MODULE]="FAIL:$ERROR_COUNT"
    TOTAL_FAIL=$((TOTAL_FAIL + 1))
    TOTAL_ERRORS=$((TOTAL_ERRORS + ${ERROR_COUNT//[^0-9]/0}))
  fi

  # Log details
  {
    echo "================================================================="
    echo "MODULE: $MODULE  (level $LEVEL)"
    echo "PATHS:  $VALID_PATHS"
    echo "================================================================="
    echo "$RESULT"
    echo ""
  } >> "$REPORT_FILE"
done

echo ""
echo "================================================================="
echo " SUMMARY"
echo "================================================================="
echo "  Level:   $LEVEL"
echo "  Passed:  $TOTAL_PASS"
echo "  Failed:  $TOTAL_FAIL"
echo "  Skipped: $TOTAL_SKIP"
echo "  Total errors: $TOTAL_ERRORS"
echo ""

# Write summary file
{
  echo "# PHPStan Module Analysis — Level $LEVEL"
  echo "Date: $(date '+%Y-%m-%d %H:%M:%S')"
  echo ""
  printf "| %-15s | %-8s | %-8s |\n" "Module" "Status" "Errors"
  printf "| %-15s | %-8s | %-8s |\n" "---------------" "--------" "--------"
  for MODULE in "${SORTED_MODULES[@]}"; do
    RES="${MODULE_RESULTS[$MODULE]:-N/A}"
    STATUS="${RES%%:*}"
    ERRS="${RES##*:}"
    [[ "$STATUS" == "PASS" ]] && ERRS="0"
    [[ "$STATUS" == "SKIP" ]] && ERRS="-"
    printf "| %-15s | %-8s | %-8s |\n" "$MODULE" "$STATUS" "$ERRS"
  done
  echo ""
  echo "Passed: $TOTAL_PASS | Failed: $TOTAL_FAIL | Skipped: $TOTAL_SKIP | Total errors: $TOTAL_ERRORS"
} >> "$SUMMARY_FILE"

echo "  Full report:    $REPORT_FILE"
echo "  Summary:        $SUMMARY_FILE"
echo ""

# Exit with failure if any module failed
[[ $TOTAL_FAIL -gt 0 ]] && exit 1 || exit 0
