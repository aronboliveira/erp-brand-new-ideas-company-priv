#!/usr/bin/env bash
# ───────────────────────────────────────────────────────────────
# run_suites.sh — Run PHPUnit test suites one-at-a-time in
# separate processes so each gets its own memory pool.
# ───────────────────────────────────────────────────────────────
set -euo pipefail
cd "$(dirname "$0")/.."

MEM="${PHP_MEMORY:=2G}"
REPORT_DIR="${REPORT_DIR:-.tmp/copilot/$(date +%Y%m%d_%H%M%S)}"
mkdir -p "$REPORT_DIR"

SUITES=(
    Unit-Models
    Unit-Exports
    Unit-Imports
    Unit-Services
    Unit-Providers
    Unit-Mail
    Unit-Traits
    Unit-Jobs
    Unit-Exceptions
    Unit-Enums
    Unit-Middleware
    Unit-Database
    Unit-Modules
    Http-Activity
    Http-Planning
    Http-Bills
    Http-Individuals
    Http-Shapes
    Http-Companies
    Http-Configs
    Http-Products
    Http-Contact
    Http-Auth
    Http-Info
    Http-Views
    Http-Charts
    Http-SSR
    Http-Bugs
    Http-Other
    Feature
)

PASS=0
FAIL=0
SKIP=0
SUMMARY=""

for suite in "${SUITES[@]}"; do
    echo "━━━ Running suite: $suite ━━━"
    LOG="$REPORT_DIR/phpunit_${suite}.txt"
    set +e
    php -d memory_limit="$MEM" vendor/bin/phpunit \
        --testsuite "$suite" \
        --no-coverage \
        --colors=never \
        2>&1 | tee "$LOG"
    RC=${PIPESTATUS[0]}
    set -e

    if [[ $RC -eq 0 ]]; then
        STATUS="✓ PASS"
        PASS=$((PASS+1))
    elif grep -q 'Allowed memory size' "$LOG"; then
        STATUS="✗ OOM"
        FAIL=$((FAIL+1))
    elif grep -q 'No tests executed' "$LOG"; then
        STATUS="⊘ SKIP (0 tests)"
        SKIP=$((SKIP+1))
    else
        STATUS="✗ FAIL (rc=$RC)"
        FAIL=$((FAIL+1))
    fi
    SUMMARY+="  $STATUS  $suite"$'\n'
    echo ""
done

echo ""
echo "════════════════════════════════════════════════════"
echo "  SUITE SUMMARY  ($PASS passed, $FAIL failed, $SKIP skipped)"
echo "════════════════════════════════════════════════════"
echo "$SUMMARY"
echo "Reports in: $REPORT_DIR"
echo "$SUMMARY" > "$REPORT_DIR/phpunit_suite_summary.txt"
