#!/bin/bash
# Financial Module Test Script for ERP Brand New Ideas Company
# Tests mission-critical financial routes with various curl flags
# Outputs: JSON results for potential Playwright consumption

set -e

COOKIE_FILE="/tmp/admin_smoke_cookies6.txt"
BASE_URL="http://localhost:8888"
OUTPUT_DIR="/tmp/erp_financial_tests"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Create output directory
mkdir -p "$OUTPUT_DIR"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Results tracking
PASS=0
FAIL=0
WARN=0

log_pass() {
    echo -e "${GREEN}✓ PASS${NC}: $1"
    ((PASS++))
}

log_fail() {
    echo -e "${RED}✗ FAIL${NC}: $1"
    ((FAIL++))
}

log_warn() {
    echo -e "${YELLOW}⚠ WARN${NC}: $1"
    ((WARN++))
}

# Test function with detailed curl options
test_route() {
    local route="$1"
    local expected_code="${2:-200}"
    local method="${3:-GET}"
    local description="$4"
    
    local url="${BASE_URL}/${route}"
    local output_file="${OUTPUT_DIR}/${route//\//_}_${TIMESTAMP}.html"
    
    # Full curl with headers, timing, and content
    local response=$(curl -sS -w "\n%{http_code}\n%{time_total}\n%{size_download}" \
        -X "$method" \
        -b "$COOKIE_FILE" \
        -H "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8" \
        -H "Accept-Language: en-US,en;q=0.5" \
        -H "User-Agent: ERP-Financial-Tester/1.0" \
        --max-time 30 \
        -o "$output_file" \
        "$url" 2>&1)
    
    local http_code=$(echo "$response" | tail -3 | head -1)
    local time_total=$(echo "$response" | tail -2 | head -1)
    local size=$(echo "$response" | tail -1)
    
    # Validate response
    if [[ "$http_code" == "$expected_code" ]]; then
        log_pass "${description:-$route} ($http_code, ${time_total}s, ${size}B)"
        return 0
    elif [[ "$http_code" == "302" || "$http_code" == "301" ]]; then
        log_warn "${description:-$route} redirected ($http_code)"
        return 1
    else
        log_fail "${description:-$route} got $http_code, expected $expected_code"
        return 2
    fi
}

# Test route and validate HTML structure
test_route_with_validation() {
    local route="$1"
    local expected_code="${2:-200}"
    local required_elements="$3"
    local description="$4"
    
    local url="${BASE_URL}/${route}"
    local output_file="${OUTPUT_DIR}/${route//\//_}_${TIMESTAMP}.html"
    
    local http_code=$(curl -sS -w "%{http_code}" \
        -b "$COOKIE_FILE" \
        -H "Accept: text/html" \
        --max-time 30 \
        -o "$output_file" \
        "$url" 2>/dev/null)
    
    if [[ "$http_code" != "$expected_code" ]]; then
        log_fail "${description:-$route} HTTP $http_code != $expected_code"
        return 2
    fi
    
    # Validate required HTML elements
    if [[ -n "$required_elements" ]]; then
        IFS=',' read -ra elements <<< "$required_elements"
        for elem in "${elements[@]}"; do
            if ! grep -q "$elem" "$output_file" 2>/dev/null; then
                log_fail "${description:-$route} missing element: $elem"
                return 1
            fi
        done
    fi
    
    log_pass "${description:-$route} ($http_code, validated)"
    return 0
}

# Test JSON API endpoint
test_json_api() {
    local route="$1"
    local expected_code="${2:-200}"
    local required_keys="$3"
    local description="$4"
    
    local url="${BASE_URL}/${route}"
    local output_file="${OUTPUT_DIR}/${route//\//_}_api_${TIMESTAMP}.json"
    
    local response=$(curl -sS -w "\n%{http_code}" \
        -b "$COOKIE_FILE" \
        -H "Accept: application/json" \
        -H "X-Requested-With: XMLHttpRequest" \
        --max-time 30 \
        "$url" 2>/dev/null)
    
    local http_code=$(echo "$response" | tail -1)
    local body=$(echo "$response" | sed '$d')
    
    echo "$body" > "$output_file"
    
    if [[ "$http_code" != "$expected_code" ]]; then
        log_fail "${description:-$route} API HTTP $http_code != $expected_code"
        return 2
    fi
    
    # Validate JSON keys if specified
    if [[ -n "$required_keys" ]]; then
        IFS=',' read -ra keys <<< "$required_keys"
        for key in "${keys[@]}"; do
            if ! echo "$body" | grep -q "\"$key\"" 2>/dev/null; then
                log_fail "${description:-$route} API missing key: $key"
                return 1
            fi
        done
    fi
    
    log_pass "${description:-$route} API ($http_code)"
    return 0
}

