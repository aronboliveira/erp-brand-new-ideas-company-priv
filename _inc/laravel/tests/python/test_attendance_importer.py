from __future__ import annotations

import io
import json

from attendance_importer import AttendanceImporter


def test_validate_row_applies_defaults_and_strict_date_parsing() -> None:
    importer = AttendanceImporter()

    valid = importer.validate_row(
        {
            "employee_id": "EMP-200",
            "date": "10/01/2026",
            "status": "",
            "clock_in": "09:10",
        },
        0,
    )
    invalid = importer.validate_row({"employee_id": "EMP-201", "date": "bad-date"}, 1)

    assert valid == {
        "employee_id": "EMP-200",
        "date": "2026-01-10",
        "status": "Present",
        "clock_in": "09:10",
        "clock_out": None,
        "late": "0",
        "early_leaving": "0",
        "overtime": "0",
        "total_rest": "0",
    }
    assert invalid is None
    assert importer._errors == ["Row 1: missing or invalid date"]


def test_process_data_counts_imported_and_skipped_rows(attendance_import_payload) -> None:
    importer = AttendanceImporter()
    importer._data = attendance_import_payload

    rows = importer.process_data()

    assert importer._imported_count == 2
    assert importer._skipped_count == 3
    assert len(rows) == 2
    assert rows[1]["status"] == "Present"
    assert "row is not a dict" in importer._errors[1]


def test_run_writes_partial_result_for_mixed_payload(monkeypatch, attendance_import_payload) -> None:
    importer = AttendanceImporter()
    monkeypatch.setattr("sys.stdin", io.StringIO(json.dumps(attendance_import_payload)))
    fake_stdout = io.StringIO()
    monkeypatch.setattr("sys.stdout", fake_stdout)

    importer.run()
    result = json.loads(fake_stdout.getvalue())

    assert result["status"] == "partial"
    assert result["imported"] == 2
    assert result["skipped"] == 3
    assert len(result["errors"]) == 3
    assert len(result["rows"]) == 2

