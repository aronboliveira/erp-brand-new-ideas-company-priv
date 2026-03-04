#!/usr/bin/env bash
# ──────────────────────────────────────────────────────────────
# mysql_health.sh — MySQL health & diagnostics for erp_prestech
# Usage:  ./mysql_health.sh [--full|--quick|--seeds|--schema|--perf]
# Env overrides: DB_HOST, DB_PORT, DB_USER, DB_PASS, DB_NAME
# ──────────────────────────────────────────────────────────────
set -euo pipefail

# ── Defaults ──────────────────────────────────────────────────
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-admin_prestech}"
DB_PASS="${DB_PASS:-76562f3A*@prestech}"
DB_NAME="${DB_NAME:-erp_prestech}"

MYSQL="mysql -u${DB_USER} -p${DB_PASS} -h${DB_HOST} -P${DB_PORT} --batch --skip-column-names"
MYSQL_T="mysql -u${DB_USER} -p${DB_PASS} -h${DB_HOST} -P${DB_PORT}"

RED='\033[0;31m'; GRN='\033[0;32m'; YLW='\033[0;33m'; CYN='\033[0;36m'; RST='\033[0m'
PASS_N=0; FAIL_N=0; WARN_N=0

ok()   { ((PASS_N++)); printf "${GRN}[PASS]${RST} %s\n" "$1"; }
fail() { ((FAIL_N++)); printf "${RED}[FAIL]${RST} %s\n" "$1"; }
warn() { ((WARN_N++)); printf "${YLW}[WARN]${RST} %s\n" "$1"; }
info() { printf "${CYN}[INFO]${RST} %s\n" "$1"; }
hdr()  { printf "\n${CYN}═══ %s ═══${RST}\n" "$1"; }

# ── Connection check ──────────────────────────────────────────
check_connection() {
  hdr "Connection & Server Status"
  if $MYSQL -e "SELECT 1" "$DB_NAME" &>/dev/null; then
    ok "MySQL connection to ${DB_HOST}:${DB_PORT}/${DB_NAME}"
  else
    fail "Cannot connect to MySQL at ${DB_HOST}:${DB_PORT}/${DB_NAME}"
    exit 1
  fi

  local ver
  ver=$($MYSQL -e "SELECT VERSION()" "$DB_NAME" 2>/dev/null)
  info "MySQL version: ${ver}"

  local uptime
  uptime=$($MYSQL -e "SHOW STATUS LIKE 'Uptime'" "$DB_NAME" 2>/dev/null | awk '{print $2}')
  if [[ -n "$uptime" ]]; then
    local days=$((uptime / 86400))
    local hours=$(( (uptime % 86400) / 3600 ))
    local mins=$(( (uptime % 3600) / 60 ))
    info "Uptime: ${days}d ${hours}h ${mins}m"
  fi

  local threads
  threads=$($MYSQL -e "SHOW STATUS LIKE 'Threads_connected'" "$DB_NAME" 2>/dev/null | awk '{print $2}')
  info "Active threads: ${threads:-?}"

  local max_conn
  max_conn=$($MYSQL -e "SHOW VARIABLES LIKE 'max_connections'" "$DB_NAME" 2>/dev/null | awk '{print $2}')
  info "Max connections: ${max_conn:-?}"
  if [[ -n "$threads" && -n "$max_conn" ]]; then
    local pct=$(( threads * 100 / max_conn ))
    if (( pct > 80 )); then
      warn "Connection usage at ${pct}% (${threads}/${max_conn})"
    else
      ok "Connection usage at ${pct}% (${threads}/${max_conn})"
    fi
  fi
}

