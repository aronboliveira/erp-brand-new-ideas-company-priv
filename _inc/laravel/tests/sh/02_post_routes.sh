#!/usr/bin/env bash
# ─────────────────────────────────────────────────────
# 02_post_routes.sh  –  POST route coverage (all 409 POST routes)
# Auto-generated — do not edit by hand.
# ─────────────────────────────────────────────────────
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=_common.sh
source "$SCRIPT_DIR/_common.sh"

log_info "=== POST ROUTE TESTS (409 routes) ==="

# Ensure authenticated session
do_login

# ── _debugbars ──────────────────────────────────
curl_post_csrf "/_debugbars/queries/explain" "200,302,400,403,404,405,419,422,429,500,503"  # debugbar.queries.explain

# ── _ignitions ──────────────────────────────────
curl_post_csrf "/_ignitions/execute-solution" "200,302,400,403,404,405,419,422,429,500,503"  # ignition.executeSolution
curl_post_csrf "/_ignitions/update-config" "200,302,400,403,404,405,419,422,429,500,503"  # ignition.updateConfig

# ── account_assets ──────────────────────────────────
curl_post_csrf "/account_assets" "200,302,400,403,404,405,419,422,429,500,503"  # account_assets.store

# ── allowance_options ──────────────────────────────────
curl_post_csrf "/allowance_options" "200,302,400,403,404,405,419,422,429,500,503"  # allowance_options.store

# ── allowances ──────────────────────────────────
curl_post_csrf "/allowances" "200,302,400,403,404,405,419,422,429,500,503"  # allowances.store

# ── announcement ──────────────────────────────────
curl_post_csrf "/announcement" "200,302,400,403,404,405,419,422,429,500,503"  # announcement.store

# ── announcements ──────────────────────────────────
curl_post_csrf "/announcements/getdepartment" "200,302,400,403,404,405,419,422,429,500,503"  # announcements.getdepartment
curl_post_csrf "/announcements/getemployee" "200,302,400,403,404,405,419,422,429,500,503"  # announcements.getemployee

# ── api ──────────────────────────────────
curl_post_csrf "/api/logout" "200,302,400,403,404,405,419,422,429,500,503"  # auth.logout

# ── apis ──────────────────────────────────
curl_post_csrf "/apis/add-tracker" "200,302,400,403,404,405,419,422,429,500,503"  # trackers.store
curl_post_csrf "/apis/auth-login" "200,302,400,403,404,405,419,422,429,500,503"  # auth.login
curl_post_csrf "/apis/stop-tracker" "200,302,400,403,404,405,419,422,429,500,503"  # trackers.stop
curl_post_csrf "/apis/upload-photos" "200,302,400,403,404,405,419,422,429,500,503"  # photos.upload

# ── appraisals ──────────────────────────────────
curl_post_csrf "/appraisals" "200,302,400,403,404,405,419,422,429,500,503"  # appraisals.employees.star
curl_post_csrf "/appraisals/get-employee" "200,302,400,403,404,405,419,422,429,500,503"  # appraisals.get.employee

# ── appraisals1 ──────────────────────────────────
curl_post_csrf "/appraisals1" "200,302,400,403,404,405,419,422,429,500,503"  # appraisals.employees.star1

# ── attendances ──────────────────────────────────
curl_post_csrf "/attendances/imports/index" "200,302,400,403,404,405,419,422,429,500,503"  # attendance.import

# ── award_types ──────────────────────────────────
curl_post_csrf "/award_types" "200,302,400,403,404,405,419,422,429,500,503"  # award_types.store

# ── awards ──────────────────────────────────
curl_post_csrf "/awards" "200,302,400,403,404,405,419,422,429,500,503"  # awards.store

# ── balance-sheets ──────────────────────────────────
curl_post_csrf "/balance-sheets/export" "200,302,400,403,404,405,419,422,429,500,503"  # reports.balance.sheet.export
curl_post_csrf "/balance-sheets/prints/1" "200,302,400,403,404,405,419,422,429,500,503"  # reports.balance.sheet.print

# ── bank_accounts ──────────────────────────────────
curl_post_csrf "/bank_accounts" "200,302,400,403,404,405,419,422,429,500,503"  # bank_accounts.store

# ── bank_transfers ──────────────────────────────────
curl_post_csrf "/bank_transfers" "200,302,400,403,404,405,419,422,429,500,503"  # bank_transfers.store

# ── bills ──────────────────────────────────
curl_post_csrf "/bills" "200,302,400,403,404,405,419,422,429,500,503"  # bills.store
curl_post_csrf "/bills/templates/setting" "200,302,400,403,404,405,419,422,429,500,503"  # bills.templates.setting

# ── billsproduct ──────────────────────────────────
curl_post_csrf "/billsproduct" "200,302,400,403,404,405,419,422,429,500,503"  # bills.product

# ── billsproducts ──────────────────────────────────
curl_post_csrf "/billsproducts/destroy" "200,302,400,403,404,405,419,422,429,500,503"  # bills.product.destroy

# ── billsvendor ──────────────────────────────────
curl_post_csrf "/billsvendor" "200,302,400,403,404,405,419,422,429,500,503"  # bills.vendor

# ── bills{id} ──────────────────────────────────
curl_post_csrf "/bills1/debit-note" "200,302,400,403,404,405,419,422,429,500,503"  # bills.debit.note
curl_post_csrf "/bills1/debit_notes/edits/1" "200,302,400,403,404,405,419,422,429,500,503"  # bills.edit.debit.note
curl_post_csrf "/bills1/payment" "200,302,400,403,404,405,419,422,429,500,503"  # bills.payment
curl_post_csrf "/bills1/payments/1/destroy" "200,302,400,403,404,405,419,422,429,500,503"  # bills.payment.destroy

# ── branches ──────────────────────────────────
curl_post_csrf "/branches" "200,302,400,403,404,405,419,422,429,500,503"  # branches.store
curl_post_csrf "/branches/employees/json" "200,302,400,403,404,405,419,422,429,500,503"  # branches.employee.json

# ── broadcastings ──────────────────────────────────
curl_post_csrf "/broadcastings/auth" "200,302,400,403,404,405,419,422,429,500,503"

# ── budgets ──────────────────────────────────
curl_post_csrf "/budgets" "200,302,400,403,404,405,419,422,429,500,503"  # budgets.store

# ── bug_status ──────────────────────────────────
curl_post_csrf "/bug_status" "200,302,400,403,404,405,419,422,429,500,503"  # bug_status.store

# ── bug_statuses ──────────────────────────────────
curl_post_csrf "/bug_statuses/order" "200,302,400,403,404,405,419,422,429,500,503"  # bug_status.order

# ── business-setting ──────────────────────────────────
curl_post_csrf "/business-setting" "200,302,400,403,404,405,419,422,429,500,503"  # business.setting

# ── cache-settings ──────────────────────────────────
curl_post_csrf "/cache-settings" "200,302,400,403,404,405,419,422,429,500,503"  # cache.settings.store

# ── calendars ──────────────────────────────────
curl_post_csrf "/calendars/get_task_data" "200,302,400,403,404,405,419,422,429,500,503"  # projects.tasks.calendar.get_task_data
curl_post_csrf "/calendars/1/drag" "200,302,400,403,404,405,419,422,429,500,503"  # projects.tasks.calendar.drag

# ── cashfrees ──────────────────────────────────
curl_post_csrf "/cashfrees/payments/store" "200,302,400,403,404,405,419,422,429,500,503"  # plans.pay.with.cashfree
curl_post_csrf "/cashfrees/payments/success" "200,302,400,403,404,405,419,422,429,500,503"  # cashfree.payment.success

