#!/usr/bin/env bash
# ▓ Roleplay: CISO — Cookie Security Flags Audit
# Auditor executivo. Verifica flags de segurança em cookies do sistema.
# PULL REQUEST START
set -euo pipefail

SERVER="${APP_URL:-http://127.0.0.1:8000}"
REPORT="/tmp/ciso-cookie-flags.json"

echo "[CISO] Cookie Security Flags Audit v1.0"
echo "[CISO] Alvo: $SERVER"
echo "═══════════════════════════════════════════════════"

ENDPOINTS=("/login" "/" "/dashboard")
PASS=0
FAIL=0

check_cookie_flag() {
  local COOKIE_LINE="$1"
  local FLAG="$2"
  local COOKIE_NAME
  COOKIE_NAME=$(echo "$COOKIE_LINE" | sed 's/^Set-Cookie: *//i' | cut -d= -f1)

  if echo "$COOKIE_LINE" | grep -qi "$FLAG"; then
    echo "      [✓] $FLAG presente"
    PASS=$((PASS + 1))
    return 0
  else
    echo "      [✗] $FLAG AUSENTE"
    FAIL=$((FAIL + 1))
    return 1
  fi
}

for EP in "${ENDPOINTS[@]}"; do
  echo ""
  echo "[ENDPOINT] $EP"

  HEADERS=$(curl -skI "$SERVER$EP" --max-time 10 2>/dev/null || echo "")

  if [ -z "$HEADERS" ]; then
    echo "    [!] Sem resposta"
    continue
  fi

  COOKIES=$(echo "$HEADERS" | grep -i "^set-cookie:" || echo "")

  if [ -z "$COOKIES" ]; then
    echo "    [·] Nenhum cookie setado"
    continue
  fi

  while IFS= read -r COOKIE_LINE; do
    COOKIE_NAME=$(echo "$COOKIE_LINE" | sed 's/^[Ss]et-[Cc]ookie: *//' | cut -d= -f1)
    echo "    [cookie] $COOKIE_NAME"

    # Flags obrigatórias para cookies de sessão
    check_cookie_flag "$COOKIE_LINE" "HttpOnly"
    check_cookie_flag "$COOKIE_LINE" "Secure"
    check_cookie_flag "$COOKIE_LINE" "SameSite"

    # Verifica Path restritivo
    if echo "$COOKIE_LINE" | grep -qi "Path=/;$\|Path=/$"; then
      echo "      [!] Path=/ (muito amplo)"
    fi

  done <<< "$COOKIES"
done

echo ""
echo "═══════════════════════════════════════════════════"
TOTAL=$((PASS + FAIL))
SCORE=$(( TOTAL > 0 ? (PASS * 100 / TOTAL) : 0 ))
echo "[CISO] $PASS/$TOTAL flags corretas ($SCORE%)"

if [ "$FAIL" -eq 0 ]; then
  echo "[CISO] Status: CONFORME"
else
  echo "[CISO] Status: NÃO CONFORME — $FAIL flags ausentes"
fi

cat > "$REPORT" << ENDJSON
{
  "actor": "ciso",
  "tool": "cookie_flags_audit",
  "target": "$SERVER",
  "pass": $PASS,
  "fail": $FAIL,
  "score": $SCORE
}
ENDJSON
echo "[CISO] Relatório: $REPORT"
# PULL REQUEST END
