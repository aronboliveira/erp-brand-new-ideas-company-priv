#!/usr/bin/env python3
"""Generate mock HTML+JS test pages for every route group in the ERP system."""
import html
import json
import os
import re
from datetime import datetime, timezone
from typing import Any

ROUTES_FILE = "/tmp/routes_full.json"
OUTPUT_DIR = "/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel/tests/frontend/js/pages/mocks"

TIMESTAMP = datetime.now(timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ")

# Higher-level category mapping: first-segment -> category
CATEGORY_MAP = {
    # Auth
    "login": "auth", "register": "auth", "logout": "auth",
    "forgot-password": "auth", "reset-password": "auth",
    "confirm-password": "auth", "two-factor-challenge": "auth",
    "verify": "auth", "fortify-login": "auth", "fortify-register": "auth",
    "fortify-forgot-password": "auth", "fortify-logout": "auth",
    "change-password": "auth", "user-reset-passwords": "auth",
    "client-reset-passwords": "auth",
    # Dashboard
    "/": "dashboard", "dashboard": "dashboard", "dashboard-view": "dashboard",
    "account-dashboard": "dashboard", "crm-dashboard": "dashboard",
    "hrm-dashboard": "dashboard", "pos-dashboard": "dashboard",
    "project-dashboard": "dashboard", "home": "dashboard",
    "home_section": "dashboard",
    # CRM
    "deals": "crm", "leads": "crm", "pipelines": "crm",
    "lead_stages": "crm", "sources": "crm",
    "reports-deal": "crm", "reports-lead": "crm",
    # HRM
    "employees": "hrm", "leaves": "hrm", "leave_types": "hrm",
    "leave": "hrm", "payslips": "hrm", "payslip_types": "hrm",
    "set_salaries": "hrm", "allowances": "hrm", "allowance_options": "hrm",
    "commissions": "hrm", "saturation_deductions": "hrm",
    "deduction_options": "hrm", "other_payments": "hrm",
    "overtimes": "hrm", "loans": "hrm", "loan_options": "hrm",
    "employee_attendances": "hrm", "attendances": "hrm",
    "holidays": "hrm", "holiday-calendar": "hrm",
    "departments": "hrm", "designations": "hrm", "branches": "hrm",
    "transfers": "hrm", "promotions": "hrm", "resignations": "hrm",
    "terminations": "hrm", "terminationtypes": "hrm",
    "terminationtype": "hrm", "warnings": "hrm",
    "complaints": "hrm", "awards": "hrm", "award_types": "hrm",
    "travels": "hrm", "trainings": "hrm", "training_types": "hrm",
    "trainers": "hrm", "appraisals": "hrm", "appraisals1": "hrm",
    "indicators": "hrm", "competencies": "hrm",
    "goals": "hrm", "goal_types": "hrm", "goal_trackings": "hrm",
    "performance_types": "hrm", "company_policies": "hrm",
    "documents": "hrm", "document_uploads": "hrm",
    "employee-profile": "hrm", "show-employee-profiles": "hrm",
    "edit-profile": "hrm",
    "reports-monthly-attendance": "hrm", "reports-monthly-attendances": "hrm",
    "reports-leave": "hrm", "reports-payroll": "hrm", "reports-payrolls": "hrm",
    "announcements": "hrm", "announcement": "hrm",
    # Finance
    "invoices": "finance", "bills": "finance", "bills{id}": "finance",
    "billscreates": "finance", "billsindex": "finance",
    "billsitems": "finance", "billsproduct": "finance",
    "billsproducts": "finance", "billsvendor": "finance",
    "expenses": "finance", "expense-list": "finance",
    "revenues": "finance", "payments": "finance",
    "bank_accounts": "finance", "bank_transfers": "finance",
    "taxes": "finance", "chart_of_accounts": "finance",
    "journal_entries": "finance", "budgets": "finance",
    "account_assets": "finance", "account_statements": "finance",
    "credit_notes": "finance", "credit-notes": "finance",
    "debit_notes": "finance", "custom-credit-note": "finance",
    "custom-debit-note": "finance", "balance-sheets": "finance",
    "trial-balances": "finance", "transactions": "finance",
    "receivables": "finance", "payables": "finance",
    "reports-monthly-cashflow": "finance", "reports-quarterly-cashflow": "finance",
    "sales": "finance",
    # Projects
    "projects": "projects", "projects.timesheets": "projects",
    "project_stages": "projects", "project_task_stages": "projects",
    "project_task_stages-new": "projects", "project_reports": "projects",
    "task-board-view": "projects", "task-boards": "projects",
    "trackers": "projects", "time_trackers": "projects",
    "tracker-settings": "projects", "stop-tracker": "projects",
    "bugs_reports": "projects", "bug_statuses": "projects",
    "bug_status": "projects",
    "get-projects": "projects", "invite-project-members": "projects",
    "invite-project-user-member": "projects",
    "projects-users": "projects", "projects-view": "projects",
    "remove-user-from-projects": "projects", "share-projects": "projects",
    "update-task-priority-color": "projects",
    # Purchases
    "purchases": "purchases", "vendors": "purchases",
    "reports-daily-purchase": "purchases",
    "reports-monthly-purchase": "purchases",
    # POS
    "pos": "pos", "orders": "pos", "pos-print-setting": "pos",
    "pos-receipt": "pos", "print-setting": "pos",
    "add-to-carts": "pos", "empty-cart": "pos",
    "remove-from-cart": "pos", "update-cart": "pos",
    "warehouses": "pos", "warehouse_transfers": "pos",
    "warehouse-empty-cart": "pos", "product_stocks": "pos",
    "reports-daily-pos": "pos", "reports-monthly-pos": "pos",
    "reports-pos-vs-purchase": "pos", "reports-warehouse": "pos",
    # Products
    "product_services": "products", "product_service_categories": "products",
    "product_service_units": "products", "product-categories": "products",
    "search-products": "products", "name-search-products": "products",
    # Customers
    "customers": "customers", "clients": "customers",
    # Contracts
    "contracts": "contracts", "contract_types": "contracts",
    # Proposals
    "proposals": "proposals", "proposal": "proposals",
    # Users
    "users": "users", "user": "users", "roles": "users",
    "permissions": "users", "users-view": "users",
    "last-login": "users", "checkuserexists": "users",
    "profile": "users",
    # Settings
    "settings": "settings", "systems": "settings",
    "system-settings": "settings", "business-setting": "settings",
    "company-settings": "settings", "company-email-settings": "settings",
    "company-payment-setting": "settings",
    "email-settings": "settings", "email_templates": "settings",
    "email_template": "settings", "email_template_langs": "settings",
    "email_template_store": "settings", "email_template_stores": "settings",
    "notification_templates": "settings",
    "stripe-settings": "settings", "chatgpt-settings": "settings",
    "pusher-setting": "settings", "slack-settings": "settings",
    "telegram-settings": "settings", "twilio-settings": "settings",
    "zoom-settings": "settings", "storage-settings": "settings",
    "cache-settings": "settings", "seo-settings": "settings",
    "recaptcha-settings": "settings", "cookie-setting": "settings",
    "cookie-consent": "settings",
    # Recruitment
    "jobs": "recruitment", "job-applications": "recruitment",
    "job-application": "recruitment", "job-stages": "recruitment",
    "job-stage": "recruitment", "job-categories": "recruitment",
    "job-category": "recruitment", "custom-questions": "recruitment",
    "custom-question": "recruitment", "interview-schedules": "recruitment",
    "interview-schedule": "recruitment", "interview_schedules": "recruitment",
    "jobs_onboards": "recruitment", "job-onboard": "recruitment",
    "candidates-job-applications": "recruitment", "careers": "recruitment",
    # Forms
    "form_builders": "forms", "forms": "forms",
    "custom_fields": "forms",
    # Meetings/Events
    "meetings": "meetings", "meeting-calendar": "meetings",
    "events": "meetings", "calendars": "meetings",
    "zoom_meetings": "meetings", "zoom-meetings": "meetings",
    "zoom-meeting-calendar": "meetings",
    # Chat
    "chats": "chat",
    # Reports
    "reports": "reports",
    # Landing Page
    "landingpage": "landingpage", "features": "landingpage",
    "testimonials": "landingpage", "faqs": "landingpage",
    "discover": "landingpage", "custom_pages": "landingpage",
    "join_us": "landingpage", "screenshots": "landingpage",
    "pricing_plans": "landingpage", "about_us": "landingpage",
    "pages": "landingpage", "privacy_policy": "landingpage",
    "terms_and_conditions": "landingpage",
    # Payments
    "stripe": "payment-gateways", "stripes": "payment-gateways",
    "cashfrees": "payment-gateways", "payment-i-p-n": "payment-gateways",
    "plan-pay-with-bank": "payment-gateways",
    # Plans
    "plans": "plans", "coupons": "plans",
    "plan_requests": "plans",
    # Supports
    "supports": "support",
    # Install/Update
    "installs": "install", "install": "install",
    "updates": "install", "update": "install",
    # Misc
    "labels": "misc", "stages": "misc", "todos": "misc",
    "grammars": "misc", "signatures": "misc", "signature-store": "misc",
    "search": "misc", "changes": "misc", "langs": "misc",
    "manage-languages": "misc", "create-language": "misc",
    "store-language": "misc", "store-language-datas": "misc",
    "disable-language": "misc", "change-languages": "misc",
    "exports": "misc", "generates": "misc", "prints": "misc",
    "printviews": "misc",
    "broadcastings": "misc", "emails": "misc", "test-mail": "misc",
    "test-mails": "misc",
    "webhook-settings": "misc", "apis": "misc", "api": "misc",
    "sancta": "misc",
    # Debug
    "_debugbars": "debug", "_ignitions": "debug",
    ".well-knowns": "debug", "{uid}": "debug",
}

def route_to_category(uri: str) -> str:
    seg = uri.strip('/').split('/')[0] if uri.strip('/') else '/'
    return CATEGORY_MAP.get(seg, "other")

def sanitize_filename(s: str) -> str:
    return re.sub(r'[^a-z0-9_-]', '-', s.lower()).strip('-')

def gen_html(category: str, routes: list[dict[str, Any]]) -> str:
    safe_cat = html.escape(category)
    methods_with_body = {"POST", "PUT", "PATCH"}

    route_rows = []
    js_fetches = []

    for i, r in enumerate(routes):
        uri = r['uri']
        raw_methods = [m for m in r['method'].split('|') if m != 'HEAD']
        name = r.get('name') or ''
        action = r.get('action') or ''
        mw_list = r.get('middleware', [])
        needs_auth = any('Authenticate' in str(m) for m in mw_list)

        safe_uri = html.escape(uri)
        safe_name = html.escape(name)
        safe_action = html.escape(action)
        auth_badge = '<span class="badge auth">Auth</span>' if needs_auth else '<span class="badge public">Public</span>'

        route_rows.append(f"""      <tr data-route-idx="{i}" data-uri="/{uri}">
        <td><code>{', '.join(raw_methods)}</code></td>
        <td><code>/{safe_uri}</code></td>
        <td>{safe_name}</td>
        <td class="action-col">{safe_action}</td>
        <td>{auth_badge}</td>
        <td>
          <button class="btn-fetch" data-idx="{i}" title="Fetch endpoint">&#9654;</button>
          <span class="status" id="status-{i}"></span>
        </td>
      </tr>""")

        # For JS: pick first real method
        primary = raw_methods[0] if raw_methods else 'GET'
        has_body = primary in methods_with_body

        # Build a parameterized URI (replace {param} with example values)
        js_uri = re.sub(r'\{([^}]+)\}', '1', '/' + uri)

        js_fetches.append(f"""    case {i}:
      return {{ url: '{js_uri}', method: '{primary}', hasBody: {str(has_body).lower()} }};""")

    rows_html = "\n".join(route_rows)
    cases_js = "\n".join(js_fetches)

    return f"""<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mock Test Page — {safe_cat}</title>
  <style>
    * {{ margin: 0; padding: 0; box-sizing: border-box; }}
    body {{ font-family: system-ui, -apple-system, sans-serif; padding: 1rem; background: #fafafa; color: #222; }}
    h1 {{ font-size: 1.4rem; margin-bottom: 0.2rem; }}
    .timestamp {{ font-size: 0.75rem; color: #888; margin-bottom: 1rem; }}
    .summary {{ font-size: 0.85rem; margin-bottom: 1rem; color: #555; }}
    table {{ width: 100%; border-collapse: collapse; font-size: 0.8rem; }}
    th, td {{ border: 1px solid #ddd; padding: 4px 6px; text-align: left; vertical-align: top; }}
    th {{ background: #f0f0f0; position: sticky; top: 0; }}
    .action-col {{ max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }}
    .badge {{ padding: 1px 5px; border-radius: 3px; font-size: 0.7rem; font-weight: 600; }}
    .badge.auth {{ background: #fde68a; color: #92400e; }}
    .badge.public {{ background: #bbf7d0; color: #166534; }}
    .btn-fetch {{ cursor: pointer; border: 1px solid #aaa; background: #fff; border-radius: 3px; padding: 2px 6px; }}
    .btn-fetch:hover {{ background: #e0e0e0; }}
    .status {{ font-size: 0.75rem; margin-left: 4px; }}
    .status.ok {{ color: green; }}
    .status.err {{ color: red; }}
    .status.pending {{ color: orange; }}
    #results {{ margin-top: 1rem; }}
    .result-card {{ margin-bottom: 0.5rem; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px; background: #fff; }}
    .result-card.ok {{ border-color: #4ade80; }}
    .result-card.err {{ border-color: #f87171; }}
    .result-card pre {{ font-size: 0.7rem; max-height: 200px; overflow: auto; white-space: pre-wrap; word-break: break-all; }}
    #bulk-controls {{ margin-bottom: 0.5rem; }}
    #bulk-controls button {{ margin-right: 0.5rem; padding: 4px 10px; cursor: pointer; }}
    #filter {{ margin-bottom: 0.5rem; padding: 4px 8px; width: 300px; font-size: 0.8rem; }}
  </style>
</head>
<body>
  <h1>Mock Test Page &mdash; {safe_cat}</h1>
  <p class="timestamp">Generated: {TIMESTAMP}</p>
  <p class="summary">{len(routes)} route(s) in this group</p>

  <div id="bulk-controls">
    <button id="btn-fetch-all" title="Fetch all GET endpoints sequentially">Fetch All GET</button>
    <button id="btn-clear" title="Clear results">Clear Results</button>
    <input type="text" id="filter" placeholder="Filter routes by URI or name..." />
  </div>

  <table id="route-table">
    <thead>
      <tr>
        <th>Method</th>
        <th>URI</th>
        <th>Name</th>
        <th>Action</th>
        <th>Auth</th>
        <th>Test</th>
      </tr>
    </thead>
    <tbody>
{rows_html}
    </tbody>
  </table>

  <div id="results"></div>

  <script>
    const BASE_URL = window.__TEST_BASE_URL__ || '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function getRouteInfo(idx) {{
      switch (idx) {{
{cases_js}
        default: return null;
      }}
    }}

    async function fetchRoute(idx) {{
      const info = getRouteInfo(idx);
      if (!info) return;
      const statusEl = document.getElementById('status-' + idx);
      statusEl.textContent = '⏳';
      statusEl.className = 'status pending';
      try {{
        const opts = {{ method: info.method, credentials: 'same-origin' }};
        if (info.hasBody) {{
          opts.headers = {{ 'Content-Type': 'application/json', 'Accept': 'application/json' }};
          opts.body = JSON.stringify({{ _token: csrfToken }});
        }}
        const resp = await fetch(BASE_URL + info.url, opts);
        const text = await resp.text().catch(() => '');
        const ok = resp.status < 400;
        statusEl.textContent = resp.status;
        statusEl.className = 'status ' + (ok ? 'ok' : 'err');
        appendResult(idx, info, resp.status, text.substring(0, 2000), ok);
      }} catch (e) {{
        statusEl.textContent = 'ERR';
        statusEl.className = 'status err';
        appendResult(idx, info, 0, e.message, false);
      }}
    }}

    function appendResult(idx, info, status, body, ok) {{
      const div = document.createElement('div');
      div.className = 'result-card ' + (ok ? 'ok' : 'err');
      div.innerHTML = '<strong>' + info.method + ' ' + info.url + '</strong> &mdash; ' + status +
        '<pre>' + escapeHtml(body) + '</pre>';
      document.getElementById('results').prepend(div);
    }}

    function escapeHtml(s) {{
      const d = document.createElement('div');
      d.textContent = s;
      return d.innerHTML;
    }}

    // Bind individual fetch buttons
    document.querySelectorAll('.btn-fetch').forEach(btn => {{
      btn.addEventListener('click', () => fetchRoute(parseInt(btn.dataset.idx)));
    }});

    // Bulk fetch all GET routes
    document.getElementById('btn-fetch-all').addEventListener('click', async () => {{
      const rows = document.querySelectorAll('#route-table tbody tr');
      for (const row of rows) {{
        if (row.style.display === 'none') continue;
        const idx = parseInt(row.dataset.routeIdx);
        const info = getRouteInfo(idx);
        if (info && info.method === 'GET') {{
          await fetchRoute(idx);
          await new Promise(r => setTimeout(r, 100)); // throttle
        }}
      }}
    }});

    // Clear results
    document.getElementById('btn-clear').addEventListener('click', () => {{
      document.getElementById('results').innerHTML = '';
      document.querySelectorAll('.status').forEach(el => {{ el.textContent = ''; el.className = 'status'; }});
    }});

    // Filter
    document.getElementById('filter').addEventListener('input', (e) => {{
      const q = e.target.value.toLowerCase();
      document.querySelectorAll('#route-table tbody tr').forEach(row => {{
        const uri = row.dataset.uri || '';
        const text = row.textContent.toLowerCase();
        row.style.display = (text.includes(q) || uri.includes(q)) ? '' : 'none';
      }});
    }});
  </script>
</body>
</html>"""


# Load routes
with open(ROUTES_FILE) as f:
    routes = json.load(f)

# Group by category
categories: dict[str, list[dict[str, Any]]] = {}
for r in routes:
    cat = route_to_category(r['uri'])
    categories.setdefault(cat, []).append(r)

# Create output directory
os.makedirs(OUTPUT_DIR, exist_ok=True)

# Generate one HTML per category
created = []
for cat, cat_routes in sorted(categories.items()):
    fname = sanitize_filename(cat) + ".html"
    fpath = os.path.join(OUTPUT_DIR, fname)
    with open(fpath, "w") as f:
        f.write(gen_html(cat, cat_routes))
    created.append((cat, fname, len(cat_routes)))

# Generate index page
index_rows = []
for cat, fname, count in sorted(created, key=lambda x: -x[2]):
    index_rows.append(f'      <tr><td><a href="{fname}">{html.escape(cat)}</a></td><td>{count}</td></tr>')

index_html = f"""<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mock Test Pages — Index</title>
  <style>
    body {{ font-family: system-ui, sans-serif; padding: 1rem; }}
    h1 {{ font-size: 1.4rem; margin-bottom: 0.2rem; }}
    .timestamp {{ font-size: 0.75rem; color: #888; margin-bottom: 1rem; }}
    table {{ border-collapse: collapse; }}
    th, td {{ border: 1px solid #ddd; padding: 4px 10px; text-align: left; }}
    th {{ background: #f0f0f0; }}
    a {{ color: #2563eb; }}
  </style>
</head>
<body>
  <h1>Mock Test Pages &mdash; Route Index</h1>
  <p class="timestamp">Generated: {TIMESTAMP}</p>
  <p>{len(routes)} total routes across {len(created)} categories</p>
  <table>
    <thead><tr><th>Category</th><th>Routes</th></tr></thead>
    <tbody>
{chr(10).join(index_rows)}
    </tbody>
  </table>
</body>
</html>"""

with open(os.path.join(OUTPUT_DIR, "index.html"), "w") as f:
    f.write(index_html)
created.append(("_index", "index.html", len(routes)))

# Print summary
print(f"Created {len(created)} files in {OUTPUT_DIR}")
for cat, fname, count in created:
    print(f"  {fname:40s} {count:4d} routes")
