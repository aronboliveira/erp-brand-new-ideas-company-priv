#!/usr/bin/env python3
"""
Payslip Exporter Module.

Handles Excel export for payslip data with employee banking
details and salary information.
"""
import sys
from typing import Any, Dict, List

import pandas as pd
from openpyxl.styles import Border, Font, PatternFill, Side

from base_exporter import BaseExporter, parse_number, safe_get


class PayslipExporter(BaseExporter):
    """
    Exporter for Payslip data.

    Generates xlsx files with employee ID, name, salary details,
    payment status, and banking information.
    """

    HEADINGS: List[str] = [
        "EMP ID", "Name", "Salary", "Net Salary", "Status",
        "Account Holder Name", "Account Number", "Bank Name",
        "Bank Identifier Code", "Branch Location", "Tax Payer Id"
    ]
    COLUMN_WIDTHS: Dict[str, int] = {
        "A": 12, "B": 20, "C": 15, "D": 15, "E": 10,
        "F": 20, "G": 18, "H": 20, "I": 20, "J": 20, "K": 15
    }
    HEADER_FILL_COLOR: str = "001122"

    def __init__(self) -> None:
        """Initialize the Payslip exporter."""
        super().__init__("PayslipExporter")

    def get_headings(self) -> List[str]:
        """
        Return column headings for payslip export.

        Returns:
            List of column header strings.
        """
        return self.HEADINGS

    def process_data(self) -> pd.DataFrame:
        """
        Process input JSON data into a DataFrame.

        Expects data with 'rows' key containing payslip records.

        Returns:
            DataFrame with processed payslip data.
        """
        self.logger.info("Processing payslip data")
        rows: List[Dict[str, Any]] = self.data.get("rows", [])
        if not rows:
            self.logger.warning("No rows found in input data")
            return pd.DataFrame(columns=self.HEADINGS)
        processed: List[Dict[str, Any]] = []
        for idx, row in enumerate(rows):
            try:
                status_val = safe_get(row, "status", 0)
                status_str = "Paid" if status_val == 1 else "UnPaid"
                processed.append({
                    "EMP ID": safe_get(row, "emp_id", ""),
                    "Name": safe_get(row, "name", ""),
                    "Salary": parse_number(
                        safe_get(row, "gross_salary", 0),
                        default=0.0
                    ),
                    "Net Salary": parse_number(
                        safe_get(row, "net_payable", 0),
                        default=0.0
                    ),
                    "Status": status_str,
                    "Account Holder Name": safe_get(row, "account_holder_name", ""),
                    "Account Number": safe_get(row, "account_number", ""),
                    "Bank Name": safe_get(row, "bank_name", ""),
                    "Bank Identifier Code": safe_get(row, "bank_identifier_code", ""),
                    "Branch Location": safe_get(row, "branch_location", ""),
                    "Tax Payer Id": safe_get(row, "tax_payer_id", ""),
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
        """Apply custom header styling for payslip."""
        if self.sheet is None:
            return
        header_fill = PatternFill(
            start_color=self.HEADER_FILL_COLOR,
            end_color=self.HEADER_FILL_COLOR,
            fill_type="solid"
        )
        header_border = Border(
            left=Side(style="thin", color="333333"),
            right=Side(style="thin", color="333333"),
            top=Side(style="hair", color="333333"),
            bottom=Side(style="hair", color="333333"),
        )
        for col in range(1, len(self.HEADINGS) + 1):
            cell = self.sheet.cell(row=1, column=col)
            cell.font = Font(bold=True, color="FFFFFF")
            cell.fill = header_fill
            cell.border = header_border

    def export(self) -> None:
        """
        Execute the full export workflow.

        Reads JSON from stdin, processes data, creates styled workbook,
        and outputs binary xlsx to stdout.
        """
        self.logger.info("Starting payslip export")
        try:
            self.read_stdin()
            df = self.process_data()
            self.create_workbook()
            if self.sheet is None:
                self.logger.error("Failed to create worksheet")
                return
            self.sheet.title = "Payslips"
            for col, val in enumerate(self.get_headings(), start=1):
                self.sheet.cell(row=1, column=col, value=val)
            self._apply_header_style()
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
                    col=3,
                    row_start=2,
                    row_end=rows_written + 1,
                    symbol=symbol,
                )
                self.apply_currency_format(
                    col=4,
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
            self.logger.info("Payslip export completed successfully")
        except (ValueError, KeyError) as e:
            self.logger.error("Export failed due to data error: %s", str(e))
            sys.exit(1)
        except IOError as e:
            self.logger.error("Export failed due to IO error: %s", str(e))
            sys.exit(2)


def main() -> None:
    """Entry point for payslip export."""
    exporter = PayslipExporter()
    exporter.export()


if __name__ == "__main__":
    main()
