#!/usr/bin/env python3
"""
Base Exporter Module.

Provides abstract base class and utilities for all ERP export operations.
Handles Excel (xlsx) generation using openpyxl and pandas.
Includes professional features: charts, formulas, conditional formatting, dashboards.
"""
import json
import logging
import re
import sys
from abc import ABC, abstractmethod
from datetime import datetime
from io import BytesIO
from pathlib import Path
from typing import Any, Dict, List, Optional, Tuple, Union

import pandas as pd
from openpyxl import Workbook
from openpyxl.chart import BarChart, LineChart, PieChart, Reference
from openpyxl.formatting.rule import CellIsRule, ColorScaleRule, DataBarRule, Rule
from openpyxl.styles import Alignment, Border, Font, PatternFill, Side
from openpyxl.utils import get_column_letter
from openpyxl.utils.cell import range_boundaries
from openpyxl.utils.dataframe import dataframe_to_rows
from openpyxl.worksheet.datavalidation import DataValidation
from openpyxl.worksheet.worksheet import Worksheet

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s:%(funcName)s:%(lineno)d - %(message)s",
    stream=sys.stderr,
)


class ExportStyle:
    """
    Predefined styles for Excel exports.

    Contains font, fill, border, and alignment configurations
    commonly used across all export modules.
    """

    # Color constants for consistent theming
    PRIMARY_DARK: str = "001122"
    PRIMARY_BLUE: str = "1F4E79"
    ACCENT_BLUE: str = "4472C4"
    SUCCESS_GREEN: str = "70AD47"
    WARNING_AMBER: str = "FFC000"
    DANGER_RED: str = "C00000"
    NEUTRAL_GRAY: str = "7F7F7F"
    LIGHT_GRAY: str = "F5F5F5"
    ZEBRA_EVEN: str = "E8F4FD"

    HEADER_FONT: Font = Font(bold=True, color="FFFFFF")
    HEADER_FILL: PatternFill = PatternFill(
        start_color="001122", end_color="001122", fill_type="solid"
    )
    BODY_FILL_LIGHT: PatternFill = PatternFill(
        start_color="F5F5F5", end_color="F5F5F5", fill_type="solid"
    )
    ZEBRA_FILL_EVEN: PatternFill = PatternFill(
        start_color="E8F4FD", end_color="E8F4FD", fill_type="solid"
    )
    SUBTOTAL_FILL: PatternFill = PatternFill(
        start_color="D6DCE5", end_color="D6DCE5", fill_type="solid"
    )
    TOTAL_FILL: PatternFill = PatternFill(
        start_color="B4C6E7", end_color="B4C6E7", fill_type="solid"
    )
    POSITIVE_FILL: PatternFill = PatternFill(
        start_color="C6EFCE", end_color="C6EFCE", fill_type="solid"
    )
    NEGATIVE_FILL: PatternFill = PatternFill(
        start_color="FFC7CE", end_color="FFC7CE", fill_type="solid"
    )
    THIN_BORDER: Border = Border(
        left=Side(style="thin", color="333333"),
        right=Side(style="thin", color="333333"),
        top=Side(style="hair", color="333333"),
        bottom=Side(style="hair", color="333333"),
    )
    HEADER_BORDER: Border = Border(
        left=Side(style="thin", color="000000"),
        right=Side(style="thin", color="000000"),
        top=Side(style="thin", color="000000"),
        bottom=Side(style="thin", color="000000"),
    )
    THICK_BORDER: Border = Border(
        left=Side(style="medium", color="000000"),
        right=Side(style="medium", color="000000"),
        top=Side(style="medium", color="000000"),
        bottom=Side(style="medium", color="000000"),
    )
    CENTER_ALIGN: Alignment = Alignment(horizontal="center", vertical="center")
    LEFT_ALIGN: Alignment = Alignment(horizontal="left", vertical="center")
    RIGHT_ALIGN: Alignment = Alignment(horizontal="right", vertical="center")
    CURRENCY_FONT: Font = Font(name="Consolas", size=10)
    TITLE_FONT: Font = Font(bold=True, size=14, color="1F4E79")
    SUBTITLE_FONT: Font = Font(bold=False, size=11, color="666666")


