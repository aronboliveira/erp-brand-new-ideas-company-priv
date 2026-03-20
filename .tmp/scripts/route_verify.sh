#!/bin/bash
# Route verification script — curl all static GET routes
# Reads cookies.txt from CWD, outputs status per route
# Usage: bash route_verify.sh [cookies_file]

set -euo pipefail
COOKIE="${1:-cookies.txt}"
BASE="http://localhost:8000"
PASS=0; FAIL=0; REDIR=0; TOTAL=0

declare -A RESULTS

test_route() {
    local route="$1"
    local code
    code=$(curl -s -b "$COOKIE" -o /dev/null -w "%{http_code}" --max-time 15 "${BASE}/${route}" 2>/dev/null || echo "000")
    TOTAL=$((TOTAL + 1))
    if [[ "$code" == "200" ]]; then
        PASS=$((PASS + 1))
        echo "  ✅ 200  /${route}"
    elif [[ "$code" =~ ^3 ]]; then
        REDIR=$((REDIR + 1))
        echo "  ↪  ${code}  /${route}"
    else
        FAIL=$((FAIL + 1))
        echo "  ❌ ${code}  /${route}"
    fi
    RESULTS["${route:-ROOT}"]="$code"
}

echo "=== ROUTE VERIFICATION $(date +%Y-%m-%d) ==="
echo ""

echo "--- AUTH ---"
test_route "login"

echo ""
echo "--- DASHBOARDS ---"
for r in "" dashboard account-dashboard crm-dashboard hrm-dashboard pos-dashboard project-dashboard dashboard-view; do
    test_route "$r"
done

echo ""
echo "--- HRM: CORE ---"
for r in employees employees/create employee-profile employee_attendances employee_attendances/create employee_attendances/bulk-attendance departments departments/create designations designations/create branches branches/create; do
    test_route "$r"
done

echo ""
echo "--- HRM: LEAVE & ATTENDANCE ---"
for r in leaves/create leaves/export leave_types leave_types/create holidays holidays/create holiday-calendar; do
    test_route "$r"
done

echo ""
echo "--- HRM: MEETINGS & AWARDS ---"
for r in meetings meetings/create meeting-calendar awards awards/create award_types award_types/create; do
    test_route "$r"
done

echo ""
echo "--- HRM: HR ACTIONS ---"
for r in promotions promotions/create resignations resignations/create terminations terminations/create warnings warnings/create complaints complaints/create transfers transfers/create travels travels/create overtimes; do
    test_route "$r"
done

echo ""
echo "--- HRM: TRAINING ---"
for r in trainings trainings/create trainers trainers/create training_types training_types/create; do
    test_route "$r"
done

echo ""
echo "--- HRM: PAYROLL ---"
for r in set_salaries set_salaries/create payslips payslips/create payslips/employeepayslip allowances allowance_options deduction_options loan_options loans other_payments; do
    test_route "$r"
done

echo ""
echo "--- HRM: PERFORMANCE ---"
for r in appraisals appraisals/create goals goals/create goal_trackings goal_trackings/create goal_types goal_types/create indicators indicators/create competencies competencies/create performance_types performance_types/create; do
    test_route "$r"
done

echo ""
echo "--- HRM: DOCUMENTS ---"
for r in documents documents/create document_uploads document_uploads/create announcement announcements/create company_policies company_policies/create; do
    test_route "$r"
done

echo ""
echo "--- FINANCE: INVOICES & BILLS ---"
for r in invoices invoices/create invoices/export invoices/index invoices/items bills bills/create bills/export billsindex billsitems; do
    test_route "$r"
done

echo ""
echo "--- FINANCE: EXPENSES & REVENUE ---"
for r in expenses expenses/create expenses/index expenses/items expense-list revenues revenues/create revenues/index payments payments/create payments/index; do
    test_route "$r"
done

