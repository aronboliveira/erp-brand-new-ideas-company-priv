#!/usr/bin/env bash
# ──────────────────────────────────────────────────────────────────────
# run_phpunit_batched.sh — Run PHPUnit in micro-batches to avoid OOM
#
# Usage:
#   ./scripts/run_phpunit_batched.sh <test_dir> [batch_size] [output_dir]
#
# Example:
#   ./scripts/run_phpunit_batched.sh tests/Unit/app/Models/activity 5
# ──────────────────────────────────────────────────────────────────────
set -uo pipefail

TEST_DIR="${1:?Usage: $0 <test_dir> [batch_size] [output_dir]}"
BATCH_SIZE="${2:-5}"
OUTPUT_DIR="${3:-.tmp/copilot/phpunit_batches}"
MEM_LIMIT="${PHP_MEM_LIMIT:-1G}"

LARAVEL_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$LARAVEL_ROOT"

mkdir -p "$OUTPUT_DIR"

# Collect test files
mapfile -t FILES < <(find "$TEST_DIR" -name '*Test.php' -type f | sort)
TOTAL=${#FILES[@]}

if [[ $TOTAL -eq 0 ]]; then
  echo "No test files found in $TEST_DIR"
  exit 1
fi

echo "╔══════════════════════════════════════════════════════════════╗"
echo "║  PHPUnit Batched Runner                                      ║"
echo "║  Directory: $TEST_DIR"
echo "║  Files: $TOTAL | Batch size: $BATCH_SIZE | Memory: $MEM_LIMIT"
echo "╚══════════════════════════════════════════════════════════════╝"

TOTAL_TESTS=0
TOTAL_PASS=0
TOTAL_FAIL=0
TOTAL_ERROR=0
TOTAL_SKIP=0
BATCH_NUM=0
SUITE_NAME="$(basename "$TEST_DIR")"
REPORT_FILE="$OUTPUT_DIR/phpunit_${SUITE_NAME}.txt"

> "$REPORT_FILE"

for ((i=0; i<TOTAL; i+=BATCH_SIZE)); do
  BATCH_NUM=$((BATCH_NUM + 1))
  BATCH_FILES=("${FILES[@]:i:BATCH_SIZE}")
  BATCH_COUNT=${#BATCH_FILES[@]}

  echo ""
  echo "── Batch $BATCH_NUM ($BATCH_COUNT files, starting at $((i+1))/$TOTAL) ──"
  for f in "${BATCH_FILES[@]}"; do echo "   $(basename "$f")"; done

  # Check memory before batch
  FREE_MB=$(awk '/MemAvailable/ {printf "%.0f", $2/1024}' /proc/meminfo)
  if [[ $FREE_MB -lt 3000 ]]; then
    echo "⚠  Available memory: ${FREE_MB}MB — sleeping 5s for GC..."
    sleep 5
    FREE_MB=$(awk '/MemAvailable/ {printf "%.0f", $2/1024}' /proc/meminfo)
    if [[ $FREE_MB -lt 2000 ]]; then
      echo "✖  Memory too low (${FREE_MB}MB) — aborting"
      break
    fi
  fi

  # Run batch
  BATCH_OUTPUT=$(php -d memory_limit="$MEM_LIMIT" vendor/bin/phpunit \
    --no-coverage "${BATCH_FILES[@]}" 2>&1) || true

  echo "$BATCH_OUTPUT" >> "$REPORT_FILE"
  echo "---BATCH-${BATCH_NUM}-END---" >> "$REPORT_FILE"

  # Parse results from the last line(s)
  SUMMARY=$(echo "$BATCH_OUTPUT" | grep -oP '(Tests: \d+|OK \(\d+ test)' | tail -1)
  if echo "$BATCH_OUTPUT" | grep -q 'Fatal error'; then
    echo "  ✖  OOM or Fatal Error in batch $BATCH_NUM"
    # Try individual files for this batch
    for f in "${BATCH_FILES[@]}"; do
      SINGLE_OUT=$(php -d memory_limit="$MEM_LIMIT" vendor/bin/phpunit \
        --no-coverage "$f" 2>&1) || true
      echo "$SINGLE_OUT" >> "$REPORT_FILE"
      if echo "$SINGLE_OUT" | grep -q 'Fatal error'; then
        echo "    ✖  OOM in $(basename "$f") — skipping"
      fi
    done
  else
    # Extract numbers
    TESTS=$(echo "$BATCH_OUTPUT" | grep -oP 'Tests: \K\d+' | tail -1 || echo 0)
    FAILS=$(echo "$BATCH_OUTPUT" | grep -oP 'Failures: \K\d+' | tail -1 || echo 0)
    ERRORS=$(echo "$BATCH_OUTPUT" | grep -oP 'Errors: \K\d+' | tail -1 || echo 0)
    SKIPPED=$(echo "$BATCH_OUTPUT" | grep -oP 'Skipped: \K\d+' | tail -1 || echo 0)

    # Handle OK format
    if [[ -z "$TESTS" || "$TESTS" == "0" ]]; then
      TESTS=$(echo "$BATCH_OUTPUT" | grep -oP 'OK \(\K\d+' | tail -1 || echo 0)
      FAILS=0
      ERRORS=0
    fi

    TESTS=${TESTS:-0}
    FAILS=${FAILS:-0}
    ERRORS=${ERRORS:-0}
    SKIPPED=${SKIPPED:-0}
    PASS=$((TESTS - FAILS - ERRORS - SKIPPED))

    TOTAL_TESTS=$((TOTAL_TESTS + TESTS))
    TOTAL_PASS=$((TOTAL_PASS + PASS))
    TOTAL_FAIL=$((TOTAL_FAIL + FAILS))
    TOTAL_ERROR=$((TOTAL_ERROR + ERRORS))
    TOTAL_SKIP=$((TOTAL_SKIP + SKIPPED))

    echo "  ✓  Tests: $TESTS | Pass: $PASS | Fail: $FAILS | Error: $ERRORS"
  fi
done

echo ""
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║  FINAL SUMMARY: $SUITE_NAME"
echo "║  Tests: $TOTAL_TESTS | Pass: $TOTAL_PASS | Fail: $TOTAL_FAIL | Error: $TOTAL_ERROR | Skip: $TOTAL_SKIP"
if [[ $TOTAL_TESTS -gt 0 ]]; then
  PASS_RATE=$(awk "BEGIN {printf \"%.1f\", ($TOTAL_PASS/$TOTAL_TESTS)*100}")
  echo "║  Pass Rate: ${PASS_RATE}%"
fi
echo "║  Report: $REPORT_FILE"
echo "╚══════════════════════════════════════════════════════════════╝"

echo ""
echo "$SUITE_NAME: Tests=$TOTAL_TESTS Pass=$TOTAL_PASS Fail=$TOTAL_FAIL Error=$TOTAL_ERROR Skip=$TOTAL_SKIP" >> "$OUTPUT_DIR/summary.txt"
