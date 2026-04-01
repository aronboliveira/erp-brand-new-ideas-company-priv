#!/usr/bin/env bash
# ▓ Roleplay: Backend Dev — Secret Scanner ▓
# Procura segredos hardcoded e credenciais no código fonte
# PULL REQUEST START
set -euo pipefail

PROJECT_ROOT="${1:-$(cd "$(dirname "$0")/../../../../.." && pwd)}"

echo "[BACKEND-DEV] Secret Scanner v1.0"
echo "[BACKEND-DEV] Raiz: $PROJECT_ROOT"
echo "═══════════════════════════════════════════════════"

TOTAL_FINDINGS=0

scan_secret() {
  local LABEL="$1"
  local PATTERN="$2"
  local SEVERITY="$3"
  local GLOB="${4:-*}"

  local MATCHES
  MATCHES=$(grep -rnE "$PATTERN" "$PROJECT_ROOT/app" "$PROJECT_ROOT/Modules" \
    "$PROJECT_ROOT/config" "$PROJECT_ROOT/routes" \
    --include="$GLOB" 2>/dev/null \
    | grep -v "vendor/" | grep -v "node_modules/" \
    | grep -v ".env.example" | grep -v "test" \
    | head -20 || echo "")

  local COUNT
  if [ -z "$MATCHES" ]; then
    COUNT=0
  else
    COUNT=$(echo "$MATCHES" | wc -l | tr -d ' ')
  fi

  if [[ "$COUNT" -gt 0 ]]; then
    echo "  [✗] $LABEL ($SEVERITY) — $COUNT ocorrências"
    echo "$MATCHES" | head -3 | while IFS= read -r line; do
      # Mascarar o valor do segredo
      echo "      $(echo "$line" | sed 's/=.*/=***MASKED***/' | cut -c1-120)"
    done
    if [ "$COUNT" -gt 3 ]; then
      echo "      ... e mais $((COUNT - 3))"
    fi
    TOTAL_FINDINGS=$((TOTAL_FINDINGS + COUNT))
  else
    echo "  [✓] $LABEL — nenhuma ocorrência"
  fi
}

# ── API Keys & Tokens ──
echo ""
echo "[CATEGORY] API Keys & Tokens"
scan_secret "AWS Access Key" 'AKIA[0-9A-Z]{16}' "CRITICAL" "*.php"
scan_secret "AWS Secret Key" 'aws_secret_access_key\s*=.*[A-Za-z0-9/+=]{40}' "CRITICAL" "*"
scan_secret "Generic API Key" 'api[_-]?key\s*[:=]\s*["\x27][a-zA-Z0-9_\-]{20,}' "HIGH" "*.php"
scan_secret "Bearer Token hardcoded" 'Authorization.*Bearer\s+[a-zA-Z0-9_\-\.]{20,}' "CRITICAL" "*.php"
scan_secret "JWT hardcoded" 'eyJ[a-zA-Z0-9_-]{10,}\.eyJ[a-zA-Z0-9_-]{10,}\.' "HIGH" "*.php"

# ── Database Credentials ──
echo ""
echo "[CATEGORY] Database Credentials"
scan_secret "DB password hardcoded" "(DB_PASSWORD|db_password|database_password)\s*[:=]\s*['\"][^'\"]{4,}" "CRITICAL" "*.php"
scan_secret "MySQL connect string" 'mysql://[^:]+:[^@]+@' "CRITICAL" "*.php"
scan_secret "PostgreSQL connect" 'postgresql://[^:]+:[^@]+@' "CRITICAL" "*.php"

# ── Encryption Keys ──
echo ""
echo "[CATEGORY] Encryption & Signing Keys"
scan_secret "APP_KEY hardcoded" "APP_KEY\s*=\s*base64:[a-zA-Z0-9+/=]{30,}" "CRITICAL" "*.php"
scan_secret "Private key" "-----BEGIN (RSA |EC |DSA )?PRIVATE KEY-----" "CRITICAL" "*"
scan_secret "Encryption key" "encryption[_-]?key\s*[:=]\s*['\"][^'\"]{8,}" "HIGH" "*.php"

# ── Service Credentials ──
echo ""
echo "[CATEGORY] Service Credentials"
scan_secret "SMTP password" "(MAIL_PASSWORD|smtp_password)\s*[:=]\s*['\"][^'\"]{4,}" "HIGH" "*.php"
scan_secret "Redis password" "(REDIS_PASSWORD|redis_password)\s*[:=]\s*['\"][^'\"]{4,}" "HIGH" "*.php"
scan_secret "OAuth secret" "(client_secret|oauth_secret)\s*[:=]\s*['\"][^'\"]{8,}" "HIGH" "*.php"
scan_secret "Webhook secret" "webhook[_-]?secret\s*[:=]\s*['\"][^'\"]{8,}" "MEDIUM" "*.php"

# ── Passwords in Code ──
echo ""
echo "[CATEGORY] Passwords in Code"
scan_secret "Password literal" "password\s*[:=]\s*['\"][^'\"]{4,}['\"]" "HIGH" "*.php"
scan_secret "Secret literal" "secret\s*[:=]\s*['\"][^'\"]{8,}['\"]" "HIGH" "*.php"

# ── Resumo ──
echo ""
echo "── Resumo ──"
echo "  Total de segredos encontrados: $TOTAL_FINDINGS"
if [[ "$TOTAL_FINDINGS" -gt 0 ]]; then
  echo "  [!] AÇÃO REQUERIDA: Mover segredos para .env ou vault"
else
  echo "  [✓] Nenhum segredo hardcoded encontrado"
fi
echo "{\"total_findings\":$TOTAL_FINDINGS}"
echo "[BACKEND-DEV] Secret Scanner completo"
# PULL REQUEST END
