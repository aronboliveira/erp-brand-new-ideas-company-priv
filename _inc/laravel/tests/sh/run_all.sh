#!/usr/bin/env bash
# ─────────────────────────────────────────────────────
# run_all.sh  –  Run all curl test suites
# ─────────────────────────────────────────────────────
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "══════════════════════════════════════════════════════"
echo "  ERP Curl Test Suite Runner"
echo "  Base URL: ${ERP_BASE_URL:-http://127.0.0.1:8000}"
echo "══════════════════════════════════════════════════════"
echo ""

# Usage: run_all.sh [suite_numbers...]
#   e.g. ./run_all.sh 00 01 02   → only auth + GET + POST
#   e.g. ./run_all.sh             → all suites

SUITES=(
    00_auth.sh
    01_get_routes.sh
    02_post_routes.sh
    03_put_patch_routes.sh
    04_delete_routes.sh
    05_watch_health.sh
    06_timing_report.sh
    07_stress_quick.sh
    08_header_variants.sh
    09_unauthenticated.sh
    10_csrf_validation.sh
    11_json_api.sh
    12_landing_page.sh
)

TOTAL_PASS=0
TOTAL_FAIL=0
STARTED=$(date +%s)

run_suite() {
    local script="$1"
    local label="${script%.sh}"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "  Running: $label"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    if bash "$SCRIPT_DIR/$script" 2>&1; then
        echo "  ✓ $label: PASSED"
    else
        echo "  ✗ $label: FAILED (exit code $?)"
        ((TOTAL_FAIL++))
    fi
    ((TOTAL_PASS++))
}

if [[ $# -gt 0 ]]; then
    # Run specific suites
    for num in "$@"; do
        for s in "${SUITES[@]}"; do
            if [[ "$s" == "${num}"* ]]; then
                run_suite "$s"
            fi
        done
    done
else
    # Run all suites
    for s in "${SUITES[@]}"; do
        run_suite "$s"
    done
fi

ELAPSED=$(( $(date +%s) - STARTED ))
echo ""
echo "══════════════════════════════════════════════════════"
echo "  ALL SUITES COMPLETE"
echo "  Time: ${ELAPSED}s"
echo "  Suites run: $TOTAL_PASS"
echo "  Suites with failures: $TOTAL_FAIL"
echo "══════════════════════════════════════════════════════"
