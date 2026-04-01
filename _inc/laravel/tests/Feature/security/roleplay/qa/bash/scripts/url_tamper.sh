#!/usr/bin/env bash
# ▓ Roleplay: QA — URL Parameter Tamper Tool
# QA tester. Testa manipulação de parâmetros de URL.
# PULL REQUEST START
set -euo pipefail

SERVER="${APP_URL:-http://127.0.0.1:8000}"
REPORT="/tmp/qa-url-tamper.json"

echo "[QA] URL Parameter Tamper v1.0"
echo "[QA] Alvo: $SERVER"
echo "═══════════════════════════════════════════════════"

PASS=0
FAIL=0
TOTAL=0

test_url() {
  local LABEL="$1"
  local URL="$2"
  local EXPECTED="$3"  # "block" ou "allow"

  TOTAL=$((TOTAL + 1))

  local CODE
  CODE=$(curl -sk -o /dev/null -w "%{http_code}" \
    "$URL" \
    -H "User-Agent: Mozilla/5.0" \
    --max-time 10 2>/dev/null || echo "000")

  local BLOCKED=false
  if [ "$CODE" = "302" ] || [ "$CODE" = "403" ] || [ "$CODE" = "404" ] || [ "$CODE" = "419" ]; then
    BLOCKED=true
  fi

  if [ "$EXPECTED" = "block" ]; then
    if $BLOCKED; then
      echo "  [✓] $LABEL → $CODE (bloqueado como esperado)"
      PASS=$((PASS + 1))
    else
      echo "  [✗] $LABEL → $CODE (deveria ser bloqueado)"
      FAIL=$((FAIL + 1))
    fi
  else
    if ! $BLOCKED; then
      echo "  [✓] $LABEL → $CODE (acessível como esperado)"
      PASS=$((PASS + 1))
    else
      echo "  [!] $LABEL → $CODE (bloqueado inesperado)"
      FAIL=$((FAIL + 1))
    fi
  fi
}

# ── 1. IDOR via ID sequencial ───────────────────────────
echo ""
echo "[CATEGORY] IDOR — IDs sequenciais"
test_url "Invoice ID=0" "$SERVER/invoices/0" "block"
test_url "Invoice ID=-1" "$SERVER/invoices/-1" "block"
test_url "Invoice ID=99999" "$SERVER/invoices/99999" "block"
test_url "Invoice ID=string" "$SERVER/invoices/abc" "block"

# ── 2. Path Traversal ───────────────────────────────────
echo ""
echo "[CATEGORY] Path Traversal"
test_url "Dot-dot-slash" "$SERVER/../../etc/passwd" "block"
test_url "Encoded traversal" "$SERVER/%2e%2e%2f%2e%2e%2fetc%2fpasswd" "block"
test_url "Double-encoded" "$SERVER/%252e%252e%252f" "block"

# ── 3. Query String injection ───────────────────────────
echo ""
echo "[CATEGORY] Query String Manipulation"
test_url "SQLi no param" "$SERVER/search?q=%27%20OR%201=1--" "allow"
test_url "Param excesso" "$SERVER/search?q=a&q=b&q=c&q=d&q=e" "allow"
test_url "Param vazio" "$SERVER/search?q=" "allow"
test_url "Param longo" "$SERVER/search?q=$(python3 -c 'print("A"*2000)')" "allow"

# ── 4. Method tampering ─────────────────────────────────
echo ""
echo "[CATEGORY] HTTP Method Tampering"
CODE=$(curl -sk -o /dev/null -w "%{http_code}" \
  -X DELETE "$SERVER/invoices/1" \
  --max-time 10 2>/dev/null || echo "000")
echo "  [·] DELETE /invoices/1 → $CODE"

CODE=$(curl -sk -o /dev/null -w "%{http_code}" \
  -X PATCH "$SERVER/invoices/1" \
  -d "status=paid" \
  --max-time 10 2>/dev/null || echo "000")
echo "  [·] PATCH /invoices/1 → $CODE"

CODE=$(curl -sk -o /dev/null -w "%{http_code}" \
  -X OPTIONS "$SERVER/api" \
  --max-time 10 2>/dev/null || echo "000")
echo "  [·] OPTIONS /api → $CODE"

echo ""
echo "═══════════════════════════════════════════════════"
echo "[QA] $PASS/$TOTAL testes passaram, $FAIL falharam"

cat > "$REPORT" << ENDJSON
{
  "actor": "qa",
  "tool": "url_tamper",
  "target": "$SERVER",
  "pass": $PASS,
  "fail": $FAIL,
  "total": $TOTAL
}
ENDJSON
echo "[QA] Relatório: $REPORT"
# PULL REQUEST END
