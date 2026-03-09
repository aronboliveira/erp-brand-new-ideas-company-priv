#!/usr/bin/env bash
# ─────────────────────────────────────────────────────
# 01_get_routes.sh  –  GET route coverage (all 846 GET routes)
# Auto-generated — do not edit by hand.
# ─────────────────────────────────────────────────────
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=_common.sh
source "$SCRIPT_DIR/_common.sh"

log_info "=== GET ROUTE TESTS (846 routes) ==="

# Ensure authenticated session
do_login

# ── .well-knowns ──────────────────────────────────
curl_test GET "/.well-knowns/appspecifics/com.chrome.devtools.json" "200,302,301,403,404"

# ── _debugbars ──────────────────────────────────
curl_test GET "/_debugbars/assets/javascript" "200,302,301,403,404"  # debugbar.assets.js
curl_test GET "/_debugbars/assets/stylesheets" "200,302,301,403,404"  # debugbar.assets.css
curl_test GET "/_debugbars/clockworks/1" "200,302,301,403,404"  # debugbar.clockwork
curl_test GET "/_debugbars/open" "200,302,301,403,404"  # debugbar.openhandler

# ── _ignitions ──────────────────────────────────
curl_test GET "/_ignitions/health-check" "200,302,301,403,404"  # ignition.healthCheck

# ── about_us ──────────────────────────────────
curl_test GET "/about_us" "200,302,301,403,404"  # about_us

# ── account-dashboard ──────────────────────────────────
curl_test GET "/account-dashboard" "200,302,301,403,404"  # dashboard

# ── account_assets ──────────────────────────────────
curl_test GET "/account_assets" "200,302,301,403,404"  # account_assets.index
curl_test GET "/account_assets/create" "200,302,301,403,404"  # account_assets.create
curl_test GET "/account_assets/1" "200,302,301,403,404"  # account_assets.show
curl_test GET "/account_assets/1/edit" "200,302,301,403,404"  # account_assets.edit

# ── account_statements ──────────────────────────────────
curl_test GET "/account_statements/export" "200,302,301,403,404"  # account_statements.export

# ── add-to-carts ──────────────────────────────────
curl_test GET "/add-to-carts/1/1" "200,302,301,403,404"

# ── allowance_options ──────────────────────────────────
curl_test GET "/allowance_options" "200,302,301,403,404"  # allowance_options.index
curl_test GET "/allowance_options/create" "200,302,301,403,404"  # allowance_options.create
curl_test GET "/allowance_options/1" "200,302,301,403,404"  # allowance_options.show
curl_test GET "/allowance_options/1/edit" "200,302,301,403,404"  # allowance_options.edit

# ── allowances ──────────────────────────────────
curl_test GET "/allowances" "200,302,301,403,404"  # allowances.index
curl_test GET "/allowances/create" "200,302,301,403,404"  # allowances.create
curl_test GET "/allowances/creates/1" "200,302,301,403,404"  # allowances.create
curl_test GET "/allowances/1" "200,302,301,403,404"  # allowances.show
curl_test GET "/allowances/1/edit" "200,302,301,403,404"  # allowances.edit

# ── announcement ──────────────────────────────────
curl_test GET "/announcement" "200,302,301,403,404"  # announcement.index

# ── announcements ──────────────────────────────────
curl_test GET "/announcements/create" "200,302,301,403,404"  # announcement.create
curl_test GET "/announcements/1" "200,302,301,403,404"  # announcement.show
curl_test GET "/announcements/1/edit" "200,302,301,403,404"  # announcement.edit

# ── api ──────────────────────────────────
curl_test GET "/api/landingpage" "200,302,301,403,404"

# ── apis ──────────────────────────────────
curl_test GET "/apis/get-projects" "200,302,301,403,404"  # projects.index

# ── appraisals ──────────────────────────────────
curl_test GET "/appraisals" "200,302,301,403,404"  # appraisals.index
curl_test GET "/appraisals/create" "200,302,301,403,404"  # appraisals.create
curl_test GET "/appraisals/1" "200,302,301,403,404"  # appraisals.show
curl_test GET "/appraisals/1/edit" "200,302,301,403,404"  # appraisals.edit

# ── attendances ──────────────────────────────────
curl_test GET "/attendances/imports/file" "200,302,301,403,404"  # attendance.file.import

# ── award_types ──────────────────────────────────
curl_test GET "/award_types" "200,302,301,403,404"  # award_types.index
curl_test GET "/award_types/create" "200,302,301,403,404"  # award_types.create
curl_test GET "/award_types/1" "200,302,301,403,404"  # award_types.show
curl_test GET "/award_types/1/edit" "200,302,301,403,404"  # award_types.edit

# ── awards ──────────────────────────────────
curl_test GET "/awards" "200,302,301,403,404"  # awards.index
curl_test GET "/awards/create" "200,302,301,403,404"  # awards.create
curl_test GET "/awards/1" "200,302,301,403,404"  # awards.show
curl_test GET "/awards/1/edit" "200,302,301,403,404"  # awards.edit

# ── bank_accounts ──────────────────────────────────
curl_test GET "/bank_accounts" "200,302,301,403,404"  # bank_accounts.index
curl_test GET "/bank_accounts/create" "200,302,301,403,404"  # bank_accounts.create
curl_test GET "/bank_accounts/1" "200,302,301,403,404"  # bank_accounts.show
curl_test GET "/bank_accounts/1/edit" "200,302,301,403,404"  # bank_accounts.edit

# ── bank_transfers ──────────────────────────────────
curl_test GET "/bank_transfers" "200,302,301,403,404"  # bank_transfers.index
curl_test GET "/bank_transfers/create" "200,302,301,403,404"  # bank_transfers.create
curl_test GET "/bank_transfers/index" "200,302,301,403,404"  # bank_transfers.index
curl_test GET "/bank_transfers/1" "200,302,301,403,404"  # bank_transfers.show
curl_test GET "/bank_transfers/1/edit" "200,302,301,403,404"  # bank_transfers.edit

# ── bills ──────────────────────────────────
curl_test GET "/bills" "200,302,301,403,404"  # bills.index
curl_test GET "/bills/create" "200,302,301,403,404"  # bills.create
curl_test GET "/bills/export" "200,302,301,403,404"  # bills.export
curl_test GET "/bills/pdfs/1" "200,302,301,403,404"  # bills.pdf
curl_test GET "/bills/previews/1/1" "200,302,301,403,404"  # bills.preview
curl_test GET "/bills/1" "200,302,301,403,404"  # bills.show
curl_test GET "/bills/1/edit" "200,302,301,403,404"  # bills.edit

# ── billscreates ──────────────────────────────────
curl_test GET "/billscreates/1" "200,302,301,403,404"  # bills.create

# ── billsindex ──────────────────────────────────
curl_test GET "/billsindex" "200,302,301,403,404"  # bills.index

# ── billsitems ──────────────────────────────────
curl_test GET "/billsitems" "200,302,301,403,404"  # bills.items

# ── bills{id} ──────────────────────────────────
curl_test GET "/bills1/debit-note" "200,302,301,403,404"  # bills.debit.note
curl_test GET "/bills1/debit_notes/edits/1" "200,302,301,403,404"  # bills.edit.debit.note
curl_test GET "/bills1/duplicate" "200,302,301,403,404"  # bills.duplicate
curl_test GET "/bills1/payment" "200,302,301,403,404"  # bills.payment
curl_test GET "/bills1/resent" "200,302,301,403,404"  # bills.resent
curl_test GET "/bills1/sent" "200,302,301,403,404"  # bills.sent
curl_test GET "/bills1/shippings/print" "200,302,301,403,404"  # bills.shipping.print

# ── branches ──────────────────────────────────
curl_test GET "/branches" "200,302,301,403,404"  # branches.index
curl_test GET "/branches/create" "200,302,301,403,404"  # branches.create
curl_test GET "/branches/1" "200,302,301,403,404"  # branches.show
curl_test GET "/branches/1/edit" "200,302,301,403,404"  # branches.edit

# ── broadcastings ──────────────────────────────────
curl_test GET "/broadcastings/auth" "200,302,301,403,404"

# ── budgets ──────────────────────────────────
curl_test GET "/budgets" "200,302,301,403,404"  # budgets.index
curl_test GET "/budgets/create" "200,302,301,403,404"  # budgets.create
curl_test GET "/budgets/1" "200,302,301,403,404"  # budgets.show
curl_test GET "/budgets/1/edit" "200,302,301,403,404"  # budgets.edit

# ── bug_status ──────────────────────────────────
curl_test GET "/bug_status" "200,302,301,403,404"  # bug_status.index

# ── bug_statuses ──────────────────────────────────
curl_test GET "/bug_statuses/create" "200,302,301,403,404"  # bug_status.create
curl_test GET "/bug_statuses/1" "200,302,301,403,404"  # bug_status.show
curl_test GET "/bug_statuses/1/edit" "200,302,301,403,404"  # bug_status.edit

# ── bugs_reports ──────────────────────────────────
curl_test GET "/bugs_reports/1" "200,302,301,403,404"  # projects.bugs.view

# ── calendars ──────────────────────────────────
curl_test GET "/calendars/1/show" "200,302,301,403,404"  # projects.tasks.calendar.show
curl_test GET "/calendars/1/1" "200,302,301,403,404"  # projects.tasks.calendar

# ── candidates-job-applications ──────────────────────────────────
curl_test GET "/candidates-job-applications" "200,302,301,403,404"  # jobs.application.candidate

# ── careers ──────────────────────────────────
curl_test GET "/careers/1/1" "200,302,301,403,404"  # careers

# ── cashfrees ──────────────────────────────────
curl_test GET "/cashfrees/payments/success" "200,302,301,403,404"  # cashfree.payment.success

# ── change-languages ──────────────────────────────────
curl_test GET "/change-languages/1" "200,302,301,403,404"  # languages.change

# ── changes ──────────────────────────────────
curl_test GET "/changes/mode" "200,302,301,403,404"  # change.mode

# ── chart_of_accounts ──────────────────────────────────
curl_test GET "/chart_of_accounts" "200,302,301,403,404"  # chart_of_accounts.index
curl_test GET "/chart_of_accounts/create" "200,302,301,403,404"  # chart_of_accounts.create
curl_test GET "/chart_of_accounts/1" "200,302,301,403,404"  # chart_of_accounts.show
curl_test GET "/chart_of_accounts/1/edit" "200,302,301,403,404"  # chart_of_accounts.edit

# ── chats ──────────────────────────────────
curl_test GET "/chats" "200,302,301,403,404"  # chats
curl_test GET "/chats/downloads/1" "200,302,301,403,404"  # attachments.download
curl_test GET "/chats/downloads/1" "200,302,301,403,404"  # chatify.download.safe
curl_test GET "/chats/groups/1" "200,302,301,403,404"  # group
curl_test GET "/chats/1" "200,302,301,403,404"  # user

