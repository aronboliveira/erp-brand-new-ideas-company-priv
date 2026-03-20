#!/usr/bin/env bash
# Route Health Check Suite
# Tests all GET routes for http_code, speed_download, time_starttransfer
set -euo pipefail

BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"
ADMIN_EMAIL="u_68ca0ef2-8cf2-4930-9129-24da61a4874a@test.local"
ADMIN_PASS="password"
REPORT_DIR="${REPORT_DIR:-$(dirname "$0")/../../.tmp/copilot/routes-$(date +%Y%m%dT%H%M)}"
mkdir -p "$REPORT_DIR"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo "=== Route Health Check Suite ==="
echo "Base URL: $BASE_URL"
echo "Report:   $REPORT_DIR/route_health_report.csv"
echo ""

# Step 1: Get CSRF token and session cookie
echo "[1/4] Authenticating..."
COOKIE_JAR="$REPORT_DIR/.cookies.txt"
rm -f "$COOKIE_JAR"

# Get CSRF token from login page
LOGIN_PAGE=$(curl -s -c "$COOKIE_JAR" "$BASE_URL/login" 2>/dev/null)
CSRF_TOKEN=$(echo "$LOGIN_PAGE" | grep -oP 'name="_token"[^>]*value="\K[^"]+' | head -1 || true)

if [ -z "$CSRF_TOKEN" ]; then
    echo "  Warning: Could not extract CSRF token. Trying meta tag..."
    CSRF_TOKEN=$(echo "$LOGIN_PAGE" | grep -oP 'content="\K[^"]+(?="[^>]*name="csrf-token")' | head -1 || true)
fi

if [ -z "$CSRF_TOKEN" ]; then
    echo "  Warning: No CSRF token found. Continuing without auth..."
    AUTH_OK=0
else
    # Authenticate
    AUTH_RESP=$(curl -s -b "$COOKIE_JAR" -c "$COOKIE_JAR" \
        -X POST "$BASE_URL/login" \
        -H "Content-Type: application/x-www-form-urlencoded" \
        -d "_token=$CSRF_TOKEN&email=$ADMIN_EMAIL&password=$ADMIN_PASS" \
        -w "%{http_code}" -o /dev/null 2>/dev/null)

    if [ "$AUTH_RESP" = "302" ] || [ "$AUTH_RESP" = "200" ]; then
        echo "  Authenticated successfully (HTTP $AUTH_RESP)"
        AUTH_OK=1
    else
        echo "  Authentication failed (HTTP $AUTH_RESP). Continuing without auth."
        AUTH_OK=0
    fi
fi

# Step 2: Get route list from artisan
echo "[2/4] Collecting routes..."
cd "$(dirname "$0")/../.."
ROUTES_JSON=$(php -d memory_limit=512M artisan route:list --json 2>/dev/null)

# Extract GET routes without parameters (directly testable)
echo "$ROUTES_JSON" | python3 -c "
import json, sys
routes = json.load(sys.stdin)
get_routes = [r for r in routes if 'GET' in r.get('method','')]
# No-param routes first
no_param = [r for r in get_routes if '{' not in r.get('uri','')]
# Filter out debugbar, ignition, sanctum, internal
skip = ['_debugbar', '_ignition', 'sanctum', 'livewire', 'broadcasting', '__clockwork']
filtered = [r for r in no_param if not any(s in r.get('uri','') for s in skip)]
for r in filtered:
    print(r['uri'])
" > "$REPORT_DIR/.routes_to_test.txt"

ROUTE_COUNT=$(wc -l < "$REPORT_DIR/.routes_to_test.txt")
echo "  Found $ROUTE_COUNT testable GET routes (no params, no internal)"

# Step 3: Test each route
echo "[3/4] Testing routes..."
echo "route,http_code,speed_download,time_starttransfer,size_download,redirect_url" > "$REPORT_DIR/route_health_report.csv"

PASS=0
FAIL=0
REDIRECT=0
SLOW=0
ERRORS=""

while IFS= read -r route; do
    # Skip empty lines
    [ -z "$route" ] && continue

    URL="$BASE_URL/$route"

    RESULT=$(curl -s -b "$COOKIE_JAR" -L --max-redirs 5 \
        -o /dev/null \
        -w "%{http_code},%{speed_download},%{time_starttransfer},%{size_download},%{redirect_url}" \
        --max-time 30 \
        "$URL" 2>/dev/null || echo "000,0,0,0,timeout")

    HTTP_CODE=$(echo "$RESULT" | cut -d',' -f1)
    SPEED=$(echo "$RESULT" | cut -d',' -f2)
    TTFB=$(echo "$RESULT" | cut -d',' -f3)
    SIZE=$(echo "$RESULT" | cut -d',' -f4)
    REDIR=$(echo "$RESULT" | cut -d',' -f5)

    echo "$route,$RESULT" >> "$REPORT_DIR/route_health_report.csv"

    # Classify
    case "$HTTP_CODE" in
        200) 
            PASS=$((PASS + 1))
            # Check for slow responses (> 2s TTFB)
            if (( $(echo "$TTFB > 2.0" | bc -l 2>/dev/null || echo 0) )); then
                SLOW=$((SLOW + 1))
                printf "  ${YELLOW}SLOW${NC} %-60s %s %ss\n" "$route" "$HTTP_CODE" "$TTFB"
            fi
            ;;
        301|302|303|307|308)
            REDIRECT=$((REDIRECT + 1))
            ;;
        401|403)
            # Auth-required routes - expected if not authenticated
            if [ "$AUTH_OK" = "1" ]; then
                FAIL=$((FAIL + 1))
                ERRORS="${ERRORS}  AUTHFAIL $route -> $HTTP_CODE\n"
                printf "  ${RED}FAIL${NC} %-60s %s (auth but forbidden)\n" "$route" "$HTTP_CODE"
            else
                REDIRECT=$((REDIRECT + 1))
            fi
            ;;
        404)
            FAIL=$((FAIL + 1))
            ERRORS="${ERRORS}  404 $route\n"
            printf "  ${RED}404 ${NC} %-60s\n" "$route"
            ;;
        500|502|503)
            FAIL=$((FAIL + 1))
            ERRORS="${ERRORS}  ${HTTP_CODE} $route\n"
            printf "  ${RED}${HTTP_CODE} ${NC} %-60s\n" "$route"
            ;;
        000)
            FAIL=$((FAIL + 1))
            ERRORS="${ERRORS}  TIMEOUT $route\n"
            printf "  ${RED}TOUT${NC} %-60s\n" "$route"
            ;;
        *)
            printf "  ${YELLOW}%s${NC}  %-60s\n" "$HTTP_CODE" "$route"
            ;;
    esac
done < "$REPORT_DIR/.routes_to_test.txt"

# Step 4: Summary
echo ""
echo "[4/4] Summary"
echo "============================================"
printf "  ${GREEN}PASS${NC}:      %d\n" "$PASS"
printf "  ${YELLOW}REDIRECT${NC}: %d\n" "$REDIRECT"
printf "  ${RED}FAIL${NC}:      %d\n" "$FAIL"
printf "  ${YELLOW}SLOW${NC}:     %d (TTFB > 2s)\n" "$SLOW"
echo "  TOTAL:     $ROUTE_COUNT"
echo "============================================"

if [ -n "$ERRORS" ]; then
    echo ""
    echo "Error details:"
    printf "$ERRORS"
fi

echo ""
echo "Full report: $REPORT_DIR/route_health_report.csv"

# Cleanup
rm -f "$REPORT_DIR/.cookies.txt" "$REPORT_DIR/.routes_to_test.txt"
