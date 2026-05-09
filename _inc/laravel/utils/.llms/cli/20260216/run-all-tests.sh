#!/usr/bin/env bash
# ============================================================================
# run-all-tests.sh — Master test runner for Finance/Admin Learning ERP
# Runs PHPUnit, Jest, and Playwright tests with summary
# Usage: bash run-all-tests.sh [--phpunit] [--jest] [--playwright] [--curl]
# ============================================================================
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LARAVEL_DIR="$(cd "$SCRIPT_DIR/../../../../laravel" && pwd)"
JS_TEST_DIR="$LARAVEL_DIR/tests/frontend/js"
LOG_DIR="$SCRIPT_DIR/logs"
mkdir -p "$LOG_DIR"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

run_all=true
run_phpunit=false
run_jest=false
run_playwright=false
run_curl=false

for arg in "$@"; do
  case $arg in
    --phpunit)    run_phpunit=true; run_all=false ;;
    --jest)       run_jest=true; run_all=false ;;
    --playwright) run_playwright=true; run_all=false ;;
    --curl)       run_curl=true; run_all=false ;;
  esac
done

echo -e "${BLUE}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║     Finance/Admin Learning ERP — Test Runner    ║${NC}"
echo -e "${BLUE}║     $(date '+%Y-%m-%d %H:%M:%S')                         ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════╝${NC}"

SUMMARY=""

# ---------- PHPUnit ----------
if $run_all || $run_phpunit; then
  echo -e "\n${YELLOW}▶ Running PHPUnit tests...${NC}"
  PHPUNIT_LOG="$LOG_DIR/phpunit_${TIMESTAMP}.log"
  cd "$LARAVEL_DIR"
  if php -d memory_limit=3G vendor/bin/phpunit --testsuite=Unit --no-progress > "$PHPUNIT_LOG" 2>&1; then
    PHPUNIT_RESULT=$(grep -E '^(OK|Tests:|FAILURES!)' "$PHPUNIT_LOG" | tail -1)
    echo -e "${GREEN}✓ PHPUnit: $PHPUNIT_RESULT${NC}"
    SUMMARY="$SUMMARY\nPHPUnit: $PHPUNIT_RESULT"
  else
    PHPUNIT_RESULT=$(grep -E '^(Tests:|FAILURES!)' "$PHPUNIT_LOG" | tail -1)
    echo -e "${RED}✗ PHPUnit: $PHPUNIT_RESULT${NC}"
    SUMMARY="$SUMMARY\nPHPUnit: $PHPUNIT_RESULT"
    echo "  Full log: $PHPUNIT_LOG"
  fi
fi

# ---------- Jest ----------
if $run_all || $run_jest; then
  echo -e "\n${YELLOW}▶ Running Jest tests...${NC}"
  JEST_LOG="$LOG_DIR/jest_${TIMESTAMP}.log"
  cd "$JS_TEST_DIR"
  if npx jest --verbose > "$JEST_LOG" 2>&1; then
    JEST_RESULT=$(grep -E '^Tests:' "$JEST_LOG" | tail -1)
    echo -e "${GREEN}✓ Jest: $JEST_RESULT${NC}"
    SUMMARY="$SUMMARY\nJest: $JEST_RESULT"
  else
    JEST_RESULT=$(grep -E '^Tests:' "$JEST_LOG" | tail -1)
    echo -e "${RED}✗ Jest: $JEST_RESULT${NC}"
    SUMMARY="$SUMMARY\nJest: $JEST_RESULT"
    echo "  Full log: $JEST_LOG"
  fi
fi

# ---------- Playwright ----------
if $run_all || $run_playwright; then
  echo -e "\n${YELLOW}▶ Running Playwright E2E tests...${NC}"
  PW_LOG="$LOG_DIR/playwright_${TIMESTAMP}.log"
  cd "$JS_TEST_DIR"
  if npx playwright test > "$PW_LOG" 2>&1; then
    PW_RESULT=$(grep -E 'passed|failed' "$PW_LOG" | tail -1)
    echo -e "${GREEN}✓ Playwright: $PW_RESULT${NC}"
    SUMMARY="$SUMMARY\nPlaywright: $PW_RESULT"
  else
    PW_RESULT=$(grep -E 'passed|failed' "$PW_LOG" | tail -1)
    echo -e "${RED}✗ Playwright: $PW_RESULT${NC}"
    SUMMARY="$SUMMARY\nPlaywright: $PW_RESULT"
    echo "  Full log: $PW_LOG"
  fi
fi

# ---------- Curl Route Testing ----------
if $run_all || $run_curl; then
  echo -e "\n${YELLOW}▶ Running Curl route tests...${NC}"
  CURL_LOG="$LOG_DIR/curl_${TIMESTAMP}.log"
  bash "$SCRIPT_DIR/curl-route-tester.sh" > "$CURL_LOG" 2>&1 || true
  CURL_ERRORS=$(grep -c 'ERROR' "$CURL_LOG" 2>/dev/null || echo "0")
  CURL_OK=$(grep -c 'OK' "$CURL_LOG" 2>/dev/null || echo "0")
  echo -e "${GREEN}✓ Curl: $CURL_OK OK, $CURL_ERRORS errors${NC}"
  SUMMARY="$SUMMARY\nCurl Routes: $CURL_OK OK, $CURL_ERRORS errors"
fi

# ---------- Summary ----------
echo -e "\n${BLUE}══════════════════════════════════════════════════${NC}"
echo -e "${BLUE}SUMMARY:${NC}"
echo -e "$SUMMARY"
echo -e "\n${BLUE}Logs saved to: $LOG_DIR/${NC}"
echo -e "${BLUE}══════════════════════════════════════════════════${NC}"
