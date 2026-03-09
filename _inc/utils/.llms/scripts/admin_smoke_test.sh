#!/usr/bin/env bash
# Full smoke test with super-admin user — captures real HTTP status codes (no -L)
set -euo pipefail

BASE="http://127.0.0.1:8888"
COOKIE="/tmp/admin_smoke_cookies.txt"
OUTDIR="/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/utils/.llms"
RESULT_JSON="$OUTDIR/admin_smoke_results.json"
RESULT_TXT="$OUTDIR/admin_smoke_summary.txt"

EMAIL='u_1ecb6d5a-e2c5-4961-af3b-0ad83f9d259c@test.local'
PASS='Admin@1234'

rm -f "$COOKIE"

# Step 1: Get CSRF token
echo "=== Logging in as super-admin ==="
TOKEN=$(curl -s -c "$COOKIE" "$BASE/login" | grep -oP 'name="_token"\s+value="\K[^"]+' | head -1)
if [ -z "$TOKEN" ]; then
  echo "FATAL: Could not get CSRF token"
  exit 1
fi

# Step 2: POST login (follow redirect to get session cookie established)
HTTP_LOGIN=$(curl -s -o /dev/null -w "%{http_code}" -c "$COOKIE" -b "$COOKIE" -L \
  -d "_token=$TOKEN&email=$EMAIL&password=$PASS" "$BASE/login")
echo "Login response: $HTTP_LOGIN"

# Verify we're logged in by checking dashboard
HTTP_DASH=$(curl -s -o /dev/null -w "%{http_code}" -b "$COOKIE" "$BASE/")
echo "Dashboard check: $HTTP_DASH"
if [ "$HTTP_DASH" != "200" ]; then
  echo "FATAL: Not authenticated (dashboard returned $HTTP_DASH)"
  exit 1
fi

# Step 3: Get all GET routes from artisan
echo "=== Collecting routes ==="
cd /home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel

ROUTES=$(php artisan route:list --json 2>/dev/null | grep -v 'Constructing\|^$' | python3 -c "
import json, sys, re
routes = json.load(sys.stdin)
seen = set()
for r in routes:
    m = r.get('method','')
    uri = r.get('uri','')
    # Only GET routes, skip API/internal/parameterized
    if 'GET' not in m:
        continue
    if uri.startswith('_') or uri.startswith('sanctum') or uri.startswith('api/'):
        continue
    # Skip routes with required parameters (has {param} without ?)
    if re.search(r'\{[^}?]+\}', uri):
        continue
    # Skip optional-param routes that have been seen as base
    base = re.sub(r'/\{[^}]*\?\}$', '', uri)
    if base in seen and base != uri:
        continue
    if uri not in seen:
        seen.add(uri)
        print(uri)
")

TOTAL=$(echo "$ROUTES" | wc -l)
echo "Total testable GET routes: $TOTAL"

# Step 4: Test each route (NO -L, capture raw status)
echo "=== Testing routes ==="
i=0
declare -a RESULTS_200=()
declare -a RESULTS_3XX=()
declare -a RESULTS_4XX=()
declare -a RESULTS_5XX=()

# Clear rate limit cache periodically  
php artisan cache:clear > /dev/null 2>&1

while IFS= read -r uri; do
  i=$((i + 1))
  
  # Clear cache every 80 requests to avoid LandingPage throttle
  if [ $((i % 80)) -eq 0 ]; then
    php artisan cache:clear > /dev/null 2>&1
  fi
  
  # Capture status AND size (no redirect following!)
  RESP=$(curl -s -o /tmp/admin_smoke_body.tmp -w "%{http_code}|%{size_download}|%{redirect_url}" \
    -b "$COOKIE" --max-time 15 "$BASE/$uri" 2>/dev/null || echo "000|0|")
  
  STATUS=$(echo "$RESP" | cut -d'|' -f1)
  SIZE=$(echo "$RESP" | cut -d'|' -f2)
  REDIR=$(echo "$RESP" | cut -d'|' -f3)
  
  printf "[%3d/%d] %3s %6s %s" "$i" "$TOTAL" "$STATUS" "${SIZE}B" "$uri"
  
  case "$STATUS" in
    200) 
      RESULTS_200+=("$STATUS|$SIZE|$uri")
      echo ""
      ;;
    3[0-9][0-9])
      RESULTS_3XX+=("$STATUS|$SIZE|$uri|$REDIR")
      echo " -> $REDIR"
      ;;
    4[0-9][0-9])
      RESULTS_4XX+=("$STATUS|$SIZE|$uri")
      echo " !!!"
      ;;
    5[0-9][0-9])
      RESULTS_5XX+=("$STATUS|$SIZE|$uri")
      echo " SERVERR"
      ;;
    *)
      RESULTS_5XX+=("$STATUS|$SIZE|$uri")
      echo " ???"
      ;;
  esac
done <<< "$ROUTES"

# Step 5: Write summary
echo ""
echo "================================================================"
echo "SUPER-ADMIN SMOKE TEST RESULTS"
echo "================================================================"
echo "200 OK:     ${#RESULTS_200[@]}"
echo "3xx Redir:  ${#RESULTS_3XX[@]}"
echo "4xx Error:  ${#RESULTS_4XX[@]}"
echo "5xx Error:  ${#RESULTS_5XX[@]}"
echo ""

{
  echo "=========================================="
  echo "SUPER-ADMIN SMOKE TEST — $(date -Iseconds)"
  echo "=========================================="
  echo "Total: $TOTAL | 200: ${#RESULTS_200[@]} | 3xx: ${#RESULTS_3XX[@]} | 4xx: ${#RESULTS_4XX[@]} | 5xx: ${#RESULTS_5XX[@]}"
  echo ""
  
  if [ ${#RESULTS_3XX[@]} -gt 0 ]; then
    echo "--- 3xx REDIRECTS ---"
    for r in "${RESULTS_3XX[@]}"; do echo "  $r"; done
    echo ""
  fi
  
  if [ ${#RESULTS_4XX[@]} -gt 0 ]; then
    echo "--- 4xx CLIENT ERRORS ---"
    for r in "${RESULTS_4XX[@]}"; do echo "  $r"; done
    echo ""
  fi
  
  if [ ${#RESULTS_5XX[@]} -gt 0 ]; then
    echo "--- 5xx SERVER ERRORS ---"
    for r in "${RESULTS_5XX[@]}"; do echo "  $r"; done
    echo ""
  fi
  
  echo "--- 200 OK (small content, possible issues) ---"
  for r in "${RESULTS_200[@]}"; do
    sz=$(echo "$r" | cut -d'|' -f2)
    if [ "$sz" -lt 1000 ] 2>/dev/null; then
      echo "  $r"
    fi
  done
  echo ""
  
  echo "--- 200 OK (all) ---"
  for r in "${RESULTS_200[@]}"; do echo "  $r"; done
} > "$RESULT_TXT"

echo "Results written to: $RESULT_TXT"
echo "Done."
