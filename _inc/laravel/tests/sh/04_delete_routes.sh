#!/usr/bin/env bash
# ─────────────────────────────────────────────────────
# 04_delete_routes.sh  –  DELETE route coverage (188 routes)
# Auto-generated — do not edit by hand.
# ─────────────────────────────────────────────────────
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=_common.sh
source "$SCRIPT_DIR/_common.sh"

log_info "=== DELETE ROUTE TESTS (188 routes) ==="

# Ensure authenticated session
do_login

# NOTE: DELETE tests use IDs that may not exist, so 404 is acceptable.
# We only verify the route itself responds (not 500).

# ── _debugbars ──────────────────────────────────
curl_post_csrf "/_debugbars/caches/1/1" "200,302,404,403,405,419,422" "_method=DELETE"  # debugbar.cache.delete

# ── account_assets ──────────────────────────────────
curl_post_csrf "/account_assets/1" "200,302,404,403,405,419,422" "_method=DELETE"  # account_assets.destroy

# ── allowance_options ──────────────────────────────────
curl_post_csrf "/allowance_options/1" "200,302,404,403,405,419,422" "_method=DELETE"  # allowance_options.destroy

# ── allowances ──────────────────────────────────
curl_post_csrf "/allowances/1" "200,302,404,403,405,419,422" "_method=DELETE"  # allowances.destroy

# ── announcements ──────────────────────────────────
curl_post_csrf "/announcements/1" "200,302,404,403,405,419,422" "_method=DELETE"  # announcement.destroy

# ── appraisals ──────────────────────────────────
curl_post_csrf "/appraisals/1" "200,302,404,403,405,419,422" "_method=DELETE"  # appraisals.destroy

# ── award_types ──────────────────────────────────
curl_post_csrf "/award_types/1" "200,302,404,403,405,419,422" "_method=DELETE"  # award_types.destroy

# ── awards ──────────────────────────────────
curl_post_csrf "/awards/1" "200,302,404,403,405,419,422" "_method=DELETE"  # awards.destroy

# ── bank_accounts ──────────────────────────────────
curl_post_csrf "/bank_accounts/1" "200,302,404,403,405,419,422" "_method=DELETE"  # bank_accounts.destroy

# ── bank_transfers ──────────────────────────────────
curl_post_csrf "/bank_transfers/1" "200,302,404,403,405,419,422" "_method=DELETE"  # bank_transfers.destroy

# ── bills ──────────────────────────────────
curl_post_csrf "/bills/1" "200,302,404,403,405,419,422" "_method=DELETE"  # bills.destroy

# ── bills{id} ──────────────────────────────────
curl_post_csrf "/bills1/debit_notes/deletes/1" "200,302,404,403,405,419,422" "_method=DELETE"  # bills.delete.debit.note

# ── branches ──────────────────────────────────
curl_post_csrf "/branches/1" "200,302,404,403,405,419,422" "_method=DELETE"  # branches.destroy

# ── budgets ──────────────────────────────────
curl_post_csrf "/budgets/1" "200,302,404,403,405,419,422" "_method=DELETE"  # budgets.destroy

# ── bug_statuses ──────────────────────────────────
curl_post_csrf "/bug_statuses/1" "200,302,404,403,405,419,422" "_method=DELETE"  # bug_status.destroy

# ── cashfrees ──────────────────────────────────
curl_post_csrf "/cashfrees/payments/success" "200,302,404,403,405,419,422" "_method=DELETE"  # cashfree.payment.success

# ── chart_of_accounts ──────────────────────────────────
curl_post_csrf "/chart_of_accounts/1" "200,302,404,403,405,419,422" "_method=DELETE"  # chart_of_accounts.destroy

# ── client-reset-passwords ──────────────────────────────────
curl_post_csrf "/client-reset-passwords/1" "200,302,404,403,405,419,422" "_method=DELETE"  # clients.reset

# ── clients ──────────────────────────────────
curl_post_csrf "/clients/1" "200,302,404,403,405,419,422" "_method=DELETE"  # clients.destroy