# ── checkuserexists ──────────────────────────────────
curl_test GET "/checkuserexists" "200,302,301,403,404"  # users.exists

# ── client-reset-passwords ──────────────────────────────────
curl_test GET "/client-reset-passwords/1" "200,302,301,403,404"  # clients.reset

# ── clients ──────────────────────────────────
curl_test GET "/clients" "200,302,301,403,404"  # clients.index
curl_test GET "/clients/create" "200,302,301,403,404"  # clients.create
curl_test GET "/clients/1" "200,302,301,403,404"  # clients.show
curl_test GET "/clients/1/edit" "200,302,301,403,404"  # clients.edit

# ── commissions ──────────────────────────────────
curl_test GET "/commissions" "200,302,301,403,404"  # commissions.index
curl_test GET "/commissions/create" "200,302,301,403,404"  # commissions.create
curl_test GET "/commissions/creates/1" "200,302,301,403,404"  # commissions.create
curl_test GET "/commissions/1" "200,302,301,403,404"  # commissions.show
curl_test GET "/commissions/1/edit" "200,302,301,403,404"  # commissions.edit

# ── company_policies ──────────────────────────────────
curl_test GET "/company_policies" "200,302,301,403,404"  # company_policies.index
curl_test GET "/company_policies/create" "200,302,301,403,404"  # company_policies.create
curl_test GET "/company_policies/1" "200,302,301,403,404"  # company_policies.show
curl_test GET "/company_policies/1/edit" "200,302,301,403,404"  # company_policies.edit

# ── competencies ──────────────────────────────────
curl_test GET "/competencies" "200,302,301,403,404"  # competencies.index
curl_test GET "/competencies/create" "200,302,301,403,404"  # competencies.create
curl_test GET "/competencies/1" "200,302,301,403,404"  # competencies.show
curl_test GET "/competencies/1/edit" "200,302,301,403,404"  # competencies.edit

# ── complaints ──────────────────────────────────
curl_test GET "/complaints" "200,302,301,403,404"  # complaints.index
curl_test GET "/complaints/create" "200,302,301,403,404"  # complaints.create
curl_test GET "/complaints/1" "200,302,301,403,404"  # complaints.show
curl_test GET "/complaints/1/edit" "200,302,301,403,404"  # complaints.edit

# ── confirm-password ──────────────────────────────────
curl_test GET "/confirm-password" "200,302,301,403,404"  # password.confirm

# ── contract_types ──────────────────────────────────
curl_test GET "/contract_types" "200,302,301,403,404"  # contract_types.index
curl_test GET "/contract_types/create" "200,302,301,403,404"  # contract_types.create
curl_test GET "/contract_types/1" "200,302,301,403,404"  # contract_types.show
curl_test GET "/contract_types/1/edit" "200,302,301,403,404"  # contract_types.edit

# ── contracts ──────────────────────────────────
curl_test GET "/contracts" "200,302,301,403,404"  # contracts.index
curl_test GET "/contracts/clients/selects/1" "200,302,301,403,404"  # contracts.clients.select
curl_test GET "/contracts/copies/1" "200,302,301,403,404"  # contracts.copy
curl_test GET "/contracts/create" "200,302,301,403,404"  # contracts.create
curl_test GET "/contracts/grid" "200,302,301,403,404"  # contracts.grid
curl_test GET "/contracts/pdfs/1" "200,302,301,403,404"  # contracts.download.pdf
curl_test GET "/contracts/1" "200,302,301,403,404"  # contracts.show
curl_test GET "/contracts/1/edit" "200,302,301,403,404"  # contracts.edit
curl_test GET "/contracts/1/description" "200,302,301,403,404"  # contracts.description
curl_test GET "/contracts/1/files/1" "200,302,301,403,404"  # contracts.file.download
curl_test GET "/contracts/1/get_contract" "200,302,301,403,404"  # contracts.get
curl_test GET "/contracts/1/mail" "200,302,301,403,404"  # contracts.send.mail

# ── cookie-consent ──────────────────────────────────
curl_test GET "/cookie-consent" "200,302,301,403,404"  # cookie-consent

# ── coupons ──────────────────────────────────
curl_test GET "/coupons" "200,302,301,403,404"  # coupons.index
curl_test GET "/coupons/apply" "200,302,301,403,404"  # coupons.apply
curl_test GET "/coupons/create" "200,302,301,403,404"  # coupons.create
curl_test GET "/coupons/1" "200,302,301,403,404"  # coupons.show
curl_test GET "/coupons/1/edit" "200,302,301,403,404"  # coupons.edit

# ── create-language ──────────────────────────────────
curl_test GET "/create-language" "200,302,301,403,404"  # languages.create

# ── credit-notes ──────────────────────────────────
curl_test GET "/credit-notes" "200,302,301,403,404"  # credit.note

# ── credit_notes ──────────────────────────────────
curl_test GET "/credit_notes/invoice" "200,302,301,403,404"  # invoices.get

# ── crm-dashboard ──────────────────────────────────
curl_test GET "/crm-dashboard" "200,302,301,403,404"  # crm.dashboard

# ── custom-credit-note ──────────────────────────────────
curl_test GET "/custom-credit-note" "200,302,301,403,404"  # invoices.custom.credit.note

# ── custom-debit-note ──────────────────────────────────
curl_test GET "/custom-debit-note" "200,302,301,403,404"  # bills.custom.debit.note

# ── custom-question ──────────────────────────────────
curl_test GET "/custom-question" "200,302,301,403,404"  # custom_questions.index

# ── custom-questions ──────────────────────────────────
curl_test GET "/custom-questions/create" "200,302,301,403,404"  # custom-question.create
curl_test GET "/custom-questions/create" "200,302,301,403,404"  # custom_questions.create
curl_test GET "/custom-questions/1" "200,302,301,403,404"  # custom-question.show
curl_test GET "/custom-questions/1" "200,302,301,403,404"  # custom_questions.show
curl_test GET "/custom-questions/1/edit" "200,302,301,403,404"  # custom-question.edit
curl_test GET "/custom-questions/1/edit" "200,302,301,403,404"  # custom_questions.edit

# ── custom_fields ──────────────────────────────────
curl_test GET "/custom_fields" "200,302,301,403,404"  # custom_fields.index
curl_test GET "/custom_fields/create" "200,302,301,403,404"  # custom_fields.create
curl_test GET "/custom_fields/1" "200,302,301,403,404"  # custom_fields.show
curl_test GET "/custom_fields/1/edit" "200,302,301,403,404"  # custom_fields.edit

# ── custom_pages ──────────────────────────────────
curl_test GET "/custom_pages" "200,302,301,403,404"  # custom_pages.index
curl_test GET "/custom_pages/create" "200,302,301,403,404"  # custom_pages.create
curl_test GET "/custom_pages/delete/1" "200,302,301,403,404"  # custom_pages.delete
curl_test GET "/custom_pages/edit/1" "200,302,301,403,404"  # custom_pages.edit
curl_test GET "/custom_pages/1" "200,302,301,403,404"  # custom_pages.show
curl_test GET "/custom_pages/1/edit" "200,302,301,403,404"  # custom_pages.edit

# ── customers ──────────────────────────────────
curl_test GET "/customers" "200,302,301,403,404"  # customers.index
curl_test GET "/customers/create" "200,302,301,403,404"  # customers.create
curl_test GET "/customers/export" "200,302,301,403,404"  # customers.export
curl_test GET "/customers/imports/file" "200,302,301,403,404"  # customers.file.import
curl_test GET "/customers/invoices/1" "200,302,301,403,404"  # invoices.link.copy
curl_test GET "/customers/proposals/1" "200,302,301,403,404"  # proposals.link.copy
curl_test GET "/customers/1" "200,302,301,403,404"  # customers.show
curl_test GET "/customers/1/edit" "200,302,301,403,404"  # customers.edit
curl_test GET "/customers/1/show" "200,302,301,403,404"  # customers.show

# ── dashboard ──────────────────────────────────
curl_test GET "/dashboard" "200,302,301,403,404"  # client.dashboard.view

# ── dashboard-view ──────────────────────────────────
curl_test GET "/dashboard-view" "200,302,301,403,404"  # dashboard.view

# ── deals ──────────────────────────────────
curl_test GET "/deals" "200,302,301,403,404"  # deals.index
curl_test GET "/deals/create" "200,302,301,403,404"  # deals.create
curl_test GET "/deals/list" "200,302,301,403,404"  # deals.list
curl_test GET "/deals/1" "200,302,301,403,404"  # deals.show
curl_test GET "/deals/1/edit" "200,302,301,403,404"  # deals.edit
curl_test GET "/deals/1/call" "200,302,301,403,404"  # deals.calls.create
curl_test GET "/deals/1/calls/1/edit" "200,302,301,403,404"  # deals.calls.edit
curl_test GET "/deals/1/clients" "200,302,301,403,404"  # deals.clients.edit
curl_test GET "/deals/1/discussions" "200,302,301,403,404"  # deals.discussions.create
curl_test GET "/deals/1/email" "200,302,301,403,404"  # deals.emails.create
curl_test GET "/deals/1/files/1" "200,302,301,403,404"  # deals.file.download
curl_test GET "/deals/1/labels" "200,302,301,403,404"  # deals.labels
curl_test GET "/deals/1/permissions/1" "200,302,301,403,404"  # deals.client.permission
curl_test GET "/deals/1/products" "200,302,301,403,404"  # deals.products.edit
curl_test GET "/deals/1/sources" "200,302,301,403,404"  # deals.sources.edit
curl_test GET "/deals/1/tasks" "200,302,301,403,404"  # deals.tasks.create
curl_test GET "/deals/1/tasks/1/edit" "200,302,301,403,404"  # deals.tasks.edit
curl_test GET "/deals/1/tasks/1/show" "200,302,301,403,404"  # deals.tasks.show
curl_test GET "/deals/1/users" "200,302,301,403,404"  # deals.users.edit

# ── debit_notes ──────────────────────────────────
curl_test GET "/debit_notes" "200,302,301,403,404"  # debit.note
curl_test GET "/debit_notes/bill" "200,302,301,403,404"  # bills.get

# ── deduction_options ──────────────────────────────────
curl_test GET "/deduction_options" "200,302,301,403,404"  # deduction_options.index
curl_test GET "/deduction_options/create" "200,302,301,403,404"  # deduction_options.create
curl_test GET "/deduction_options/1" "200,302,301,403,404"  # deduction_options.show
curl_test GET "/deduction_options/1/edit" "200,302,301,403,404"  # deduction_options.edit

# ── departments ──────────────────────────────────
curl_test GET "/departments" "200,302,301,403,404"  # departments.index
curl_test GET "/departments/create" "200,302,301,403,404"  # departments.create
curl_test GET "/departments/1" "200,302,301,403,404"  # departments.show
curl_test GET "/departments/1/edit" "200,302,301,403,404"  # departments.edit

