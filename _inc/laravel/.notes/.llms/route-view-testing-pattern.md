# Route & View Testing Pattern

> Systematic coverage requirements for curl, wget, php cli, and Playwright verification.
> Referenced from `where-to-update-and-read.yml` → `starters.etc.filesystem.issue-tracking.testing-patterns`

---

## Route Categories (fs-tree of static GET routes)

```
routes/
├── auth/
│   ├── login                          # curl + wget
│   ├── fortify-login
│   ├── fortify-register
│   ├── fortify-forgot-password
│   ├── confirm-password
│   └── two-factor-challenge
│
├── dashboards/
│   ├── /                              # curl + wget
│   ├── dashboard                      # curl + wget
│   ├── account-dashboard
│   ├── crm-dashboard
│   ├── hrm-dashboard
│   ├── pos-dashboard
│   ├── project-dashboard
│   └── dashboard-view
│
├── hrm/
│   ├── employees                      # curl + wget + playwright
│   ├── employees/create
│   ├── employees/export
│   ├── employees/salary
│   ├── employee-profile
│   ├── employee_attendances
│   ├── employee_attendances/create
│   ├── employee_attendances/bulk-attendance
│   ├── departments
│   ├── departments/create
│   ├── designations
│   ├── designations/create
│   ├── branches
│   ├── branches/create
│   ├── leaves/create
│   ├── leaves/export
│   ├── leave_types
│   ├── leave_types/create
│   ├── holidays
│   ├── holidays/create
│   ├── holiday-calendar
│   ├── meetings                       # curl + wget + playwright
│   ├── meetings/create
│   ├── meeting-calendar
│   ├── awards
│   ├── awards/create
│   ├── award_types                    # curl + wget + playwright
│   ├── award_types/create
│   ├── promotions
│   ├── promotions/create
│   ├── resignations
│   ├── resignations/create
│   ├── terminations
│   ├── terminations/create
│   ├── warnings
│   ├── warnings/create
│   ├── complaints
│   ├── complaints/create
│   ├── trainings
│   ├── trainings/create
│   ├── trainers
│   ├── trainers/create
│   ├── training_types
│   ├── training_types/create
│   ├── transfers
│   ├── transfers/create
│   ├── travels
│   ├── travels/create
│   ├── overtimes
│   ├── announcements/create
│   ├── announcement
│   ├── company_policies
│   ├── company_policies/create
│   ├── set_salaries
│   ├── set_salaries/create
│   ├── payslips
│   ├── payslips/create
│   ├── payslips/employeepayslip
│   ├── allowances
│   ├── allowances/create
│   ├── allowance_options
│   ├── allowance_options/create
│   ├── deduction_options
│   ├── deduction_options/create
│   ├── loan_options
│   ├── loan_options/create
│   ├── loans
│   ├── other_payments
│   ├── appraisals
│   ├── appraisals/create
│   ├── goals
│   ├── goals/create
│   ├── goal_trackings
│   ├── goal_trackings/create
│   ├── goal_types
│   ├── goal_types/create
│   ├── indicators
│   ├── indicators/create
│   ├── competencies
│   ├── competencies/create
│   ├── performance_types
│   ├── performance_types/create
│   ├── documents
│   ├── documents/create
│   ├── document_uploads
│   ├── document_uploads/create
│   ├── time_trackers
│   ├── screenshots
│   └── screenshots/create
│
├── finance/
│   ├── invoices                       # curl + wget + playwright
│   ├── invoices/create
│   ├── invoices/export
│   ├── invoices/index
│   ├── invoices/items
│   ├── bills                          # curl + wget + playwright
│   ├── bills/create
│   ├── bills/export
│   ├── billsindex
│   ├── billsitems
│   ├── expenses
│   ├── expenses/create
│   ├── expenses/index
│   ├── expenses/items
│   ├── expense-list
│   ├── revenues
│   ├── revenues/create
│   ├── revenues/index
│   ├── payments
│   ├── payments/create
│   ├── payments/index
│   ├── credit-notes
│   ├── credit_notes/invoice
│   ├── custom-credit-note
│   ├── debit_notes
│   ├── debit_notes/bill
│   ├── custom-debit-note
│   ├── proposals/create
│   ├── proposals/export
│   ├── proposals/items
│   ├── proposal
│   ├── bank_accounts
│   ├── bank_accounts/create
│   ├── bank_transfers
│   ├── bank_transfers/create
│   ├── bank_transfers/index
│   ├── taxes
│   ├── taxes/create
│   ├── chart_of_accounts
│   ├── chart_of_accounts/create
│   ├── journal_entries
│   ├── journal_entries/create
│   ├── budgets
│   ├── budgets/create
│   └── transactions/export
│
├── crm/
│   ├── leads
│   ├── leads/create
│   ├── leads/list
│   ├── lead_stages
│   ├── lead_stages/create
│   ├── deals
│   ├── deals/create
│   ├── deals/list
│   ├── pipelines
│   ├── pipelines/create
│   ├── sources
│   ├── sources/create
│   ├── stages
│   ├── stages/create
│   ├── labels
│   └── labels/create
│
├── projects/
│   ├── projects
│   ├── projects/create
│   ├── projects-view
│   ├── projects-users
│   ├── project_stages
│   ├── project_stages/create
│   ├── project_task_stages
│   ├── project_task_stages/create
│   ├── project_reports
│   ├── project_reports/create
│   ├── task-board-view
│   ├── projects.timesheets/list
│   ├── projects.timesheets/list-get
│   ├── projects.timesheets/table-view
│   ├── projects.timesheets/view
│   └── projects.timesheets/append-task
│
├── pos/
│   ├── pos
│   ├── pos/create
│   ├── pos/barcode
│   ├── pos/print
│   ├── pos/data/store
│   ├── pos-print-setting
│   ├── print-setting
│   ├── printviews/pos
│   ├── warehouses
│   ├── warehouses/create
│   ├── warehouse_transfers
│   ├── warehouse_transfers/create
│   ├── purchases
│   ├── purchases/create
│   ├── purchases/items
│   ├── product_services
│   ├── product_services/create
│   ├── product_services/export
│   ├── product_services/index
│   ├── product_service_categories
│   ├── product_service_categories/create
│   ├── product_service_units
│   ├── product_service_units/create
│   ├── product_stocks
│   ├── product_stocks/create
│   ├── product_stocks/export
│   ├── product-categories
│   ├── orders
│   ├── coupons
│   └── coupons/create
│
├── reports/
│   ├── reports/account-statement-report
│   ├── reports/bill-summary
│   ├── reports/expense-summary
│   ├── reports/income-summary
│   ├── reports/income-vs-expense-summary
│   ├── reports/invoice-report
│   ├── reports/invoice-summary
│   ├── reports/leave
│   ├── reports/payables
│   ├── reports/payrolls/export
│   ├── reports/product-stock-report
│   ├── reports/receivables
│   ├── reports/sales
│   ├── reports/tax-summary
│   ├── reports/transaction
│   ├── reports/trial-balance
│   ├── reports-daily-pos
│   ├── reports-daily-purchase
│   ├── reports-deal
│   ├── reports-lead
│   ├── reports-leave
│   ├── reports-monthly-attendance
│   ├── reports-monthly-cashflow
│   ├── reports-monthly-pos
│   ├── reports-monthly-purchase
│   ├── reports-payroll
│   ├── reports-pos-vs-purchase
│   ├── reports-quarterly-cashflow
│   └── reports-warehouse
│
├── recruitment/
│   ├── jobs
│   ├── jobs/create
│   ├── job-application
│   ├── job-applications/create
│   ├── job-category
│   ├── job-categories/create
│   ├── job-stage
│   ├── job-stages/create
│   ├── interview-schedule
│   ├── interview-schedules/create
│   ├── job-onboard
│   ├── candidates-job-applications
│   ├── custom-question
│   └── custom-questions/create
│
├── contracts/
│   ├── contracts
│   ├── contracts/create
│   ├── contracts/grid
│   ├── contract_types
│   └── contract_types/create
│
├── admin/
│   ├── users
│   ├── users/create
│   ├── users-view
│   ├── users/logs
│   ├── users/profile
│   ├── last-login
│   ├── roles
│   ├── roles/create
│   ├── permissions
│   ├── permissions/create
│   ├── settings
│   ├── email_template
│   ├── email_templates/create
│   ├── notification_templates
│   ├── notification_templates/create
│   ├── webhook-settings/create
│   ├── systems
│   ├── custom_fields
│   ├── custom_fields/create
│   ├── plans
│   ├── plans/create
│   ├── plan_requests
│   ├── pricing_plans
│   ├── pricing_plans/create
│   ├── form_builders
│   └── form_builders/create
│
├── assets/
│   ├── account_assets
│   ├── account_assets/create
│   └── account_statements/export
│
├── support/
│   ├── supports
│   ├── supports/create
│   └── supports/grid
│
├── saas/
│   ├── customers
│   ├── customers/create
│   ├── customers/export
│   ├── commissions
│   ├── commissions/create
│   ├── discover
│   └── discover/create
│
├── communication/
│   ├── chats
│   ├── chats/get-contacts
│   ├── chats/search
│   ├── events
│   ├── events/create
│   ├── zoom_meetings
│   ├── zoom_meetings/create
│   └── zoom-meeting-calendar
│
├── frontend/
│   ├── home_section
│   ├── home_section/create
│   ├── features
│   ├── features/create
│   ├── faqs
│   ├── faqs/create
│   ├── testimonials
│   ├── testimonials/create
│   ├── join_us
│   ├── join_us/create
│   ├── custom_pages
│   ├── custom_pages/create
│   ├── landingpage
│   ├── landingpage/create
│   ├── about-us
│   ├── privacy-policy
│   └── terms-and-conditions
│
└── misc/
    ├── profile
    ├── search
    ├── home
    ├── leave
    ├── bug_status
    ├── bug_statuses/create
    ├── bugs_reports/{view?}
    ├── changes/mode
    ├── change-languages/{lang}
    └── create-language
```

