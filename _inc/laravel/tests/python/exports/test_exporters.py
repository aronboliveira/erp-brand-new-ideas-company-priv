"""Parametrized tests for all 19 concrete Python exporter classes.

Each exporter is tested for:
  1. get_headings() returns correct list
  2. process_data() with valid sample input → correct DataFrame
  3. process_data() with empty rows → empty DataFrame
  4. export() end-to-end writes xlsx to file
  5. Performance: process_data with 50 rows within 5 s
"""

import json
import os
import tempfile
from io import StringIO
from typing import Any, Dict, List, Type
from unittest.mock import patch

import pandas as pd
import pytest

from base_exporter import BaseExporter

# Import all 19 exporter classes
from account_statement_exporter import AccountStatementExporter
from balance_sheet_exporter import BalanceSheetExporter
from bill_exporter import BillExporter
from customer_exporter import CustomerExporter
from employee_exporter import EmployeeExporter
from invoice_exporter import InvoiceExporter
from leave_report_exporter import LeaveReportExporter
from payroll_exporter import PayrollExporter
from payslip_exporter import PayslipExporter
from product_service_exporter import ProductServiceExporter
from product_stock_exporter import ProductStockExporter
from profit_loss_exporter import ProfitLossExporter
from proposal_exporter import ProposalExporter
from receivable_exporter import ReceivableExporter
from sales_report_exporter import SalesReportExporter
from task_report_exporter import TaskReportExporter
from transaction_exporter import TransactionExporter
from trial_balance_exporter import TrialBalanceExporter
from vendor_exporter import VendorExporter


# ── Sample data factories ──────────────────────────────────────────


def _account_statement_row() -> Dict[str, Any]:
    return {"id": "AS-001", "date": "2025-06-15", "amount": 500.0, "description": "Payment"}


def _bill_row() -> Dict[str, Any]:
    return {
        "bill_id": "B-001", "customer": "Acme", "bill_date": "2025-06-01",
        "due_date": "2025-07-01", "status": "Paid", "total_amount": 1000, "paid_amount": 1000,
    }


def _customer_row() -> Dict[str, Any]:
    return {
        "id": "C-001", "name": "Alice", "email": "a@b.com", "contact": "123",
        "billing_name": "A", "billing_country": "US", "billing_state": "CA",
        "billing_city": "LA", "billing_phone": "555", "billing_zip": "90001",
        "billing_address": "123 St", "shipping_name": "A", "shipping_country": "US",
        "shipping_state": "CA", "shipping_city": "LA", "shipping_phone": "555",
        "shipping_zip": "90001", "shipping_address": "123 St", "balance": 250,
    }


def _employee_row() -> Dict[str, Any]:
    return {
        "employee_id": "E-001", "name": "Bob", "email": "b@b.com", "phone": "555",
        "dob": "1990-01-01", "company_doj": "2020-01-15", "gender": "Male",
        "address": "456 Ave", "branch": "HQ", "department": "Eng",
        "designation": "Dev", "salary_type": "Monthly", "salary": 5000,
        "account_holder_name": "Bob", "account_number": "123456",
        "bank_name": "Bank",
    }


def _invoice_row() -> Dict[str, Any]:
    return {
        "invoice_id": "I-001", "customer": "Acme", "issue_date": "2025-06-01",
        "due_date": "2025-07-01", "status": "Sent", "total_amount": 2000,
        "paid_amount": 500,
    }


def _leave_report_row() -> Dict[str, Any]:
    return {
        "employee_id": "E-001", "employee_name": "Bob",
        "approved": 5, "rejected": 1, "pending": 2,
    }


def _payroll_row() -> Dict[str, Any]:
    return {
        "employee_id": "E-001", "status": 1, "employee_name": "Bob",
        "gross_salary": 5000, "net_payable": 4500, "salary_month": "2025-06",
    }


def _payslip_row() -> Dict[str, Any]:
    return {
        "emp_id": "E-001", "name": "Bob", "gross_salary": 5000, "net_payable": 4500,
        "status": 1, "account_holder_name": "Bob", "account_number": "123456",
        "bank_name": "Bank", "bank_identifier_code": "BIC", "branch_location": "NYC",
        "tax_payer_id": "TX-001",
    }


def _product_service_row() -> Dict[str, Any]:
    return {
        "id": "P-001", "name": "Widget", "sku": "W-100", "sale_price": 29.99,
        "purchase_price": 15.0, "tax": "10%", "category": "Parts",
        "unit": "pcs", "type": "Product", "description": "A widget",
    }


def _product_stock_row() -> Dict[str, Any]:
    return {
        "id": "S-001", "product_name": "Widget", "quantity": 100,
        "type": "Purchase", "description": "Initial stock", "date": "2025-06-01",
    }


def _proposal_row() -> Dict[str, Any]:
    return {
        "id": "PR-001", "proposal_no": "PRP-100", "issue_date": "2025-06-01",
        "send_date": "2025-06-02", "category": "IT", "status": "Open",
    }


def _receivable_row() -> Dict[str, Any]:
    return {
        "name": "Acme Inc", "total_price": 5000, "pay_price": 2000,
        "credit_price": 500,
    }


