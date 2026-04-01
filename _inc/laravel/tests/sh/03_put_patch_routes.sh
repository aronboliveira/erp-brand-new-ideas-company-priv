#!/usr/bin/env bash
# ─────────────────────────────────────────────────────
# 03_put_patch_routes.sh  –  PUT|PATCH route coverage (298 routes)
# Auto-generated — do not edit by hand.
# ─────────────────────────────────────────────────────
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=_common.sh
source "$SCRIPT_DIR/_common.sh"

log_info "=== PUT/PATCH ROUTE TESTS (298 routes) ==="

# Ensure authenticated session
do_login

# ── account_assets ──────────────────────────────────
curl_write_csrf "PUT" "/account_assets/1" "200,302,422,403,404,405,419"  # account_assets.update
curl_write_csrf "PATCH" "/account_assets/1" "200,302,422,403,404,405,419"  # account_assets.update

# ── allowance_options ──────────────────────────────────
curl_write_csrf "PUT" "/allowance_options/1" "200,302,422,403,404,405,419"  # allowance_options.update
curl_write_csrf "PATCH" "/allowance_options/1" "200,302,422,403,404,405,419"  # allowance_options.update

# ── allowances ──────────────────────────────────
curl_write_csrf "PUT" "/allowances/1" "200,302,422,403,404,405,419"  # allowances.update
curl_write_csrf "PATCH" "/allowances/1" "200,302,422,403,404,405,419"  # allowances.update

# ── announcements ──────────────────────────────────
curl_write_csrf "PUT" "/announcements/1" "200,302,422,403,404,405,419"  # announcement.update
curl_write_csrf "PATCH" "/announcements/1" "200,302,422,403,404,405,419"  # announcement.update

# ── appraisals ──────────────────────────────────
curl_write_csrf "PUT" "/appraisals/1" "200,302,422,403,404,405,419"  # appraisals.update
curl_write_csrf "PATCH" "/appraisals/1" "200,302,422,403,404,405,419"  # appraisals.update

# ── award_types ──────────────────────────────────
curl_write_csrf "PUT" "/award_types/1" "200,302,422,403,404,405,419"  # award_types.update
curl_write_csrf "PATCH" "/award_types/1" "200,302,422,403,404,405,419"  # award_types.update

# ── awards ──────────────────────────────────
curl_write_csrf "PUT" "/awards/1" "200,302,422,403,404,405,419"  # awards.update
curl_write_csrf "PATCH" "/awards/1" "200,302,422,403,404,405,419"  # awards.update

# ── bank_accounts ──────────────────────────────────
curl_write_csrf "PUT" "/bank_accounts/1" "200,302,422,403,404,405,419"  # bank_accounts.update
curl_write_csrf "PATCH" "/bank_accounts/1" "200,302,422,403,404,405,419"  # bank_accounts.update

# ── bank_transfers ──────────────────────────────────
curl_write_csrf "PUT" "/bank_transfers/1" "200,302,422,403,404,405,419"  # bank_transfers.update
curl_write_csrf "PATCH" "/bank_transfers/1" "200,302,422,403,404,405,419"  # bank_transfers.update

# ── bills ──────────────────────────────────
curl_write_csrf "PUT" "/bills/1" "200,302,422,403,404,405,419"  # bills.update
curl_write_csrf "PATCH" "/bills/1" "200,302,422,403,404,405,419"  # bills.update

# ── branches ──────────────────────────────────
curl_write_csrf "PUT" "/branches/1" "200,302,422,403,404,405,419"  # branches.update
curl_write_csrf "PATCH" "/branches/1" "200,302,422,403,404,405,419"  # branches.update

# ── budgets ──────────────────────────────────
curl_write_csrf "PUT" "/budgets/1" "200,302,422,403,404,405,419"  # budgets.update
curl_write_csrf "PATCH" "/budgets/1" "200,302,422,403,404,405,419"  # budgets.update

# ── bug_statuses ──────────────────────────────────
curl_write_csrf "PUT" "/bug_statuses/1" "200,302,422,403,404,405,419"  # bug_status.update
curl_write_csrf "PATCH" "/bug_statuses/1" "200,302,422,403,404,405,419"  # bug_status.update

# ── cashfrees ──────────────────────────────────
curl_write_csrf "PUT" "/cashfrees/payments/success" "200,302,422,403,404,405,419"  # cashfree.payment.success
curl_write_csrf "PATCH" "/cashfrees/payments/success" "200,302,422,403,404,405,419"  # cashfree.payment.success

# ── chart_of_accounts ──────────────────────────────────
curl_write_csrf "PUT" "/chart_of_accounts/1" "200,302,422,403,404,405,419"  # chart_of_accounts.update
curl_write_csrf "PATCH" "/chart_of_accounts/1" "200,302,422,403,404,405,419"  # chart_of_accounts.update

# ── client-reset-passwords ──────────────────────────────────
curl_write_csrf "PUT" "/client-reset-passwords/1" "200,302,422,403,404,405,419"  # clients.reset
curl_write_csrf "PATCH" "/client-reset-passwords/1" "200,302,422,403,404,405,419"  # clients.reset

