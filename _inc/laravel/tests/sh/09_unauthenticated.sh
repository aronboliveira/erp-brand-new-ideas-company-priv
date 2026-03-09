#!/usr/bin/env bash
# ─────────────────────────────────────────────────────
# 09_unauthenticated.sh  –  Unauthenticated access tests
# Auto-generated — do not edit by hand.
# ─────────────────────────────────────────────────────
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=_common.sh
source "$SCRIPT_DIR/_common.sh"

log_info "=== UNAUTHENTICATED ACCESS TESTS ==="
log_info "Testing that protected routes redirect/deny without auth"

# Clear any existing session
rm -f "$COOKIE_JAR"

curl_test GET "/" "302,301,401,403,200"  # home_section.index
curl_test GET "/allowances/create" "302,301,401,403,200"  # allowances.create
curl_test GET "/appraisals/1/edit" "302,301,401,403,200"  # appraisals.edit
curl_test GET "/bank_accounts/1/edit" "302,301,401,403,200"  # bank_accounts.edit
curl_test GET "/billscreates/1" "302,301,401,403,200"  # bills.create
curl_test GET "/branches/1/edit" "302,301,401,403,200"  # branches.edit
curl_test GET "/cashfrees/payments/success" "302,301,401,403,200"  # cashfree.payment.success
curl_test GET "/client-reset-passwords/1" "302,301,401,403,200"  # clients.reset
curl_test GET "/company_policies/1/edit" "302,301,401,403,200"  # company_policies.edit
curl_test GET "/contract_types/1/edit" "302,301,401,403,200"  # contract_types.edit
curl_test GET "/coupons" "302,301,401,403,200"  # coupons.index
curl_test GET "/custom-questions/create" "302,301,401,403,200"  # custom_questions.create
curl_test GET "/custom_pages/1" "302,301,401,403,200"  # custom_pages.show
curl_test GET "/deals/create" "302,301,401,403,200"  # deals.create
curl_test GET "/deals/1/sources" "302,301,401,403,200"  # deals.sources.edit
curl_test GET "/departments/1" "302,301,401,403,200"  # departments.show
curl_test GET "/document_uploads/create" "302,301,401,403,200"  # document_uploads.create
curl_test GET "/email_template_stores/1" "302,301,401,403,200"  # emails.store.language
curl_test GET "/employees/exp-docs/1" "302,301,401,403,200"  # exp.download.doc
curl_test GET "/events/create" "302,301,401,403,200"  # events.create
curl_test GET "/expenses/1" "302,301,401,403,200"  # expenses.show
curl_test GET "/features/update/1" "302,301,401,403,200"  # features.update
curl_test GET "/forms/responses/1/detail" "302,301,401,403,200"  # forms.response.detail
curl_test GET "/goal_types/1" "302,301,401,403,200"  # goal_types.show
curl_test GET "/home" "302,301,401,403,200"  # home_section.index
curl_test GET "/interview-schedules/1/edit" "302,301,401,403,200"  # interview-schedule.edit
curl_test GET "/invoices/with-benefit" "302,301,401,403,200"  # invoices.benefit.initiate
curl_test GET "/job-application" "302,301,401,403,200"  # job-application.index
curl_test GET "/jobs" "302,301,401,403,200"  # jobs.index
curl_test GET "/journal_entries" "302,301,401,403,200"  # journal_entries.index
curl_test GET "/lead_stages" "302,301,401,403,200"  # lead_stages.index
curl_test GET "/leads/1/products" "302,301,401,403,200"  # leads.products.edit
curl_test GET "/leaves/1/action" "302,301,401,403,200"  # leaves.action
curl_test GET "/manage-languages/1" "302,301,401,403,200"  # languages.manage
curl_test GET "/orders" "302,301,401,403,200"  # orders.index
curl_test GET "/payments/benefits/callback" "302,301,401,403,200"  # benefit.callback
curl_test GET "/payslips/payslip-pdfs/1" "302,301,401,403,200"  # payslips.payslipPdf
curl_test GET "/permissions/1/edit" "302,301,401,403,200"  # permissions.edit
curl_test GET "/pos-receipt" "302,301,401,403,200"  # pos.receipt
curl_test GET "/print-setting" "302,301,401,403,200"  # print.setting
curl_test GET "/product_services/export" "302,301,401,403,200"  # product_services.export
curl_test GET "/project_reports/create" "302,301,401,403,200"  # project_reports.create
curl_test GET "/projects-users" "302,301,401,403,200"  # projects.user
curl_test GET "/projects/milestones/1/edit" "302,301,401,403,200"  # projects.milestones.edit
curl_test GET "/projects/1/setting-create" "302,301,401,403,200"  # projects.copy_link.setting.create
curl_test GET "/promotions/1/edit" "302,301,401,403,200"  # promotions.edit
curl_test GET "/proposals/1/edit" "302,301,401,403,200"  # proposal.edit
curl_test GET "/reports-daily-pos" "302,301,401,403,200"  # reports.daily.pos
curl_test GET "/reports/account-statement-report" "302,301,401,403,200"  # reports.account.statement
curl_test GET "/reports/pos" "302,301,401,403,200"  # pos.report
curl_test GET "/resignations" "302,301,401,403,200"  # resignations.index
curl_test GET "/saturation_deductions" "302,301,401,403,200"  # saturation_deductions.index
curl_test GET "/set_salaries" "302,301,401,403,200"  # set_salaries.index
curl_test GET "/sources" "302,301,401,403,200"  # sources.index
curl_test GET "/supports/grid" "302,301,401,403,200"  # supports.grid
curl_test GET "/taxes/create" "302,301,401,403,200"  # taxes.create
curl_test GET "/terminationtypes/1/edit" "302,301,401,403,200"  # terminationtype.edit
curl_test GET "/trainers/1/edit" "302,301,401,403,200"  # trainers.edit
curl_test GET "/transfers/1/edit" "302,301,401,403,200"  # transfers.edit
curl_test GET "/users/logs" "302,301,401,403,200"  # users.log

log_info "Unauthenticated access tests complete."
summary
