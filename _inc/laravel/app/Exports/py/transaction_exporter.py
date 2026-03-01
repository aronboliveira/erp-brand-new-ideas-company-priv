#!/usr/bin/env python3
"""
Transaction Exporter Module.

Handles Excel export for transaction data.
"""
import sys
from typing import Any, Dict, List

import pandas as pd

from base_exporter import BaseExporter, format_date, parse_number, safe_get


class TransactionExporter(BaseExporter):
    """
    Exporter for Transaction data.

    Generates xlsx files with transaction ID, account, type,
    amount, description, date, and category.
    """

    HEADINGS: List[str] = [
        "Transaction Id", "Account", "Type",
        "Amount", "Description", "Date", "Category"
    ]
    COLUMN_WIDTHS: Dict[str, int] = {
        "A": 15, "B": 20, "C": 12, "D": 15, "E": 40, "F": 15, "G": 18
    }

    def __init__(self) -> None:
        """Initialize the Transaction exporter."""
        super().__init__("TransactionExporter")

    def get_headings(self) -> List[str]:
        """
        Return column headings for transaction export.

        Returns:
            List of column header strings.
        """
        return self.HEADINGS

    def process_data(self) -> pd.DataFrame:
        """
        Process input JSON data into a DataFrame.

        Expects data with 'rows' key containing transaction records.

        Returns:
            DataFrame with processed transaction data.
        """
        self.logger.info("Processing transaction data")
        rows: List[Dict[str, Any]] = self.data.get("rows", [])
        if not rows:
            self.logger.warning("No rows found in input data")
            return pd.DataFrame(columns=self.HEADINGS)
        processed: List[Dict[str, Any]] = []
        for idx, row in enumerate(rows):
            try:
                processed.append({
                    "Transaction Id": safe_get(row, "id", ""),
                    "Account": safe_get(row, "account", ""),
                    "Type": safe_get(row, "type", ""),
                    "Amount": parse_number(
                        safe_get(row, "amount", 0),
                        default=0.0
                    ),
                    "Description": safe_get(row, "description", ""),
                    "Date": format_date(safe_get(row, "date", "")),
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

    def export(self) -> None:
        """
        Execute the full export workflow.

        Reads JSON from stdin, processes data, creates styled workbook,
        and outputs binary xlsx to stdout.
        """
        self.logger.info("Starting transaction export")
        try:
            self.read_stdin()
            df = self.process_data()
            self.create_workbook()
            if self.sheet is None:
                self.logger.error("Failed to create worksheet")
                return
            self.sheet.title = "Transactions"
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
                self.apply_currency_format(
                    col=4,
                    row_start=2,
                    row_end=rows_written + 1,
                    symbol=self.data.get("currency_symbol", ""),
                )
            self.set_column_widths(self.COLUMN_WIDTHS)
            self.freeze_pane("A2")
            output_path = self.data.get("output_path")
            if output_path:
                self.output_to_file(output_path)
                print(output_path, file=sys.stderr)
            else:
                self.output_to_stdout()
            self.logger.info("Transaction export completed successfully")
        except (ValueError, KeyError) as e:
            self.logger.error("Export failed due to data error: %s", str(e))
            sys.exit(1)
        except IOError as e:
            self.logger.error("Export failed due to IO error: %s", str(e))
            sys.exit(2)


def main() -> None:
    """Entry point for transaction export."""
    exporter = TransactionExporter()
    exporter.export()


if __name__ == "__main__":
    main()
