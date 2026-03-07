#!/bin/bash
# PHPStan retry for timed-out directories with 3600s (1 hour) timeout
# Uses 8 parallel workers and keeps cache

set -uo pipefail
# NOT using set -e so timeouts/errors don't abort the script

LARAVEL_DIR="/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel"
cd "$LARAVEL_DIR"

RESULTS_DIR="storage/phpstan_results"
mkdir -p "$RESULTS_DIR"

# Directories that timed out at 1200s (Activity excluded — handled separately)
TIMED_OUT_DIRS=(
    "app/Http/Controllers/Bills"
    "app/Http/Controllers/Companies"
    "app/Http/Controllers/Individuals"
    "app/Http/Controllers/Planning"
    "app/Models/Bills"
)

TIMEOUT_SECS=3600
TOTAL_ERRORS=0
COMPLETED=0
TIMED_OUT=0

echo "=============================================="
echo "PHPStan RETRY - Timed-out directories"
echo "Timeout: ${TIMEOUT_SECS}s (1 hour) per directory"
echo "Workers: 8 parallel"
echo "=============================================="
echo ""

for dir in "${TIMED_OUT_DIRS[@]}"; do
    if [ ! -d "$dir" ]; then
        echo "SKIP: $dir (not found)"
        continue
    fi

    FILE_COUNT=$(find "$dir" -maxdepth 1 -name '*.php' | wc -l)
    SAFE_NAME=$(echo "$dir" | tr '/' '_')
    RESULT_FILE="$RESULTS_DIR/${SAFE_NAME}_retry.txt"

    printf "%-50s %3d files ... " "$dir" "$FILE_COUNT"

    START_TIME=$(date +%s)
    timeout "$TIMEOUT_SECS" php vendor/bin/phpstan analyse \
        --level=3 \
        --memory-limit=2G \
        --no-progress \
        --error-format=table \
        "$dir" > "$RESULT_FILE" 2>&1
    EXIT_CODE=$?
    END_TIME=$(date +%s)
    ELAPSED=$((END_TIME - START_TIME))

    if [ $EXIT_CODE -eq 124 ]; then
        echo "TIMEOUT (${TIMEOUT_SECS}s)"
        TIMED_OUT=$((TIMED_OUT + 1))
    else
        ERROR_COUNT=$(grep -oP '\[ERROR\] Found \K[0-9]+' "$RESULT_FILE" 2>/dev/null || echo "0")
        if [ "$ERROR_COUNT" = "0" ]; then
            ERROR_COUNT=$(grep -c '^\s*---' "$RESULT_FILE" 2>/dev/null || echo "0")
        fi
        echo "DONE (${ELAPSED}s, $ERROR_COUNT errors)"
        TOTAL_ERRORS=$((TOTAL_ERRORS + ERROR_COUNT))
        COMPLETED=$((COMPLETED + 1))
    fi
done

echo ""
echo "=============================================="
echo "RETRY SUMMARY"
echo "=============================================="
echo "Completed: $COMPLETED / ${#TIMED_OUT_DIRS[@]}"
echo "Still timed out: $TIMED_OUT"
echo "Total errors (completed): $TOTAL_ERRORS"
echo "=============================================="