# ── designations ──────────────────────────────────
curl_test GET "/designations" "200,302,301,403,404"  # designations.index
curl_test GET "/designations/create" "200,302,301,403,404"  # designations.create
curl_test GET "/designations/1" "200,302,301,403,404"  # designations.show
curl_test GET "/designations/1/edit" "200,302,301,403,404"  # designations.edit

# ── discover ──────────────────────────────────
curl_test GET "/discover" "200,302,301,403,404"  # discover.index
curl_test GET "/discover/create" "200,302,301,403,404"  # discover.create
curl_test GET "/discover/delete/1" "200,302,301,403,404"  # discover.delete
curl_test GET "/discover/edit/1" "200,302,301,403,404"  # discover.edit
curl_test GET "/discover/1" "200,302,301,403,404"  # discover.show
curl_test GET "/discover/1/edit" "200,302,301,403,404"  # discover.edit

# ── document_uploads ──────────────────────────────────
curl_test GET "/document_uploads" "200,302,301,403,404"  # document_uploads.index
curl_test GET "/document_uploads/create" "200,302,301,403,404"  # document_uploads.create
curl_test GET "/document_uploads/1" "200,302,301,403,404"  # document_uploads.show
curl_test GET "/document_uploads/1/edit" "200,302,301,403,404"  # document_uploads.edit

# ── documents ──────────────────────────────────
curl_test GET "/documents" "200,302,301,403,404"  # documents.index
curl_test GET "/documents/create" "200,302,301,403,404"  # documents.create
curl_test GET "/documents/1" "200,302,301,403,404"  # documents.show
curl_test GET "/documents/1/edit" "200,302,301,403,404"  # documents.edit

# ── edit-profile ──────────────────────────────────
curl_test GET "/edit-profile" "200,302,301,403,404"  # users.account.update

# ── email ──────────────────────────────────
curl_test GET "/email/verify" "200,302,301,403,404"  # verification.notice
curl_test GET "/email/verify/1/1" "200,302,301,403,404"  # verification.verify

# ── email_template ──────────────────────────────────
curl_test GET "/email_template" "200,302,301,403,404"  # email_template.index

# ── email_template_langs ──────────────────────────────────
curl_test GET "/email_template_langs/1/1" "200,302,301,403,404"  # emails.manage.language

# ── email_template_store ──────────────────────────────────
curl_test GET "/email_template_store" "200,302,301,403,404"  # emails.status.language

# ── email_template_stores ──────────────────────────────────
curl_test GET "/email_template_stores/1" "200,302,301,403,404"  # emails.store.language

# ── email_templates ──────────────────────────────────
curl_test GET "/email_templates/create" "200,302,301,403,404"  # email_template.create
curl_test GET "/email_templates/1" "200,302,301,403,404"  # email_template.show
curl_test GET "/email_templates/1/edit" "200,302,301,403,404"  # email_template.edit

# ── employee-profile ──────────────────────────────────
curl_test GET "/employee-profile" "200,302,301,403,404"  # employees.profile

# ── employee_attendances ──────────────────────────────────
curl_test GET "/employee_attendances" "200,302,301,403,404"  # employee_attendances.index
curl_test GET "/employee_attendances/bulk-attendance" "200,302,301,403,404"  # employee_attendances.bulkAttendance
curl_test GET "/employee_attendances/create" "200,302,301,403,404"  # employee_attendances.create
curl_test GET "/employee_attendances/1" "200,302,301,403,404"  # employee_attendances.show
curl_test GET "/employee_attendances/1/edit" "200,302,301,403,404"  # employee_attendances.edit

# ── employees ──────────────────────────────────
curl_test GET "/employees" "200,302,301,403,404"  # employees.index
curl_test GET "/employees/create" "200,302,301,403,404"  # employees.create
curl_test GET "/employees/docs/1" "200,302,301,403,404"  # joining_letter.download.doc
curl_test GET "/employees/exp-docs/1" "200,302,301,403,404"  # exp.download.doc
curl_test GET "/employees/exp-pdfs/1" "200,302,301,403,404"  # exp.download.pdf
curl_test GET "/employees/export" "200,302,301,403,404"  # employees.export
curl_test GET "/employees/imports/file" "200,302,301,403,404"  # employees.file.import
curl_test GET "/employees/noc-docs/1" "200,302,301,403,404"  # noc.download.doc
curl_test GET "/employees/noc-pdfs/1" "200,302,301,403,404"  # noc.download.pdf
curl_test GET "/employees/pdfs/1" "200,302,301,403,404"  # joining_letter.download.pdf
curl_test GET "/employees/salaries/1" "200,302,301,403,404"  # employees.salary.basic
curl_test GET "/employees/salary" "200,302,301,403,404"  # employees.salary
curl_test GET "/employees/1" "200,302,301,403,404"  # employees.show
curl_test GET "/employees/1/edit" "200,302,301,403,404"  # employees.edit
curl_test GET "/employees/1/leaves/1/1/1/1" "200,302,301,403,404"  # reports.employee.leave

# ── events ──────────────────────────────────
curl_test GET "/events" "200,302,301,403,404"  # events.index
curl_test GET "/events/create" "200,302,301,403,404"  # events.create
curl_test GET "/events/get_dashboard_event_data" "200,302,301,403,404"  # events.get_dashboard_event_data
curl_test GET "/events/get_event_data" "200,302,301,403,404"  # events.get_event_data
curl_test GET "/events/1" "200,302,301,403,404"  # events.show
curl_test GET "/events/1/edit" "200,302,301,403,404"  # events.edit

# ── expense-list ──────────────────────────────────
curl_test GET "/expense-list" "200,302,301,403,404"  # expenses.list

# ── expenses ──────────────────────────────────
curl_test GET "/expenses" "200,302,301,403,404"  # expenses.index
curl_test GET "/expenses/create" "200,302,301,403,404"  # expenses.create
curl_test GET "/expenses/creates/1" "200,302,301,403,404"  # expenses.create
curl_test GET "/expenses/customer" "200,302,301,403,404"  # expenses.customer
curl_test GET "/expenses/index" "200,302,301,403,404"  # expenses.index
curl_test GET "/expenses/items" "200,302,301,403,404"  # expenses.items
curl_test GET "/expenses/pdfs/1" "200,302,301,403,404"  # expenses.pdf
curl_test GET "/expenses/1" "200,302,301,403,404"  # expenses.show
curl_test GET "/expenses/1/edit" "200,302,301,403,404"  # expenses.edit
curl_test GET "/expenses/1/payment" "200,302,301,403,404"  # expenses.payment

# ── faqs ──────────────────────────────────
curl_test GET "/faqs" "200,302,301,403,404"  # faqs.index
curl_test GET "/faqs/create" "200,302,301,403,404"  # faqs.create
curl_test GET "/faqs/delete/1" "200,302,301,403,404"  # faqs.delete
curl_test GET "/faqs/edit/1" "200,302,301,403,404"  # faqs.edit
curl_test GET "/faqs/1" "200,302,301,403,404"  # faqs.show
curl_test GET "/faqs/1/edit" "200,302,301,403,404"  # faqs.edit

# ── features ──────────────────────────────────
curl_test GET "/features" "200,302,301,403,404"  # features.index
curl_test GET "/features/create" "200,302,301,403,404"  # features.create
curl_test GET "/features/delete/1" "200,302,301,403,404"  # features.delete
curl_test GET "/features/edit/1" "200,302,301,403,404"  # features.edit
curl_test GET "/features/update/1" "200,302,301,403,404"  # features.update
curl_test GET "/features/1" "200,302,301,403,404"  # features.show
curl_test GET "/features/1/edit" "200,302,301,403,404"  # features.edit

# ── forgot-password ──────────────────────────────────
curl_test GET "/forgot-password/1" "200,302,301,403,404"  # password.request

# ── form_builders ──────────────────────────────────
curl_test GET "/form_builders" "200,302,301,403,404"  # form_builders.index
curl_test GET "/form_builders/create" "200,302,301,403,404"  # form_builders.create
curl_test GET "/form_builders/1" "200,302,301,403,404"  # form_builders.show
curl_test GET "/form_builders/1/edit" "200,302,301,403,404"  # form_builders.edit
curl_test GET "/form_builders/1/field" "200,302,301,403,404"  # forms.fields.create
curl_test GET "/form_builders/1/fields/1/edit" "200,302,301,403,404"  # forms.fields.edit
curl_test GET "/form_builders/1/fields/1/show" "200,302,301,403,404"  # forms.fields.show

# ── forms ──────────────────────────────────
curl_test GET "/forms/binds/1" "200,302,301,403,404"  # forms.fields.bind
curl_test GET "/forms/responses/1" "200,302,301,403,404"  # forms.response
curl_test GET "/forms/responses/1/detail" "200,302,301,403,404"  # forms.response.detail
curl_test GET "/forms/1" "200,302,301,403,404"  # forms.view

# ── fortify-forgot-password ──────────────────────────────────
curl_test GET "/fortify-forgot-password" "200,302,301,403,404"  # fortify.password.request

# ── fortify-login ──────────────────────────────────
curl_test GET "/fortify-login" "200,302,301,403,404"  # fortify.login

# ── fortify-register ──────────────────────────────────
curl_test GET "/fortify-register" "200,302,301,403,404"  # fortify.register

# ── generates ──────────────────────────────────
curl_test GET "/generates/1" "200,302,301,403,404"  # generate

# ── get-projects ──────────────────────────────────
curl_test GET "/get-projects/1" "200,302,301,403,404"  # projects.by.user.id

# ── goal_trackings ──────────────────────────────────
curl_test GET "/goal_trackings" "200,302,301,403,404"  # goal_trackings.index
curl_test GET "/goal_trackings/create" "200,302,301,403,404"  # goal_trackings.create
curl_test GET "/goal_trackings/1" "200,302,301,403,404"  # goal_trackings.show
curl_test GET "/goal_trackings/1/edit" "200,302,301,403,404"  # goal_trackings.edit

# ── goal_types ──────────────────────────────────
curl_test GET "/goal_types" "200,302,301,403,404"  # goal_types.index
curl_test GET "/goal_types/create" "200,302,301,403,404"  # goal_types.create
curl_test GET "/goal_types/1" "200,302,301,403,404"  # goal_types.show
curl_test GET "/goal_types/1/edit" "200,302,301,403,404"  # goal_types.edit

# ── goals ──────────────────────────────────
curl_test GET "/goals" "200,302,301,403,404"  # goals.index
curl_test GET "/goals/create" "200,302,301,403,404"  # goals.create
curl_test GET "/goals/1" "200,302,301,403,404"  # goals.show
curl_test GET "/goals/1/edit" "200,302,301,403,404"  # goals.edit

# ── grammars ──────────────────────────────────
curl_test GET "/grammars/1" "200,302,301,403,404"  # grammar

