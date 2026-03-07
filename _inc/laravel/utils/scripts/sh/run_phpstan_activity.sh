#!/bin/bash
# PHPStan per-file analysis for Controllers/Activity
# Analyzes each file individually with 180s timeout

LARAVEL_DIR="/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel"
cd "$LARAVEL_DIR"

DIR="app/Http/Controllers/Activity"
RESULTS_DIR="storage/phpstan_results"
RESULT_FILE="$RESULTS_DIR/app_Http_Controllers_Activity_perfile.txt"
TIMEOUT_SECS=180

mkdir -p "$RESULTS_DIR"

TOTAL_ERRORS=0
COMPLETED=0
TIMED_OUT=0
FILES_LIST=$(find "$DIR" -maxdepth 1 -name '*.php' | sort)
TOTAL_FILES=$(echo "$FILES_LIST" | wc -l)

echo "=============================================="
echo "PHPStan PER-FILE — $DIR"
echo "Files: $TOTAL_FILES, Timeout: ${TIMEOUT_SECS}s each"
echo "=============================================="
echo "" 

> "$RESULT_FILE"

for file in $FILES_LIST; do
    BASENAME=$(basename "$file")
    printf "  %-55s " "$BASENAME"
    
    START_TIME=$(date +%s)
    OUTPUT=$(timeout "$TIMEOUT_SECS" php vendor/bin/phpstan analyse \
        --level=3 \
        --memory-limit=2G \
        --no-progress \
        --error-format=table \
        "$file" 2>&1)
    EXIT_CODE=$?
    END_TIME=$(date +%s)
    ELAPSED=$((END_TIME - START_TIME))
    
    if [ $EXIT_CODE -eq 124 ]; then
        echo "TIMEOUT (${TIMEOUT_SECS}s)"
        echo "=== $BASENAME === TIMEOUT (${TIMEOUT_SECS}s)" >> "$RESULT_FILE"
        TIMED_OUT=$((TIMED_OUT + 1))
    else
        ERROR_COUNT=$(echo "$OUTPUT" | grep -oP '\[ERROR\] Found \K[0-9]+' 2>/dev/null || echo "0")
        if [ "$ERROR_COUNT" = "0" ]; then
            # Check for "No errors" message
            if echo "$OUTPUT" | grep -q 'No errors'; then
                ERROR_COUNT="0"
            fi
        fi
        echo "DONE (${ELAPSED}s, $ERROR_COUNT errors)"
        echo "=== $BASENAME === DONE (${ELAPSED}s, $ERROR_COUNT errors)" >> "$RESULT_FILE"
        if [ "$ERROR_COUNT" != "0" ]; then
            echo "$OUTPUT" >> "$RESULT_FILE"
        fi
        TOTAL_ERRORS=$((TOTAL_ERRORS + ERROR_COUNT))
        COMPLETED=$((COMPLETED + 1))
    fi
done

echo ""
echo "=============================================="
echo "SUMMARY — $DIR"
echo "=============================================="
echo "Completed: $COMPLETED / $TOTAL_FILES"
echo "Timed out: $TIMED_OUT"
echo "Total errors: $TOTAL_ERRORS"
echo "=============================================="
