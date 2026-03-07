#!/usr/bin/env python3
"""
Balance Sheet Exporter Module — Professional Edition.

Handles Excel export for balance sheet reports with:
- Hierarchical account grouping and totals
- KPI Dashboard with key financial metrics
- Asset vs Liability composition charts
- Conditional formatting for variance analysis
- Dynamic formulas for account queries
- Print-ready professional formatting
"""
import sys
from typing import Any, Dict, List, Tuple

import pandas as pd
from openpyxl.chart import PieChart, Reference
from openpyxl.chart.label import DataLabelList
from openpyxl.styles import Alignment, Font, PatternFill

from base_exporter import BaseExporter, ExportStyle, parse_number, safe_get


class BalanceSheetExporter(BaseExporter):
    """
    Professional Balance Sheet Exporter.

    Generates xlsx files with:
    - Hierarchical account structure with category totals
    - Financial KPI dashboard (Total Assets, Liabilities, Equity, Ratios)
    - Composition pie charts for assets and liabilities
    - Zebra striping and professional formatting
    - Excel formulas for dynamic calculations
    - Autofilter for easy data analysis
    """

    HEADINGS: List[str] = ["Account", "Account No", "Total"]
    COLUMN_WIDTHS: Dict[str, int] = {"A": 40, "B": 15, "C": 20}
    BOLD_LABELS: List[str] = [
        "Liabilities & Equity",
        "Total Assets",
        "Total Equity",
        "Total Liabilities & Equity",
    ]
    HEADER_FILL_RGB: str = "1F4E79"
    MERGE_RANGES: List[str] = ["A1:C1", "A2:C2", "A3:C3"]
    DATA_START_ROW: int = 5

    def __init__(self) -> None:
        """Initialize the Balance Sheet exporter."""
        super().__init__("BalanceSheetExporter")
        self._company_name: str = ""
        self._start_date: str = ""
        self._end_date: str = ""
        self._total_assets: float = 0.0
        self._total_liabilities: float = 0.0
        self._total_equity: float = 0.0
        self._asset_breakdown: List[Tuple[str, float]] = []
        self._liability_breakdown: List[Tuple[str, float]] = []

    def get_headings(self) -> List[str]:
        """Return column headings for balance sheet export."""
        return self.HEADINGS

    def process_data(self) -> pd.DataFrame:
        """
        Process hierarchical balance sheet data into flat rows.

        Captures category breakdowns for chart generation.
        Calculates totals for KPI dashboard.

        Returns:
            DataFrame with flattened account rows.
        """
        self.logger.info("Processing balance sheet data")
        self._company_name = safe_get(self.data, "company_name", "Company")
        self._start_date = safe_get(self.data, "start_date", "")
        self._end_date = safe_get(self.data, "end_date", "")

        rows_data: Dict[str, Any] = self.data.get("rows", {})
        if not rows_data:
            self.logger.warning("No rows found in balance sheet data")
            return pd.DataFrame(columns=self.HEADINGS)

        formatted: List[Dict[str, Any]] = []
        self._total_assets = 0.0
        self._total_liabilities = 0.0
        self._total_equity = 0.0
        self._asset_breakdown = []
        self._liability_breakdown = []
        li_eq_started: bool = False

        for category, subs in rows_data.items():
            if not isinstance(subs, list):
                self.logger.warning("Category %s has invalid subs type", category)
                continue

            cat_total: float = 0.0
            li_set: bool = False
            as_set: bool = False

            # First pass: check if category has data
            for sub in subs:
                if not isinstance(sub, dict):
                    continue
                accounts = sub.get("account", [])
                for acct in accounts:
                    net_amount = acct.get("netAmount")
                    parsed_amount = parse_number(net_amount, default=None)
                    if parsed_amount is not None:
                        if category in ("Liabilities", "Equity"):
                            if not li_eq_started:
                                formatted.append({
                                    "Account": "Liabilities & Equity",
                                    "Account No": "",
                                    "Total": "",
                                    "_row_type": "header",
                                })
                                li_eq_started = True
                            if not li_set:
                                formatted.append({
                                    "Account": f"  {category}",
                                    "Account No": "",
                                    "Total": "",
                                    "_row_type": "category",
                                })
                                li_set = True
                        elif not as_set:
                            formatted.append({
                                "Account": category,
                                "Account No": "",
                                "Total": "",
                                "_row_type": "category",
                            })
                            as_set = True
                        break

            # Second pass: process accounts
            for sub in subs:
                if not isinstance(sub, dict):
                    continue
                sub_type = sub.get("subType", "")
                accounts = sub.get("account", [])
                sub_total: float = 0.0
                sub_header_added: bool = False

                for acct in accounts:
                    net_amount = acct.get("netAmount")
                    amount = parse_number(net_amount, default=None)
                    if amount is not None:
                        if not sub_header_added:
                            formatted.append({
                                "Account": f"    {sub_type}",
                                "Account No": "",
                                "Total": "",
                                "_row_type": "subcategory",
                            })
                            sub_header_added = True

                        formatted.append({
                            "Account": f"       {acct.get('account_name', '')}",
                            "Account No": acct.get("account_no", ""),
                            "Total": amount,
                            "_row_type": "data",
                        })
                        sub_total += amount

                if sub_total != 0:
                    formatted.append({
                        "Account": f"    Total {sub_type}",
                        "Account No": "",
                        "Total": sub_total,
                        "_row_type": "subtotal",
                    })
                    # Track for charts
                    if category == "Assets":
                        self._asset_breakdown.append((sub_type, sub_total))
                    elif category == "Liabilities":
                        self._liability_breakdown.append((sub_type, sub_total))

                cat_total += sub_total

            if cat_total != 0:
                if category in ("Liabilities", "Equity"):
                    formatted.append({
                        "Account": f"  Total {category}",
                        "Account No": "",
                        "Total": cat_total,
                        "_row_type": "total",
                    })
                    if category == "Liabilities":
                        self._total_liabilities = cat_total
                    else:
                        self._total_equity = cat_total
                else:
                    formatted.append({
                        "Account": f"Total {category}",
                        "Account No": "",
                        "Total": cat_total,
                        "_row_type": "total",
                    })
                    self._total_assets = cat_total

        # Grand total for Liabilities & Equity
        grand_total = self._total_liabilities + self._total_equity
        formatted.append({
            "Account": "Total Liabilities & Equity",
            "Account No": "",
            "Total": grand_total,
            "_row_type": "grand_total",
        })

        self.logger.info(
            "Processed %d balance sheet rows — Assets: %.2f, Liab: %.2f, Equity: %.2f",
            len(formatted), self._total_assets, self._total_liabilities, self._total_equity
        )
        return pd.DataFrame(formatted)

    def _apply_title_section(self) -> None:
        """Apply merged cells and titles to the header section."""
        if self.sheet is None:
            return

        for merge_range in self.MERGE_RANGES:
            self.sheet.merge_cells(merge_range)

        # Company title
        title_cell = self.sheet["A1"]
        title_cell.value = f"Balance Sheet - {self._company_name}"
        title_cell.font = Font(bold=True, size=16, color="1F4E79")
        title_cell.alignment = Alignment(horizontal="center", vertical="center")

        # Print date
        self.sheet["A2"].value = f"Print Out Date: {pd.Timestamp.now().strftime('%Y-%m-%d %H:%M')}"
        self.sheet["A2"].font = Font(size=10, color="666666")
        self.sheet["A2"].alignment = Alignment(horizontal="center")

        # Report period
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
                cell.border = ExportStyle.THIN_BORDER

                if row_type in ("header", "grand_total"):
                    cell.font = Font(bold=True, size=12, color="1F4E79")
                    cell.fill = ExportStyle.TOTAL_FILL
                    cell.border = ExportStyle.THICK_BORDER
                elif row_type == "category":
                    cell.font = Font(bold=True, size=11)
                    cell.fill = PatternFill(
                        start_color="E8F4FD",
                        end_color="E8F4FD",
                        fill_type="solid"
                    )
                elif row_type == "subcategory":
                    cell.font = Font(bold=True, size=10, color="4472C4")
                elif row_type == "subtotal":
                    cell.font = Font(bold=True, size=10)
                    cell.fill = ExportStyle.SUBTOTAL_FILL
                elif row_type == "total":
                    cell.font = Font(bold=True, size=11)
                    cell.fill = ExportStyle.SUBTOTAL_FILL
                elif row_type == "data":
                    if int(idx) % 2 == 0:
                        cell.fill = ExportStyle.ZEBRA_FILL_EVEN

                # Currency formatting for Total column
                if col == 3 and isinstance(row.get("Total"), (int, float)):
                    fmt = f'{currency_symbol}#,##0.00' if currency_symbol else '#,##0.00'
                    cell.number_format = fmt
                    cell.alignment = Alignment(horizontal="right", vertical="center")

    def _create_dashboard(self) -> None:
        """Create KPI dashboard sheet with financial metrics and charts."""
        if self.workbook is None:
            return

        dash = self.workbook.create_sheet("Dashboard", 0)
        dash.sheet_view.showGridLines = False
        currency_symbol = self.data.get("currency_symbol", "")

        # Title
        dash.merge_cells("B2:G2")
        title_cell = dash["B2"]
        title_cell.value = f"Balance Sheet Dashboard - {self._company_name}"
        title_cell.font = Font(bold=True, size=18, color="1F4E79")
        title_cell.alignment = Alignment(horizontal="center")

        # Subtitle
        dash.merge_cells("B3:G3")
        dash["B3"].value = f"As of {self._end_date}"
        dash["B3"].font = Font(size=11, color="666666")
        dash["B3"].alignment = Alignment(horizontal="center")

        # KPI Cards
        kpis = [
            ("Total Assets", self._total_assets, "currency"),
            ("Total Liabilities", self._total_liabilities, "currency"),
            ("Total Equity", self._total_equity, "currency"),
            ("Debt-to-Equity Ratio",
             self._total_liabilities / self._total_equity if self._total_equity != 0 else 0,
             "ratio"),
            ("Equity Ratio",
             self._total_equity / self._total_assets if self._total_assets != 0 else 0,
             "percent"),
        ]

        row = 6
        col = 2
        for label, value, fmt_type in kpis:
            label_cell = dash.cell(row=row, column=col, value=label)
            label_cell.font = Font(size=9, color="7F7F7F")

            value_cell = dash.cell(row=row + 1, column=col)
            value_cell.value = value
            if fmt_type == "currency":
                value_cell.number_format = f'{currency_symbol}#,##0.00'
            elif fmt_type == "percent":
                value_cell.number_format = "0.0%"
            else:
                value_cell.number_format = "0.00"
            value_cell.font = Font(bold=True, size=16, color="1F4E79")

            # Trend indicator for D/E ratio
            if label == "Debt-to-Equity Ratio":
                trend_cell = dash.cell(row=row + 1, column=col + 1)
                if value < 1:
                    trend_cell.value = "▲ Good"
                    trend_cell.font = Font(color="70AD47", size=10)
                else:
                    trend_cell.value = "▼ High"
                    trend_cell.font = Font(color="C00000", size=10)

            col += 2
            if col > 8:
                col = 2
                row += 4

        # Asset Composition Chart
        if self._asset_breakdown:
            chart_start_row = 14
            dash.cell(row=chart_start_row, column=2, value="Asset Composition")
            dash["B14"].font = Font(bold=True, size=12)

            for i, (name, value) in enumerate(self._asset_breakdown[:5]):
                dash.cell(row=chart_start_row + 1 + i, column=2, value=name)
                dash.cell(row=chart_start_row + 1 + i, column=3, value=value)

            if len(self._asset_breakdown) > 0:
                pie = PieChart()
                pie.title = "Asset Distribution"
                data = Reference(
                    dash,
                    min_col=3,
                    min_row=chart_start_row + 1,
                    max_row=chart_start_row + len(self._asset_breakdown[:5])
                )
                cats = Reference(
                    dash,
                    min_col=2,
                    min_row=chart_start_row + 1,
                    max_row=chart_start_row + len(self._asset_breakdown[:5])
                )
                pie.add_data(data, titles_from_data=False)
                pie.set_categories(cats)
                pie.width = 12
                pie.height = 8
                pie.dataLabels = DataLabelList()
                pie.dataLabels.showPercent = True
                dash.add_chart(pie, "E14")

        # Column widths
        for col_letter in "BCDEFGH":
            dash.column_dimensions[col_letter].width = 18

    def _add_formulas_and_filters(self, df: pd.DataFrame) -> None:
        """Add dynamic formulas and autofilter for analysis."""
        if self.sheet is None:
            return

        data_start = self.DATA_START_ROW + 1
        data_end = data_start + len(df) - 1

        # Add autofilter
        self.add_autofilter(row=self.DATA_START_ROW, col_start=1, col_end=3)

        # Balance check formula
        check_row = data_end + 2
        self.sheet.cell(row=check_row, column=1, value="Balance Check:")
        self.sheet["A" + str(check_row)].font = Font(bold=True, size=10)

        balance_diff = self._total_assets - (self._total_liabilities + self._total_equity)
        check_cell = self.sheet.cell(row=check_row, column=2, value=balance_diff)
        check_cell.number_format = '#,##0.00'

        if abs(balance_diff) < 0.01:
            check_cell.font = Font(bold=True, color="70AD47")
            self.sheet.cell(row=check_row, column=3, value="✓ Balanced")
            self.sheet["C" + str(check_row)].font = Font(color="70AD47")
        else:
            check_cell.font = Font(bold=True, color="C00000")
            self.sheet.cell(row=check_row, column=3, value="✗ Imbalanced")
            self.sheet["C" + str(check_row)].font = Font(color="C00000")

    def export(self) -> None:
        """Execute the full export workflow with professional features."""
        self.logger.info("Starting balance sheet export")
        try:
            self.read_stdin()
            df = self.process_data()
            self.create_workbook()

            if self.sheet is None:
                self.logger.error("Failed to create worksheet")
                return

            self.sheet.title = "Balance Sheet"
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
            display_cols = ["Account", "Account No", "Total"]
            for r_idx, row in df.iterrows():
                for c_idx, col_name in enumerate(display_cols, start=1):
                    value = row.get(col_name, "")
                    self.sheet.cell(row=int(r_idx) + data_start, column=c_idx, value=value)

            # Apply styling
            self.auto_fit_columns(start_col=1, end_col=3, min_width=16, max_width=40, padding=2)
            self.freeze_pane(f"A{data_start}")
            self._apply_row_styling(df)
            self._add_formulas_and_filters(df)

            # Add outlier detection for Total column
            data_end = data_start + len(df) - 1
            self.add_outlier_formatting(
                cell_range=f"C{data_start}:C{data_end}",
                std_threshold=2.5,
                outlier_color="FFC7CE",
                outlier_font_color="9C0006"
            )

            # Add statistical summary for Total column
            summary_start = data_end + 5
            self.add_statistical_summary(
                data_range=f"C{data_start}:C{data_end}",
                summary_start_row=summary_start,
                summary_col=1,
                currency_symbol=self.data.get("currency_symbol", "")
            )

            # Create dashboard
            self._create_dashboard()

            # Print settings
            self.setup_print_settings(
                col_end="C",
                row_end=data_start + len(df) + 5,
                landscape=False
            )

            # Output
            output_path = self.data.get("output_path")
            if output_path:
                self.output_to_file(output_path)
                print(output_path, file=sys.stderr)
            else:
                self.output_to_stdout()

            self.logger.info("Balance sheet export completed successfully")

        except (ValueError, KeyError) as e:
            self.logger.error("Export failed due to data error: %s", str(e))
            sys.exit(1)
        except IOError as e:
            self.logger.error("Export failed due to IO error: %s", str(e))
            sys.exit(2)


def main() -> None:
    """Entry point for balance sheet export."""
    exporter = BalanceSheetExporter()
    exporter.export()


if __name__ == "__main__":
    main()
