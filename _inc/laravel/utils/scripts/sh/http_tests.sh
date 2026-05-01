#!/usr/bin/env bash
# ──────────────────────────────────────────────────────────────
# http_tests.sh — curl + wget HTTP smoke tests for erp_brand_new_ideas_company
#
# Tests all major routes for status codes, response content,
# headers, timing, and basic security headers.
#
# Usage:  ./http_tests.sh [--curl|--wget|--both] [--auth] [--json]
#         ./http_tests.sh --help
#
# Env: BASE_URL, ADMIN_EMAIL, ADMIN_PASS, CSRF_TOKEN
# ──────────────────────────────────────────────────────────────
set -euo pipefail

# ── Config ────────────────────────────────────────────────────
BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"
ADMIN_EMAIL="${ADMIN_EMAIL:-admin@example.com}"
ADMIN_PASS="${ADMIN_PASS:-password}"
COOKIE_JAR="/tmp/erp_http_test_cookies.txt"
TIMEOUT=15
TOOL="${1:---both}"

RED='\033[0;31m'; GRN='\033[0;32m'; YLW='\033[0;33m'; CYN='\033[0;36m'; RST='\033[0m'
PASS_N=0; FAIL_N=0; WARN_N=0; SKIP_N=0

ok()   { ((PASS_N++)); printf "${GRN}[PASS]${RST} %s\n" "$1"; }
fail() { ((FAIL_N++)); printf "${RED}[FAIL]${RST} %s\n" "$1"; }
warn() { ((WARN_N++)); printf "${YLW}[WARN]${RST} %s\n" "$1"; }
skip() { ((SKIP_N++)); printf "${YLW}[SKIP]${RST} %s\n" "$1"; }
info() { printf "${CYN}[INFO]${RST} %s\n" "$1"; }
hdr()  { printf "\n${CYN}═══ %s ═══${RST}\n" "$1"; }

# ── Helpers ───────────────────────────────────────────────────
has_curl() { command -v curl &>/dev/null; }
has_wget() { command -v wget &>/dev/null; }

# curl: GET a URL, return "status_code body"
curl_get() {
  local url="$1"
  local status body
  status=$(curl -s -o /dev/null -w "%{http_code}" \
    --max-time "$TIMEOUT" -b "$COOKIE_JAR" "$url" 2>/dev/null || echo "000")
  body=$(curl -s --max-time "$TIMEOUT" -b "$COOKIE_JAR" "$url" 2>/dev/null || echo "")
  echo "$status"
  echo "$body"
}

# curl: detailed timing (returns namelookup|connect|starttransfer|total|status)
curl_timing() {
  local url="$1"
  curl -s -o /dev/null \
    -w "%{time_namelookup}|%{time_connect}|%{time_starttransfer}|%{time_total}|%{http_code}" \
    --max-time "$TIMEOUT" -b "$COOKIE_JAR" "$url" 2>/dev/null || echo "0|0|0|0|000"
}

# curl: get response headers
curl_headers() {
  local url="$1"
  curl -s -I --max-time "$TIMEOUT" -b "$COOKIE_JAR" "$url" 2>/dev/null || echo ""
}

# wget: GET status code
wget_status() {
  local url="$1"
  local code
  code=$(wget --spider -S --max-redirect=5 --timeout="$TIMEOUT" "$url" 2>&1 \
    | grep "HTTP/" | tail -1 | awk '{print $2}')
  echo "${code:-000}"
}

# wget: download body
wget_body() {
  local url="$1"
  wget -q -O - --max-redirect=5 --timeout="$TIMEOUT" "$url" 2>/dev/null || echo ""
}

