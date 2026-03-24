#!/usr/bin/env bash
set -euo pipefail

BASE="http://127.0.0.1:8000"
COOKIE="/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/cookies.txt"
REPORT_DIR="/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/.tmp/copilot/reports/20260324"
ROUTES_FILE="/tmp/all_static_routes.txt"
CURL_LOG="$REPORT_DIR/curl-detailed.log"
CURL_CSV="$REPORT_DIR/curl-timing.csv"

: > "$CURL_LOG"
echo "route,http_code,time_connect,time_starttransfer,time_total,speed_download,size_download,content_type" > "$CURL_CSV"

TOTAL=0; OK=0; REDIR=0; C4XX=0; C5XX=0; TIMEOUT=0; SLOW=0
CURL_FMT='%{http_code}|%{time_connect}|%{time_starttransfer}|%{time_total}|%{speed_download}|%{size_download}|%{content_type}'

while IFS= read -r route; do
  [[ -z "$route" ]] && continue
  TOTAL=$((TOTAL + 1))

  if [[ "$route" == "/" ]]; then
    url="$BASE/"
  else
    url="$BASE/$route"
  fi

  result=$(curl -s --max-time 30 -b "$COOKIE" -c "$COOKIE" -L -o /dev/null -w "$CURL_FMT" "$url" 2>/dev/null || echo "000|0|0|0|0|0|timeout")
  IFS='|' read -r code tc ts tt sd sz ct <<< "$result"

  # CSV row
  echo "\"$route\",$code,$tc,$ts,$tt,$sd,$sz,\"$ct\"" >> "$CURL_CSV"

  # Classify
  status_icon="?"
  if [[ "$code" =~ ^2 ]]; then
    OK=$((OK + 1))
    status_icon="✅"
  elif [[ "$code" =~ ^3 ]]; then
    REDIR=$((REDIR + 1))
    status_icon="↪"
  elif [[ "$code" =~ ^4 ]]; then
    C4XX=$((C4XX + 1))
    status_icon="⚠️"
  elif [[ "$code" =~ ^5 ]]; then
    C5XX=$((C5XX + 1))
    status_icon="❌"
  else
    TIMEOUT=$((TIMEOUT + 1))
    status_icon="⏱️"
  fi

  # Flag slow responses (>3s TTFB or >5s total)
  slow_flag=""
  if (( $(echo "$ts > 3.0" | bc -l 2>/dev/null || echo 0) )); then
    slow_flag=" [SLOW-TTFB:${ts}s]"
    SLOW=$((SLOW + 1))
  elif (( $(echo "$tt > 5.0" | bc -l 2>/dev/null || echo 0) )); then
    slow_flag=" [SLOW-TOTAL:${tt}s]"
    SLOW=$((SLOW + 1))
  fi

  echo "$status_icon $code /$route  tc=${tc}s ts=${ts}s tt=${tt}s dl=${sd}B/s sz=${sz}B${slow_flag}" >> "$CURL_LOG"

  # Progress every 50 routes
  if (( TOTAL % 50 == 0 )); then
    echo "  progress: $TOTAL routes scanned..."
  fi
done < "$ROUTES_FILE"

# Summary
cat >> "$CURL_LOG" <<EOF

=== CURL SCAN SUMMARY ===
Date: $(date -u +%Y-%m-%dT%H:%M:%SZ)
Total routes: $TOTAL
  200 OK:      $OK
  3xx Redir:   $REDIR
  4xx Client:  $C4XX
  5xx Server:  $C5XX
  Timeout:     $TIMEOUT
  Slow (>3s):  $SLOW
EOF

echo ""
echo "=== CURL SCAN COMPLETE ==="
echo "Total: $TOTAL | 200: $OK | 3xx: $REDIR | 4xx: $C4XX | 5xx: $C5XX | timeout: $TIMEOUT | slow: $SLOW"
echo "CSV: $CURL_CSV"
echo "Log: $CURL_LOG"
