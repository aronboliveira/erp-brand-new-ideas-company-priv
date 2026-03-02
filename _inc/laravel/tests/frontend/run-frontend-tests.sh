#!/usr/bin/env bash
#
# Frontend Test Runner
# ====================
# Runs comprehensive Playwright tests for RBAC, API responses, forms, and navigation
#
# Usage:
#   ./run-frontend-tests.sh           # Run all tests
#   ./run-frontend-tests.sh rbac      # Run only RBAC tests
#   ./run-frontend-tests.sh api       # Run only API response tests
#   ./run-frontend-tests.sh forms     # Run only form tests
#   ./run-frontend-tests.sh nav       # Run only navigation tests
#   ./run-frontend-tests.sh --headed  # Run all tests with browser visible
#   ./run-frontend-tests.sh --ui      # Run with Playwright UI
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

# Change to project root
cd "$PROJECT_ROOT"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}   Frontend Playwright Test Runner      ${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Check if node_modules exists
if [ ! -d "node_modules" ]; then
    echo -e "${YELLOW}Installing dependencies...${NC}"
    npm install
fi

# Check if Playwright browsers are installed
if ! npx playwright --version &> /dev/null; then
    echo -e "${YELLOW}Installing Playwright browsers...${NC}"
    npx playwright install
fi

# Parse arguments
TEST_FILE=""
EXTRA_ARGS=""

for arg in "$@"; do
    case $arg in
        rbac)
            TEST_FILE="rbac.spec.ts"
            ;;
        api)
            TEST_FILE="api-responses.spec.ts"
            ;;
        forms)
            TEST_FILE="forms.spec.ts"
            ;;
        nav|navigation)
            TEST_FILE="navigation.spec.ts"
            ;;
        --headed)
            EXTRA_ARGS="$EXTRA_ARGS --headed"
            ;;
        --ui)
            EXTRA_ARGS="$EXTRA_ARGS --ui"
            ;;
        --debug)
            EXTRA_ARGS="$EXTRA_ARGS --debug"
            ;;
        *)
            EXTRA_ARGS="$EXTRA_ARGS $arg"
            ;;
    esac
done

# Build the command
CMD="npx playwright test --config=playwright-frontend.config.cjs"

if [ -n "$TEST_FILE" ]; then
    CMD="$CMD $TEST_FILE"
    echo -e "${GREEN}Running: $TEST_FILE${NC}"
else
    echo -e "${GREEN}Running: All frontend tests${NC}"
fi

if [ -n "$EXTRA_ARGS" ]; then
    CMD="$CMD $EXTRA_ARGS"
fi

echo -e "${BLUE}Command: $CMD${NC}"
echo ""

# Run the tests
$CMD

# Check exit code
if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}========================================${NC}"
    echo -e "${GREEN}   All tests passed!                    ${NC}"
    echo -e "${GREEN}========================================${NC}"
else
    echo ""
    echo -e "${RED}========================================${NC}"
    echo -e "${RED}   Some tests failed!                   ${NC}"
    echo -e "${RED}========================================${NC}"
    exit 1
fi