# ── change-password ──────────────────────────────────
curl_post_csrf "/change-password" "200,302,400,403,404,405,419,422,429,500,503"  # users.password.update

# ── chart_of_accounts ──────────────────────────────────
curl_post_csrf "/chart_of_accounts" "200,302,400,403,404,405,419,422,429,500,503"  # chart_of_accounts.store
curl_post_csrf "/chart_of_accounts/subtype" "200,302,400,403,404,405,419,422,429,500,503"  # chart_of_accounts.sub_type

# ── chatgpt-settings ──────────────────────────────────
curl_post_csrf "/chatgpt-settings" "200,302,400,403,404,405,419,422,429,500,503"  # settings.chatgpt.settings

# ── chats ──────────────────────────────────
curl_post_csrf "/chats/chats/auth" "200,302,400,403,404,405,419,422,429,500,503"  # pusher.auth
curl_post_csrf "/chats/delete-conversation" "200,302,400,403,404,405,419,422,429,500,503"  # conversation.delete
curl_post_csrf "/chats/favorites" "200,302,400,403,404,405,419,422,429,500,503"  # favorites
curl_post_csrf "/chats/fetch-messages" "200,302,400,403,404,405,419,422,429,500,503"  # fetch.messages
curl_post_csrf "/chats/get-contacts" "200,302,400,403,404,405,419,422,429,500,503"  # contacts.get
curl_post_csrf "/chats/id-info" "200,302,400,403,404,405,419,422,429,500,503"
curl_post_csrf "/chats/make-seen" "200,302,400,403,404,405,419,422,429,500,503"  # messages.seen
curl_post_csrf "/chats/search" "200,302,400,403,404,405,419,422,429,500,503"  # search
curl_post_csrf "/chats/send-message" "200,302,400,403,404,405,419,422,429,500,503"  # send.message
curl_post_csrf "/chats/set-active-status" "200,302,400,403,404,405,419,422,429,500,503"  # activeStatus.set
curl_post_csrf "/chats/shared" "200,302,400,403,404,405,419,422,429,500,503"  # shared
curl_post_csrf "/chats/star" "200,302,400,403,404,405,419,422,429,500,503"  # star
curl_post_csrf "/chats/update-contacts" "200,302,400,403,404,405,419,422,429,500,503"  # contacts.update
curl_post_csrf "/chats/update-settings" "200,302,400,403,404,405,419,422,429,500,503"  # avatar.update

# ── client-reset-passwords ──────────────────────────────────
curl_post_csrf "/client-reset-passwords/1" "200,302,400,403,404,405,419,422,429,500,503"  # clients.reset
curl_post_csrf "/client-reset-passwords/1" "200,302,400,403,404,405,419,422,429,500,503"  # clients.password.update

# ── clients ──────────────────────────────────
curl_post_csrf "/clients" "200,302,400,403,404,405,419,422,429,500,503"  # clients.store

# ── commissions ──────────────────────────────────
curl_post_csrf "/commissions" "200,302,400,403,404,405,419,422,429,500,503"  # commissions.store

# ── company-email-settings ──────────────────────────────────
curl_post_csrf "/company-email-settings" "200,302,400,403,404,405,419,422,429,500,503"  # companies.email.settings

# ── company-payment-setting ──────────────────────────────────
curl_post_csrf "/company-payment-setting" "200,302,400,403,404,405,419,422,429,500,503"  # companies.payment.settings

# ── company-settings ──────────────────────────────────
curl_post_csrf "/company-settings" "200,302,400,403,404,405,419,422,429,500,503"  # companies.settings

# ── company_policies ──────────────────────────────────
curl_post_csrf "/company_policies" "200,302,400,403,404,405,419,422,429,500,503"  # company_policies.store

# ── competencies ──────────────────────────────────
curl_post_csrf "/competencies" "200,302,400,403,404,405,419,422,429,500,503"  # competencies.store

# ── complaints ──────────────────────────────────
curl_post_csrf "/complaints" "200,302,400,403,404,405,419,422,429,500,503"  # complaints.store

# ── confirm-password ──────────────────────────────────
curl_post_csrf "/confirm-password" "200,302,400,403,404,405,419,422,429,500,503"

# ── contract_types ──────────────────────────────────
curl_post_csrf "/contract_types" "200,302,400,403,404,405,419,422,429,500,503"  # contract_types.store

# ── contracts ──────────────────────────────────
curl_post_csrf "/contracts" "200,302,400,403,404,405,419,422,429,500,503"  # contracts.store
curl_post_csrf "/contracts/clients/selects/1" "200,302,400,403,404,405,419,422,429,500,503"  # contracts.clients.select
curl_post_csrf "/contracts/contract_status_edits/1" "200,302,400,403,404,405,419,422,429,500,503"  # contracts.status
curl_post_csrf "/contracts/copies/store" "200,302,400,403,404,405,419,422,429,500,503"  # contracts.copy.store
curl_post_csrf "/contracts/1/comment" "200,302,400,403,404,405,419,422,429,500,503"  # contracts.comment.store
curl_post_csrf "/contracts/1/contract_description" "200,302,400,403,404,405,419,422,429,500,503"  # contracts.contract_description.store
curl_post_csrf "/contracts/1/file" "200,302,400,403,404,405,419,422,429,500,503"  # contracts.file.upload
curl_post_csrf "/contracts/1/notes" "200,302,400,403,404,405,419,422,429,500,503"  # contracts.note.store

# ── cookie-consent ──────────────────────────────────
curl_post_csrf "/cookie-consent" "200,302,400,403,404,405,419,422,429,500,503"  # cookie-consent

# ── cookie-setting ──────────────────────────────────
curl_post_csrf "/cookie-setting" "200,302,400,403,404,405,419,422,429,500,503"  # settings.cookies.store

# ── coupons ──────────────────────────────────
curl_post_csrf "/coupons" "200,302,400,403,404,405,419,422,429,500,503"  # coupons.store

# ── custom-credit-note ──────────────────────────────────
curl_post_csrf "/custom-credit-note" "200,302,400,403,404,405,419,422,429,500,503"  # invoices.custom.credit.note

# ── custom-debit-note ──────────────────────────────────
curl_post_csrf "/custom-debit-note" "200,302,400,403,404,405,419,422,429,500,503"  # bills.custom.debit.note

# ── custom-question ──────────────────────────────────
curl_post_csrf "/custom-question" "200,302,400,403,404,405,419,422,429,500,503"  # custom_questions.store

# ── custom_fields ──────────────────────────────────
curl_post_csrf "/custom_fields" "200,302,400,403,404,405,419,422,429,500,503"  # custom_fields.store

# ── custom_pages ──────────────────────────────────
curl_post_csrf "/custom_pages" "200,302,400,403,404,405,419,422,429,500,503"  # custom_pages.store
curl_post_csrf "/custom_pages/custom-store" "200,302,400,403,404,405,419,422,429,500,503"  # custom_pages.custom.store
curl_post_csrf "/custom_pages/store" "200,302,400,403,404,405,419,422,429,500,503"  # custom_pages.store

# ── customers ──────────────────────────────────
curl_post_csrf "/customers" "200,302,400,403,404,405,419,422,429,500,503"  # customers.store
curl_post_csrf "/customers/imports/index" "200,302,400,403,404,405,419,422,429,500,503"  # customers.import
curl_post_csrf "/customers/pay-with-bank" "200,302,400,403,404,405,419,422,429,500,503"  # customers.pay.with.bank