---

## Testing Methods

### 1. curl — HTTP status code verification

```bash
# Pattern: cookie-authenticated GET requests
COOKIE_FILE="cookies.txt"

# Login first
curl -s -c "$COOKIE_FILE" -b "$COOKIE_FILE" \
  -X POST http://localhost:8000/login \
  -d "email=admin@example.com&password=1234&_token=$(curl -s http://localhost:8000/login | grep '_token' | head -1 | sed 's/.*value="\([^"]*\)".*/\1/')" \
  -L -o /dev/null -w "%{http_code}"

# Then test each route
curl -s -b "$COOKIE_FILE" -o /dev/null -w "%{http_code}" http://localhost:8000/ROUTE
```

**Minimum coverage:** Every index route (no `{param}`) in each category above.

### 2. wget — Spider + download verification

```bash
# Spider mode (no download, check reachability)
wget --spider --load-cookies=cookies.txt http://localhost:8000/ROUTE

# Full page download for content inspection
wget --load-cookies=cookies.txt -q -O /tmp/page.html http://localhost:8000/ROUTE
```

### 3. php artisan — CLI view rendering

```bash
# Test that a view compiles without errors
php artisan view:clear && php artisan view:cache

# Test a specific route's controller response
php artisan route:list --name=ROUTE_NAME
```