# ── commissions ──────────────────────────────────
curl_post_csrf "/commissions/1" "200,302,404,403,405,419,422" "_method=DELETE"  # commissions.destroy

# ── company_policies ──────────────────────────────────
curl_post_csrf "/company_policies/1" "200,302,404,403,405,419,422" "_method=DELETE"  # company_policies.destroy

# ── competencies ──────────────────────────────────
curl_post_csrf "/competencies/1" "200,302,404,403,405,419,422" "_method=DELETE"  # competencies.destroy

# ── complaints ──────────────────────────────────
curl_post_csrf "/complaints/1" "200,302,404,403,405,419,422" "_method=DELETE"  # complaints.destroy

# ── contract_types ──────────────────────────────────
curl_post_csrf "/contract_types/1" "200,302,404,403,405,419,422" "_method=DELETE"  # contract_types.destroy

# ── contracts ──────────────────────────────────
curl_post_csrf "/contracts/clients/selects/1" "200,302,404,403,405,419,422" "_method=DELETE"  # contracts.clients.select
curl_post_csrf "/contracts/1" "200,302,404,403,405,419,422" "_method=DELETE"  # contracts.destroy
curl_post_csrf "/contracts/1/comment" "200,302,404,403,405,419,422" "_method=DELETE"  # contracts.comment.destroy
curl_post_csrf "/contracts/1/files/deletes/1" "200,302,404,403,405,419,422" "_method=DELETE"  # contracts.file.delete
curl_post_csrf "/contracts/1/notes" "200,302,404,403,405,419,422" "_method=DELETE"  # contracts.note.destroy

# ── cookie-consent ──────────────────────────────────
curl_post_csrf "/cookie-consent" "200,302,404,403,405,419,422" "_method=DELETE"  # cookie-consent

# ── coupons ──────────────────────────────────
curl_post_csrf "/coupons/1" "200,302,404,403,405,419,422" "_method=DELETE"  # coupons.destroy

# ── custom-questions ──────────────────────────────────
curl_post_csrf "/custom-questions/1" "200,302,404,403,405,419,422" "_method=DELETE"  # custom-question.destroy
curl_post_csrf "/custom-questions/1" "200,302,404,403,405,419,422" "_method=DELETE"  # custom_questions.destroy

# ── custom_fields ──────────────────────────────────
curl_post_csrf "/custom_fields/1" "200,302,404,403,405,419,422" "_method=DELETE"  # custom_fields.destroy

# ── custom_pages ──────────────────────────────────
curl_post_csrf "/custom_pages/1" "200,302,404,403,405,419,422" "_method=DELETE"  # custom_pages.destroy

# ── customers ──────────────────────────────────
curl_post_csrf "/customers/1" "200,302,404,403,405,419,422" "_method=DELETE"  # customers.destroy

# ── deals ──────────────────────────────────
curl_post_csrf "/deals/1" "200,302,404,403,405,419,422" "_method=DELETE"  # deals.destroy
curl_post_csrf "/deals/1/calls/1" "200,302,404,403,405,419,422" "_method=DELETE"  # deals.calls.destroy
curl_post_csrf "/deals/1/clients/1" "200,302,404,403,405,419,422" "_method=DELETE"  # deals.clients.destroy
curl_post_csrf "/deals/1/files/deletes/1" "200,302,404,403,405,419,422" "_method=DELETE"  # deals.file.delete
curl_post_csrf "/deals/1/products/1" "200,302,404,403,405,419,422" "_method=DELETE"  # deals.products.destroy
curl_post_csrf "/deals/1/sources/1" "200,302,404,403,405,419,422" "_method=DELETE"  # deals.sources.destroy
curl_post_csrf "/deals/1/tasks/1" "200,302,404,403,405,419,422" "_method=DELETE"  # deals.tasks.destroy
curl_post_csrf "/deals/1/users/1" "200,302,404,403,405,419,422" "_method=DELETE"  # deals.users.destroy