# ── deals ──────────────────────────────────
curl_post_csrf "/deals" "200,302,400,403,404,405,419,422,429,500,503"  # deals.store
curl_post_csrf "/deals/change-deal-statuses/1" "200,302,400,403,404,405,419,422,429,500,503"  # deals.change.status
curl_post_csrf "/deals/change-pipeline" "200,302,400,403,404,405,419,422,429,500,503"  # deals.change.pipeline
curl_post_csrf "/deals/order" "200,302,400,403,404,405,419,422,429,500,503"  # deals.order
curl_post_csrf "/deals/user" "200,302,400,403,404,405,419,422,429,500,503"  # deals.user.json
curl_post_csrf "/deals/1/call" "200,302,400,403,404,405,419,422,429,500,503"  # deals.calls.store
curl_post_csrf "/deals/1/discussions" "200,302,400,403,404,405,419,422,429,500,503"  # deals.discussion.store
curl_post_csrf "/deals/1/email" "200,302,400,403,404,405,419,422,429,500,503"  # deals.emails.store
curl_post_csrf "/deals/1/file" "200,302,400,403,404,405,419,422,429,500,503"  # deals.file.upload
curl_post_csrf "/deals/1/labels" "200,302,400,403,404,405,419,422,429,500,503"  # deals.labels.store
curl_post_csrf "/deals/1/note" "200,302,400,403,404,405,419,422,429,500,503"  # deals.note.store
curl_post_csrf "/deals/1/tasks" "200,302,400,403,404,405,419,422,429,500,503"  # deals.tasks.store

# ── deduction_options ──────────────────────────────────
curl_post_csrf "/deduction_options" "200,302,400,403,404,405,419,422,429,500,503"  # deduction_options.store

# ── departments ──────────────────────────────────
curl_post_csrf "/departments" "200,302,400,403,404,405,419,422,429,500,503"  # departments.store

# ── designations ──────────────────────────────────
curl_post_csrf "/designations" "200,302,400,403,404,405,419,422,429,500,503"  # designations.store

# ── disable-language ──────────────────────────────────
curl_post_csrf "/disable-language" "200,302,400,403,404,405,419,422,429,500,503"  # language.disable

# ── discover ──────────────────────────────────
curl_post_csrf "/discover" "200,302,400,403,404,405,419,422,429,500,503"  # discover.store
curl_post_csrf "/discover/store" "200,302,400,403,404,405,419,422,429,500,503"  # discover.store
curl_post_csrf "/discover/update/1" "200,302,400,403,404,405,419,422,429,500,503"  # discover.update

# ── document_uploads ──────────────────────────────────
curl_post_csrf "/document_uploads" "200,302,400,403,404,405,419,422,429,500,503"  # document_uploads.store

# ── documents ──────────────────────────────────
curl_post_csrf "/documents" "200,302,400,403,404,405,419,422,429,500,503"  # documents.store

# ── edit-profile ──────────────────────────────────
curl_post_csrf "/edit-profile" "200,302,400,403,404,405,419,422,429,500,503"  # users.account.update

# ── email-settings ──────────────────────────────────
curl_post_csrf "/email-settings" "200,302,400,403,404,405,419,422,429,500,503"  # email.settings

# ── email_template ──────────────────────────────────
curl_post_csrf "/email_template" "200,302,400,403,404,405,419,422,429,500,503"  # email_template.store

# ── email_template_store ──────────────────────────────────
curl_post_csrf "/email_template_store" "200,302,400,403,404,405,419,422,429,500,503"  # emails.status.language

# ── email_template_stores ──────────────────────────────────
curl_post_csrf "/email_template_stores/1" "200,302,400,403,404,405,419,422,429,500,503"  # emails.store.language

# ── emails ──────────────────────────────────
curl_post_csrf "/emails/verification-notification" "200,302,400,403,404,405,419,422,429,500,503"  # verification.send

# ── employee_attendances ──────────────────────────────────
curl_post_csrf "/employee_attendances" "200,302,400,403,404,405,419,422,429,500,503"  # employee_attendances.store
curl_post_csrf "/employee_attendances/attendance" "200,302,400,403,404,405,419,422,429,500,503"  # employee_attendances.attendance
curl_post_csrf "/employee_attendances/bulk-attendance" "200,302,400,403,404,405,419,422,429,500,503"  # employee_attendances.bulkAttendance

# ── employees ──────────────────────────────────
curl_post_csrf "/employees" "200,302,400,403,404,405,419,422,429,500,503"  # employees.store
curl_post_csrf "/employees/getdepartment" "200,302,400,403,404,405,419,422,429,500,503"  # employees.getdepartment
curl_post_csrf "/employees/imports/index" "200,302,400,403,404,405,419,422,429,500,503"  # employees.import
curl_post_csrf "/employees/json" "200,302,400,403,404,405,419,422,429,500,503"  # employees.json
curl_post_csrf "/employees/updates/sallaries/1" "200,302,400,403,404,405,419,422,429,500,503"  # employees.salary.update

# ── empty-cart ──────────────────────────────────
curl_post_csrf "/empty-cart" "200,302,400,403,404,405,419,422,429,500,503"

# ── events ──────────────────────────────────
curl_post_csrf "/events" "200,302,400,403,404,405,419,422,429,500,503"  # events.store
curl_post_csrf "/events/get-department" "200,302,400,403,404,405,419,422,429,500,503"  # events.getdepartment
curl_post_csrf "/events/get-employee" "200,302,400,403,404,405,419,422,429,500,503"  # events.getemployee
curl_post_csrf "/events/get_dashboard_event_data" "200,302,400,403,404,405,419,422,429,500,503"  # events.get_dashboard_event_data
curl_post_csrf "/events/get_event_data" "200,302,400,403,404,405,419,422,429,500,503"  # events.get_event_data

# ── expenses ──────────────────────────────────
curl_post_csrf "/expenses" "200,302,400,403,404,405,419,422,429,500,503"  # expenses.store
curl_post_csrf "/expenses/customer" "200,302,400,403,404,405,419,422,429,500,503"  # expenses.customer
curl_post_csrf "/expenses/employee" "200,302,400,403,404,405,419,422,429,500,503"  # expenses.employee
curl_post_csrf "/expenses/product" "200,302,400,403,404,405,419,422,429,500,503"  # expenses.product
curl_post_csrf "/expenses/products/destroy" "200,302,400,403,404,405,419,422,429,500,503"  # expenses.product.destroy
curl_post_csrf "/expenses/vendor" "200,302,400,403,404,405,419,422,429,500,503"  # expenses.vendor

# ── exports ──────────────────────────────────
curl_post_csrf "/exports/profit-loss" "200,302,400,403,404,405,419,422,429,500,503"  # reports.profit.loss.export

# ── faqs ──────────────────────────────────
curl_post_csrf "/faqs" "200,302,400,403,404,405,419,422,429,500,503"  # faqs.store
curl_post_csrf "/faqs/store" "200,302,400,403,404,405,419,422,429,500,503"  # faqs.store
curl_post_csrf "/faqs/update/1" "200,302,400,403,404,405,419,422,429,500,503"  # faqs.update

# ── features ──────────────────────────────────
curl_post_csrf "/features" "200,302,400,403,404,405,419,422,429,500,503"  # features.store
curl_post_csrf "/features/highlight/store" "200,302,400,403,404,405,419,422,429,500,503"  # features.highlight.store
curl_post_csrf "/features/store" "200,302,400,403,404,405,419,422,429,500,503"  # features.store
curl_post_csrf "/features/update/1" "200,302,400,403,404,405,419,422,429,500,503"  # features.update

# ── forgot-password ──────────────────────────────────
curl_post_csrf "/forgot-password" "200,302,400,403,404,405,419,422,429,500,503"  # password.email

# ── form_builders ──────────────────────────────────
curl_post_csrf "/form_builders" "200,302,400,403,404,405,419,422,429,500,503"  # form_builders.store
curl_post_csrf "/form_builders/1/field" "200,302,400,403,404,405,419,422,429,500,503"  # forms.fields.store
curl_post_csrf "/form_builders/1/fields/1" "200,302,400,403,404,405,419,422,429,500,503"  # forms.fields.update