# ── clients ──────────────────────────────────
curl_write_csrf "PUT" "/clients/1" "200,302,422,403,404,405,419"  # clients.update
curl_write_csrf "PATCH" "/clients/1" "200,302,422,403,404,405,419"  # clients.update

# ── commissions ──────────────────────────────────
curl_write_csrf "PUT" "/commissions/1" "200,302,422,403,404,405,419"  # commissions.update
curl_write_csrf "PATCH" "/commissions/1" "200,302,422,403,404,405,419"  # commissions.update

# ── company_policies ──────────────────────────────────
curl_write_csrf "PUT" "/company_policies/1" "200,302,422,403,404,405,419"  # company_policies.update
curl_write_csrf "PATCH" "/company_policies/1" "200,302,422,403,404,405,419"  # company_policies.update

# ── competencies ──────────────────────────────────
curl_write_csrf "PUT" "/competencies/1" "200,302,422,403,404,405,419"  # competencies.update
curl_write_csrf "PATCH" "/competencies/1" "200,302,422,403,404,405,419"  # competencies.update

# ── complaints ──────────────────────────────────
curl_write_csrf "PUT" "/complaints/1" "200,302,422,403,404,405,419"  # complaints.update
curl_write_csrf "PATCH" "/complaints/1" "200,302,422,403,404,405,419"  # complaints.update

# ── contract_types ──────────────────────────────────
curl_write_csrf "PUT" "/contract_types/1" "200,302,422,403,404,405,419"  # contract_types.update
curl_write_csrf "PATCH" "/contract_types/1" "200,302,422,403,404,405,419"  # contract_types.update

# ── contracts ──────────────────────────────────
curl_write_csrf "PUT" "/contracts/clients/selects/1" "200,302,422,403,404,405,419"  # contracts.clients.select
curl_write_csrf "PUT" "/contracts/1" "200,302,422,403,404,405,419"  # contracts.update
curl_write_csrf "PATCH" "/contracts/clients/selects/1" "200,302,422,403,404,405,419"  # contracts.clients.select
curl_write_csrf "PATCH" "/contracts/1" "200,302,422,403,404,405,419"  # contracts.update

# ── cookie-consent ──────────────────────────────────
curl_write_csrf "PUT" "/cookie-consent" "200,302,422,403,404,405,419"  # cookie-consent
curl_write_csrf "PATCH" "/cookie-consent" "200,302,422,403,404,405,419"  # cookie-consent

# ── coupons ──────────────────────────────────
curl_write_csrf "PUT" "/coupons/1" "200,302,422,403,404,405,419"  # coupons.update
curl_write_csrf "PATCH" "/coupons/1" "200,302,422,403,404,405,419"  # coupons.update

# ── custom-questions ──────────────────────────────────
curl_write_csrf "PUT" "/custom-questions/1" "200,302,422,403,404,405,419"  # custom-question.update
curl_write_csrf "PUT" "/custom-questions/1" "200,302,422,403,404,405,419"  # custom_questions.update
curl_write_csrf "PATCH" "/custom-questions/1" "200,302,422,403,404,405,419"  # custom-question.update

# ── custom_fields ──────────────────────────────────
curl_write_csrf "PUT" "/custom_fields/1" "200,302,422,403,404,405,419"  # custom_fields.update
curl_write_csrf "PATCH" "/custom_fields/1" "200,302,422,403,404,405,419"  # custom_fields.update

# ── custom_pages ──────────────────────────────────
curl_write_csrf "PUT" "/custom_pages/1" "200,302,422,403,404,405,419"  # custom_pages.update
curl_write_csrf "PATCH" "/custom_pages/1" "200,302,422,403,404,405,419"  # custom_pages.update

# ── customers ──────────────────────────────────
curl_write_csrf "PUT" "/customers/1" "200,302,422,403,404,405,419"  # customers.update
curl_write_csrf "PATCH" "/customers/1" "200,302,422,403,404,405,419"  # customers.update

# ── deals ──────────────────────────────────
curl_write_csrf "PUT" "/deals/1" "200,302,422,403,404,405,419"  # deals.update
curl_write_csrf "PUT" "/deals/1/calls/1" "200,302,422,403,404,405,419"  # deals.calls.update
curl_write_csrf "PUT" "/deals/1/clients" "200,302,422,403,404,405,419"  # deals.clients.update
curl_write_csrf "PUT" "/deals/1/permissions/1" "200,302,422,403,404,405,419"  # deals.client.permissions.store
curl_write_csrf "PUT" "/deals/1/products" "200,302,422,403,404,405,419"  # deals.products.update
curl_write_csrf "PUT" "/deals/1/sources" "200,302,422,403,404,405,419"  # deals.sources.update
curl_write_csrf "PUT" "/deals/1/task_statuses/1" "200,302,422,403,404,405,419"  # deals.tasks.update_status
curl_write_csrf "PUT" "/deals/1/tasks/1" "200,302,422,403,404,405,419"  # deals.tasks.update
curl_write_csrf "PUT" "/deals/1/users" "200,302,422,403,404,405,419"  # deals.users.update
curl_write_csrf "PATCH" "/deals/1" "200,302,422,403,404,405,419"  # deals.update