# ── deduction_options ──────────────────────────────────
curl_post_csrf "/deduction_options/1" "200,302,404,403,405,419,422" "_method=DELETE"  # deduction_options.destroy

# ── departments ──────────────────────────────────
curl_post_csrf "/departments/1" "200,302,404,403,405,419,422" "_method=DELETE"  # departments.destroy

# ── designations ──────────────────────────────────
curl_post_csrf "/designations/1" "200,302,404,403,405,419,422" "_method=DELETE"  # designations.destroy

# ── discover ──────────────────────────────────
curl_post_csrf "/discover/1" "200,302,404,403,405,419,422" "_method=DELETE"  # discover.destroy

# ── document_uploads ──────────────────────────────────
curl_post_csrf "/document_uploads/1" "200,302,404,403,405,419,422" "_method=DELETE"  # document_uploads.destroy

# ── documents ──────────────────────────────────
curl_post_csrf "/documents/1" "200,302,404,403,405,419,422" "_method=DELETE"  # documents.destroy

# ── edit-profile ──────────────────────────────────
curl_post_csrf "/edit-profile" "200,302,404,403,405,419,422" "_method=DELETE"  # users.account.update

# ── email_template_store ──────────────────────────────────
curl_post_csrf "/email_template_store" "200,302,404,403,405,419,422" "_method=DELETE"  # emails.status.language

# ── email_template_stores ──────────────────────────────────
curl_post_csrf "/email_template_stores/1" "200,302,404,403,405,419,422" "_method=DELETE"  # emails.store.language

# ── email_templates ──────────────────────────────────
curl_post_csrf "/email_templates/1" "200,302,404,403,405,419,422" "_method=DELETE"  # email_template.destroy

# ── employee_attendances ──────────────────────────────────
curl_post_csrf "/employee_attendances/1" "200,302,404,403,405,419,422" "_method=DELETE"  # employee_attendances.destroy

# ── employees ──────────────────────────────────
curl_post_csrf "/employees/1" "200,302,404,403,405,419,422" "_method=DELETE"  # employees.destroy

# ── events ──────────────────────────────────
curl_post_csrf "/events/get_dashboard_event_data" "200,302,404,403,405,419,422" "_method=DELETE"  # events.get_dashboard_event_data
curl_post_csrf "/events/get_event_data" "200,302,404,403,405,419,422" "_method=DELETE"  # events.get_event_data
curl_post_csrf "/events/1" "200,302,404,403,405,419,422" "_method=DELETE"  # events.destroy

# ── expenses ──────────────────────────────────
curl_post_csrf "/expenses/customer" "200,302,404,403,405,419,422" "_method=DELETE"  # expenses.customer
curl_post_csrf "/expenses/1" "200,302,404,403,405,419,422" "_method=DELETE"  # expenses.destroy

# ── faqs ──────────────────────────────────
curl_post_csrf "/faqs/1" "200,302,404,403,405,419,422" "_method=DELETE"  # faqs.destroy

# ── features ──────────────────────────────────
curl_post_csrf "/features/1" "200,302,404,403,405,419,422" "_method=DELETE"  # features.destroy

# ── form_builders ──────────────────────────────────
curl_post_csrf "/form_builders/1" "200,302,404,403,405,419,422" "_method=DELETE"  # form_builders.destroy
curl_post_csrf "/form_builders/1/fields/1" "200,302,404,403,405,419,422" "_method=DELETE"  # forms.fields.destroy

# ── goal_trackings ──────────────────────────────────
curl_post_csrf "/goal_trackings/1" "200,302,404,403,405,419,422" "_method=DELETE"  # goal_trackings.destroy

# ── goal_types ──────────────────────────────────
curl_post_csrf "/goal_types/1" "200,302,404,403,405,419,422" "_method=DELETE"  # goal_types.destroy

# ── goals ──────────────────────────────────
curl_post_csrf "/goals/1" "200,302,404,403,405,419,422" "_method=DELETE"  # goals.destroy

