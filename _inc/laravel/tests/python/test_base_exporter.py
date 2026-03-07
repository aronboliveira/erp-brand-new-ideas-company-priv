from __future__ import annotations

import io
from datetime import datetime
from typing import Any

import pandas as pd
from openpyxl import load_workbook

from base_exporter import BaseExporter, format_currency, format_date, parse_number, safe_get


class DummyExporter(BaseExporter):
    def __init__(self) -> None:
        super().__init__("DummyExporter")

    def get_headings(self) -> list[str]:
        return ["Name", "Amount"]

    def process_data(self) -> pd.DataFrame:
        return pd.DataFrame(columns=self.get_headings())

    def export(self) -> None:
        raise NotImplementedError


class DummyStdout:
    def __init__(self) -> None:
        self.buffer = io.BytesIO()

    def flush(self) -> None:
        return None


def test_safe_get_supports_dicts_and_objects() -> None:
    class Obj:
        amount = 5

    assert safe_get({"amount": 10}, "amount") == 10
    assert safe_get({"amount": None}, "amount", 99) is None
    assert safe_get(Obj(), "amount", 0) == 5
    assert safe_get(None, "missing", "fallback") == "fallback"


def test_format_helpers_cover_dates_currency_and_numbers() -> None:
    assert format_date("2026-01-10T08:30:00Z") == "2026-01-10"
    assert format_date(datetime(2026, 1, 10, 8, 30)) == "2026-01-10"
    assert format_date("not-a-date") == "not-a-date"
    assert format_currency(1234.5, "$") == "$1,234.50"
    assert format_currency(None, "$") == "$0.00"
    assert parse_number("$1,234.50") == 1234.5
    assert parse_number("(99.95)") == -99.95
    assert parse_number(True) == 1.0
    assert parse_number("invalid", default=None) is None


def test_dataframe_to_sheet_currency_format_and_file_output(tmp_path: Any) -> None:
    exporter = DummyExporter()
    exporter.create_workbook()
    assert exporter.sheet is not None

    frame = pd.DataFrame([{"Name": "Alpha", "Amount": 10.5}, {"Name": "Beta", "Amount": 20}])
    rows_written = exporter.dataframe_to_sheet(frame, start_row=1, include_header=True)
    exporter.apply_currency_format(col=2, row_start=2, row_end=3, symbol="$")
    exporter.set_column_widths({"A": 18, "B": 14})

    output_path = tmp_path / "dummy.xlsx"
    saved_path = exporter.output_to_file(str(output_path))

    assert rows_written == 3
    assert saved_path.endswith("dummy.xlsx")
    workbook = load_workbook(output_path)
    sheet = workbook.active
    assert sheet is not None
    assert sheet["A1"].value == "Name"
    assert sheet["B2"].value == 10.5
    assert sheet["B2"].number_format == "$#,##0.00"
    assert sheet.column_dimensions["A"].width == 18


def test_output_to_stdout_writes_binary_workbook(monkeypatch: Any) -> None:
    exporter = DummyExporter()
    exporter.create_workbook()
    assert exporter.sheet is not None
    exporter.sheet["A1"] = "Hello"

    fake_stdout = DummyStdout()
    monkeypatch.setattr("sys.stdout", fake_stdout)
    exporter.output_to_stdout()

    assert fake_stdout.buffer.getvalue().startswith(b"PK")


def test_chart_and_formula_helpers_add_expected_artifacts() -> None:
    exporter = DummyExporter()
    exporter.create_workbook()
    assert exporter.sheet is not None

    data = pd.DataFrame(
        [
            {"Name": "A", "Amount": 10},
            {"Name": "B", "Amount": 20},
            {"Name": "C", "Amount": 30},
        ]
    )
    exporter.dataframe_to_sheet(data, start_row=1, include_header=True)

    exporter.add_bar_chart((2, 1, 2, 4), (1, 2, 1, 4), title="Bars", anchor="D2")
    exporter.add_line_chart((2, 1, 2, 4), (1, 2, 1, 4), title="Trend", anchor="D18")
    exporter.add_pie_chart((2, 2, 2, 4), (1, 2, 1, 4), title="Split", anchor="D34")
    exporter.add_sum_formula(row=6, col=2, range_col="B", start_row=2, end_row=4)
    exporter.add_percentage_formula(row=7, col=2, value_cell="B6", total_cell="B4")

    assert len(exporter.sheet._charts) == 3  # type: ignore[attr-defined]
    assert exporter.sheet["B6"].value == "=SUM(B2:B4)"
    assert exporter.sheet["B7"].value == "=IF(B4=0,0,B6/B4)"