# ── holiday-calendar ──────────────────────────────────
curl_test GET "/holiday-calendar" "200,302,301,403,404"  # holidays.calendar

# ── holidays ──────────────────────────────────
curl_test GET "/holidays" "200,302,301,403,404"  # holidays.index
curl_test GET "/holidays/create" "200,302,301,403,404"  # holidays.create
curl_test GET "/holidays/data" "200,302,301,403,404"  # holidays.get_holiday_data
curl_test GET "/holidays/1" "200,302,301,403,404"  # holidays.show
curl_test GET "/holidays/1/edit" "200,302,301,403,404"  # holidays.edit

# ── home ──────────────────────────────────
curl_test GET "/home" "200,302,301,403,404"  # home_section.index

# ── home_section ──────────────────────────────────
curl_test GET "/home_section" "200,302,301,403,404"  # home_section.index
curl_test GET "/home_section/create" "200,302,301,403,404"  # home_section.create
curl_test GET "/home_section/1" "200,302,301,403,404"  # home_section.show
curl_test GET "/home_section/1/edit" "200,302,301,403,404"  # home_section.edit

# ── hrm-dashboard ──────────────────────────────────
curl_test GET "/hrm-dashboard" "200,302,301,403,404"  # hrm.dashboard

# ── indicators ──────────────────────────────────
curl_test GET "/indicators" "200,302,301,403,404"  # indicators.index
curl_test GET "/indicators/create" "200,302,301,403,404"  # indicators.create
curl_test GET "/indicators/1" "200,302,301,403,404"  # indicators.show
curl_test GET "/indicators/1/edit" "200,302,301,403,404"  # indicators.edit

# ── install ──────────────────────────────────
curl_test GET "/install" "200,302,301,403,404"  # LaravelInstaller::welcome

# ── installs ──────────────────────────────────
curl_test GET "/installs/database" "200,302,301,403,404"  # LaravelInstaller::database
curl_test GET "/installs/environment" "200,302,301,403,404"  # LaravelInstaller::environment
curl_test GET "/installs/environments/classic" "200,302,301,403,404"  # LaravelInstaller::environmentClassic
curl_test GET "/installs/environments/wizard" "200,302,301,403,404"  # LaravelInstaller::environmentWizard
curl_test GET "/installs/final" "200,302,301,403,404"  # LaravelInstaller::final
curl_test GET "/installs/permissions" "200,302,301,403,404"  # LaravelInstaller::permissions
curl_test GET "/installs/requirements" "200,302,301,403,404"  # LaravelInstaller::requirements

# ── interview-schedule ──────────────────────────────────
curl_test GET "/interview-schedule" "200,302,301,403,404"  # interview-schedule.index

# ── interview-schedules ──────────────────────────────────
curl_test GET "/interview-schedules/create" "200,302,301,403,404"  # interview-schedule.create
curl_test GET "/interview-schedules/1" "200,302,301,403,404"  # interview-schedule.show
curl_test GET "/interview-schedules/1/edit" "200,302,301,403,404"  # interview-schedule.edit

# ── interview_schedules ──────────────────────────────────
curl_test GET "/interview_schedules/creates/1" "200,302,301,403,404"  # interview_schedules.create
curl_test GET "/interview_schedules/data" "200,302,301,403,404"  # interview_schedules.get_interview_data

# ── invite-project-members ──────────────────────────────────
curl_test GET "/invite-project-members/1" "200,302,301,403,404"  # projects.invite.member.view

# ── invoices ──────────────────────────────────
curl_test GET "/invoices" "200,302,301,403,404"  # invoices.index
curl_test GET "/invoices/benefits/1/1" "200,302,301,403,404"  # invoices.benefit.callback
curl_test GET "/invoices/create" "200,302,301,403,404"  # invoices.create
curl_test GET "/invoices/creates/1" "200,302,301,403,404"  # invoices.create
curl_test GET "/invoices/export" "200,302,301,403,404"  # invoices.export
curl_test GET "/invoices/index" "200,302,301,403,404"  # invoices.index
curl_test GET "/invoices/items" "200,302,301,403,404"  # invoices.items
curl_test GET "/invoices/pdfs/1" "200,302,301,403,404"  # invoices.pdf
curl_test GET "/invoices/previews/1/1" "200,302,301,403,404"  # invoices.preview
curl_test GET "/invoices/with-benefit" "200,302,301,403,404"  # invoices.benefit.initiate
curl_test GET "/invoices/with-cashfrees/status" "200,302,301,403,404"  # invoices.cashfree.payment.success
curl_test GET "/invoices/1/action" "200,302,301,403,404"  # invoices.action
curl_test GET "/invoices/1/credit-note" "200,302,301,403,404"  # invoices.credit.note
curl_test GET "/invoices/1/credit-notes/edits/1" "200,302,301,403,404"  # invoices.edit.credit.note
curl_test GET "/invoices/1/duplicate" "200,302,301,403,404"  # invoices.duplicate
curl_test GET "/invoices/1/payment" "200,302,301,403,404"  # invoices.payment
curl_test GET "/invoices/1/payments/reminder" "200,302,301,403,404"  # invoices.payment.reminder
curl_test GET "/invoices/1/resent" "200,302,301,403,404"  # invoices.resent
curl_test GET "/invoices/1/sent" "200,302,301,403,404"  # invoices.sent
curl_test GET "/invoices/1/shippings/print" "200,302,301,403,404"  # invoices.shipping.print
curl_test GET "/invoices/1" "200,302,301,403,404"  # invoices.show
curl_test GET "/invoices/1/edit" "200,302,301,403,404"  # invoices.edit

# ── job-application ──────────────────────────────────
curl_test GET "/job-application" "200,302,301,403,404"  # job-application.index

# ── job-applications ──────────────────────────────────
curl_test GET "/job-applications/create" "200,302,301,403,404"  # job-application.create
curl_test GET "/job-applications/1" "200,302,301,403,404"  # job-application.show
curl_test GET "/job-applications/1/edit" "200,302,301,403,404"  # job-application.edit

# ── job-categories ──────────────────────────────────
curl_test GET "/job-categories/create" "200,302,301,403,404"  # job-category.create
curl_test GET "/job-categories/1" "200,302,301,403,404"  # job-category.show
curl_test GET "/job-categories/1/edit" "200,302,301,403,404"  # job-category.edit

# ── job-category ──────────────────────────────────
curl_test GET "/job-category" "200,302,301,403,404"  # job-category.index

# ── job-onboard ──────────────────────────────────
curl_test GET "/job-onboard" "200,302,301,403,404"  # jobs.on.board

# ── job-stage ──────────────────────────────────
curl_test GET "/job-stage" "200,302,301,403,404"  # job-stage.index

# ── job-stages ──────────────────────────────────
curl_test GET "/job-stages/create" "200,302,301,403,404"  # job-stage.create
curl_test GET "/job-stages/1" "200,302,301,403,404"  # job-stage.show
curl_test GET "/job-stages/1/edit" "200,302,301,403,404"  # job-stage.edit

# ── jobs ──────────────────────────────────
curl_test GET "/jobs" "200,302,301,403,404"  # jobs.index
curl_test GET "/jobs/applies/1/1" "200,302,301,403,404"  # jobs.apply
curl_test GET "/jobs/create" "200,302,301,403,404"  # jobs.create
curl_test GET "/jobs/requirements/1/1" "200,302,301,403,404"  # jobs.requirement
curl_test GET "/jobs/1" "200,302,301,403,404"  # jobs.show
curl_test GET "/jobs/1/edit" "200,302,301,403,404"  # jobs.edit

# ── jobs_onboards ──────────────────────────────────
curl_test GET "/jobs_onboards/converts/1" "200,302,301,403,404"  # jobs.on.board.convert
curl_test GET "/jobs_onboards/creates/1" "200,302,301,403,404"  # jobs.on.board.create
curl_test GET "/jobs_onboards/docs/1" "200,302,301,403,404"  # offer_letter.download.doc
curl_test GET "/jobs_onboards/edits/1" "200,302,301,403,404"  # jobs.on.board.edit
curl_test GET "/jobs_onboards/pdfs/1" "200,302,301,403,404"  # offer_letter.download.pdf

# ── join_us ──────────────────────────────────
curl_test GET "/join_us" "200,302,301,403,404"  # join_us.index
curl_test GET "/join_us/create" "200,302,301,403,404"  # join_us.create
curl_test GET "/join_us/1" "200,302,301,403,404"  # join_us.show
curl_test GET "/join_us/1/edit" "200,302,301,403,404"  # join_us.edit

# ── journal_entries ──────────────────────────────────
curl_test GET "/journal_entries" "200,302,301,403,404"  # journal_entries.index
curl_test GET "/journal_entries/create" "200,302,301,403,404"  # journal_entries.create
curl_test GET "/journal_entries/1" "200,302,301,403,404"  # journal_entries.show
curl_test GET "/journal_entries/1/edit" "200,302,301,403,404"  # journal_entries.edit

# ── labels ──────────────────────────────────
curl_test GET "/labels" "200,302,301,403,404"  # labels.index
curl_test GET "/labels/create" "200,302,301,403,404"  # labels.create
curl_test GET "/labels/1" "200,302,301,403,404"  # labels.show
curl_test GET "/labels/1/edit" "200,302,301,403,404"  # labels.edit

# ── landingpage ──────────────────────────────────
curl_test GET "/landingpage" "200,302,301,403,404"  # landingpage.index
curl_test GET "/landingpage/create" "200,302,301,403,404"  # landingpage.create
curl_test GET "/landingpage/1" "200,302,301,403,404"  # landingpage.show
curl_test GET "/landingpage/1/edit" "200,302,301,403,404"  # landingpage.edit

# ── last-login ──────────────────────────────────
curl_test GET "/last-login" "200,302,301,403,404"  # last_login

# ── lead_stages ──────────────────────────────────
curl_test GET "/lead_stages" "200,302,301,403,404"  # lead_stages.index
curl_test GET "/lead_stages/create" "200,302,301,403,404"  # lead_stages.create
curl_test GET "/lead_stages/1" "200,302,301,403,404"  # lead_stages.show
curl_test GET "/lead_stages/1/edit" "200,302,301,403,404"  # lead_stages.edit

# ── leads ──────────────────────────────────
curl_test GET "/leads" "200,302,301,403,404"  # leads.index
curl_test GET "/leads/create" "200,302,301,403,404"  # leads.create
curl_test GET "/leads/list" "200,302,301,403,404"  # leads.list
curl_test GET "/leads/1/call" "200,302,301,403,404"  # leads.calls.create
curl_test GET "/leads/1/calls/1/edit" "200,302,301,403,404"  # leads.calls.edit
curl_test GET "/leads/1/discussions" "200,302,301,403,404"  # leads.discussions.create
curl_test GET "/leads/1/email" "200,302,301,403,404"  # leads.emails.create
curl_test GET "/leads/1/files/1" "200,302,301,403,404"  # leads.file.download
curl_test GET "/leads/1/labels" "200,302,301,403,404"  # leads.labels
curl_test GET "/leads/1/products" "200,302,301,403,404"  # leads.products.edit
curl_test GET "/leads/1/show_convert" "200,302,301,403,404"  # leads.convert.deal
curl_test GET "/leads/1/sources" "200,302,301,403,404"  # leads.sources.edit
curl_test GET "/leads/1/users" "200,302,301,403,404"  # leads.users.edit
curl_test GET "/leads/1" "200,302,301,403,404"  # leads.show
curl_test GET "/leads/1/edit" "200,302,301,403,404"  # leads.edit

