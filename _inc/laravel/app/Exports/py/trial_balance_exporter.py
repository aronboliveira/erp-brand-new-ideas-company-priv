#!/usr/bin/env python3
"""
Trial Balance Exporter Module — Professional Edition.

Handles Excel export for trial balance reports with:
- Debit/Credit columns with running totals
- KPI Dashboard with balance validation
- Debit vs Credit comparison charts
- Account type distribution pie chart
- Professional formatting with zebra stripes
- Excel formulas for balance validation
"""
import sys
from typing import Any, Dict, List, Tuple

import pandas as pd
from openpyxl.chart import BarChart, PieChart, Reference
from openpyxl.chart.label import DataLabelList
from openpyxl.styles import Alignment, Font, PatternFill

from base_exporter import BaseExporter, ExportStyle, parse_number, safe_get


class TrialBalanceExporter(BaseExporter):
    """
    Professional Trial Balance Exporter.

    Generates xlsx files with:
    - Account name, number, debit, credit columns
    - Balance validation (Debit = Credit check)
    - Account type distribution charts
    - KPI dashboard with key metrics
    - Professional formatting and zebra stripes
    - Excel formulas for dynamic totals
    """

    HEADINGS: List[str] = ["Account Name", "Account No", "Debit", "Credit"]
    COLUMN_WIDTHS: Dict[str, int] = {"A": 35, "B": 15, "C": 20, "D": 20}
    HEADER_FILL_COLOR: str = "1F2937"
    DATA_START_ROW: int = 6

    def __init__(self) -> None:
        """Initialize the Trial Balance exporter."""
        super().__init__("TrialBalanceExporter")
        self._company_name: str = ""
        self._start_date: str = ""
        self._end_date: str = ""
        # Metrics for dashboard
        self._total_debit: float = 0.0
        self._total_credit: float = 0.0
        self._account_count: int = 0
        self._type_totals: Dict[str, Tuple[float, float]] = {}
        self._is_balanced: bool = False

    def get_headings(self) -> List[str]:
        """Return column headings for trial balance export."""
        return self.HEADINGS

    def process_data(self) -> pd.DataFrame:
        """
        Process input JSON data into a DataFrame.

        Captures metrics for dashboard and charting.
        Stores raw numeric values for Excel formulas.

        Returns:
            DataFrame with processed trial balance data.
        """
        self.logger.info("Processing trial balance data")
        self._company_name = safe_get(self.data, "company_name", "Company")
        self._start_date = safe_get(self.data, "start_date", "")
        self._end_date = safe_get(self.data, "end_date", "")

        rows_data: Dict[str, Any] = self.data.get("rows", {})
        if not rows_data:
            self.logger.warning("No rows found in trial balance data")
            return pd.DataFrame(columns=self.HEADINGS + ["_row_type"])

        formatted: List[Dict[str, Any]] = []
        self._total_debit = 0.0
        self._total_credit = 0.0
        self._account_count = 0
        self._type_totals = {}

        for type_name, type_items in rows_data.items():
            if not isinstance(type_items, list):
                continue

            # Spacer row
            formatted.append({
                "Account Name": "",
                "Account No": "",
                "Debit": "",
                "Credit": "",
                "_raw_debit": None,
                "_raw_credit": None,
                "_row_type": "spacer",
            })

            # Type header row
            formatted.append({
                "Account Name": type_name,
                "Account No": "",
                "Debit": "",
                "Credit": "",
                "_raw_debit": None,
                "_raw_credit": None,
                "_row_type": "category",
            })

            type_debit = 0.0
            type_credit = 0.0

            for acct in type_items:
                if not isinstance(acct, dict):
                    continue

                debit = parse_number(acct.get("totalDebit", 0), default=0.0) or 0.0
                credit = parse_number(acct.get("totalCredit", 0), default=0.0) or 0.0

                self._total_debit += debit
                self._total_credit += credit
                type_debit += debit
                type_credit += credit
                self._account_count += 1

                formatted.append({
                    "Account Name": f"   {acct.get('name', '')}",
                    "Account No": acct.get("code", ""),
                    "Debit": debit if debit else "",
                    "Credit": credit if credit else "",
                    "_raw_debit": debit,
                    "_raw_credit": credit,
                    "_row_type": "data",
                })

            # Type subtotal
            formatted.append({
                "Account Name": f"Subtotal - {type_name}",
                "Account No": "",
                "Debit": type_debit,
                "Credit": type_credit,
                "_raw_debit": type_debit,
                "_raw_credit": type_credit,
                "_row_type": "subtotal",
            })

            self._type_totals[type_name] = (type_debit, type_credit)

        # Grand total row
        formatted.append({
            "Account Name": "",
            "Account No": "",
            "Debit": "",
            "Credit": "",
            "_raw_debit": None,
            "_raw_credit": None,
            "_row_type": "spacer",
        })
        formatted.append({
            "Account Name": "TOTAL",
            "Account No": "",
            "Debit": self._total_debit,
            "Credit": self._total_credit,
            "_raw_debit": self._total_debit,
            "_raw_credit": self._total_credit,
            "_row_type": "total",
        })

        # Check balance
        self._is_balanced = abs(self._total_debit - self._total_credit) < 0.01

        self.logger.info(
            "Processed %d accounts — Debit: %.2f, Credit: %.2f, Balanced: %s",
            self._account_count, self._total_debit, self._total_credit, self._is_balanced
        )
        return pd.DataFrame(formatted)

    def _apply_title_section(self) -> None:
        """Apply merged cells and titles to the header section."""
        if self.sheet is None:
            return

        merge_ranges = ["A1:D1", "A2:D2", "A3:D3", "A4:D4"]
        for merge_range in merge_ranges:
            self.sheet.merge_cells(merge_range)

        # Title
        title_cell = self.sheet["A1"]
        title_cell.value = f"Trial Balance - {self._company_name}"
        title_cell.font = Font(bold=True, size=16, color="1F2937")
        title_cell.alignment = Alignment(horizontal="center", vertical="center")

        # Subtitle
        self.sheet["A2"].value = f"Print Out Date: {pd.Timestamp.now().strftime('%Y-%m-%d %H:%M')}"
        self.sheet["A2"].font = Font(size=10, color="666666")
        self.sheet["A2"].alignment = Alignment(horizontal="center")

        # Date range
        self.sheet["A3"].value = f"Period: {self._start_date} to {self._end_date}"
        self.sheet["A3"].font = Font(size=10, color="666666")
        self.sheet["A3"].alignment = Alignment(horizontal="center")

        # Balance status
        currency_symbol = self.data.get("currency_symbol", "")
        if self._is_balanced:
            status = f"✓ BALANCED — Total: {currency_symbol}{self._total_debit:,.2f}"
            status_color = "70AD47"
        else:
            diff = abs(self._total_debit - self._total_credit)
            status = f"⚠ UNBALANCED — Difference: {currency_symbol}{diff:,.2f}"
            status_color = "C00000"

        self.sheet["A4"].value = status
        self.sheet["A4"].font = Font(bold=True, size=11, color=status_color)
        self.sheet["A4"].alignment = Alignment(horizontal="center")

    def _apply_row_styling(self, df: pd.DataFrame) -> None:
        """Apply professional styling based on row type."""
        if self.sheet is None:
            return

        data_start = self.DATA_START_ROW + 1
        currency_symbol = self.data.get("currency_symbol", "")
        data_row_idx = 0

        for idx, row in df.iterrows():
            row_num = int(idx) + data_start
            row_type = row.get("_row_type", "data")

            for col in range(1, 5):
                cell = self.sheet.cell(row=row_num, column=col)

                if row_type == "spacer":
                    continue

                cell.border = ExportStyle.THIN_BORDER

                if row_type == "category":
                    cell.font = Font(bold=True, size=11, color="1F2937")
                    cell.fill = PatternFill(
                        start_color="E5E7EB",
                        end_color="E5E7EB",
                        fill_type="solid"
                    )
                elif row_type == "subtotal":
                    cell.font = Font(bold=True, size=10, italic=True)
                    cell.fill = ExportStyle.SUBTOTAL_FILL
                elif row_type == "total":
                    cell.font = Font(bold=True, size=12)
                    cell.fill = ExportStyle.TOTAL_FILL
                    cell.border = ExportStyle.THICK_BORDER
                elif row_type == "data":
                    if data_row_idx % 2 == 0:
                        cell.fill = ExportStyle.ZEBRA_FILL_EVEN

                # Currency formatting for Debit/Credit
                if col in (3, 4) and isinstance(row.get("_raw_debit" if col == 3 else "_raw_credit"), (int, float)):
                    fmt = f'{currency_symbol}#,##0.00' if currency_symbol else '#,##0.00'
                    cell.number_format = fmt
                    cell.alignment = Alignment(horizontal="right", vertical="center")

            if row_type == "data":
                data_row_idx += 1

    def _create_dashboard(self) -> None:
        """Create KPI dashboard sheet with balance metrics and charts."""
        if self.workbook is None:
            return

        dash = self.workbook.create_sheet("Dashboard", 0)
        dash.sheet_view.showGridLines = False
        currency_symbol = self.data.get("currency_symbol", "")

        # Title
        dash.merge_cells("B2:H2")
        title_cell = dash["B2"]
        title_cell.value = f"Trial Balance Dashboard - {self._company_name}"
        title_cell.font = Font(bold=True, size=18, color="1F2937")
        title_cell.alignment = Alignment(horizontal="center")

        # Subtitle
        dash.merge_cells("B3:H3")
        dash["B3"].value = f"Period: {self._start_date} to {self._end_date}"
        dash["B3"].font = Font(size=11, color="666666")
        dash["B3"].alignment = Alignment(horizontal="center")

        # Balance status indicator
        dash.merge_cells("B4:H4")
        if self._is_balanced:
            dash["B4"].value = "✓ Trial Balance is BALANCED"
            dash["B4"].font = Font(bold=True, size=14, color="70AD47")
        else:
            diff = abs(self._total_debit - self._total_credit)
            dash["B4"].value = f"⚠ UNBALANCED — Difference: {currency_symbol}{diff:,.2f}"
            dash["B4"].font = Font(bold=True, size=14, color="C00000")
        dash["B4"].alignment = Alignment(horizontal="center")

        # KPI Cards
        kpis = [
            ("Total Debits", self._total_debit, "currency", None),
            ("Total Credits", self._total_credit, "currency", None),
            ("Account Types", len(self._type_totals), "number", None),
            ("Total Accounts", self._account_count, "number", None),
        ]

        row = 7
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

        # Debit vs Credit Bar Chart Data
        if self._type_totals:
            chart_start_row = 13
            dash.cell(row=chart_start_row, column=2, value="Debit vs Credit by Account Type")
            dash["B13"].font = Font(bold=True, size=12)

            # Headers
            dash.cell(row=chart_start_row + 1, column=2, value="Account Type")
            dash.cell(row=chart_start_row + 1, column=3, value="Debit")
            dash.cell(row=chart_start_row + 1, column=4, value="Credit")
            for c in range(2, 5):
                dash.cell(row=chart_start_row + 1, column=c).font = Font(bold=True)

            for i, (type_name, (debit, credit)) in enumerate(self._type_totals.items()):
                row_num = chart_start_row + 2 + i
                dash.cell(row=row_num, column=2, value=type_name[:25])
                dash.cell(row=row_num, column=3, value=debit)
                dash.cell(row=row_num, column=4, value=credit)

            if len(self._type_totals) > 0:
                bar = BarChart()
                bar.title = "Debit vs Credit"
                bar.type = "col"
                bar.grouping = "clustered"
                bar.style = 10

                data = Reference(
                    dash,
                    min_col=3,
                    max_col=4,
                    min_row=chart_start_row + 1,
                    max_row=chart_start_row + 1 + len(self._type_totals)
                )
                cats = Reference(
                    dash,
                    min_col=2,
                    min_row=chart_start_row + 2,
                    max_row=chart_start_row + 1 + len(self._type_totals)
                )
                bar.add_data(data, titles_from_data=True)
                bar.set_categories(cats)
                bar.width = 14
                bar.height = 10
                bar.y_axis.majorGridlines = None
                dash.add_chart(bar, "F13")

        # Account Type Distribution Pie Chart
        if self._type_totals:
            chart_start_row = 26
            dash.cell(row=chart_start_row, column=2, value="Account Type Distribution (by Total Activity)")
            dash["B26"].font = Font(bold=True, size=12)

            for i, (type_name, (debit, credit)) in enumerate(self._type_totals.items()):
                activity = debit + credit
                dash.cell(row=chart_start_row + 1 + i, column=2, value=type_name[:20])
                dash.cell(row=chart_start_row + 1 + i, column=3, value=activity)

            pie = PieChart()
            pie.title = "Activity Distribution"
            data = Reference(
                dash,
                min_col=3,
                min_row=chart_start_row + 1,
                max_row=chart_start_row + len(self._type_totals)
            )
            cats = Reference(
                dash,
                min_col=2,
                min_row=chart_start_row + 1,
                max_row=chart_start_row + len(self._type_totals)
            )
            pie.add_data(data, titles_from_data=False)
            pie.set_categories(cats)
            pie.width = 12
            pie.height = 8
            pie.dataLabels = DataLabelList()
            pie.dataLabels.showPercent = True
            dash.add_chart(pie, "F26")

        # Column widths
        for col_letter in "BCDEFGHI":
            dash.column_dimensions[col_letter].width = 16

    def _add_formulas_and_filters(self, df: pd.DataFrame) -> None:
        """Add autofilter, validation formulas, and conditional formatting."""
        if self.sheet is None:
            return

        data_start = self.DATA_START_ROW + 1
        data_end = data_start + len(df) - 1

        # Autofilter
        self.add_autofilter(row=self.DATA_START_ROW, col_start=1, col_end=4)

        # Balance check row
        check_row = data_end + 2
        self.sheet.cell(row=check_row, column=1, value="Balance Check")
        self.sheet["A" + str(check_row)].font = Font(bold=True)

        check_cell = self.sheet.cell(row=check_row, column=2)
        # Find the total row and reference its cells
        total_row = data_end
        check_cell.value = f'=IF(ABS(C{total_row}-D{total_row})<0.01,"✓ Balanced","⚠ Unbalanced")'
        check_cell.font = Font(bold=True)

        # Difference cell
        diff_cell = self.sheet.cell(row=check_row, column=3)
        diff_cell.value = f"=C{total_row}-D{total_row}"
        diff_cell.number_format = "#,##0.00"

        # Add positive/negative formatting for the difference
        cell_range = f"C{check_row}:C{check_row}"
        self.add_positive_negative_formatting(
            cell_range=cell_range,
            positive_color="70AD47",
            negative_color="C00000"
        )

        # Data bars for debit and credit columns
        debit_range = f"C{data_start}:C{data_end - 1}"
        credit_range = f"D{data_start}:D{data_end - 1}"
        self.add_data_bars(cell_range=debit_range, color="4472C4")
        self.add_data_bars(cell_range=credit_range, color="ED7D31")

    def export(self) -> None:
        """Execute the full export workflow with professional features."""
        self.logger.info("Starting trial balance export")
        try:
            self.read_stdin()
            df = self.process_data()
            self.create_workbook()

            if self.sheet is None:
                self.logger.error("Failed to create worksheet")
                return

            self.sheet.title = "Trial Balance"
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

            # Write data (raw numeric values) with grouping
            data_start = header_row + 1
            account_type_groups: dict[str, list[int]] = {}

            for r_idx, row in df.iterrows():
                row_num = int(r_idx) + data_start
                acct_name = row.get("Account Name", "")
                acct_no = row.get("Account No", "")

                self.sheet.cell(row=row_num, column=1, value=acct_name)
                self.sheet.cell(row=row_num, column=2, value=acct_no)

                # Use raw values for Debit/Credit
                debit = row.get("_raw_debit")
                credit = row.get("_raw_credit")
                if debit is not None:
                    self.sheet.cell(row=row_num, column=3, value=debit if debit else "")
                if credit is not None:
                    self.sheet.cell(row=row_num, column=4, value=credit if credit else "")

                # Track account types for drill-down grouping
                if acct_no and str(acct_no):
                    account_type = str(acct_no)[0]
                    if account_type not in account_type_groups:
                        account_type_groups[account_type] = []
                    account_type_groups[account_type].append(row_num)

            # Add drill-down grouping by account type (1xxx, 2xxx, etc.)
            for group_rows in account_type_groups.values():
                if len(group_rows) > 1:
                    self.add_row_grouping(
                        start_row=min(group_rows),
                        end_row=max(group_rows),
                        level=1,
                        hidden=False
                    )

            # Apply styling
            self.auto_fit_columns(start_col=1, end_col=4, min_width=16, max_width=40)
            self.freeze_pane(f"A{data_start}")
            self._apply_row_styling(df)
            self._add_formulas_and_filters(df)

            # Add outlier detection for debit and credit columns
            if len(df) > 5:
                data_end = data_start + len(df) - 1
                self.add_outlier_formatting(f"C{data_start}:C{data_end - 2}", std_threshold=2.5)
                self.add_outlier_formatting(f"D{data_start}:D{data_end - 2}", std_threshold=2.5)

            # Add statistical summaries for both debit and credit
            stats_row = data_start + len(df) + 6
            debit_end = self.add_statistical_summary(
                data_range=f"C{data_start}:C{data_start + len(df) - 2}",
                summary_start_row=stats_row,
                summary_col=1,
                currency_symbol=self.data.get("currency_symbol", ""),
            )
            self.sheet.cell(row=stats_row, column=1, value="Debit Statistics")

            credit_start = debit_end + 2
            self.add_statistical_summary(
                data_range=f"D{data_start}:D{data_start + len(df) - 2}",
                summary_start_row=credit_start,
                summary_col=1,
                currency_symbol=self.data.get("currency_symbol", ""),
            )
            self.sheet.cell(row=credit_start, column=1, value="Credit Statistics")

            # Create dashboard
            self._create_dashboard()

            # Print settings
            self.setup_print_settings(col_end="D", row_end=data_start + len(df) + 5)

            # Output
            output_path = self.data.get("output_path")
            if output_path:
                self.output_to_file(output_path)
                print(output_path, file=sys.stderr)
            else:
                self.output_to_stdout()

            self.logger.info("Trial balance export completed successfully")

        except (ValueError, KeyError) as e:
            self.logger.error("Export failed due to data error: %s", str(e))
            sys.exit(1)
        except IOError as e:
            self.logger.error("Export failed due to IO error: %s", str(e))
            sys.exit(2)


def main() -> None:
    """Entry point for trial balance export."""
    exporter = TrialBalanceExporter()
    exporter.export()


if __name__ == "__main__":
    main()