# ── Auth (get CSRF + session cookie) ─────────────────────────
do_auth() {
  hdr "Authentication"
  if ! has_curl; then
    skip "curl not available for auth"
    return
  fi

  # Get CSRF token from login page
  local login_page
  login_page=$(curl -s -c "$COOKIE_JAR" --max-time "$TIMEOUT" "${BASE_URL}/login" 2>/dev/null)
  if [[ -z "$login_page" ]]; then
    fail "Cannot reach login page"
    return
  fi
  ok "Login page reachable"

  local csrf
  csrf=$(echo "$login_page" | grep -oP 'name="_token"\s+value="\K[^"]+' | head -1 || true)
  if [[ -z "$csrf" ]]; then
    csrf=$(echo "$login_page" | grep -oP "csrf[_-]token.*?content=\"\K[^\"]*" | head -1 || true)
  fi

  if [[ -z "$csrf" ]]; then
    warn "CSRF token not found — skipping login"
    return
  fi
  ok "CSRF token extracted"

  # POST login
  local login_code
  login_code=$(curl -s -o /dev/null -w "%{http_code}" \
    -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
    --max-time "$TIMEOUT" \
    -d "email=${ADMIN_EMAIL}&password=${ADMIN_PASS}&_token=${csrf}" \
    -L "${BASE_URL}/login" 2>/dev/null)

  if [[ "$login_code" == "200" || "$login_code" == "302" ]]; then
    ok "Login succeeded (HTTP ${login_code})"
  else
    fail "Login failed (HTTP ${login_code})"
  fi
}

# ── Route tests ───────────────────────────────────────────────
# Format: "METHOD|PATH|EXPECTED_STATUS|DESCRIPTION|BODY_CONTAINS"
ROUTES=(
  "GET|/|200|Homepage / redirect|"
  "GET|/login|200|Login page|email"
  "GET|/register|200|Register page|"
  "GET|/password/reset|200|Password reset|"
  "GET|/dashboard|302|Dashboard (auth redirect)|login"
  "GET|/clients|302|Clients (auth redirect)|login"
  "GET|/projects|302|Projects (auth redirect)|login"
  "GET|/invoices|302|Invoices (auth redirect)|login"
  "GET|/settings|302|Settings (auth redirect)|login"
  "GET|/api/user|401|API auth check|Unauthenticated"
  "GET|/favicon.ico|200|Static asset|"
)

# Authenticated routes (only tested if auth succeeds)
AUTH_ROUTES=(
  "GET|/dashboard|200|Dashboard|dashboard"
  "GET|/clients|200|Clients index|"
  "GET|/projects|200|Projects index|"
  "GET|/users|200|Users index|"
  "GET|/leads|200|Leads index|"
  "GET|/deals|200|Deals index|"
  "GET|/schedules|200|Schedules index|"
  "GET|/settings|200|Settings index|"
)

test_route_curl() {
  local method="$1" path="$2" expected="$3" desc="$4" body_check="$5"
  local url="${BASE_URL}${path}"

  local result
  result=$(curl_get "$url")
  local status=$(echo "$result" | head -1)
  local body=$(echo "$result" | tail -n +2)

  if [[ "$status" == "$expected" ]]; then
    ok "curl ${method} ${path} → ${status} (${desc})"
  else
    fail "curl ${method} ${path} → ${status} (expected ${expected}) (${desc})"
  fi

  if [[ -n "$body_check" ]]; then
    if echo "$body" | grep -qi "$body_check"; then
      ok "  Body contains '${body_check}'"
    else
      warn "  Body missing '${body_check}'"
    fi
  fi
}

test_route_wget() {
  local method="$1" path="$2" expected="$3" desc="$4" body_check="$5"
  local url="${BASE_URL}${path}"

  if [[ "$method" != "GET" ]]; then
    skip "wget: ${method} ${path} (wget only supports GET easily)"
    return
  fi

  local status
  status=$(wget_status "$url")

  if [[ "$status" == "$expected" ]]; then
    ok "wget ${method} ${path} → ${status} (${desc})"
  else
    fail "wget ${method} ${path} → ${status} (expected ${expected}) (${desc})"
  fi

  if [[ -n "$body_check" ]]; then
    local body
    body=$(wget_body "$url")
    if echo "$body" | grep -qi "$body_check"; then
      ok "  Body contains '${body_check}'"
    else
      warn "  Body missing '${body_check}'"
    fi
  fi
}

# ── Security header checks ───────────────────────────────────
check_security_headers() {
  hdr "Security Headers"
  if ! has_curl; then
    skip "curl not available for header checks"
    return
  fi

  local headers
  headers=$(curl_headers "${BASE_URL}/login")

  local sec_headers=(
    "X-Frame-Options"
    "X-Content-Type-Options"
    "X-XSS-Protection"
    "Strict-Transport-Security"
    "Content-Security-Policy"
    "Referrer-Policy"
  )

  for h in "${sec_headers[@]}"; do
    if echo "$headers" | grep -qi "^${h}:"; then
      ok "Header present: ${h}"
    else
      warn "Header missing: ${h}"
    fi
  done
}

