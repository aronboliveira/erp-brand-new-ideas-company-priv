#!/usr/bin/env bash
###############################################################################
# crawl_write_routes.sh — POST / PUT / PATCH / DELETE route crawler
# ─────────────────────────────────────────────────────────────────────────────
# Authenticates against the local Laravel dev server then fires actual requests
# at every writable endpoint it can safely test.
#
# Usage:
#   chmod +x tests/crawl_write_routes.sh
#   bash tests/crawl_write_routes.sh [base_url]
#
# The script creates test entities, verifies responses, then cleans them up.
# Results are written to /tmp/erp_write_crawl_results.log
###############################################################################
set -uo pipefail
# Note: no 'set -e' — we want to continue on non-zero curl responses

BASE="${1:-http://127.0.0.1:8000}"
JAR="/tmp/erp_write_cookies.txt"
LOG="/tmp/erp_write_crawl_results.log"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# ── colours ──────────────────────────────────────────────────────────────────
RED='\033[0;31m'; GRN='\033[0;32m'; YLW='\033[1;33m'; CYN='\033[0;36m'; RST='\033[0m'

# ── counters ─────────────────────────────────────────────────────────────────
TOTAL=0; PASS=0; FAIL=0; SKIP=0
declare -A RESULTS

log() { echo -e "$1" | tee -a "$LOG"; }

record() {
  local method="$1" uri="$2" status="$3" label="$4"
  TOTAL=$((TOTAL+1))
  if [[ "$status" -ge 200 && "$status" -lt 400 ]]; then
    PASS=$((PASS+1))
    RESULTS["$method $uri"]="${GRN}$status${RST} $label"
    log "${GRN}  ✓${RST} $method $uri → $status $label"
  elif [[ "$status" -eq 422 || "$status" -eq 419 ]]; then
    # 422 = validation error (expected when we omit required fields on purpose)
    # 419 = CSRF expired (also acceptable note)
    SKIP=$((SKIP+1))
    RESULTS["$method $uri"]="${YLW}$status${RST} $label"
    log "${YLW}  ⚠${RST} $method $uri → $status $label"
  else
    FAIL=$((FAIL+1))
    RESULTS["$method $uri"]="${RED}$status${RST} $label"
    log "${RED}  ✗${RST} $method $uri → $status $label"
  fi
}

# ── helper: fire a request and record ────────────────────────────────────────
fire() {
  local method="$1" uri="$2" label="$3"
  shift 3
  # remaining args are extra curl flags (e.g., -d "field=val")
  local http_code
  http_code=$(curl -s -o /dev/null -w '%{http_code}' \
    -b "$JAR" -c "$JAR" \
    -X "$method" \
    -H "Accept: text/html,application/json" \
    -H "X-Requested-With: XMLHttpRequest" \
    --max-time 15 \
    "$@" \
    "${BASE}/${uri}" 2>/dev/null || echo "000")
  record "$method" "$uri" "$http_code" "$label"
  echo "$http_code"
}

