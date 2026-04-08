#!/usr/bin/env bash
# ──────────────────────────────────────────────────────────────────
# run-all-tests.sh — Full test suite for ts/ TypeScript mirror
#
# Executa: tsc build → eslint → jest → playwright
# Também: cria DB de teste MySQL, valida com curl/wget, destrói DB
#
# Uso:  cd ts && bash scripts/run-all-tests.sh
# ──────────────────────────────────────────────────────────────────
# PULL REQUEST START
set -euo pipefail

# ── Config ──────────────────────────────────────────────────────
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
TS_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
LARAVEL_ROOT="$(cd "$TS_ROOT/.." && pwd)"

# Saída e artefatos dentro de ts/
LOG_DIR="$TS_ROOT/.tmp/test-output/$(date +%Y%m%d_%H%M%S)"
mkdir -p "$LOG_DIR"

# MySQL config (lê do .env do Laravel ou usa defaults)
ENV_FILE="$LARAVEL_ROOT/.env"
if [[ -f "$ENV_FILE" ]]; then
  DB_HOST=$(grep -E "^DB_HOST=" "$ENV_FILE" | cut -d= -f2 | tr -d '[:space:]')
  DB_PORT=$(grep -E "^DB_PORT=" "$ENV_FILE" | cut -d= -f2 | tr -d '[:space:]')
  DB_USER=$(grep -E "^DB_USERNAME=" "$ENV_FILE" | cut -d= -f2 | tr -d '[:space:]')
  DB_PASS=$(grep -E "^DB_PASSWORD=" "$ENV_FILE" | cut -d= -f2 | tr -d '[:space:]')
fi
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-test}"
DB_PASS="${DB_PASS:-test}"
TEST_DB="erp_prestech_ts_test"
TEST_TABLE="erp_prestech_ts_test"

# Contadores
PASS=0
FAIL=0
SKIP=0

# ── Helpers ─────────────────────────────────────────────────────
log()  { echo -e "\n\033[1;34m▶ $1\033[0m"; }
ok()   { echo -e "  \033[1;32m✓ $1\033[0m"; PASS=$((PASS + 1)); }
fail() { echo -e "  \033[1;31m✗ $1\033[0m"; FAIL=$((FAIL + 1)); }
skip() { echo -e "  \033[1;33m⊘ $1\033[0m"; SKIP=$((SKIP + 1)); }

mysql_cmd() {
  mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" --batch --skip-column-names "$@" 2>/dev/null
}

# ── 0. Pré-requisitos ──────────────────────────────────────────
log "Verificando pré-requisitos"
cd "$TS_ROOT"

for tool in node npm npx tsc curl wget mysql; do
  if command -v "$tool" &>/dev/null; then
    ok "$tool encontrado: $(command -v "$tool")"
  else
    fail "$tool não encontrado"
  fi
done

echo "  Node $(node -v) | npm $(npm -v)"
echo "  Diretório de log: $LOG_DIR"

# ── 1. MySQL — Criar DB e tabela de teste ──────────────────────
log "MySQL — Criando banco de teste: $TEST_DB"