def _sales_report_row() -> Dict[str, Any]:
    return {
        "name": "Widget", "invoice_count": 10, "price": 2999,
        "avg_price": 299.9, "total_tax": 150,
    }


def _task_report_row() -> Dict[str, Any]:
    return {
        "id": "T-001", "title": "Fix Bug", "description": "Fix the bug",
        "start_date": "2025-06-01", "end_date": "2025-06-15",
        "priority": "High", "assign_to": "Bob", "milestone": "v1", "status": "Open",
    }


def _transaction_row() -> Dict[str, Any]:
    return {
        "id": "TX-001", "account": "Cash", "type": "Credit",
        "amount": 1500, "description": "Sale", "date": "2025-06-15",
        "category": "Revenue",
    }


def _vendor_row() -> Dict[str, Any]:
    return {
        "vendor_no": "V-001", "name": "Acme", "email": "v@a.com", "contact": "555",
        "billing_name": "A", "billing_country": "US", "billing_state": "CA",
        "billing_city": "LA", "billing_phone": "555", "billing_zip": "90001",
        "billing_address": "123 St", "shipping_name": "A", "shipping_country": "US",
        "shipping_state": "CA", "shipping_city": "LA", "shipping_phone": "555",
        "shipping_zip": "90001", "shipping_address": "123 St", "balance": 100,
    }


# ── Balance Sheet / Profit Loss / Trial Balance special data ───────


def _balance_sheet_data() -> Dict[str, Any]:
    return {
        "rows": {
            "Assets": [
                {
                    "subType": "Current Assets",
                    "account": [
                        {"account_name": "Cash", "account_no": "100", "netAmount": 5000},
                    ],
                }
            ],
        },
        "company_name": "TestCo",
        "start_date": "2025-01-01",
        "end_date": "2025-12-31",
    }


def _profit_loss_data() -> Dict[str, Any]:
    return {
        "rows": [
            {
                "Type": "Income",
                "account": [
                    {"account_name": "Sales", "account_code": "400", "netAmount": 10000},
                    {"account_name": "Total Income", "account_code": "", "netAmount": 10000},
                ],
            },
        ],
        "company_name": "TestCo",
        "start_date": "2025-01-01",
        "end_date": "2025-12-31",
    }


def _trial_balance_data() -> Dict[str, Any]:
    return {
        "rows": {
            "Assets": [
                {"name": "Cash", "code": "100", "totalDebit": 5000, "totalCredit": 0},
            ],
        },
        "company_name": "TestCo",
        "start_date": "2025-01-01",
        "end_date": "2025-12-31",
    }


# ── Exporter registry ─────────────────────────────────────────────

# Each tuple: (ExporterClass, expected_heading_count, sample_data_builder)
# For most: data = {"rows": [row_builder()]}
# For special ones: custom data builder

_SIMPLE_EXPORTERS: List[tuple] = [
    (AccountStatementExporter, 4, lambda n: {"rows": [_account_statement_row() for _ in range(n)]}),
    (BillExporter, 7, lambda n: {"rows": [_bill_row() for _ in range(n)]}),
    (CustomerExporter, 19, lambda n: {"rows": [_customer_row() for _ in range(n)]}),
    (EmployeeExporter, 16, lambda n: {"rows": [_employee_row() for _ in range(n)]}),
    (InvoiceExporter, 8, lambda n: {"rows": [_invoice_row() for _ in range(n)]}),
    (LeaveReportExporter, 5, lambda n: {"rows": [_leave_report_row() for _ in range(n)]}),
    (PayrollExporter, 6, lambda n: {"rows": [_payroll_row() for _ in range(n)]}),
    (PayslipExporter, 11, lambda n: {"rows": [_payslip_row() for _ in range(n)]}),
    (ProductServiceExporter, 10, lambda n: {"rows": [_product_service_row() for _ in range(n)]}),
    (ProductStockExporter, 6, lambda n: {"rows": [_product_stock_row() for _ in range(n)]}),
    (ProposalExporter, 6, lambda n: {"rows": [_proposal_row() for _ in range(n)]}),
    (ReceivableExporter, 5, lambda n: {
        "rows": [_receivable_row() for _ in range(n)],
        "company_name": "Co", "start_date": "2025-01-01", "end_date": "2025-12-31",
    }),
    (SalesReportExporter, 4, lambda n: {
        "rows": [_sales_report_row() for _ in range(n)],
        "company_name": "Co", "start_date": "2025-01-01", "end_date": "2025-12-31",
        "report_name": "Item",
    }),
    (TaskReportExporter, 9, lambda n: {"rows": [_task_report_row() for _ in range(n)]}),
    (TransactionExporter, 7, lambda n: {"rows": [_transaction_row() for _ in range(n)]}),
    (VendorExporter, 19, lambda n: {"rows": [_vendor_row() for _ in range(n)]}),
]

_SPECIAL_EXPORTERS: List[tuple] = [
    (BalanceSheetExporter, 3, lambda _: _balance_sheet_data()),
    (ProfitLossExporter, 3, lambda _: _profit_loss_data()),
    (TrialBalanceExporter, 4, lambda _: _trial_balance_data()),
]

