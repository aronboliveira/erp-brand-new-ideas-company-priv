"""Tests for base_importer.py — BaseImporter, safe_get, normalise_header, parse_date."""

import json
from datetime import datetime
from io import StringIO
from typing import Any, Dict, List, Optional
from unittest.mock import patch

import pandas as pd
import pytest

from base_importer import BaseImporter, normalise_header, parse_date, safe_get


# ── Override: clean_cell is a staticmethod on BaseImporter ─────────
# We test it via the class directly.


# ── Utility functions ──────────────────────────────────────────────


class TestSafeGet:
    def test_key_exists(self):
        assert safe_get({"x": 10}, "x") == 10

    def test_key_missing(self):
        assert safe_get({"x": 10}, "y", "N/A") == "N/A"

    def test_none_value_returns_default(self):
        assert safe_get({"x": None}, "x", 0) == 0

    def test_empty_dict(self):
        assert safe_get({}, "k", "fallback") == "fallback"


class TestNormaliseHeader:
    def test_basic(self):
        assert normalise_header("Employee Name") == "employee_name"

    def test_special_chars(self):
        assert normalise_header("Account #No!") == "account_no"

    def test_leading_trailing_spaces(self):
        assert normalise_header("  Tax ID  ") == "tax_id"

    def test_single_word(self):
        assert normalise_header("email") == "email"


class TestParseDate:
    def test_none(self):
        assert parse_date(None) is None

    def test_valid_string(self):
        assert parse_date("2025-06-15") == "2025-06-15"

    def test_datetime_object(self):
        dt = datetime(2025, 6, 15)
        assert parse_date(dt) == "2025-06-15"

    def test_invalid_returns_stripped(self):
        assert parse_date("not-a-date") == "not-a-date"

    def test_empty_string(self):
        assert parse_date("") is None


# ── BaseImporter (via stub) ───────────────────────────────────────


class StubImporter(BaseImporter):
    """Concrete implementation for testing abstract base methods."""

    def get_expected_headers(self) -> List[str]:
        return ["name", "email"]

    def validate_row(self, row: Dict[str, Any], row_idx: int) -> Optional[Dict[str, Any]]:
        name = self.clean_cell(row.get("name"))
        if name is None:
            self.add_error(row_idx, "missing name")
            return None
        return {"name": name, "email": self.clean_cell(row.get("email"))}

    def process_data(self) -> List[Dict[str, Any]]:
        result = []
        for idx, row in enumerate(self._data.get("rows", [])):
            cleaned = self.validate_row(row, idx)
            if cleaned:
                self._imported_count += 1
                result.append(cleaned)
        return result

    def run(self) -> None:
        self.read_stdin()
        rows = self.process_data()
        self.write_result(rows)


class TestBaseImporterInit:
    def test_initial_state(self):
        imp = StubImporter("test")
        assert imp._name == "test"
        assert imp._data == {}
        assert imp._errors == []
        assert imp._imported_count == 0
        assert imp._skipped_count == 0


class TestBaseImporterReadStdin:
    def test_valid_json(self):
        payload = json.dumps({"rows": [{"name": "Alice"}]})
        imp = StubImporter("t")
        with patch("sys.stdin", StringIO(payload)):
            data = imp.read_stdin()
        assert data["rows"][0]["name"] == "Alice"

    def test_empty_stdin(self):
        imp = StubImporter("t")
        with patch("sys.stdin", StringIO("")):
            data = imp.read_stdin()
        assert data == {}


class TestCleanCell:
    def test_none(self):
        assert BaseImporter.clean_cell(None) is None

    def test_nan_float(self):
        assert BaseImporter.clean_cell(float("nan")) is None

    def test_nan_string(self):
        assert BaseImporter.clean_cell("NaN") is None

    def test_empty_string(self):
        assert BaseImporter.clean_cell("") is None

    def test_whitespace(self):
        assert BaseImporter.clean_cell("   ") is None

    def test_valid_value(self):
        assert BaseImporter.clean_cell("hello") == "hello"

    def test_numeric(self):
        assert BaseImporter.clean_cell(42) == 42


class TestAddError:
    def test_increments_skipped(self):
        imp = StubImporter("t")
        imp.add_error(0, "bad row")
        assert imp._skipped_count == 1
        assert len(imp._errors) == 1
        assert "Row 0: bad row" in imp._errors[0]


class TestWriteResult:
    def test_json_output(self):
        imp = StubImporter("t")
        imp._imported_count = 2
        buf = StringIO()
        with patch("sys.stdout", buf):
            imp.write_result([{"name": "A"}, {"name": "B"}])
        result = json.loads(buf.getvalue())
        assert result["status"] == "success"
        assert result["imported"] == 2
        assert len(result["rows"]) == 2

    def test_partial_status_on_errors(self):
        imp = StubImporter("t")
        imp._errors = ["Row 0: bad"]
        buf = StringIO()
        with patch("sys.stdout", buf):
            imp.write_result([])
        result = json.loads(buf.getvalue())
        assert result["status"] == "partial"


class TestReadRowsFromData:
    def test_builds_dataframe(self):
        imp = StubImporter("t")
        imp._data = {"rows": [{"a": 1}, {"a": 2}]}
        df = imp.read_rows_from_data()
        assert isinstance(df, pd.DataFrame)
        assert len(df) == 2

    def test_empty_rows(self):
        imp = StubImporter("t")
        imp._data = {"rows": []}
        df = imp.read_rows_from_data()
        assert len(df) == 0


class TestStubImporterEndToEnd:
    def test_full_pipeline(self):
        payload = json.dumps({
            "rows": [
                {"name": "Alice", "email": "a@b.com"},
                {"name": None, "email": "x@y.com"},  # missing name → skipped
                {"name": "Bob", "email": ""},
            ]
        })
        imp = StubImporter("t")
        buf = StringIO()
        with patch("sys.stdin", StringIO(payload)), patch("sys.stdout", buf):
            imp.run()
        result = json.loads(buf.getvalue())
        assert result["imported"] == 2
        assert result["skipped"] == 1
        assert len(result["errors"]) == 1


class TestBaseImporterPerformance:
    @pytest.mark.timeout(5)
    def test_process_100_rows(self):
        imp = StubImporter("perf")
        imp._data = {
            "rows": [{"name": f"User{i}", "email": f"u{i}@test.com"} for i in range(100)]
        }
        result = imp.process_data()
        assert len(result) == 100
        assert imp._imported_count == 100