echo ""
echo "--- FINANCE: NOTES & PROPOSALS ---"
for r in credit-notes credit_notes/invoice custom-credit-note debit_notes debit_notes/bill custom-debit-note proposals/create proposals/export proposals/items proposal; do
    test_route "$r"
done

echo ""
echo "--- FINANCE: BANKING ---"
for r in bank_accounts bank_accounts/create bank_transfers bank_transfers/create bank_transfers/index taxes taxes/create; do
    test_route "$r"
done

echo ""
echo "--- FINANCE: ACCOUNTING ---"
for r in chart_of_accounts chart_of_accounts/create journal_entries journal_entries/create budgets budgets/create transactions/export; do
    test_route "$r"
done

echo ""
echo "--- CRM ---"
for r in leads leads/create leads/list lead_stages lead_stages/create deals deals/create deals/list pipelines pipelines/create sources sources/create stages stages/create labels labels/create; do
    test_route "$r"
done

echo ""
echo "--- PROJECTS ---"
for r in projects projects/create projects-view projects-users project_stages project_stages/create project_task_stages project_task_stages/create project_reports project_reports/create task-board-view; do
    test_route "$r"
done

echo ""
echo "--- POS & PRODUCTS ---"
for r in pos pos/create pos/barcode pos-print-setting warehouses warehouses/create warehouse_transfers warehouse_transfers/create purchases purchases/create product_services product_services/create product_services/export product_service_categories product_service_categories/create product_service_units product_service_units/create product_stocks product_stocks/create product_stocks/export product-categories orders coupons coupons/create; do
    test_route "$r"
done

echo ""
echo "--- REPORTS ---"
for r in reports/account-statement-report reports/bill-summary reports/expense-summary reports/income-summary reports/income-vs-expense-summary reports/invoice-report reports/invoice-summary reports/leave reports/payables reports/payrolls/export reports/product-stock-report reports/receivables reports/sales reports/tax-summary reports/transaction reports/trial-balance reports-daily-pos reports-daily-purchase reports-deal reports-lead reports-leave reports-monthly-attendance reports-monthly-cashflow reports-monthly-pos reports-monthly-purchase reports-payroll reports-pos-vs-purchase reports-quarterly-cashflow reports-warehouse; do
    test_route "$r"
done

echo ""
echo "--- RECRUITMENT ---"
for r in jobs jobs/create job-application job-applications/create job-category job-categories/create job-stage job-stages/create interview-schedule interview-schedules/create job-onboard candidates-job-applications custom-question custom-questions/create; do
    test_route "$r"
done

echo ""
echo "--- CONTRACTS ---"
for r in contracts contracts/create contracts/grid contract_types contract_types/create; do
    test_route "$r"
done

echo ""
echo "--- ADMIN ---"
for r in users users/create users-view users/logs roles roles/create permissions permissions/create settings email_template email_templates/create notification_templates notification_templates/create systems custom_fields custom_fields/create plans plans/create plan_requests; do
    test_route "$r"
done

echo ""
echo "--- ASSETS ---"
for r in account_assets account_assets/create account_statements/export; do
    test_route "$r"
done

echo ""
echo "--- SUPPORT ---"
for r in supports supports/create supports/grid; do
    test_route "$r"
done

echo ""
echo "--- SAAS ---"
for r in customers customers/create customers/export commissions commissions/create discover discover/create; do
    test_route "$r"
done

echo ""
echo "--- COMMUNICATION ---"
for r in chats events events/create zoom_meetings zoom_meetings/create; do
    test_route "$r"
done

echo ""
echo "--- FRONTEND ---"
for r in home_section features faqs testimonials join_us custom_pages landingpage about-us privacy-policy terms-and-conditions; do
    test_route "$r"
done

echo ""
echo "--- MISC ---"
for r in profile search home leave bug_status time_trackers screenshots; do
    test_route "$r"
done

echo ""
echo "======================================="
echo "TOTAL: $TOTAL | ✅ 200: $PASS | ↪ 3xx: $REDIR | ❌ FAIL: $FAIL"
echo "======================================="
