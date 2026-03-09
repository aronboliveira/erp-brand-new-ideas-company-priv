#!/usr/bin/env bash
# Content verification — check that 200 routes return real HTML with data structures
set -euo pipefail
COOKIE="/tmp/admin_smoke_cookies3.txt"
BASE="http://127.0.0.1:8888"
REPORT="/tmp/content_verify_$(date +%Y%m%d_%H%M%S).txt"

php artisan cache:clear 2>/dev/null || true

# Sample of key routes across all modules
SAMPLE_ROUTES=(
  # Dashboard
  "dashboard"
  # HR
  "employees" "allowances" "commissions" "loans" "other_payments" "overtimes"
  "employee_attendances" "holidays" "leaves" "payslips" "departments"
  # CRM
  "deals" "leads" "clients" "contacts"
  # Accounting
  "invoices" "bills" "expenses" "revenues" "payments" "budgets"
  "debit_notes" "credit_notes" "journal_entries"
  # Project
  "projects" "tasks"
  # Planning
  "calendars" "goals" "notes" "todos"
  # Activity
  "appraisals" "award_types" "labels" "performance_types" "meetings"
  # Configs
  "taxes" "product_categories" "webhook-settings" "form_builders"
  # Assets
  "assets"
  # Contracts & Documents
  "contracts" "contract_types" "documents"
  # Reports
  "reports/monthly/income-expense" "reports/income-vs-expense"
)

PASS=0; EMPTY=0; TOTAL=0
declare -a EMPTY_ROUTES=()

{
echo "=== CONTENT VERIFICATION ==="
echo "Date: $(date)"
echo ""
echo "Checking that routes return real HTML with data structures..."
echo ""

for path in "${SAMPLE_ROUTES[@]}"; do
  url="${BASE}/${path}"
  BODY=$(curl -s -b "$COOKIE" -c "$COOKIE" --max-time 30 "$url" 2>/dev/null || echo "")
  CODE=$(curl -s -b "$COOKIE" -o /dev/null -w "%{http_code}" --max-time 5 "$url" 2>/dev/null || echo "000")
  TOTAL=$((TOTAL+1))

  if [[ "$CODE" != 2* ]]; then
    echo "SKIP [$CODE] /$path"
    continue
  fi

  # Check for data structures
  HAS_TABLE=$(echo "$BODY" | grep -ciP '<table|<thead|<tbody|dataTable' || true)
  HAS_CARD=$(echo "$BODY" | grep -ciP 'card-body|card-header|widget|stat-' || true)
  HAS_CHART=$(echo "$BODY" | grep -ciP 'chart|canvas|apexchart|Chart\(' || true)
  HAS_FORM=$(echo "$BODY" | grep -ciP '<form|<input|<select' || true)
  HAS_GRID=$(echo "$BODY" | grep -ciP 'col-md-|col-lg-|col-sm-|col-xl-|row' || true)
  HAS_NUMBERS=$(echo "$BODY" | grep -ciP 'badge|count|total|amount|\$[0-9]' || true)
  BODY_LEN=${#BODY}

  STRUCTURES=""
  [[ $HAS_TABLE -gt 0 ]] && STRUCTURES="${STRUCTURES}table,"
  [[ $HAS_CARD -gt 0 ]] && STRUCTURES="${STRUCTURES}card,"
  [[ $HAS_CHART -gt 0 ]] && STRUCTURES="${STRUCTURES}chart,"
  [[ $HAS_FORM -gt 0 ]] && STRUCTURES="${STRUCTURES}form,"
  [[ $HAS_GRID -gt 0 ]] && STRUCTURES="${STRUCTURES}grid,"
  [[ $HAS_NUMBERS -gt 0 ]] && STRUCTURES="${STRUCTURES}nums,"

  if [[ -n "$STRUCTURES" ]]; then
    PASS=$((PASS+1))
    echo "OK   [$CODE] /$path (${BODY_LEN}b) — ${STRUCTURES%,}"
  else
    EMPTY=$((EMPTY+1))
    EMPTY_ROUTES+=("$path")
    echo "EMPTY[$CODE] /$path (${BODY_LEN}b) — no data structures found"
  fi
done

echo ""
echo "=== SUMMARY ==="
echo "Checked: $TOTAL | With data: $PASS | Empty shells: $EMPTY"
if [ ${#EMPTY_ROUTES[@]} -gt 0 ]; then
  echo ""
  echo "Routes needing attention:"
  printf '  /%s\n' "${EMPTY_ROUTES[@]}"
fi
} | tee "$REPORT"

echo ""
echo "Report saved to: $REPORT"