# ── deduction_options ──────────────────────────────────
curl_write_csrf "PUT" "/deduction_options/1" "200,302,422,403,404,405,419"  # deduction_options.update
curl_write_csrf "PATCH" "/deduction_options/1" "200,302,422,403,404,405,419"  # deduction_options.update

# ── departments ──────────────────────────────────
curl_write_csrf "PUT" "/departments/1" "200,302,422,403,404,405,419"  # departments.update
curl_write_csrf "PATCH" "/departments/1" "200,302,422,403,404,405,419"  # departments.update

# ── designations ──────────────────────────────────
curl_write_csrf "PUT" "/designations/1" "200,302,422,403,404,405,419"  # designations.update
curl_write_csrf "PATCH" "/designations/1" "200,302,422,403,404,405,419"  # designations.update

# ── discover ──────────────────────────────────
curl_write_csrf "PUT" "/discover/1" "200,302,422,403,404,405,419"  # discover.update
curl_write_csrf "PATCH" "/discover/1" "200,302,422,403,404,405,419"  # discover.update

# ── document_uploads ──────────────────────────────────
curl_write_csrf "PUT" "/document_uploads/1" "200,302,422,403,404,405,419"  # document_uploads.update
curl_write_csrf "PATCH" "/document_uploads/1" "200,302,422,403,404,405,419"  # document_uploads.update

# ── documents ──────────────────────────────────
curl_write_csrf "PUT" "/documents/1" "200,302,422,403,404,405,419"  # documents.update
curl_write_csrf "PATCH" "/documents/1" "200,302,422,403,404,405,419"  # documents.update

# ── edit-profile ──────────────────────────────────
curl_write_csrf "PUT" "/edit-profile" "200,302,422,403,404,405,419"  # users.account.update
curl_write_csrf "PATCH" "/edit-profile" "200,302,422,403,404,405,419"  # users.account.update

# ── email_template_store ──────────────────────────────────
curl_write_csrf "PUT" "/email_template_store" "200,302,422,403,404,405,419"  # emails.status.language
curl_write_csrf "PATCH" "/email_template_store" "200,302,422,403,404,405,419"  # emails.status.language

# ── email_template_stores ──────────────────────────────────
curl_write_csrf "PUT" "/email_template_stores/1" "200,302,422,403,404,405,419"  # emails.store.language
curl_write_csrf "PATCH" "/email_template_stores/1" "200,302,422,403,404,405,419"  # emails.store.language

# ── email_templates ──────────────────────────────────
curl_write_csrf "PUT" "/email_templates/1" "200,302,422,403,404,405,419"  # email_template.update
curl_write_csrf "PATCH" "/email_templates/1" "200,302,422,403,404,405,419"  # email_template.update

# ── employee_attendances ──────────────────────────────────
curl_write_csrf "PUT" "/employee_attendances/1" "200,302,422,403,404,405,419"  # employee_attendances.update
curl_write_csrf "PATCH" "/employee_attendances/1" "200,302,422,403,404,405,419"  # employee_attendances.update

# ── employees ──────────────────────────────────
curl_write_csrf "PUT" "/employees/1" "200,302,422,403,404,405,419"  # employees.update
curl_write_csrf "PATCH" "/employees/1" "200,302,422,403,404,405,419"  # employees.update

# ── events ──────────────────────────────────
curl_write_csrf "PUT" "/events/get_dashboard_event_data" "200,302,422,403,404,405,419"  # events.get_dashboard_event_data
curl_write_csrf "PUT" "/events/get_event_data" "200,302,422,403,404,405,419"  # events.get_event_data
curl_write_csrf "PUT" "/events/1" "200,302,422,403,404,405,419"  # events.update
curl_write_csrf "PATCH" "/events/get_dashboard_event_data" "200,302,422,403,404,405,419"  # events.get_dashboard_event_data
curl_write_csrf "PATCH" "/events/get_event_data" "200,302,422,403,404,405,419"  # events.get_event_data
curl_write_csrf "PATCH" "/events/1" "200,302,422,403,404,405,419"  # events.update

# ── expenses ──────────────────────────────────
curl_write_csrf "PUT" "/expenses/customer" "200,302,422,403,404,405,419"  # expenses.customer
curl_write_csrf "PUT" "/expenses/1" "200,302,422,403,404,405,419"  # expenses.update
curl_write_csrf "PATCH" "/expenses/customer" "200,302,422,403,404,405,419"  # expenses.customer
curl_write_csrf "PATCH" "/expenses/1" "200,302,422,403,404,405,419"  # expenses.update

# ── faqs ──────────────────────────────────
curl_write_csrf "PUT" "/faqs/1" "200,302,422,403,404,405,419"  # faqs.update
curl_write_csrf "PATCH" "/faqs/1" "200,302,422,403,404,405,419"  # faqs.update

# ── features ──────────────────────────────────
curl_write_csrf "PUT" "/features/1" "200,302,422,403,404,405,419"  # features.update
curl_write_csrf "PATCH" "/features/1" "200,302,422,403,404,405,419"  # features.update

# ── form_builders ──────────────────────────────────
curl_write_csrf "PUT" "/form_builders/1" "200,302,422,403,404,405,419"  # form_builders.update
curl_write_csrf "PATCH" "/form_builders/1" "200,302,422,403,404,405,419"  # form_builders.update

