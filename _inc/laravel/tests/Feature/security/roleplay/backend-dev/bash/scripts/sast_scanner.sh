#!/usr/bin/env bash
# ▓ Roleplay: Backend Developer — Static Analysis Security Testing (SAST)
# Dev backend. Scanner grep-based para padrões inseguros no código PHP.
# PULL REQUEST START
set -euo pipefail

PROJECT_ROOT="${1:-$(cd "$(dirname "$0")/../../../../.." && pwd)}"
REPORT="/tmp/backend-dev-sast-scan.json"

echo "[BACKEND-DEV] SAST Scanner v1.0"
echo "[BACKEND-DEV] Raiz: $PROJECT_ROOT"
echo "═══════════════════════════════════════════════════"

TOTAL_FINDINGS=0

scan_pattern() {
  local LABEL="$1"
  local PATTERN="$2"
  local SEVERITY="$3"
  local GLOB="$4"

  local MATCHES
  MATCHES=$(grep -rnE "$PATTERN" "$PROJECT_ROOT/app" "$PROJECT_ROOT/Modules" \
    --include="$GLOB" 2>/dev/null | grep -v "vendor/" | grep -v "node_modules/" | head -20 || echo "")

  local COUNT
  if [ -z "$MATCHES" ]; then
    COUNT=0
  else
    COUNT=$(echo "$MATCHES" | wc -l | tr -d ' ')
  fi

  if [[ "$COUNT" -gt 0 ]]; then
    echo "  [✗] $LABEL ($SEVERITY) — $COUNT ocorrências"
    echo "$MATCHES" | head -5 | while IFS= read -r line; do
      echo "      $line" | cut -c1-120
    done
    if [ "$COUNT" -gt 5 ]; then
      echo "      ... e mais $((COUNT - 5))"
    fi
    TOTAL_FINDINGS=$((TOTAL_FINDINGS + COUNT))
  else
    echo "  [✓] $LABEL — nenhuma ocorrência"
  fi
}

# ── Raw SQL / Query Building inseguro ────────────────────
echo ""
echo "[CATEGORY] SQL Injection Patterns"
scan_pattern "DB::raw() com variável" 'DB::raw\s*\(\s*["\x27].*\$' "CRITICAL" "*.php"
scan_pattern "whereRaw com variável" 'whereRaw\s*\(\s*["\x27].*\$' "CRITICAL" "*.php"
scan_pattern "selectRaw com variável" 'selectRaw\s*\(\s*["\x27].*\$' "HIGH" "*.php"
scan_pattern "query() concatenação" '\->query\s*\(\s*["\x27].*\.\s*\$' "CRITICAL" "*.php"

# ── XSS / Output Encoding ───────────────────────────────
echo ""
echo "[CATEGORY] XSS / Output Patterns"
scan_pattern "{!! (unescaped output)" '\{!!' "MEDIUM" "*.php"
scan_pattern "echo sem htmlspecialchars" 'echo\s+\$(?!this)' "HIGH" "*.php"

# ── File Operations ──────────────────────────────────────
echo ""
echo "[CATEGORY] File Operation Risks"
scan_pattern "file_get_contents com variável" 'file_get_contents\s*\(\s*\$' "HIGH" "*.php"
scan_pattern "unlink com variável" 'unlink\s*\(\s*\$' "MEDIUM" "*.php"
scan_pattern "exec/shell_exec/system" '\b(exec|shell_exec|system|passthru|popen)\s*\(' "CRITICAL" "*.php"

# ── Authentication / Authorization ───────────────────────
echo ""
echo "[CATEGORY] Auth Patterns"
scan_pattern "md5/sha1 para senhas" '\b(md5|sha1)\s*\(' "HIGH" "*.php"
scan_pattern "Hardcoded credentials" '(password|secret|api.key)\s*=\s*["\x27][^"\x27]{4,}' "CRITICAL" "*.php"

# ── Misc Security ────────────────────────────────────────
echo ""
echo "[CATEGORY] Misc Security"
scan_pattern "eval()" '\beval\s*\(' "CRITICAL" "*.php"
scan_pattern "serialize/unserialize" '\bunserialize\s*\(' "HIGH" "*.php"
scan_pattern "extract()" '\bextract\s*\(' "MEDIUM" "*.php"

echo ""
echo "═══════════════════════════════════════════════════"
echo "[BACKEND-DEV] Total findings: $TOTAL_FINDINGS"

cat > "$REPORT" << ENDJSON
{
  "actor": "backend-dev",
  "tool": "sast_scanner",
  "root": "$PROJECT_ROOT",
  "total_findings": $TOTAL_FINDINGS
}
ENDJSON
echo "[BACKEND-DEV] Relatório: $REPORT"
# PULL REQUEST END
