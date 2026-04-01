#!/usr/bin/env python3
"""
Invoice Exporter Module — Professional Edition.

Handles Excel export for invoice data with:
- Status distribution and aging analysis
- KPI Dashboard with invoice metrics
- Status breakdown pie chart
- Aging analysis bar chart
- Professional formatting with conditional coloring
- Excel formulas for dynamic status summary
"""
import sys
from datetime import datetime
from typing import Any, Dict, List, Optional, Tuple

import pandas as pd
from openpyxl.chart import BarChart, PieChart, Reference
from openpyxl.chart.label import DataLabelList
from openpyxl.styles import Alignment, Border, Font, PatternFill, Side
from openpyxl.utils import get_column_letter
from openpyxl.worksheet.worksheet import Worksheet

from base_exporter import BaseExporter, ExportStyle, format_date, safe_get


class InvoiceExporter(BaseExporter):
    """
    Professional Invoice Exporter.

    Generates xlsx files with:
    - Invoice details: ID, dates, category, status
    - Status-based conditional formatting
    - Aging analysis (Current, 1-30, 31-60, 61-90, 90+ days)
    - KPI dashboard with invoice metrics
    - Status distribution pie chart
    - Professional formatting and zebra stripes
    """

    HEADINGS: List[str] = [
        "Invoice Id", "Issue Date", "Due Date", "Send Date",
        "Category", "Ref Number", "Status", "Days Overdue"
    ]
    COLUMN_WIDTHS: Dict[str, int] = {
        "A": 18, "B": 14, "C": 14, "D": 14, "E": 20, "F": 16, "G": 14, "H": 14
    }
    HEADER_FILL_RGB: str = "374151"
    DATA_START_ROW: int = 5

    # Status colors
    STATUS_COLORS: Dict[str, str] = {
        "Draft": "9CA3AF",       # Gray
        "Sent": "3B82F6",        # Blue
        "Paid": "10B981",        # Green
        "Partially Paid": "F59E0B",  # Amber
        "Overdue": "EF4444",     # Red
        "Cancelled": "6B7280",   # Dark Gray
    }

    def __init__(self) -> None:
        """Initialize the Invoice exporter."""
        super().__init__("InvoiceExporter")
        self._company_name: str = ""
        # Metrics for dashboard
        self._invoice_count: int = 0
        self._status_counts: Dict[str, int] = {}
        self._aging_buckets: Dict[str, int] = {
            "Current": 0,
            "1-30 days": 0,
            "31-60 days": 0,
            "61-90 days": 0,
            "90+ days": 0,
        }
        self._overdue_count: int = 0

    def get_headings(self) -> List[str]:
        """Return column headings for invoice export."""
        return self.HEADINGS

    def _calculate_days_overdue(self, due_date_str: str) -> int:
        """Calculate days overdue from due date string."""
        if not due_date_str:
            return 0
        try:
            due_date = pd.to_datetime(due_date_str)
            today = pd.Timestamp.now().normalize()
            diff = (today - due_date).days
            return max(0, diff)  # Only positive days
        except (ValueError, TypeError):
            return 0

    def _get_aging_bucket(self, days_overdue: int) -> str:
        """Determine aging bucket from days overdue."""
        if days_overdue <= 0:
            return "Current"
        elif days_overdue <= 30:
            return "1-30 days"
        elif days_overdue <= 60:
            return "31-60 days"
        elif days_overdue <= 90:
            return "61-90 days"
        else:
            return "90+ days"

    def process_data(self) -> pd.DataFrame:
        """
        Process input JSON data into a DataFrame.

        Calculates aging metrics and status distributions.

        Returns:
            DataFrame with processed invoice data.
        """
        self.logger.info("Processing invoice data")
        self._company_name = safe_get(self.data, "company_name", "Company")

        rows: List[Dict[str, Any]] = self.data.get("rows", [])
        if not rows:
            self.logger.warning("No rows found in input data")
            return pd.DataFrame(columns=self.HEADINGS + ["_row_type"])

        processed: List[Dict[str, Any]] = []
        self._status_counts = {}
        self._aging_buckets = {k: 0 for k in self._aging_buckets}
        self._overdue_count = 0

        for idx, row in enumerate(rows):
            try:
                status = safe_get(row, "status", "Unknown")
                due_date = safe_get(row, "due_date", "")
                days_overdue = self._calculate_days_overdue(due_date)
                aging_bucket = self._get_aging_bucket(days_overdue)

                # Update metrics
                self._status_counts[status] = self._status_counts.get(status, 0) + 1
                self._aging_buckets[aging_bucket] = self._aging_buckets.get(aging_bucket, 0) + 1

                if days_overdue > 0 and status not in ("Paid", "Cancelled"):
                    self._overdue_count += 1

                processed.append({
                    "Invoice Id": safe_get(row, "invoice_id", ""),
                    "Issue Date": format_date(safe_get(row, "issue_date", "")),
                    "Due Date": format_date(due_date),
                    "Send Date": format_date(safe_get(row, "send_date", "")),
                    "Category": safe_get(row, "category", ""),
                    "Ref Number": safe_get(row, "ref_number", ""),
                    "Status": status,
                    "Days Overdue": days_overdue if days_overdue > 0 else "",
                    "_raw_status": status,
                    "_raw_days_overdue": days_overdue,
                    "_row_type": "data",
                })
            except (KeyError, TypeError) as e:
                self.logger.error(
                    "Row %d processing failed: %s - row_data=%s",
                    idx, str(e), str(row)[:100]
                )
                continue

        self._invoice_count = len(processed)
        self.logger.info(
            "Processed %d invoices — Overdue: %d",
            self._invoice_count, self._overdue_count
        )
        return pd.DataFrame(processed)

    def _apply_title_section(self) -> None:
        """Apply merged cells and titles to the header section."""
        if self.sheet is None:
            return

        col_count = len(self.HEADINGS)
        end_col = get_column_letter(col_count)

        merge_ranges = [f"A1:{end_col}1", f"A2:{end_col}2", f"A3:{end_col}3"]
        for merge_range in merge_ranges:
            self.sheet.merge_cells(merge_range)

        # Title
        title_cell = self.sheet["A1"]
        title_cell.value = f"Invoice Report - {self._company_name}"
        title_cell.font = Font(bold=True, size=16, color="374151")
        title_cell.alignment = Alignment(horizontal="center", vertical="center")

        # Subtitle
        self.sheet["A2"].value = f"Generated: {pd.Timestamp.now().strftime('%Y-%m-%d %H:%M')}"
        self.sheet["A2"].font = Font(size=10, color="666666")
        self.sheet["A2"].alignment = Alignment(horizontal="center")

        # Quick stats
        paid_count = self._status_counts.get("Paid", 0)
        stats = f"Total: {self._invoice_count} | Paid: {paid_count} | Overdue: {self._overdue_count}"
        self.sheet["A3"].value = stats
        self.sheet["A3"].font = Font(size=10, color="374151", bold=True)
        self.sheet["A3"].alignment = Alignment(horizontal="center")

    def _apply_row_styling(self, df: pd.DataFrame) -> None:
        """Apply professional styling with status-based colors."""
        if self.sheet is None:
            return

        data_start = self.DATA_START_ROW + 1

        for idx, row in df.iterrows():
            row_num = int(idx) + data_start
            status = row.get("_raw_status", "")
            days_overdue = row.get("_raw_days_overdue", 0)

            for col in range(1, len(self.HEADINGS) + 1):
                cell = self.sheet.cell(row=row_num, column=col)
                cell.border = ExportStyle.THIN_BORDER

                # Zebra stripes
                if int(idx) % 2 == 0:
                    cell.fill = ExportStyle.ZEBRA_FILL_EVEN

            # Status column coloring
            status_cell = self.sheet.cell(row=row_num, column=7)
            status_color = self.STATUS_COLORS.get(status, "9CA3AF")
            status_cell.fill = PatternFill(
                start_color=status_color,
                end_color=status_color,
                fill_type="solid"
            )
            # White text for dark backgrounds
            if status in ("Paid", "Overdue", "Sent", "Cancelled"):
                status_cell.font = Font(bold=True, color="FFFFFF")
            else:
                status_cell.font = Font(bold=True)

            # Days overdue highlighting
            overdue_cell = self.sheet.cell(row=row_num, column=8)
            if days_overdue > 0:
                if days_overdue > 90:
                    overdue_cell.fill = PatternFill(
                        start_color="FEE2E2",
                        end_color="FEE2E2",
                        fill_type="solid"
                    )
                    overdue_cell.font = Font(color="B91C1C", bold=True)
                elif days_overdue > 60:
                    overdue_cell.fill = PatternFill(
                        start_color="FEF3C7",
                        end_color="FEF3C7",
                        fill_type="solid"
                    )
                    overdue_cell.font = Font(color="B45309")
                elif days_overdue > 30:
                    overdue_cell.fill = PatternFill(
                        start_color="FEF9C3",
                        end_color="FEF9C3",
                        fill_type="solid"
                    )
                    overdue_cell.font = Font(color="CA8A04")

            # Date column alignment
            for date_col in (2, 3, 4):
                self.sheet.cell(row=row_num, column=date_col).alignment = Alignment(horizontal="center")

    def _add_summary_section(self, df: pd.DataFrame) -> None:
        """Add summary section below data."""
        if self.sheet is None:
            return

        data_start = self.DATA_START_ROW + 1
        summary_row = data_start + len(df) + 2

        # Summary title
        self.sheet.cell(row=summary_row, column=1, value="Status Summary")
        self.sheet["A" + str(summary_row)].font = Font(bold=True, size=12, color="374151")

        # Status breakdown
        for i, (status, count) in enumerate(self._status_counts.items()):
            row_num = summary_row + 1 + i
            self.sheet.cell(row=row_num, column=1, value=status)
            count_cell = self.sheet.cell(row=row_num, column=2, value=count)
            count_cell.alignment = Alignment(horizontal="right")

            # Percentage
            pct = count / self._invoice_count if self._invoice_count else 0
            pct_cell = self.sheet.cell(row=row_num, column=3, value=pct)
            pct_cell.number_format = "0.0%"
            pct_cell.alignment = Alignment(horizontal="right")

        # Aging summary
        aging_start_row = summary_row + len(self._status_counts) + 3
        self.sheet.cell(row=aging_start_row, column=1, value="Aging Summary")
        self.sheet["A" + str(aging_start_row)].font = Font(bold=True, size=12, color="374151")

        for i, (bucket, count) in enumerate(self._aging_buckets.items()):
            row_num = aging_start_row + 1 + i
            self.sheet.cell(row=row_num, column=1, value=bucket)
            count_cell = self.sheet.cell(row=row_num, column=2, value=count)
            count_cell.alignment = Alignment(horizontal="right")

    def _create_dashboard(self) -> None:
        """Create KPI dashboard with invoice metrics and charts."""
        if self.workbook is None:
            return

        dash = self.workbook.create_sheet("Dashboard", 0)
        dash.sheet_view.showGridLines = False

        # Title
        dash.merge_cells("B2:H2")
        title_cell = dash["B2"]
        title_cell.value = f"Invoice Dashboard - {self._company_name}"
        title_cell.font = Font(bold=True, size=18, color="374151")
        title_cell.alignment = Alignment(horizontal="center")

        # Subtitle
        dash.merge_cells("B3:H3")
        dash["B3"].value = f"Generated: {pd.Timestamp.now().strftime('%Y-%m-%d %H:%M')}"
        dash["B3"].font = Font(size=11, color="666666")
        dash["B3"].alignment = Alignment(horizontal="center")

        # KPI Cards
        paid_count = self._status_counts.get("Paid", 0)
        paid_pct = paid_count / self._invoice_count if self._invoice_count else 0
        overdue_pct = self._overdue_count / self._invoice_count if self._invoice_count else 0

        kpis = [
            ("Total Invoices", self._invoice_count, "number", None),
            ("Paid Invoices", paid_count, "number", "up" if paid_pct > 0.5 else None),
            ("Overdue Invoices", self._overdue_count, "number", "down" if self._overdue_count > 0 else None),
            ("Paid Rate", paid_pct, "percent", "up" if paid_pct > 0.7 else None),
        ]

        row = 6
        col = 2
        for label, value, fmt_type, trend in kpis:
            self.add_kpi_cell(
                row=row,
                col=col,
                label=label,
                value=value,
                format_type=fmt_type,
                trend=trend,
                sheet=dash,
            )
            col += 2

        # Status Distribution Pie Chart
        if self._status_counts:
            chart_start_row = 12
            dash.cell(row=chart_start_row, column=2, value="Status Distribution")
            dash["B12"].font = Font(bold=True, size=12)

            for i, (status, count) in enumerate(self._status_counts.items()):
                dash.cell(row=chart_start_row + 1 + i, column=2, value=status)
                dash.cell(row=chart_start_row + 1 + i, column=3, value=count)

            pie = PieChart()
            pie.title = "Invoice Status"
            data = Reference(
                dash,
                min_col=3,
                min_row=chart_start_row + 1,
                max_row=chart_start_row + len(self._status_counts)
            )
            cats = Reference(
                dash,
                min_col=2,
                min_row=chart_start_row + 1,
                max_row=chart_start_row + len(self._status_counts)
            )
            pie.add_data(data, titles_from_data=False)
            pie.set_categories(cats)
            pie.width = 12
            pie.height = 8
            pie.dataLabels = DataLabelList()
            pie.dataLabels.showPercent = True
            dash.add_chart(pie, "E12")

        # Aging Distribution Bar Chart
        if any(self._aging_buckets.values()):
            chart_start_row = 24
            dash.cell(row=chart_start_row, column=2, value="Aging Analysis")
            dash["B24"].font = Font(bold=True, size=12)

            for i, (bucket, count) in enumerate(self._aging_buckets.items()):
                dash.cell(row=chart_start_row + 1 + i, column=2, value=bucket)
                dash.cell(row=chart_start_row + 1 + i, column=3, value=count)

            bar = BarChart()
            bar.title = "Invoice Aging"
            bar.type = "col"
            bar.style = 10
            data = Reference(
                dash,
                min_col=3,
                min_row=chart_start_row + 1,
                max_row=chart_start_row + 5
            )
            cats = Reference(
                dash,
                min_col=2,
                min_row=chart_start_row + 1,
                max_row=chart_start_row + 5
            )
            bar.add_data(data, titles_from_data=False)
            bar.set_categories(cats)
            bar.width = 14
            bar.height = 8
            bar.y_axis.majorGridlines = None
            dash.add_chart(bar, "E24")

        # Column widths
        for col_letter in "BCDEFGHI":
            dash.column_dimensions[col_letter].width = 16

    def export(self) -> None:
        """Execute the full export workflow with professional features."""
        self.logger.info("Starting invoice export")
        try:
            self.read_stdin()
            df = self.process_data()
            self.create_workbook()

            if self.sheet is None:
                self.logger.error("Failed to create worksheet")
                return

            self.sheet.title = "Invoices"
            self._apply_title_section()

            # Header row
            header_row = self.DATA_START_ROW
            header_fill = PatternFill(
                start_color=self.HEADER_FILL_RGB,
                end_color=self.HEADER_FILL_RGB,
                fill_type="solid"
            )
            for col, val in enumerate(self.get_headings(), start=1):
                cell = self.sheet.cell(row=header_row, column=col, value=val)
                cell.font = Font(bold=True, color="FFFFFF", size=11)
                cell.fill = header_fill
                cell.border = ExportStyle.HEADER_BORDER
                cell.alignment = Alignment(horizontal="center", vertical="center")

            # Write data
            data_start = header_row + 1
            display_cols = [h for h in self.HEADINGS]

            for r_idx, row in df.iterrows():
                row_num = int(r_idx) + data_start
                for c_idx, col_name in enumerate(display_cols, start=1):
                    value = row.get(col_name, "")
                    self.sheet.cell(row=row_num, column=c_idx, value=value)

            # Apply styling
            self.auto_fit_columns(start_col=1, end_col=len(self.HEADINGS), min_width=14, max_width=35)
            self.freeze_pane(f"A{data_start}")
            self._apply_row_styling(df)
            self._add_summary_section(df)
            
            # Add data validation for status column (commented for reference)
            # If adding an editable status column, uncomment:
            # data_end = data_start + len(df) - 1
            # status_options = ["Draft", "Sent", "Partial", "Paid", "Cancelled"]
            # self.add_dropdown_validation(f"I{data_start}:I{data_end}", status_options)
            
            # Add outlier detection for days overdue
            if len(df) > 5:
                data_end = data_start + len(df) - 1
                self.add_outlier_formatting(f"H{data_start}:H{data_end}", std_threshold=2.0)

            # Autofilter
            self.add_autofilter(row=self.DATA_START_ROW, col_start=1, col_end=len(self.HEADINGS))

            # Create dashboard
            self._create_dashboard()

            # Print settings
            self.setup_print_settings(
                col_end=get_column_letter(len(self.HEADINGS)),
                row_end=data_start + len(df) + 20,
                landscape=True
            )

            # Output
            output_path = self.data.get("output_path")
            if output_path:
                self.output_to_file(output_path)
                print(output_path, file=sys.stderr)
            else:
                self.output_to_stdout()

            self.logger.info("Invoice export completed successfully")

        except (ValueError, KeyError) as e:
            self.logger.error("Export failed due to data error: %s", str(e))
            sys.exit(1)
        except IOError as e:
            self.logger.error("Export failed due to IO error: %s", str(e))
            sys.exit(2)


def main() -> None:
    """Entry point for invoice export."""
    exporter = InvoiceExporter()
    exporter.export()


if __name__ == "__main__":
    main()