echo "=================================================="
echo "  ERP Brand New Ideas Company Financial Module Tests"
echo "  Started: $(date)"
echo "=================================================="
echo ""

# ============================================
# SECTION 1: Invoice Module
# ============================================
echo "--- INVOICE MODULE ---"
test_route "invoices" 200 "GET" "Invoices Index"
test_route "invoices/create" 200 "GET" "Invoice Create Form"
test_route_with_validation "invoices" 200 "table,invoice" "Invoices Table Structure"

# ============================================
# SECTION 2: Bills Module  
# ============================================
echo ""
echo "--- BILLS MODULE ---"
test_route "bills" 200 "GET" "Bills Index"
test_route "bills/create" 200 "GET" "Bill Create Form"
test_route_with_validation "bills" 200 "table" "Bills Table Structure"

# ============================================
# SECTION 3: Payments Module
# ============================================
echo ""
echo "--- PAYMENTS MODULE ---"
test_route "payments" 200 "GET" "Payments Index"
test_route "payments/create" 200 "GET" "Payment Create Form"

# ============================================
# SECTION 4: Expenses Module
# ============================================
echo ""
echo "--- EXPENSES MODULE ---"
test_route "expenses" 200 "GET" "Expenses Index"
test_route "expenses/create" 200 "GET" "Expense Create Form"

# ============================================
# SECTION 5: Revenues Module
# ============================================
echo ""
echo "--- REVENUES MODULE ---"
test_route "revenues" 200 "GET" "Revenues Index"
test_route "revenues/create" 200 "GET" "Revenue Create Form"

# ============================================
# SECTION 6: Financial Reports
# ============================================
echo ""
echo "--- FINANCIAL REPORTS ---"
test_route "reports/invoice-summary" 200 "GET" "Invoice Summary Report"
test_route "reports/bill-summary" 200 "GET" "Bill Summary Report"
test_route "reports/expense-summary" 200 "GET" "Expense Summary Report"
test_route "reports/income-summary" 200 "GET" "Income Summary Report"
test_route "reports/income-vs-expense-summary" 200 "GET" "Income vs Expense Report"
test_route "reports/account-statement-report" 200 "GET" "Account Statement Report"
test_route "reports/trial-balance" 200 "GET" "Trial Balance Report"
test_route "reports/profit-loss" 200 "GET" "Profit/Loss Report"
test_route "reports/balance-sheet" 200 "GET" "Balance Sheet Report"

# ============================================
# SECTION 7: Payroll & HR Financial
# ============================================
echo ""
echo "--- PAYROLL & HR FINANCIAL ---"
test_route "payslips" 200 "GET" "Payslips Index"
test_route "set_salaries" 200 "GET" "Set Salaries Index"
test_route "allowances" 200 "GET" "Allowances Index"
test_route "deductions" 200 "GET" "Deductions Index"
test_route "loans" 200 "GET" "Loans Index"

# ============================================
# SECTION 8: Accounting
# ============================================
echo ""
echo "--- ACCOUNTING ---"
test_route "journal-entry" 200 "GET" "Journal Entry Index"
test_route "chart-of-accounts" 200 "GET" "Chart of Accounts"
test_route "transactions" 200 "GET" "Transactions List"
test_route "transfers" 200 "GET" "Transfers List"

# ============================================
# SECTION 9: Export Endpoints (AJAX)
# ============================================
echo ""
echo "--- EXPORT ENDPOINTS ---"
test_route "invoices/export" 200 "GET" "Invoice Export"
test_route "bills/export" 200 "GET" "Bills Export"

# ============================================
# Summary
# ============================================
echo ""
echo "=================================================="
echo "  TEST SUMMARY"
echo "=================================================="
echo -e "${GREEN}Passed: $PASS${NC}"
echo -e "${RED}Failed: $FAIL${NC}"
echo -e "${YELLOW}Warnings: $WARN${NC}"
echo ""
TOTAL=$((PASS + FAIL))
if [[ $TOTAL -gt 0 ]]; then
    RATE=$(echo "scale=1; $PASS * 100 / $TOTAL" | bc)
    echo "Pass Rate: ${RATE}%"
fi
echo ""
echo "HTML outputs saved to: $OUTPUT_DIR"
echo "Finished: $(date)"

# Generate JSON summary for Playwright consumption
cat > "${OUTPUT_DIR}/summary_${TIMESTAMP}.json" << EOF
{
    "timestamp": "$(date -Iseconds)",
    "results": {
        "passed": $PASS,
        "failed": $FAIL,
        "warnings": $WARN,
        "total": $TOTAL,
        "pass_rate": $RATE
    },
    "output_directory": "$OUTPUT_DIR"
}
EOF

echo "JSON summary: ${OUTPUT_DIR}/summary_${TIMESTAMP}.json"

# Exit with failure if any tests failed
[[ $FAIL -eq 0 ]] && exit 0 || exit 1