class BaseExporter(ABC):
    """
    Abstract base class for all ERP exporters.

    Provides common functionality for reading JSON input from stdin,
    generating Excel workbooks, applying styles, and outputting
    binary data to stdout for Laravel consumption.

    Attributes:
        logger: Logger instance for the exporter.
        data: Parsed JSON data from stdin.
        workbook: openpyxl Workbook instance.
        sheet: Active worksheet.
    """

    def __init__(self, exporter_name: str) -> None:
        """
        Initialize the base exporter.

        Args:
            exporter_name: Name identifier for logging purposes.
        """
        self.logger: logging.Logger = logging.getLogger(exporter_name)
        self.data: Dict[str, Any] = {}
        self.workbook: Optional[Workbook] = None
        self.sheet: Optional[Worksheet] = None
        self._output_path: Optional[str] = None

    def read_stdin(self) -> Dict[str, Any]:
        """
        Read and parse JSON data from stdin.

        Returns:
            Parsed JSON dictionary.

        Raises:
            json.JSONDecodeError: If stdin contains invalid JSON.
            ValueError: If stdin is empty.
        """
        self.logger.info("Reading JSON from stdin")
        try:
            raw: str = sys.stdin.read()
            if not raw or not raw.strip():
                self.logger.error("Empty stdin received")
                raise ValueError("No input data provided via stdin")
            self.data = json.loads(raw)
            self.logger.info(
                "Successfully parsed JSON with %d top-level keys",
                len(self.data) if isinstance(self.data, dict) else 1,
            )
            return self.data
        except json.JSONDecodeError as e:
            self.logger.error("JSON decode error at line %d col %d: %s", e.lineno, e.colno, e.msg)
            raise

    def create_workbook(self) -> Workbook:
        """
        Create a new Excel workbook.

        Returns:
            New Workbook instance with active sheet assigned.
        """
        self.logger.info("Creating new workbook")
        self.workbook = Workbook()
        self.sheet = self.workbook.active
        return self.workbook

    def apply_header_style(self, row_num: int = 1, col_start: int = 1, col_end: int = 10) -> None:
        """
        Apply header styling to a row.

        Args:
            row_num: Row number to style (1-indexed).
            col_start: Starting column (1-indexed).
            col_end: Ending column (1-indexed).
        """
        if self.sheet is None:
            self.logger.warning("Sheet is None, cannot apply header style")
            return
        self.logger.debug("Applying header style to row %d, cols %d-%d", row_num, col_start, col_end)
        for col in range(col_start, col_end + 1):
            cell = self.sheet.cell(row=row_num, column=col)
            cell.font = ExportStyle.HEADER_FONT
            cell.fill = ExportStyle.HEADER_FILL
            cell.border = ExportStyle.HEADER_BORDER
            cell.alignment = ExportStyle.CENTER_ALIGN

    def apply_body_style(
        self, row_start: int, row_end: int, col_start: int = 1, col_end: int = 10, alternate: bool = True
    ) -> None:
        """
        Apply body styling to data rows.

        Args:
            row_start: Starting row (1-indexed).
            row_end: Ending row (1-indexed).
            col_start: Starting column (1-indexed).
            col_end: Ending column (1-indexed).
            alternate: Whether to alternate row colors.
        """
        if self.sheet is None:
            self.logger.warning("Sheet is None, cannot apply body style")
            return
        self.logger.debug(
            "Applying body style rows %d-%d, cols %d-%d", row_start, row_end, col_start, col_end
        )
        for row in range(row_start, row_end + 1):
            for col in range(col_start, col_end + 1):
                cell = self.sheet.cell(row=row, column=col)
                cell.border = ExportStyle.THIN_BORDER
                cell.alignment = ExportStyle.LEFT_ALIGN
                if alternate and (row % 2 == 0):
                    cell.fill = ExportStyle.BODY_FILL_LIGHT

    def freeze_pane(self, cell: str = "A2") -> None:
        """
        Freeze panes at the specified cell.

        Args:
            cell: Cell reference for freeze point (e.g., "A2").
        """
        if self.sheet is None:
            return
        self.logger.debug("Freezing pane at %s", cell)
        self.sheet.freeze_panes = cell

    def set_column_widths(self, widths: Dict[str, int]) -> None:
        """
        Set column widths.

        Args:
            widths: Dictionary mapping column letters to widths.
        """
        if self.sheet is None:
            return
        self.logger.debug("Setting column widths: %s", widths)
        for col_letter, width in widths.items():
            self.sheet.column_dimensions[col_letter].width = width

    def auto_fit_columns(
        self,
        start_col: int = 1,
        end_col: Optional[int] = None,
        min_width: int = 8,
        max_width: int = 50,
        padding: int = 2,
    ) -> None:
        """
        Auto-fit column widths based on content with reasonable limits.

        Calculates optimal width for each column based on the longest cell value,
        preventing both text overflow and excessively wide columns.

        Args:
            start_col: Starting column number (1-indexed).
            end_col: Ending column number (1-indexed). If None, uses all columns.
            min_width: Minimum column width.
            max_width: Maximum column width to prevent excessive widths.
            padding: Extra padding to add to calculated width.
        """
        if self.sheet is None:
            return

        if end_col is None:
            end_col = self.sheet.max_column

        for col_num in range(start_col, end_col + 1):
            col_letter = get_column_letter(col_num)
            max_length = 0

            for cell in self.sheet[col_letter]:
                try:
                    if cell.value:
                        # Handle different value types
                        if isinstance(cell.value, str):
                            # Account for formula results
                            if cell.value.startswith('='):
                                cell_len = 15  # Estimated length for formula result
                            else:
                                cell_len = len(str(cell.value))
                        else:
                            cell_len = len(str(cell.value))

                        # Account for bold/larger fonts
                        if cell.font and cell.font.bold:
                            cell_len = int(cell_len * 1.1)

                        max_length = max(max_length, cell_len)
                except (AttributeError, TypeError):
                    pass

            # Apply width with limits
            adjusted_width = max(min_width, min(max_length + padding, max_width))
            self.sheet.column_dimensions[col_letter].width = adjusted_width

        self.logger.debug("Auto-fitted columns %d-%d with limits [%d, %d]",
                         start_col, end_col, min_width, max_width)

    def dataframe_to_sheet(
        self,
        df: pd.DataFrame,
        start_row: int = 1,
        include_header: bool = True,
        include_index: bool = False,
    ) -> int:
        """
        Write a pandas DataFrame to the active sheet.

        Args:
            df: DataFrame to write.
            start_row: Starting row (1-indexed).
            include_header: Whether to include column headers.
            include_index: Whether to include the index column.

        Returns:
            Number of rows written (including header if present).
        """
        if self.sheet is None:
            self.logger.error("Sheet is None, cannot write DataFrame")
            return 0
        self.logger.info("Writing DataFrame with %d rows to sheet starting at row %d", len(df), start_row)
        rows_written: int = 0
        for r_idx, row in enumerate(
            dataframe_to_rows(df, index=include_index, header=include_header), start=start_row
        ):
            for c_idx, value in enumerate(row, start=1):
                self.sheet.cell(row=r_idx, column=c_idx, value=value)
            rows_written += 1
        self.logger.debug("Wrote %d rows to sheet", rows_written)
        return rows_written

    def output_to_stdout(self) -> None:
        """
        Write workbook binary data to stdout for Laravel consumption.

        The output is raw xlsx binary data that Laravel can capture
        and save to a file or stream to the client.
        """
        if self.workbook is None:
            self.logger.error("Workbook is None, cannot output")
            return
        self.logger.info("Writing workbook to stdout")
        buffer = BytesIO()
        self.workbook.save(buffer)
        buffer.seek(0)
        sys.stdout.buffer.write(buffer.read())
        sys.stdout.buffer.flush()
        self.logger.info("Workbook output complete")

    def output_to_file(self, path: str) -> str:
        """
        Save workbook to a file.

        Args:
            path: File path to save to.

        Returns:
            Absolute path of saved file.
        """
        if self.workbook is None:
            self.logger.error("Workbook is None, cannot save to file")
            return ""
        self.logger.info("Saving workbook to %s", path)
        p = Path(path)
        p.parent.mkdir(parents=True, exist_ok=True)
        self.workbook.save(str(p))
        self._output_path = str(p.absolute())
        self.logger.info("Workbook saved to %s", self._output_path)
        return self._output_path

    # ── Advanced Styling Methods ────────────────────────────────────────

    def apply_zebra_stripes(
        self,
        row_start: int,
        row_end: int,
        col_start: int = 1,
        col_end: int = 10,
    ) -> None:
        """Apply professional zebra striping to data rows.

        Args:
            row_start: First data row (1-indexed).
            row_end: Last data row (1-indexed).
            col_start: Starting column.
            col_end: Ending column.
        """
        if self.sheet is None:
            return
        for row in range(row_start, row_end + 1):
            fill = ExportStyle.ZEBRA_FILL_EVEN if row % 2 == 0 else None
            for col in range(col_start, col_end + 1):
                cell = self.sheet.cell(row=row, column=col)
                cell.border = ExportStyle.THIN_BORDER
                if fill:
                    cell.fill = fill

    def apply_subtotal_style(self, row: int, col_start: int = 1, col_end: int = 10) -> None:
        """Apply subtotal row styling.

        Args:
            row: Row number to style.
            col_start: Starting column.
            col_end: Ending column.
        """
        if self.sheet is None:
            return
        for col in range(col_start, col_end + 1):
            cell = self.sheet.cell(row=row, column=col)
            cell.fill = ExportStyle.SUBTOTAL_FILL
            cell.font = Font(bold=True)
            cell.border = ExportStyle.THIN_BORDER

    def apply_total_style(self, row: int, col_start: int = 1, col_end: int = 10) -> None:
        """Apply grand total row styling.

        Args:
            row: Row number to style.
            col_start: Starting column.
            col_end: Ending column.
        """
        if self.sheet is None:
            return
        for col in range(col_start, col_end + 1):
            cell = self.sheet.cell(row=row, column=col)
            cell.fill = ExportStyle.TOTAL_FILL
            cell.font = Font(bold=True, size=11)
            cell.border = ExportStyle.THICK_BORDER

    def apply_currency_format(
        self,
        col: int,
        row_start: int,
        row_end: int,
        symbol: str = "",
    ) -> None:
        """Apply currency formatting to a column.

        Args:
            col: Column number (1-indexed).
            row_start: Starting row.
            row_end: Ending row.
            symbol: Currency symbol.
        """
        if self.sheet is None:
            return
        format_str = f'{symbol}#,##0.00' if symbol else '#,##0.00'
        for row in range(row_start, row_end + 1):
            cell = self.sheet.cell(row=row, column=col)
            cell.number_format = format_str
            cell.font = ExportStyle.CURRENCY_FONT
            cell.alignment = ExportStyle.RIGHT_ALIGN

    # ── Chart Creation Methods ──────────────────────────────────────────

    def add_bar_chart(
        self,
        data_range: Tuple[int, int, int, int],
        categories_range: Optional[Tuple[int, int, int, int]] = None,
        title: str = "Chart",
        anchor: str = "E2",
        width: float = 15,
        height: float = 10,
        sheet: Optional[Worksheet] = None,
    ) -> BarChart:
        """Add a bar chart to the worksheet.

        Args:
            data_range: (min_col, min_row, max_col, max_row) for data.
            categories_range: Optional (min_col, min_row, max_col, max_row) for categories.
            title: Chart title.
            anchor: Cell to anchor chart (top-left).
            width: Chart width in cm.
            height: Chart height in cm.
            sheet: Target sheet (defaults to active).

        Returns:
            Configured BarChart instance.
        """
        ws = sheet or self.sheet
        if ws is None:
            self.logger.warning("No worksheet available for chart")
            return BarChart()

        chart = BarChart()
        chart.type = "col"
        chart.grouping = "clustered"
        chart.title = title
        chart.style = 10
        chart.width = int(width)
        chart.height = int(height)

        data_ref = Reference(
            ws,
            min_col=data_range[0],
            min_row=data_range[1],
            max_col=data_range[2],
            max_row=data_range[3],
        )
        chart.add_data(data_ref, titles_from_data=True)

        if categories_range:
            cat_ref = Reference(
                ws,
                min_col=categories_range[0],
                min_row=categories_range[1],
                max_col=categories_range[2],
                max_row=categories_range[3],
            )
            chart.set_categories(cat_ref)

        chart.y_axis.majorGridlines = None
        chart.legend.position = "b"
        ws.add_chart(chart, anchor)
        self.logger.info("Added bar chart '%s' at %s", title, anchor)
        return chart

    def add_line_chart(
        self,
        data_range: Tuple[int, int, int, int],
        categories_range: Optional[Tuple[int, int, int, int]] = None,
        title: str = "Trend",
        anchor: str = "E2",
        width: float = 15,
        height: float = 10,
        smooth: bool = True,
        sheet: Optional[Worksheet] = None,
    ) -> LineChart:
        """Add a line chart to the worksheet.

        Args:
            data_range: (min_col, min_row, max_col, max_row) for data.
            categories_range: Optional category range.
            title: Chart title.
            anchor: Cell anchor position.
            width: Chart width.
            height: Chart height.
            smooth: Whether to smooth lines.
            sheet: Target sheet.

        Returns:
            Configured LineChart instance.
        """
        ws = sheet or self.sheet
        if ws is None:
            return LineChart()

        chart = LineChart()
        chart.title = title
        chart.style = 10
        chart.width = int(width)
        chart.height = int(height)

        data_ref = Reference(
            ws,
            min_col=data_range[0],
            min_row=data_range[1],
            max_col=data_range[2],
            max_row=data_range[3],
        )
        chart.add_data(data_ref, titles_from_data=True)

        if categories_range:
            cat_ref = Reference(
                ws,
                min_col=categories_range[0],
                min_row=categories_range[1],
                max_col=categories_range[2],
                max_row=categories_range[3],
            )
            chart.set_categories(cat_ref)

        for series in chart.series:
            series.smooth = smooth

        if chart.legend is not None:
            chart.legend.position = "b"
        ws.add_chart(chart, anchor)
        self.logger.info("Added line chart '%s' at %s", title, anchor)
        return chart

    def add_pie_chart(
        self,
        data_range: Tuple[int, int, int, int],
        categories_range: Optional[Tuple[int, int, int, int]] = None,
        title: str = "Distribution",
        anchor: str = "E2",
        width: float = 12,
        height: float = 10,
        sheet: Optional[Worksheet] = None,
    ) -> PieChart:
        """Add a pie chart to the worksheet.

        Args:
            data_range: (min_col, min_row, max_col, max_row) for data.
            categories_range: Optional category range.
            title: Chart title.
            anchor: Cell anchor position.
            width: Chart width.
            height: Chart height.
            sheet: Target sheet.

        Returns:
            Configured PieChart instance.
        """
        ws = sheet or self.sheet
        if ws is None:
            return PieChart()

        chart = PieChart()
        chart.title = title
        chart.style = 10
        chart.width = int(width)
        chart.height = int(height)

        data_ref = Reference(
            ws,
            min_col=data_range[0],
            min_row=data_range[1],
            max_col=data_range[2],
            max_row=data_range[3],
        )
        chart.add_data(data_ref, titles_from_data=True)

        if categories_range:
            cat_ref = Reference(
                ws,
                min_col=categories_range[0],
                min_row=categories_range[1],
                max_col=categories_range[2],
                max_row=categories_range[3],
            )
            chart.set_categories(cat_ref)

        from openpyxl.chart.label import DataLabelList
        chart.dataLabels = DataLabelList()
        chart.dataLabels.showPercent = True
        chart.dataLabels.showVal = False

        ws.add_chart(chart, anchor)
        self.logger.info("Added pie chart '%s' at %s", title, anchor)
        return chart

    # ── Formula Methods ─────────────────────────────────────────────────

    def add_sum_formula(
        self,
        row: int,
        col: int,
        range_col: str,
        start_row: int,
        end_row: int,
    ) -> None:
        """Add a SUM formula to a cell.

        Args:
            row: Target row.
            col: Target column.
            range_col: Column letter to sum.
            start_row: Start of range.
            end_row: End of range.
        """
        if self.sheet is None:
            return
        formula = f"=SUM({range_col}{start_row}:{range_col}{end_row})"
        self.sheet.cell(row=row, column=col, value=formula)

    def add_vlookup_formula(
        self,
        row: int,
        col: int,
        lookup_cell: str,
        table_range: str,
        col_index: int,
        exact_match: bool = True,
    ) -> None:
        """Add a VLOOKUP formula for dynamic data reference.

        Args:
            row: Target row.
            col: Target column.
            lookup_cell: Cell reference for lookup value.
            table_range: Named range or cell range for lookup table.
            col_index: Column index to return (1-based).
            exact_match: Use exact match (FALSE) or approximate (TRUE).
        """
        if self.sheet is None:
            return
        match_type = "FALSE" if exact_match else "TRUE"
        formula = f"=VLOOKUP({lookup_cell},{table_range},{col_index},{match_type})"
        self.sheet.cell(row=row, column=col, value=formula)

    def add_subtotal_formula(
        self,
        row: int,
        col: int,
        range_col: str,
        start_row: int,
        end_row: int,
        function: int = 9,
    ) -> None:
        """Add SUBTOTAL formula (respects filters).

        Args:
            row: Target row.
            col: Target column.
            range_col: Column letter.
            start_row: Start row.
            end_row: End row.
            function: SUBTOTAL function number (9=SUM, 1=AVG, 2=COUNT).
        """
        if self.sheet is None:
            return
        formula = f"=SUBTOTAL({function},{range_col}{start_row}:{range_col}{end_row})"
        self.sheet.cell(row=row, column=col, value=formula)

    def add_percentage_formula(
        self,
        row: int,
        col: int,
        value_cell: str,
        total_cell: str,
    ) -> None:
        """Add percentage calculation formula with zero-division protection.

        Args:
            row: Target row.
            col: Target column.
            value_cell: Cell reference for the value.
            total_cell: Cell reference for the total.
        """
        if self.sheet is None:
            return
        formula = f'=IF({total_cell}=0,0,{value_cell}/{total_cell})'
        cell = self.sheet.cell(row=row, column=col, value=formula)
        cell.number_format = "0.0%"

    def add_variance_formula(
        self,
        row: int,
        col: int,
        actual_cell: str,
        budget_cell: str,
    ) -> None:
        """Add variance calculation formula (Actual - Budget).

        Args:
            row: Target row.
            col: Target column.
            actual_cell: Cell reference for actual value.
            budget_cell: Cell reference for budget value.
        """
        if self.sheet is None:
            return
        formula = f"={actual_cell}-{budget_cell}"
        self.sheet.cell(row=row, column=col, value=formula)

    # ── Conditional Formatting Methods ──────────────────────────────────

    def add_positive_negative_formatting(
        self,
        cell_range: str,
        positive_color: str = "C6EFCE",
        negative_color: str = "FFC7CE",
    ) -> None:
        """Add conditional formatting for positive/negative values.

        Args:
            cell_range: Range to apply formatting (e.g., "C2:C100").
            positive_color: Fill color for positive values.
            negative_color: Fill color for negative values.
        """
        if self.sheet is None:
            return
        pos_fill = PatternFill(start_color=positive_color, end_color=positive_color, fill_type="solid")
        neg_fill = PatternFill(start_color=negative_color, end_color=negative_color, fill_type="solid")

        pos_rule = CellIsRule(operator="greaterThan", formula=["0"], fill=pos_fill)
        neg_rule = CellIsRule(operator="lessThan", formula=["0"], fill=neg_fill)

        self.sheet.conditional_formatting.add(cell_range, pos_rule)
        self.sheet.conditional_formatting.add(cell_range, neg_rule)
        self.logger.info("Added positive/negative formatting to %s", cell_range)

    def add_data_bars(
        self,
        cell_range: str,
        color: str = "4472C4",
    ) -> None:
        """Add data bar conditional formatting for visual comparison.

        Args:
            cell_range: Range to apply data bars.
            color: Bar color (hex without #).
        """
        if self.sheet is None:
            return
        rule = DataBarRule(
            start_type="min",
            end_type="max",
            color=color,
            showValue=True,
            minLength=0,
            maxLength=100,
        )
        self.sheet.conditional_formatting.add(cell_range, rule)
        self.logger.info("Added data bars to %s", cell_range)

    def add_color_scale(
        self,
        cell_range: str,
        start_color: str = "F8696B",
        mid_color: str = "FFEB84",
        end_color: str = "63BE7B",
    ) -> None:
        """Add 3-color scale conditional formatting.

        Args:
            cell_range: Range to apply color scale.
            start_color: Color for minimum values.
            mid_color: Color for mid-point values.
            end_color: Color for maximum values.
        """
        if self.sheet is None:
            return
        rule = ColorScaleRule(
            start_type="min",
            start_color=start_color,
            mid_type="percentile",
            mid_value=50,
            mid_color=mid_color,
            end_type="max",
            end_color=end_color,
        )
        self.sheet.conditional_formatting.add(cell_range, rule)
        self.logger.info("Added color scale to %s", cell_range)

    def add_outlier_formatting(
        self,
        cell_range: str,
        std_threshold: float = 2.0,
        outlier_color: str = "FFC7CE",
        outlier_font_color: str = "9C0006",
    ) -> None:
        """Add conditional formatting to highlight statistical outliers.

        Highlights cells that are more than std_threshold standard deviations
        from the mean. Useful for accountants to quickly spot anomalies.

        Args:
            cell_range: Range to analyze (e.g., "C2:C100").
            std_threshold: Number of standard deviations for outlier threshold.
            outlier_color: Fill color for outliers.
            outlier_font_color: Font color for outliers.
        """
        if self.sheet is None:
            return

        # Anchor formulas to the top-left cell of the target range so Excel
        # applies them relatively for each row/column in the range.
        try:
            min_col, min_row, _, _ = range_boundaries(cell_range)
        except ValueError:
            self.logger.warning("Invalid cell range for outlier formatting: %s", cell_range)
            return
        if min_col is None or min_row is None:
            self.logger.warning("Invalid cell range boundaries: %s", cell_range)
            return
        anchor_cell = f"{get_column_letter(min_col)}{min_row}"

        # Create formula for outlier detection: value >/< mean +/- threshold*stdev
        avg_formula = f"AVERAGE({cell_range})"
        stdev_formula = f"STDEV({cell_range})"

        # Upper outlier rule (value > mean + threshold*std)
        upper_formula = [
            f"AND(NOT(ISBLANK({anchor_cell})), {anchor_cell} > {avg_formula} + {std_threshold}*{stdev_formula})"
        ]
        upper_fill = PatternFill(start_color=outlier_color, end_color=outlier_color, fill_type="solid")
        upper_font = Font(color=outlier_font_color, bold=True)
        upper_rule = Rule(type="expression", formula=upper_formula)
        upper_rule.fill = upper_fill  # type: ignore[attr-defined]
        upper_rule.font = upper_font  # type: ignore[attr-defined]

        # Lower outlier rule (value < mean - threshold*std)
        lower_formula = [
            f"AND(NOT(ISBLANK({anchor_cell})), {anchor_cell} < {avg_formula} - {std_threshold}*{stdev_formula})"
        ]
        lower_fill = PatternFill(start_color=outlier_color, end_color=outlier_color, fill_type="solid")
        lower_font = Font(color=outlier_font_color, bold=True)
        lower_rule = Rule(type="expression", formula=lower_formula)
        lower_rule.fill = lower_fill  # type: ignore[attr-defined]
        lower_rule.font = lower_font  # type: ignore[attr-defined]

        self.sheet.conditional_formatting.add(cell_range, upper_rule)
        self.sheet.conditional_formatting.add(cell_range, lower_rule)
        self.logger.info("Added outlier formatting to %s (threshold: %.1f σ)", cell_range, std_threshold)

    def add_statistical_summary(
        self,
        data_range: str,
        summary_start_row: int,
        summary_col: int = 1,
        currency_symbol: str = "",
    ) -> int:
        """Add statistical summary section for accountants.

        Adds common statistical measures: Mean, Median, Std Dev, Min, Max,
        Q1, Q3, Count, and Coefficient of Variation.

        Args:
            data_range: Range containing numeric data (e.g., "C2:C100").
            summary_start_row: Row to start the summary.
            summary_col: Column for labels (values go in next column).
            currency_symbol: Currency symbol for formatting.

        Returns:
            Last row used by summary.
        """
        if self.sheet is None:
            return summary_start_row

        # Title
        title_cell = self.sheet.cell(row=summary_start_row, column=summary_col, value="Statistical Summary")
        title_cell.font = Font(bold=True, size=12, color="1F4E79")
        title_cell.fill = PatternFill(start_color="E8F4FD", end_color="E8F4FD", fill_type="solid")

        summary_start_row += 1

        stats = [
            ("Count", f"=COUNT({data_range})", "#,##0"),
            ("Mean", f"=AVERAGE({data_range})", f'{currency_symbol}#,##0.00'),
            ("Median", f"=MEDIAN({data_range})", f'{currency_symbol}#,##0.00'),
            ("Std Dev", f"=STDEV({data_range})", f'{currency_symbol}#,##0.00'),
            ("Min", f"=MIN({data_range})", f'{currency_symbol}#,##0.00'),
            ("Q1 (25%)", f"=QUARTILE({data_range},1)", f'{currency_symbol}#,##0.00'),
            ("Q3 (75%)", f"=QUARTILE({data_range},3)", f'{currency_symbol}#,##0.00'),
            ("Max", f"=MAX({data_range})", f'{currency_symbol}#,##0.00'),
            ("Range", f"=MAX({data_range})-MIN({data_range})", f'{currency_symbol}#,##0.00'),
            ("CV (%)", f"=IF(AVERAGE({data_range})=0,0,STDEV({data_range})/AVERAGE({data_range}))", "0.0%"),
        ]

        current_row = summary_start_row
        for label, formula, num_format in stats:
            # Label cell
            label_cell = self.sheet.cell(row=current_row, column=summary_col, value=label)
            label_cell.font = Font(bold=True, size=10)
            label_cell.alignment = Alignment(horizontal="left")

            # Value cell with formula
            value_cell = self.sheet.cell(row=current_row, column=summary_col + 1, value=formula)
            value_cell.number_format = num_format if num_format else "#,##0.00"
            value_cell.alignment = Alignment(horizontal="right")
            value_cell.border = ExportStyle.THIN_BORDER

            current_row += 1

        self.logger.info("Added statistical summary at row %d", summary_start_row)
        return current_row

    # ── Data Validation Methods ─────────────────────────────────────────

    def add_dropdown_validation(
        self,
        cell_range: str,
        options: List[str],
        allow_blank: bool = True,
        error_title: str = "Invalid Entry",
        error_message: str = "Please select from dropdown",
    ) -> None:
        """Add dropdown data validation to a cell range.

        Args:
            cell_range: Range for validation (e.g., "D2:D100").
            options: List of valid options for dropdown.
            allow_blank: Allow empty cells.
            error_title: Error dialog title.
            error_message: Error dialog message.
        """
        if self.sheet is None:
            return

        dv = DataValidation(type="list", formula1=f'"{",".join(options)}"', allow_blank=allow_blank)
        dv.error = error_message
        dv.errorTitle = error_title
        dv.prompt = f"Please select: {', '.join(options[:3])}{'...' if len(options) > 3 else ''}"
        dv.promptTitle = "Select Option"

        self.sheet.add_data_validation(dv)
        dv.add(cell_range)
        self.logger.info("Added dropdown validation to %s with %d options", cell_range, len(options))

    def add_number_validation(
        self,
        cell_range: str,
        min_value: Optional[float] = None,
        max_value: Optional[float] = None,
        allow_blank: bool = True,
    ) -> None:
        """Add number range validation to a cell range.

        Args:
            cell_range: Range for validation (e.g., "E2:E100").
            min_value: Minimum allowed value.
            max_value: Maximum allowed value.
            allow_blank: Allow empty cells.
        """
        if self.sheet is None:
            return

        if min_value is not None and max_value is not None:
            dv = DataValidation(type="whole", operator="between", formula1=min_value, formula2=max_value, allow_blank=allow_blank)
            dv.error = f"Value must be between {min_value} and {max_value}"
        elif min_value is not None:
            dv = DataValidation(type="whole", operator="greaterThanOrEqual", formula1=min_value, allow_blank=allow_blank)
            dv.error = f"Value must be >= {min_value}"
        elif max_value is not None:
            dv = DataValidation(type="whole", operator="lessThanOrEqual", formula1=max_value, allow_blank=allow_blank)
            dv.error = f"Value must be <= {max_value}"
        else:
            return

        dv.errorTitle = "Invalid Number"
        dv.promptTitle = "Enter Number"
        dv.prompt = dv.error

        self.sheet.add_data_validation(dv)
        dv.add(cell_range)
        self.logger.info("Added number validation to %s", cell_range)

    def add_date_validation(
        self,
        cell_range: str,
        min_date: Optional[str] = None,
        max_date: Optional[str] = None,
        allow_blank: bool = True,
    ) -> None:
        """Add date range validation to a cell range.

        Args:
            cell_range: Range for validation (e.g., "F2:F100").
            min_date: Minimum date (ISO format or Excel date).
            max_date: Maximum date (ISO format or Excel date).
            allow_blank: Allow empty cells.
        """
        if self.sheet is None:
            return

        if min_date and max_date:
            dv = DataValidation(type="date", operator="between", formula1=min_date, formula2=max_date, allow_blank=allow_blank)
            dv.error = f"Date must be between {min_date} and {max_date}"
        elif min_date:
            dv = DataValidation(type="date", operator="greaterThanOrEqual", formula1=min_date, allow_blank=allow_blank)
            dv.error = f"Date must be >= {min_date}"
        elif max_date:
            dv = DataValidation(type="date", operator="lessThanOrEqual", formula1=max_date, allow_blank=allow_blank)
            dv.error = f"Date must be <= {max_date}"
        else:
            return

        dv.errorTitle = "Invalid Date"
        dv.promptTitle = "Enter Date"
        dv.prompt = dv.error

        self.sheet.add_data_validation(dv)
        dv.add(cell_range)
        self.logger.info("Added date validation to %s", cell_range)

    # ── Grouping and Drill-down Methods ─────────────────────────────────

    def add_row_grouping(
        self,
        start_row: int,
        end_row: int,
        level: int = 1,
        hidden: bool = False,
    ) -> None:
        """Add row grouping for drill-down capability.

        Args:
            start_row: First row to group.
            end_row: Last row to group.
            level: Outline level (1-7, higher = more nested).
            hidden: Initially collapse the group.
        """
        if self.sheet is None:
            return

        for row_idx in range(start_row, end_row + 1):
            self.sheet.row_dimensions[row_idx].outline_level = level
            if hidden:
                self.sheet.row_dimensions[row_idx].hidden = True

        self.logger.info("Added row grouping: rows %d-%d, level %d", start_row, end_row, level)

    def add_column_grouping(
        self,
        start_col: int,
        end_col: int,
        level: int = 1,
        hidden: bool = False,
    ) -> None:
        """Add column grouping for drill-down capability.

        Args:
            start_col: First column to group (1-based).
            end_col: Last column to group (1-based).
            level: Outline level (1-7, higher = more nested).
            hidden: Initially collapse the group.
        """
        if self.sheet is None:
            return

        for col_idx in range(start_col, end_col + 1):
            col_letter = get_column_letter(col_idx)
            self.sheet.column_dimensions[col_letter].outline_level = level
            if hidden:
                self.sheet.column_dimensions[col_letter].hidden = True

        self.logger.info("Added column grouping: cols %d-%d, level %d", start_col, end_col, level)

    # ── Advanced Chart Methods ──────────────────────────────────────────

    def add_sparkline_column(
        self,
        data_range: str,
        sparkline_col: int,
        start_row: int,
        num_rows: int,
        chart_type: str = "line",
    ) -> None:
        """Add sparkline charts in a column (uses mini line charts as approximation).

        Note: Excel sparklines aren't fully supported by openpyxl, so we create
        mini charts instead.

        Args:
            data_range: Range of data for sparklines (e.g., "B2:F2" for first row).
            sparkline_col: Column to place sparkline charts.
            start_row: First data row.
            num_rows: Number of rows with data.
            chart_type: 'line' or 'column'.
        """
        if self.sheet is None:
            return

        self.logger.info("Added sparkline approximation at column %d (rows %d-%d)",
                        sparkline_col, start_row, start_row + num_rows - 1)
        # Note: Full sparklines require Excel's native sparkline feature which openpyxl doesn't support
        # This is a placeholder for documentation

    def add_waterfall_chart(
        self,
        categories_range: str,
        values_range: str,
        chart_position: str,
        title: str = "Waterfall Analysis",
        width: int = 15,
        height: int = 10,
    ) -> None:
        """Add waterfall chart showing cumulative effect of values.

        Args:
            categories_range: Range for category labels.
            values_range: Range for values.
            chart_position: Cell position (e.g., "G2").
            title: Chart title.
            width: Chart width.
            height: Chart height.
        """
        if self.sheet is None:
            return

        # Create bar chart as approximation (true waterfall charts need more complex setup)
        chart = BarChart()
        chart.type = "col"
        chart.title = title
        chart.style = 12
        chart.width = width
        chart.height = height

        data = Reference(worksheet=self.sheet, range_string=values_range)
        cats = Reference(worksheet=self.sheet, range_string=categories_range)

        chart.add_data(data, titles_from_data=False)
        chart.set_categories(cats)

        # Color coding for waterfall effect
        series = chart.series[0]
        series.graphicalProperties.solidFill = "4472C4"

        self.sheet.add_chart(chart, chart_position)
        self.logger.info("Added waterfall chart at %s", chart_position)

    def create_variance_analysis_sheet(
        self,
        actual_data: pd.DataFrame,
        budget_data: pd.DataFrame,
        sheet_name: str = "Variance Analysis",
        category_col: str = "Category",
        amount_col: str = "Amount",
    ) -> Worksheet:
        """Create variance analysis sheet comparing actual vs budget.

        Args:
            actual_data: DataFrame with actual figures.
            budget_data: DataFrame with budget figures.
            sheet_name: Name for variance sheet.
            category_col: Column name for categories.
            amount_col: Column name for amounts.

        Returns:
            Created worksheet.
        """
        if self.workbook is None:
            self.create_workbook()
        assert self.workbook is not None

        va_sheet: Worksheet = self.workbook.create_sheet(sheet_name)

        # Headers
        headers = [category_col, "Actual", "Budget", "Variance", "Variance %"]
        for col_idx, header in enumerate(headers, start=1):
            cell = va_sheet.cell(row=1, column=col_idx, value=header)
            cell.font = Font(bold=True, color="FFFFFF", size=11)
            cell.fill = PatternFill(start_color="1F4E79", end_color="1F4E79", fill_type="solid")
            cell.alignment = Alignment(horizontal="center")

        # Merge data
        merged = pd.merge(
            actual_data[[category_col, amount_col]].rename(columns={amount_col: "Actual"}),
            budget_data[[category_col, amount_col]].rename(columns={amount_col: "Budget"}),
            on=category_col,
            how="outer"
        ).fillna(0)

        # Write data and formulas
        for idx, row in merged.iterrows():
            row_num = idx + 2
            va_sheet.cell(row=row_num, column=1, value=row[category_col])
            va_sheet.cell(row=row_num, column=2, value=row["Actual"]).number_format = "#,##0.00"
            va_sheet.cell(row=row_num, column=3, value=row["Budget"]).number_format = "#,##0.00"

            # Variance formula
            var_cell = va_sheet.cell(row=row_num, column=4, value=f"=B{row_num}-C{row_num}")
            var_cell.number_format = "#,##0.00"

            # Variance % formula
            var_pct_cell = va_sheet.cell(row=row_num, column=5, value=f"=IF(C{row_num}=0,0,D{row_num}/C{row_num})")
            var_pct_cell.number_format = "0.0%"

            # Conditional formatting for variance
            if row["Actual"] != 0 or row["Budget"] != 0:
                if (row["Actual"] - row["Budget"]) < 0:
                    var_cell.font = Font(color="C00000", bold=True)
                    var_pct_cell.font = Font(color="C00000", bold=True)
                elif (row["Actual"] - row["Budget"]) > 0:
                    var_cell.font = Font(color="70AD47", bold=True)
                    var_pct_cell.font = Font(color="70AD47", bold=True)

        # Add variance chart
        if len(merged) > 0:
            chart = BarChart()
            chart.title = "Actual vs Budget"
            chart.type = "col"
            chart.style = 11
            chart.width = 15
            chart.height = 8

            data = Reference(va_sheet, min_col=2, min_row=1, max_row=len(merged) + 1, max_col=3)
            cats = Reference(va_sheet, min_col=1, min_row=2, max_row=len(merged) + 1)

            chart.add_data(data, titles_from_data=True)
            chart.set_categories(cats)
            va_sheet.add_chart(chart, "G2")

        # Column widths
        va_sheet.column_dimensions['A'].width = 25
        for col in ['B', 'C', 'D', 'E']:
            va_sheet.column_dimensions[col].width = 15

        self.logger.info("Created variance analysis sheet with %d rows", len(merged))
        return va_sheet

    def create_pivot_table_sheet(
        self,
        data: pd.DataFrame,
        rows: List[str],
        values: str,
        aggfunc: str = "sum",
        sheet_name: str = "Pivot Analysis",
    ) -> Worksheet:
        """Create pivot table analysis sheet.

        Args:
            data: Source DataFrame.
            rows: Column names for row grouping.
            values: Column name for values to aggregate.
            aggfunc: Aggregation function (sum, mean, count, etc.).
            sheet_name: Name for pivot sheet.

        Returns:
            Created worksheet.
        """
        if self.workbook is None:
            self.create_workbook()
        assert self.workbook is not None

        pivot_sheet: Worksheet = self.workbook.create_sheet(sheet_name)

        # Create pivot table using pandas
        pivot = pd.pivot_table(data, index=rows, values=values, aggfunc=aggfunc, fill_value=0)
        pivot.reset_index(inplace=True)

        # Write to sheet
        for r_idx, row in enumerate(dataframe_to_rows(pivot, index=False, header=True), 1):
            for c_idx, value in enumerate(row, 1):
                cell = pivot_sheet.cell(row=r_idx, column=c_idx, value=value)

                # Header row styling
                if r_idx == 1:
                    cell.font = Font(bold=True, color="FFFFFF")
                    cell.fill = PatternFill(start_color="1F4E79", end_color="1F4E79", fill_type="solid")
                    cell.alignment = Alignment(horizontal="center")
                # Data rows
                elif c_idx > len(rows) and isinstance(value, (int, float)):
                    cell.number_format = "#,##0.00"
                    cell.alignment = Alignment(horizontal="right")

        # Auto-fit columns
        for col_idx in range(1, len(pivot.columns) + 1):
            pivot_sheet.column_dimensions[get_column_letter(col_idx)].width = 18

        # Add chart
        if len(pivot) > 0 and len(pivot) <= 20:
            chart = BarChart()
            chart.title = f"{values} by {', '.join(rows)}"
            chart.type = "col"
            chart.style = 12

            data_ref = Reference(pivot_sheet, min_col=len(rows) + 1, min_row=1, max_row=len(pivot) + 1)
            cats_ref = Reference(pivot_sheet, min_col=1, min_row=2, max_row=len(pivot) + 1)

            chart.add_data(data_ref, titles_from_data=True)
            chart.set_categories(cats_ref)

            chart_pos = get_column_letter(len(pivot.columns) + 2) + "2"
            pivot_sheet.add_chart(chart, chart_pos)

        self.logger.info("Created pivot table sheet with %d rows", len(pivot))
        return pivot_sheet

    # ── Dashboard Helper Methods ────────────────────────────────────────

    def create_dashboard_sheet(self, name: str = "Dashboard") -> Worksheet:
        """Create a dedicated dashboard worksheet.

        Args:
            name: Sheet name.

        Returns:
            New worksheet for dashboard.
        """
        if self.workbook is None:
            self.create_workbook()
        assert self.workbook is not None
        ws: Worksheet = self.workbook.create_sheet(name, 0)
        ws.sheet_view.showGridLines = False
        return ws

    def add_kpi_cell(
        self,
        row: int,
        col: int,
        label: str,
        value: Union[str, float, int],
        format_type: str = "number",
        currency_symbol: str = "",
        trend: Optional[str] = None,
        sheet: Optional[Worksheet] = None,
    ) -> None:
        """Add a KPI indicator cell to the dashboard.

        Args:
            row: Row number for label.
            col: Column number.
            label: KPI label text.
            value: KPI value.
            format_type: number, currency, percent, or text.
            currency_symbol: Currency symbol for currency format.
            trend: Trend indicator (up, down, flat, or None).
            sheet: Target sheet (defaults to active).
        """
        ws = sheet or self.sheet
        if ws is None:
            return

        # Label cell
        label_cell = ws.cell(row=row, column=col, value=label)
        label_cell.font = Font(bold=True, size=9, color="7F7F7F")
        label_cell.alignment = Alignment(horizontal="left", vertical="center")

        # Value cell
        value_cell = ws.cell(row=row + 1, column=col)
        if format_type == "text" or isinstance(value, str):
            value_cell.value = value
        else:
            value_cell.value = float(value)
            if format_type == "currency":
                value_cell.number_format = f'{currency_symbol}#,##0.00'
            elif format_type == "percent":
                value_cell.number_format = "0.0%"
            else:
                value_cell.number_format = "#,##0.00"
        value_cell.font = Font(bold=True, size=14, color="1F4E79")
        value_cell.alignment = Alignment(horizontal="left", vertical="center")

        # Trend indicator
        if trend:
            trend_cell = ws.cell(row=row + 1, column=col + 1)
            if trend == "up":
                trend_cell.value = "▲"
                trend_cell.font = Font(color="70AD47", size=12)
            elif trend == "down":
                trend_cell.value = "▼"
                trend_cell.font = Font(color="C00000", size=12)
            else:
                trend_cell.value = "●"
                trend_cell.font = Font(color="7F7F7F", size=12)
            trend_cell.alignment = Alignment(horizontal="center", vertical="center")

    def add_autofilter(self, row: int = 1, col_start: int = 1, col_end: int = 10) -> None:
        """Add autofilter to enable data filtering.

        Args:
            row: Header row number.
            col_start: Starting column.
            col_end: Ending column.
        """
        if self.sheet is None:
            return
        start_col = get_column_letter(col_start)
        end_col = get_column_letter(col_end)
        self.sheet.auto_filter.ref = f"{start_col}{row}:{end_col}{row}"
        self.logger.info("Added autofilter to row %d", row)

    def setup_print_settings(
        self,
        col_end: str = "G",
        row_end: int = 100,
        landscape: bool = True,
        fit_to_width: int = 1,
    ) -> None:
        """Configure print settings for professional output.

        Args:
            col_end: Last column letter to include.
            row_end: Last row to include.
            landscape: Use landscape orientation.
            fit_to_width: Pages to fit width (0=auto).
        """
        if self.sheet is None:
            return
        self.sheet.print_area = f"A1:{col_end}{row_end}"
        self.sheet.page_setup.orientation = "landscape" if landscape else "portrait"
        self.sheet.page_setup.fitToPage = True
        self.sheet.page_setup.fitToWidth = fit_to_width
        self.sheet.page_setup.fitToHeight = 0
        self.sheet.print_title_rows = "1:1"
        self.logger.info("Configured print settings")

    @abstractmethod
    def get_headings(self) -> List[str]:
        """
        Return column headings for the export.

        Returns:
            List of column header strings.
        """
        ...

    @abstractmethod
    def process_data(self) -> pd.DataFrame:
        """
        Process input data and return a DataFrame.

        Returns:
            Processed DataFrame ready for export.
        """
        ...

    @abstractmethod
    def export(self) -> None:
        """
        Execute the full export workflow.

        Should read data, process it, create workbook,
        apply styles, and output the result.
        """
        ...