### 4. Playwright — Browser rendering verification

```javascript
// Pattern: assert page renders with content
async function assertPageRenders(page, url, expectedSelector) {
  await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 15000 });
  expect(page.url()).not.toContain('/login');
  await expect(page.locator(expectedSelector)).toBeVisible({ timeout: 5000 });
}
```

**Playwright test files per module:**
- `tests/e2e/hrm.spec.cjs` — HRM routes
- `tests/e2e/financial.spec.cjs` — Finance routes
- `tests/e2e/crm.spec.cjs` — CRM routes
- `tests/e2e/pm.spec.cjs` — Project management routes
- `tests/e2e/reports.spec.cjs` — Report routes
- `tests/e2e/security-api.spec.cjs` — Security routes
- `tests/e2e/ui-triggers.spec.cjs` — UI interaction routes

---

## Coverage Requirements

| Method | Minimum Coverage | Notes |
|--------|-----------------|-------|
| curl | All index routes (no `{param}`) — ~120 routes | HTTP status code check (expect 200 or 302) |
| wget | All dashboard + module index pages — ~30 routes | `--spider` mode |
| php cli | `view:cache` must succeed | Catches Blade compilation errors |
| Playwright | All module index + create pages — per spec file | Browser rendering with assertion |

---

## Route Count Summary

| Category | Static GET Routes | Parameterised GET Routes |
|----------|------------------|------------------------|
| Auth | 6 | 0 |
| Dashboards | 8 | 0 |
| HRM | ~65 | ~30 |
| Finance | ~45 | ~40 |
| CRM | 16 | ~10 |
| Projects | ~15 | ~15 |
| POS | ~25 | ~10 |
| Reports | ~30 | 0 |
| Recruitment | 14 | ~8 |
| Admin | ~25 | ~15 |
| Other | ~30 | ~20 |
| **Total** | **~377** | **~426** |