# ── leave ──────────────────────────────────
curl_test GET "/leave" "200,302,301,403,404"  # leave.index

# ── leave_types ──────────────────────────────────
curl_test GET "/leave_types" "200,302,301,403,404"  # leave_types.index
curl_test GET "/leave_types/create" "200,302,301,403,404"  # leave_types.create
curl_test GET "/leave_types/1" "200,302,301,403,404"  # leave_types.show
curl_test GET "/leave_types/1/edit" "200,302,301,403,404"  # leave_types.edit

# ── leaves ──────────────────────────────────
curl_test GET "/leaves/create" "200,302,301,403,404"  # leave.create
curl_test GET "/leaves/export" "200,302,301,403,404"  # leaves.export
curl_test GET "/leaves/1/action" "200,302,301,403,404"  # leaves.action
curl_test GET "/leaves/1" "200,302,301,403,404"  # leave.show
curl_test GET "/leaves/1/edit" "200,302,301,403,404"  # leave.edit

# ── loan_options ──────────────────────────────────
curl_test GET "/loan_options" "200,302,301,403,404"  # loan_options.index
curl_test GET "/loan_options/create" "200,302,301,403,404"  # loan_options.create
curl_test GET "/loan_options/1" "200,302,301,403,404"  # loan_options.show
curl_test GET "/loan_options/1/edit" "200,302,301,403,404"  # loan_options.edit

# ── loans ──────────────────────────────────
curl_test GET "/loans" "200,302,301,403,404"  # loans.index
curl_test GET "/loans/create" "200,302,301,403,404"  # loans.create
curl_test GET "/loans/creates/1" "200,302,301,403,404"  # loans.create
curl_test GET "/loans/1" "200,302,301,403,404"  # loans.show
curl_test GET "/loans/1/edit" "200,302,301,403,404"  # loans.edit

# ── login ──────────────────────────────────
curl_test GET "/login/1" "200,302,301,403,404"  # login

# ── manage-languages ──────────────────────────────────
curl_test GET "/manage-languages/1" "200,302,301,403,404"  # languages.manage

# ── meeting-calendar ──────────────────────────────────
curl_test GET "/meeting-calendar" "200,302,301,403,404"  # meetings.calendar

# ── meetings ──────────────────────────────────
curl_test GET "/meetings" "200,302,301,403,404"  # meetings.index
curl_test GET "/meetings/create" "200,302,301,403,404"  # meetings.create
curl_test GET "/meetings/get_meeting_data" "200,302,301,403,404"  # meetings.get_meeting_data
curl_test GET "/meetings/1" "200,302,301,403,404"  # meetings.show
curl_test GET "/meetings/1/edit" "200,302,301,403,404"  # meetings.edit

# ── name-search-products ──────────────────────────────────
curl_test GET "/name-search-products" "200,302,301,403,404"  # name.search.products

# ── notification_templates ──────────────────────────────────
curl_test GET "/notification_templates" "200,302,301,403,404"  # notification_templates.index
curl_test GET "/notification_templates/create" "200,302,301,403,404"  # notification_templates.create
curl_test GET "/notification_templates/1/1" "200,302,301,403,404"  # notification_templates.index
curl_test GET "/notification_templates/1" "200,302,301,403,404"  # notification_templates.show
curl_test GET "/notification_templates/1/edit" "200,302,301,403,404"  # notification_templates.edit

# ── orders ──────────────────────────────────
curl_test GET "/orders" "200,302,301,403,404"  # orders.index
curl_test GET "/orders/1/action" "200,302,301,403,404"  # orders.action

# ── other_payments ──────────────────────────────────
curl_test GET "/other_payments" "200,302,301,403,404"  # other_payments.index
curl_test GET "/other_payments/create" "200,302,301,403,404"  # other_payments.create
curl_test GET "/other_payments/creates/1" "200,302,301,403,404"  # other_payments.create
curl_test GET "/other_payments/1" "200,302,301,403,404"  # other_payments.show
curl_test GET "/other_payments/1/edit" "200,302,301,403,404"  # other_payments.edit

# ── overtimes ──────────────────────────────────
curl_test GET "/overtimes" "200,302,301,403,404"  # overtimes.index
curl_test GET "/overtimes/create" "200,302,301,403,404"  # overtimes.create
curl_test GET "/overtimes/creates/1" "200,302,301,403,404"  # overtimes.create
curl_test GET "/overtimes/1" "200,302,301,403,404"  # overtimes.show
curl_test GET "/overtimes/1/edit" "200,302,301,403,404"  # overtimes.edit

# ── pages ──────────────────────────────────
curl_test GET "/pages/1" "200,302,301,403,404"  # custom.page

# ── payments ──────────────────────────────────
curl_test GET "/payments" "200,302,301,403,404"  # payments.index
curl_test GET "/payments/benefits/callback" "200,302,301,403,404"  # benefit.callback
curl_test GET "/payments/benefits/initiate" "200,302,301,403,404"  # plans.pay.with.benefit
curl_test GET "/payments/create" "200,302,301,403,404"  # payments.create
curl_test GET "/payments/index" "200,302,301,403,404"  # payments.index
curl_test GET "/payments/1" "200,302,301,403,404"  # payments.show
curl_test GET "/payments/1/edit" "200,302,301,403,404"  # payments.edit

# ── payslips ──────────────────────────────────
curl_test GET "/payslips" "200,302,301,403,404"  # payslips.index
curl_test GET "/payslips/bulk_pay_creates/1" "200,302,301,403,404"  # payslips.bulk_pay_create
curl_test GET "/payslips/create" "200,302,301,403,404"  # payslips.create
curl_test GET "/payslips/deletes/1" "200,302,301,403,404"  # payslips.delete
curl_test GET "/payslips/edits/1" "200,302,301,403,404"  # payslips.editemployee
curl_test GET "/payslips/employeepayslip" "200,302,301,403,404"  # payslips.employeepayslip
curl_test GET "/payslips/paysalaries/1/1" "200,302,301,403,404"  # payslips.paysalary
curl_test GET "/payslips/payslip-pdfs/1" "200,302,301,403,404"  # payslips.payslipPdf
curl_test GET "/payslips/pdfs/1/1" "200,302,301,403,404"  # payslips.pdf
curl_test GET "/payslips/sends/1/1" "200,302,301,403,404"  # payslips.send
curl_test GET "/payslips/shows/1" "200,302,301,403,404"  # payslips.showemployee
curl_test GET "/payslips/1" "200,302,301,403,404"  # payslips.show
curl_test GET "/payslips/1/edit" "200,302,301,403,404"  # payslips.edit

# ── performance_types ──────────────────────────────────
curl_test GET "/performance_types" "200,302,301,403,404"  # performance_types.index
curl_test GET "/performance_types/create" "200,302,301,403,404"  # performance_types.create
curl_test GET "/performance_types/1" "200,302,301,403,404"  # performance_types.show
curl_test GET "/performance_types/1/edit" "200,302,301,403,404"  # performance_types.edit

# ── permissions ──────────────────────────────────
curl_test GET "/permissions" "200,302,301,403,404"  # permissions.index
curl_test GET "/permissions/create" "200,302,301,403,404"  # permissions.create
curl_test GET "/permissions/1" "200,302,301,403,404"  # permissions.show
curl_test GET "/permissions/1/edit" "200,302,301,403,404"  # permissions.edit

# ── pipelines ──────────────────────────────────
curl_test GET "/pipelines" "200,302,301,403,404"  # pipelines.index
curl_test GET "/pipelines/create" "200,302,301,403,404"  # pipelines.create
curl_test GET "/pipelines/1" "200,302,301,403,404"  # pipelines.show
curl_test GET "/pipelines/1/edit" "200,302,301,403,404"  # pipelines.edit

# ── plan_requests ──────────────────────────────────
curl_test GET "/plan_requests" "200,302,301,403,404"  # plan_requests.index

# ── plans ──────────────────────────────────
curl_test GET "/plans" "200,302,301,403,404"  # plans.index
curl_test GET "/plans/create" "200,302,301,403,404"  # plans.create
curl_test GET "/plans/1" "200,302,301,403,404"  # plans.show
curl_test GET "/plans/1/edit" "200,302,301,403,404"  # plans.edit

# ── pos ──────────────────────────────────
curl_test GET "/pos" "200,302,301,403,404"  # pos.index
curl_test GET "/pos/barcode" "200,302,301,403,404"  # pos.barcode
curl_test GET "/pos/create" "200,302,301,403,404"  # pos.create
curl_test GET "/pos/data/store" "200,302,301,403,404"  # pos.data.store
curl_test GET "/pos/pdfs/1" "200,302,301,403,404"  # pos.pdf
curl_test GET "/pos/previews/1/1" "200,302,301,403,404"  # pos.preview
curl_test GET "/pos/print" "200,302,301,403,404"  # pos.print
curl_test GET "/pos/1" "200,302,301,403,404"  # pos.show
curl_test GET "/pos/1/edit" "200,302,301,403,404"  # pos.edit

# ── pos-dashboard ──────────────────────────────────
curl_test GET "/pos-dashboard" "200,302,301,403,404"  # pos.dashboard

# ── pos-print-setting ──────────────────────────────────
curl_test GET "/pos-print-setting" "200,302,301,403,404"  # pos.print.setting

# ── pos-receipt ──────────────────────────────────
curl_test GET "/pos-receipt" "200,302,301,403,404"  # pos.receipt

# ── pricing_plans ──────────────────────────────────
curl_test GET "/pricing_plans" "200,302,301,403,404"  # pricing_plans.index
curl_test GET "/pricing_plans/create" "200,302,301,403,404"  # pricing_plans.create
curl_test GET "/pricing_plans/1" "200,302,301,403,404"  # pricing_plans.show
curl_test GET "/pricing_plans/1/edit" "200,302,301,403,404"  # pricing_plans.edit

# ── print-setting ──────────────────────────────────
curl_test GET "/print-setting" "200,302,301,403,404"  # print.setting

# ── printviews ──────────────────────────────────
curl_test GET "/printviews/pos" "200,302,301,403,404"  # pos.printview

# ── privacy_policy ──────────────────────────────────
curl_test GET "/privacy_policy" "200,302,301,403,404"  # privacy_policy