# ── forms ──────────────────────────────────
curl_post_csrf "/forms/binds/1/store" "200,302,400,403,404,405,419,422,429,500,503"  # forms.bind.store
curl_post_csrf "/forms/view_store" "200,302,400,403,404,405,419,422,429,500,503"  # forms.view.store

# ── fortify-forgot-password ──────────────────────────────────
curl_post_csrf "/fortify-forgot-password" "200,302,400,403,404,405,419,422,429,500,503"  # fortify.password.email

# ── fortify-login ──────────────────────────────────
curl_post_csrf "/fortify-login" "200,302,400,403,404,405,419,422,429,500,503"  # fortify.login.store

# ── fortify-logout ──────────────────────────────────
curl_post_csrf "/fortify-logout" "200,302,400,403,404,405,419,422,429,500,503"  # fortify.logout

# ── fortify-register ──────────────────────────────────
curl_post_csrf "/fortify-register" "200,302,400,403,404,405,419,422,429,500,503"  # fortify.register.store

# ── generates ──────────────────────────────────
curl_post_csrf "/generates/keywords/1" "200,302,400,403,404,405,419,422,429,500,503"  # generate.keywords
curl_post_csrf "/generates/response" "200,302,400,403,404,405,419,422,429,500,503"  # generate.response

# ── goal_trackings ──────────────────────────────────
curl_post_csrf "/goal_trackings" "200,302,400,403,404,405,419,422,429,500,503"  # goal_trackings.store

# ── goal_types ──────────────────────────────────
curl_post_csrf "/goal_types" "200,302,400,403,404,405,419,422,429,500,503"  # goal_types.store

# ── goals ──────────────────────────────────
curl_post_csrf "/goals" "200,302,400,403,404,405,419,422,429,500,503"  # goals.store

# ── grammars ──────────────────────────────────
curl_post_csrf "/grammars/response" "200,302,400,403,404,405,419,422,429,500,503"  # grammar.response

# ── holidays ──────────────────────────────────
curl_post_csrf "/holidays" "200,302,400,403,404,405,419,422,429,500,503"  # holidays.store
curl_post_csrf "/holidays/data" "200,302,400,403,404,405,419,422,429,500,503"  # holidays.get_holiday_data

# ── home_section ──────────────────────────────────
curl_post_csrf "/home_section" "200,302,400,403,404,405,419,422,429,500,503"  # home_section.store

# ── indicators ──────────────────────────────────
curl_post_csrf "/indicators" "200,302,400,403,404,405,419,422,429,500,503"  # indicators.store

# ── installs ──────────────────────────────────
curl_post_csrf "/installs/environments/save-classic" "200,302,400,403,404,405,419,422,429,500,503"  # LaravelInstaller::environmentSaveClassic
curl_post_csrf "/installs/environments/save-wizard" "200,302,400,403,404,405,419,422,429,500,503"  # LaravelInstaller::environmentSaveWizard

# ── interview-schedule ──────────────────────────────────
curl_post_csrf "/interview-schedule" "200,302,400,403,404,405,419,422,429,500,503"  # interview-schedule.store

# ── interview_schedules ──────────────────────────────────
curl_post_csrf "/interview_schedules/data" "200,302,400,403,404,405,419,422,429,500,503"  # interview_schedules.get_interview_data

# ── invite-project-user-member ──────────────────────────────────
curl_post_csrf "/invite-project-user-member" "200,302,400,403,404,405,419,422,429,500,503"  # projects.invite.user.member

# ── invoices ──────────────────────────────────
curl_post_csrf "/invoices" "200,302,400,403,404,405,419,422,429,500,503"  # invoices.store
curl_post_csrf "/invoices/benefits/1/1" "200,302,400,403,404,405,419,422,429,500,503"  # invoices.benefit.callback
curl_post_csrf "/invoices/customer" "200,302,400,403,404,405,419,422,429,500,503"  # invoices.customer
curl_post_csrf "/invoices/product" "200,302,400,403,404,405,419,422,429,500,503"  # invoices.product
curl_post_csrf "/invoices/products/destroy" "200,302,400,403,404,405,419,422,429,500,503"  # invoices.product.destroy
curl_post_csrf "/invoices/templates/setting" "200,302,400,403,404,405,419,422,429,500,503"  # invoices.templates.settings
curl_post_csrf "/invoices/with-benefit" "200,302,400,403,404,405,419,422,429,500,503"  # invoices.benefit.initiate
curl_post_csrf "/invoices/with-cashfrees/payment" "200,302,400,403,404,405,419,422,429,500,503"  # customers.pay.with.cashfree
curl_post_csrf "/invoices/with-cashfrees/status" "200,302,400,403,404,405,419,422,429,500,503"  # invoices.cashfree.payment.success
curl_post_csrf "/invoices/1/change-action" "200,302,400,403,404,405,419,422,429,500,503"  # invoices.change.status
curl_post_csrf "/invoices/1/credit-note" "200,302,400,403,404,405,419,422,429,500,503"  # invoices.credit.note
curl_post_csrf "/invoices/1/credit-notes/edits/1" "200,302,400,403,404,405,419,422,429,500,503"  # invoices.edit.credit.note
curl_post_csrf "/invoices/1/payment" "200,302,400,403,404,405,419,422,429,500,503"  # invoices.payment
curl_post_csrf "/invoices/1/payments/1/destroy" "200,302,400,403,404,405,419,422,429,500,503"  # invoices.payment.destroy

# ── job-application ──────────────────────────────────
curl_post_csrf "/job-application" "200,302,400,403,404,405,419,422,429,500,503"  # job-application.store

# ── job-applications ──────────────────────────────────
curl_post_csrf "/job-applications/get-by-job" "200,302,400,403,404,405,419,422,429,500,503"  # job_applications.get
curl_post_csrf "/job-applications/order" "200,302,400,403,404,405,419,422,429,500,503"  # jobs.application.order
curl_post_csrf "/job-applications/stages/change" "200,302,400,403,404,405,419,422,429,500,503"  # jobs.application.stage.change
curl_post_csrf "/job-applications/1/notes/store" "200,302,400,403,404,405,419,422,429,500,503"  # jobs.application.note.store
curl_post_csrf "/job-applications/1/rating" "200,302,400,403,404,405,419,422,429,500,503"  # jobs.application.rating
curl_post_csrf "/job-applications/1/skills/store" "200,302,400,403,404,405,419,422,429,500,503"  # jobs.application.skill.store

# ── job-category ──────────────────────────────────
curl_post_csrf "/job-category" "200,302,400,403,404,405,419,422,429,500,503"  # job-category.store

# ── job-stage ──────────────────────────────────
curl_post_csrf "/job-stage" "200,302,400,403,404,405,419,422,429,500,503"  # job-stage.store

# ── job-stages ──────────────────────────────────
curl_post_csrf "/job-stages/order" "200,302,400,403,404,405,419,422,429,500,503"  # jobs.stage.order

# ── jobs ──────────────────────────────────
curl_post_csrf "/jobs" "200,302,400,403,404,405,419,422,429,500,503"  # jobs.store
curl_post_csrf "/jobs/applies/data/1" "200,302,400,403,404,405,419,422,429,500,503"  # jobs.apply.data

# ── jobs_onboards ──────────────────────────────────
curl_post_csrf "/jobs_onboards/converts/1" "200,302,400,403,404,405,419,422,429,500,503"  # jobs.on.board.convert
curl_post_csrf "/jobs_onboards/stores/1" "200,302,400,403,404,405,419,422,429,500,503"  # jobs.on.board.store
curl_post_csrf "/jobs_onboards/updates/1" "200,302,400,403,404,405,419,422,429,500,503"  # jobs.on.board.update