# ── goal_trackings ──────────────────────────────────
curl_write_csrf "PUT" "/goal_trackings/1" "200,302,422,403,404,405,419"  # goal_trackings.update
curl_write_csrf "PATCH" "/goal_trackings/1" "200,302,422,403,404,405,419"  # goal_trackings.update

# ── goal_types ──────────────────────────────────
curl_write_csrf "PUT" "/goal_types/1" "200,302,422,403,404,405,419"  # goal_types.update
curl_write_csrf "PATCH" "/goal_types/1" "200,302,422,403,404,405,419"  # goal_types.update

# ── goals ──────────────────────────────────
curl_write_csrf "PUT" "/goals/1" "200,302,422,403,404,405,419"  # goals.update
curl_write_csrf "PATCH" "/goals/1" "200,302,422,403,404,405,419"  # goals.update

# ── holidays ──────────────────────────────────
curl_write_csrf "PUT" "/holidays/data" "200,302,422,403,404,405,419"  # holidays.get_holiday_data
curl_write_csrf "PUT" "/holidays/1" "200,302,422,403,404,405,419"  # holidays.update
curl_write_csrf "PATCH" "/holidays/data" "200,302,422,403,404,405,419"  # holidays.get_holiday_data
curl_write_csrf "PATCH" "/holidays/1" "200,302,422,403,404,405,419"  # holidays.update

# ── home_section ──────────────────────────────────
curl_write_csrf "PUT" "/home_section/1" "200,302,422,403,404,405,419"  # home_section.update
curl_write_csrf "PATCH" "/home_section/1" "200,302,422,403,404,405,419"  # home_section.update

# ── indicators ──────────────────────────────────
curl_write_csrf "PUT" "/indicators/1" "200,302,422,403,404,405,419"  # indicators.update
curl_write_csrf "PATCH" "/indicators/1" "200,302,422,403,404,405,419"  # indicators.update

# ── interview-schedules ──────────────────────────────────
curl_write_csrf "PUT" "/interview-schedules/1" "200,302,422,403,404,405,419"  # interview-schedule.update
curl_write_csrf "PATCH" "/interview-schedules/1" "200,302,422,403,404,405,419"  # interview-schedule.update

# ── interview_schedules ──────────────────────────────────
curl_write_csrf "PUT" "/interview_schedules/data" "200,302,422,403,404,405,419"  # interview_schedules.get_interview_data
curl_write_csrf "PATCH" "/interview_schedules/data" "200,302,422,403,404,405,419"  # interview_schedules.get_interview_data

# ── invoices ──────────────────────────────────
curl_write_csrf "PUT" "/invoices/benefits/1/1" "200,302,422,403,404,405,419"  # invoices.benefit.callback
curl_write_csrf "PUT" "/invoices/with-benefit" "200,302,422,403,404,405,419"  # invoices.benefit.initiate
curl_write_csrf "PUT" "/invoices/with-cashfrees/status" "200,302,422,403,404,405,419"  # invoices.cashfree.payment.success
curl_write_csrf "PUT" "/invoices/1" "200,302,422,403,404,405,419"  # invoices.update
curl_write_csrf "PATCH" "/invoices/benefits/1/1" "200,302,422,403,404,405,419"  # invoices.benefit.callback
curl_write_csrf "PATCH" "/invoices/with-benefit" "200,302,422,403,404,405,419"  # invoices.benefit.initiate
curl_write_csrf "PATCH" "/invoices/with-cashfrees/status" "200,302,422,403,404,405,419"  # invoices.cashfree.payment.success
curl_write_csrf "PATCH" "/invoices/1" "200,302,422,403,404,405,419"  # invoices.update

# ── job-applications ──────────────────────────────────
curl_write_csrf "PUT" "/job-applications/1" "200,302,422,403,404,405,419"  # job-application.update
curl_write_csrf "PATCH" "/job-applications/1" "200,302,422,403,404,405,419"  # job-application.update

# ── job-categories ──────────────────────────────────
curl_write_csrf "PUT" "/job-categories/1" "200,302,422,403,404,405,419"  # job-category.update
curl_write_csrf "PATCH" "/job-categories/1" "200,302,422,403,404,405,419"  # job-category.update

# ── job-stages ──────────────────────────────────
curl_write_csrf "PUT" "/job-stages/1" "200,302,422,403,404,405,419"  # job-stage.update
curl_write_csrf "PATCH" "/job-stages/1" "200,302,422,403,404,405,419"  # job-stage.update

# ── jobs ──────────────────────────────────
curl_write_csrf "PUT" "/jobs/1" "200,302,422,403,404,405,419"  # jobs.update
curl_write_csrf "PATCH" "/jobs/1" "200,302,422,403,404,405,419"  # jobs.update

# ── join_us ──────────────────────────────────
curl_write_csrf "PUT" "/join_us/1" "200,302,422,403,404,405,419"  # join_us.update
curl_write_csrf "PATCH" "/join_us/1" "200,302,422,403,404,405,419"  # join_us.update

