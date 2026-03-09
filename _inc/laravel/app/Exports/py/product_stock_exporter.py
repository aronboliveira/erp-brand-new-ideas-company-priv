#!/usr/bin/env python3
"""
Product Stock Exporter Module.

Handles Excel export for product stock/inventory data.
"""
import sys
from typing import Any, Dict, List

import pandas as pd
from openpyxl.styles import Border, Font, PatternFill, Side

from base_exporter import BaseExporter, format_date, safe_get


class ProductStockExporter(BaseExporter):
    """
    Exporter for Product Stock data.

    Generates xlsx files with stock ID, product name, quantity,
    type, description, and date.
    """

    HEADINGS: List[str] = [
        "Stock Id", "Product Name", "Quantity",
        "Type", "Description", "Date"
    ]
    COLUMN_WIDTHS: Dict[str, int] = {
        "A": 12, "B": 25, "C": 12, "D": 15, "E": 40, "F": 15
    }
    HEADER_FILL_COLOR: str = "1F1F2F"
    BODY_FILL_OPACITY: str = "F5F5F5"

    def __init__(self) -> None:
        """Initialize the Product Stock exporter."""
        super().__init__("ProductStockExporter")

    def get_headings(self) -> List[str]:
        """
        Return column headings for product stock export.

        Returns:
            List of column header strings.
        """
        return self.HEADINGS

    def process_data(self) -> pd.DataFrame:
        """
        Process input JSON data into a DataFrame.

        Expects data with 'rows' key containing stock records.

        Returns:
            DataFrame with processed stock data.
        """
        self.logger.info("Processing product stock data")
        rows: List[Dict[str, Any]] = self.data.get("rows", [])
        if not rows:
            self.logger.warning("No rows found in input data")
            return pd.DataFrame(columns=self.HEADINGS)
        processed: List[Dict[str, Any]] = []
        for idx, row in enumerate(rows):
            try:
                processed.append({
                    "Stock Id": safe_get(row, "id", ""),
                    "Product Name": safe_get(row, "product_name", ""),
                    "Quantity": safe_get(row, "quantity", 0),
                    "Type": safe_get(row, "type", ""),
                    "Description": safe_get(row, "description", ""),
                    "Date": format_date(safe_get(row, "date", "")),
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
        """Apply custom header styling for product stock."""
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
        """Apply alternating row colors and borders to body."""
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
        self.logger.info("Starting product stock export")
        try:
            self.read_stdin()
            df = self.process_data()
            self.create_workbook()
            if self.sheet is None:
                self.logger.error("Failed to create worksheet")
                return
            self.sheet.title = "Product Stock"
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
            self.logger.info("Product stock export completed successfully")
        except (ValueError, KeyError) as e:
            self.logger.error("Export failed due to data error: %s", str(e))
            sys.exit(1)
        except IOError as e:
            self.logger.error("Export failed due to IO error: %s", str(e))
            sys.exit(2)


def main() -> None:
    """Entry point for product stock export."""
    exporter = ProductStockExporter()
    exporter.export()


if __name__ == "__main__":
    main()
