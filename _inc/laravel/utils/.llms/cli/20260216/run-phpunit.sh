#!/usr/bin/env bash
# ============================================================================
# run-phpunit.sh — PHPUnit test runner with options
# Usage: bash run-phpunit.sh [--filter=TestName] [--suite=Unit|Feature]
# ============================================================================
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LARAVEL_DIR="$(cd "$SCRIPT_DIR/../../../../laravel" && pwd)"

cd "$LARAVEL_DIR"

FILTER=""
SUITE="Unit"
MEMORY="3G"

for arg in "$@"; do
  case $arg in
    --filter=*) FILTER="--filter=${arg#*=}" ;;
    --suite=*)  SUITE="${arg#*=}" ;;
    --memory=*) MEMORY="${arg#*=}" ;;
  esac
done

echo "╔══════════════════════════════════════════╗"
echo "║  PHPUnit Test Runner                     ║"
echo "║  Suite: $SUITE                           "
echo "║  $(date '+%Y-%m-%d %H:%M:%S')                    ║"
echo "╚══════════════════════════════════════════╝"

php -d memory_limit="$MEMORY" vendor/bin/phpunit \
  --testsuite="$SUITE" \
  --no-progress \
  $FILTER \
  2>&1

echo ""
echo "Done: $(date '+%H:%M:%S')"
