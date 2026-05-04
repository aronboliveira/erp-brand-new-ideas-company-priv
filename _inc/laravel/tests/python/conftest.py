# tests/python/conftest.py
"""Root conftest — adds both Exports/py and Imports/py directories to sys.path."""

import copy
import importlib.util
import sys
from pathlib import Path
from typing import Any, Callable, Dict

import pytest

_LARAVEL = Path(__file__).resolve().parents[2]  # _inc/laravel
_EXPORTS_PY = _LARAVEL / "app" / "Exports" / "py"
_IMPORTS_PY = _LARAVEL / "app" / "Imports" / "py"

# Public exports for test files
EXPORTS_PY_DIR = _EXPORTS_PY
IMPORTS_PY_DIR = _IMPORTS_PY
EXPORT_WRAPPER_DIR = _LARAVEL / "app" / "Exports" / "_withPython"
IMPORT_WRAPPER_DIR = _LARAVEL / "app" / "Imports" / "_withPython"

for _dir in (_EXPORTS_PY, _IMPORTS_PY):
    if str(_dir) not in sys.path:
        sys.path.insert(0, str(_dir))


def load_exporter_class(module_name: str):
    """Dynamically load an exporter class from app/Exports/py/<module_name>.py"""
    module_path = _EXPORTS_PY / f"{module_name}.py"
    if not module_path.exists():
        raise FileNotFoundError(f"Exporter module not found: {module_path}")

    spec = importlib.util.spec_from_file_location(module_name, module_path)
    if spec is None or spec.loader is None:
        raise ImportError(f"Could not load spec for {module_name} at {module_path}")
    module = importlib.util.module_from_spec(spec)
    sys.modules[module_name] = module
    spec.loader.exec_module(module)

    # Find the exporter class - typically <ModuleName>Exporter
    class_name = "".join(w.title() for w in module_name.replace("_exporter", "").split("_")) + "Exporter"
    if hasattr(module, class_name):
        return getattr(module, class_name)

    # Fallback: return first class that ends with Exporter
    for name in dir(module):
        obj = getattr(module, name)
        if isinstance(obj, type) and name.endswith("Exporter") and name != "BaseExporter":
            return obj

    raise AttributeError(f"No Exporter class found in {module_name}")


# ---------------------------------------------------------------------------
# Payload factories for exporter / importer tests
# ---------------------------------------------------------------------------

