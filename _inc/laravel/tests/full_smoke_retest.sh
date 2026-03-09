#!/usr/bin/env bash
# Full smoke re-test — all GET routes
set -euo pipefail
COOKIE="/tmp/admin_smoke_cookies3.txt"
BASE="http://127.0.0.1:8888"
REPORT="/tmp/smoke_retest_$(date +%Y%m%d_%H%M%S).txt"

# Clear rate limiter
php artisan cache:clear 2>/dev/null || true

# Get all GET routes
ROUTES=$(php artisan route:list --method=GET 2>/dev/null \
  | grep -oP '(?:GET\|HEAD)\s+\K\S+' \
  | grep -v '{' \
  | sort -u)

OK=0; REDIR=0; ERR4=0; ERR5=0; TOTAL=0
declare -a FAIL4=() FAIL5=() REDIR_LIST=()

for path in $ROUTES; do
  url="${BASE}/${path}"
  code=$(curl -s -b "$COOKIE" -c "$COOKIE" -o /dev/null -w "%{http_code}" -L0 --max-time 30 "$url" 2>/dev/null || echo "000")
  TOTAL=$((TOTAL+1))
  case "$code" in
    2*) OK=$((OK+1)) ;;
    3*) REDIR=$((REDIR+1)); REDIR_LIST+=("$code $path") ;;
    4*) ERR4=$((ERR4+1)); FAIL4+=("$code $path") ;;
    5*) ERR5=$((ERR5+1)); FAIL5+=("$code $path") ;;
    *)  ERR5=$((ERR5+1)); FAIL5+=("$code $path") ;;
  esac
  # Rate-limit: short pause every 20 requests
  if (( TOTAL % 20 == 0 )); then sleep 0.3; fi
done

{
  echo "=== SMOKE RE-TEST RESULTS ==="
  echo "Date: $(date)"
  echo "Total: $TOTAL | OK: $OK | 3xx: $REDIR | 4xx: $ERR4 | 5xx: $ERR5"
  echo ""
  if [ ${#FAIL5[@]} -gt 0 ]; then
    echo "--- 5xx ERRORS ---"
    printf '%s\n' "${FAIL5[@]}"
    echo ""
  fi
  if [ ${#FAIL4[@]} -gt 0 ]; then
    echo "--- 4xx ERRORS ---"
    printf '%s\n' "${FAIL4[@]}"
    echo ""
  fi
  echo "--- 3xx REDIRECTS ---"
  printf '%s\n' "${REDIR_LIST[@]}"
} | tee "$REPORT"

echo ""
echo "Full report saved to: $REPORT"