# ── journal_entries ──────────────────────────────────
curl_write_csrf "PUT" "/journal_entries/1" "200,302,422,403,404,405,419"  # journal_entries.update
curl_write_csrf "PATCH" "/journal_entries/1" "200,302,422,403,404,405,419"  # journal_entries.update

# ── labels ──────────────────────────────────
curl_write_csrf "PUT" "/labels/1" "200,302,422,403,404,405,419"  # labels.update
curl_write_csrf "PATCH" "/labels/1" "200,302,422,403,404,405,419"  # labels.update

# ── landingpage ──────────────────────────────────
curl_write_csrf "PUT" "/landingpage/1" "200,302,422,403,404,405,419"  # landingpage.update
curl_write_csrf "PATCH" "/landingpage/1" "200,302,422,403,404,405,419"  # landingpage.update

# ── lead_stages ──────────────────────────────────
curl_write_csrf "PUT" "/lead_stages/1" "200,302,422,403,404,405,419"  # lead_stages.update
curl_write_csrf "PATCH" "/lead_stages/1" "200,302,422,403,404,405,419"  # lead_stages.update

# ── leads ──────────────────────────────────
curl_write_csrf "PUT" "/leads/1/calls/1" "200,302,422,403,404,405,419"  # leads.calls.update
curl_write_csrf "PUT" "/leads/1/products" "200,302,422,403,404,405,419"  # leads.products.update
curl_write_csrf "PUT" "/leads/1/sources" "200,302,422,403,404,405,419"  # leads.sources.update
curl_write_csrf "PUT" "/leads/1/users" "200,302,422,403,404,405,419"  # leads.users.update
curl_write_csrf "PUT" "/leads/1" "200,302,422,403,404,405,419"  # leads.update
curl_write_csrf "PATCH" "/leads/1" "200,302,422,403,404,405,419"  # leads.update

# ── leave_types ──────────────────────────────────
curl_write_csrf "PUT" "/leave_types/1" "200,302,422,403,404,405,419"  # leave_types.update
curl_write_csrf "PATCH" "/leave_types/1" "200,302,422,403,404,405,419"  # leave_types.update

# ── leaves ──────────────────────────────────
curl_write_csrf "PUT" "/leaves/1" "200,302,422,403,404,405,419"  # leave.update
curl_write_csrf "PATCH" "/leaves/1" "200,302,422,403,404,405,419"  # leave.update

# ── loan_options ──────────────────────────────────
curl_write_csrf "PUT" "/loan_options/1" "200,302,422,403,404,405,419"  # loan_options.update
curl_write_csrf "PATCH" "/loan_options/1" "200,302,422,403,404,405,419"  # loan_options.update

# ── loans ──────────────────────────────────
curl_write_csrf "PUT" "/loans/1" "200,302,422,403,404,405,419"  # loans.update
curl_write_csrf "PATCH" "/loans/1" "200,302,422,403,404,405,419"  # loans.update

# ── meetings ──────────────────────────────────
curl_write_csrf "PUT" "/meetings/get_meeting_data" "200,302,422,403,404,405,419"  # meetings.get_meeting_data
curl_write_csrf "PUT" "/meetings/1" "200,302,422,403,404,405,419"  # meetings.update
curl_write_csrf "PATCH" "/meetings/get_meeting_data" "200,302,422,403,404,405,419"  # meetings.get_meeting_data
curl_write_csrf "PATCH" "/meetings/1" "200,302,422,403,404,405,419"  # meetings.update

# ── notification_templates ──────────────────────────────────
curl_write_csrf "PUT" "/notification_templates/1" "200,302,422,403,404,405,419"  # notification_templates.update
curl_write_csrf "PATCH" "/notification_templates/1" "200,302,422,403,404,405,419"  # notification_templates.update

# ── other_payments ──────────────────────────────────
curl_write_csrf "PUT" "/other_payments/1" "200,302,422,403,404,405,419"  # other_payments.update
curl_write_csrf "PATCH" "/other_payments/1" "200,302,422,403,404,405,419"  # other_payments.update

# ── overtimes ──────────────────────────────────
curl_write_csrf "PUT" "/overtimes/1" "200,302,422,403,404,405,419"  # overtimes.update
curl_write_csrf "PATCH" "/overtimes/1" "200,302,422,403,404,405,419"  # overtimes.update

# ── payments ──────────────────────────────────
curl_write_csrf "PUT" "/payments/benefits/callback" "200,302,422,403,404,405,419"  # benefit.callback
curl_write_csrf "PUT" "/payments/benefits/initiate" "200,302,422,403,404,405,419"  # plans.pay.with.benefit
curl_write_csrf "PUT" "/payments/1" "200,302,422,403,404,405,419"  # payments.update
curl_write_csrf "PATCH" "/payments/benefits/callback" "200,302,422,403,404,405,419"  # benefit.callback
curl_write_csrf "PATCH" "/payments/benefits/initiate" "200,302,422,403,404,405,419"  # plans.pay.with.benefit
curl_write_csrf "PATCH" "/payments/1" "200,302,422,403,404,405,419"  # payments.update

# ── payslips ──────────────────────────────────
curl_write_csrf "PUT" "/payslips/1" "200,302,422,403,404,405,419"  # payslips.update
curl_write_csrf "PATCH" "/payslips/1" "200,302,422,403,404,405,419"  # payslips.update