_EXPORTER_PAYLOADS: Dict[str, Callable[[], Dict[str, Any]]] = {
    # ── simple exporters (rows = list[dict]) ──────────────────────────
    "account_statement_exporter": lambda: {
        "currency_symbol": "$",
        "rows": [
            {"id": "STMT-001", "date": "2026-01-05", "amount": 1500.50, "description": "Invoice payment received"},
            {"id": "STMT-002", "date": "2026-01-12", "amount": -320.00, "description": "Office supplies purchase"},
            {"id": "STMT-003", "date": "2026-01-20", "amount": 750.25, "description": "Consulting fee"},
        ],
    },
    "customer_exporter": lambda: {
        "currency_symbol": "$",
        "rows": [
            {
                "customer_no": "CUST-001", "name": "Acme Corp", "email": "acme@example.com",
                "contact": "+1-555-0100", "billing_name": "Acme Corp",
                "billing_country": "US", "billing_state": "CA", "billing_city": "San Francisco",
                "billing_phone": "+1-555-0101", "billing_zip": "94105",
                "billing_address": "123 Market St", "shipping_name": "Acme Warehouse",
                "shipping_country": "US", "shipping_state": "CA", "shipping_city": "Oakland",
                "shipping_phone": "+1-555-0102", "shipping_zip": "94607",
                "shipping_address": "456 Industrial Blvd", "balance": 2500.00,
            },
            {
                "customer_no": "CUST-002", "name": "Globex Inc", "email": "info@globex.com",
                "contact": "+1-555-0200", "billing_name": "Globex Inc",
                "billing_country": "US", "billing_state": "NY", "billing_city": "New York",
                "billing_phone": "+1-555-0201", "billing_zip": "10001",
                "billing_address": "789 Broadway", "shipping_name": "Globex Depot",
                "shipping_country": "US", "shipping_state": "NJ", "shipping_city": "Newark",
                "shipping_phone": "+1-555-0202", "shipping_zip": "07102",
                "shipping_address": "321 Shipping Ln", "balance": 1800.75,
            },
            {
                "customer_no": "CUST-003", "name": "Initech LLC", "email": "bill@initech.com",
                "contact": "+1-555-0300", "billing_name": "Initech LLC",
                "billing_country": "US", "billing_state": "TX", "billing_city": "Austin",
                "billing_phone": "+1-555-0301", "billing_zip": "73301",
                "billing_address": "100 Tech Dr", "shipping_name": "Initech LLC",
                "shipping_country": "US", "shipping_state": "TX", "shipping_city": "Austin",
                "shipping_phone": "+1-555-0301", "shipping_zip": "73301",
                "shipping_address": "100 Tech Dr", "balance": 0.0,
            },
        ],
    },
    "employee_exporter": lambda: {
        "currency_symbol": "$",
        "rows": [
            {
                "name": "Alice Johnson", "dob": "1990-05-15", "gender": "Female",
                "phone": "+1-555-1001", "address": "10 Elm St, Springfield",
                "email": "alice@company.com", "branch": "HQ", "department": "Engineering",
                "designation": "Senior Developer", "join_date": "2020-03-01",
                "account_holder_name": "Alice Johnson", "account_number": "1234567890",
                "bank_name": "First National", "bank_identifier_code": "FNATUS33",
                "branch_location": "Springfield Branch", "salary": 85000.0,
            },
            {
                "name": "Bob Smith", "dob": "1985-11-22", "gender": "Male",
                "phone": "+1-555-1002", "address": "20 Oak Ave, Shelbyville",
                "email": "bob@company.com", "branch": "West", "department": "Sales",
                "designation": "Sales Manager", "join_date": "2018-07-15",
                "account_holder_name": "Bob Smith", "account_number": "9876543210",
                "bank_name": "Commerce Bank", "bank_identifier_code": "CMBKUS44",
                "branch_location": "Shelbyville Branch", "salary": 72000.0,
            },
            {
                "name": "Carol Davis", "dob": "1993-02-28", "gender": "Female",
                "phone": "+1-555-1003", "address": "30 Pine Rd, Capital City",
                "email": "carol@company.com", "branch": "East", "department": "HR",
                "designation": "HR Specialist", "join_date": "2022-01-10",
                "account_holder_name": "Carol Davis", "account_number": "5678901234",
                "bank_name": "Union Credit", "bank_identifier_code": "UCRDUS55",
                "branch_location": "Capital City Branch", "salary": 62000.0,
            },
        ],
    },
    "payroll_exporter": lambda: {
        "currency_symbol": "$",
        "rows": [
            {
                "employee_id": "EMP-001", "status": 1, "employee_name": "Alice Johnson",
                "gross_salary": 7083.33, "net_payable": 5500.00, "salary_month": "Jan 2026",
            },
            {
                "employee_id": "EMP-002", "status": 0, "employee_name": "Bob Smith",
                "gross_salary": 6000.00, "net_payable": 4700.00, "salary_month": "Jan 2026",
            },
            {
                "employee_id": "EMP-003", "status": 1, "employee_name": "Carol Davis",
                "gross_salary": 5166.67, "net_payable": 4100.00, "salary_month": "Jan 2026",
            },
        ],
    },
    "payslip_exporter": lambda: {
        "currency_symbol": "$",
        "rows": [
            {
                "emp_id": "EMP-001", "name": "Alice Johnson", "gross_salary": 7083.33,
                "net_payable": 5500.00, "status": 1, "account_holder_name": "Alice Johnson",
                "account_number": "1234567890", "bank_name": "First National",
                "bank_identifier_code": "FNATUS33", "branch_location": "Springfield",
                "tax_payer_id": "TAX-A001",
            },
            {
                "emp_id": "EMP-002", "name": "Bob Smith", "gross_salary": 6000.00,
                "net_payable": 4700.00, "status": 0, "account_holder_name": "Bob Smith",
                "account_number": "9876543210", "bank_name": "Commerce Bank",
                "bank_identifier_code": "CMBKUS44", "branch_location": "Shelbyville",
                "tax_payer_id": "TAX-B002",
            },
            {
                "emp_id": "EMP-003", "name": "Carol Davis", "gross_salary": 5166.67,
                "net_payable": 4100.00, "status": 1, "account_holder_name": "Carol Davis",
                "account_number": "5678901234", "bank_name": "Union Credit",
                "bank_identifier_code": "UCRDUS55", "branch_location": "Capital City",
                "tax_payer_id": "TAX-C003",
            },
        ],
    },
    "product_service_exporter": lambda: {
        "currency_symbol": "$",
        "rows": [
            {
                "id": 1, "name": "Widget A", "sku": "WGT-001",
                "sale_price": 29.99, "purchase_price": 15.00,
                "tax": "GST 10%", "category": "Widgets",
                "unit": "pcs", "type": "Product", "description": "Standard widget",
            },
            {
                "id": 2, "name": "Service Plan B", "sku": "SVC-002",
                "sale_price": 199.00, "purchase_price": 0,
                "tax": "VAT 5%", "category": "Services",
                "unit": "hour", "type": "Service", "description": "Monthly maintenance",
            },
            {
                "id": 3, "name": "Gadget C", "sku": "GDG-003",
                "sale_price": 49.50, "purchase_price": 22.75,
                "tax": "GST 10%", "category": "Gadgets",
                "unit": "pcs", "type": "Product", "description": "Premium gadget",
            },
        ],
    },
    "transaction_exporter": lambda: {
        "currency_symbol": "$",
        "rows": [
            {
                "id": "TXN-001", "account": "Cash", "type": "Credit", "amount": 5000.00,
                "description": "Client payment", "date": "2026-01-05", "category": "Revenue",
            },
            {
                "id": "TXN-002", "account": "Expenses", "type": "Debit", "amount": 1200.00,
                "description": "Rent payment", "date": "2026-01-10", "category": "Operating",
            },
            {
                "id": "TXN-003", "account": "Cash", "type": "Debit", "amount": 350.00,
                "description": "Utility bill", "date": "2026-01-15", "category": "Operating",
            },
        ],
    },
    "vendor_exporter": lambda: {
        "currency_symbol": "$",
        "rows": [
            {
                "vendor_no": "VND-001", "name": "SupplyCo", "email": "orders@supplyco.com",
                "contact": "+1-555-2001", "billing_name": "SupplyCo Ltd",
                "billing_country": "US", "billing_state": "IL", "billing_city": "Chicago",
                "billing_phone": "+1-555-2002", "billing_zip": "60601",
                "billing_address": "500 Wacker Dr", "shipping_name": "SupplyCo Warehouse",
                "shipping_country": "US", "shipping_state": "IL", "shipping_city": "Chicago",
                "shipping_phone": "+1-555-2003", "shipping_zip": "60602",
                "shipping_address": "600 Industrial Ave", "balance": 3200.50,
            },
            {
                "vendor_no": "VND-002", "name": "Parts R Us", "email": "sales@partsrus.com",
                "contact": "+1-555-2010", "billing_name": "Parts R Us Inc",
                "billing_country": "US", "billing_state": "OH", "billing_city": "Columbus",
                "billing_phone": "+1-555-2011", "billing_zip": "43215",
                "billing_address": "200 High St", "shipping_name": "Parts R Us Depot",
                "shipping_country": "US", "shipping_state": "OH", "shipping_city": "Columbus",
                "shipping_phone": "+1-555-2012", "shipping_zip": "43215",
                "shipping_address": "210 Depot Ln", "balance": 890.00,
            },
            {
                "vendor_no": "VND-003", "name": "Raw Materials Ltd", "email": "info@rawmat.com",
                "contact": "+1-555-2020", "billing_name": "Raw Materials Ltd",
                "billing_country": "CA", "billing_state": "ON", "billing_city": "Toronto",
                "billing_phone": "+1-555-2021", "billing_zip": "M5H 2N2",
                "billing_address": "1 King St W", "shipping_name": "Raw Materials Ltd",
                "shipping_country": "CA", "shipping_state": "ON", "shipping_city": "Toronto",
                "shipping_phone": "+1-555-2021", "shipping_zip": "M5H 2N2",
                "shipping_address": "1 King St W", "balance": 0.0,
            },
        ],
    },
    # ── hierarchical / special exporters ──────────────────────────────
    "balance_sheet_exporter": lambda: {
        "company_name": "PrestTech",
        "start_date": "2026-01-01",
        "end_date": "2026-01-31",
        "currency_symbol": "$",
        "rows": {
            "Assets": [
                {
                    "subType": "Current Assets",
                    "account": [
                        {"account_name": "Cash", "account_no": "1000", "netAmount": 1500.50},
                        {"account_name": "Accounts Receivable", "account_no": "1100", "netAmount": 500.00},
                    ],
                },
                {
                    "subType": "Fixed Assets",
                    "account": [
                        {"account_name": "Equipment", "account_no": "1500", "netAmount": 3000.00},
                    ],
                },
            ],
            "Liabilities": [
                {
                    "subType": "Current Liabilities",
                    "account": [
                        {"account_name": "Accounts Payable", "account_no": "2000", "netAmount": 1000.00},
                    ],
                },
            ],
            "Equity": [
                {
                    "subType": "Owner's Equity",
                    "account": [
                        {"account_name": "Retained Earnings", "account_no": "3000", "netAmount": 4000.50},
                    ],
                },
            ],
        },
    },
    "sales_report_exporter": lambda: {
        "company_name": "PrestTech",
        "start_date": "2026-01-01",
        "end_date": "2026-01-31",
        "report_name": "Item",
        "currency_symbol": "$",
        "rows": [
            {"name": "Integration", "invoice_count": 5, "price": 1500.0, "avg_price": 300.0},
            {"name": "Widget", "invoice_count": 3, "price": 1200.0, "avg_price": 400.0},
            {"name": "Service", "invoice_count": 4, "price": 1000.0, "avg_price": 250.0},
            {"name": "Support", "invoice_count": 2, "price": 800.0, "avg_price": 400.0},
            {"name": "Hosting", "invoice_count": 3, "price": 450.0, "avg_price": 150.0},
            {"name": "Consulting", "invoice_count": 2, "price": 320.0, "avg_price": 160.0},
        ],
    },
    "trial_balance_exporter": lambda: {
        "company_name": "PrestTech",
        "start_date": "2026-01-01",
        "end_date": "2026-01-31",
        "currency_symbol": "$",
        "rows": {
            "Assets": [
                {"name": "Cash", "code": "1000", "totalDebit": 1000.0, "totalCredit": 0},
                {"name": "Accounts Receivable", "code": "1100", "totalDebit": 500.0, "totalCredit": 0},
            ],
            "Liabilities": [
                {"name": "Accounts Payable", "code": "2000", "totalDebit": 0, "totalCredit": 1000.0},
                {"name": "Loan Payable", "code": "2100", "totalDebit": 0, "totalCredit": 500.0},
            ],
        },
    },
}


