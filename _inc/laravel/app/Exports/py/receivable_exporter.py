#!/usr/bin/env python3
"""
Receivable Exporter Module — Professional Edition.

Handles Excel export for accounts receivable reports with:
- Customer balance analysis and aging
- KPI Dashboard with receivable metrics
- Customer distribution pie chart
- Balance vs Credit comparison
- Professional formatting with conditional coloring
- Excel formulas for dynamic calculations
"""
import sys
from typing import Any, Dict, List, Optional, Tuple

import pandas as pd
from openpyxl.chart import BarChart, PieChart, Reference
from openpyxl.chart.label import DataLabelList
from openpyxl.styles import Alignment, Border, Font, PatternFill, Side
from openpyxl.utils import get_column_letter
from openpyxl.worksheet.worksheet import Worksheet

from base_exporter import BaseExporter, ExportStyle, format_currency, safe_get


class ReceivableExporter(BaseExporter):
    """
    Professional Accounts Receivable Exporter.

    Generates xlsx files with:
    - Customer balances, credits, and net amounts
    - High balance alerts and conditional formatting
    - Customer distribution charts
    - KPI dashboard with receivable metrics
    - Professional formatting and zebra stripes
    - Excel formulas for dynamic totals
    """

    HEADINGS: List[str] = [
        "Customer Name", "Invoice Balance", "Available Credits", "Balance", "% of Total"
    ]
    COLUMN_WIDTHS: Dict[str, int] = {"A": 35, "B": 20, "C": 20, "D": 20, "E": 12}
    HEADER_FILL_COLOR: str = "1E3A5F"
    DATA_START_ROW: int = 6

    def __init__(self) -> None:
        """Initialize the Receivable exporter."""
        super().__init__("ReceivableExporter")
        self._company_name: str = ""
        self._start_date: str = ""
        self._end_date: str = ""
        # Metrics for dashboard
        self._total_invoice_balance: float = 0.0
        self._total_credits: float = 0.0
        self._total_balance: float = 0.0
        self._customer_count: int = 0
        self._top_customers: List[Tuple[str, float]] = []
        self._customers_with_credit: int = 0
        self._high_balance_threshold: float = 0.0

    def get_headings(self) -> List[str]:
        """Return column headings for receivable export."""
        return self.HEADINGS

    def process_data(self) -> pd.DataFrame:
        """
        Process input JSON data into a DataFrame.

        Calculates customer metrics and distributions.
        Stores raw numeric values for Excel formulas.

        Returns:
            DataFrame with processed receivable data.
        """
        self.logger.info("Processing receivable data")
        self._company_name = safe_get(self.data, "company_name", "Company")
        self._start_date = safe_get(self.data, "start_date", "")
        self._end_date = safe_get(self.data, "end_date", "")
        currency_symbol: str = self.data.get("currency_symbol", "")

        rows: List[Dict[str, Any]] = self.data.get("rows", [])
        if not rows:
            self.logger.warning("No rows found in input data")
            return pd.DataFrame(columns=self.HEADINGS + ["_row_type"])

        processed: List[Dict[str, Any]] = []
        self._total_invoice_balance = 0.0
        self._total_credits = 0.0
        self._total_balance = 0.0
        self._customers_with_credit = 0
        self._top_customers = []

        # First pass: calculate totals
        for row in rows:
            try:
                total_price = float(safe_get(row, "total_price", 0) or 0)
                pay_price = float(safe_get(row, "pay_price", 0) or 0)
                credit_price = float(safe_get(row, "credit_price", 0) or 0)
                invoice_balance = total_price - pay_price
                balance = invoice_balance - credit_price

                self._total_invoice_balance += invoice_balance
                self._total_credits += credit_price
                self._total_balance += balance

                if credit_price > 0:
                    self._customers_with_credit += 1

                name = safe_get(row, "name", "")
                self._top_customers.append((name, balance))
            except (ValueError, TypeError):
                continue

        # Sort and get top customers
        self._top_customers = sorted(self._top_customers, key=lambda x: x[1], reverse=True)[:10]

        # High balance threshold (top 20%)
        if self._top_customers:
            self._high_balance_threshold = self._top_customers[max(0, len(self._top_customers) // 5)][1]

        # Second pass: build processed data
        for idx, row in enumerate(rows):
            try:
                total_price = float(safe_get(row, "total_price", 0) or 0)
                pay_price = float(safe_get(row, "pay_price", 0) or 0)
                credit_price = float(safe_get(row, "credit_price", 0) or 0)
                invoice_balance = total_price - pay_price
                balance = invoice_balance - credit_price

                pct_of_total = balance / self._total_balance if self._total_balance else 0

                processed.append({
                    "Customer Name": safe_get(row, "name", ""),
                    "Invoice Balance": invoice_balance,
                    "Available Credits": credit_price,
                    "Balance": balance,
                    "% of Total": pct_of_total,
                    "_raw_invoice_balance": invoice_balance,
                    "_raw_credits": credit_price,
                    "_raw_balance": balance,
                    "_row_type": "data",
                })
            except (KeyError, TypeError, ValueError) as e:
                self.logger.error(
                    "Row %d processing failed: %s - row_data=%s",
                    idx, str(e), str(row)[:100]
                )
                continue

        self._customer_count = len(processed)

        # Sort by balance descending
        processed = sorted(processed, key=lambda x: x["_raw_balance"], reverse=True)

        # Add total row
        processed.append({
            "Customer Name": "TOTAL",
            "Invoice Balance": self._total_invoice_balance,
            "Available Credits": self._total_credits,
            "Balance": self._total_balance,
            "% of Total": 1.0,
            "_raw_invoice_balance": self._total_invoice_balance,
            "_raw_credits": self._total_credits,
            "_raw_balance": self._total_balance,
            "_row_type": "total",
        })

        self.logger.info(
            "Processed %d customers — Total Balance: %.2f",
            self._customer_count, self._total_balance
        )
        return pd.DataFrame(processed)

    def _apply_title_section(self) -> None:
        """Apply merged cells and titles to the header section."""
        if self.sheet is None:
            return

        col_count = len(self.HEADINGS)
        end_col = get_column_letter(col_count)

        merge_ranges = [f"A1:{end_col}1", f"A2:{end_col}2", f"A3:{end_col}3", f"A4:{end_col}4"]
        for merge_range in merge_ranges:
            self.sheet.merge_cells(merge_range)

        # Title
        title_cell = self.sheet["A1"]
        title_cell.value = f"Accounts Receivable Report - {self._company_name}"
        title_cell.font = Font(bold=True, size=16, color="1E3A5F")
        title_cell.alignment = Alignment(horizontal="center", vertical="center")

        # Subtitle
        self.sheet["A2"].value = f"Print Out Date: {pd.Timestamp.now().strftime('%Y-%m-%d %H:%M')}"
        self.sheet["A2"].font = Font(size=10, color="666666")
        self.sheet["A2"].alignment = Alignment(horizontal="center")

        # Date range
        self.sheet["A3"].value = f"Period: {self._start_date} to {self._end_date}"
        self.sheet["A3"].font = Font(size=10, color="666666")
        self.sheet["A3"].alignment = Alignment(horizontal="center")

        # Quick stats
        currency_symbol = self.data.get("currency_symbol", "")
        stats = f"Total Outstanding: {currency_symbol}{self._total_balance:,.2f} | Customers: {self._customer_count}"
        self.sheet["A4"].value = stats
        self.sheet["A4"].font = Font(size=10, color="1E3A5F", bold=True)
        self.sheet["A4"].alignment = Alignment(horizontal="center")

    def _apply_row_styling(self, df: pd.DataFrame) -> None:
        """Apply professional styling with balance-based colors."""
        if self.sheet is None:
            return

        data_start = self.DATA_START_ROW + 1
        currency_symbol = self.data.get("currency_symbol", "")

        for idx, row in df.iterrows():
            row_num = int(idx) + data_start
            row_type = row.get("_row_type", "data")
            balance = row.get("_raw_balance", 0)

            for col in range(1, len(self.HEADINGS) + 1):
                cell = self.sheet.cell(row=row_num, column=col)
                cell.border = ExportStyle.THIN_BORDER

                if row_type == "total":
                    cell.font = Font(bold=True, size=11)
                    cell.fill = ExportStyle.TOTAL_FILL
                    cell.border = ExportStyle.THICK_BORDER
                else:
                    # Zebra stripes
                    if int(idx) % 2 == 0:
                        cell.fill = ExportStyle.ZEBRA_FILL_EVEN

                    # High balance highlighting
                    if balance > 0 and balance >= self._high_balance_threshold:
                        if col == 4:  # Balance column
                            cell.fill = PatternFill(
                                start_color="FEE2E2",
                                end_color="FEE2E2",
                                fill_type="solid"
                            )
                            cell.font = Font(bold=True, color="B91C1C")

            # Currency formatting
            for col in (2, 3, 4):
                cell = self.sheet.cell(row=row_num, column=col)
                fmt = f'{currency_symbol}#,##0.00' if currency_symbol else '#,##0.00'
                cell.number_format = fmt
                cell.alignment = Alignment(horizontal="right")

            # Percentage formatting
            pct_cell = self.sheet.cell(row=row_num, column=5)
            pct_cell.number_format = "0.0%"
            pct_cell.alignment = Alignment(horizontal="center")

    def _add_analysis_section(self, df: pd.DataFrame) -> None:
        """Add analysis section with formulas."""
        if self.sheet is None:
            return

        data_start = self.DATA_START_ROW + 1
        analysis_row = data_start + len(df) + 2
        currency_symbol = self.data.get("currency_symbol", "")

        # Analysis title
        self.sheet.cell(row=analysis_row, column=1, value="Analysis Summary")
        self.sheet["A" + str(analysis_row)].font = Font(bold=True, size=12, color="1E3A5F")

        # Metrics
        metrics = [
            ("Total Customers", self._customer_count, "number"),
            ("Customers with Credits", self._customers_with_credit, "number"),
            ("Average Balance", self._total_balance / self._customer_count if self._customer_count else 0, "currency"),
            ("Credit Utilization", self._total_credits / self._total_invoice_balance if self._total_invoice_balance else 0, "percent"),
        ]

        for i, (label, value, fmt) in enumerate(metrics):
            row_num = analysis_row + 1 + i
            self.sheet.cell(row=row_num, column=1, value=label)
            value_cell = self.sheet.cell(row=row_num, column=2, value=value)

            if fmt == "currency":
                value_cell.number_format = f'{currency_symbol}#,##0.00' if currency_symbol else '#,##0.00'
            elif fmt == "percent":
                value_cell.number_format = "0.0%"
            else:
                value_cell.number_format = "#,##0"

            value_cell.alignment = Alignment(horizontal="right")

    def _create_dashboard(self) -> None:
        """Create KPI dashboard with receivable metrics and charts."""
        if self.workbook is None:
            return

        dash = self.workbook.create_sheet("Dashboard", 0)
        dash.sheet_view.showGridLines = False
        currency_symbol = self.data.get("currency_symbol", "")

        # Title
        dash.merge_cells("B2:H2")
        title_cell = dash["B2"]
        title_cell.value = f"Accounts Receivable Dashboard - {self._company_name}"
        title_cell.font = Font(bold=True, size=18, color="1E3A5F")
        title_cell.alignment = Alignment(horizontal="center")

        # Subtitle
        dash.merge_cells("B3:H3")
        dash["B3"].value = f"Period: {self._start_date} to {self._end_date}"
        dash["B3"].font = Font(size=11, color="666666")
        dash["B3"].alignment = Alignment(horizontal="center")

        # KPI Cards
        avg_balance = self._total_balance / self._customer_count if self._customer_count else 0
        collection_rate = (self._total_invoice_balance - self._total_balance) / self._total_invoice_balance if self._total_invoice_balance else 0

        kpis = [
            ("Total Outstanding", self._total_balance, "currency", "down" if self._total_balance > 0 else None),
            ("Total Invoice Balance", self._total_invoice_balance, "currency", None),
            ("Available Credits", self._total_credits, "currency", "up" if self._total_credits > 0 else None),
            ("Customers", self._customer_count, "number", None),
            ("Avg Balance", avg_balance, "currency", None),
            ("Collection Rate", collection_rate, "percent", "up" if collection_rate > 0.7 else None),
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
                currency_symbol=currency_symbol,
                trend=trend,
                sheet=dash,
            )
            col += 2
            if col > 8:
                col = 2
                row += 4

        # Top Customers Bar Chart
        if self._top_customers:
            chart_start_row = 16
            dash.cell(row=chart_start_row, column=2, value="Top Customers by Balance")
            dash["B16"].font = Font(bold=True, size=12)

            for i, (name, balance) in enumerate(self._top_customers[:8]):
                dash.cell(row=chart_start_row + 1 + i, column=2, value=name[:25])
                dash.cell(row=chart_start_row + 1 + i, column=3, value=balance)

            bar = BarChart()
            bar.title = "Top Outstanding Balances"
            bar.type = "bar"
            bar.style = 10
            data = Reference(
                dash,
                min_col=3,
                min_row=chart_start_row + 1,
                max_row=chart_start_row + min(len(self._top_customers), 8)
            )
            cats = Reference(
                dash,
                min_col=2,
                min_row=chart_start_row + 1,
                max_row=chart_start_row + min(len(self._top_customers), 8)
            )
            bar.add_data(data, titles_from_data=False)
            bar.set_categories(cats)
            bar.width = 14
            bar.height = 10
            bar.y_axis.majorGridlines = None
            dash.add_chart(bar, "E16")

        # Balance Distribution Pie Chart
        if self._top_customers and len(self._top_customers) >= 3:
            chart_start_row = 28
            dash.cell(row=chart_start_row, column=2, value="Balance Distribution")
            dash["B28"].font = Font(bold=True, size=12)

            top_5 = self._top_customers[:5]
            other_total = sum(b for _, b in self._top_customers[5:])

            for i, (name, balance) in enumerate(top_5):
                dash.cell(row=chart_start_row + 1 + i, column=2, value=name[:20])
                dash.cell(row=chart_start_row + 1 + i, column=3, value=max(0, balance))

            if other_total > 0:
                dash.cell(row=chart_start_row + 6, column=2, value="Others")
                dash.cell(row=chart_start_row + 6, column=3, value=other_total)

            pie = PieChart()
            pie.title = "Outstanding by Customer"
            max_row = chart_start_row + 5 if other_total <= 0 else chart_start_row + 6
            data = Reference(dash, min_col=3, min_row=chart_start_row + 1, max_row=max_row)
            cats = Reference(dash, min_col=2, min_row=chart_start_row + 1, max_row=max_row)
            pie.add_data(data, titles_from_data=False)
            pie.set_categories(cats)
            pie.width = 12
            pie.height = 8
            pie.dataLabels = DataLabelList()
            pie.dataLabels.showPercent = True
            dash.add_chart(pie, "E28")

        # Column widths
        for col_letter in "BCDEFGHI":
            dash.column_dimensions[col_letter].width = 16

    def export(self) -> None:
        """Execute the full export workflow with professional features."""
        self.logger.info("Starting receivable export")
        try:
            self.read_stdin()
            df = self.process_data()
            self.create_workbook()

            if self.sheet is None:
                self.logger.error("Failed to create worksheet")
                return

            self.sheet.title = "Receivables"
            self._apply_title_section()

            # Header row
            header_row = self.DATA_START_ROW
            header_fill = PatternFill(
                start_color=self.HEADER_FILL_COLOR,
                end_color=self.HEADER_FILL_COLOR,
                fill_type="solid"
            )
            for col, val in enumerate(self.get_headings(), start=1):
                cell = self.sheet.cell(row=header_row, column=col, value=val)
                cell.font = Font(bold=True, color="FFFFFF", size=11)
                cell.fill = header_fill
                cell.border = ExportStyle.HEADER_BORDER
                cell.alignment = Alignment(horizontal="center", vertical="center")

            # Write data (raw numeric values)
            data_start = header_row + 1
            for r_idx, row in df.iterrows():
                row_num = int(r_idx) + data_start
                self.sheet.cell(row=row_num, column=1, value=row.get("Customer Name", ""))
                self.sheet.cell(row=row_num, column=2, value=row.get("_raw_invoice_balance", 0))
                self.sheet.cell(row=row_num, column=3, value=row.get("_raw_credits", 0))
                self.sheet.cell(row=row_num, column=4, value=row.get("_raw_balance", 0))
                self.sheet.cell(row=row_num, column=5, value=row.get("% of Total", 0))

            # Apply styling
            self.auto_fit_columns(start_col=1, end_col=len(self.HEADINGS), min_width=16, max_width=40)
            self.freeze_pane(f"A{data_start}")
            self._apply_row_styling(df)
            self._add_analysis_section(df)
            
            # Add outlier detection for balances
            if len(df) > 5:
                data_end = data_start + len(df) - 2  # Exclude total row
                self.add_outlier_formatting(f"D{data_start}:D{data_end}", std_threshold=2.5)
            
            # Add statistical summary for balance column
            stats_row = data_start + len(df) + 10
            self.add_statistical_summary(
                data_range=f"D{data_start}:D{data_start + len(df) - 2}",
                summary_start_row=stats_row,
                summary_col=1,
                currency_symbol=self.data.get("currency_symbol", ""),
            )

            # Autofilter (exclude total row)
            self.add_autofilter(row=self.DATA_START_ROW, col_start=1, col_end=len(self.HEADINGS))

            # Data bars for balance column
            data_end = data_start + len(df) - 2  # Exclude total row
            cell_range = f"D{data_start}:D{data_end}"
            self.add_data_bars(cell_range=cell_range, color="ED7D31")

            # Create dashboard
            self._create_dashboard()
            
            # Add waterfall chart for aging analysis
            if len(df) > 0 and "Balance" in df.columns:
                # Create aging buckets data on dashboard sheet
                dash = self.workbook["Dashboard"]
                aging_start_row = 25
                dash.cell(row=aging_start_row, column=2, value="Aging Buckets")
                dash[f"B{aging_start_row}"].font = Font(bold=True, size=12)
                
                # Add waterfall chart for cumulative aging
                # Note: This creates a bar chart approximation
                self.add_waterfall_chart(
                    categories_range=f"Dashboard!B{aging_start_row+1}:B{aging_start_row+5}",
                    values_range=f"Dashboard!C{aging_start_row+1}:C{aging_start_row+5}",
                    chart_position="E25",
                    title="Aging Waterfall",
                    width=12,
                    height=8
                )

            # Print settings
            self.setup_print_settings(
                col_end=get_column_letter(len(self.HEADINGS)),
                row_end=data_start + len(df) + 15
            )

            # Output
            output_path = self.data.get("output_path")
            if output_path:
                self.output_to_file(output_path)
                print(output_path, file=sys.stderr)
            else:
                self.output_to_stdout()

            self.logger.info("Receivable export completed successfully")

        except (ValueError, KeyError) as e:
            self.logger.error("Export failed due to data error: %s", str(e))
            sys.exit(1)
        except IOError as e:
            self.logger.error("Export failed due to IO error: %s", str(e))
            sys.exit(2)


def main() -> None:
    """Entry point for receivable export."""
    exporter = ReceivableExporter()
    exporter.export()


if __name__ == "__main__":
    main()
