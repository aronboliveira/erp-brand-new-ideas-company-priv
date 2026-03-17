#!/usr/bin/env bash
# ─────────────────────────────────────────────────────
# 10_csrf_validation.sh  –  CSRF token validation tests
# Auto-generated — do not edit by hand.
# ─────────────────────────────────────────────────────
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=_common.sh
source "$SCRIPT_DIR/_common.sh"

log_info "=== CSRF VALIDATION TESTS ==="
log_info "Testing that POST/PUT/PATCH/DELETE routes reject requests without CSRF"
do_login

curl_test POST "/_debugbars/queries/explain" "400,403,405,419,422,302" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # debugbar.queries.explain
curl_test POST "/apis/upload-photos" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # photos.upload
curl_test POST "/billsproduct" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # bills.product
curl_test POST "/business-setting" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # business.setting
curl_test POST "/chats/fetch-messages" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # fetch.messages
curl_test POST "/clients" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # clients.store
curl_test POST "/contracts/copies/store" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # contracts.copy.store
curl_test POST "/custom_pages/custom-store" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # custom_pages.custom.store
curl_test POST "/deals/1/file" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # deals.file.upload
curl_test POST "/edit-profile" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # users.account.update
curl_test POST "/employees/updates/sallaries/1" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # employees.salary.update
curl_test POST "/exports/profit-loss" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # reports.profit.loss.export
curl_test POST "/forms/view_store" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # forms.view.store
curl_test POST "/home_section" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # home_section.store
curl_test POST "/invoices/with-benefit" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # invoices.benefit.initiate
curl_test POST "/job-applications/1/rating" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # jobs.application.rating
curl_test POST "/journal_entries/accounts/destroy" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # journalsaccount.destroy
curl_test POST "/leads/1/labels" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # leads.labels.store
curl_test POST "/meetings/get_meeting_data" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # meetings.get_meeting_data
curl_test POST "/payslips/export" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # payslips.export
curl_test POST "/pricing_plans/store" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # pricing_plans.store
curl_test POST "/project_stages" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # project_stages.store
curl_test POST "/projects/links/1/1" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # projects.link
curl_test POST "/projects/1/gantt" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # projects.gantt.post
curl_test POST "/proposals/templates/settings" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # proposalssettings
curl_test POST "/reports-monthly-attendances/getdepartment" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # reports.attendance.getdepartment
curl_test POST "/screenshots/store" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # screenshots.store
curl_test POST "/stages/json" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # stages.json
curl_test POST "/systems/creates/ip" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # systems.ip.store
curl_test POST "/tracker-settings" "419,302,405,422,403" -H "Content-Type: application/x-www-form-urlencoded" -d "test=1"  # time_trackers.settings

log_info "CSRF validation tests complete."
summary
