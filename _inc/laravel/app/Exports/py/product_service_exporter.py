#!/usr/bin/env python3
"""
Product Service Exporter Module.

Handles Excel export for product/service catalog data.
"""
import sys
from typing import Any, Dict, List

import pandas as pd

from base_exporter import BaseExporter, parse_number, safe_get


class ProductServiceExporter(BaseExporter):
    """
    Exporter for Product/Service catalog data.

    Generates xlsx files with product ID, name, SKU, prices,
    tax, category, unit, type, and description.
    """

    HEADINGS: List[str] = [
        "ID", "Name", "SKU", "Sale Price", "Purchase Price",
        "Tax", "Category", "Unit", "Type", "Description"
    ]
    COLUMN_WIDTHS: Dict[str, int] = {
        "A": 10, "B": 25, "C": 15, "D": 15, "E": 15,
        "F": 15, "G": 20, "H": 12, "I": 12, "J": 40
    }

    def __init__(self) -> None:
        """Initialize the Product Service exporter."""
        super().__init__("ProductServiceExporter")

    def get_headings(self) -> List[str]:
        """
        Return column headings for product service export.

        Returns:
            List of column header strings.
        """
        return self.HEADINGS

    def process_data(self) -> pd.DataFrame:
        """
        Process input JSON data into a DataFrame.

        Expects data with 'rows' key containing product/service records.

        Returns:
            DataFrame with processed product/service data.
        """
        self.logger.info("Processing product service data")
        rows: List[Dict[str, Any]] = self.data.get("rows", [])
        if not rows:
            self.logger.warning("No rows found in input data")
            return pd.DataFrame(columns=self.HEADINGS)
        processed: List[Dict[str, Any]] = []
        for idx, row in enumerate(rows):
            try:
                processed.append({
                    "ID": safe_get(row, "id", ""),
                    "Name": safe_get(row, "name", ""),
                    "SKU": safe_get(row, "sku", ""),
                    "Sale Price": parse_number(
                        safe_get(row, "sale_price", 0),
                        default=0.0
                    ),
                    "Purchase Price": parse_number(
                        safe_get(row, "purchase_price", 0),
                        default=0.0
                    ),
                    "Tax": safe_get(row, "tax", ""),
                    "Category": safe_get(row, "category", ""),
                    "Unit": safe_get(row, "unit", ""),
                    "Type": safe_get(row, "type", ""),
                    "Description": safe_get(row, "description", ""),
                })
            except (KeyError, TypeError) as e:
                self.logger.error(
                    "Row %d processing failed: %s - row_data=%s",
                    idx, str(e), str(row)[:100]
                )
                continue
        self.logger.info("Processed %d rows successfully", len(processed))
        return pd.DataFrame(processed)

    def export(self) -> None:
        """
        Execute the full export workflow.

        Reads JSON from stdin, processes data, creates styled workbook,
        and outputs binary xlsx to stdout.
        """
        self.logger.info("Starting product service export")
        try:
            self.read_stdin()
            df = self.process_data()
            self.create_workbook()
            if self.sheet is None:
                self.logger.error("Failed to create worksheet")
                return
            self.sheet.title = "Products & Services"
            for col, val in enumerate(self.get_headings(), start=1):
                self.sheet.cell(row=1, column=col, value=val)
            self.apply_header_style(row_num=1, col_start=1, col_end=len(self.HEADINGS))
            rows_written = self.dataframe_to_sheet(df, start_row=2, include_header=False)
            if rows_written > 0:
                self.apply_body_style(
                    row_start=2,
                    row_end=rows_written + 1,
                    col_start=1,
                    col_end=len(self.HEADINGS)
                )
                symbol = self.data.get("currency_symbol", "")
                self.apply_currency_format(
                    col=4,
                    row_start=2,
                    row_end=rows_written + 1,
                    symbol=symbol,
                )
                self.apply_currency_format(
                    col=5,
                    row_start=2,
                    row_end=rows_written + 1,
                    symbol=symbol,
                )
            self.set_column_widths(self.COLUMN_WIDTHS)
            self.freeze_pane("A2")
            output_path = self.data.get("output_path")
            if output_path:
                self.output_to_file(output_path)
                print(output_path, file=sys.stderr)
            else:
                self.output_to_stdout()
            self.logger.info("Product service export completed successfully")
        except (ValueError, KeyError) as e:
            self.logger.error("Export failed due to data error: %s", str(e))
            sys.exit(1)
        except IOError as e:
            self.logger.error("Export failed due to IO error: %s", str(e))
            sys.exit(2)


def main() -> None:
    """Entry point for product service export."""
    exporter = ProductServiceExporter()
    exporter.export()


if __name__ == "__main__":
    main()
