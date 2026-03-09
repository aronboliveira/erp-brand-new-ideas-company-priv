#!/usr/bin/env bash
# ============================================================================
# curl-route-tester.sh — Test ERP routes via curl for errors/redirects
# Checks GET routes for 500 errors, unexpected redirects, broken views
# Usage: bash curl-route-tester.sh [BASE_URL]
# ============================================================================
set -uo pipefail

BASE_URL="${1:-http://127.0.0.1:8000}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LARAVEL_DIR="$(cd "$SCRIPT_DIR/../../../../laravel" && pwd)"
COOKIE_JAR="/tmp/erp_curl_cookies.txt"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

ERRORS=0
REDIRECTS=0
OK=0
TOTAL=0

# Helper: test a single route
test_route() {
  local method="$1"
  local path="$2"
  local url="${BASE_URL}${path}"
  TOTAL=$((TOTAL + 1))

  local response
  response=$(curl -s -o /dev/null -w "%{http_code}|%{redirect_url}|%{size_download}" \
    -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
    -L --max-redirs 5 \
    --connect-timeout 10 \
    --max-time 30 \
    "$url" 2>/dev/null || echo "000||0")

  local status_code redirect_url size
  IFS='|' read -r status_code redirect_url size <<< "$response"

  if [[ "$status_code" -ge 500 ]]; then
    echo -e "${RED}ERROR [$status_code] $method $path${NC}"
    ERRORS=$((ERRORS + 1))
  elif [[ "$status_code" -ge 300 && "$status_code" -lt 400 ]]; then
    echo -e "${YELLOW}REDIRECT [$status_code] $method $path → $redirect_url${NC}"
    REDIRECTS=$((REDIRECTS + 1))
  elif [[ "$status_code" == "000" ]]; then
    echo -e "${RED}ERROR [TIMEOUT] $method $path${NC}"
    ERRORS=$((ERRORS + 1))
  elif [[ "$size" -lt 100 && "$status_code" == "200" ]]; then
    echo -e "${YELLOW}WARN [EMPTY] $method $path (${size} bytes)${NC}"
  else
    echo -e "${GREEN}OK [$status_code] $method $path (${size}B)${NC}"
    OK=$((OK + 1))
  fi
}

# Helper: attempt login to get session
attempt_login() {
  echo -e "${YELLOW}▶ Attempting login...${NC}"
  # Get CSRF token from login page
  local csrf
  csrf=$(curl -s -c "$COOKIE_JAR" "${BASE_URL}/login" | grep -oP 'name="_token" value="\K[^"]+' | head -1)

  if [[ -n "$csrf" ]]; then
    curl -s -o /dev/null -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
      -X POST "${BASE_URL}/login" \
      -d "email=admin@example.com&password=1234&_token=$csrf" \
      -L 2>/dev/null
    echo -e "${GREEN}✓ Login attempted${NC}"
  else
    echo -e "${YELLOW}⚠ Could not extract CSRF token${NC}"
  fi
}

echo "═══════════════════════════════════════════════"
echo " ERP Prestech — Curl Route Tester"
echo " Base URL: $BASE_URL"
echo " $(date '+%Y-%m-%d %H:%M:%S')"
echo "═══════════════════════════════════════════════"

# Public routes (no auth needed)
echo -e "\n${YELLOW}▶ Testing public routes...${NC}"
PUBLIC_ROUTES=(
  "/"
  "/login"
  "/register"
  "/about_us"
  "/features"
  "/pricing"
  "/faqs"
  "/contact_us"
)

for route in "${PUBLIC_ROUTES[@]}"; do
  test_route "GET" "$route"
done

# Attempt login for auth routes
attempt_login

echo -e "\n${YELLOW}▶ Testing authenticated routes...${NC}"
AUTH_ROUTES=(
  "/account-dashboard"
  "/invoices"
  "/bills"
  "/proposals"
  "/customers"
  "/vendors"
  "/employees"
  "/bank_accounts"
  "/products"
  "/leads"
  "/projects"
  "/account_assets"
  "/allowances"
  "/announcement"
  "/appraisals"
  "/awards"
  "/bank_transfers"
  "/contracts"
  "/deals"
  "/expenses"
  "/goals"
  "/holidays"
  "/meetings"
  "/payslips"
  "/taxes"
)

for route in "${AUTH_ROUTES[@]}"; do
  test_route "GET" "$route"
done

# Check create forms
echo -e "\n${YELLOW}▶ Testing create forms...${NC}"
FORM_ROUTES=(
  "/invoices/create"
  "/bills/create"
  "/proposals/create"
  "/customers/create"
  "/vendors/create"
  "/employees/create"
  "/products/create"
)

for route in "${FORM_ROUTES[@]}"; do
  test_route "GET" "$route"
done

# Summary
echo -e "\n═══════════════════════════════════════════════"
echo -e " RESULTS: ${GREEN}${OK} OK${NC}, ${YELLOW}${REDIRECTS} redirects${NC}, ${RED}${ERRORS} errors${NC} / ${TOTAL} total"
echo "═══════════════════════════════════════════════"

rm -f "$COOKIE_JAR"
exit $ERRORS
