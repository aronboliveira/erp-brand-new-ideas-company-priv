#!/bin/bash
# Smoke Test Routes - Quick Access Script
# Usage: ./smoke_test.sh [basic|auth|help]

set -e

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../../.." && pwd)"
export SMOKE_TEST_URL="${SMOKE_TEST_URL:-http://127.0.0.1:8888}"

cd "$PROJECT_ROOT"

case "${1:-basic}" in
    basic|unauthenticated)
        echo "Running unauthenticated smoke tests..."
        python3 "$SCRIPT_DIR/smoke_test_routes.py"
        ;;
    
    auth|authenticated)
        echo "Running authenticated smoke tests..."
        python3 "$SCRIPT_DIR/smoke_test_authenticated.py"
        ;;
    
    both|all)
        echo "Running both unauthenticated and authenticated tests..."
        python3 "$SCRIPT_DIR/smoke_test_routes.py"
        echo ""
        echo "========================================"
        echo ""
        python3 "$SCRIPT_DIR/smoke_test_authenticated.py"
        ;;
    
    results|summary)
        echo "Opening test results..."
        if [ -f "_inc/utils/.llms/smoke_test_summary.txt" ]; then
            less "_inc/utils/.llms/smoke_test_summary.txt"
        else
            echo "No results found. Run tests first."
        fi
        ;;
    
    results-auth)
        echo "Opening authenticated test results..."
        if [ -f "_inc/utils/.llms/smoke_test_authenticated_summary.txt" ]; then
            less "_inc/utils/.llms/smoke_test_authenticated_summary.txt"
        else
            echo "No authenticated results found. Run tests first."
        fi
        ;;
    
    analysis)
        echo "Opening analysis document..."
        if [ -f "_inc/utils/.llms/routes_smoke_test_analysis.md" ]; then
            less "_inc/utils/.llms/routes_smoke_test_analysis.md"
        else
            echo "No analysis found."
        fi
        ;;
    
    clean)
        echo "Cleaning up test results..."
        rm -f _inc/utils/.llms/smoke_test*.txt
        rm -f _inc/utils/.llms/smoke_test*.json
        rm -f .tmp/smoke_test*.log
        echo "✓ Test results removed"
        ;;
    
    help|--help|-h)
        cat << EOF
Smoke Test Routes - Quick Access Script

USAGE:
    $0 [command]

COMMANDS:
    basic, unauthenticated   Run basic smoke tests (no auth)
    auth, authenticated      Run authenticated smoke tests
    both, all                Run both test suites
    results, summary         View unauthenticated test summary
    results-auth             View authenticated test summary
    analysis                 View complete analysis document
    clean                    Remove all test results
    help                     Show this help message

EXAMPLES:
    $0                       # Run basic tests (default)
    $0 auth                  # Run authenticated tests
    $0 results               # View test results

COMPOSER/NPM ALIASES:
    composer smoke-routes          # Same as: $0 basic
    composer smoke-routes-auth     # Same as: $0 auth
    npm run smoke:routes           # Same as: $0 basic
    npm run smoke:routes:auth      # Same as: $0 auth

RESULTS LOCATION:
    Persistent: _inc/utils/.llms/smoke_test_*.{json,txt,md}
    Temporary:  .tmp/smoke_test_*.{py,log}

EOF
        ;;
    
    *)
        echo "Unknown command: $1"
        echo "Run '$0 help' for usage information"
        exit 1
        ;;
esac