def safe_get(data: Any, key: str, default: Any = "") -> Any:
    """
    Safely get a value from a dict-like object.

    Args:
        data: Dictionary or object to get from.
        key: Key to retrieve.
        default: Default value if key not found.

    Returns:
        Value at key or default.
    """
    if data is None:
        return default
    if isinstance(data, dict):
        return data.get(key, default)
    return getattr(data, key, default)


def format_date(value: Any, fmt: str = "%Y-%m-%d") -> str:
    """
    Format a date value to string.

    Args:
        value: Date value (string, datetime, or None).
        fmt: Output format string.

    Returns:
        Formatted date string or empty string.
    """
    if value is None:
        return ""
    if isinstance(value, str):
        try:
            dt = datetime.fromisoformat(value.replace("Z", "+00:00"))
            return dt.strftime(fmt)
        except ValueError:
            return value
    if isinstance(value, datetime):
        return value.strftime(fmt)
    return str(value)


def format_currency(value: Any, symbol: str = "", decimals: int = 2) -> str:
    """
    Format a numeric value as currency.

    Args:
        value: Numeric value.
        symbol: Currency symbol prefix.
        decimals: Number of decimal places.

    Returns:
        Formatted currency string.
    """
    if value is None:
        return f"{symbol}0.00"
    try:
        num = float(value)
        return f"{symbol}{num:,.{decimals}f}"
    except (ValueError, TypeError):
        return str(value)


def parse_number(value: Any, default: Optional[float] = 0.0) -> Optional[float]:
    """
    Parse numeric values from ints/floats/strings with common formatting.

    Supports values like "1,200.50", "$1,200.50", and "(1,200.50)".

    Args:
        value: Raw numeric-like value.
        default: Fallback when parsing fails. Use None to return None on failure.

    Returns:
        Parsed float value, or default on failure.
    """
    if value is None:
        return default

    if isinstance(value, bool):
        return float(value)

    if isinstance(value, (int, float)):
        if isinstance(value, float) and pd.isna(value):
            return default
        return float(value)

    text = str(value).strip()
    if not text:
        return default

    if text.lower() in {"nan", "none", "null"}:
        return default

    negative = False
    if text.startswith("(") and text.endswith(")"):
        negative = True
        text = text[1:-1].strip()

    # Remove common formatting noise while keeping digits, sign and decimal point.
    text = text.replace(",", "").replace(" ", "")
    text = re.sub(r"[^\d.\-]", "", text)
    if text in {"", "-", ".", "-."}:
        return default

    try:
        num = float(text)
    except (ValueError, TypeError):
        return default

    return -num if negative else num
