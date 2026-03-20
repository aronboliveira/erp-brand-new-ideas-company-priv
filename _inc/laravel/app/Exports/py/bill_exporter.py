#!/usr/bin/env python3
"""
Bill Exporter Module.

Handles Excel export for vendor bill reports with
header styling, alternating row colors, and border formatting.
"""
import sys
from typing import Any, Dict, List

import pandas as pd
from openpyxl.styles import Border, Font, PatternFill, Side

from base_exporter import BaseExporter, format_date, safe_get


class BillExporter(BaseExporter):
    """
    Exporter for Bill reports.

    Generates xlsx files with bill number, dates, order number,
    status, and category columns with styled headers and body rows.
    """

    HEADINGS: List[str] = [
        "Bill No", "Bill Date", "Due Date",
        "Order No", "Status", "Send Date", "Category"
    ]
    COLUMN_WIDTHS: Dict[str, int] = {
        "A": 15, "B": 15, "C": 15, "D": 15, "E": 12, "F": 15, "G": 20
    }
    HEADER_FILL_COLOR: str = "1F1F2F"
    BODY_FILL_OPACITY: str = "F5F5F5"
    BORDER_COLOR: str = "808080"
    HEADER_BORDER_COLOR: str = "000000"

    def __init__(self) -> None:
        """Initialize the Bill exporter."""
        super().__init__("BillExporter")

    def get_headings(self) -> List[str]:
        """
        Return column headings for bill export.

        Returns:
            List of column header strings.
        """
        return self.HEADINGS

    def process_data(self) -> pd.DataFrame:
        """
        Process input JSON data into a DataFrame.

        Expects data with 'rows' key containing bill records.

        Returns:
            DataFrame with processed bill data.
        """
        self.logger.info("Processing bill data")
        rows: List[Dict[str, Any]] = self.data.get("rows", [])
        if not rows:
            self.logger.warning("No rows found in input data")
            return pd.DataFrame(columns=self.HEADINGS)
        processed: List[Dict[str, Any]] = []
        for idx, row in enumerate(rows):
            try:
                processed.append({
                    "Bill No": safe_get(row, "bill_no", ""),
                    "Bill Date": format_date(safe_get(row, "bill_date", "")),
                    "Due Date": format_date(safe_get(row, "due_date", "")),
                    "Order No": safe_get(row, "order_no", ""),
                    "Status": safe_get(row, "status", ""),
                    "Send Date": format_date(safe_get(row, "send_date", "")),
                    "Category": safe_get(row, "category", ""),
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
        """Apply custom header styling for bill export."""
        if self.sheet is None:
            return
        header_fill = PatternFill(
            start_color=self.HEADER_FILL_COLOR,
            end_color=self.HEADER_FILL_COLOR,
            fill_type="solid"
        )
        header_border = Border(
            left=Side(style="thin", color=self.HEADER_BORDER_COLOR),
            right=Side(style="thin", color=self.HEADER_BORDER_COLOR),
            top=Side(style="thin", color=self.HEADER_BORDER_COLOR),
            bottom=Side(style="thin", color=self.HEADER_BORDER_COLOR),
        )
        for col in range(1, len(self.HEADINGS) + 1):
            cell = self.sheet.cell(row=1, column=col)
            cell.font = Font(bold=True, color="FFFFFF")
            cell.fill = header_fill
            cell.border = header_border

    def _apply_body_style(self, row_count: int) -> None:
        """Apply alternating row colors and borders to body."""
        if self.sheet is None or row_count < 1:
            return
        body_fill = PatternFill(
            start_color=self.BODY_FILL_OPACITY,
            end_color=self.BODY_FILL_OPACITY,
            fill_type="solid"
        )
        body_border = Border(
            left=Side(style="thin", color=self.BORDER_COLOR),
            right=Side(style="thin", color=self.BORDER_COLOR),
            top=Side(style="hair", color=self.BORDER_COLOR),
            bottom=Side(style="hair", color=self.BORDER_COLOR),
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
        self.logger.info("Starting bill export")
        try:
            self.read_stdin()
            df = self.process_data()
            self.create_workbook()
            if self.sheet is None:
                self.logger.error("Failed to create worksheet")
                return
            self.sheet.title = "Bills"
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
            self.logger.info("Bill export completed successfully")
        except (ValueError, KeyError) as e:
            self.logger.error("Export failed due to data error: %s", str(e))
            sys.exit(1)
        except IOError as e:
            self.logger.error("Export failed due to IO error: %s", str(e))
            sys.exit(2)


def main() -> None:
    """Entry point for bill export."""
    exporter = BillExporter()
    exporter.export()


if __name__ == "__main__":
    main()