_ALL_EXPORTERS = _SIMPLE_EXPORTERS + _SPECIAL_EXPORTERS


# ── Parametrized tests ─────────────────────────────────────────────


@pytest.mark.parametrize(
    "cls, heading_count, _",
    _ALL_EXPORTERS,
    ids=[c.__name__ for c, _, __ in _ALL_EXPORTERS],
)
class TestExporterHeadings:
    def test_get_headings_returns_list(self, cls, heading_count, _):
        exp = cls()
        headings = exp.get_headings()
        assert isinstance(headings, list)
        assert len(headings) == heading_count
        assert all(isinstance(h, str) for h in headings)


@pytest.mark.parametrize(
    "cls, heading_count, data_fn",
    _ALL_EXPORTERS,
    ids=[c.__name__ for c, _, __ in _ALL_EXPORTERS],
)
class TestExporterProcessData:
    def test_process_data_with_sample(self, cls, heading_count, data_fn):
        exp = cls()
        exp.data = data_fn(3)
        df = exp.process_data()
        assert isinstance(df, pd.DataFrame)
        assert len(df) > 0

    def test_process_data_empty_rows(self, cls, heading_count, data_fn):
        exp = cls()
        # Set empty rows depending on the exporter's expected data shape
        if cls in (BalanceSheetExporter, TrialBalanceExporter):
            exp.data = {"rows": {}, "company_name": "Co", "start_date": "", "end_date": ""}
        elif cls == ProfitLossExporter:
            exp.data = {"rows": [], "company_name": "Co", "start_date": "", "end_date": ""}
        elif cls in (ReceivableExporter, SalesReportExporter):
            exp.data = {
                "rows": [], "company_name": "Co",
                "start_date": "", "end_date": "", "report_name": "Item",
            }
        else:
            exp.data = {"rows": []}
        df = exp.process_data()
        assert isinstance(df, pd.DataFrame)
        assert len(df) == 0


@pytest.mark.parametrize(
    "cls, heading_count, data_fn",
    _ALL_EXPORTERS,
    ids=[c.__name__ for c, _, __ in _ALL_EXPORTERS],
)
class TestExporterExportEndToEnd:
    def test_export_writes_file(self, cls, heading_count, data_fn):
        """Full export pipeline: stdin → process → xlsx file."""
        data = data_fn(2)
        with tempfile.NamedTemporaryFile(suffix=".xlsx", delete=False) as f:
            out_path = f.name
        data["output_path"] = out_path
        payload = json.dumps(data)
        exp = cls()
        try:
            with patch("sys.stdin", StringIO(payload)):
                exp.export()
            assert os.path.exists(out_path)
            assert os.path.getsize(out_path) > 0
        finally:
            if os.path.exists(out_path):
                os.unlink(out_path)


@pytest.mark.parametrize(
    "cls, heading_count, data_fn",
    _ALL_EXPORTERS,
    ids=[c.__name__ for c, _, __ in _ALL_EXPORTERS],
)
class TestExporterPerformance:
    @pytest.mark.timeout(5)
    def test_process_data_50_rows(self, cls, heading_count, data_fn):
        exp = cls()
        exp.data = data_fn(50)
        df = exp.process_data()
        assert isinstance(df, pd.DataFrame)


# ── SalesReportExporter special: Customer mode ─────────────────────


class TestSalesReportCustomerMode:
    def test_customer_headings(self):
        exp = SalesReportExporter()
        exp.data = {
            "rows": [_sales_report_row()],
            "report_name": "Customer",
            "company_name": "Co",
            "start_date": "",
            "end_date": "",
        }
        exp._report_name = "Customer"
        headings = exp.get_headings()
        assert headings[0] == "Customer Name"
        assert len(headings) == 4

    def test_customer_process_data(self):
        exp = SalesReportExporter()
        exp.data = {
            "rows": [_sales_report_row()],
            "report_name": "Customer",
            "company_name": "Co",
            "start_date": "",
            "end_date": "",
        }
        df = exp.process_data()
        assert "Customer Name" in df.columns


# ── Receivable total row ───────────────────────────────────────────


class TestReceivableTotalRow:
    def test_total_appended(self):
        exp = ReceivableExporter()
        exp.data = {
            "rows": [_receivable_row(), _receivable_row()],
            "company_name": "Co",
            "start_date": "",
            "end_date": "",
        }
        df = exp.process_data()
        last_row = df.iloc[-1]
        assert last_row["Customer Name"] == "TOTAL"


# ── PayrollExporter status mapping ─────────────────────────────────


class TestPayrollStatusMapping:
    def test_paid_status(self):
        exp = PayrollExporter()
        exp.data = {"rows": [_payroll_row()]}
        df = exp.process_data()
        assert df.iloc[0]["Status"] == "Paid"

    def test_unpaid_status(self):
        row = _payroll_row()
        row["status"] = 0
        exp = PayrollExporter()
        exp.data = {"rows": [row]}
        df = exp.process_data()
        assert df.iloc[0]["Status"] == "UnPaid"