# ── performance_types ──────────────────────────────────
curl_write_csrf "PUT" "/performance_types/1" "200,302,422,403,404,405,419"  # performance_types.update
curl_write_csrf "PATCH" "/performance_types/1" "200,302,422,403,404,405,419"  # performance_types.update

# ── permissions ──────────────────────────────────
curl_write_csrf "PUT" "/permissions/1" "200,302,422,403,404,405,419"  # permissions.update
curl_write_csrf "PATCH" "/permissions/1" "200,302,422,403,404,405,419"  # permissions.update

# ── pipelines ──────────────────────────────────
curl_write_csrf "PUT" "/pipelines/1" "200,302,422,403,404,405,419"  # pipelines.update
curl_write_csrf "PATCH" "/pipelines/1" "200,302,422,403,404,405,419"  # pipelines.update

# ── plans ──────────────────────────────────
curl_write_csrf "PUT" "/plans/1" "200,302,422,403,404,405,419"  # plans.update
curl_write_csrf "PATCH" "/plans/1" "200,302,422,403,404,405,419"  # plans.update

# ── pos ──────────────────────────────────
curl_write_csrf "PUT" "/pos/1" "200,302,422,403,404,405,419"  # pos.update
curl_write_csrf "PATCH" "/pos/1" "200,302,422,403,404,405,419"  # pos.update

# ── pos-receipt ──────────────────────────────────
curl_write_csrf "PUT" "/pos-receipt" "200,302,422,403,404,405,419"  # pos.receipt
curl_write_csrf "PATCH" "/pos-receipt" "200,302,422,403,404,405,419"  # pos.receipt

# ── pricing_plans ──────────────────────────────────
curl_write_csrf "PUT" "/pricing_plans/1" "200,302,422,403,404,405,419"  # pricing_plans.update
curl_write_csrf "PATCH" "/pricing_plans/1" "200,302,422,403,404,405,419"  # pricing_plans.update

# ── product_service_categories ──────────────────────────────────
curl_write_csrf "PUT" "/product_service_categories/1" "200,302,422,403,404,405,419"  # product_service_categories.update
curl_write_csrf "PATCH" "/product_service_categories/1" "200,302,422,403,404,405,419"  # product_service_categories.update

# ── product_service_units ──────────────────────────────────
curl_write_csrf "PUT" "/product_service_units/1" "200,302,422,403,404,405,419"  # product_service_units.update
curl_write_csrf "PATCH" "/product_service_units/1" "200,302,422,403,404,405,419"  # product_service_units.update

# ── product_services ──────────────────────────────────
curl_write_csrf "PUT" "/product_services/1" "200,302,422,403,404,405,419"  # product_services.update
curl_write_csrf "PATCH" "/product_services/1" "200,302,422,403,404,405,419"  # product_services.update

# ── product_stocks ──────────────────────────────────
curl_write_csrf "PUT" "/product_stocks/1" "200,302,422,403,404,405,419"  # product_stocks.update
curl_write_csrf "PATCH" "/product_stocks/1" "200,302,422,403,404,405,419"  # product_stocks.update

# ── project_reports ──────────────────────────────────
curl_write_csrf "PUT" "/project_reports/1" "200,302,422,403,404,405,419"  # project_reports.update
curl_write_csrf "PATCH" "/project_reports/1" "200,302,422,403,404,405,419"  # project_reports.update

# ── project_stages ──────────────────────────────────
curl_write_csrf "PUT" "/project_stages/1" "200,302,422,403,404,405,419"  # project_stages.update
curl_write_csrf "PATCH" "/project_stages/1" "200,302,422,403,404,405,419"  # project_stages.update

# ── project_task_stages ──────────────────────────────────
curl_write_csrf "PUT" "/project_task_stages/1" "200,302,422,403,404,405,419"  # project_task_stages.update
curl_write_csrf "PATCH" "/project_task_stages/1" "200,302,422,403,404,405,419"  # project_task_stages.update

# ── projects ──────────────────────────────────
curl_write_csrf "PUT" "/projects/copies/links/1" "200,302,422,403,404,405,419"  # projects.copy.link
curl_write_csrf "PUT" "/projects/links/1/1" "200,302,422,403,404,405,419"  # projects.link
curl_write_csrf "PUT" "/projects/1/setting-create" "200,302,422,403,404,405,419"  # projects.copy_link.setting.create
curl_write_csrf "PUT" "/projects/1" "200,302,422,403,404,405,419"  # projects.update
curl_write_csrf "PATCH" "/projects/copies/links/1" "200,302,422,403,404,405,419"  # projects.copy.link
curl_write_csrf "PATCH" "/projects/links/1/1" "200,302,422,403,404,405,419"  # projects.link
curl_write_csrf "PATCH" "/projects/1/setting-create" "200,302,422,403,404,405,419"  # projects.copy_link.setting.create
curl_write_csrf "PATCH" "/projects/1/tasks/order" "200,302,422,403,404,405,419"  # projects.tasks.update.order
curl_write_csrf "PATCH" "/projects/1" "200,302,422,403,404,405,419"  # projects.update

