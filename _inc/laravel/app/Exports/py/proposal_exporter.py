#!/usr/bin/env python3
"""
Proposal Exporter Module.

Handles Excel export for proposal data.
"""
import sys
from typing import Any, Dict, List

import pandas as pd

from base_exporter import BaseExporter, format_date, safe_get


class ProposalExporter(BaseExporter):
    """
    Exporter for Proposal data.

    Generates xlsx files with proposal ID, number, dates,
    category, and status information.
    """

    HEADINGS: List[str] = [
        "ID", "Proposal No", "Issue Date", "Send Date", "Category", "Status"
    ]
    COLUMN_WIDTHS: Dict[str, int] = {
        "A": 10, "B": 18, "C": 15, "D": 15, "E": 20, "F": 15
    }

    def __init__(self) -> None:
        """Initialize the Proposal exporter."""
        super().__init__("ProposalExporter")

    def get_headings(self) -> List[str]:
        """
        Return column headings for proposal export.

        Returns:
            List of column header strings.
        """
        return self.HEADINGS

    def process_data(self) -> pd.DataFrame:
        """
        Process input JSON data into a DataFrame.

        Expects data with 'rows' key containing proposal records.

        Returns:
            DataFrame with processed proposal data.
        """
        self.logger.info("Processing proposal data")
        rows: List[Dict[str, Any]] = self.data.get("rows", [])
        if not rows:
            self.logger.warning("No rows found in input data")
            return pd.DataFrame(columns=self.HEADINGS)
        processed: List[Dict[str, Any]] = []
        for idx, row in enumerate(rows):
            try:
                processed.append({
                    "ID": safe_get(row, "id", ""),
                    "Proposal No": safe_get(row, "proposal_no", ""),
                    "Issue Date": format_date(safe_get(row, "issue_date", "")),
                    "Send Date": format_date(safe_get(row, "send_date", "")),
                    "Category": safe_get(row, "category", ""),
                    "Status": safe_get(row, "status", ""),
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
        self.logger.info("Starting proposal export")
        try:
            self.read_stdin()
            df = self.process_data()
            self.create_workbook()
            if self.sheet is None:
                self.logger.error("Failed to create worksheet")
                return
            self.sheet.title = "Proposals"
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
            self.set_column_widths(self.COLUMN_WIDTHS)
            self.freeze_pane("A2")
            output_path = self.data.get("output_path")
            if output_path:
                self.output_to_file(output_path)
                print(output_path, file=sys.stderr)
            else:
                self.output_to_stdout()
            self.logger.info("Proposal export completed successfully")
        except (ValueError, KeyError) as e:
            self.logger.error("Export failed due to data error: %s", str(e))
            sys.exit(1)
        except IOError as e:
            self.logger.error("Export failed due to IO error: %s", str(e))
            sys.exit(2)


def main() -> None:
    """Entry point for proposal export."""
    exporter = ProposalExporter()
    exporter.export()


if __name__ == "__main__":
    main()
