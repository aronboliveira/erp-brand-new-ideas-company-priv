#!/usr/bin/env bash
# run_phpunit_suites.sh — Runs each PHPUnit suite in its own process to avoid OOM.
# Usage: bash run_phpunit_suites.sh [MEMORY_LIMIT] [REPORT_DIR]
#
# Each suite runs as a separate `php` invocation so accumulated memory is released.
# Results are collected into a single summary report.

set -euo pipefail

MEMORY_LIMIT="${1:-2G}"
REPORT_DIR="${2:-.tmp/copilot/$(date +%Y%m%d_%H%M%S)}"
PHPUNIT="vendor/bin/phpunit"
TIMEOUT_S=300

mkdir -p "$REPORT_DIR"

# Extract suite names from phpunit.xml
SUITES=$(grep 'name="' phpunit.xml | sed -n 's/.*name="\([^"]*\)".*/\1/p')

TOTAL_PASS=0
TOTAL_FAIL=0
TOTAL_ERR=0
TOTAL_TESTS=0
SUITE_COUNT=0
FAILED_SUITES=""

echo "=== PHPUnit Suite Runner — $(date '+%Y-%m-%d %H:%M:%S') ==="
echo "Memory limit: $MEMORY_LIMIT"
echo "Report dir:   $REPORT_DIR"
echo ""

for suite in $SUITES; do
    SUITE_COUNT=$((SUITE_COUNT + 1))
    echo -n "  [$SUITE_COUNT] $suite ... "

    SUITE_REPORT="$REPORT_DIR/phpunit_${suite}.txt"

    # Run in subshell with timeout
    set +e
    timeout "$TIMEOUT_S" php -d memory_limit="$MEMORY_LIMIT" \
        "$PHPUNIT" --testsuite="$suite" --no-coverage --no-logging 2>&1 \
        > "$SUITE_REPORT"
    EXIT_CODE=$?
    set -e

    if [ $EXIT_CODE -eq 137 ] || [ $EXIT_CODE -eq 134 ]; then
        echo "OOM/KILLED (exit $EXIT_CODE)"
        FAILED_SUITES="$FAILED_SUITES $suite(OOM)"
        continue
    elif [ $EXIT_CODE -eq 124 ]; then
        echo "TIMEOUT (>${TIMEOUT_S}s)"
        FAILED_SUITES="$FAILED_SUITES $suite(TIMEOUT)"
        continue
    fi

    # Parse result line
    RESULT_LINE=$(grep -E "^(OK|Tests:|FAILURES)" "$SUITE_REPORT" | tail -1)
    if [ -z "$RESULT_LINE" ]; then
        RESULT_LINE=$(tail -3 "$SUITE_REPORT" | grep -E "Tests:|OK" | head -1)
    fi

    # Extract numbers
    TESTS=$(echo "$RESULT_LINE" | grep -oP 'Tests:\s*\K\d+' || echo 0)
    ERRORS=$(echo "$RESULT_LINE" | grep -oP 'Errors:\s*\K\d+' || echo 0)
    FAILURES=$(echo "$RESULT_LINE" | grep -oP 'Failures:\s*\K\d+' || echo 0)

    TOTAL_TESTS=$((TOTAL_TESTS + TESTS))
    TOTAL_ERR=$((TOTAL_ERR + ERRORS))
    TOTAL_FAIL=$((TOTAL_FAIL + FAILURES))

    if [ "$ERRORS" -gt 0 ] || [ "$FAILURES" -gt 0 ]; then
        echo "$TESTS tests, $ERRORS errors, $FAILURES failures"
        [ "$ERRORS" -gt 0 ] && FAILED_SUITES="$FAILED_SUITES $suite(E$ERRORS)"
    else
        PASS_COUNT=$((TESTS - ERRORS - FAILURES))
        TOTAL_PASS=$((TOTAL_PASS + PASS_COUNT))
        echo "$TESTS tests OK"
    fi

    # Check memory before next suite
    MEM_PCT=$(free -m | awk '/Mem:/{printf "%d", $3/$2*100}')
    if [ "$MEM_PCT" -gt 90 ]; then
        echo "  ⚠ Memory at ${MEM_PCT}% — pausing 10s"
        sleep 10
    fi
done

echo ""
echo "========================================"
echo "SUMMARY"
echo "========================================"
echo "Suites run:      $SUITE_COUNT"
echo "Total tests:     $TOTAL_TESTS"
echo "Total errors:    $TOTAL_ERR"
echo "Total failures:  $TOTAL_FAIL"
echo "Problematic:     ${FAILED_SUITES:-none}"
echo "========================================"

# Save summary
{
    echo "PHPUnit Suite Run — $(date '+%Y-%m-%d %H:%M:%S')"
    echo "Memory limit: $MEMORY_LIMIT"
    echo "Suites: $SUITE_COUNT"
    echo "Tests: $TOTAL_TESTS"
    echo "Errors: $TOTAL_ERR"
    echo "Failures: $TOTAL_FAIL"
    echo "Problematic: ${FAILED_SUITES:-none}"
} > "$REPORT_DIR/phpunit_summary.txt"

echo "Saved to $REPORT_DIR/phpunit_summary.txt"