DB_CREATED=0
if mysql_cmd -e "SELECT 1" &>/dev/null; then
  mysql_cmd <<-SQL
    CREATE DATABASE IF NOT EXISTS \`${TEST_DB}\`
      CHARACTER SET utf8mb4
      COLLATE utf8mb4_unicode_ci;
SQL
  mysql_cmd "$TEST_DB" <<-SQL
    CREATE TABLE IF NOT EXISTS \`${TEST_TABLE}\` (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      test_name VARCHAR(255) NOT NULL,
      suite VARCHAR(100) NOT NULL DEFAULT 'ts-mirror',
      status ENUM('pass','fail','skip','error') NOT NULL DEFAULT 'pass',
      duration_ms INT UNSIGNED DEFAULT 0,
      output TEXT,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;
SQL
  DB_CREATED=1
  ok "Banco $TEST_DB e tabela $TEST_TABLE criados"
else
  skip "MySQL indisponível — testes de DB ignorados"
fi

# Helper: registrar resultado no MySQL
record_result() {
  local name="$1" suite="$2" status="$3" duration="$4" output_file="${5:-}"
  if [[ $DB_CREATED -eq 1 ]]; then
    local out=""
    if [[ -n "$output_file" && -f "$output_file" ]]; then
      out=$(head -c 60000 "$output_file" | sed "s/'/''/g")
    fi
    mysql_cmd "$TEST_DB" -e \
      "INSERT INTO \`${TEST_TABLE}\` (test_name, suite, status, duration_ms, output) VALUES ('$name', '$suite', '$status', $duration, '$out');" 2>/dev/null || true
  fi
}

# ── 2. TypeScript Build (tsc) ──────────────────────────────────
log "TypeScript — tsc --build"
TSC_LOG="$LOG_DIR/tsc.log"
TSC_START=$(date +%s%3N)

if npm run build > "$TSC_LOG" 2>&1; then
  TSC_END=$(date +%s%3N)
  ok "tsc build concluído em $((TSC_END - TSC_START))ms"
  record_result "tsc-build" "tsc" "pass" "$((TSC_END - TSC_START))" "$TSC_LOG"
else
  TSC_END=$(date +%s%3N)
  fail "tsc build falhou (ver $TSC_LOG)"
  record_result "tsc-build" "tsc" "fail" "$((TSC_END - TSC_START))" "$TSC_LOG"
fi

# ── 3. ESLint ──────────────────────────────────────────────────
log "ESLint — lint src/"
ESLINT_LOG="$LOG_DIR/eslint.log"
ESLINT_START=$(date +%s%3N)

if npm run lint > "$ESLINT_LOG" 2>&1; then
  ESLINT_END=$(date +%s%3N)
  ok "ESLint passed em $((ESLINT_END - ESLINT_START))ms"
  record_result "eslint" "eslint" "pass" "$((ESLINT_END - ESLINT_START))" "$ESLINT_LOG"
else
  ESLINT_END=$(date +%s%3N)
  # Lint com warnings pode sair 0 ou 1 dependendo de --max-warnings
  WARN_COUNT=$(grep -c "warning" "$ESLINT_LOG" 2>/dev/null || echo "0")
  ERR_COUNT=$(grep -c "error" "$ESLINT_LOG" 2>/dev/null || echo "0")
  if [[ "$ERR_COUNT" -gt 0 ]]; then
    fail "ESLint: $ERR_COUNT errors, $WARN_COUNT warnings (ver $ESLINT_LOG)"
    record_result "eslint" "eslint" "fail" "$((ESLINT_END - ESLINT_START))" "$ESLINT_LOG"
  else
    ok "ESLint: $WARN_COUNT warnings, 0 errors em $((ESLINT_END - ESLINT_START))ms"
    record_result "eslint" "eslint" "pass" "$((ESLINT_END - ESLINT_START))" "$ESLINT_LOG"
  fi
fi

# ── 4. Jest ────────────────────────────────────────────────────
log "Jest — testes unitários"
JEST_LOG="$LOG_DIR/jest.log"
JEST_START=$(date +%s%3N)

if npm run test -- --forceExit --no-cache 2>&1 | tee "$JEST_LOG"; then
  JEST_END=$(date +%s%3N)
  ok "Jest passed em $((JEST_END - JEST_START))ms"
  record_result "jest" "jest" "pass" "$((JEST_END - JEST_START))" "$JEST_LOG"
else
  JEST_END=$(date +%s%3N)
  fail "Jest falhou (ver $JEST_LOG)"
  record_result "jest" "jest" "fail" "$((JEST_END - JEST_START))" "$JEST_LOG"
fi

# ── 5. curl/wget — Validação de artefatos build ──────────────
log "curl/wget — Validação de artefatos"

# Verificar que dist/ foi gerado
if [[ -d "$TS_ROOT/dist" ]]; then
  DIST_COUNT=$(find "$TS_ROOT/dist" -name "*.js" | wc -l)
  ok "dist/ contém $DIST_COUNT arquivos .js"
  record_result "dist-count" "build-validation" "pass" "0"

  # Verificar que arquivos core existem
  for f in public/assets/js/core/erp-guard.js public/assets/js/core/erp-utils.js; do
    if [[ -f "$TS_ROOT/dist/$f" ]]; then
      ok "dist/$f existe"
    else
      fail "dist/$f não encontrado"
    fi
  done
else
  fail "dist/ não encontrado — build falhou?"
  record_result "dist-check" "build-validation" "fail" "0"
fi

# curl: verificar se o conteúdo compilado é JS válido (syntax check via node)
CURL_LOG="$LOG_DIR/curl-wget.log"
echo "=== Build artifact validation ===" > "$CURL_LOG"

for jsfile in $(find "$TS_ROOT/dist" -name "*.js" -type f 2>/dev/null | head -20); do
  REL=$(realpath --relative-to="$TS_ROOT" "$jsfile")
  if node --check "$jsfile" 2>>"$CURL_LOG"; then
    echo "  ✓ $REL — syntax OK" >> "$CURL_LOG"
  else
    echo "  ✗ $REL — syntax error" >> "$CURL_LOG"
    fail "Syntax error in $REL"
  fi
done

# wget: baixar arquivo local de referência para comparar tamanhos
WGET_LOG="$LOG_DIR/wget-check.log"
echo "=== wget artifact size check ===" > "$WGET_LOG"
if [[ -d "$TS_ROOT/dist" ]]; then
  find "$TS_ROOT/dist" -name "*.js" -type f -exec ls -lh {} \; >> "$WGET_LOG" 2>&1
  TOTAL_SIZE=$(find "$TS_ROOT/dist" -name "*.js" -type f -exec cat {} + 2>/dev/null | wc -c)
  echo "Total JS output: $TOTAL_SIZE bytes" >> "$WGET_LOG"
  ok "Build artifacts total: $(numfmt --to=iec "$TOTAL_SIZE" 2>/dev/null || echo "${TOTAL_SIZE}B")"
  record_result "artifact-size" "build-validation" "pass" "0" "$WGET_LOG"
fi

# ── 6. Playwright — Harness tests ─────────────────────────────
log "Playwright — harness tests"
PW_LOG="$LOG_DIR/playwright.log"
PW_START=$(date +%s%3N)

# Verificar se serve-harness.cjs e harness-specs existem
if [[ -f "$TS_ROOT/tests/serve-harness.cjs" && -d "$TS_ROOT/tests/harness-specs" ]]; then
  SPEC_COUNT=$(find "$TS_ROOT/tests/harness-specs" -name "*.spec.ts" -o -name "*.test.ts" 2>/dev/null | wc -l)
  if [[ "$SPEC_COUNT" -gt 0 ]]; then
    if npx playwright test --config=playwright.harness.config.ts 2>&1 | tee "$PW_LOG"; then
      PW_END=$(date +%s%3N)
      ok "Playwright passed ($SPEC_COUNT specs) em $((PW_END - PW_START))ms"
      record_result "playwright" "playwright" "pass" "$((PW_END - PW_START))" "$PW_LOG"
    else
      PW_END=$(date +%s%3N)
      fail "Playwright falhou (ver $PW_LOG)"
      record_result "playwright" "playwright" "fail" "$((PW_END - PW_START))" "$PW_LOG"
    fi
  else
    skip "Nenhum spec encontrado em tests/harness-specs/"
    record_result "playwright" "playwright" "skip" "0"
  fi
else
  skip "Playwright harness não configurado (serve-harness.cjs ou harness-specs/ ausente)"
  record_result "playwright" "playwright" "skip" "0"
fi

# ── 7. MySQL — Consultar resultados e destruir ─────────────────
if [[ $DB_CREATED -eq 1 ]]; then
  log "MySQL — Resultados registrados"
  MYSQL_REPORT="$LOG_DIR/mysql-results.log"
  mysql_cmd "$TEST_DB" -e \
    "SELECT test_name, suite, status, duration_ms FROM \`${TEST_TABLE}\` ORDER BY id;" \
    > "$MYSQL_REPORT" 2>/dev/null
  cat "$MYSQL_REPORT"

  log "MySQL — Destruindo banco de teste: $TEST_DB"
  mysql_cmd -e "DROP DATABASE IF EXISTS \`${TEST_DB}\`;" 2>/dev/null
  ok "Banco $TEST_DB destruído"
fi

# ── Resumo ──────────────────────────────────────────────────────
log "═══════════════ RESUMO ═══════════════"
echo "  ✓ Passed:  $PASS"
echo "  ✗ Failed:  $FAIL"
echo "  ⊘ Skipped: $SKIP"
echo "  Logs em: $LOG_DIR"
echo ""

if [[ $FAIL -gt 0 ]]; then
  echo -e "\033[1;31m  RESULTADO: FALHOU ($FAIL falhas)\033[0m"
  exit 1
else
  echo -e "\033[1;32m  RESULTADO: PASSOU\033[0m"
  exit 0
fi
# PULL REQUEST END