# ── projects.timesheets ──────────────────────────────────
curl_write_csrf "PUT" "/projects.timesheets/projects/updates/1" "200,302,422,403,404,405,419"  # projects.timesheets.update
curl_write_csrf "PATCH" "/projects.timesheets/projects/updates/1" "200,302,422,403,404,405,419"  # projects.timesheets.update

# ── promotions ──────────────────────────────────
curl_write_csrf "PUT" "/promotions/1" "200,302,422,403,404,405,419"  # promotions.update
curl_write_csrf "PATCH" "/promotions/1" "200,302,422,403,404,405,419"  # promotions.update

# ── proposals ──────────────────────────────────
curl_write_csrf "PUT" "/proposals/1" "200,302,422,403,404,405,419"  # proposal.update
curl_write_csrf "PATCH" "/proposals/1" "200,302,422,403,404,405,419"  # proposal.update

# ── purchases ──────────────────────────────────
curl_write_csrf "PUT" "/purchases/1" "200,302,422,403,404,405,419"  # purchases.update
curl_write_csrf "PATCH" "/purchases/1" "200,302,422,403,404,405,419"  # purchases.update

# ── remove-user-from-projects ──────────────────────────────────
curl_write_csrf "PATCH" "/remove-user-from-projects/1/1" "200,302,422,403,404,405,419"  # remove.user.from.project

# ── reports ──────────────────────────────────
curl_write_csrf "PUT" "/reports/pos" "200,302,422,403,404,405,419"  # pos.report
curl_write_csrf "PATCH" "/reports/pos" "200,302,422,403,404,405,419"  # pos.report

# ── resignations ──────────────────────────────────
curl_write_csrf "PUT" "/resignations/1" "200,302,422,403,404,405,419"  # resignations.update
curl_write_csrf "PATCH" "/resignations/1" "200,302,422,403,404,405,419"  # resignations.update

# ── revenues ──────────────────────────────────
curl_write_csrf "PUT" "/revenues/1" "200,302,422,403,404,405,419"  # revenues.update
curl_write_csrf "PATCH" "/revenues/1" "200,302,422,403,404,405,419"  # revenues.update

# ── roles ──────────────────────────────────
curl_write_csrf "PUT" "/roles/1" "200,302,422,403,404,405,419"  # roles.update
curl_write_csrf "PATCH" "/roles/1" "200,302,422,403,404,405,419"  # roles.update

# ── saturation_deductions ──────────────────────────────────
curl_write_csrf "PUT" "/saturation_deductions/1" "200,302,422,403,404,405,419"  # saturation_deductions.update
curl_write_csrf "PATCH" "/saturation_deductions/1" "200,302,422,403,404,405,419"  # saturation_deductions.update

# ── screenshots ──────────────────────────────────
curl_write_csrf "PUT" "/screenshots/1" "200,302,422,403,404,405,419"  # screenshots.update
curl_write_csrf "PATCH" "/screenshots/1" "200,302,422,403,404,405,419"  # screenshots.update

# ── sources ──────────────────────────────────
curl_write_csrf "PUT" "/sources/1" "200,302,422,403,404,405,419"  # sources.update
curl_write_csrf "PATCH" "/sources/1" "200,302,422,403,404,405,419"  # sources.update

# ── stages ──────────────────────────────────
curl_write_csrf "PUT" "/stages/1" "200,302,422,403,404,405,419"  # stages.update
curl_write_csrf "PATCH" "/stages/1" "200,302,422,403,404,405,419"  # stages.update

# ── store-language ──────────────────────────────────
curl_write_csrf "PUT" "/store-language" "200,302,422,403,404,405,419"  # languages.store
curl_write_csrf "PATCH" "/store-language" "200,302,422,403,404,405,419"  # languages.store

# ── supports ──────────────────────────────────
curl_write_csrf "PUT" "/supports/1" "200,302,422,403,404,405,419"  # supports.update
curl_write_csrf "PATCH" "/supports/1" "200,302,422,403,404,405,419"  # supports.update

# ── systems ──────────────────────────────────
curl_write_csrf "PUT" "/systems/1" "200,302,422,403,404,405,419"  # systems.update
curl_write_csrf "PATCH" "/systems/1" "200,302,422,403,404,405,419"  # systems.update

# ── taxes ──────────────────────────────────
curl_write_csrf "PUT" "/taxes/1" "200,302,422,403,404,405,419"  # taxes.update
curl_write_csrf "PATCH" "/taxes/1" "200,302,422,403,404,405,419"  # taxes.update

# ── terminations ──────────────────────────────────
curl_write_csrf "PUT" "/terminations/1" "200,302,422,403,404,405,419"  # terminations.update
curl_write_csrf "PATCH" "/terminations/1" "200,302,422,403,404,405,419"  # terminations.update

# ── terminationtypes ──────────────────────────────────
curl_write_csrf "PUT" "/terminationtypes/1" "200,302,422,403,404,405,419"  # terminationtype.update
curl_write_csrf "PUT" "/terminationtypes/1" "200,302,422,403,404,405,419"  # termination_types.update
curl_write_csrf "PATCH" "/terminationtypes/1" "200,302,422,403,404,405,419"  # terminationtype.update