# ── holidays ──────────────────────────────────
curl_post_csrf "/holidays/data" "200,302,404,403,405,419,422" "_method=DELETE"  # holidays.get_holiday_data
curl_post_csrf "/holidays/1" "200,302,404,403,405,419,422" "_method=DELETE"  # holidays.destroy

# ── home_section ──────────────────────────────────
curl_post_csrf "/home_section/1" "200,302,404,403,405,419,422" "_method=DELETE"  # home_section.destroy

# ── indicators ──────────────────────────────────
curl_post_csrf "/indicators/1" "200,302,404,403,405,419,422" "_method=DELETE"  # indicators.destroy

# ── interview-schedules ──────────────────────────────────
curl_post_csrf "/interview-schedules/1" "200,302,404,403,405,419,422" "_method=DELETE"  # interview-schedule.destroy

# ── interview_schedules ──────────────────────────────────
curl_post_csrf "/interview_schedules/data" "200,302,404,403,405,419,422" "_method=DELETE"  # interview_schedules.get_interview_data

# ── invoices ──────────────────────────────────
curl_post_csrf "/invoices/benefits/1/1" "200,302,404,403,405,419,422" "_method=DELETE"  # invoices.benefit.callback
curl_post_csrf "/invoices/with-benefit" "200,302,404,403,405,419,422" "_method=DELETE"  # invoices.benefit.initiate
curl_post_csrf "/invoices/with-cashfrees/status" "200,302,404,403,405,419,422" "_method=DELETE"  # invoices.cashfree.payment.success
curl_post_csrf "/invoices/1/credit-notes/deletes/1" "200,302,404,403,405,419,422" "_method=DELETE"  # invoices.delete.credit.note
curl_post_csrf "/invoices/1" "200,302,404,403,405,419,422" "_method=DELETE"  # invoices.destroy

# ── job-applications ──────────────────────────────────
curl_post_csrf "/job-applications/1/archive" "200,302,404,403,405,419,422" "_method=DELETE"  # jobs.application.archive
curl_post_csrf "/job-applications/1/notes/destroy" "200,302,404,403,405,419,422" "_method=DELETE"  # jobs.application.note.destroy
curl_post_csrf "/job-applications/1" "200,302,404,403,405,419,422" "_method=DELETE"  # job-application.destroy

# ── job-categories ──────────────────────────────────
curl_post_csrf "/job-categories/1" "200,302,404,403,405,419,422" "_method=DELETE"  # job-category.destroy

# ── job-stages ──────────────────────────────────
curl_post_csrf "/job-stages/1" "200,302,404,403,405,419,422" "_method=DELETE"  # job-stage.destroy

# ── jobs ──────────────────────────────────
curl_post_csrf "/jobs/1" "200,302,404,403,405,419,422" "_method=DELETE"  # jobs.destroy

# ── jobs_onboards ──────────────────────────────────
curl_post_csrf "/jobs_onboards/deletes/1" "200,302,404,403,405,419,422" "_method=DELETE"  # jobs.on.board.delete

# ── join_us ──────────────────────────────────
curl_post_csrf "/join_us/1" "200,302,404,403,405,419,422" "_method=DELETE"  # join_us.destroy

# ── journal_entries ──────────────────────────────────
curl_post_csrf "/journal_entries/journals/destroys/1" "200,302,404,403,405,419,422" "_method=DELETE"  # journals.destroy
curl_post_csrf "/journal_entries/1" "200,302,404,403,405,419,422" "_method=DELETE"  # journal_entries.destroy

# ── labels ──────────────────────────────────
curl_post_csrf "/labels/1" "200,302,404,403,405,419,422" "_method=DELETE"  # labels.destroy

# ── landingpage ──────────────────────────────────
curl_post_csrf "/landingpage/1" "200,302,404,403,405,419,422" "_method=DELETE"  # landingpage.destroy

# ── langs ──────────────────────────────────
curl_post_csrf "/langs/1" "200,302,404,403,405,419,422" "_method=DELETE"  # languages.destroy

