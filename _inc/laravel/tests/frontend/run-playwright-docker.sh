#!/usr/bin/env bash
# Run Playwright tests in Docker container
# Avoids SIGSEGV and sandbox issues on host systems

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

usage() {
    echo -e "${BLUE}Usage:${NC} $0 [command] [options]"
    echo
    echo "Commands:"
    echo "  all         Run all frontend mock tests (default)"
    echo "  rbac        Run only RBAC tests"
    echo "  hardening   Run only hardening tests"
    echo "  api         Run only API response tests"
    echo "  debug       Open interactive shell in container"
    echo "  build       Build the Docker image only"
    echo "  clean       Remove containers and images"
    echo
    echo "Options:"
    echo "  --headed    Run tests in headed mode (requires X11 forwarding)"
    echo "  --ui        Open Playwright UI mode"
    echo
    echo "Examples:"
    echo "  $0                    # Run all tests"
    echo "  $0 rbac               # Run RBAC tests only"
    echo "  $0 debug              # Open shell for debugging"
    echo "  $0 clean              # Cleanup Docker resources"
    exit 0
}

build_image() {
    echo -e "${BLUE}Building Playwright Docker image...${NC}"
    docker compose -f docker-compose.playwright.yml build playwright-tests
    echo -e "${GREEN}Build complete!${NC}"
}

run_all_tests() {
    echo -e "${BLUE}Running all Playwright frontend tests...${NC}"
    docker compose -f docker-compose.playwright.yml run --rm playwright-tests
}

run_rbac_tests() {
    echo -e "${BLUE}Running RBAC tests...${NC}"
    docker compose -f docker-compose.playwright.yml --profile rbac run --rm playwright-rbac
}

run_hardening_tests() {
    echo -e "${BLUE}Running hardening tests...${NC}"
    docker compose -f docker-compose.playwright.yml --profile hardening run --rm playwright-hardening
}

run_api_tests() {
    echo -e "${BLUE}Running API response tests...${NC}"
    docker compose -f docker-compose.playwright.yml run --rm playwright-tests \
        npx playwright test --config=playwright-frontend.config.cjs api-responses.spec.ts --reporter=list
}

run_debug() {
    echo -e "${YELLOW}Opening interactive shell in Playwright container...${NC}"
    echo -e "${YELLOW}Run 'npx playwright test --config=playwright-frontend.config.cjs' to execute tests${NC}"
    docker compose -f docker-compose.playwright.yml --profile debug run --rm playwright-debug
}

clean_docker() {
    echo -e "${YELLOW}Cleaning up Docker resources...${NC}"
    docker compose -f docker-compose.playwright.yml down --rmi local --volumes --remove-orphans
    echo -e "${GREEN}Cleanup complete!${NC}"
}

# Parse arguments
COMMAND="${1:-all}"

case "$COMMAND" in
    -h|--help|help)
        usage
        ;;
    build)
        build_image
        ;;
    all)
        build_image
        run_all_tests
        ;;
    rbac)
        build_image
        run_rbac_tests
        ;;
    hardening)
        build_image
        run_hardening_tests
        ;;
    api)
        build_image
        run_api_tests
        ;;
    debug)
        build_image
        run_debug
        ;;
    clean)
        clean_docker
        ;;
    *)
        echo -e "${RED}Unknown command: $COMMAND${NC}"
        usage
        ;;
esac