# ── Schema overview ───────────────────────────────────────────
check_schema() {
  hdr "Schema Overview"
  local tables
  tables=$($MYSQL -e "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='${DB_NAME}'" "$DB_NAME" 2>/dev/null)
  info "Total tables: ${tables}"

  # Data + index size
  local size
  size=$($MYSQL -e "
    SELECT CONCAT(ROUND(SUM(data_length + index_length) / 1024 / 1024, 2), ' MB')
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA='${DB_NAME}'
  " "$DB_NAME" 2>/dev/null)
  info "Database size: ${size}"

  # Tables without primary keys
  local no_pk
  no_pk=$($MYSQL -e "
    SELECT t.TABLE_NAME
    FROM information_schema.TABLES t
    LEFT JOIN information_schema.TABLE_CONSTRAINTS c
      ON t.TABLE_NAME = c.TABLE_NAME
      AND c.CONSTRAINT_TYPE = 'PRIMARY KEY'
      AND c.TABLE_SCHEMA = t.TABLE_SCHEMA
    WHERE t.TABLE_SCHEMA='${DB_NAME}'
      AND t.TABLE_TYPE = 'BASE TABLE'
      AND c.CONSTRAINT_NAME IS NULL
  " "$DB_NAME" 2>/dev/null)
  if [[ -n "$no_pk" ]]; then
    warn "Tables without PRIMARY KEY: ${no_pk//$'\n'/, }"
  else
    ok "All tables have primary keys"
  fi

  # Table-by-table listing
  info "Table details:"
  $MYSQL_T -e "
    SELECT
      TABLE_NAME AS 'Table',
      TABLE_ROWS AS 'Rows',
      CONCAT(ROUND(data_length/1024,1),'K') AS 'Data',
      CONCAT(ROUND(index_length/1024,1),'K') AS 'Index',
      ENGINE AS 'Engine',
      AUTO_INCREMENT AS 'AutoInc'
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA='${DB_NAME}'
    ORDER BY TABLE_NAME;
  " "$DB_NAME" 2>/dev/null || true
}

# ── Seed verification ─────────────────────────────────────────
check_seeds() {
  hdr "Seed Verification"

  # Core tables that should have data after seeding
  declare -A EXPECTED_SEEDS=(
    [users]=1
    [settings]=1
    [languages]=1
    [pipelines]=1
    [stages]=1
    [project_stages]=1
    [labels]=1
    [sources]=1
  )

  local total=0 empty=0 seeded=0
  local all_tables
  all_tables=$($MYSQL -e "
    SELECT TABLE_NAME FROM information_schema.TABLES
    WHERE TABLE_SCHEMA='${DB_NAME}' AND TABLE_TYPE='BASE TABLE'
    ORDER BY TABLE_NAME
  " "$DB_NAME" 2>/dev/null)

  for tbl in $all_tables; do
    local cnt
    cnt=$($MYSQL -e "SELECT COUNT(*) FROM \`${tbl}\`" "$DB_NAME" 2>/dev/null)
    ((total++))

    if [[ -v "EXPECTED_SEEDS[$tbl]" ]]; then
      if (( cnt > 0 )); then
        ok "${tbl}: ${cnt} rows (expected seeded data)"
        ((seeded++))
      else
        fail "${tbl}: EMPTY — expected seeded data"
        ((empty++))
      fi
    else
      if (( cnt > 0 )); then
        info "${tbl}: ${cnt} rows"
        ((seeded++))
      else
        info "${tbl}: empty"
        ((empty++))
      fi
    fi
  done

  printf "\n"
  info "Summary: ${total} tables, ${seeded} with data, ${empty} empty"

  # Check migrations
  local mig_count
  mig_count=$($MYSQL -e "SELECT COUNT(*) FROM migrations" "$DB_NAME" 2>/dev/null)
  if (( mig_count > 0 )); then
    ok "Migrations table has ${mig_count} entries"
  else
    fail "Migrations table is empty — migrations may not have run"
  fi

  # Check for pending migrations (compare file count vs DB)
  local mig_files
  SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
  LARAVEL_ROOT="${SCRIPT_DIR}/../../.."
  if [[ -d "${LARAVEL_ROOT}/database/migrations" ]]; then
    mig_files=$(find "${LARAVEL_ROOT}/database/migrations" -name '*.php' | wc -l)
    info "Migration files on disk: ${mig_files}, in DB: ${mig_count}"
    if (( mig_files > mig_count )); then
      warn "Possible pending migrations: ${mig_files} files vs ${mig_count} DB entries"
    else
      ok "Migration count matches (${mig_count})"
    fi
  fi
}

# ── Performance checks ────────────────────────────────────────
check_performance() {
  hdr "Performance & Health"

  # Slow query log status
  local slow_log
  slow_log=$($MYSQL -e "SHOW VARIABLES LIKE 'slow_query_log'" "$DB_NAME" 2>/dev/null | awk '{print $2}')
  if [[ "$slow_log" == "ON" ]]; then
    ok "Slow query log: enabled"
    local slow_count
    slow_count=$($MYSQL -e "SHOW STATUS LIKE 'Slow_queries'" "$DB_NAME" 2>/dev/null | awk '{print $2}')
    if (( slow_count > 0 )); then
      warn "Slow queries since last restart: ${slow_count}"
    else
      ok "No slow queries recorded"
    fi
  else
    warn "Slow query log: disabled"
  fi

  # InnoDB buffer pool usage
  local bp_total bp_free
  bp_total=$($MYSQL -e "SHOW STATUS LIKE 'Innodb_buffer_pool_pages_total'" "$DB_NAME" 2>/dev/null | awk '{print $2}')
  bp_free=$($MYSQL -e "SHOW STATUS LIKE 'Innodb_buffer_pool_pages_free'" "$DB_NAME" 2>/dev/null | awk '{print $2}')
  if [[ -n "$bp_total" && -n "$bp_free" && "$bp_total" -gt 0 ]]; then
    local used=$(( (bp_total - bp_free) * 100 / bp_total ))
    info "InnoDB buffer pool usage: ${used}% (${bp_free} free / ${bp_total} total pages)"
    if (( used > 95 )); then
      warn "Buffer pool nearly full — consider increasing innodb_buffer_pool_size"
    fi
  fi

  # Table fragmentation
  local frag_tables
  frag_tables=$($MYSQL -e "
    SELECT TABLE_NAME
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA='${DB_NAME}'
      AND DATA_FREE > 10485760
    ORDER BY DATA_FREE DESC
  " "$DB_NAME" 2>/dev/null)
  if [[ -n "$frag_tables" ]]; then
    warn "Tables with >10MB fragmentation: ${frag_tables//$'\n'/, }"
  else
    ok "No significant table fragmentation"
  fi

  # Long-running queries
  local long_q
  long_q=$($MYSQL -e "
    SELECT COUNT(*)
    FROM information_schema.PROCESSLIST
    WHERE COMMAND != 'Sleep'
      AND TIME > 30
      AND DB = '${DB_NAME}'
  " "$DB_NAME" 2>/dev/null)
  if (( long_q > 0 )); then
    warn "${long_q} queries running > 30s"
    $MYSQL_T -e "
      SELECT ID, USER, HOST, DB, COMMAND, TIME, STATE, LEFT(INFO, 80) AS Query
      FROM information_schema.PROCESSLIST
      WHERE COMMAND != 'Sleep' AND TIME > 30 AND DB = '${DB_NAME}'
    " "$DB_NAME" 2>/dev/null || true
  else
    ok "No long-running queries"
  fi

  # Table check (CHECK TABLE) for all tables
  local check_tables
  check_tables=$($MYSQL -e "
    SELECT GROUP_CONCAT(TABLE_NAME)
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA='${DB_NAME}' AND TABLE_TYPE='BASE TABLE'
  " "$DB_NAME" 2>/dev/null)
  if [[ -n "$check_tables" ]]; then
    local check_result
    check_result=$($MYSQL -e "
      CHECK TABLE ${check_tables//,/ ,}
    " "$DB_NAME" 2>/dev/null | grep -v "^$" || true)
    local bad
    bad=$(echo "$check_result" | grep -iv 'status.*ok' | grep -iv '^$' || true)
    if [[ -z "$bad" ]]; then
      ok "All tables passed CHECK TABLE"
    else
      warn "Some tables reported issues:"
      echo "$bad"
    fi
  fi
}

# ── Summary ───────────────────────────────────────────────────
summary() {
  hdr "Summary"
  printf "${GRN}PASS: %d${RST}  ${YLW}WARN: %d${RST}  ${RED}FAIL: %d${RST}\n" "$PASS_N" "$WARN_N" "$FAIL_N"
  if (( FAIL_N > 0 )); then
    exit 1
  fi
}

# ── Main ──────────────────────────────────────────────────────
MODE="${1:---full}"
case "$MODE" in
  --quick)    check_connection ;;
  --seeds)    check_connection; check_seeds ;;
  --schema)   check_connection; check_schema ;;
  --perf)     check_connection; check_performance ;;
  --full|*)   check_connection; check_schema; check_seeds; check_performance ;;
esac

summary