# ── product-categories ──────────────────────────────────
curl_test GET "/product-categories" "200,302,301,403,404"  # product_service_categories.categories

# ── product_service_categories ──────────────────────────────────
curl_test GET "/product_service_categories" "200,302,301,403,404"  # product_service_categories.index
curl_test GET "/product_service_categories/create" "200,302,301,403,404"  # product_service_categories.create
curl_test GET "/product_service_categories/1" "200,302,301,403,404"  # product_service_categories.show
curl_test GET "/product_service_categories/1/edit" "200,302,301,403,404"  # product_service_categories.edit

# ── product_service_units ──────────────────────────────────
curl_test GET "/product_service_units" "200,302,301,403,404"  # product_service_units.index
curl_test GET "/product_service_units/create" "200,302,301,403,404"  # product_service_units.create
curl_test GET "/product_service_units/1" "200,302,301,403,404"  # product_service_units.show
curl_test GET "/product_service_units/1/edit" "200,302,301,403,404"  # product_service_units.edit

# ── product_services ──────────────────────────────────
curl_test GET "/product_services" "200,302,301,403,404"  # product_services.index
curl_test GET "/product_services/create" "200,302,301,403,404"  # product_services.create
curl_test GET "/product_services/export" "200,302,301,403,404"  # product_services.export
curl_test GET "/product_services/index" "200,302,301,403,404"  # product_services.index
curl_test GET "/product_services/1/detail" "200,302,301,403,404"  # product_services.detail
curl_test GET "/product_services/1" "200,302,301,403,404"  # product_services.show
curl_test GET "/product_services/1/edit" "200,302,301,403,404"  # product_services.edit

# ── product_stocks ──────────────────────────────────
curl_test GET "/product_stocks" "200,302,301,403,404"  # product_stocks.index
curl_test GET "/product_stocks/create" "200,302,301,403,404"  # product_stocks.create
curl_test GET "/product_stocks/export" "200,302,301,403,404"  # product_stocks.export
curl_test GET "/product_stocks/1" "200,302,301,403,404"  # product_stocks.show
curl_test GET "/product_stocks/1/edit" "200,302,301,403,404"  # product_stocks.edit

# ── profile ──────────────────────────────────
curl_test GET "/profile" "200,302,301,403,404"  # user.profile

# ── project-dashboard ──────────────────────────────────
curl_test GET "/project-dashboard" "200,302,301,403,404"  # projects.dashboard

# ── project_reports ──────────────────────────────────
curl_test GET "/project_reports" "200,302,301,403,404"  # project_reports.index
curl_test GET "/project_reports/create" "200,302,301,403,404"  # project_reports.create
curl_test GET "/project_reports/exports/1" "200,302,301,403,404"  # project_reports.export
curl_test GET "/project_reports/1" "200,302,301,403,404"  # project_reports.show
curl_test GET "/project_reports/1/edit" "200,302,301,403,404"  # project_reports.edit

# ── project_stages ──────────────────────────────────
curl_test GET "/project_stages" "200,302,301,403,404"  # project_stages.index
curl_test GET "/project_stages/create" "200,302,301,403,404"  # project_stages.create
curl_test GET "/project_stages/1" "200,302,301,403,404"  # project_stages.show
curl_test GET "/project_stages/1/edit" "200,302,301,403,404"  # project_stages.edit

# ── project_task_stages ──────────────────────────────────
curl_test GET "/project_task_stages" "200,302,301,403,404"  # project_task_stages.index
curl_test GET "/project_task_stages/create" "200,302,301,403,404"  # project_task_stages.create
curl_test GET "/project_task_stages/1" "200,302,301,403,404"  # project_task_stages.show
curl_test GET "/project_task_stages/1/edit" "200,302,301,403,404"  # project_task_stages.edit

# ── projects ──────────────────────────────────
curl_test GET "/projects" "200,302,301,403,404"  # projects.index
curl_test GET "/projects/copies/links/1" "200,302,301,403,404"  # projects.copy.link
curl_test GET "/projects/copies/1" "200,302,301,403,404"  # projects.copy
curl_test GET "/projects/copy-links/1" "200,302,301,403,404"  # projects.copy_link
curl_test GET "/projects/create" "200,302,301,403,404"  # projects.create
curl_test GET "/projects/links/1/1" "200,302,301,403,404"  # projects.link
curl_test GET "/projects/milestones/1/edit" "200,302,301,403,404"  # projects.milestones.edit
curl_test GET "/projects/milestones/1/show" "200,302,301,403,404"  # projects.milestones.show
curl_test GET "/projects/tasks/1/get" "200,302,301,403,404"  # projects.tasks.get
curl_test GET "/projects/time-trackers/1" "200,302,301,403,404"  # projects.time.tracker
curl_test GET "/projects/1/bugs" "200,302,301,403,404"  # projects.tasks.bugs
curl_test GET "/projects/1/bugs/create" "200,302,301,403,404"  # projects.tasks.bugs.create
curl_test GET "/projects/1/bugs/kanban" "200,302,301,403,404"  # projects.tasks.bugs.kanban
curl_test GET "/projects/1/bugs/1/edit" "200,302,301,403,404"  # projects.tasks.bugs.edit
curl_test GET "/projects/1/bugs/1/show" "200,302,301,403,404"  # projects.tasks.bugs.show
curl_test GET "/projects/1/expenses" "200,302,301,403,404"  # projects.expenses.index
curl_test GET "/projects/1/expenses/1/edit" "200,302,301,403,404"  # projects.expenses.edit
curl_test GET "/projects/1/gantts/1" "200,302,301,403,404"  # projects.gantt
curl_test GET "/projects/1/milestones" "200,302,301,403,404"  # projects.milestones
curl_test GET "/projects/1/setting-create" "200,302,301,403,404"  # projects.copy_link.setting.create
curl_test GET "/projects/1/tasks" "200,302,301,403,404"  # projects.tasks.index
curl_test GET "/projects/1/tasks/1/edit" "200,302,301,403,404"  # projects.tasks.edit
curl_test GET "/projects/1/tasks/1/show" "200,302,301,403,404"  # projects.tasks.show
curl_test GET "/projects/1/users/1/permission" "200,302,301,403,404"  # projects.users.permission
curl_test GET "/projects/1/expenses/create" "200,302,301,403,404"  # projects.expenses.create
curl_test GET "/projects/1/tasks/1" "200,302,301,403,404"  # projects.tasks.create
curl_test GET "/projects/1" "200,302,301,403,404"  # projects.show
curl_test GET "/projects/1/edit" "200,302,301,403,404"  # projects.edit
curl_test GET "/projects/1" "200,302,301,403,404"  # projects.list

# ── projects-users ──────────────────────────────────
curl_test GET "/projects-users" "200,302,301,403,404"  # projects.user

# ── projects-view ──────────────────────────────────
curl_test GET "/projects-view" "200,302,301,403,404"  # filter.project.view

# ── projects.timesheets ──────────────────────────────────
curl_test GET "/projects.timesheets/append-task" "200,302,301,403,404"  # projects.timesheets.append.task
curl_test GET "/projects.timesheets/list" "200,302,301,403,404"  # projects.timesheets.list
curl_test GET "/projects.timesheets/list-get" "200,302,301,403,404"  # projects.timesheets.list.get
curl_test GET "/projects.timesheets/projects/updates/1" "200,302,301,403,404"  # projects.timesheets.update
curl_test GET "/projects.timesheets/projects/1" "200,302,301,403,404"  # projects.timesheets.index
curl_test GET "/projects.timesheets/projects/1/create" "200,302,301,403,404"  # projects.timesheets.create
curl_test GET "/projects.timesheets/projects/1/edits/1" "200,302,301,403,404"  # projects.timesheets.edit
curl_test GET "/projects.timesheets/table-view" "200,302,301,403,404"  # projects.timesheets.filters.table.view
curl_test GET "/projects.timesheets/view" "200,302,301,403,404"  # projects.timesheets.filters.view

# ── promotions ──────────────────────────────────
curl_test GET "/promotions" "200,302,301,403,404"  # promotions.index
curl_test GET "/promotions/create" "200,302,301,403,404"  # promotions.create
curl_test GET "/promotions/1" "200,302,301,403,404"  # promotions.show
curl_test GET "/promotions/1/edit" "200,302,301,403,404"  # promotions.edit

# ── proposal ──────────────────────────────────
curl_test GET "/proposal" "200,302,301,403,404"  # proposal.index

# ── proposals ──────────────────────────────────
curl_test GET "/proposals/create" "200,302,301,403,404"  # proposal.create
curl_test GET "/proposals/creates/1" "200,302,301,403,404"  # proposals.create
curl_test GET "/proposals/export" "200,302,301,403,404"  # proposals.export
curl_test GET "/proposals/items" "200,302,301,403,404"  # proposals.items
curl_test GET "/proposals/pdfs/1" "200,302,301,403,404"  # proposals.pdf
curl_test GET "/proposals/previews/1/1" "200,302,301,403,404"  # proposals.preview
curl_test GET "/proposals/1/convert" "200,302,301,403,404"  # proposals.convert
curl_test GET "/proposals/1/duplicate" "200,302,301,403,404"  # proposals.duplicate
curl_test GET "/proposals/1/resent" "200,302,301,403,404"  # proposals.resent
curl_test GET "/proposals/1/sent" "200,302,301,403,404"  # proposals.sent
curl_test GET "/proposals/1/statuses/change" "200,302,301,403,404"  # proposals.status.change
curl_test GET "/proposals/1" "200,302,301,403,404"  # proposal.show
curl_test GET "/proposals/1/edit" "200,302,301,403,404"  # proposal.edit

# ── purchases ──────────────────────────────────
curl_test GET "/purchases" "200,302,301,403,404"  # purchases.index
curl_test GET "/purchases/create" "200,302,301,403,404"  # purchases.create
curl_test GET "/purchases/creates/1" "200,302,301,403,404"  # purchases.create
curl_test GET "/purchases/items" "200,302,301,403,404"  # purchases.items
curl_test GET "/purchases/pdfs/1" "200,302,301,403,404"  # purchases.pdf
curl_test GET "/purchases/previews/1/1" "200,302,301,403,404"  # purchases.preview
curl_test GET "/purchases/1/payment" "200,302,301,403,404"  # purchases.payment
curl_test GET "/purchases/1/resent" "200,302,301,403,404"  # purchases.resent
curl_test GET "/purchases/1/sent" "200,302,301,403,404"  # purchases.sent
curl_test GET "/purchases/1" "200,302,301,403,404"  # purchases.show
curl_test GET "/purchases/1/edit" "200,302,301,403,404"  # purchases.edit

# ── register ──────────────────────────────────
curl_test GET "/register/1" "200,302,301,403,404"  # register