# ── lead_stages ──────────────────────────────────
curl_post_csrf "/lead_stages/1" "200,302,404,403,405,419,422" "_method=DELETE"  # lead_stages.destroy

# ── leads ──────────────────────────────────
curl_post_csrf "/leads/1/calls/1" "200,302,404,403,405,419,422" "_method=DELETE"  # leads.calls.destroy
curl_post_csrf "/leads/1/files/deletes/1" "200,302,404,403,405,419,422" "_method=DELETE"  # leads.file.delete
curl_post_csrf "/leads/1/products/1" "200,302,404,403,405,419,422" "_method=DELETE"  # leads.products.destroy
curl_post_csrf "/leads/1/sources/1" "200,302,404,403,405,419,422" "_method=DELETE"  # leads.sources.destroy
curl_post_csrf "/leads/1/users/1" "200,302,404,403,405,419,422" "_method=DELETE"  # leads.users.destroy
curl_post_csrf "/leads/1" "200,302,404,403,405,419,422" "_method=DELETE"  # leads.destroy

# ── leave_types ──────────────────────────────────
curl_post_csrf "/leave_types/1" "200,302,404,403,405,419,422" "_method=DELETE"  # leave_types.destroy

# ── leaves ──────────────────────────────────
curl_post_csrf "/leaves/1" "200,302,404,403,405,419,422" "_method=DELETE"  # leave.destroy

# ── loan_options ──────────────────────────────────
curl_post_csrf "/loan_options/1" "200,302,404,403,405,419,422" "_method=DELETE"  # loan_options.destroy

# ── loans ──────────────────────────────────
curl_post_csrf "/loans/1" "200,302,404,403,405,419,422" "_method=DELETE"  # loans.destroy

# ── meetings ──────────────────────────────────
curl_post_csrf "/meetings/get_meeting_data" "200,302,404,403,405,419,422" "_method=DELETE"  # meetings.get_meeting_data
curl_post_csrf "/meetings/1" "200,302,404,403,405,419,422" "_method=DELETE"  # meetings.destroy

# ── notification_templates ──────────────────────────────────
curl_post_csrf "/notification_templates/1" "200,302,404,403,405,419,422" "_method=DELETE"  # notification_templates.destroy

# ── orders ──────────────────────────────────
curl_post_csrf "/orders/1" "200,302,404,403,405,419,422" "_method=DELETE"  # orders.destroy

# ── other_payments ──────────────────────────────────
curl_post_csrf "/other_payments/1" "200,302,404,403,405,419,422" "_method=DELETE"  # other_payments.destroy

# ── overtimes ──────────────────────────────────
curl_post_csrf "/overtimes/1" "200,302,404,403,405,419,422" "_method=DELETE"  # overtimes.destroy

# ── payments ──────────────────────────────────
curl_post_csrf "/payments/benefits/callback" "200,302,404,403,405,419,422" "_method=DELETE"  # benefit.callback
curl_post_csrf "/payments/benefits/initiate" "200,302,404,403,405,419,422" "_method=DELETE"  # plans.pay.with.benefit
curl_post_csrf "/payments/1" "200,302,404,403,405,419,422" "_method=DELETE"  # payments.destroy

# ── payslips ──────────────────────────────────
curl_post_csrf "/payslips/1" "200,302,404,403,405,419,422" "_method=DELETE"  # payslips.destroy

# ── performance_types ──────────────────────────────────
curl_post_csrf "/performance_types/1" "200,302,404,403,405,419,422" "_method=DELETE"  # performance_types.destroy

# ── permissions ──────────────────────────────────
curl_post_csrf "/permissions/1" "200,302,404,403,405,419,422" "_method=DELETE"  # permissions.destroy

# ── pipelines ──────────────────────────────────
curl_post_csrf "/pipelines/1" "200,302,404,403,405,419,422" "_method=DELETE"  # pipelines.destroy