# ── Timing benchmark ─────────────────────────────────────────
benchmark_routes() {
  hdr "Response Time Benchmark"
  if ! has_curl; then
    skip "curl not available for benchmarks"
    return
  fi

  local paths=("/" "/login" "/dashboard" "/api/user")
  printf "  %-20s %10s %10s %10s %10s %6s\n" "Path" "DNS" "Connect" "TTFB" "Total" "Status"
  printf "  %-20s %10s %10s %10s %10s %6s\n" "$(printf '─%.0s' {1..20})" \
    "$(printf '─%.0s' {1..10})" "$(printf '─%.0s' {1..10})" \
    "$(printf '─%.0s' {1..10})" "$(printf '─%.0s' {1..10})" "$(printf '─%.0s' {1..6})"

  for p in "${paths[@]}"; do
    local raw
    raw=$(curl_timing "${BASE_URL}${p}")
    IFS='|' read -r dns conn ttfb total status <<< "$raw"
    printf "  %-20s %9ss %9ss %9ss %9ss %6s\n" "$p" "$dns" "$conn" "$ttfb" "$total" "$status"
  done
}

# ── Run tests ─────────────────────────────────────────────────
run_tests() {
  hdr "Public Route Tests"

  for route in "${ROUTES[@]}"; do
    IFS='|' read -r method path expected desc body_check <<< "$route"
    case "$TOOL" in
      --curl)  has_curl && test_route_curl "$method" "$path" "$expected" "$desc" "$body_check" || skip "curl: $desc" ;;
      --wget)  has_wget && test_route_wget "$method" "$path" "$expected" "$desc" "$body_check" || skip "wget: $desc" ;;
      --both|*)
        has_curl && test_route_curl "$method" "$path" "$expected" "$desc" "$body_check" || skip "curl: $desc"
        has_wget && test_route_wget "$method" "$path" "$expected" "$desc" "$body_check" || skip "wget: $desc"
        ;;
    esac
  done
}

run_auth_tests() {
  hdr "Authenticated Route Tests"
  if [[ ! -f "$COOKIE_JAR" ]] || ! grep -q "laravel_session" "$COOKIE_JAR" 2>/dev/null; then
    skip "No session — skipping auth route tests"
    return
  fi

  for route in "${AUTH_ROUTES[@]}"; do
    IFS='|' read -r method path expected desc body_check <<< "$route"
    has_curl && test_route_curl "$method" "$path" "$expected" "$desc" "$body_check" || skip "auth: $desc"
  done
}

# ── Main ──────────────────────────────────────────────────────
case "${1:---both}" in
  --help|-h)
    echo "Usage: $0 [--curl|--wget|--both] [--auth] [--json]"
    echo "  --curl    Use curl only"
    echo "  --wget    Use wget only"
    echo "  --both    Use both (default)"
    echo "  --auth    Include authentication + auth-only routes"
    echo ""
    echo "Env: BASE_URL (default http://127.0.0.1:8000)"
    exit 0
    ;;
esac

hdr "HTTP Smoke Tests — ${BASE_URL}"
info "Tool: ${TOOL} | Timeout: ${TIMEOUT}s"

# Check if server is reachable
if has_curl; then
  if ! curl -s --max-time 5 "${BASE_URL}" &>/dev/null; then
    fail "Server unreachable at ${BASE_URL}"
    echo "Start the server with: php artisan serve"
    exit 1
  fi
  ok "Server reachable"
fi

run_tests

if [[ "${*}" == *"--auth"* ]]; then
  do_auth
  run_auth_tests
fi

check_security_headers
benchmark_routes

hdr "Summary"
printf "${GRN}PASS: %d${RST}  ${YLW}WARN: %d${RST}  ${RED}FAIL: %d${RST}  SKIP: %d\n" \
  "$PASS_N" "$WARN_N" "$FAIL_N" "$SKIP_N"

rm -f "$COOKIE_JAR" 2>/dev/null || true

if (( FAIL_N > 0 )); then exit 1; fi
