#!/usr/bin/env python3
"""
Leave Report Exporter Module.

Handles Excel export for employee leave reports with
approved, rejected, and pending leave counts.
"""
import sys
from typing import Any, Dict, List

import pandas as pd
from openpyxl.styles import Border, Font, PatternFill, Side

from base_exporter import BaseExporter, safe_get


class LeaveReportExporter(BaseExporter):
    """
    Exporter for Leave Report data.

    Generates xlsx files with employee ID, name, and leave
    status counts (approved, rejected, pending).
    """

    HEADINGS: List[str] = [
        "Employee ID", "Employee", "Approved Leaves",
        "Rejected Leaves", "Pending Leaves"
    ]
    COLUMN_WIDTHS: Dict[str, int] = {
        "A": 15, "B": 25, "C": 18, "D": 18, "E": 18
    }
    HEADER_FILL_COLOR: str = "1F1F2F"
    BODY_FILL_OPACITY: str = "F5F5F5"

    def __init__(self) -> None:
        """Initialize the Leave Report exporter."""
        super().__init__("LeaveReportExporter")

    def get_headings(self) -> List[str]:
        """
        Return column headings for leave report export.

        Returns:
            List of column header strings.
        """
        return self.HEADINGS

    def process_data(self) -> pd.DataFrame:
        """
        Process input JSON data into a DataFrame.

        Expects data with 'rows' key containing leave report records.

        Returns:
            DataFrame with processed leave report data.
        """
        self.logger.info("Processing leave report data")
        rows: List[Dict[str, Any]] = self.data.get("rows", [])
        if not rows:
            self.logger.warning("No rows found in input data")
            return pd.DataFrame(columns=self.HEADINGS)
        processed: List[Dict[str, Any]] = []
        for idx, row in enumerate(rows):
            try:
                processed.append({
                    "Employee ID": safe_get(row, "employee_id", ""),
                    "Employee": safe_get(row, "employee_name", ""),
                    "Approved Leaves": safe_get(row, "approved", 0),
                    "Rejected Leaves": safe_get(row, "rejected", 0),
                    "Pending Leaves": safe_get(row, "pending", 0),
                })
            except (KeyError, TypeError) as e:
                self.logger.error(
                    "Row %d processing failed: %s - row_data=%s",
                    idx, str(e), str(row)[:100]
                )
                continue
        self.logger.info("Processed %d rows successfully", len(processed))
        return pd.DataFrame(processed)

    def _apply_header_style(self) -> None:
        """Apply custom header styling for leave report."""
        if self.sheet is None:
            return
        header_fill = PatternFill(
            start_color=self.HEADER_FILL_COLOR,
            end_color=self.HEADER_FILL_COLOR,
            fill_type="solid"
        )
        header_border = Border(
            left=Side(style="thin", color="000000"),
            right=Side(style="thin", color="000000"),
            top=Side(style="thin", color="000000"),
            bottom=Side(style="thin", color="000000"),
        )
        for col in range(1, len(self.HEADINGS) + 1):
            cell = self.sheet.cell(row=1, column=col)
            cell.font = Font(bold=True, color="FFFFFF")
            cell.fill = header_fill
            cell.border = header_border

    def _apply_body_style(self, row_count: int) -> None:
        """Apply alternating row colors to body."""
        if self.sheet is None or row_count < 1:
            return
        body_fill = PatternFill(
            start_color=self.BODY_FILL_OPACITY,
            end_color=self.BODY_FILL_OPACITY,
            fill_type="solid"
        )
        body_border = Border(
            left=Side(style="thin", color="808080"),
            right=Side(style="thin", color="808080"),
            top=Side(style="hair", color="E0E0E0"),
            bottom=Side(style="hair", color="E0E0E0"),
        )
        for row in range(2, row_count + 2):
            for col in range(1, len(self.HEADINGS) + 1):
                cell = self.sheet.cell(row=row, column=col)
                cell.border = body_border
                if row % 2 == 0:
                    cell.fill = body_fill

    def export(self) -> None:
        """
        Execute the full export workflow.

        Reads JSON from stdin, processes data, creates styled workbook,
        and outputs binary xlsx to stdout.
        """
        self.logger.info("Starting leave report export")
        try:
            self.read_stdin()
            df = self.process_data()
            self.create_workbook()
            if self.sheet is None:
                self.logger.error("Failed to create worksheet")
                return
            self.sheet.title = "Leave Report"
            for col, val in enumerate(self.get_headings(), start=1):
                self.sheet.cell(row=1, column=col, value=val)
            self._apply_header_style()
            rows_written = self.dataframe_to_sheet(df, start_row=2, include_header=False)
            self._apply_body_style(rows_written)
            self.set_column_widths(self.COLUMN_WIDTHS)
            self.freeze_pane("A2")
            output_path = self.data.get("output_path")
            if output_path:
                self.output_to_file(output_path)
                print(output_path, file=sys.stderr)
            else:
                self.output_to_stdout()
            self.logger.info("Leave report export completed successfully")
        except (ValueError, KeyError) as e:
            self.logger.error("Export failed due to data error: %s", str(e))
            sys.exit(1)
        except IOError as e:
            self.logger.error("Export failed due to IO error: %s", str(e))
            sys.exit(2)


def main() -> None:
    """Entry point for leave report export."""
    exporter = LeaveReportExporter()
    exporter.export()


if __name__ == "__main__":
    main()
