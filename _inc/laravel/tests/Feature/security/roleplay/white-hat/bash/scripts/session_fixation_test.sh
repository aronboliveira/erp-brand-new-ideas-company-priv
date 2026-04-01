#!/usr/bin/env bash
# ▓ Roleplay: White Hat — Session Fixation Test
# Pentester ético. Testa fixação de sessão e replay de cookie.
# PULL REQUEST START
set -euo pipefail

BASE="${APP_URL:-http://127.0.0.1:8000}"

echo "[WHITE-HAT] Session Fixation Test v1.0"
echo "[WHITE-HAT] Alvo: $BASE"
echo "═══════════════════════════════════════"

# 1. Pega sessão antes de login
echo "[STEP 1] Obtendo sessão pré-login..."
PRE_COOKIE=$(curl -s -c - "$BASE/login" 2>/dev/null \
  | grep -i "laravel_session" | awk '{print $NF}' || echo "none")
echo "  Sessão pré-login: ${PRE_COOKIE:0:30}..."

# 2. Faz login (tentativa)
echo "[STEP 2] Tentando login..."
CSRF=$(curl -s "$BASE/login" -c /tmp/wh-sess.txt 2>/dev/null \
  | grep -oP 'name="_token"\s+value="\K[^"]+' || echo "no-csrf")

POST_COOKIE=$(curl -s -c - -b /tmp/wh-sess.txt \
  -X POST "$BASE/login" \
  -d "email=test@test.com&password=wrong&_token=$CSRF" \
  -L --max-redirs 3 --max-time 10 2>/dev/null \
  | grep -i "laravel_session" | awk '{print $NF}' || echo "none")
echo "  Sessão pós-login: ${POST_COOKIE:0:30}..."

# 3. Verificar se sessão mudou (session regeneration)
if [[ "$PRE_COOKIE" == "$POST_COOKIE" && "$PRE_COOKIE" != "none" ]]; then
  echo "[⚠ VULN] Sessão NÃO foi regenerada após login!"
  FIXATION_RESULT="VULNERABLE"
else
  echo "[✓ OK] Sessão foi regenerada (ou não obtida)"
  FIXATION_RESULT="SAFE"
fi

# 4. Teste de replay: reutilizar cookie antigo
echo "[STEP 3] Testando replay de cookie antigo..."
if [[ "$PRE_COOKIE" != "none" ]]; then
  REPLAY_STATUS=$(curl -s -o /dev/null -w "%{http_code}" \
    -b "laravel_session=$PRE_COOKIE" \
    "$BASE/dashboard" --max-time 10 2>/dev/null || echo "000")
  if [[ "$REPLAY_STATUS" == "200" ]]; then
    echo "[⚠ VULN] Cookie antigo aceito para /dashboard (status $REPLAY_STATUS)"
  else
    echo "[✓ OK] Cookie antigo rejeitado (status $REPLAY_STATUS)"
  fi
else
  echo "[SKIP] Sem cookie pré-login para replay"
fi

echo "═══════════════════════════════════════"
echo "[WHITE-HAT] Session Fixation: $FIXATION_RESULT"

rm -f /tmp/wh-sess.txt
exit 0
# PULL REQUEST END