# ── reports ──────────────────────────────────
curl_test GET "/reports/account-statement-report" "200,302,301,403,404"  # reports.account.statement
curl_test GET "/reports/attendances/1/1/1" "200,302,301,403,404"  # reports.attendance
curl_test GET "/reports/balance-sheets/1" "200,302,301,403,404"  # reports.balance.sheet
curl_test GET "/reports/bill-summary" "200,302,301,403,404"  # reports.bill.summary
curl_test GET "/reports/expense-summary" "200,302,301,403,404"  # reports.expense.summary
curl_test GET "/reports/income-summary" "200,302,301,403,404"  # reports.income.summary
curl_test GET "/reports/income-vs-expense-summary" "200,302,301,403,404"  # reports.income.vs.expense.summary
curl_test GET "/reports/invoice-report" "200,302,301,403,404"  # reports.invoice
curl_test GET "/reports/invoice-summary" "200,302,301,403,404"  # reports.invoice.summary
curl_test GET "/reports/leave" "200,302,301,403,404"  # reports.leave
curl_test GET "/reports/ledgers/1" "200,302,301,403,404"  # reports.ledger
curl_test GET "/reports/payables" "200,302,301,403,404"  # reports.payables
curl_test GET "/reports/payrolls/export" "200,302,301,403,404"  # reports.payroll.export
curl_test GET "/reports/pos" "200,302,301,403,404"  # pos.report
curl_test GET "/reports/product-stock-report" "200,302,301,403,404"  # reports.product.stock.report
curl_test GET "/reports/profit-losses/1" "200,302,301,403,404"  # reports.profit.loss
curl_test GET "/reports/receivables" "200,302,301,403,404"  # reports.receivables
curl_test GET "/reports/sales" "200,302,301,403,404"  # reports.sales
curl_test GET "/reports/tax-summary" "200,302,301,403,404"  # reports.tax.summary
curl_test GET "/reports/transaction" "200,302,301,403,404"  # transactions.index
curl_test GET "/reports/trial-balance" "200,302,301,403,404"  # reports.trial.balance

# ── reports-daily-pos ──────────────────────────────────
curl_test GET "/reports-daily-pos" "200,302,301,403,404"  # reports.daily.pos

# ── reports-daily-purchase ──────────────────────────────────
curl_test GET "/reports-daily-purchase" "200,302,301,403,404"  # reports.daily.purchase

# ── reports-deal ──────────────────────────────────
curl_test GET "/reports-deal" "200,302,301,403,404"  # reports.deal

# ── reports-lead ──────────────────────────────────
curl_test GET "/reports-lead" "200,302,301,403,404"  # reports.lead

# ── reports-leave ──────────────────────────────────
curl_test GET "/reports-leave" "200,302,301,403,404"  # reports.leave

# ── reports-monthly-attendance ──────────────────────────────────
curl_test GET "/reports-monthly-attendance" "200,302,301,403,404"  # reports.monthly.attendance

# ── reports-monthly-cashflow ──────────────────────────────────
curl_test GET "/reports-monthly-cashflow" "200,302,301,403,404"  # reports.monthly.cashflow

# ── reports-monthly-pos ──────────────────────────────────
curl_test GET "/reports-monthly-pos" "200,302,301,403,404"  # reports.monthly.pos

# ── reports-monthly-purchase ──────────────────────────────────
curl_test GET "/reports-monthly-purchase" "200,302,301,403,404"  # reports.monthly.purchase

# ── reports-payroll ──────────────────────────────────
curl_test GET "/reports-payroll" "200,302,301,403,404"  # reports.payroll

# ── reports-pos-vs-purchase ──────────────────────────────────
curl_test GET "/reports-pos-vs-purchase" "200,302,301,403,404"  # reports.pos.vs.purchase

# ── reports-quarterly-cashflow ──────────────────────────────────
curl_test GET "/reports-quarterly-cashflow" "200,302,301,403,404"  # reports.quarterly.cashflow

# ── reports-warehouse ──────────────────────────────────
curl_test GET "/reports-warehouse" "200,302,301,403,404"  # reports.warehouse

# ── request_cancels ──────────────────────────────────
curl_test GET "/request_cancels/1" "200,302,301,403,404"  # plan_requests.request.cancel

# ── request_frequencies ──────────────────────────────────
curl_test GET "/request_frequencies/1" "200,302,301,403,404"  # plan_requests.request.view

# ── request_responses ──────────────────────────────────
curl_test GET "/request_responses/1/1" "200,302,301,403,404"  # plan_requests.request.response

# ── request_sends ──────────────────────────────────
curl_test GET "/request_sends/1" "200,302,301,403,404"  # plan_requests.request.send

# ── reset-password ──────────────────────────────────
curl_test GET "/reset-password/1" "200,302,301,403,404"  # password.reset

# ── resignations ──────────────────────────────────
curl_test GET "/resignations" "200,302,301,403,404"  # resignations.index
curl_test GET "/resignations/create" "200,302,301,403,404"  # resignations.create
curl_test GET "/resignations/1" "200,302,301,403,404"  # resignations.show
curl_test GET "/resignations/1/edit" "200,302,301,403,404"  # resignations.edit

# ── revenues ──────────────────────────────────
curl_test GET "/revenues" "200,302,301,403,404"  # revenues.index
curl_test GET "/revenues/create" "200,302,301,403,404"  # revenues.create
curl_test GET "/revenues/index" "200,302,301,403,404"  # revenues.index
curl_test GET "/revenues/1" "200,302,301,403,404"  # revenues.show
curl_test GET "/revenues/1/edit" "200,302,301,403,404"  # revenues.edit

# ── roles ──────────────────────────────────
curl_test GET "/roles" "200,302,301,403,404"  # roles.index
curl_test GET "/roles/create" "200,302,301,403,404"  # roles.create
curl_test GET "/roles/1" "200,302,301,403,404"  # roles.show
curl_test GET "/roles/1/edit" "200,302,301,403,404"  # roles.edit

# ── root ──────────────────────────────────
curl_test GET "/" "200,302,301,403,404"  # home_section.index

# ── sancta ──────────────────────────────────
curl_test GET "/sancta/csrf-cookie" "200,302,301,403,404"  # sanctum.csrf-cookie

# ── saturation_deductions ──────────────────────────────────
curl_test GET "/saturation_deductions" "200,302,301,403,404"  # saturation_deductions.index
curl_test GET "/saturation_deductions/create" "200,302,301,403,404"  # saturation_deductions.create
curl_test GET "/saturation_deductions/creates/1" "200,302,301,403,404"  # saturation_deductions.create
curl_test GET "/saturation_deductions/1" "200,302,301,403,404"  # saturation_deductions.show
curl_test GET "/saturation_deductions/1/edit" "200,302,301,403,404"  # saturation_deductions.edit

# ── screenshots ──────────────────────────────────
curl_test GET "/screenshots" "200,302,301,403,404"  # screenshots.index
curl_test GET "/screenshots/create" "200,302,301,403,404"  # screenshots.create
curl_test GET "/screenshots/delete/1" "200,302,301,403,404"  # screenshots.delete
curl_test GET "/screenshots/edit/1" "200,302,301,403,404"  # screenshots.edit
curl_test GET "/screenshots/1" "200,302,301,403,404"  # screenshots.show
curl_test GET "/screenshots/1/edit" "200,302,301,403,404"  # screenshots.edit

# ── search ──────────────────────────────────
curl_test GET "/search" "200,302,301,403,404"  # search.json

# ── search-products ──────────────────────────────────
curl_test GET "/search-products" "200,302,301,403,404"  # search.products

# ── set_salaries ──────────────────────────────────
curl_test GET "/set_salaries" "200,302,301,403,404"  # set_salaries.index
curl_test GET "/set_salaries/create" "200,302,301,403,404"  # set_salaries.create
curl_test GET "/set_salaries/1" "200,302,301,403,404"  # set_salaries.show
curl_test GET "/set_salaries/1/edit" "200,302,301,403,404"  # set_salaries.edit

# ── settings ──────────────────────────────────
curl_test GET "/settings" "200,302,301,403,404"  # settings
curl_test GET "/settings/exp" "200,302,301,403,404"  # settings.experience_certificate.language
curl_test GET "/settings/joining-letter" "200,302,301,403,404"  # settingsjoining_letter.language
curl_test GET "/settings/noc" "200,302,301,403,404"  # settings.noc.language
curl_test GET "/settings/offer-letter" "200,302,301,403,404"  # settings.offer_letter.language
curl_test GET "/settings/pos" "200,302,301,403,404"  # pos.setting

# ── share-projects ──────────────────────────────────
curl_test GET "/share-projects/1" "200,302,301,403,404"  # share.project

# ── show-employee-profiles ──────────────────────────────────
curl_test GET "/show-employee-profiles/1" "200,302,301,403,404"  # employees.show.profile

# ── signatures ──────────────────────────────────
curl_test GET "/signatures/1" "200,302,301,403,404"  # contracts.signature

# ── sources ──────────────────────────────────
curl_test GET "/sources" "200,302,301,403,404"  # sources.index
curl_test GET "/sources/create" "200,302,301,403,404"  # sources.create
curl_test GET "/sources/1" "200,302,301,403,404"  # sources.show
curl_test GET "/sources/1/edit" "200,302,301,403,404"  # sources.edit

# ── stages ──────────────────────────────────
curl_test GET "/stages" "200,302,301,403,404"  # stages.index
curl_test GET "/stages/create" "200,302,301,403,404"  # stages.create
curl_test GET "/stages/1/tasks" "200,302,301,403,404"  # projects.tasks.stage
curl_test GET "/stages/1" "200,302,301,403,404"  # stages.show
curl_test GET "/stages/1/edit" "200,302,301,403,404"  # stages.edit

# ── store-language ──────────────────────────────────
curl_test GET "/store-language" "200,302,301,403,404"  # languages.store

# ── stripes ──────────────────────────────────
curl_test GET "/stripes/1" "200,302,301,403,404"  # stripe

# ── supports ──────────────────────────────────
curl_test GET "/supports" "200,302,301,403,404"  # supports.index
curl_test GET "/supports/create" "200,302,301,403,404"  # supports.create
curl_test GET "/supports/grid" "200,302,301,403,404"  # supports.grid
curl_test GET "/supports/1/reply" "200,302,301,403,404"  # supports.reply
curl_test GET "/supports/1" "200,302,301,403,404"  # supports.show
curl_test GET "/supports/1/edit" "200,302,301,403,404"  # supports.edit

# ── systems ──────────────────────────────────
curl_test GET "/systems" "200,302,301,403,404"  # systems.index
curl_test GET "/systems/create" "200,302,301,403,404"  # systems.create
curl_test GET "/systems/creates/ip" "200,302,301,403,404"  # systems.ip.create
curl_test GET "/systems/edits/ips/1" "200,302,301,403,404"  # systems.ip.edit
curl_test GET "/systems/1" "200,302,301,403,404"  # systems.show
curl_test GET "/systems/1/edit" "200,302,301,403,404"  # systems.edit

