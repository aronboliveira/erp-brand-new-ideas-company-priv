#!/usr/bin/env bash
# ============================================================================
# run-jest.sh — Jest test runner for frontend tests
# Usage: bash run-jest.sh [--coverage] [--watch] [--pattern=core/]
# ============================================================================
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
JS_TEST_DIR="$(cd "$SCRIPT_DIR/../../../../laravel/tests/frontend/js" && pwd)"

cd "$JS_TEST_DIR"

ARGS=""

for arg in "$@"; do
  case $arg in
    --coverage) ARGS="$ARGS --coverage" ;;
    --watch)    ARGS="$ARGS --watch" ;;
    --pattern=*) ARGS="$ARGS --testPathPattern=${arg#*=}" ;;
    --verbose)  ARGS="$ARGS --verbose" ;;
  esac
done

echo "╔══════════════════════════════════════════╗"
echo "║  Jest Frontend Test Runner               ║"
echo "║  $(date '+%Y-%m-%d %H:%M:%S')                    ║"
echo "╚══════════════════════════════════════════╝"

npx jest $ARGS 2>&1

echo ""
echo "Done: $(date '+%H:%M:%S')"