# ── join_us ──────────────────────────────────
curl_post_csrf "/join_us/store" "200,302,400,403,404,405,419,422,429,500,503"  # join_us.store
curl_post_csrf "/join_us/user-store" "200,302,400,403,404,405,419,422,429,500,503"  # join_us.user.store

# ── journal_entries ──────────────────────────────────
curl_post_csrf "/journal_entries" "200,302,400,403,404,405,419,422,429,500,503"  # journal_entries.store
curl_post_csrf "/journal_entries/accounts/destroy" "200,302,400,403,404,405,419,422,429,500,503"  # journalsaccount.destroy

# ── labels ──────────────────────────────────
curl_post_csrf "/labels" "200,302,400,403,404,405,419,422,429,500,503"  # labels.store

# ── landingpage ──────────────────────────────────
curl_post_csrf "/landingpage" "200,302,400,403,404,405,419,422,429,500,503"  # landingpage.store

# ── lead_stages ──────────────────────────────────
curl_post_csrf "/lead_stages" "200,302,400,403,404,405,419,422,429,500,503"  # lead_stages.store
curl_post_csrf "/lead_stages/order" "200,302,400,403,404,405,419,422,429,500,503"  # lead_stages.order

# ── leads ──────────────────────────────────
curl_post_csrf "/leads" "200,302,400,403,404,405,419,422,429,500,503"  # leads.store
curl_post_csrf "/leads/json" "200,302,400,403,404,405,419,422,429,500,503"  # leads.json
curl_post_csrf "/leads/order" "200,302,400,403,404,405,419,422,429,500,503"  # leads.order
curl_post_csrf "/leads/1/call" "200,302,400,403,404,405,419,422,429,500,503"  # leads.calls.store
curl_post_csrf "/leads/1/convert" "200,302,400,403,404,405,419,422,429,500,503"  # leads.convert.to.deal
curl_post_csrf "/leads/1/discussions" "200,302,400,403,404,405,419,422,429,500,503"  # leads.discussion.store
curl_post_csrf "/leads/1/email" "200,302,400,403,404,405,419,422,429,500,503"  # leads.emails.store
curl_post_csrf "/leads/1/file" "200,302,400,403,404,405,419,422,429,500,503"  # leads.file.upload
curl_post_csrf "/leads/1/labels" "200,302,400,403,404,405,419,422,429,500,503"  # leads.labels.store
curl_post_csrf "/leads/1/note" "200,302,400,403,404,405,419,422,429,500,503"  # leads.note.store

# ── leave ──────────────────────────────────
curl_post_csrf "/leave" "200,302,400,403,404,405,419,422,429,500,503"  # leave.store

# ── leave_types ──────────────────────────────────
curl_post_csrf "/leave_types" "200,302,400,403,404,405,419,422,429,500,503"  # leave_types.store

# ── leaves ──────────────────────────────────
curl_post_csrf "/leaves/changeaction" "200,302,400,403,404,405,419,422,429,500,503"  # leaves.change_action
curl_post_csrf "/leaves/jsoncount" "200,302,400,403,404,405,419,422,429,500,503"  # leaves.jsoncount

# ── loan_options ──────────────────────────────────
curl_post_csrf "/loan_options" "200,302,400,403,404,405,419,422,429,500,503"  # loan_options.store

# ── loans ──────────────────────────────────
curl_post_csrf "/loans" "200,302,400,403,404,405,419,422,429,500,503"  # loans.store

# ── login ──────────────────────────────────
curl_post_csrf "/login" "200,302,400,403,404,405,419,422,429,500,503"  # login.store

# ── logout ──────────────────────────────────
curl_post_csrf "/logout" "200,302,400,403,404,405,419,422,429,500,503"  # logout

# ── meetings ──────────────────────────────────
curl_post_csrf "/meetings" "200,302,400,403,404,405,419,422,429,500,503"  # meetings.store
curl_post_csrf "/meetings/get-department" "200,302,400,403,404,405,419,422,429,500,503"  # meetings.getdepartment
curl_post_csrf "/meetings/get-employee" "200,302,400,403,404,405,419,422,429,500,503"  # meetings.getemployee
curl_post_csrf "/meetings/get_meeting_data" "200,302,400,403,404,405,419,422,429,500,503"  # meetings.get_meeting_data

# ── notification_templates ──────────────────────────────────
curl_post_csrf "/notification_templates" "200,302,400,403,404,405,419,422,429,500,503"  # notification_templates.store

# ── orders ──────────────────────────────────
curl_post_csrf "/orders/1/changeaction" "200,302,400,403,404,405,419,422,429,500,503"  # orders.change.status

# ── other_payments ──────────────────────────────────
curl_post_csrf "/other_payments" "200,302,400,403,404,405,419,422,429,500,503"  # other_payments.store

# ── overtimes ──────────────────────────────────
curl_post_csrf "/overtimes" "200,302,400,403,404,405,419,422,429,500,503"  # overtimes.store

# ── payables ──────────────────────────────────
curl_post_csrf "/payables/print" "200,302,400,403,404,405,419,422,429,500,503"  # reports.payables.print

# ── payment-i-p-n ──────────────────────────────────
curl_post_csrf "/payment-i-p-n" "200,302,400,403,404,405,419,422,429,500,503"  # payment_ipn

# ── payments ──────────────────────────────────
curl_post_csrf "/payments" "200,302,400,403,404,405,419,422,429,500,503"  # payments.store
curl_post_csrf "/payments/benefits/callback" "200,302,400,403,404,405,419,422,429,500,503"  # benefit.callback
curl_post_csrf "/payments/benefits/initiate" "200,302,400,403,404,405,419,422,429,500,503"  # plans.pay.with.benefit

# ── payslips ──────────────────────────────────
curl_post_csrf "/payslips" "200,302,400,403,404,405,419,422,429,500,503"  # payslips.store
curl_post_csrf "/payslips/bulk_payments/1" "200,302,400,403,404,405,419,422,429,500,503"  # payslips.bulkpayment
curl_post_csrf "/payslips/employees/updates/1" "200,302,400,403,404,405,419,422,429,500,503"  # payslips.updateEmployee
curl_post_csrf "/payslips/export" "200,302,400,403,404,405,419,422,429,500,503"  # payslips.export
curl_post_csrf "/payslips/search_json" "200,302,400,403,404,405,419,422,429,500,503"  # payslips.search_json

# ── performance_types ──────────────────────────────────
curl_post_csrf "/performance_types" "200,302,400,403,404,405,419,422,429,500,503"  # performance_types.store

# ── permissions ──────────────────────────────────
curl_post_csrf "/permissions" "200,302,400,403,404,405,419,422,429,500,503"  # permissions.store

# ── pipelines ──────────────────────────────────
curl_post_csrf "/pipelines" "200,302,400,403,404,405,419,422,429,500,503"  # pipelines.store

# ── plan-pay-with-bank ──────────────────────────────────
curl_post_csrf "/plan-pay-with-bank" "200,302,400,403,404,405,419,422,429,500,503"  # plans.pay.with.bank

# ── plans ──────────────────────────────────
curl_post_csrf "/plans" "200,302,400,403,404,405,419,422,429,500,503"  # plans.store

# ── pos ──────────────────────────────────
curl_post_csrf "/pos" "200,302,400,403,404,405,419,422,429,500,503"  # pos.store
curl_post_csrf "/pos/cart-discount" "200,302,400,403,404,405,419,422,429,500,503"  # pos.cart.discount
curl_post_csrf "/pos/get-product" "200,302,400,403,404,405,419,422,429,500,503"  # pos.get.product
curl_post_csrf "/pos/templates/setting" "200,302,400,403,404,405,419,422,429,500,503"  # purchases.templates.settings

