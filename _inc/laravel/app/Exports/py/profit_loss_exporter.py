#!/usr/bin/env python3
"""
Profit Loss Exporter Module — Professional Edition.

Handles Excel export for profit and loss statement reports with:
- Income, COGS, Expenses breakdown with hierarchical structure
- KPI Dashboard with key profitability metrics
- Revenue vs Expense composition charts
- Gross/Net margin visualization
- Conditional formatting for variance analysis
- Dynamic formulas for trend analysis
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


class ProfitLossExporter(BaseExporter):
    """
    Professional Profit & Loss Statement Exporter.

    Generates xlsx files with:
    - Income, COGS, Expenses hierarchical structure
    - Gross and Net profit calculations with margins
    - Financial KPI dashboard (Revenue, Margins, Expense ratios)
    - Revenue vs Expense comparison charts
    - Zebra striping and professional formatting
    - Excel formulas for dynamic analysis
    """

    HEADINGS: List[str] = ["Account Name", "Account No", "Total"]
    COLUMN_WIDTHS: Dict[str, int] = {"A": 40, "B": 15, "C": 20}
    HEADER_FILL_RGB: str = "1F4E79"
    BOLD_TYPES: List[str] = [
        "Income", "Costs of Goods Sold", "Expenses",
        "Total Income", "Total Costs of Goods Sold", "Total Expenses",
        "Gross Profit", "Net Profit/Loss"
    ]
    DATA_START_ROW: int = 5

    def __init__(self) -> None:
        """Initialize the Profit Loss exporter."""
        super().__init__("ProfitLossExporter")
        self._company_name: str = ""
        self._start_date: str = ""
        self._end_date: str = ""
        self._total_income: float = 0.0
        self._total_cogs: float = 0.0
        self._total_expenses: float = 0.0
        self._gross_profit: float = 0.0
        self._net_profit: float = 0.0
        self._income_breakdown: List[Tuple[str, float]] = []
        self._expense_breakdown: List[Tuple[str, float]] = []

    def get_headings(self) -> List[str]:
        """Return column headings for profit loss export."""
        return self.HEADINGS

    def process_data(self) -> pd.DataFrame:
        """
        Process profit/loss data into flat rows.

        Captures income/expense breakdowns for charting.
        Calculates gross and net profit with margins.

        Returns:
            DataFrame with flattened P&L rows.
        """
        self.logger.info("Processing profit loss data")
        self._company_name = safe_get(self.data, "company_name", "Company")
        self._start_date = safe_get(self.data, "start_date", "")
        self._end_date = safe_get(self.data, "end_date", "")

        rows_data: List[Dict[str, Any]] = self.data.get("rows", [])
        if not rows_data:
            self.logger.warning("No rows found in profit loss data")
            return pd.DataFrame(columns=self.HEADINGS)

        formatted: List[Dict[str, Any]] = []
        self._total_income = 0.0
        self._total_cogs = 0.0
        self._total_expenses = 0.0
        self._income_breakdown = []
        self._expense_breakdown = []

        # Process Income and COGS
        for cat in rows_data:
            cat_type = cat.get("Type", "")
            accounts = cat.get("account", [])

            if cat_type in ("Income", "Costs of Goods Sold"):
                # Section header
                formatted.append({
                    "Account Name": "",
                    "Account No": "",
                    "Total": "",
                    "_row_type": "spacer",
                })
                formatted.append({
                    "Account Name": cat_type,
                    "Account No": "",
                    "Total": "",
                    "_row_type": "category",
                })

                for acct in accounts:
                    acct_name = acct.get("account_name", "")
                    acct_code = acct.get("account_code", "")
                    amt = abs(acct.get("netAmount", 0) or 0)

                    is_total = "total" in acct_name.lower()
                    formatted.append({
                        "Account Name": acct_name if is_total else f"   {acct_name}",
                        "Account No": acct_code,
                        "Total": amt,
                        "_row_type": "total" if is_total else "data",
                    })

                    if acct_name == "Total Income":
                        self._total_income = amt
                    elif acct_name == "Total Costs of Goods Sold":
                        self._total_cogs = amt
                    elif not is_total and cat_type == "Income":
                        self._income_breakdown.append((acct_name, amt))

        # Gross Profit
        self._gross_profit = self._total_income - self._total_cogs
        formatted.append({
            "Account Name": "",
            "Account No": "",
            "Total": "",
            "_row_type": "spacer",
        })
        formatted.append({
            "Account Name": "Gross Profit",
            "Account No": "",
            "Total": self._gross_profit,
            "_row_type": "gross_profit",
        })

        # Process Expenses
        for cat in rows_data:
            cat_type = cat.get("Type", "")
            accounts = cat.get("account", [])

            if cat_type == "Expenses":
                formatted.append({
                    "Account Name": "",
                    "Account No": "",
                    "Total": "",
                    "_row_type": "spacer",
                })
                formatted.append({
                    "Account Name": "Expenses",
                    "Account No": "",
                    "Total": "",
                    "_row_type": "category",
                })

                for acct in accounts:
                    acct_name = acct.get("account_name", "")
                    acct_code = acct.get("account_code", "")
                    amt = abs(acct.get("netAmount", 0) or 0)

                    is_total = acct_name == "Total Expenses"
                    if is_total:
                        self._total_expenses = amt

                    formatted.append({
                        "Account Name": acct_name if is_total else f"   {acct_name}",
                        "Account No": acct_code,
                        "Total": amt,
                        "_row_type": "total" if is_total else "data",
                    })

                    if not is_total:
                        self._expense_breakdown.append((acct_name, amt))

        # Net Profit/Loss
        self._net_profit = self._gross_profit - self._total_expenses
        formatted.append({
            "Account Name": "",
            "Account No": "",
            "Total": "",
            "_row_type": "spacer",
        })
        formatted.append({
            "Account Name": "Net Profit/Loss",
            "Account No": "",
            "Total": self._net_profit,
            "_row_type": "net_profit",
        })

        self.logger.info(
            "Processed %d P&L rows — Income: %.2f, COGS: %.2f, Exp: %.2f, Net: %.2f",
            len(formatted), self._total_income, self._total_cogs,
            self._total_expenses, self._net_profit
        )
        return pd.DataFrame(formatted)

    def _apply_title_section(self) -> None:
        """Apply merged cells and titles to the header section."""
        if self.sheet is None:
            return

        merge_ranges = ["A1:C1", "A2:C2", "A3:C3"]
        for merge_range in merge_ranges:
            self.sheet.merge_cells(merge_range)

        title_cell = self.sheet["A1"]
        title_cell.value = f"Profit & Loss Statement - {self._company_name}"
        title_cell.font = Font(bold=True, size=16, color="1F4E79")
        title_cell.alignment = Alignment(horizontal="center", vertical="center")

        self.sheet["A2"].value = f"Print Out Date: {pd.Timestamp.now().strftime('%Y-%m-%d %H:%M')}"
        self.sheet["A2"].font = Font(size=10, color="666666")
        self.sheet["A2"].alignment = Alignment(horizontal="center")

        self.sheet["A3"].value = f"Period: {self._start_date} to {self._end_date}"
        self.sheet["A3"].font = Font(size=10, color="666666")
        self.sheet["A3"].alignment = Alignment(horizontal="center")

    def _apply_row_styling(self, df: pd.DataFrame) -> None:
        """Apply professional styling based on row type."""
        if self.sheet is None:
            return

        data_start = self.DATA_START_ROW + 1
        currency_symbol = self.data.get("currency_symbol", "")

        for idx, row in df.iterrows():
            row_num = int(idx) + data_start
            row_type = row.get("_row_type", "data")

            for col in range(1, 4):
                cell = self.sheet.cell(row=row_num, column=col)

                if row_type == "spacer":
                    continue

                cell.border = ExportStyle.THIN_BORDER

                if row_type == "category":
                    cell.font = Font(bold=True, size=12, color="1F4E79")
                    cell.fill = PatternFill(
                        start_color="E8F4FD",
                        end_color="E8F4FD",
                        fill_type="solid"
                    )
                elif row_type == "total":
                    cell.font = Font(bold=True, size=10)
                    cell.fill = ExportStyle.SUBTOTAL_FILL
                elif row_type == "gross_profit":
                    cell.font = Font(bold=True, size=12, color="1F4E79")
                    cell.fill = ExportStyle.SUBTOTAL_FILL
                    cell.border = ExportStyle.THICK_BORDER
                elif row_type == "net_profit":
                    cell.font = Font(bold=True, size=14)
                    cell.fill = ExportStyle.TOTAL_FILL
                    cell.border = ExportStyle.THICK_BORDER
                    # Color based on profit/loss
                    if self._net_profit >= 0:
                        cell.font = Font(bold=True, size=14, color="70AD47")
                    else:
                        cell.font = Font(bold=True, size=14, color="C00000")
                elif row_type == "data":
                    if int(idx) % 2 == 0:
                        cell.fill = ExportStyle.ZEBRA_FILL_EVEN

                # Currency formatting for Total column
                if col == 3 and isinstance(row.get("Total"), (int, float)):
                    fmt = f'{currency_symbol}#,##0.00' if currency_symbol else '#,##0.00'
                    cell.number_format = fmt
                    cell.alignment = Alignment(horizontal="right", vertical="center")

    def _create_dashboard(self) -> None:
        """Create KPI dashboard sheet with profitability metrics and charts."""
        if self.workbook is None:
            return

        dash = self.workbook.create_sheet("Dashboard", 0)
        dash.sheet_view.showGridLines = False
        currency_symbol = self.data.get("currency_symbol", "")

        # Title
        dash.merge_cells("B2:H2")
        title_cell = dash["B2"]
        title_cell.value = f"Profit & Loss Dashboard - {self._company_name}"
        title_cell.font = Font(bold=True, size=18, color="1F4E79")
        title_cell.alignment = Alignment(horizontal="center")

        # Subtitle
        dash.merge_cells("B3:H3")
        dash["B3"].value = f"Period: {self._start_date} to {self._end_date}"
        dash["B3"].font = Font(size=11, color="666666")
        dash["B3"].alignment = Alignment(horizontal="center")

        # Calculate margins
        gross_margin = (self._gross_profit / self._total_income * 100) if self._total_income else 0
        net_margin = (self._net_profit / self._total_income * 100) if self._total_income else 0
        expense_ratio = (self._total_expenses / self._total_income * 100) if self._total_income else 0

        # KPI Cards
        kpis = [
            ("Total Revenue", self._total_income, "currency", None),
            ("Cost of Goods Sold", self._total_cogs, "currency", None),
            ("Gross Profit", self._gross_profit, "currency",
             "up" if self._gross_profit > 0 else "down"),
            ("Operating Expenses", self._total_expenses, "currency", None),
            ("Net Profit", self._net_profit, "currency",
             "up" if self._net_profit > 0 else "down"),
            ("Gross Margin", gross_margin / 100, "percent",
             "up" if gross_margin > 30 else "flat"),
            ("Net Margin", net_margin / 100, "percent",
             "up" if net_margin > 10 else ("down" if net_margin < 0 else "flat")),
            ("Expense Ratio", expense_ratio / 100, "percent",
             "down" if expense_ratio < 50 else "up"),
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

        # Revenue Breakdown Chart Data
        if self._income_breakdown:
            chart_start_row = 16
            dash.cell(row=chart_start_row, column=2, value="Revenue Breakdown")
            dash["B16"].font = Font(bold=True, size=12)

            for i, (name, value) in enumerate(self._income_breakdown[:5]):
                dash.cell(row=chart_start_row + 1 + i, column=2, value=name[:25])
                dash.cell(row=chart_start_row + 1 + i, column=3, value=value)

            if len(self._income_breakdown) > 0:
                pie = PieChart()
                pie.title = "Revenue Sources"
                data = Reference(
                    dash,
                    min_col=3,
                    min_row=chart_start_row + 1,
                    max_row=chart_start_row + min(len(self._income_breakdown), 5)
                )
                cats = Reference(
                    dash,
                    min_col=2,
                    min_row=chart_start_row + 1,
                    max_row=chart_start_row + min(len(self._income_breakdown), 5)
                )
                pie.add_data(data, titles_from_data=False)
                pie.set_categories(cats)
                pie.width = 12
                pie.height = 8
                pie.dataLabels = DataLabelList()
                pie.dataLabels.showPercent = True
                dash.add_chart(pie, "E16")

        # Expense Breakdown Chart
        if self._expense_breakdown:
            chart_start_row = 26
            dash.cell(row=chart_start_row, column=2, value="Expense Breakdown")
            dash["B26"].font = Font(bold=True, size=12)

            sorted_expenses = sorted(self._expense_breakdown, key=lambda x: x[1], reverse=True)
            for i, (name, value) in enumerate(sorted_expenses[:6]):
                dash.cell(row=chart_start_row + 1 + i, column=2, value=name[:25])
                dash.cell(row=chart_start_row + 1 + i, column=3, value=value)

            if len(sorted_expenses) > 0:
                bar = BarChart()
                bar.title = "Top Expenses"
                bar.type = "bar"
                bar.style = 10
                data = Reference(
                    dash,
                    min_col=3,
                    min_row=chart_start_row + 1,
                    max_row=chart_start_row + min(len(sorted_expenses), 6)
                )
                cats = Reference(
                    dash,
                    min_col=2,
                    min_row=chart_start_row + 1,
                    max_row=chart_start_row + min(len(sorted_expenses), 6)
                )
                bar.add_data(data, titles_from_data=False)
                bar.set_categories(cats)
                bar.width = 14
                bar.height = 8
                bar.y_axis.majorGridlines = None
                dash.add_chart(bar, "E26")

        # Column widths
        for col_letter in "BCDEFGHI":
            dash.column_dimensions[col_letter].width = 16

    def _add_analysis_formulas(self, df: pd.DataFrame) -> None:
        """Add analysis formulas and conditional formatting."""
        if self.sheet is None:
            return

        data_start = self.DATA_START_ROW + 1
        data_end = data_start + len(df) - 1

        # Add autofilter
        self.add_autofilter(row=self.DATA_START_ROW, col_start=1, col_end=3)

        # Margin analysis section
        analysis_row = data_end + 3
        currency_symbol = self.data.get("currency_symbol", "")

        self.sheet.cell(row=analysis_row, column=1, value="Key Metrics")
        self.sheet["A" + str(analysis_row)].font = Font(bold=True, size=12, color="1F4E79")

        metrics = [
            ("Gross Margin", self._gross_profit / self._total_income if self._total_income else 0),
            ("Net Margin", self._net_profit / self._total_income if self._total_income else 0),
            ("COGS %", self._total_cogs / self._total_income if self._total_income else 0),
            ("OpEx %", self._total_expenses / self._total_income if self._total_income else 0),
        ]

        for i, (label, value) in enumerate(metrics):
            row = analysis_row + 1 + i
            self.sheet.cell(row=row, column=1, value=label)
            cell = self.sheet.cell(row=row, column=2, value=value)
            cell.number_format = "0.0%"
            cell.alignment = Alignment(horizontal="right")

            # Trend indicator
            trend_cell = self.sheet.cell(row=row, column=3)
            if "Margin" in label:
                if value > 0.2:
                    trend_cell.value = "▲"
                    trend_cell.font = Font(color="70AD47")
                elif value < 0.1:
                    trend_cell.value = "▼"
                    trend_cell.font = Font(color="C00000")
                else:
                    trend_cell.value = "●"
                    trend_cell.font = Font(color="FFC000")

    def export(self) -> None:
        """Execute the full export workflow with professional features."""
        self.logger.info("Starting profit loss export")
        try:
            self.read_stdin()
            df = self.process_data()
            self.create_workbook()

            if self.sheet is None:
                self.logger.error("Failed to create worksheet")
                return

            self.sheet.title = "Profit & Loss"
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
            display_cols = ["Account Name", "Account No", "Total"]
            for r_idx, row in df.iterrows():
                for c_idx, col_name in enumerate(display_cols, start=1):
                    value = row.get(col_name, "")
                    self.sheet.cell(row=int(r_idx) + data_start, column=c_idx, value=value)

            # Apply styling
            self.auto_fit_columns(start_col=1, end_col=3, min_width=18, max_width=45)
            self.freeze_pane(f"A{data_start}")
            self._apply_row_styling(df)
            self._add_analysis_formulas(df)
            
            # Add outlier detection for amounts
            if len(df) > 5:
                data_end = data_start + len(df) - 1
                amount_range = f"C{data_start}:C{data_end - 2}"  # Exclude totals
                self.add_outlier_formatting(amount_range, std_threshold=2.0)
            
            # Add statistical summary
            stats_row = data_start + len(df) + 15
            self.add_statistical_summary(
                data_range=f"C{data_start}:C{data_start + len(df) - 3}",
                summary_start_row=stats_row,
                summary_col=1,
                currency_symbol=self.data.get("currency_symbol", ""),
            )

            # Create dashboard
            self._create_dashboard()
            
            # Create variance analysis sheet if budget data available
            budget_data = self.data.get("budget_data")
            if budget_data and len(budget_data) > 0:
                budget_df = pd.DataFrame(budget_data)
                self.create_variance_analysis_sheet(
                    actual_data=df,
                    budget_data=budget_df,
                    sheet_name="Variance Analysis",
                    category_col="Account Name",
                    amount_col="Total"
                )

            # Print settings
            self.setup_print_settings(
                col_end="C",
                row_end=data_start + len(df) + 10,
                landscape=False
            )

            # Output
            output_path = self.data.get("output_path")
            if output_path:
                self.output_to_file(output_path)
                print(output_path, file=sys.stderr)
            else:
                self.output_to_stdout()

            self.logger.info("Profit loss export completed successfully")

        except (ValueError, KeyError) as e:
            self.logger.error("Export failed due to data error: %s", str(e))
            sys.exit(1)
        except IOError as e:
            self.logger.error("Export failed due to IO error: %s", str(e))
            sys.exit(2)


def main() -> None:
    """Entry point for profit loss export."""
    exporter = ProfitLossExporter()
    exporter.export()


if __name__ == "__main__":
    main()
