from __future__ import annotations

import importlib
import json
import sys
from pathlib import Path
from typing import Any, Callable

import pytest


LARAVEL_ROOT = Path(__file__).resolve().parents[2]
EXPORTS_PY_DIR = LARAVEL_ROOT / "app" / "Exports" / "py"
IMPORTS_PY_DIR = LARAVEL_ROOT / "app" / "Imports" / "py"
EXPORT_WRAPPER_DIR = LARAVEL_ROOT / "app" / "Exports" / "_withPython"
IMPORT_WRAPPER_DIR = LARAVEL_ROOT / "app" / "Imports" / "_withPython"

for candidate in (str(EXPORTS_PY_DIR), str(IMPORTS_PY_DIR)):
    if candidate not in sys.path:
        sys.path.insert(0, candidate)


def _sales_rows() -> list[dict[str, Any]]:
    return [
        {"name": "Consulting", "invoice_count": 3, "price": 1200.0, "avg_price": 400.0},
        {"name": "Hosting", "invoice_count": 7, "price": 700.0, "avg_price": 100.0},
        {"name": "Support", "invoice_count": 4, "price": 920.0, "avg_price": 230.0},
        {"name": "Integration", "invoice_count": 2, "price": 1500.0, "avg_price": 750.0},
        {"name": "Training", "invoice_count": 1, "price": 350.0, "avg_price": 350.0},
        {"name": "Audit", "invoice_count": 2, "price": 600.0, "avg_price": 300.0},
    ]


