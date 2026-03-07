from __future__ import annotations

import io
import json
from typing import Any

import pandas as pd
from openpyxl import Workbook

from base_importer import BaseImporter, normalise_header, parse_date, safe_get


class DummyImporter(BaseImporter):
    def __init__(self) -> None:
        super().__init__("DummyImporter")

    def get_expected_headers(self) -> list[str]:
        return ["employee_id", "date", "status"]

    def validate_row(self, row: dict[str, Any], row_idx: int) -> dict[str, Any] | None:
        return row

    def process_data(self) -> list[Any]:
        return list(self._data.get("rows", []))

    def run(self) -> None:
        self.read_stdin()
        self.write_result(self.process_data())


def test_clean_cell_parse_date_and_header_normalisation() -> None:
    assert DummyImporter.clean_cell(None) is None
    assert DummyImporter.clean_cell(float("nan")) is None
    assert DummyImporter.clean_cell("  ") is None
    assert DummyImporter.clean_cell("value") == "value"
    assert normalise_header("Employee ID") == "employee_id"
    assert normalise_header("Clock-In Time") == "clockin_time"
    assert parse_date("2026-01-10") == "2026-01-10"
    assert parse_date("10/01/2026") == "2026-01-10"
    assert parse_date("not-a-date", strict=True) is None
    assert parse_date("not-a-date", strict=False) == "not-a-date"
    assert safe_get({"name": None}, "name", "fallback") == "fallback"


def test_detect_header_row_finds_expected_columns() -> None:
    workbook = Workbook()
    sheet = workbook.active
    assert sheet is not None
    sheet.append(["Ignore", "This", "Row"])
    sheet.append(["Employee ID", "Date", "Status"])

    importer = DummyImporter()
    row_idx, mapping = importer.detect_header_row(sheet, ["employee id", "date", "status"])

    assert row_idx == 1
    assert mapping == {"employee id": 0, "date": 1, "status": 2}


def test_read_rows_from_data_and_add_error_updates_counts() -> None:
    importer = DummyImporter()
    importer._data = {"rows": [{"employee_id": "EMP-1"}, {"employee_id": "EMP-2"}]}
    frame = importer.read_rows_from_data()

    importer.add_error(3, "bad row")

    assert list(frame.columns) == ["employee_id"]
    assert importer._skipped_count == 1
    assert importer._errors == ["Row 3: bad row"]


def test_read_spreadsheet_supports_csv_and_xlsx(tmp_path: Any) -> None:
    importer = DummyImporter()
    frame = pd.DataFrame(
        [
            {"employee_id": "EMP-1", "date": "2026-01-10", "status": "Present"},
            {"employee_id": "EMP-2", "date": "2026-01-11", "status": "Absent"},
        ]
    )

    csv_path = tmp_path / "attendance.csv"
    xlsx_path = tmp_path / "attendance.xlsx"
    frame.to_csv(csv_path, index=False)
    frame.to_excel(xlsx_path, index=False)

    csv_frame = importer.read_spreadsheet(str(csv_path))
    xlsx_frame = importer.read_spreadsheet(str(xlsx_path))

    assert csv_frame.to_dict("records")[0]["employee_id"] == "EMP-1"
    assert xlsx_frame.to_dict("records")[1]["status"] == "Absent"


def test_run_reads_and_writes_json(monkeypatch: Any) -> None:
    importer = DummyImporter()
    monkeypatch.setattr("sys.stdin", io.StringIO(json.dumps({"rows": [{"employee_id": "EMP-1"}]})))
    fake_stdout = io.StringIO()
    monkeypatch.setattr("sys.stdout", fake_stdout)

    importer.run()
    result = json.loads(fake_stdout.getvalue())

    assert result["status"] == "success"
    assert result["imported"] == 0
    assert result["rows"] == [{"employee_id": "EMP-1"}]