# ── plans ──────────────────────────────────
curl_post_csrf "/plans/1" "200,302,404,403,405,419,422" "_method=DELETE"  # plans.destroy

# ── pos ──────────────────────────────────
curl_post_csrf "/pos/1" "200,302,404,403,405,419,422" "_method=DELETE"  # pos.destroy

# ── pos-receipt ──────────────────────────────────
curl_post_csrf "/pos-receipt" "200,302,404,403,405,419,422" "_method=DELETE"  # pos.receipt

# ── pricing_plans ──────────────────────────────────
curl_post_csrf "/pricing_plans/1" "200,302,404,403,405,419,422" "_method=DELETE"  # pricing_plans.destroy

# ── product_service_categories ──────────────────────────────────
curl_post_csrf "/product_service_categories/1" "200,302,404,403,405,419,422" "_method=DELETE"  # product_service_categories.destroy

# ── product_service_units ──────────────────────────────────
curl_post_csrf "/product_service_units/1" "200,302,404,403,405,419,422" "_method=DELETE"  # product_service_units.destroy

# ── product_services ──────────────────────────────────
curl_post_csrf "/product_services/1" "200,302,404,403,405,419,422" "_method=DELETE"  # product_services.destroy

# ── product_stocks ──────────────────────────────────
curl_post_csrf "/product_stocks/1" "200,302,404,403,405,419,422" "_method=DELETE"  # product_stocks.destroy

# ── project_reports ──────────────────────────────────
curl_post_csrf "/project_reports/1" "200,302,404,403,405,419,422" "_method=DELETE"  # project_reports.destroy

# ── project_stages ──────────────────────────────────
curl_post_csrf "/project_stages/1" "200,302,404,403,405,419,422" "_method=DELETE"  # project_stages.destroy

# ── project_task_stages ──────────────────────────────────
curl_post_csrf "/project_task_stages/1" "200,302,404,403,405,419,422" "_method=DELETE"  # project_task_stages.destroy

# ── projects ──────────────────────────────────
curl_post_csrf "/projects/bugs/comments/1" "200,302,404,403,405,419,422" "_method=DELETE"  # projects.bugs.comments.destroy
curl_post_csrf "/projects/copies/links/1" "200,302,404,403,405,419,422" "_method=DELETE"  # projects.copy.link
curl_post_csrf "/projects/links/1/1" "200,302,404,403,405,419,422" "_method=DELETE"  # projects.link
curl_post_csrf "/projects/milestones/1" "200,302,404,403,405,419,422" "_method=DELETE"  # projects.milestones.destroy
curl_post_csrf "/projects/1/expenses" "200,302,404,403,405,419,422" "_method=DELETE"  # projects.expenses.destroy
curl_post_csrf "/projects/1/bugs/1/destroy" "200,302,404,403,405,419,422" "_method=DELETE"  # projects.tasks.bugs.destroy
curl_post_csrf "/projects/1/checklists/1" "200,302,404,403,405,419,422" "_method=DELETE"  # projects.tasks.checklist.destroy
curl_post_csrf "/projects/1/comments/1/files/1" "200,302,404,403,405,419,422" "_method=DELETE"  # projects.tasks.comment.destroy.file
curl_post_csrf "/projects/1/comments/1/1" "200,302,404,403,405,419,422" "_method=DELETE"  # projects.tasks.comment.destroy
curl_post_csrf "/projects/1/setting-create" "200,302,404,403,405,419,422" "_method=DELETE"  # projects.copy_link.setting.create
curl_post_csrf "/projects/1/tasks/1" "200,302,404,403,405,419,422" "_method=DELETE"  # projects.tasks.destroy
curl_post_csrf "/projects/1/users/1" "200,302,404,403,405,419,422" "_method=DELETE"  # projects.users.destroy
curl_post_csrf "/projects/1" "200,302,404,403,405,419,422" "_method=DELETE"  # projects.destroy

