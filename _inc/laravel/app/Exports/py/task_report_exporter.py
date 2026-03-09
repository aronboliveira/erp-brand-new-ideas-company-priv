#!/usr/bin/env python3
"""
Task Report Exporter Module.

Handles Excel export for project task reports.
"""
import sys
from typing import Any, Dict, List

import pandas as pd

from base_exporter import BaseExporter, format_date, safe_get


class TaskReportExporter(BaseExporter):
    """
    Exporter for Task Report data.

    Generates xlsx files with task ID, title, description,
    dates, priority, assignee, milestone, and status.
    """

    HEADINGS: List[str] = [
        "ID", "Title", "Description", "Start Date", "End Date",
        "Priority", "Assign To", "Milestone", "Status"
    ]
    COLUMN_WIDTHS: Dict[str, int] = {
        "A": 8, "B": 25, "C": 40, "D": 15, "E": 15,
        "F": 12, "G": 20, "H": 18, "I": 12
    }

    def __init__(self) -> None:
        """Initialize the Task Report exporter."""
        super().__init__("TaskReportExporter")

    def get_headings(self) -> List[str]:
        """
        Return column headings for task report export.

        Returns:
            List of column header strings.
        """
        return self.HEADINGS

    def process_data(self) -> pd.DataFrame:
        """
        Process input JSON data into a DataFrame.

        Expects data with 'rows' key containing task records.

        Returns:
            DataFrame with processed task data.
        """
        self.logger.info("Processing task report data")
        rows: List[Dict[str, Any]] = self.data.get("rows", [])
        if not rows:
            self.logger.warning("No rows found in input data")
            return pd.DataFrame(columns=self.HEADINGS)
        processed: List[Dict[str, Any]] = []
        for idx, row in enumerate(rows):
            try:
                processed.append({
                    "ID": safe_get(row, "id", ""),
                    "Title": safe_get(row, "title", ""),
                    "Description": safe_get(row, "description", ""),
                    "Start Date": format_date(safe_get(row, "start_date", "")),
                    "End Date": format_date(safe_get(row, "end_date", "")),
                    "Priority": safe_get(row, "priority", ""),
                    "Assign To": safe_get(row, "assign_to", ""),
                    "Milestone": safe_get(row, "milestone", ""),
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
        self.logger.info("Starting task report export")
        try:
            self.read_stdin()
            df = self.process_data()
            self.create_workbook()
            if self.sheet is None:
                self.logger.error("Failed to create worksheet")
                return
            self.sheet.title = "Task Report"
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
            self.logger.info("Task report export completed successfully")
        except (ValueError, KeyError) as e:
            self.logger.error("Export failed due to data error: %s", str(e))
            sys.exit(1)
        except IOError as e:
            self.logger.error("Export failed due to IO error: %s", str(e))
            sys.exit(2)


def main() -> None:
    """Entry point for task report export."""
    exporter = TaskReportExporter()
    exporter.export()


if __name__ == "__main__":
    main()
