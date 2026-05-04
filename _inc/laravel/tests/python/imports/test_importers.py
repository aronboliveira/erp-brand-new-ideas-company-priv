"""Parametrized tests for all 5 concrete Python importer classes.

Each importer is tested for:
  1. get_expected_headers() returns correct list
  2. validate_row() with valid data → cleaned dict
  3. validate_row() with missing required fields → None
  4. process_data() end-to-end with valid rows
  5. process_data() with empty rows → empty list
  6. run() end-to-end: stdin JSON → stdout JSON
  7. Performance: process_data with 100 rows within 5 s
"""

import json
from io import StringIO
from typing import Any, Dict
from unittest.mock import patch

import pytest

from attendance_importer import AttendanceImporter
from customer_importer import CustomerImporter
from employees_importer import EmployeesImporter
from product_service_importer import ProductServiceImporter
from vendor_importer import VendorImporter


# ── Sample data ────────────────────────────────────────────────────


def _attendance_row() -> Dict[str, Any]:
    return {
        "employee_id": "E-001", "date": "2025-06-15", "status": "Present",
        "clock_in": "08:00", "clock_out": "17:00", "late": "0",
        "early_leaving": "0", "overtime": "0", "total_rest": "0",
    }


def _customer_row() -> Dict[str, Any]:
    return {
        "name": "Acme Inc", "email": "acme@example.com", "contact": "555-1234",
        "billing_name": "Acme", "billing_country": "US", "billing_state": "CA",
        "billing_city": "LA", "billing_phone": "555", "billing_zip": "90001",
        "billing_address": "123 Main St", "shipping_name": "Acme",
        "shipping_country": "US", "shipping_state": "CA", "shipping_city": "LA",
        "shipping_phone": "555", "shipping_zip": "90001", "shipping_address": "123 St",
    }


def _employee_row() -> Dict[str, Any]:
    return {
        "name": "Alice", "email": "alice@example.com", "employee_id": "EMP-001",
        "phone": "555-0001", "dob": "1990-01-15", "gender": "Female",
        "address": "456 Oak Ave",
    }


def _product_service_row() -> Dict[str, Any]:
    return {
        "name": "Widget", "sku": "W-100", "sale_price": "29.99",
        "purchase_price": "15.00", "quantity": "100", "type": "Product",
        "description": "A standard widget",
    }


def _vendor_row() -> Dict[str, Any]:
    return {
        "name": "Vendor Co", "email": "vendor@example.com", "contact": "555-9999",
        "billing_name": "Vendor", "billing_country": "US", "billing_state": "NY",
        "billing_city": "NYC", "billing_phone": "555", "billing_zip": "10001",
        "billing_address": "789 Broadway", "shipping_name": "Vendor",
        "shipping_country": "US", "shipping_state": "NY", "shipping_city": "NYC",
        "shipping_phone": "555", "shipping_zip": "10001", "shipping_address": "789 B",
    }


# ── Registry ───────────────────────────────────────────────────────

# (ImporterClass, expected_header_count, sample_row_fn, required_fields)
_IMPORTERS = [
    (AttendanceImporter, 9, _attendance_row, ["employee_id", "date"]),
    (CustomerImporter, 17, _customer_row, ["name"]),
    (EmployeesImporter, 19, _employee_row, ["name", "email", "employee_id"]),
    (ProductServiceImporter, 10, _product_service_row, ["name"]),
    (VendorImporter, 23, _vendor_row, ["name", "email"]),
]


# ── 1. Headers ─────────────────────────────────────────────────────


@pytest.mark.parametrize(
    "cls, hdr_count, _, __",
    _IMPORTERS,
    ids=[c.__name__ for c, *_ in _IMPORTERS],
)
class TestImporterHeaders:
    def test_headers_count(self, cls, hdr_count, _, __):
        imp = cls()
        headers = imp.get_expected_headers()
        assert isinstance(headers, list)
        assert len(headers) == hdr_count

    def test_headers_are_strings(self, cls, hdr_count, _, __):
        imp = cls()
        assert all(isinstance(h, str) for h in imp.get_expected_headers())


