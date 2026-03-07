#!/bin/bash
# PHPStan per-file analysis for remaining timed-out directories
# Analyzes each file individually with 300s timeout per file

LARAVEL_DIR="/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel"
cd "$LARAVEL_DIR"

RESULTS_DIR="storage/phpstan_results"
mkdir -p "$RESULTS_DIR"

TIMEOUT_SECS=300

# Directories/files to analyze
DIRS_TO_SCAN=(
    "app/Http/Controllers/Planning"
    "app/Http/Controllers/Bills"
    "app/Models/Bills"
)

# Activity files that timed out at 180s
ACTIVITY_TIMEOUT_FILES=(
    "app/Http/Controllers/Activity/AppraisalController.php"
    "app/Http/Controllers/Activity/DealController.php"
    "app/Http/Controllers/Activity/EventController.php"
    "app/Http/Controllers/Activity/LeadController.php"
    "app/Http/Controllers/Activity/PosController.php"
    "app/Http/Controllers/Activity/PurchaseController.php"
    "app/Http/Controllers/Activity/ReportController.php"
    "app/Http/Controllers/Activity/TransferController.php"
)

GRAND_TOTAL=0
GRAND_COMPLETED=0
GRAND_TIMEDOUT=0

analyze_file() {
    local file="$1"
    local result_file="$2"
    local basename=$(basename "$file")
    
    printf "  %-55s " "$basename"
    
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
        echo "=== $basename === TIMEOUT (${TIMEOUT_SECS}s)" >> "$result_file"
        GRAND_TIMEDOUT=$((GRAND_TIMEDOUT + 1))
        return 0
    else
        ERROR_COUNT=$(echo "$OUTPUT" | grep -oP '\[ERROR\] Found \K[0-9]+' 2>/dev/null || echo "0")
        if [ "$ERROR_COUNT" = "0" ] && echo "$OUTPUT" | grep -q 'No errors'; then
            ERROR_COUNT="0"
        fi
        echo "DONE (${ELAPSED}s, $ERROR_COUNT errors)"
        echo "=== $basename === DONE (${ELAPSED}s, $ERROR_COUNT errors)" >> "$result_file"
        if [ "$ERROR_COUNT" != "0" ]; then
            echo "$OUTPUT" >> "$result_file"
        fi
        GRAND_TOTAL=$((GRAND_TOTAL + ERROR_COUNT))
        GRAND_COMPLETED=$((GRAND_COMPLETED + 1))
        return 0
    fi
}

echo "=============================================="
echo "PHPStan PER-FILE — Remaining timed-out dirs"
echo "Timeout: ${TIMEOUT_SECS}s per file"
echo "=============================================="
echo ""

# First: handle Activity timeout files
echo "--- Activity timeout files (8 files) ---"
RESULT_FILE="$RESULTS_DIR/app_Http_Controllers_Activity_timeout_retry.txt"
> "$RESULT_FILE"
for file in "${ACTIVITY_TIMEOUT_FILES[@]}"; do
    if [ -f "$file" ]; then
        analyze_file "$file" "$RESULT_FILE"
    fi
done
echo ""

# Then: handle full directories
for dir in "${DIRS_TO_SCAN[@]}"; do
    if [ ! -d "$dir" ]; then
        echo "SKIP: $dir (not found)"
        continue
    fi

    FILE_COUNT=$(find "$dir" -maxdepth 1 -name '*.php' | wc -l)
    SAFE_NAME=$(echo "$dir" | tr '/' '_')
    RESULT_FILE="$RESULTS_DIR/${SAFE_NAME}_perfile.txt"
    > "$RESULT_FILE"

    echo "--- $dir ($FILE_COUNT files) ---"
    
    for file in $(find "$dir" -maxdepth 1 -name '*.php' | sort); do
        analyze_file "$file" "$RESULT_FILE"
    done
    echo ""
done

echo "=============================================="
echo "FINAL SUMMARY"
echo "=============================================="
echo "Completed files: $GRAND_COMPLETED"
echo "Timed out files: $GRAND_TIMEDOUT"
echo "Total errors (completed): $GRAND_TOTAL"
echo "=============================================="