# ── pos-receipt ──────────────────────────────────
curl_post_csrf "/pos-receipt" "200,302,400,403,404,405,419,422,429,500,503"  # pos.receipt

# ── pricing_plans ──────────────────────────────────
curl_post_csrf "/pricing_plans" "200,302,400,403,404,405,419,422,429,500,503"  # pricing_plans.store
curl_post_csrf "/pricing_plans/store" "200,302,400,403,404,405,419,422,429,500,503"  # pricing_plans.store

# ── prints ──────────────────────────────────
curl_post_csrf "/prints/profit-losses/1" "200,302,400,403,404,405,419,422,429,500,503"  # reports.profit.loss.print
curl_post_csrf "/prints/trial-balance" "200,302,400,403,404,405,419,422,429,500,503"  # trial.balance.print

# ── product_service_categories ──────────────────────────────────
curl_post_csrf "/product_service_categories" "200,302,400,403,404,405,419,422,429,500,503"  # product_service_categories.store
curl_post_csrf "/product_service_categories/get-account" "200,302,400,403,404,405,419,422,429,500,503"  # product_service_categories.get_account

# ── product_service_units ──────────────────────────────────
curl_post_csrf "/product_service_units" "200,302,400,403,404,405,419,422,429,500,503"  # product_service_units.store

# ── product_services ──────────────────────────────────
curl_post_csrf "/product_services" "200,302,400,403,404,405,419,422,429,500,503"  # product_services.store
curl_post_csrf "/product_services/import" "200,302,400,403,404,405,419,422,429,500,503"  # product_services.import

# ── product_stocks ──────────────────────────────────
curl_post_csrf "/product_stocks" "200,302,400,403,404,405,419,422,429,500,503"  # product_stocks.store

# ── profile ──────────────────────────────────
curl_post_csrf "/profile" "200,302,400,403,404,405,419,422,429,500,503"  # update.profile

# ── project_reports ──────────────────────────────────
curl_post_csrf "/project_reports" "200,302,400,403,404,405,419,422,429,500,503"  # project_reports.store
curl_post_csrf "/project_reports/data" "200,302,400,403,404,405,419,422,429,500,503"  # project_reports.ajax
curl_post_csrf "/project_reports/tasks/1" "200,302,400,403,404,405,419,422,429,500,503"  # project_reports.tasks.ajaxdata

# ── project_stages ──────────────────────────────────
curl_post_csrf "/project_stages" "200,302,400,403,404,405,419,422,429,500,503"  # project_stages.store
curl_post_csrf "/project_stages/order" "200,302,400,403,404,405,419,422,429,500,503"  # project_stages.order

# ── project_task_stages ──────────────────────────────────
curl_post_csrf "/project_task_stages" "200,302,400,403,404,405,419,422,429,500,503"  # project_task_stages.store
curl_post_csrf "/project_task_stages/order" "200,302,400,403,404,405,419,422,429,500,503"  # project_task_stages.order

# ── project_task_stages-new ──────────────────────────────────
curl_post_csrf "/project_task_stages-new" "200,302,400,403,404,405,419,422,429,500,503"  # project_task_stages.new

# ── projects ──────────────────────────────────
curl_post_csrf "/projects" "200,302,400,403,404,405,419,422,429,500,503"  # projects.store
curl_post_csrf "/projects/bugs/files/1" "200,302,400,403,404,405,419,422,429,500,503"  # projects.bugs.comments.file.destroy
curl_post_csrf "/projects/bugs/kanbans/order" "200,302,400,403,404,405,419,422,429,500,503"  # projects.bugs.kanban.order
curl_post_csrf "/projects/bugs/1/file" "200,302,400,403,404,405,419,422,429,500,503"  # projects.bugs.comments.file.store
curl_post_csrf "/projects/copies/links/1" "200,302,400,403,404,405,419,422,429,500,503"  # projects.copy.link
curl_post_csrf "/projects/copies/stores/1" "200,302,400,403,404,405,419,422,429,500,503"  # projects.copy.store
curl_post_csrf "/projects/links/1/1" "200,302,400,403,404,405,419,422,429,500,503"  # projects.link
curl_post_csrf "/projects/milestones/1" "200,302,400,403,404,405,419,422,429,500,503"  # projects.milestones.update
curl_post_csrf "/projects/1/bugs/store" "200,302,400,403,404,405,419,422,429,500,503"  # projects.tasks.bugs.store
curl_post_csrf "/projects/1/bugs/1/comment" "200,302,400,403,404,405,419,422,429,500,503"  # projects.bugs.comments.store
curl_post_csrf "/projects/1/bugs/1/update" "200,302,400,403,404,405,419,422,429,500,503"  # projects.tasks.bugs.update
curl_post_csrf "/projects/1/changes/1/complete" "200,302,400,403,404,405,419,422,429,500,503"  # projects.tasks.change.complete
curl_post_csrf "/projects/1/changes/1/fav" "200,302,400,403,404,405,419,422,429,500,503"  # projects.tasks.change.fav
curl_post_csrf "/projects/1/changes/1/progress" "200,302,400,403,404,405,419,422,429,500,503"  # projects.taskschange.progress
curl_post_csrf "/projects/1/checklists/updates/1" "200,302,400,403,404,405,419,422,429,500,503"  # projects.tasks.checklist.update
curl_post_csrf "/projects/1/checklists/1" "200,302,400,403,404,405,419,422,429,500,503"  # projects.tasks.checklist.store
curl_post_csrf "/projects/1/comments/1" "200,302,400,403,404,405,419,422,429,500,503"  # projects.tasks.comment.store
curl_post_csrf "/projects/1/comments/1/file" "200,302,400,403,404,405,419,422,429,500,503"  # projects.tasks.comment.store.file
curl_post_csrf "/projects/1/expenses/1" "200,302,400,403,404,405,419,422,429,500,503"  # projects.expenses.update
curl_post_csrf "/projects/1/gantt" "200,302,400,403,404,405,419,422,429,500,503"  # projects.gantt.post
curl_post_csrf "/projects/1/milestones" "200,302,400,403,404,405,419,422,429,500,503"  # projects.milestones.store
curl_post_csrf "/projects/1/setting-create" "200,302,400,403,404,405,419,422,429,500,503"  # projects.copy_link.setting.create
curl_post_csrf "/projects/1/store-stages/1" "200,302,400,403,404,405,419,422,429,500,503"  # projects.stages.store
curl_post_csrf "/projects/1/tasks/updates/1" "200,302,400,403,404,405,419,422,429,500,503"  # projects.tasks.update
curl_post_csrf "/projects/1/users/1/permission" "200,302,400,403,404,405,419,422,429,500,503"  # projects.users.permissions.store
curl_post_csrf "/projects/1/expenses/store" "200,302,400,403,404,405,419,422,429,500,503"  # projects.expenses.store
curl_post_csrf "/projects/1/tasks/1" "200,302,400,403,404,405,419,422,429,500,503"  # projects.tasks.store

# ── projects.timesheets ──────────────────────────────────
curl_post_csrf "/projects.timesheets/projects/updates/1" "200,302,400,403,404,405,419,422,429,500,503"  # projects.timesheets.update
curl_post_csrf "/projects.timesheets/projects/1" "200,302,400,403,404,405,419,422,429,500,503"  # projects.timesheets.store

# ── promotions ──────────────────────────────────
curl_post_csrf "/promotions" "200,302,400,403,404,405,419,422,429,500,503"  # promotions.store