# ── testimonials ──────────────────────────────────
curl_write_csrf "PUT" "/testimonials/1" "200,302,422,403,404,405,419"  # testimonials.update
curl_write_csrf "PATCH" "/testimonials/1" "200,302,422,403,404,405,419"  # testimonials.update

# ── trainers ──────────────────────────────────
curl_write_csrf "PUT" "/trainers/1" "200,302,422,403,404,405,419"  # trainers.update
curl_write_csrf "PATCH" "/trainers/1" "200,302,422,403,404,405,419"  # trainers.update

# ── training_types ──────────────────────────────────
curl_write_csrf "PUT" "/training_types/1" "200,302,422,403,404,405,419"  # training_types.update
curl_write_csrf "PATCH" "/training_types/1" "200,302,422,403,404,405,419"  # training_types.update

# ── trainings ──────────────────────────────────
curl_write_csrf "PUT" "/trainings/1" "200,302,422,403,404,405,419"  # trainings.update
curl_write_csrf "PATCH" "/trainings/1" "200,302,422,403,404,405,419"  # trainings.update

# ── transfers ──────────────────────────────────
curl_write_csrf "PUT" "/transfers/1" "200,302,422,403,404,405,419"  # transfers.update
curl_write_csrf "PATCH" "/transfers/1" "200,302,422,403,404,405,419"  # transfers.update

# ── travels ──────────────────────────────────
curl_write_csrf "PUT" "/travels/1" "200,302,422,403,404,405,419"  # travels.update
curl_write_csrf "PATCH" "/travels/1" "200,302,422,403,404,405,419"  # travels.update

# ── update-cart ──────────────────────────────────
curl_write_csrf "PATCH" "/update-cart" "200,302,422,403,404,405,419"

# ── update-task-priority-color ──────────────────────────────────
curl_write_csrf "PATCH" "/update-task-priority-color" "200,302,422,403,404,405,419"  # projects.tasks.update.priority.color

# ── user ──────────────────────────────────
curl_write_csrf "PUT" "/user/password" "200,302,422,403,404,405,419"  # user-password.update

# ── user-reset-passwords ──────────────────────────────────
curl_write_csrf "PUT" "/user-reset-passwords/1" "200,302,422,403,404,405,419"  # users.reset
curl_write_csrf "PATCH" "/user-reset-passwords/1" "200,302,422,403,404,405,419"  # users.reset

# ── users ──────────────────────────────────
curl_write_csrf "PUT" "/users/profile-information" "200,302,422,403,404,405,419"  # user-profile-information.update
curl_write_csrf "PUT" "/users/1" "200,302,422,403,404,405,419"  # users.update
curl_write_csrf "PATCH" "/users/1" "200,302,422,403,404,405,419"  # users.update

# ── vendors ──────────────────────────────────
curl_write_csrf "PUT" "/vendors/1" "200,302,422,403,404,405,419"  # vendors.update
curl_write_csrf "PATCH" "/vendors/1" "200,302,422,403,404,405,419"  # vendors.update

# ── warehouse_transfers ──────────────────────────────────
curl_write_csrf "PUT" "/warehouse_transfers/1" "200,302,422,403,404,405,419"  # warehouse_transfers.update
curl_write_csrf "PATCH" "/warehouse_transfers/1" "200,302,422,403,404,405,419"  # warehouse_transfers.update

# ── warehouses ──────────────────────────────────
curl_write_csrf "PUT" "/warehouses/1" "200,302,422,403,404,405,419"  # warehouses.update
curl_write_csrf "PATCH" "/warehouses/1" "200,302,422,403,404,405,419"  # warehouses.update

# ── warnings ──────────────────────────────────
curl_write_csrf "PUT" "/warnings/1" "200,302,422,403,404,405,419"  # warnings.update
curl_write_csrf "PATCH" "/warnings/1" "200,302,422,403,404,405,419"  # warnings.update

# ── webhook-settings ──────────────────────────────────
curl_write_csrf "PUT" "/webhook-settings" "200,302,422,403,404,405,419"  # webhooks.settings
curl_write_csrf "PATCH" "/webhook-settings" "200,302,422,403,404,405,419"  # webhooks.settings

# ── zoom-meetings ──────────────────────────────────
curl_write_csrf "PUT" "/zoom-meetings/get_zoom_meeting_data" "200,302,422,403,404,405,419"  # zoom_meetings.get_zoom_meeting_data
curl_write_csrf "PATCH" "/zoom-meetings/get_zoom_meeting_data" "200,302,422,403,404,405,419"  # zoom_meetings.get_zoom_meeting_data

# ── zoom_meetings ──────────────────────────────────
curl_write_csrf "PUT" "/zoom_meetings/projects/selects/1" "200,302,422,403,404,405,419"  # zoom_meetings.projects.select
curl_write_csrf "PUT" "/zoom_meetings/1" "200,302,422,403,404,405,419"  # zoom_meetings.update
curl_write_csrf "PATCH" "/zoom_meetings/projects/selects/1" "200,302,422,403,404,405,419"  # zoom_meetings.projects.select
curl_write_csrf "PATCH" "/zoom_meetings/1" "200,302,422,403,404,405,419"  # zoom_meetings.update

log_info "PUT/PATCH route tests complete."
summary
