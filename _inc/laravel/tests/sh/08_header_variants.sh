#!/usr/bin/env bash
# ─────────────────────────────────────────────────────
# 08_header_variants.sh  –  Header variant coverage
# Auto-generated — do not edit by hand.
# ─────────────────────────────────────────────────────
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=_common.sh
source "$SCRIPT_DIR/_common.sh"

log_info "=== HEADER VARIANT TESTS ==="
do_login

curl_header_variants GET "/.well-knowns/appspecifics/com.chrome.devtools.json"
curl_header_variants GET "/allowances/creates/1"
curl_header_variants GET "/awards/1/edit"
curl_header_variants GET "/bills1/debit_notes/edits/1"
curl_header_variants GET "/calendars/1/1"
curl_header_variants GET "/commissions"
curl_header_variants GET "/contract_types/1/edit"
curl_header_variants GET "/credit_notes/invoice"
curl_header_variants GET "/customers"
curl_header_variants GET "/deals/1/files/1"
curl_header_variants GET "/designations/1"
curl_header_variants GET "/email_template_store"
curl_header_variants GET "/employees/salaries/1"
curl_header_variants GET "/expenses/1/payment"
curl_header_variants GET "/form_builders/1/fields/1/show"
curl_header_variants GET "/goals/1/edit"
curl_header_variants GET "/installs/environments/classic"
curl_header_variants GET "/invoices/with-benefit"
curl_header_variants GET "/job-onboard"
curl_header_variants GET "/journal_entries/create"
curl_header_variants GET "/leads/1/discussions"
curl_header_variants GET "/loan_options/create"
curl_header_variants GET "/notification_templates/1/edit"
curl_header_variants GET "/payslips"
curl_header_variants GET "/pipelines"
curl_header_variants GET "/pricing_plans"
curl_header_variants GET "/product_services/1"
curl_header_variants GET "/project_task_stages/1/edit"
curl_header_variants GET "/projects/time-trackers/1"
curl_header_variants GET "/promotions/create"
curl_header_variants GET "/purchases/pdfs/1"
curl_header_variants GET "/reports/account-statement-report"
curl_header_variants GET "/request_cancels/1"
curl_header_variants GET "/saturation_deductions/creates/1"
curl_header_variants GET "/share-projects/1"
curl_header_variants GET "/systems/create"
curl_header_variants GET "/terminationtypes/1/edit"
curl_header_variants GET "/trainings/1"
curl_header_variants GET "/users/create"
curl_header_variants GET "/vendors/1"

log_info "Header variant tests complete."
summary
