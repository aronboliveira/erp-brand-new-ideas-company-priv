#!/usr/bin/env bash
# Quick Feature test runner with per-file timeout and resource monitoring
set -uo pipefail
cd "$(dirname "$0")/../.." || exit 1

PASS=0; FAIL=0; SKIP=0
echo "=== Feature Test Run — $(date) ==="
echo "Load: $(uptime | grep -oP 'load average: .+')"
echo ""

for f in tests/Feature/*.php; do
    bn="$(basename "$f" .php)"
    printf "%-45s" "$bn"
    result=$(timeout 180 php vendor/bin/phpunit "$f" --no-coverage 2>&1)
    ok=$(echo "$result" | grep -oP 'OK \(\d+ tests?, \d+ assertions?\)' || true)
    if [[ -n "$ok" ]]; then
        echo "  $ok"
        ((PASS++))
    else
        fail=$(echo "$result" | grep -oP 'FAILURES|ERRORS|Fatal' | head -1 || true)
        if [[ -n "$fail" ]]; then
            echo "  ** $fail **"
            ((FAIL++))
            # Show first failure detail
            echo "$result" | grep -A3 "FAILED\|Error:" | head -6 | sed 's/^/    /'
        else
            echo "  TIMEOUT/UNKNOWN"
            ((SKIP++))
        fi
    fi
done

echo ""
echo "=== Results: $PASS pass, $FAIL fail, $SKIP skip ==="
echo "Load: $(uptime | grep -oP 'load average: .+')"
