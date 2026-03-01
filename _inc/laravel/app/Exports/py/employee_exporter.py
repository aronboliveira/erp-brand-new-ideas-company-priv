#!/usr/bin/env python3
"""
Employee Exporter Module.

Handles Excel export for employee data with personal info,
department/branch assignments, and banking details.
"""
import sys
from typing import Any, Dict, List

import pandas as pd

from base_exporter import BaseExporter, format_date, parse_number, safe_get


class EmployeeExporter(BaseExporter):
    """
    Exporter for Employee data.

    Generates xlsx files with employee personal information,
    organizational assignments, and banking/salary details.
    """

    HEADINGS: List[str] = [
        "Name", "Date of Birth", "Gender", "Phone Number", "Address",
        "Email ID", "Branch", "Department", "Designation", "Date of Join",
        "Account Holder Name", "Account Number", "Bank Name",
        "Bank Identifier Code", "Branch Location", "Salary"
    ]
    COLUMN_WIDTHS: Dict[str, int] = {
        "A": 20, "B": 15, "C": 10, "D": 15, "E": 30,
        "F": 25, "G": 15, "H": 15, "I": 15, "J": 15,
        "K": 20, "L": 18, "M": 20, "N": 18, "O": 20, "P": 15
    }

    def __init__(self) -> None:
        """Initialize the Employee exporter."""
        super().__init__("EmployeeExporter")

    def get_headings(self) -> List[str]:
        """
        Return column headings for employee export.

        Returns:
            List of column header strings.
        """
        return self.HEADINGS

    def process_data(self) -> pd.DataFrame:
        """
        Process input JSON data into a DataFrame.

        Expects data with 'rows' key containing employee records.

        Returns:
            DataFrame with processed employee data.
        """
        self.logger.info("Processing employee data")
        rows: List[Dict[str, Any]] = self.data.get("rows", [])
        if not rows:
            self.logger.warning("No rows found in input data")
            return pd.DataFrame(columns=self.HEADINGS)
        processed: List[Dict[str, Any]] = []
        for idx, row in enumerate(rows):
            try:
                processed.append({
                    "Name": safe_get(row, "name", ""),
                    "Date of Birth": format_date(safe_get(row, "dob", "")),
                    "Gender": safe_get(row, "gender", ""),
                    "Phone Number": safe_get(row, "phone", ""),
                    "Address": safe_get(row, "address", ""),
                    "Email ID": safe_get(row, "email", ""),
                    "Branch": safe_get(row, "branch", "-"),
                    "Department": safe_get(row, "department", "-"),
                    "Designation": safe_get(row, "designation", "-"),
                    "Date of Join": format_date(safe_get(row, "join_date", "")),
                    "Account Holder Name": safe_get(row, "account_holder_name", ""),
                    "Account Number": safe_get(row, "account_number", ""),
                    "Bank Name": safe_get(row, "bank_name", ""),
                    "Bank Identifier Code": safe_get(row, "bank_identifier_code", ""),
                    "Branch Location": safe_get(row, "branch_location", ""),
                    "Salary": parse_number(
                        safe_get(row, "salary", 0),
                        default=0.0
                    ),
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
        self.logger.info("Starting employee export")
        try:
            self.read_stdin()
            df = self.process_data()
            self.create_workbook()
            if self.sheet is None:
                self.logger.error("Failed to create worksheet")
                return
            self.sheet.title = "Employees"
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
                    col=16,
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
            self.logger.info("Employee export completed successfully")
        except (ValueError, KeyError) as e:
            self.logger.error("Export failed due to data error: %s", str(e))
            sys.exit(1)
        except IOError as e:
            self.logger.error("Export failed due to IO error: %s", str(e))
            sys.exit(2)


def main() -> None:
    """Entry point for employee export."""
    exporter = EmployeeExporter()
    exporter.export()


if __name__ == "__main__":
    main()