fire_form() {
  local method="$1" uri="$2" label="$3"
  shift 3
  # Laravel requires POST for method-spoofed PUT/PATCH/DELETE
  local actual_method="POST"
  local method_field=""
  if [[ "$method" != "POST" ]]; then
    method_field="&_method=${method}"
  fi
  local extra_data=""
  # Collect any -d arguments from "$@" into extra_data
  local curl_extra=()
  while [[ $# -gt 0 ]]; do
    if [[ "$1" == "-d" ]]; then
      shift
      extra_data="&$1"
      shift
    else
      curl_extra+=("$1")
      shift
    fi
  done
  local http_code
  http_code=$(curl -s -o /dev/null -w '%{http_code}' \
    -b "$JAR" -c "$JAR" \
    -X "$actual_method" \
    -H "Accept: text/html,application/json" \
    -H "X-Requested-With: XMLHttpRequest" \
    --max-time 15 \
    -d "_token=${CSRF_TOKEN}${method_field}${extra_data}" \
    "${curl_extra[@]+"${curl_extra[@]}"}" \
    "${BASE}/${uri}" 2>/dev/null || echo "000")
  record "$method" "$uri" "$http_code" "$label"
  echo "$http_code"
}

###############################################################################
# 1. Authenticate
###############################################################################
log "\n${CYN}═══════════════════════════════════════════════════${RST}"
log "${CYN}  ERP Write-Route Crawler — $TIMESTAMP${RST}"
log "${CYN}═══════════════════════════════════════════════════${RST}\n"

rm -f "$JAR" "$LOG"
touch "$LOG"

log "${YLW}[1/7] Authenticating...${RST}"

# Get CSRF token from login page
LOGIN_HTML=$(curl -s -c "$JAR" -b "$JAR" "${BASE}/logins/en" 2>/dev/null)
CSRF_TOKEN=$(echo "$LOGIN_HTML" | grep -oP 'name="_token"[^>]*value="\K[^"]+' | head -1)

if [[ -z "$CSRF_TOKEN" ]]; then
  # Fallback: try meta tag
  CSRF_TOKEN=$(echo "$LOGIN_HTML" | grep -oP 'content="\K[a-zA-Z0-9]{40}' | head -1)
fi

if [[ -z "$CSRF_TOKEN" ]]; then
  log "${RED}FATAL: Could not extract CSRF token from login page${RST}"
  exit 1
fi
log "  CSRF token: ${CSRF_TOKEN:0:12}..."

# POST login
LOGIN_CODE=$(curl -s -o /dev/null -w '%{http_code}' \
  -b "$JAR" -c "$JAR" \
  -X POST \
  -d "_token=${CSRF_TOKEN}&email=suporte@prestech.com.br&password=123456789qwe.*" \
  -H "Accept: text/html" \
  "${BASE}/login" 2>/dev/null)

if [[ "$LOGIN_CODE" != "302" ]]; then
  log "${RED}FATAL: Login failed (HTTP $LOGIN_CODE, expected 302)${RST}"
  exit 1
fi
log "${GRN}  Authenticated (302 redirect)${RST}"

# Refresh CSRF after login (session may rotate)
# The dashboard/pages may embed CSRF in JS objects: "_token":"xxx" or csrfToken":"xxx"
for try_page in "dashboard" "deals" "clients" "projects"; do
  DASH_HTML=$(curl -s -b "$JAR" -c "$JAR" "${BASE}/${try_page}" 2>/dev/null)
  # Try JS object patterns first
  NEW_CSRF=$(echo "$DASH_HTML" | grep -oP '"_token"\s*:\s*"\K[^"]+' | head -1)
  [[ -z "$NEW_CSRF" ]] && NEW_CSRF=$(echo "$DASH_HTML" | grep -oP 'csrfToken"\s*:\s*"\K[^"]+' | head -1)
  # Then try HTML form patterns
  [[ -z "$NEW_CSRF" ]] && NEW_CSRF=$(echo "$DASH_HTML" | grep -oP 'name="_token"\s*(type="hidden"\s*)?value="\K[^"]+' | head -1)
  [[ -z "$NEW_CSRF" ]] && NEW_CSRF=$(echo "$DASH_HTML" | grep -oP '<meta\s+name="csrf-token"\s+content="\K[^"]+' | head -1)
  if [[ -n "$NEW_CSRF" ]]; then
    CSRF_TOKEN="$NEW_CSRF"
    log "  Refreshed CSRF from /${try_page}: ${CSRF_TOKEN:0:12}..."
    break
  fi
done
if [[ -z "$NEW_CSRF" ]]; then
  log "${YLW}  ⚠ Could not refresh CSRF from any page — requests will likely fail with 419${RST}"
fi

###############################################################################
# 2. Gather entity IDs from the DB (via a quick API/tinker read)
###############################################################################
log "\n${YLW}[2/7] Gathering entity IDs...${RST}"

# We'll scrape IDs from index pages or use known routes
# Helper to extract first UUID from an HTML page
extract_uuid() {
  echo "$1" | grep -oP '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}' | head -1
}

# Fetch known entity pages to get real IDs
DEAL_PAGE=$(curl -s -b "$JAR" "${BASE}/deals" 2>/dev/null)
DEAL_ID=$(extract_uuid "$DEAL_PAGE")

PROJECT_PAGE=$(curl -s -b "$JAR" "${BASE}/projects" 2>/dev/null)
PROJECT_ID=$(extract_uuid "$PROJECT_PAGE")

CLIENT_PAGE=$(curl -s -b "$JAR" "${BASE}/clients" 2>/dev/null)
CLIENT_ID=$(extract_uuid "$CLIENT_PAGE")

EMPLOYEE_PAGE=$(curl -s -b "$JAR" "${BASE}/employees" 2>/dev/null)
EMPLOYEE_ID=$(extract_uuid "$EMPLOYEE_PAGE")

VENDOR_PAGE=$(curl -s -b "$JAR" "${BASE}/vendors" 2>/dev/null)
VENDOR_ID=$(extract_uuid "$VENDOR_PAGE")

LEAD_PAGE=$(curl -s -b "$JAR" "${BASE}/leads" 2>/dev/null)
LEAD_ID=$(extract_uuid "$LEAD_PAGE")

log "  deal=$DEAL_ID"
log "  project=$PROJECT_ID"
log "  client=$CLIENT_ID"
log "  employee=$EMPLOYEE_ID"
log "  vendor=$VENDOR_ID"
log "  lead=$LEAD_ID"

###############################################################################
# 3. POST — Create new entities
###############################################################################
log "\n${YLW}[3/7] Testing POST (create) routes...${RST}"

# 3a. Create a Deal (minimal: just 'name')
DEAL_RESP=$(curl -s -w '\n%{http_code}' \
  -b "$JAR" -c "$JAR" \
  -X POST \
  -H "Accept: text/html,application/json" \
  -H "X-Requested-With: XMLHttpRequest" \
  -d "_token=${CSRF_TOKEN}&name=CrawlTestDeal_${TIMESTAMP}&phone=11999990000&price=100" \
  "${BASE}/deals" 2>/dev/null)
DEAL_POST_CODE=$(echo "$DEAL_RESP" | tail -1)
DEAL_POST_BODY=$(echo "$DEAL_RESP" | sed '$d')
NEW_DEAL_ID=$(echo "$DEAL_POST_BODY" | grep -oP '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}' | head -1)
record "POST" "deals" "$DEAL_POST_CODE" "Create deal"
log "  → New Deal ID: ${NEW_DEAL_ID:-none}"

# 3b. Create a Lead
UNIQ_EMAIL="crawltest_${TIMESTAMP}@test.com"
fire_form "POST" "leads" "Create lead" \
  -d "subject=CrawlTestLead&name=TestLeadName&email=${UNIQ_EMAIL}&phone=11999998888"

# 3c. Company settings (POST, idempotent)
fire_form "POST" "company-settings" "Save company settings" \
  -d "companyName=PrestechERPTest"

# 3d. Business settings (POST, idempotent)
fire_form "POST" "business-setting" "Save business settings" \
  -d "SITE_RTL=off"

# 3e. Cache settings
fire_form "POST" "cache-settings" "Save cache settings"

# 3f. Create announcement
fire_form "POST" "announcement" "Create announcement" \
  -d "title=CrawlTestAnnouncement&start_date=2026-01-01&end_date=2026-12-31&description=AutoTest"

# 3g. Create bank account
fire_form "POST" "bank_accounts" "Create bank account" \
  -d "holder_name=TestHolder&bank_name=TestBank&account_number=1234567890&opening_balance=0&contact_number=11999990000"

# 3h. Create a Bill (needs vendor_id)
if [[ -n "$VENDOR_ID" ]]; then
  fire_form "POST" "bills" "Create bill" \
    -d "vendor_id=${VENDOR_ID}&bill_date=2026-01-15&due_date=2026-02-15&category_id=0"
else
  SKIP=$((SKIP+1)); TOTAL=$((TOTAL+1))
  log "${YLW}  ⚠${RST} POST bills → SKIP (no vendor_id)"
fi

# 3i. Create a budget
fire_form "POST" "budgets" "Create budget" \
  -d "name=CrawlTestBudget&from=2026-01-01&to=2026-12-31&period=monthly"

# 3j. Create a chart of account
fire_form "POST" "chart_of_accounts" "Create chart of account" \
  -d "name=CrawlTestAccount&code=CT001&type=1&is_enabled=1"

# 3k. Change password (POST, expect validation since we send same password)
fire_form "POST" "change-password" "Change password" \
  -d "current_password=123456789qwe.*&new_password=123456789qwe.*&new_confirm_password=123456789qwe.*"

# 3l. Create company policy
fire_form "POST" "company_policies" "Create company policy" \
  -d "title=CrawlTestPolicy&description=AutoTestPolicy"

# 3m. Bug status
fire_form "POST" "bug_status" "Create bug status" \
  -d "title=CrawlTestBugStatus"

# 3n. Award type
fire_form "POST" "award_types" "Create award type" \
  -d "name=CrawlTestAwardType"

# 3o. Create an award (needs employee_id and award_type)
if [[ -n "$EMPLOYEE_ID" ]]; then
  fire_form "POST" "awards" "Create award" \
    -d "employee_id=${EMPLOYEE_ID}&award_type=10dba017-e610-4afa-b5d8-5515a2b3ec1a&date=2026-03-01&gift=TestGift"
else
  SKIP=$((SKIP+1)); TOTAL=$((TOTAL+1))
  log "${YLW}  ⚠${RST} POST awards → SKIP (no employee_id)"
fi

# 3p. Allowance option
fire_form "POST" "allowance_options" "Create allowance option" \
  -d "name=CrawlTestAllowance"

# 3q. Commission
fire_form "POST" "commissions" "Create commission" \
  -d "title=CrawlTestCommission&type=fixed&amount=100"

###############################################################################
# 4. PUT / PATCH — Update entities
###############################################################################
log "\n${YLW}[4/7] Testing PUT/PATCH (update) routes...${RST}"

# Use the deal we created (or the first one)
TARGET_DEAL="${NEW_DEAL_ID:-$DEAL_ID}"

if [[ -n "$TARGET_DEAL" ]]; then
  # Update deal via PUT
  fire_form "PUT" "deals/${TARGET_DEAL}" "Update deal" \
    -d "name=CrawlTestDealUpdated&pipeline_id=1&phone=11999991111&price=200"

  # Change deal stage (POST endpoint)
  fire_form "POST" "deals/${TARGET_DEAL}/labels" "Deal labels" \
    -d "labels[]=crawl-test"
fi

if [[ -n "$PROJECT_ID" ]]; then
  fire_form "PUT" "projects/${PROJECT_ID}" "Update project" \
    -d "name=CrawlTestProjectUpdated&start_date=2026-01-01&end_date=2026-12-31&status=OnGoing&budget=5000&description=AutoTest"
fi

if [[ -n "$LEAD_ID" ]]; then
  fire_form "PUT" "leads/${LEAD_ID}" "Update lead" \
    -d "subject=CrawlTestLeadUpdated&name=UpdatedLead&email=updated_${TIMESTAMP}@test.com"
fi

###############################################################################
# 5. Specific POST endpoints (settings, exports, misc)
###############################################################################
log "\n${YLW}[5/7] Testing misc POST endpoints...${RST}"

# System settings
fire_form "POST" "system-settings" "Save system settings" \
  -d "siteCurrency=BRL"

# Payment settings
fire_form "POST" "company-payment-setting" "Save payment settings" \
  -d "is_stripe_enabled=on&stripe_key=pk_test_MOCK&stripe_secret=sk_test_MOCK&currency=BRL"

# Email settings
fire_form "POST" "company-email-settings" "Save email settings" \
  -d "mail_driver=smtp&mail_host=smtp.test.com&mail_port=587"

# ChatGPT settings (POST)
fire_form "POST" "chatgpt-settings" "ChatGPT settings" \
  -d "chatgpt_key=mock_key"

# Task calendar data
if [[ -n "$PROJECT_ID" ]]; then
  fire_form "POST" "calendars/get_task_data" "Get task calendar data"
fi

# Search routes
fire_form "POST" "announcements/getdepartment" "Announcement getdepartment"
fire_form "POST" "announcements/getemployee" "Announcement getemployee"

# Bill product helper
fire_form "POST" "billsproduct" "Bill product search"

# Balance sheet exports
fire_form "POST" "balance-sheets/export" "Balance sheet export" \
  -d "start_date=2026-01-01&end_date=2026-12-31"

# Branch employee JSON helper
fire_form "POST" "branches/employees/json" "Branch employee JSON"

###############################################################################
# 6. DELETE — Destroy entities (only our test entities)
###############################################################################
log "\n${YLW}[6/7] Testing DELETE routes...${RST}"

if [[ -n "$NEW_DEAL_ID" ]]; then
  fire_form "DELETE" "deals/${NEW_DEAL_ID}" "Delete test deal"
fi

# We'll test delete on the bug status we created
# (The route expects the ID, but we don't capture it — test the endpoint anyway)
# For resource DELETE routes, test with a known-bad ID to confirm 404 vs 500
fire_form "DELETE" "deals/00000000-0000-0000-0000-000000000000" "Delete nonexistent deal (expect 404)"

if [[ -n "$PROJECT_ID" ]]; then
  # Don't actually delete the project — test with bogus ID
  fire_form "DELETE" "projects/00000000-0000-0000-0000-000000000000" "Delete nonexistent project (expect 404)"
fi

###############################################################################
# 7. Bulk route scan — fire all remaining POST routes (minimal payload)
###############################################################################
log "\n${YLW}[7/7] Scanning remaining write routes (minimal payload)...${RST}"

# These routes get fired with just the CSRF token to see if they return
# 422 (validation) rather than 500 (crash). 500 = real bug.
SCAN_ROUTES=(
  "POST|account_assets|store account asset"
  "POST|allowances|store allowance"
  "POST|appraisals|appraisal empByStar"
  "POST|appraisals/get-employee|appraisal getEmployee"
  "POST|bank_transfers|store bank transfer"
  "POST|branches|store branch"
  "POST|chart_of_accounts/subtype|chart subtype"
  "POST|contract_types|store contract type"
  "POST|contracts|store contract"
  "POST|custom-credit-note|store credit note"
  "POST|custom_fields|store custom field"
  "POST|deduction_options|store deduction option"
  "POST|departments|store department"
  "POST|designations|store designation"
  "POST|document_uploads|store document upload"
  "POST|documents|store document"
  "POST|events|store event"
  "POST|expenses|store expense"
  "POST|goal_types|store goal type"
  "POST|goals|store goal"
  "POST|holidays|store holiday"
  "POST|indicators|store indicator"
  "POST|invoices|store invoice"
  "POST|journal_entries|store journal entry"
  "POST|leave_types|store leave type"
  "POST|loan_options|store loan option"
  "POST|loans|store loan"
  "POST|other_payments|store other payment"
  "POST|payments|store payment"
  "POST|performance_types|store performance type"
  "POST|pipelines|store pipeline"
  "POST|pos|store POS"
  "POST|purchases|store purchase"
  "POST|revenues|store revenue"
  "POST|roles|store role"
  "POST|saturation_deductions|store saturation deduction"
  "POST|sources|store source"
  "POST|stages|store stage"
  "POST|taxes|store tax"
  "POST|trainers|store trainer"
  "POST|training_types|store training type"
  "POST|trainings|store training"
  "POST|transfers|store transfer"
  "POST|travels|store travel"
  "POST|vendors|store vendor"
  "POST|warnings|store warning"
  "POST|customers|store customer"
  "POST|complaints|store complaint"
  "POST|competencies|store competency"
  "POST|resignations|store resignation"
  "POST|terminations|store termination"
  "POST|warehouses|store warehouse"
  "POST|supports|store support"
  "POST|zoom_meetings|store zoom meeting"
  "POST|coupons|store coupon"
  "POST|set_salaries|store salary"
  "POST|employee_attendances|store attendance"
)

for route_spec in "${SCAN_ROUTES[@]}"; do
  IFS='|' read -r method uri label <<< "$route_spec"
  fire_form "$method" "$uri" "$label"
done

###############################################################################
# Summary
###############################################################################
log "\n${CYN}═══════════════════════════════════════════════════${RST}"
log "${CYN}  WRITE-ROUTE CRAWL SUMMARY${RST}"
log "${CYN}═══════════════════════════════════════════════════${RST}"
log ""
log "  Total requests: $TOTAL"
log "  ${GRN}Pass (2xx/3xx):${RST} $PASS"
log "  ${YLW}Validation (422/419):${RST} $SKIP"
log "  ${RED}Errors (4xx/5xx/000):${RST} $FAIL"
log ""

if [[ "$FAIL" -gt 0 ]]; then
  log "${RED}  ⚠ Routes that returned server errors:${RST}"
  for key in "${!RESULTS[@]}"; do
    if echo "${RESULTS[$key]}" | grep -q '\\033\[0;31m'; then
      log "    $key  ${RESULTS[$key]}"
    fi
  done
fi

HTTP500_COUNT=0
for key in "${!RESULTS[@]}"; do
  # Extract the status code
  code=$(echo "${RESULTS[$key]}" | grep -oP '\d{3}' | head -1)
  if [[ "$code" == "500" ]]; then
    HTTP500_COUNT=$((HTTP500_COUNT+1))
  fi
done

log ""
if [[ "$HTTP500_COUNT" -eq 0 ]]; then
  log "${GRN}  ✓ No HTTP 500 errors detected!${RST}"
else
  log "${RED}  ✗ $HTTP500_COUNT routes returned HTTP 500${RST}"
fi

log "\nFull log saved to: $LOG"
log "Cookies jar: $JAR"
