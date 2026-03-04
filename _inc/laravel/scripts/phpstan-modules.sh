#!/usr/bin/env bash
# scripts/phpstan-modules.sh — Run PHPStan level N per-module
# Usage: bash scripts/phpstan-modules.sh [level] [module ...]
# Examples:
#   bash scripts/phpstan-modules.sh                    # all modules, level 3
#   bash scripts/phpstan-modules.sh 2                  # all modules, level 2
#   bash scripts/phpstan-modules.sh 3 Bills Planning   # only Bills & Planning
#   bash scripts/phpstan-modules.sh 3 Activity-Ctrl    # Activity small controllers
# Env: TIMEOUT=<secs>  per-module timeout (default 600)
set -euo pipefail

LARAVEL_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$LARAVEL_DIR"

LEVEL="${1:-3}"
shift 2>/dev/null || true

# Per-module timeout in seconds (override with: TIMEOUT=900 bash scripts/...)
TIMEOUT_SECS="${TIMEOUT:-600}"

# ── Module definitions ──────────────────────────────────────────────────────
# Activity and Bills are split into sub-modules because their controllers
# exceed 1 000 lines each and cause PHPStan workers to spin for hours when
# analysed as a single batch.
# ───────────────────────────────────────────────────────────────────────────
declare -A MODULES=(
  # ── Activity (split) ──────────────────────────────────────────────────────
  [Activity-Models]="app/Models/Activity"
  [Activity-Ctrl]="app/Http/Controllers/Activity/ActivityController.php
    app/Http/Controllers/Activity/AppraisalController.php
    app/Http/Controllers/Activity/AwardController.php
    app/Http/Controllers/Activity/AwardTypeController.php
    app/Http/Controllers/Activity/CommissionController.php
    app/Http/Controllers/Activity/ComplaintController.php
    app/Http/Controllers/Activity/EventController.php
    app/Http/Controllers/Activity/LeadStageController.php
    app/Http/Controllers/Activity/MeetingController.php
    app/Http/Controllers/Activity/OvertimeController.php
    app/Http/Controllers/Activity/PerformanceTypeController.php
    app/Http/Controllers/Activity/SourceController.php
    app/Http/Controllers/Activity/StageController.php
    app/Http/Controllers/Activity/SupportController.php
    app/Http/Controllers/Activity/TaskStageController.php
    app/Http/Controllers/Activity/TrainingController.php
    app/Http/Controllers/Activity/TrainingTypeController.php
    app/Http/Controllers/Activity/TransferController.php
    app/Http/Controllers/Activity/WarehouseTransferController.php"
  [Activity-DealCtrl]="app/Http/Controllers/Activity/DealController.php"
  [Activity-LeadCtrl]="app/Http/Controllers/Activity/LeadController.php"
  [Activity-PosCtrl]="app/Http/Controllers/Activity/PosController.php"
  [Activity-PurchaseCtrl]="app/Http/Controllers/Activity/PurchaseController.php"
  [Activity-ReportCtrl]="app/Http/Controllers/Activity/ReportController.php"
  # ── Auth ──────────────────────────────────────────────────────────────────
  [Auth]="app/Http/Controllers/Auth"
  # ── Bills (split) ─────────────────────────────────────────────────────────
  [Bills-Models]="app/Models/Bills"
  [Bills-Ctrl]="app/Http/Controllers/Bills/AllowanceController.php
    app/Http/Controllers/Bills/AllowanceOptionController.php
    app/Http/Controllers/Bills/BankTransferController.php
    app/Http/Controllers/Bills/BankTransferPaymentController.php
    app/Http/Controllers/Bills/BenefitPaymentController.php
    app/Http/Controllers/Bills/BudgetController.php
    app/Http/Controllers/Bills/CashfreeController.php
    app/Http/Controllers/Bills/CouponController.php
    app/Http/Controllers/Bills/CreditNoteController.php
    app/Http/Controllers/Bills/DebitNoteController.php
    app/Http/Controllers/Bills/DeductionOptionController.php
    app/Http/Controllers/Bills/LoanController.php
    app/Http/Controllers/Bills/LoanOptionController.php
    app/Http/Controllers/Bills/OtherPaymentController.php
    app/Http/Controllers/Bills/PaymentController.php
    app/Http/Controllers/Bills/PayslipTypeController.php
    app/Http/Controllers/Bills/RevenueController.php
    app/Http/Controllers/Bills/SaturationDeductionController.php
    app/Http/Controllers/Bills/StripePaymentController.php
    app/Http/Controllers/Bills/TaxController.php
    app/Http/Controllers/Bills/TransactionController.php"
  [Bills-BillCtrl]="app/Http/Controllers/Bills/BillController.php"
  [Bills-ExpenseCtrl]="app/Http/Controllers/Bills/ExpenseController.php"
  [Bills-InvoiceCtrl]="app/Http/Controllers/Bills/InvoiceController.php"
  [Bills-PayslipCtrl]="app/Http/Controllers/Bills/PayslipController.php"
  # ── Other domains ─────────────────────────────────────────────────────────
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
TOTAL_TIMEOUT=0
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
    P="$(echo "$P" | xargs)"   # trim whitespace/newlines from heredoc-style values
    [[ -z "$P" ]] && continue
    [[ -d "$P" || -f "$P" ]] && VALID_PATHS="$VALID_PATHS $P"
  done
  VALID_PATHS="$(echo "$VALID_PATHS" | xargs)"

  if [[ -z "$VALID_PATHS" ]]; then
    echo "  ⊘ $MODULE — paths not found, skipping"
    MODULE_RESULTS[$MODULE]="SKIP"
    TOTAL_SKIP=$((TOTAL_SKIP + 1))
    continue
  fi

  printf "  ▸ %-20s " "$MODULE"

  # Build PHPStan command — pass paths as arguments
  # Use the single-process config; wrap with timeout to guard against hung workers
  PHPSTAN_EXIT=0
  RESULT=$(timeout "$TIMEOUT_SECS" php vendor/bin/phpstan analyse \
    --configuration=phpstan-module.neon \
    --level="$LEVEL" \
    --memory-limit=4G \
    --no-progress \
    $VALID_PATHS 2>&1) || PHPSTAN_EXIT=$?

  # Parse result
  if [[ $PHPSTAN_EXIT -eq 124 ]]; then
    echo "⏱ TIMEOUT (>${TIMEOUT_SECS}s)"
    MODULE_RESULTS[$MODULE]="TIMEOUT"
    TOTAL_TIMEOUT=$((TOTAL_TIMEOUT + 1))
  elif echo "$RESULT" | grep -q '\[OK\] No errors'; then
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
echo "  Timeout: $TOTAL_TIMEOUT"
echo "  Skipped: $TOTAL_SKIP"
echo "  Total errors: $TOTAL_ERRORS"
echo "  Per-module timeout: ${TIMEOUT_SECS}s (override: TIMEOUT=<secs>)"
echo ""

# Write summary file
{
  echo "# PHPStan Module Analysis — Level $LEVEL"
  echo "Date: $(date '+%Y-%m-%d %H:%M:%S')"
  echo ""
  printf "| %-22s | %-8s | %-8s |\n" "Module" "Status" "Errors"
  printf "| %-22s | %-8s | %-8s |\n" "----------------------" "--------" "--------"
  for MODULE in "${SORTED_MODULES[@]}"; do
    RES="${MODULE_RESULTS[$MODULE]:-N/A}"
    STATUS="${RES%%:*}"
    ERRS="${RES##*:}"
    [[ "$STATUS" == "PASS" ]]    && ERRS="0"
    [[ "$STATUS" == "SKIP" ]]    && ERRS="-"
    [[ "$STATUS" == "TIMEOUT" ]] && ERRS="-"
    printf "| %-22s | %-8s | %-8s |\n" "$MODULE" "$STATUS" "$ERRS"
  done
  echo ""
  echo "Passed: $TOTAL_PASS | Failed: $TOTAL_FAIL | Timeout: $TOTAL_TIMEOUT | Skipped: $TOTAL_SKIP | Total errors: $TOTAL_ERRORS"
} >> "$SUMMARY_FILE"

echo "  Full report:    $REPORT_FILE"
echo "  Summary:        $SUMMARY_FILE"
echo ""

# Exit with failure if any module failed or timed out
[[ $((TOTAL_FAIL + TOTAL_TIMEOUT)) -gt 0 ]] && exit 1 || exit 0