# ── 2. validate_row — valid ────────────────────────────────────────


@pytest.mark.parametrize(
    "cls, _, row_fn, __",
    _IMPORTERS,
    ids=[c.__name__ for c, *_ in _IMPORTERS],
)
class TestValidateRowValid:
    def test_valid_row_returns_dict(self, cls, _, row_fn, __):
        imp = cls()
        result = imp.validate_row(row_fn(), 0)
        assert isinstance(result, dict)
        assert len(result) > 0


# ── 3. validate_row — missing required ─────────────────────────────


@pytest.mark.parametrize(
    "cls, _, row_fn, required",
    _IMPORTERS,
    ids=[c.__name__ for c, *_ in _IMPORTERS],
)
class TestValidateRowMissing:
    def test_missing_required_returns_none(self, cls, _, row_fn, required):
        imp = cls()
        row = row_fn()
        # Remove the first required field
        if required:
            row[required[0]] = None
        result = imp.validate_row(row, 0)
        assert result is None
        assert imp._skipped_count >= 1


# ── 4. process_data — valid ────────────────────────────────────────


@pytest.mark.parametrize(
    "cls, _, row_fn, __",
    _IMPORTERS,
    ids=[c.__name__ for c, *_ in _IMPORTERS],
)
class TestProcessDataValid:
    def test_returns_list(self, cls, _, row_fn, __):
        imp = cls()
        imp._data = {"rows": [row_fn() for _ in range(5)]}
        result = imp.process_data()
        assert isinstance(result, list)
        assert len(result) == 5
        assert imp._imported_count == 5


# ── 5. process_data — empty ────────────────────────────────────────


@pytest.mark.parametrize(
    "cls, _, __, ___",
    _IMPORTERS,
    ids=[c.__name__ for c, *_ in _IMPORTERS],
)
class TestProcessDataEmpty:
    def test_empty_rows(self, cls, _, __, ___):
        imp = cls()
        imp._data = {"rows": []}
        result = imp.process_data()
        assert result == []
        assert imp._imported_count == 0


# ── 6. run() end-to-end ───────────────────────────────────────────


@pytest.mark.parametrize(
    "cls, _, row_fn, __",
    _IMPORTERS,
    ids=[c.__name__ for c, *_ in _IMPORTERS],
)
class TestRunEndToEnd:
    def test_stdin_to_stdout(self, cls, _, row_fn, __):
        payload = json.dumps({"rows": [row_fn(), row_fn()]})
        imp = cls()
        buf = StringIO()
        with patch("sys.stdin", StringIO(payload)), patch("sys.stdout", buf):
            imp.run()
        result = json.loads(buf.getvalue())
        assert result["status"] == "success"
        assert result["imported"] == 2
        assert len(result["rows"]) == 2


# ── 7. Performance ─────────────────────────────────────────────────


@pytest.mark.parametrize(
    "cls, _, row_fn, __",
    _IMPORTERS,
    ids=[c.__name__ for c, *_ in _IMPORTERS],
)
class TestImporterPerformance:
    @pytest.mark.timeout(5)
    def test_process_100_rows(self, cls, _, row_fn, __):
        imp = cls()
        imp._data = {"rows": [row_fn() for _ in range(100)]}
        result = imp.process_data()
        assert len(result) == 100


# ── Special: non-dict row handling ─────────────────────────────────


class TestNonDictRows:
    @pytest.mark.parametrize(
        "cls",
        [AttendanceImporter, EmployeesImporter, ProductServiceImporter],
        ids=["Attendance", "Employees", "ProductService"],
    )
    def test_non_dict_row_skipped(self, cls):
        imp = cls()
        imp._data = {"rows": ["not a dict", 12345, None]}
        result = imp.process_data()
        assert result == []
        assert imp._skipped_count == 3