# ── proposal ──────────────────────────────────
curl_post_csrf "/proposal" "200,302,400,403,404,405,419,422,429,500,503"  # proposal.store

# ── proposals ──────────────────────────────────
curl_post_csrf "/proposals/customer" "200,302,400,403,404,405,419,422,429,500,503"  # proposals.customer
curl_post_csrf "/proposals/product" "200,302,400,403,404,405,419,422,429,500,503"  # proposals.product
curl_post_csrf "/proposals/products/destroy" "200,302,400,403,404,405,419,422,429,500,503"  # proposals.product.destroy
curl_post_csrf "/proposals/templates/settings" "200,302,400,403,404,405,419,422,429,500,503"  # proposalssettings

# ── purchases ──────────────────────────────────
curl_post_csrf "/purchases" "200,302,400,403,404,405,419,422,429,500,503"  # purchases.store
curl_post_csrf "/purchases/product" "200,302,400,403,404,405,419,422,429,500,503"  # purchases.product
curl_post_csrf "/purchases/products/destroy" "200,302,400,403,404,405,419,422,429,500,503"  # purchases.product.destroy
curl_post_csrf "/purchases/templates/settings" "200,302,400,403,404,405,419,422,429,500,503"  # purchases.templates.settings
curl_post_csrf "/purchases/vendor" "200,302,400,403,404,405,419,422,429,500,503"  # purchases.vendor
curl_post_csrf "/purchases/1/payment" "200,302,400,403,404,405,419,422,429,500,503"  # purchases.payment
curl_post_csrf "/purchases/1/payments/1/destroy" "200,302,400,403,404,405,419,422,429,500,503"  # purchases.payment.destroy

# ── pusher-setting ──────────────────────────────────
curl_post_csrf "/pusher-setting" "200,302,400,403,404,405,419,422,429,500,503"  # settings.pusher

# ── recaptcha-settings ──────────────────────────────────
curl_post_csrf "/recaptcha-settings" "200,302,400,403,404,405,419,422,429,500,503"  # settings.recaptcha.store

# ── receivables ──────────────────────────────────
curl_post_csrf "/receivables/export" "200,302,400,403,404,405,419,422,429,500,503"  # receivables.export
curl_post_csrf "/receivables/print" "200,302,400,403,404,405,419,422,429,500,503"  # reports.receivables.print

# ── register ──────────────────────────────────
curl_post_csrf "/register" "200,302,400,403,404,405,419,422,429,500,503"  # register.store

# ── reports ──────────────────────────────────
curl_post_csrf "/reports/pos" "200,302,400,403,404,405,419,422,429,500,503"  # pos.report

# ── reports-monthly-attendances ──────────────────────────────────
curl_post_csrf "/reports-monthly-attendances/getdepartment" "200,302,400,403,404,405,419,422,429,500,503"  # reports.attendance.getdepartment
curl_post_csrf "/reports-monthly-attendances/getemployee" "200,302,400,403,404,405,419,422,429,500,503"  # reports.attendance.getemployee

# ── reports-payrolls ──────────────────────────────────
curl_post_csrf "/reports-payrolls/getdepartment" "200,302,400,403,404,405,419,422,429,500,503"  # reports.payroll.getdepartment
curl_post_csrf "/reports-payrolls/getemployee" "200,302,400,403,404,405,419,422,429,500,503"  # reports.payroll.getemployee

# ── reset-password ──────────────────────────────────
curl_post_csrf "/reset-password" "200,302,400,403,404,405,419,422,429,500,503"  # password.update

# ── resignations ──────────────────────────────────
curl_post_csrf "/resignations" "200,302,400,403,404,405,419,422,429,500,503"  # resignations.store

# ── revenues ──────────────────────────────────
curl_post_csrf "/revenues" "200,302,400,403,404,405,419,422,429,500,503"  # revenues.store

# ── roles ──────────────────────────────────
curl_post_csrf "/roles" "200,302,400,403,404,405,419,422,429,500,503"  # roles.store

# ── sales ──────────────────────────────────
curl_post_csrf "/sales/export" "200,302,400,403,404,405,419,422,429,500,503"  # reports.sales.export
curl_post_csrf "/sales/reports/print" "200,302,400,403,404,405,419,422,429,500,503"  # reports.sales.report.print

# ── saturation_deductions ──────────────────────────────────
curl_post_csrf "/saturation_deductions" "200,302,400,403,404,405,419,422,429,500,503"  # saturation_deductions.store

# ── screenshots ──────────────────────────────────
curl_post_csrf "/screenshots" "200,302,400,403,404,405,419,422,429,500,503"  # screenshots.store
curl_post_csrf "/screenshots/store" "200,302,400,403,404,405,419,422,429,500,503"  # screenshots.store
curl_post_csrf "/screenshots/update/1" "200,302,400,403,404,405,419,422,429,500,503"  # screenshots.update

# ── seo-settings ──────────────────────────────────
curl_post_csrf "/seo-settings" "200,302,400,403,404,405,419,422,429,500,503"  # settings.seo.store

# ── settings ──────────────────────────────────
curl_post_csrf "/settings/barcode" "200,302,400,403,404,405,419,422,429,500,503"  # pos.barcode.setting
curl_post_csrf "/settings/exps/1" "200,302,400,403,404,405,419,422,429,500,503"  # experience_certificate.update
curl_post_csrf "/settings/google-calendar" "200,302,400,403,404,405,419,422,429,500,503"  # settingsgoogle.calendar
curl_post_csrf "/settings/joining-letters/1" "200,302,400,403,404,405,419,422,429,500,503"  # joining_letter.update
curl_post_csrf "/settings/nocs/1" "200,302,400,403,404,405,419,422,429,500,503"  # noc.update
curl_post_csrf "/settings/offer-letters/1" "200,302,400,403,404,405,419,422,429,500,503"  # offer_letter.update

# ── signature-store ──────────────────────────────────
curl_post_csrf "/signature-store" "200,302,400,403,404,405,419,422,429,500,503"  # contracts.signature.store

# ── slack-settings ──────────────────────────────────
curl_post_csrf "/slack-settings" "200,302,400,403,404,405,419,422,429,500,503"  # slack.settings

# ── sources ──────────────────────────────────
curl_post_csrf "/sources" "200,302,400,403,404,405,419,422,429,500,503"  # sources.store

# ── stages ──────────────────────────────────
curl_post_csrf "/stages" "200,302,400,403,404,405,419,422,429,500,503"  # stages.store
curl_post_csrf "/stages/json" "200,302,400,403,404,405,419,422,429,500,503"  # stages.json
curl_post_csrf "/stages/order" "200,302,400,403,404,405,419,422,429,500,503"  # stages.order

# ── stop-tracker ──────────────────────────────────
curl_post_csrf "/stop-tracker" "200,302,400,403,404,405,419,422,429,500,503"  # stop.tracker

# ── storage-settings ──────────────────────────────────
curl_post_csrf "/storage-settings" "200,302,400,403,404,405,419,422,429,500,503"  # settings.storage.store

# ── store-language ──────────────────────────────────
curl_post_csrf "/store-language" "200,302,400,403,404,405,419,422,429,500,503"  # languages.store

# ── store-language-datas ──────────────────────────────────
curl_post_csrf "/store-language-datas/1" "200,302,400,403,404,405,419,422,429,500,503"  # languages.store.data

# ── stripe ──────────────────────────────────
curl_post_csrf "/stripe" "200,302,400,403,404,405,419,422,429,500,503"  # stripe.post

# ── stripe-settings ──────────────────────────────────
curl_post_csrf "/stripe-settings" "200,302,400,403,404,405,419,422,429,500,503"  # payments.settings

