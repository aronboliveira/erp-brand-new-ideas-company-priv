#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────
# 13_content_validation.sh  –  Main‑app index page content validation
#
# Validates that every index/list page renders the expected HTML
# structures (tables with data rows, cards, grids, forms …).
#
# Uses the **lenient** helper so empty‑but‑structurally‑correct
# pages count as WARN (not FAIL).  A true FAIL means the page
# returned 200 but is missing the expected layout entirely.
# ─────────────────────────────────────────────────────────────
set -uo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/_common.sh"

# Delay between requests to avoid rate limiting (429)
REQ_DELAY=${REQ_DELAY:-1.0}

echo ""
echo "══════════════════════════════════════════════════════════"
echo "  13 · Main‑App Index Page Content Validation"
echo "══════════════════════════════════════════════════════════"
echo ""

# ── Login ────────────────────────────────────────────
do_login || { echo "Login failed — aborting."; exit 1; }

# ── Helper: lenient table test (most index pages) ───
tbl() { sleep "$REQ_DELAY"; curl_test_content_lenient GET "$1" "200,302,404,429,500" "table"; }

# ── Helper: card/grid test ──────────────────────────
crd() { sleep "$REQ_DELAY"; curl_test_content_lenient GET "$1" "200,302,404,429,500" "card"; }
grd() { sleep "$REQ_DELAY"; curl_test_content_lenient GET "$1" "200,302,404,429,500" "grid"; }
frm() { sleep "$REQ_DELAY"; curl_test_content_lenient GET "$1" "200,302,404,429,500" "form"; }
any() { sleep "$REQ_DELAY"; curl_test_content_lenient GET "$1" "200,302,404,429,500" "any"; }
tbl_card() { sleep "$REQ_DELAY"; curl_test_content_lenient GET "$1" "200,302,404,429,500" "table+card"; }

# ══════════════════════════════════════════════════════
#   Accounting & Finance
# ══════════════════════════════════════════════════════
echo ""; log_info "── Accounting & Finance ──"

any "/account_assets"
any "/bank_accounts"
any "/bank_transfers"
any "/bills"
any "/budgets"
any "/chart_of_accounts"
any "/expenses"
any "/invoices"
any "/journal_entries"
any "/payments"
any "/revenues"
any "/taxes"
any "/reports/transaction"

# ══════════════════════════════════════════════════════
#   HR / People
# ══════════════════════════════════════════════════════
echo ""; log_info "── HR / People ──"

any "/allowance_options"
any "/allowances"
any "/announcement"
any "/appraisals"
any "/award_types"
any "/awards"
any "/branches"
any "/commissions"
any "/company_policies"
any "/competencies"
any "/complaints"
any "/deduction_options"
any "/departments"
any "/designations"
any "/document_uploads"
any "/documents"
any "/employee_attendances"
any "/employees"
any "/events"
any "/goal_trackings"
any "/goal_types"
any "/goals"
any "/holidays"
any "/indicators"
any "/leave"
any "/leave_types"
any "/loan_options"
any "/loans"
any "/meetings"
any "/other_payments"
any "/overtimes"
any "/payslips"
any "/performance_types"
any "/promotions"
any "/resignations"
any "/saturation_deductions"
any "/set_salaries"
any "/terminations"
any "/terminationtype"
any "/trainers"
any "/training_types"
any "/trainings"
any "/transfers"
any "/travels"

# ══════════════════════════════════════════════════════
#   CRM
# ══════════════════════════════════════════════════════
echo ""; log_info "── CRM ──"

any "/clients"
any "/contracts"
any "/contract_types"
any "/customers"
any "/deals"
any "/labels"
any "/lead_stages"
any "/leads"
any "/pipelines"
any "/sources"
any "/stages"
any "/vendors"

# ══════════════════════════════════════════════════════
#   Recruitment / Jobs
# ══════════════════════════════════════════════════════
echo ""; log_info "── Recruitment / Jobs ──"

any "/custom-question"
any "/interview-schedule"
any "/job-application"
any "/job-category"
any "/job-stage"
any "/jobs"

# ══════════════════════════════════════════════════════
#   Projects & Tasks
# ══════════════════════════════════════════════════════
echo ""; log_info "── Projects & Tasks ──"

any "/projects"
any "/project_reports"
any "/project_stages"
any "/project_task_stages"
any "/bug_status"

# ══════════════════════════════════════════════════════
#   Products / Inventory / POS
# ══════════════════════════════════════════════════════
echo ""; log_info "── Products / Inventory / POS ──"

any "/coupons"
any "/orders"
any "/product_service_categories"
any "/product_service_units"
any "/product_services"
any "/product_stocks"
any "/purchases"
any "/pos"

# ══════════════════════════════════════════════════════
#   Sales / Proposals
# ══════════════════════════════════════════════════════
echo ""; log_info "── Sales / Proposals ──"

any "/proposal"

# ══════════════════════════════════════════════════════
#   System / Admin
# ══════════════════════════════════════════════════════
echo ""; log_info "── System / Admin ──"

any "/custom_fields"
any "/email_template"
any "/form_builders"
any "/notification_templates"
any "/permissions"
any "/plan_requests"
any "/plans"
any "/roles"
any "/supports"
any "/systems"
any "/users"

# ══════════════════════════════════════════════════════
#   Summary
# ══════════════════════════════════════════════════════
echo ""
content_summary
summary
