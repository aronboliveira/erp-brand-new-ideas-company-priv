#!/usr/bin/env python3
"""
Sales Report Exporter Module — Professional Edition.

Handles Excel export for sales reports (by Item or Customer) with:
- KPI Dashboard with sales metrics and trends
- Bar charts for top performers
- Pie charts for distribution analysis
- Professional formatting with zebra stripes
- Excel formulas for dynamic calculations
- Autofilter and print settings
"""
import sys
from typing import Any, Dict, List, Tuple

import pandas as pd
from openpyxl.chart import BarChart, PieChart, Reference
from openpyxl.chart.label import DataLabelList
from openpyxl.styles import Alignment, Font, PatternFill

from base_exporter import BaseExporter, ExportStyle, safe_get


class SalesReportExporter(BaseExporter):
    """
    Professional Sales Report Exporter.

    Generates xlsx files with:
    - Item-based or customer-based sales breakdown
    - Top performers bar chart
    - Distribution pie chart
    - KPI dashboard with sales metrics
    - Professional formatting and zebra stripes
    - Excel formulas for totals and averages
    """

    HEADINGS_ITEM: List[str] = [
        "Item Name", "Quantity Sold", "Amount", "Average Amount"
    ]
    HEADINGS_CUSTOMER: List[str] = [
        "Customer Name", "Invoice Count", "Sales", "Sales With Tax"
    ]
    COLUMN_WIDTHS_ITEM: Dict[str, int] = {"A": 35, "B": 18, "C": 20, "D": 22}
    COLUMN_WIDTHS_CUSTOMER: Dict[str, int] = {"A": 35, "B": 16, "C": 20, "D": 22}
    HEADER_FILL_RGB: str = "2E75B6"
    DATA_START_ROW: int = 6

    def __init__(self) -> None:
        """Initialize the Sales Report exporter."""
        super().__init__("SalesReportExporter")
        self._company_name: str = ""
        self._start_date: str = ""
        self._end_date: str = ""
        self._report_name: str = "Item"
        # Metrics for dashboard
        self._total_sales: float = 0.0
        self._total_quantity: int = 0
        self._avg_sale: float = 0.0
        self._top_performers: List[Tuple[str, float]] = []
        self._record_count: int = 0

    def get_headings(self) -> List[str]:
        """
        Return column headings based on report type.

        Returns:
            List of column header strings.
        """
        if self._report_name == "Item":
            return self.HEADINGS_ITEM
        return self.HEADINGS_CUSTOMER

    def _get_column_widths(self) -> Dict[str, int]:
        """Return column widths based on report type."""
        if self._report_name == "Item":
            return self.COLUMN_WIDTHS_ITEM
        return self.COLUMN_WIDTHS_CUSTOMER

    def process_data(self) -> pd.DataFrame:
        """
        Process input JSON data into a DataFrame.

        Captures sales metrics for dashboard and charting.
        Stores raw numeric values for Excel formulas.

        Returns:
            DataFrame with processed sales data.
        """
        self.logger.info("Processing sales report data")
        self._company_name = safe_get(self.data, "company_name", "Company")
        self._start_date = safe_get(self.data, "start_date", "")
        self._end_date = safe_get(self.data, "end_date", "")
        self._report_name = safe_get(self.data, "report_name", "Item")

        rows: List[Dict[str, Any]] = self.data.get("rows", [])
        headings = self.get_headings()

        if not rows:
            self.logger.warning("No rows found in input data")
            return pd.DataFrame(columns=headings + ["_row_type"])

        processed: List[Dict[str, Any]] = []
        self._top_performers = []
        self._total_sales = 0.0
        self._total_quantity = 0

        for idx, row in enumerate(rows):
            try:
                if self._report_name == "Item":
                    name = safe_get(row, "name", "")
                    quantity = int(safe_get(row, "invoice_count", 0) or 0)
                    amount = float(safe_get(row, "price", 0) or 0)
                    avg_amount = float(safe_get(row, "avg_price", 0) or 0)

                    self._total_sales += amount
                    self._total_quantity += quantity
                    self._top_performers.append((name, amount))

                    processed.append({
                        "Item Name": name,
                        "Quantity Sold": quantity,
                        "Amount": amount,
                        "Average Amount": avg_amount,
                        "_raw_amount": amount,
                        "_raw_avg": avg_amount,
                        "_row_type": "data",
                    })
                else:
                    name = safe_get(row, "name", "")
                    invoice_count = int(safe_get(row, "invoice_count", 0) or 0)
                    price = float(safe_get(row, "price", 0) or 0)
                    total_tax = float(safe_get(row, "total_tax", 0) or 0)
                    sales_with_tax = price + total_tax

                    self._total_sales += price
                    self._total_quantity += invoice_count
                    self._top_performers.append((name, price))

                    processed.append({
                        "Customer Name": name,
                        "Invoice Count": invoice_count,
                        "Sales": price,
                        "Sales With Tax": sales_with_tax,
                        "_raw_sales": price,
                        "_raw_sales_tax": sales_with_tax,
                        "_row_type": "data",
                    })
            except (KeyError, TypeError, ValueError) as e:
                self.logger.error(
                    "Row %d processing failed: %s - row_data=%s",
                    idx, str(e), str(row)[:100]
                )
                continue

        self._record_count = len(processed)
        self._avg_sale = self._total_sales / self._record_count if self._record_count else 0
        self._top_performers = sorted(self._top_performers, key=lambda x: x[1], reverse=True)[:10]

        self.logger.info(
            "Processed %d rows — Total Sales: %.2f, Total Qty: %d",
            len(processed), self._total_sales, self._total_quantity
        )
        return pd.DataFrame(processed)

    def _apply_title_section(self) -> None:
        """Apply merged cells and titles to the header section."""
        if self.sheet is None:
            return

        merge_ranges = ["A1:D1", "A2:D2", "A3:D3", "A4:D4"]
        for merge_range in merge_ranges:
            self.sheet.merge_cells(merge_range)

        # Title
        title = f"Sales Report ({self._report_name}) - {self._company_name}"
        title_cell = self.sheet["A1"]
        title_cell.value = title
        title_cell.font = Font(bold=True, size=16, color="2E75B6")
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
        quick_stats = f"Total: {currency_symbol}{self._total_sales:,.2f} | Records: {self._record_count}"
        self.sheet["A4"].value = quick_stats
        self.sheet["A4"].font = Font(size=10, color="2E75B6", bold=True)
        self.sheet["A4"].alignment = Alignment(horizontal="center")

    def _apply_row_styling(self, df: pd.DataFrame) -> None:
        """Apply professional styling with zebra stripes."""
        if self.sheet is None:
            return

        data_start = self.DATA_START_ROW + 1
        currency_symbol = self.data.get("currency_symbol", "")
        is_item_report = self._report_name == "Item"

        for idx, row in df.iterrows():
            row_num = int(idx) + data_start

            for col in range(1, 5):
                cell = self.sheet.cell(row=row_num, column=col)
                cell.border = ExportStyle.THIN_BORDER

                # Zebra stripes
                if int(idx) % 2 == 0:
                    cell.fill = ExportStyle.ZEBRA_FILL_EVEN

                # Currency formatting
                if is_item_report and col in (3, 4):  # Amount, Average Amount
                    fmt = f'{currency_symbol}#,##0.00' if currency_symbol else '#,##0.00'
                    cell.number_format = fmt
                    cell.alignment = Alignment(horizontal="right")
                elif not is_item_report and col in (3, 4):  # Sales, Sales With Tax
                    fmt = f'{currency_symbol}#,##0.00' if currency_symbol else '#,##0.00'
                    cell.number_format = fmt
                    cell.alignment = Alignment(horizontal="right")
                elif col == 2:  # Quantity/Invoice Count
                    cell.number_format = "#,##0"
                    cell.alignment = Alignment(horizontal="center")

    def _add_totals_row(self, df: pd.DataFrame) -> int:
        """Add totals row with Excel formulas."""
        if self.sheet is None:
            return 0

        data_start = self.DATA_START_ROW + 1
        totals_row = data_start + len(df)
        currency_symbol = self.data.get("currency_symbol", "")

        # Total label
        total_cell = self.sheet.cell(row=totals_row, column=1, value="TOTAL")
        total_cell.font = Font(bold=True, size=11)
        total_cell.fill = ExportStyle.TOTAL_FILL
        total_cell.border = ExportStyle.THICK_BORDER
        total_cell.alignment = Alignment(horizontal="left")

        is_item_report = self._report_name == "Item"

        # Quantity/Invoice Count SUM
        qty_cell = self.sheet.cell(row=totals_row, column=2)
        qty_cell.value = f"=SUM(B{data_start}:B{totals_row - 1})"
        qty_cell.font = Font(bold=True)
        qty_cell.fill = ExportStyle.TOTAL_FILL
        qty_cell.border = ExportStyle.THICK_BORDER
        qty_cell.number_format = "#,##0"
        qty_cell.alignment = Alignment(horizontal="center")

        # Sales/Amount SUM
        sales_cell = self.sheet.cell(row=totals_row, column=3)
        sales_cell.value = f"=SUM(C{data_start}:C{totals_row - 1})"
        sales_cell.font = Font(bold=True)
        sales_cell.fill = ExportStyle.TOTAL_FILL
        sales_cell.border = ExportStyle.THICK_BORDER
        fmt = f'{currency_symbol}#,##0.00' if currency_symbol else '#,##0.00'
        sales_cell.number_format = fmt
        sales_cell.alignment = Alignment(horizontal="right")

        # Average (for Item report) or Sales With Tax Sum (for Customer report)
        col4_cell = self.sheet.cell(row=totals_row, column=4)
        if is_item_report:
            col4_cell.value = f"=AVERAGE(D{data_start}:D{totals_row - 1})"
        else:
            col4_cell.value = f"=SUM(D{data_start}:D{totals_row - 1})"
        col4_cell.font = Font(bold=True)
        col4_cell.fill = ExportStyle.TOTAL_FILL
        col4_cell.border = ExportStyle.THICK_BORDER
        col4_cell.number_format = fmt
        col4_cell.alignment = Alignment(horizontal="right")

        return totals_row

    def _create_dashboard(self) -> None:
        """Create KPI dashboard sheet with sales metrics and charts."""
        if self.workbook is None:
            return

        dash = self.workbook.create_sheet("Dashboard", 0)
        dash.sheet_view.showGridLines = False
        currency_symbol = self.data.get("currency_symbol", "")

        # Title
        dash.merge_cells("B2:H2")
        title_cell = dash["B2"]
        title_cell.value = f"Sales Dashboard - {self._company_name}"
        title_cell.font = Font(bold=True, size=18, color="2E75B6")
        title_cell.alignment = Alignment(horizontal="center")

        # Subtitle
        dash.merge_cells("B3:H3")
        dash["B3"].value = f"Period: {self._start_date} to {self._end_date}"
        dash["B3"].font = Font(size=11, color="666666")
        dash["B3"].alignment = Alignment(horizontal="center")

        # KPI Cards
        kpis = [
            ("Total Sales", self._total_sales, "currency", "up" if self._total_sales > 0 else None),
            ("Total Qty/Invoices", self._total_quantity, "number", None),
            ("Average Sale", self._avg_sale, "currency", None),
            ("Records", self._record_count, "number", None),
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

        # Top Performers Bar Chart Data
        if self._top_performers:
            chart_start_row = 12
            entity_label = "Item" if self._report_name == "Item" else "Customer"
            dash.cell(row=chart_start_row, column=2, value=f"Top {entity_label}s by Sales")
            dash["B12"].font = Font(bold=True, size=12)

            for i, (name, value) in enumerate(self._top_performers[:8]):
                dash.cell(row=chart_start_row + 1 + i, column=2, value=name[:25])
                dash.cell(row=chart_start_row + 1 + i, column=3, value=value)

            if len(self._top_performers) > 0:
                bar = BarChart()
                bar.title = f"Top {entity_label}s"
                bar.type = "bar"
                bar.style = 10
                data = Reference(
                    dash,
                    min_col=3,
                    min_row=chart_start_row + 1,
                    max_row=chart_start_row + min(len(self._top_performers), 8)
                )
                cats = Reference(
                    dash,
                    min_col=2,
                    min_row=chart_start_row + 1,
                    max_row=chart_start_row + min(len(self._top_performers), 8)
                )
                bar.add_data(data, titles_from_data=False)
                bar.set_categories(cats)
                bar.width = 14
                bar.height = 10
                bar.y_axis.majorGridlines = None
                dash.add_chart(bar, "E12")

        # Sales Distribution Pie Chart
        if self._top_performers and len(self._top_performers) >= 3:
            chart_start_row = 24
            dash.cell(row=chart_start_row, column=2, value="Sales Distribution")
            dash["B24"].font = Font(bold=True, size=12)

            top_5 = self._top_performers[:5]
            other_total = sum(v for _, v in self._top_performers[5:])

            for i, (name, value) in enumerate(top_5):
                dash.cell(row=chart_start_row + 1 + i, column=2, value=name[:20])
                dash.cell(row=chart_start_row + 1 + i, column=3, value=value)

            if other_total > 0:
                dash.cell(row=chart_start_row + 6, column=2, value="Others")
                dash.cell(row=chart_start_row + 6, column=3, value=other_total)

            pie = PieChart()
            pie.title = "Sales Distribution"
            max_row = chart_start_row + 5 if other_total == 0 else chart_start_row + 6
            data = Reference(dash, min_col=3, min_row=chart_start_row + 1, max_row=max_row)
            cats = Reference(dash, min_col=2, min_row=chart_start_row + 1, max_row=max_row)
            pie.add_data(data, titles_from_data=False)
            pie.set_categories(cats)
            pie.width = 12
            pie.height = 8
            pie.dataLabels = DataLabelList()
            pie.dataLabels.showPercent = True
            dash.add_chart(pie, "E24")

        # Column widths
        for col_letter in "BCDEFGHI":
            dash.column_dimensions[col_letter].width = 16

    def _add_formulas_and_filters(self, df: pd.DataFrame) -> None:
        """Add autofilter and analysis section."""
        if self.sheet is None:
            return

        # Autofilter
        self.add_autofilter(row=self.DATA_START_ROW, col_start=1, col_end=4)

        # Data bars for sales column
        data_start = self.DATA_START_ROW + 1
        data_end = data_start + len(df) - 1
        cell_range = f"C{data_start}:C{data_end}"
        self.add_data_bars(cell_range=cell_range, color="5B9BD5")

    def export(self) -> None:
        """Execute the full export workflow with professional features."""
        self.logger.info("Starting sales report export")
        try:
            self.read_stdin()
            df = self.process_data()
            self.create_workbook()

            if self.sheet is None:
                self.logger.error("Failed to create worksheet")
                return

            self.sheet.title = f"Sales {self._report_name}"
            self._apply_title_section()

            # Header row
            header_row = self.DATA_START_ROW
            header_fill = PatternFill(
                start_color=self.HEADER_FILL_RGB,
                end_color=self.HEADER_FILL_RGB,
                fill_type="solid"
            )
            headings = self.get_headings()
            for col, val in enumerate(headings, start=1):
                cell = self.sheet.cell(row=header_row, column=col, value=val)
                cell.font = Font(bold=True, color="FFFFFF", size=11)
                cell.fill = header_fill
                cell.border = ExportStyle.HEADER_BORDER
                cell.alignment = Alignment(horizontal="center", vertical="center")

            # Write data (raw numeric values)
            data_start = header_row + 1
            is_item_report = self._report_name == "Item"

            for r_idx, row in df.iterrows():
                row_num = int(r_idx) + data_start

                if is_item_report:
                    self.sheet.cell(row=row_num, column=1, value=row.get("Item Name", ""))
                    self.sheet.cell(row=row_num, column=2, value=row.get("Quantity Sold", 0))
                    self.sheet.cell(row=row_num, column=3, value=row.get("_raw_amount", row.get("Amount", 0)))
                    self.sheet.cell(row=row_num, column=4, value=row.get("_raw_avg", row.get("Average Amount", 0)))
                else:
                    self.sheet.cell(row=row_num, column=1, value=row.get("Customer Name", ""))
                    self.sheet.cell(row=row_num, column=2, value=row.get("Invoice Count", 0))
                    self.sheet.cell(row=row_num, column=3, value=row.get("_raw_sales", row.get("Sales", 0)))
                    self.sheet.cell(row=row_num, column=4, value=row.get("_raw_sales_tax", row.get("Sales With Tax", 0)))

            # Apply styling
            self.auto_fit_columns(start_col=1, end_col=4, min_width=16, max_width=40)
            self.freeze_pane(f"A{data_start}")
            self._apply_row_styling(df)
            self._add_totals_row(df)
            self._add_formulas_and_filters(df)

            if len(df) > 0:
                data_end = data_start + len(df) - 1

                # Add outlier detection for sales column
                if len(df) > 5:
                    self.add_outlier_formatting(f"C{data_start}:C{data_end}", std_threshold=2.5)

                # Add statistical summary
                stats_row = data_start + len(df) + 3
                self.add_statistical_summary(
                    data_range=f"C{data_start}:C{data_end}",
                    summary_start_row=stats_row,
                    summary_col=1,
                    currency_symbol=self.data.get("currency_symbol", ""),
                )

            # Create dashboard
            self._create_dashboard()

            # Create pivot table analysis by item/customer
            if len(df) > 5:
                if self._report_name == "Item":
                    pivot_row_col = "Item Name"
                    pivot_value_col = "Amount"
                else:
                    pivot_row_col = "Customer Name"
                    pivot_value_col = "Sales"

                if pivot_row_col in df.columns and pivot_value_col in df.columns:
                    self.create_pivot_table_sheet(
                        data=df,
                        rows=[pivot_row_col],
                        values=pivot_value_col,
                        aggfunc="sum",
                        sheet_name="Sales Pivot"
                    )

            # Print settings
            self.setup_print_settings(col_end="D", row_end=data_start + len(df) + 5)

            # Output
            output_path = self.data.get("output_path")
            if output_path:
                self.output_to_file(output_path)
                print(output_path, file=sys.stderr)
            else:
                self.output_to_stdout()

            self.logger.info("Sales report export completed successfully")

        except (ValueError, KeyError) as e:
            self.logger.error("Export failed due to data error: %s", str(e))
            sys.exit(1)
        except IOError as e:
            self.logger.error("Export failed due to IO error: %s", str(e))
            sys.exit(2)


def main() -> None:
    """Entry point for sales report export."""
    exporter = SalesReportExporter()
    exporter.export()


if __name__ == "__main__":
    main()