# ── supports ──────────────────────────────────
curl_post_csrf "/supports" "200,302,400,403,404,405,419,422,429,500,503"  # supports.store
curl_post_csrf "/supports/1/reply" "200,302,400,403,404,405,419,422,429,500,503"  # supports.reply.answer

# ── system-settings ──────────────────────────────────
curl_post_csrf "/system-settings" "200,302,400,403,404,405,419,422,429,500,503"  # systems.settings
curl_post_csrf "/system-settings/note" "200,302,400,403,404,405,419,422,429,500,503"  # systems.settings.footernote

# ── systems ──────────────────────────────────
curl_post_csrf "/systems" "200,302,400,403,404,405,419,422,429,500,503"  # systems.store
curl_post_csrf "/systems/creates/ip" "200,302,400,403,404,405,419,422,429,500,503"  # systems.ip.store
curl_post_csrf "/systems/edits/ips/1" "200,302,400,403,404,405,419,422,429,500,503"  # systems.ip.update

# ── taxes ──────────────────────────────────
curl_post_csrf "/taxes" "200,302,400,403,404,405,419,422,429,500,503"  # taxes.store

# ── telegram-settings ──────────────────────────────────
curl_post_csrf "/telegram-settings" "200,302,400,403,404,405,419,422,429,500,503"  # telegram.settings

# ── terminations ──────────────────────────────────
curl_post_csrf "/terminations" "200,302,400,403,404,405,419,422,429,500,503"  # terminations.store

# ── terminationtype ──────────────────────────────────
curl_post_csrf "/terminationtype" "200,302,400,403,404,405,419,422,429,500,503"  # termination_types.store

# ── test-mail ──────────────────────────────────
curl_post_csrf "/test-mail" "200,302,400,403,404,405,419,422,429,500,503"  # tests.mail

# ── test-mails ──────────────────────────────────
curl_post_csrf "/test-mails/send" "200,302,400,403,404,405,419,422,429,500,503"  # tests.send.mail

# ── testimonials ──────────────────────────────────
curl_post_csrf "/testimonials" "200,302,400,403,404,405,419,422,429,500,503"  # testimonials.store
curl_post_csrf "/testimonials/store" "200,302,400,403,404,405,419,422,429,500,503"  # testimonials.store
curl_post_csrf "/testimonials/update/1" "200,302,400,403,404,405,419,422,429,500,503"  # testimonials.update

# ── todos ──────────────────────────────────
curl_post_csrf "/todos/create" "200,302,400,403,404,405,419,422,429,500,503"  # todos.store
curl_post_csrf "/todos/1/update" "200,302,400,403,404,405,419,422,429,500,503"  # todos.update

# ── tracker-settings ──────────────────────────────────
curl_post_csrf "/tracker-settings" "200,302,400,403,404,405,419,422,429,500,503"  # time_trackers.settings

# ── trackers ──────────────────────────────────
curl_post_csrf "/trackers/image-view" "200,302,400,403,404,405,419,422,429,500,503"  # time_trackers.image.view

# ── trainers ──────────────────────────────────
curl_post_csrf "/trainers" "200,302,400,403,404,405,419,422,429,500,503"  # trainers.store

# ── training_types ──────────────────────────────────
curl_post_csrf "/training_types" "200,302,400,403,404,405,419,422,429,500,503"  # training_types.store

# ── trainings ──────────────────────────────────
curl_post_csrf "/trainings" "200,302,400,403,404,405,419,422,429,500,503"  # trainings.store
curl_post_csrf "/trainings/status" "200,302,400,403,404,405,419,422,429,500,503"  # trainings.status

# ── transfers ──────────────────────────────────
curl_post_csrf "/transfers" "200,302,400,403,404,405,419,422,429,500,503"  # transfers.store

# ── travels ──────────────────────────────────
curl_post_csrf "/travels" "200,302,400,403,404,405,419,422,429,500,503"  # travels.store

# ── trial-balances ──────────────────────────────────
curl_post_csrf "/trial-balances/export" "200,302,400,403,404,405,419,422,429,500,503"  # reports.trial.balance.export

# ── twilio-settings ──────────────────────────────────
curl_post_csrf "/twilio-settings" "200,302,400,403,404,405,419,422,429,500,503"  # twilio.setting

# ── two-factor-challenge ──────────────────────────────────
curl_post_csrf "/two-factor-challenge" "200,302,400,403,404,405,419,422,429,500,503"  # two-factor.login.store

# ── user ──────────────────────────────────
curl_post_csrf "/user/confirm-password" "200,302,400,403,404,405,419,422,429,500,503"  # password.confirm.store

# ── user-reset-passwords ──────────────────────────────────
curl_post_csrf "/user-reset-passwords/1" "200,302,400,403,404,405,419,422,429,500,503"  # users.password.update

# ── users ──────────────────────────────────
curl_post_csrf "/users" "200,302,400,403,404,405,419,422,429,500,503"  # users.store
curl_post_csrf "/users/confirmed-two-factor-authentication" "200,302,400,403,404,405,419,422,429,500,503"  # two-factor.confirm
curl_post_csrf "/users/two-factor-authentication" "200,302,400,403,404,405,419,422,429,500,503"  # two-factor.enable
curl_post_csrf "/users/two-factor-recovery-codes" "200,302,400,403,404,405,419,422,429,500,503"

# ── vendors ──────────────────────────────────
curl_post_csrf "/vendors" "200,302,400,403,404,405,419,422,429,500,503"  # vendors.store
curl_post_csrf "/vendors/imports/index" "200,302,400,403,404,405,419,422,429,500,503"  # vendors.import

# ── warehouse-empty-cart ──────────────────────────────────
curl_post_csrf "/warehouse-empty-cart" "200,302,400,403,404,405,419,422,429,500,503"  # warehouse-empty-cart

# ── warehouse_transfers ──────────────────────────────────
curl_post_csrf "/warehouse_transfers" "200,302,400,403,404,405,419,422,429,500,503"  # warehouse_transfers.store
curl_post_csrf "/warehouse_transfers/get-product" "200,302,400,403,404,405,419,422,429,500,503"  # warehouse_transfers.get.product
curl_post_csrf "/warehouse_transfers/get-quantity" "200,302,400,403,404,405,419,422,429,500,503"  # warehouse_transfers.get.quantity

# ── warehouses ──────────────────────────────────
curl_post_csrf "/warehouses" "200,302,400,403,404,405,419,422,429,500,503"  # warehouses.store

# ── warnings ──────────────────────────────────
curl_post_csrf "/warnings" "200,302,400,403,404,405,419,422,429,500,503"  # warnings.store

# ── webhook-settings ──────────────────────────────────
curl_post_csrf "/webhook-settings" "200,302,400,403,404,405,419,422,429,500,503"  # webhooks.settings
curl_post_csrf "/webhook-settings/store" "200,302,400,403,404,405,419,422,429,500,503"  # webhooks.store
curl_post_csrf "/webhook-settings/1/edit" "200,302,400,403,404,405,419,422,429,500,503"  # webhooks.update

# ── zoom-meetings ──────────────────────────────────
curl_post_csrf "/zoom-meetings/get_zoom_meeting_data" "200,302,400,403,404,405,419,422,429,500,503"  # zoom_meetings.get_zoom_meeting_data

# ── zoom-settings ──────────────────────────────────
curl_post_csrf "/zoom-settings" "200,302,400,403,404,405,419,422,429,500,503"  # zoom.settings

# ── zoom_meetings ──────────────────────────────────
curl_post_csrf "/zoom_meetings" "200,302,400,403,404,405,419,422,429,500,503"  # zoom_meetings.store
curl_post_csrf "/zoom_meetings/projects/selects/1" "200,302,400,403,404,405,419,422,429,500,503"  # zoom_meetings.projects.select

log_info "POST route tests complete."
summary
