#!/usr/bin/env bash
# ============================================================================
# run-playwright.sh — Playwright E2E test runner
# Usage: bash run-playwright.sh [--headed] [--ui] [--spec=login]
# ============================================================================
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
JS_TEST_DIR="$(cd "$SCRIPT_DIR/../../../../laravel/tests/frontend/js" && pwd)"

cd "$JS_TEST_DIR"

ARGS=""
SPEC=""

for arg in "$@"; do
  case $arg in
    --headed) ARGS="$ARGS --headed" ;;
    --ui)     ARGS="$ARGS --ui" ;;
    --spec=*) SPEC="${arg#*=}" ;;
    --debug)  ARGS="$ARGS --debug" ;;
  esac
done

echo "╔══════════════════════════════════════════╗"
echo "║  Playwright E2E Test Runner              ║"
echo "║  $(date '+%Y-%m-%d %H:%M:%S')                    ║"
echo "╚══════════════════════════════════════════╝"

if [[ -n "$SPEC" ]]; then
  npx playwright test "e2e/${SPEC}.spec.ts" $ARGS 2>&1
else
  npx playwright test $ARGS 2>&1
fi

echo ""
echo "Done: $(date '+%H:%M:%S')"