# ── task-board-view ──────────────────────────────────
curl_test GET "/task-board-view" "200,302,301,403,404"  # projects.taskboard.view

# ── task-boards ──────────────────────────────────
curl_test GET "/task-boards/1" "200,302,301,403,404"  # taskboards.view

# ── taxes ──────────────────────────────────
curl_test GET "/taxes" "200,302,301,403,404"  # taxes.index
curl_test GET "/taxes/create" "200,302,301,403,404"  # taxes.create
curl_test GET "/taxes/1" "200,302,301,403,404"  # taxes.show
curl_test GET "/taxes/1/edit" "200,302,301,403,404"  # taxes.edit

# ── terminations ──────────────────────────────────
curl_test GET "/terminations" "200,302,301,403,404"  # terminations.index
curl_test GET "/terminations/create" "200,302,301,403,404"  # terminations.create
curl_test GET "/terminations/1/description" "200,302,301,403,404"  # terminations.description
curl_test GET "/terminations/1" "200,302,301,403,404"  # terminations.show
curl_test GET "/terminations/1/edit" "200,302,301,403,404"  # terminations.edit

# ── terminationtype ──────────────────────────────────
curl_test GET "/terminationtype" "200,302,301,403,404"  # termination_types.index

# ── terminationtypes ──────────────────────────────────
curl_test GET "/terminationtypes/create" "200,302,301,403,404"  # terminationtype.create
curl_test GET "/terminationtypes/create" "200,302,301,403,404"  # termination_types.create
curl_test GET "/terminationtypes/1" "200,302,301,403,404"  # terminationtype.show
curl_test GET "/terminationtypes/1" "200,302,301,403,404"  # termination_types.show
curl_test GET "/terminationtypes/1/edit" "200,302,301,403,404"  # terminationtype.edit
curl_test GET "/terminationtypes/1/edit" "200,302,301,403,404"  # termination_types.edit

# ── terms_and_conditions ──────────────────────────────────
curl_test GET "/terms_and_conditions" "200,302,301,403,404"  # terms_and_conditions

# ── test-mail ──────────────────────────────────
curl_test GET "/test-mail" "200,302,301,403,404"  # tests.mail

# ── testimonials ──────────────────────────────────
curl_test GET "/testimonials" "200,302,301,403,404"  # testimonials.index
curl_test GET "/testimonials/create" "200,302,301,403,404"  # testimonials.create
curl_test GET "/testimonials/delete/1" "200,302,301,403,404"  # testimonials.delete
curl_test GET "/testimonials/edit/1" "200,302,301,403,404"  # testimonials.edit
curl_test GET "/testimonials/1" "200,302,301,403,404"  # testimonials.show
curl_test GET "/testimonials/1/edit" "200,302,301,403,404"  # testimonials.edit

# ── time_trackers ──────────────────────────────────
curl_test GET "/time_trackers" "200,302,301,403,404"  # time.tracker

# ── trainers ──────────────────────────────────
curl_test GET "/trainers" "200,302,301,403,404"  # trainers.index
curl_test GET "/trainers/create" "200,302,301,403,404"  # trainers.create
curl_test GET "/trainers/1" "200,302,301,403,404"  # trainers.show
curl_test GET "/trainers/1/edit" "200,302,301,403,404"  # trainers.edit

# ── training_types ──────────────────────────────────
curl_test GET "/training_types" "200,302,301,403,404"  # training_types.index
curl_test GET "/training_types/create" "200,302,301,403,404"  # training_types.create
curl_test GET "/training_types/1" "200,302,301,403,404"  # training_types.show
curl_test GET "/training_types/1/edit" "200,302,301,403,404"  # training_types.edit

# ── trainings ──────────────────────────────────
curl_test GET "/trainings" "200,302,301,403,404"  # trainings.index
curl_test GET "/trainings/create" "200,302,301,403,404"  # trainings.create
curl_test GET "/trainings/1" "200,302,301,403,404"  # trainings.show
curl_test GET "/trainings/1/edit" "200,302,301,403,404"  # trainings.edit

# ── transactions ──────────────────────────────────
curl_test GET "/transactions/export" "200,302,301,403,404"  # transactions.export

# ── transfers ──────────────────────────────────
curl_test GET "/transfers" "200,302,301,403,404"  # transfers.index
curl_test GET "/transfers/create" "200,302,301,403,404"  # transfers.create
curl_test GET "/transfers/1" "200,302,301,403,404"  # transfers.show
curl_test GET "/transfers/1/edit" "200,302,301,403,404"  # transfers.edit

# ── travels ──────────────────────────────────
curl_test GET "/travels" "200,302,301,403,404"  # travels.index
curl_test GET "/travels/create" "200,302,301,403,404"  # travels.create
curl_test GET "/travels/1" "200,302,301,403,404"  # travels.show
curl_test GET "/travels/1/edit" "200,302,301,403,404"  # travels.edit

# ── two-factor-challenge ──────────────────────────────────
curl_test GET "/two-factor-challenge" "200,302,301,403,404"  # two-factor.login

# ── update ──────────────────────────────────
curl_test GET "/update" "200,302,301,403,404"  # LaravelUpdater::welcome

# ── updates ──────────────────────────────────
curl_test GET "/updates/database" "200,302,301,403,404"  # LaravelUpdater::database
curl_test GET "/updates/final" "200,302,301,403,404"  # LaravelUpdater::final
curl_test GET "/updates/overview" "200,302,301,403,404"  # LaravelUpdater::overview

# ── user ──────────────────────────────────
curl_test GET "/user/confirm-password" "200,302,301,403,404"  # password.confirm

# ── user-reset-passwords ──────────────────────────────────
curl_test GET "/user-reset-passwords/1" "200,302,301,403,404"  # users.reset

# ── users ──────────────────────────────────
curl_test GET "/users" "200,302,301,403,404"  # users.index
curl_test GET "/users/confirmed-password-status" "200,302,301,403,404"  # password.confirmation
curl_test GET "/users/create" "200,302,301,403,404"  # users.create
curl_test GET "/users/infos/1" "200,302,301,403,404"  # users.info
curl_test GET "/users/logs" "200,302,301,403,404"  # users.log
curl_test GET "/users/logs/1" "200,302,301,403,404"  # users.log.view
curl_test GET "/users/profile" "200,302,301,403,404"  # profile.show
curl_test GET "/users/two-factor-qr-code" "200,302,301,403,404"  # two-factor.qr-code
curl_test GET "/users/two-factor-recovery-codes" "200,302,301,403,404"  # two-factor.recovery-codes
curl_test GET "/users/two-factor-secret-key" "200,302,301,403,404"  # two-factor.secret-key
curl_test GET "/users/1/infos/1" "200,302,301,403,404"  # users.info.popup
curl_test GET "/users/1/plan" "200,302,301,403,404"  # plans.upgrade
curl_test GET "/users/1/plans/1" "200,302,301,403,404"  # plans.active
curl_test GET "/users/1" "200,302,301,403,404"  # users.show
curl_test GET "/users/1/edit" "200,302,301,403,404"  # users.edit
curl_test GET "/users/1" "200,302,301,403,404"  # users

# ── users-view ──────────────────────────────────
curl_test GET "/users-view" "200,302,301,403,404"  # filter.user.view

# ── vendors ──────────────────────────────────
curl_test GET "/vendors" "200,302,301,403,404"  # vendors.index
curl_test GET "/vendors/bills/1" "200,302,301,403,404"  # bills.link.copy
curl_test GET "/vendors/create" "200,302,301,403,404"  # vendors.create
curl_test GET "/vendors/export" "200,302,301,403,404"  # vendors.export
curl_test GET "/vendors/imports/file" "200,302,301,403,404"  # vendors.file.import
curl_test GET "/vendors/purchases/1" "200,302,301,403,404"  # purchases.link.copy
curl_test GET "/vendors/1/show" "200,302,301,403,404"  # vendors.show
curl_test GET "/vendors/1" "200,302,301,403,404"  # vendors.show
curl_test GET "/vendors/1/edit" "200,302,301,403,404"  # vendors.edit

# ── verify ──────────────────────────────────
curl_test GET "/verify/1/1" "200,302,301,403,404"  # verification.verify
curl_test GET "/verify/1" "200,302,301,403,404"  # verification.notice

# ── warehouse_transfers ──────────────────────────────────
curl_test GET "/warehouse_transfers" "200,302,301,403,404"  # warehouse_transfers.index
curl_test GET "/warehouse_transfers/create" "200,302,301,403,404"  # warehouse_transfers.create
curl_test GET "/warehouse_transfers/1" "200,302,301,403,404"  # warehouse_transfers.show
curl_test GET "/warehouse_transfers/1/edit" "200,302,301,403,404"  # warehouse_transfers.edit

# ── warehouses ──────────────────────────────────
curl_test GET "/warehouses" "200,302,301,403,404"  # warehouses.index
curl_test GET "/warehouses/create" "200,302,301,403,404"  # warehouses.create
curl_test GET "/warehouses/1" "200,302,301,403,404"  # warehouses.show
curl_test GET "/warehouses/1/edit" "200,302,301,403,404"  # warehouses.edit

# ── warnings ──────────────────────────────────
curl_test GET "/warnings" "200,302,301,403,404"  # warnings.index
curl_test GET "/warnings/create" "200,302,301,403,404"  # warnings.create
curl_test GET "/warnings/1" "200,302,301,403,404"  # warnings.show
curl_test GET "/warnings/1/edit" "200,302,301,403,404"  # warnings.edit

# ── webhook-settings ──────────────────────────────────
curl_test GET "/webhook-settings" "200,302,301,403,404"  # webhooks.settings
curl_test GET "/webhook-settings/create" "200,302,301,403,404"  # webhooks.create
curl_test GET "/webhook-settings/1/edit" "200,302,301,403,404"  # webhooks.edit

# ── zoom-meeting-calendar ──────────────────────────────────
curl_test GET "/zoom-meeting-calendar" "200,302,301,403,404"  # zoom_meetings.calendar

# ── zoom-meetings ──────────────────────────────────
curl_test GET "/zoom-meetings/get_zoom_meeting_data" "200,302,301,403,404"  # zoom_meetings.get_zoom_meeting_data

# ── zoom_meetings ──────────────────────────────────
curl_test GET "/zoom_meetings" "200,302,301,403,404"  # zoom_meetings.index
curl_test GET "/zoom_meetings/create" "200,302,301,403,404"  # zoom_meetings.create
curl_test GET "/zoom_meetings/projects/selects/1" "200,302,301,403,404"  # zoom_meetings.projects.select
curl_test GET "/zoom_meetings/1" "200,302,301,403,404"  # zoom_meetings.show
curl_test GET "/zoom_meetings/1/edit" "200,302,301,403,404"  # zoom_meetings.edit

# ── {uid} ──────────────────────────────────
curl_test GET "/1/notifications/seen" "200,302,301,403,404"  # notifications.seen

log_info "GET route tests complete."
summary