# ── projects.timesheets ──────────────────────────────────
curl_post_csrf "/projects.timesheets/projects/updates/1" "200,302,404,403,405,419,422" "_method=DELETE"  # projects.timesheets.update
curl_post_csrf "/projects.timesheets/projects/1" "200,302,404,403,405,419,422" "_method=DELETE"  # projects.timesheets.destroy

# ── promotions ──────────────────────────────────
curl_post_csrf "/promotions/1" "200,302,404,403,405,419,422" "_method=DELETE"  # promotions.destroy

# ── proposals ──────────────────────────────────
curl_post_csrf "/proposals/1" "200,302,404,403,405,419,422" "_method=DELETE"  # proposal.destroy

# ── purchases ──────────────────────────────────
curl_post_csrf "/purchases/1" "200,302,404,403,405,419,422" "_method=DELETE"  # purchases.destroy

# ── remove-from-cart ──────────────────────────────────
curl_post_csrf "/remove-from-cart" "200,302,404,403,405,419,422" "_method=DELETE"

# ── reports ──────────────────────────────────
curl_post_csrf "/reports/pos" "200,302,404,403,405,419,422" "_method=DELETE"  # pos.report

# ── resignations ──────────────────────────────────
curl_post_csrf "/resignations/1" "200,302,404,403,405,419,422" "_method=DELETE"  # resignations.destroy

# ── revenues ──────────────────────────────────
curl_post_csrf "/revenues/1" "200,302,404,403,405,419,422" "_method=DELETE"  # revenues.destroy

# ── roles ──────────────────────────────────
curl_post_csrf "/roles/1" "200,302,404,403,405,419,422" "_method=DELETE"  # roles.destroy

# ── saturation_deductions ──────────────────────────────────
curl_post_csrf "/saturation_deductions/1" "200,302,404,403,405,419,422" "_method=DELETE"  # saturation_deductions.destroy

# ── screenshots ──────────────────────────────────
curl_post_csrf "/screenshots/1" "200,302,404,403,405,419,422" "_method=DELETE"  # screenshots.destroy

# ── sources ──────────────────────────────────
curl_post_csrf "/sources/1" "200,302,404,403,405,419,422" "_method=DELETE"  # sources.destroy

# ── stages ──────────────────────────────────
curl_post_csrf "/stages/1" "200,302,404,403,405,419,422" "_method=DELETE"  # stages.destroy

# ── store-language ──────────────────────────────────
curl_post_csrf "/store-language" "200,302,404,403,405,419,422" "_method=DELETE"  # languages.store

# ── supports ──────────────────────────────────
curl_post_csrf "/supports/1" "200,302,404,403,405,419,422" "_method=DELETE"  # supports.destroy

# ── systems ──────────────────────────────────
curl_post_csrf "/systems/destroys/ips/1" "200,302,404,403,405,419,422" "_method=DELETE"  # systems.ip.destroy
curl_post_csrf "/systems/1" "200,302,404,403,405,419,422" "_method=DELETE"  # systems.destroy

# ── taxes ──────────────────────────────────
curl_post_csrf "/taxes/1" "200,302,404,403,405,419,422" "_method=DELETE"  # taxes.destroy

# ── terminations ──────────────────────────────────
curl_post_csrf "/terminations/1" "200,302,404,403,405,419,422" "_method=DELETE"  # terminations.destroy

# ── terminationtypes ──────────────────────────────────
curl_post_csrf "/terminationtypes/1" "200,302,404,403,405,419,422" "_method=DELETE"  # terminationtype.destroy
curl_post_csrf "/terminationtypes/1" "200,302,404,403,405,419,422" "_method=DELETE"  # termination_types.destroy

# ── testimonials ──────────────────────────────────
curl_post_csrf "/testimonials/1" "200,302,404,403,405,419,422" "_method=DELETE"  # testimonials.destroy

# ── todos ──────────────────────────────────
curl_post_csrf "/todos/1/delete" "200,302,404,403,405,419,422" "_method=DELETE"  # todos.destroy