@pytest.fixture()
def exporter_payloads() -> Dict[str, Callable[[], Dict[str, Any]]]:
    """Return a dict of *factory callables* keyed by exporter module name.

    Each callable returns a fresh deep-copy of the payload so tests can
    mutate it freely without cross-contamination.
    """
    def _factory(v: Callable[[], Dict[str, Any]]) -> Callable[[], Dict[str, Any]]:
        return lambda: copy.deepcopy(v())
    return {key: _factory(val) for key, val in _EXPORTER_PAYLOADS.items()}


@pytest.fixture()
def attendance_import_payload() -> Dict[str, Any]:
    """Payload for AttendanceImporter tests.

    Contains 5 rows: 2 valid, 3 invalid (missing employee_id, non-dict,
    invalid date) so that imported=2, skipped=3.
    """
    return {
        "rows": [
            # row 0 — invalid: employee_id is None → "missing employee_id"
            {"employee_id": None, "date": "2026-01-10", "status": "Present", "clock_in": "09:00"},
            # row 1 — invalid: not a dict → "row is not a dict"
            "not-a-dict",
            # row 2 — valid
            {
                "employee_id": "EMP-100",
                "date": "01/15/2026",
                "status": "Present",
                "clock_in": "09:00",
                "clock_out": "18:00",
                "late": "0",
                "early_leaving": "0",
                "overtime": "1",
                "total_rest": "1",
            },
            # row 3 — invalid: unparseable date → "missing or invalid date"
            {"employee_id": "EMP-102", "date": "bad-date", "status": "Absent"},
            # row 4 — valid: empty status defaults to "Present"
            {
                "employee_id": "EMP-101",
                "date": "02/20/2026",
                "status": "",
                "clock_in": "08:30",
            },
        ],
    }
