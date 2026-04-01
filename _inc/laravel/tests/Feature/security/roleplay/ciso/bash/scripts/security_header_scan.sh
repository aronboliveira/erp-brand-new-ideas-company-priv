#!/usr/bin/env bash
# ▓ Roleplay: CISO — Security Header Compliance Scan
# Auditor executivo. Verifica compliance de headers de segurança contra OWASP.
# PULL REQUEST START
set -euo pipefail

SERVER="${APP_URL:-http://127.0.0.1:8000}"
REPORT="/tmp/ciso-header-compliance.json"

echo "[CISO] Security Header Compliance Scan v1.0"
echo "[CISO] Alvo: $SERVER"
echo "═══════════════════════════════════════════════════"

ENDPOINTS=("/" "/login" "/dashboard" "/invoices")
PASS=0
FAIL=0
WARN=0

check_header() {
  local HEADERS="$1"
  local HEADER_NAME="$2"
  local EXPECTED="$3"
  local SEVERITY="$4"

  local VALUE
  VALUE=$(echo "$HEADERS" | grep -i "^${HEADER_NAME}:" | head -1 | sed 's/^[^:]*: *//' | tr -d '\r')

  if [ -z "$VALUE" ]; then
    echo "    [✗] $HEADER_NAME: AUSENTE ($SEVERITY)"
    FAIL=$((FAIL + 1))
    return 1
  elif [ -n "$EXPECTED" ] && ! echo "$VALUE" | grep -qiE "$EXPECTED"; then
    echo "    [!] $HEADER_NAME: $VALUE (esperado: $EXPECTED) ($SEVERITY)"
    WARN=$((WARN + 1))
    return 1
  else
    echo "    [✓] $HEADER_NAME: $VALUE"
    PASS=$((PASS + 1))
    return 0
  fi
}

check_absent() {
  local HEADERS="$1"
  local HEADER_NAME="$2"

  local VALUE
  VALUE=$(echo "$HEADERS" | grep -i "^${HEADER_NAME}:" | head -1 | sed 's/^[^:]*: *//' | tr -d '\r')

  if [ -n "$VALUE" ]; then
    echo "    [✗] $HEADER_NAME presente: $VALUE (deve ser removido)"
    FAIL=$((FAIL + 1))
  else
    echo "    [✓] $HEADER_NAME: ausente (correto)"
    PASS=$((PASS + 1))
  fi
}

for EP in "${ENDPOINTS[@]}"; do
  echo ""
  echo "[ENDPOINT] $EP"

  HEADERS=$(curl -skI "$SERVER$EP" --max-time 10 2>/dev/null || echo "")

  if [ -z "$HEADERS" ]; then
    echo "    [!] Não foi possível acessar $EP"
    continue
  fi

  # Headers obrigatórios (OWASP)
  check_header "$HEADERS" "X-Frame-Options" "DENY|SAMEORIGIN" "HIGH"
  check_header "$HEADERS" "X-Content-Type-Options" "nosniff" "MEDIUM"
  check_header "$HEADERS" "X-XSS-Protection" "1|0" "LOW"
  check_header "$HEADERS" "Strict-Transport-Security" "max-age" "HIGH"
  check_header "$HEADERS" "Content-Security-Policy" "." "HIGH"
  check_header "$HEADERS" "Referrer-Policy" "." "MEDIUM"
  check_header "$HEADERS" "Permissions-Policy" "." "MEDIUM"

  # Headers que NÃO devem existir (information disclosure)
  check_absent "$HEADERS" "Server"
  check_absent "$HEADERS" "X-Powered-By"
done

echo ""
echo "═══════════════════════════════════════════════════"
TOTAL=$((PASS + FAIL + WARN))
echo "[CISO] Resultado: $PASS/$TOTAL pass, $FAIL fail, $WARN warn"

SCORE=$(( TOTAL > 0 ? (PASS * 100 / TOTAL) : 0 ))
if [ "$SCORE" -ge 80 ]; then
  GRADE="A"
elif [ "$SCORE" -ge 60 ]; then
  GRADE="B"
elif [ "$SCORE" -ge 40 ]; then
  GRADE="C"
else
  GRADE="F"
fi
echo "[CISO] Nota de compliance: $GRADE ($SCORE%)"

cat > "$REPORT" << ENDJSON
{
  "actor": "ciso",
  "tool": "header_compliance",
  "target": "$SERVER",
  "pass": $PASS,
  "fail": $FAIL,
  "warn": $WARN,
  "score": $SCORE,
  "grade": "$GRADE"
}
ENDJSON
echo "[CISO] Relatório: $REPORT"
# PULL REQUEST END