# ── trackers ──────────────────────────────────
curl_post_csrf "/trackers/image-remove" "200,302,404,403,405,419,422" "_method=DELETE"  # time_trackers.image.remove
curl_post_csrf "/trackers/1/destroy" "200,302,404,403,405,419,422" "_method=DELETE"  # time_trackers.destroy

# ── trainers ──────────────────────────────────
curl_post_csrf "/trainers/1" "200,302,404,403,405,419,422" "_method=DELETE"  # trainers.destroy

# ── training_types ──────────────────────────────────
curl_post_csrf "/training_types/1" "200,302,404,403,405,419,422" "_method=DELETE"  # training_types.destroy

# ── trainings ──────────────────────────────────
curl_post_csrf "/trainings/1" "200,302,404,403,405,419,422" "_method=DELETE"  # trainings.destroy

# ── transfers ──────────────────────────────────
curl_post_csrf "/transfers/1" "200,302,404,403,405,419,422" "_method=DELETE"  # transfers.destroy

# ── travels ──────────────────────────────────
curl_post_csrf "/travels/1" "200,302,404,403,405,419,422" "_method=DELETE"  # travels.destroy

# ── user ──────────────────────────────────
curl_post_csrf "/user" "200,302,404,403,405,419,422" "_method=DELETE"  # current-user.destroy

# ── user-reset-passwords ──────────────────────────────────
curl_post_csrf "/user-reset-passwords/1" "200,302,404,403,405,419,422" "_method=DELETE"  # users.reset

# ── users ──────────────────────────────────
curl_post_csrf "/users/logs/1" "200,302,404,403,405,419,422" "_method=DELETE"  # users.log.destroy
curl_post_csrf "/users/other-browser-sessions" "200,302,404,403,405,419,422" "_method=DELETE"  # other-browser-sessions.destroy
curl_post_csrf "/users/profile-photo" "200,302,404,403,405,419,422" "_method=DELETE"  # current-user-photo.destroy
curl_post_csrf "/users/two-factor-authentication" "200,302,404,403,405,419,422" "_method=DELETE"  # two-factor.disable
curl_post_csrf "/users/1" "200,302,404,403,405,419,422" "_method=DELETE"  # users.destroy
curl_post_csrf "/users/1" "200,302,404,403,405,419,422" "_method=DELETE"  # users.destroy

# ── vendors ──────────────────────────────────
curl_post_csrf "/vendors/1" "200,302,404,403,405,419,422" "_method=DELETE"  # vendors.destroy

# ── warehouse_transfers ──────────────────────────────────
curl_post_csrf "/warehouse_transfers/1" "200,302,404,403,405,419,422" "_method=DELETE"  # warehouse_transfers.destroy

# ── warehouses ──────────────────────────────────
curl_post_csrf "/warehouses/1" "200,302,404,403,405,419,422" "_method=DELETE"  # warehouses.destroy

# ── warnings ──────────────────────────────────
curl_post_csrf "/warnings/1" "200,302,404,403,405,419,422" "_method=DELETE"  # warnings.destroy

# ── webhook-settings ──────────────────────────────────
curl_post_csrf "/webhook-settings" "200,302,404,403,405,419,422" "_method=DELETE"  # webhooks.settings
curl_post_csrf "/webhook-settings/1" "200,302,404,403,405,419,422" "_method=DELETE"  # webhooks.destroy

# ── zoom-meetings ──────────────────────────────────
curl_post_csrf "/zoom-meetings/get_zoom_meeting_data" "200,302,404,403,405,419,422" "_method=DELETE"  # zoom_meetings.get_zoom_meeting_data

# ── zoom_meetings ──────────────────────────────────
curl_post_csrf "/zoom_meetings/projects/selects/1" "200,302,404,403,405,419,422" "_method=DELETE"  # zoom_meetings.projects.select
curl_post_csrf "/zoom_meetings/1" "200,302,404,403,405,419,422" "_method=DELETE"  # zoom_meetings.destroy

log_info "DELETE route tests complete."
summary