def export_payload_factories() -> dict[str, Callable[[], dict[str, Any]]]:
    return {
        "account_statement_exporter": lambda: {
            "currency_symbol": "$",
            "rows": [
                {"id": "ST-001", "date": "2026-01-10", "amount": "$1,250.40", "description": "Opening balance"},
                {"id": "ST-002", "date": "2026-01-11T10:30:00Z", "amount": "(200.00)", "description": "Adjustment"},
            ],
        },
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
                            {"account_name": "Cash", "account_no": "1010", "netAmount": "1500.50"},
                            {"account_name": "Receivables", "account_no": "1020", "netAmount": "500.00"},
                        ],
                    },
                    {
                        "subType": "Fixed Assets",
                        "account": [
                            {"account_name": "Equipment", "account_no": "1500", "netAmount": "3000.00"},
                        ],
                    },
                ],
                "Liabilities": [
                    {
                        "subType": "Current Liabilities",
                        "account": [
                            {"account_name": "Payables", "account_no": "2010", "netAmount": "1000.00"},
                        ],
                    }
                ],
                "Equity": [
                    {
                        "subType": "Owner Equity",
                        "account": [
                            {"account_name": "Capital", "account_no": "3010", "netAmount": "4000.50"},
                        ],
                    }
                ],
            },
        },
        "customer_exporter": lambda: {
            "currency_symbol": "$",
            "rows": [
                {
                    "customer_no": "CUS-001",
                    "name": "Acme Corp",
                    "email": "finance@acme.test",
                    "contact": "+55-11-99999-0000",
                    "billing_name": "Acme Billing",
                    "billing_country": "BR",
                    "billing_state": "SP",
                    "billing_city": "Sao Paulo",
                    "billing_phone": "+55-11-0000-0000",
                    "billing_zip": "01000-000",
                    "billing_address": "Billing Street 1",
                    "shipping_name": "Acme Warehouse",
                    "shipping_country": "BR",
                    "shipping_state": "SP",
                    "shipping_city": "Sao Paulo",
                    "shipping_phone": "+55-11-1111-1111",
                    "shipping_zip": "01000-001",
                    "shipping_address": "Shipping Street 2",
                    "balance": "99.95",
                }
            ],
        },
        "employee_exporter": lambda: {
            "currency_symbol": "$",
            "rows": [
                {
                    "name": "Jane Doe",
                    "dob": "1990-05-03",
                    "gender": "Female",
                    "phone": "+55-11-2222-2222",
                    "address": "Av Paulista 1000",
                    "email": "jane.doe@test.local",
                    "branch": "HQ",
                    "department": "Engineering",
                    "designation": "Lead",
                    "join_date": "2024-01-15",
                    "account_holder_name": "Jane Doe",
                    "account_number": "12345678",
                    "bank_name": "ERP Bank",
                    "bank_identifier_code": "ERPBRSP",
                    "branch_location": "Sao Paulo",
                    "salary": "10000.00",
                }
            ],
        },
        "payroll_exporter": lambda: {
            "currency_symbol": "$",
            "rows": [
                {
                    "employee_id": "EMP-101",
                    "status": 1,
                    "employee_name": "Jane Doe",
                    "gross_salary": "10000.00",
                    "net_payable": "8600.00",
                    "salary_month": "2026-01",
                }
            ],
        },
        "payslip_exporter": lambda: {
            "currency_symbol": "$",
            "rows": [
                {
                    "emp_id": "EMP-101",
                    "name": "Jane Doe",
                    "gross_salary": "10000.00",
                    "net_payable": "8600.00",
                    "status": 1,
                    "account_holder_name": "Jane Doe",
                    "account_number": "12345678",
                    "bank_name": "ERP Bank",
                    "bank_identifier_code": "ERPBRSP",
                    "branch_location": "Sao Paulo",
                    "tax_payer_id": "CPF-0001",
                }
            ],
        },
        "product_service_exporter": lambda: {
            "currency_symbol": "$",
            "rows": [
                {
                    "id": 10,
                    "name": "Managed Service",
                    "sku": "MS-001",
                    "sale_price": "1500.00",
                    "purchase_price": "1200.00",
                    "tax": "VAT 10%",
                    "category": "Services",
                    "unit": "Hours",
                    "type": "Service",
                    "description": "Managed support package",
                }
            ],
        },
        "sales_report_exporter": lambda: {
            "company_name": "PrestTech",
            "start_date": "2026-01-01",
            "end_date": "2026-01-31",
            "report_name": "Item",
            "currency_symbol": "$",
            "rows": _sales_rows(),
        },
        "transaction_exporter": lambda: {
            "currency_symbol": "$",
            "rows": [
                {
                    "id": "TX-001",
                    "account": "Cash",
                    "type": "Credit",
                    "amount": "$250.75",
                    "description": "Customer payment",
                    "date": "2026-02-01T08:00:00Z",
                    "category": "Receipts",
                }
            ],
        },
        "trial_balance_exporter": lambda: {
            "company_name": "PrestTech",
            "start_date": "2026-01-01",
            "end_date": "2026-01-31",
            "currency_symbol": "$",
            "rows": {
                "Assets": [
                    {"name": "Cash", "code": "1010", "totalDebit": "1000.00", "totalCredit": "0.00"},
                    {"name": "Receivables", "code": "1020", "totalDebit": "500.00", "totalCredit": "0.00"},
                ],
                "Revenue": [
                    {"name": "Sales", "code": "4010", "totalDebit": "0.00", "totalCredit": "1500.00"},
                ],
            },
        },
        "vendor_exporter": lambda: {
            "currency_symbol": "$",
            "rows": [
                {
                    "vendor_no": "VEN-001",
                    "name": "Supplier Inc",
                    "email": "ops@supplier.test",
                    "contact": "+55-11-3333-3333",
                    "billing_name": "Supplier Billing",
                    "billing_country": "BR",
                    "billing_state": "SP",
                    "billing_city": "Campinas",
                    "billing_phone": "+55-11-4444-4444",
                    "billing_zip": "13000-000",
                    "billing_address": "Vendor Street 1",
                    "shipping_name": "Supplier Warehouse",
                    "shipping_country": "BR",
                    "shipping_state": "SP",
                    "shipping_city": "Campinas",
                    "shipping_phone": "+55-11-5555-5555",
                    "shipping_zip": "13000-001",
                    "shipping_address": "Vendor Street 2",
                    "balance": "250.00",
                }
            ],
        },
    }


def attendance_payload() -> dict[str, Any]:
    return {
        "rows": [
            {"employee_id": "EMP-100", "date": "2026-01-10", "status": "Present", "clock_in": "09:00"},
            {"employee_id": "EMP-101", "date": "10/01/2026", "status": "", "clock_in": "09:05"},
            {"employee_id": None, "date": "2026-01-10"},
            "bad-row",
            {"employee_id": "EMP-102", "date": "invalid-date"},
        ]
    }


def load_module(module_name: str):
    return importlib.import_module(module_name)


def load_exporter_class(module_name: str):
    module = load_module(module_name)
    class_name = "".join(part.title() for part in module_name.split("_"))
    return getattr(module, class_name)


def load_importer_class(module_name: str):
    module = load_module(module_name)
    class_name = "".join(part.title() for part in module_name.split("_"))
    return getattr(module, class_name)


def patch_stdin(monkeypatch: pytest.MonkeyPatch, payload: dict[str, Any]) -> None:
    import io

    monkeypatch.setattr(sys, "stdin", io.StringIO(json.dumps(payload)))


@pytest.fixture
def exporter_payloads() -> dict[str, Callable[[], dict[str, Any]]]:
    return export_payload_factories()


@pytest.fixture
def attendance_import_payload() -> dict[str, Any]:
    return attendance_payload()

