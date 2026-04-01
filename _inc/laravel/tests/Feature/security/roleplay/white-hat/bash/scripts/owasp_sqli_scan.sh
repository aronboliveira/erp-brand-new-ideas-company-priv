#!/usr/bin/env bash
# ▓ Roleplay: White Hat — OWASP SQLi Scanner
# Metodológico. Segue OTG-INPVAL-005. Categoriza por tipo.
# PULL REQUEST START
set -euo pipefail

BASE="${APP_URL:-http://127.0.0.1:8000}"
ENDPOINT="${1:-/invoices}"
RESULTS_FILE="/tmp/white-hat-sqli-results.json"

echo "[WHITE-HAT] OWASP SQLi Scanner v1.0"
echo "[WHITE-HAT] Target: ${BASE}${ENDPOINT}"
echo "[WHITE-HAT] OTG-INPVAL-005: SQL Injection Testing"
echo "═══════════════════════════════════════"

declare -A CATEGORIES
CATEGORIES=(
  ["error-based"]="' AND EXTRACTVALUE(1,CONCAT(0x7e,version()))--"
  ["union-based"]="' UNION SELECT NULL,NULL,NULL--"
  ["boolean-blind"]="1' AND 1=1--"
  ["time-blind"]="1' AND SLEEP(0)--"
  ["stacked"]="1'; SELECT 1--"
)

TOTAL=0
VULN=0
SAFE=0

echo '{"scan_date":"'"$(date -Iseconds)"'","results":[' > "$RESULTS_FILE"
FIRST=true

for category in "${!CATEGORIES[@]}"; do
  payload="${CATEGORIES[$category]}"
  TOTAL=$((TOTAL + 1))

  STATUS=$(curl -s -o /tmp/wh-body.txt -w "%{http_code}" \
    "${BASE}${ENDPOINT}?search=$(printf '%s' "$payload" | jq -sRr @uri)" \
    --max-time 15 2>/dev/null || echo "000")

  BODY=$(cat /tmp/wh-body.txt 2>/dev/null | head -c 5000 || echo "")
  HAS_ERROR=false

  if echo "$BODY" | grep -qi "sqlstate\|syntax error\|mysql\|mariadb"; then
    HAS_ERROR=true
    VULN=$((VULN + 1))
    VERDICT="VULNERABLE"
  else
    SAFE=$((SAFE + 1))
    VERDICT="SAFE"
  fi

  echo "[${category^^}] payload=${payload:0:50}... status=$STATUS verdict=$VERDICT"

  if [ "$FIRST" = true ]; then FIRST=false; else echo ',' >> "$RESULTS_FILE"; fi
  printf '{"category":"%s","status":%s,"verdict":"%s","has_db_error":%s}' \
    "$category" "$STATUS" "$VERDICT" "$HAS_ERROR" >> "$RESULTS_FILE"
done

echo ']}' >> "$RESULTS_FILE"

echo "═══════════════════════════════════════"
echo "[WHITE-HAT] Resumo: $TOTAL testados, $SAFE seguros, $VULN vulneráveis"
echo "[WHITE-HAT] Relatório JSON: $RESULTS_FILE"

rm -f /tmp/wh-body.txt
exit 0
# PULL REQUEST END
