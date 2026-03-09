#!/usr/bin/env python3
"""
Excel Utilities Module — Charts, Formulas, Conditional Formatting, Dashboards.

Professional spreadsheet utilities for accountants and managers.
Provides reusable components for dynamic Excel reports.
"""
from dataclasses import dataclass, field
from typing import Any, Dict, List, Optional, Tuple, Union

from openpyxl.chart import BarChart, LineChart, PieChart, Reference
from openpyxl.chart.label import DataLabelList
from openpyxl.chart.series import DataPoint
from openpyxl.formatting.rule import (
    CellIsRule,
    ColorScaleRule,
    DataBarRule,
    FormulaRule,
    IconSetRule,
)
from openpyxl.styles import Alignment, Border, Font, PatternFill, Side
from openpyxl.utils import get_column_letter
from openpyxl.worksheet.worksheet import Worksheet


# ── Styling Constants ───────────────────────────────────────────────


@dataclass(frozen=True)
class ColorPalette:
    """Professional color palette for financial reports."""

    PRIMARY: str = "003366"  # Dark blue
    SECONDARY: str = "1F4E79"  # Medium blue
    ACCENT: str = "4472C4"  # Light blue
    SUCCESS: str = "70AD47"  # Green
    WARNING: str = "FFC000"  # Amber
    DANGER: str = "C00000"  # Red
    NEUTRAL: str = "7F7F7F"  # Gray
    LIGHT: str = "F2F2F2"  # Light gray
    WHITE: str = "FFFFFF"
    BLACK: str = "000000"
    ZEBRA_ODD: str = "FFFFFF"
    ZEBRA_EVEN: str = "E8F4FD"
    HEADER_BG: str = "1F4E79"
    HEADER_FG: str = "FFFFFF"
    SUBTOTAL_BG: str = "D6DCE5"
    TOTAL_BG: str = "B4C6E7"


@dataclass(frozen=True)
class ChartColors:
    """Default chart color series for consistent styling."""

    SERIES: Tuple[str, ...] = (
        "4472C4",  # Blue
        "ED7D31",  # Orange
        "A5A5A5",  # Gray
        "FFC000",  # Yellow
        "5B9BD5",  # Light Blue
        "70AD47",  # Green
        "264478",  # Dark Blue
        "9E480E",  # Dark Orange
    )


COLORS = ColorPalette()
CHART_COLORS = ChartColors()


# ── Font and Style Factories ────────────────────────────────────────


def header_font(size: int = 11, bold: bool = True) -> Font:
    """Create header font with standard styling."""
    return Font(bold=bold, size=size, color=COLORS.HEADER_FG)


def body_font(size: int = 10, bold: bool = False) -> Font:
    """Create body font with standard styling."""
    return Font(bold=bold, size=size, color=COLORS.BLACK)


def currency_font(color: str = "000000", bold: bool = False) -> Font:
    """Create currency font (right-aligned numbers)."""
    return Font(bold=bold, color=color, name="Consolas", size=10)


def header_fill() -> PatternFill:
    """Create header fill with standard color."""
    return PatternFill(
        start_color=COLORS.HEADER_BG,
        end_color=COLORS.HEADER_BG,
        fill_type="solid",
    )


def zebra_fill(row_index: int) -> PatternFill:
    """Get zebra stripe fill based on row index."""
    color = COLORS.ZEBRA_EVEN if row_index % 2 == 0 else COLORS.ZEBRA_ODD
    return PatternFill(start_color=color, end_color=color, fill_type="solid")


def subtotal_fill() -> PatternFill:
    """Create subtotal row fill."""
    return PatternFill(
        start_color=COLORS.SUBTOTAL_BG,
        end_color=COLORS.SUBTOTAL_BG,
        fill_type="solid",
    )


def total_fill() -> PatternFill:
    """Create total row fill."""
    return PatternFill(
        start_color=COLORS.TOTAL_BG,
        end_color=COLORS.TOTAL_BG,
        fill_type="solid",
    )


def thin_border(color: str = "B4B4B4") -> Border:
    """Create thin border for data cells."""
    side = Side(style="thin", color=color)
    return Border(left=side, right=side, top=side, bottom=side)


def thick_border(color: str = "000000") -> Border:
    """Create thick border for emphasis cells."""
    side = Side(style="medium", color=color)
    return Border(left=side, right=side, top=side, bottom=side)


# ── Conditional Formatting Rules ────────────────────────────────────


def positive_negative_rule(
    start_cell: str,
    end_cell: str,
    positive_color: str = COLORS.SUCCESS,
    negative_color: str = COLORS.DANGER,
) -> Tuple[CellIsRule, CellIsRule]:
    """Create rules for positive/negative value formatting.

    Args:
        start_cell: Start cell reference (e.g., "C2").
        end_cell: End cell reference (e.g., "C100").
        positive_color: Fill color for positive values.
        negative_color: Fill color for negative values.

    Returns:
        Tuple of (positive_rule, negative_rule).
    """
    pos_fill = PatternFill(start_color=positive_color, end_color=positive_color, fill_type="solid")
    neg_fill = PatternFill(start_color=negative_color, end_color=negative_color, fill_type="solid")
    pos_rule = CellIsRule(operator="greaterThan", formula=["0"], fill=pos_fill)
    neg_rule = CellIsRule(operator="lessThan", formula=["0"], fill=neg_fill)
    return pos_rule, neg_rule


def data_bar_rule(
    color: str = COLORS.ACCENT,
    min_type: str = "min",
    max_type: str = "max",
) -> DataBarRule:
    """Create a data bar rule for visual comparison.

    Args:
        color: Bar fill color (hex without #).
        min_type: Minimum value type ("min", "num", "percent").
        max_type: Maximum value type ("max", "num", "percent").

    Returns:
        DataBarRule instance.
    """
    return DataBarRule(
        start_type=min_type,
        end_type=max_type,
        color=color,
        showValue=True,
        minLength=0,
        maxLength=100,
    )


def color_scale_rule(
    start_color: str = COLORS.DANGER,
    mid_color: str = COLORS.WARNING,
    end_color: str = COLORS.SUCCESS,
) -> ColorScaleRule:
    """Create a 3-color scale rule.

    Args:
        start_color: Color for minimum values.
        mid_color: Color for mid-point values.
        end_color: Color for maximum values.

    Returns:
        ColorScaleRule instance.
    """
    return ColorScaleRule(
        start_type="min",
        start_color=start_color,
        mid_type="percentile",
        mid_value=50,
        mid_color=mid_color,
        end_type="max",
        end_color=end_color,
    )


def icon_set_rule(icon_style: str = "3Arrows") -> IconSetRule:
    """Create icon set rule for trend indicators.

    Args:
        icon_style: Icon set name (3Arrows, 3TrafficLights, 5Arrows, etc.).

    Returns:
        IconSetRule instance.
    """
    return IconSetRule(
        icon_style=icon_style,
        type="percent",
        values=[0, 33, 67],
        showValue=True,
        reverse=False,
    )


# ── Chart Factory Functions ─────────────────────────────────────────


def create_bar_chart(
    ws: Worksheet,
    data_ref: Reference,
    categories_ref: Optional[Reference] = None,
    title: str = "Bar Chart",
    x_title: str = "",
    y_title: str = "",
    width: float = 15,
    height: float = 10,
    style: int = 10,
) -> BarChart:
    """Create a styled bar chart.

    Args:
        ws: Target worksheet.
        data_ref: Reference to data range.
        categories_ref: Reference to category labels.
        title: Chart title.
        x_title: X-axis title.
        y_title: Y-axis title.
        width: Chart width in cm.
        height: Chart height in cm.
        style: Chart style number (1-48).

    Returns:
        Configured BarChart instance.
    """
    chart = BarChart()
    chart.type = "col"
    chart.grouping = "clustered"
    chart.title = title
    chart.style = style
    chart.width = width
    chart.height = height
    if x_title:
        chart.x_axis.title = x_title
    if y_title:
        chart.y_axis.title = y_title
    chart.add_data(data_ref, titles_from_data=True)
    if categories_ref:
        chart.set_categories(categories_ref)
    chart.y_axis.majorGridlines = None
    chart.legend.position = "b"
    return chart


def create_line_chart(
    ws: Worksheet,
    data_ref: Reference,
    categories_ref: Optional[Reference] = None,
    title: str = "Line Chart",
    x_title: str = "",
    y_title: str = "",
    width: float = 15,
    height: float = 10,
    style: int = 10,
    smooth: bool = False,
) -> LineChart:
    """Create a styled line chart.

    Args:
        ws: Target worksheet.
        data_ref: Reference to data range.
        categories_ref: Reference to category labels.
        title: Chart title.
        x_title: X-axis title.
        y_title: Y-axis title.
        width: Chart width.
        height: Chart height.
        style: Chart style number.
        smooth: Whether to smooth lines.

    Returns:
        Configured LineChart instance.
    """
    chart = LineChart()
    chart.title = title
    chart.style = style
    chart.width = width
    chart.height = height
    if x_title:
        chart.x_axis.title = x_title
    if y_title:
        chart.y_axis.title = y_title
    chart.add_data(data_ref, titles_from_data=True)
    if categories_ref:
        chart.set_categories(categories_ref)
    for series in chart.series:
        series.smooth = smooth
    chart.legend.position = "b"
    return chart


def create_pie_chart(
    ws: Worksheet,
    data_ref: Reference,
    categories_ref: Optional[Reference] = None,
    title: str = "Pie Chart",
    width: float = 12,
    height: float = 10,
    style: int = 10,
) -> PieChart:
    """Create a styled pie chart.

    Args:
        ws: Target worksheet.
        data_ref: Reference to data range.
        categories_ref: Reference to category labels.
        title: Chart title.
        width: Chart width.
        height: Chart height.
        style: Chart style number.

    Returns:
        Configured PieChart instance.
    """
    chart = PieChart()
    chart.title = title
    chart.style = style
    chart.width = width
    chart.height = height
    chart.add_data(data_ref, titles_from_data=True)
    if categories_ref:
        chart.set_categories(categories_ref)
    chart.dataLabels = DataLabelList()
    chart.dataLabels.showPercent = True
    chart.dataLabels.showVal = False
    chart.dataLabels.showCatName = False
    return chart


# ── Excel Formula Builders ──────────────────────────────────────────


class FormulaBuilder:
    """Builder class for Excel formulas with type hints and validation."""

    @staticmethod
    def sum_range(col: str, start_row: int, end_row: int) -> str:
        """Create SUM formula for a column range.

        Args:
            col: Column letter (e.g., "C").
            start_row: Start row number.
            end_row: End row number.

        Returns:
            Excel SUM formula string.
        """
        return f"=SUM({col}{start_row}:{col}{end_row})"

    @staticmethod
    def sum_if(
        criteria_range: str,
        criteria: str,
        sum_range: str,
    ) -> str:
        """Create SUMIF formula.

        Args:
            criteria_range: Range to evaluate criteria.
            criteria: Condition string (e.g., '">0"').
            sum_range: Range to sum if criteria met.

        Returns:
            Excel SUMIF formula string.
        """
        return f'=SUMIF({criteria_range},{criteria},{sum_range})'

    @staticmethod
    def count_if(criteria_range: str, criteria: str) -> str:
        """Create COUNTIF formula.

        Args:
            criteria_range: Range to count.
            criteria: Condition string.

        Returns:
            Excel COUNTIF formula string.
        """
        return f'=COUNTIF({criteria_range},{criteria})'

    @staticmethod
    def average(col: str, start_row: int, end_row: int) -> str:
        """Create AVERAGE formula.

        Args:
            col: Column letter.
            start_row: Start row.
            end_row: End row.

        Returns:
            Excel AVERAGE formula string.
        """
        return f"=AVERAGE({col}{start_row}:{col}{end_row})"

    @staticmethod
    def vlookup(
        lookup_value: str,
        table_array: str,
        col_index: int,
        range_lookup: bool = False,
    ) -> str:
        """Create VLOOKUP formula.

        Args:
            lookup_value: Cell reference or value to look up.
            table_array: Range for lookup table.
            col_index: Column index to return (1-based).
            range_lookup: FALSE for exact match, TRUE for approximate.

        Returns:
            Excel VLOOKUP formula string.
        """
        lookup_type = "TRUE" if range_lookup else "FALSE"
        return f"=VLOOKUP({lookup_value},{table_array},{col_index},{lookup_type})"

    @staticmethod
    def if_formula(
        condition: str,
        value_if_true: str,
        value_if_false: str,
    ) -> str:
        """Create IF formula.

        Args:
            condition: Logical condition.
            value_if_true: Value/formula if true.
            value_if_false: Value/formula if false.

        Returns:
            Excel IF formula string.
        """
        return f"=IF({condition},{value_if_true},{value_if_false})"

    @staticmethod
    def percentage_change(
        old_value: str,
        new_value: str,
    ) -> str:
        """Create percentage change formula with division-by-zero protection.

        Args:
            old_value: Cell reference for old value.
            new_value: Cell reference for new value.

        Returns:
            Excel formula for percentage change.
        """
        return f'=IF({old_value}=0,"N/A",({new_value}-{old_value})/{old_value})'

    @staticmethod
    def subtotal(function_num: int, range_ref: str) -> str:
        """Create SUBTOTAL formula (ignores filtered rows).

        Args:
            function_num: Function number (9=SUM, 1=AVERAGE, 2=COUNT, etc.).
            range_ref: Cell range reference.

        Returns:
            Excel SUBTOTAL formula string.
        """
        return f"=SUBTOTAL({function_num},{range_ref})"

    @staticmethod
    def index_match(
        return_range: str,
        lookup_range: str,
        lookup_value: str,
    ) -> str:
        """Create INDEX/MATCH formula (more flexible than VLOOKUP).

        Args:
            return_range: Range to return value from.
            lookup_range: Range to search for value.
            lookup_value: Value to find.

        Returns:
            Excel INDEX/MATCH formula string.
        """
        return f"=INDEX({return_range},MATCH({lookup_value},{lookup_range},0))"


# ── Dashboard KPI Cell Builder ──────────────────────────────────────


@dataclass
class KPICell:
    """Represents a Key Performance Indicator cell in a dashboard."""

    label: str
    value: Union[str, float, int]
    format_type: str = "number"  # number, currency, percent, text
    comparison_value: Optional[float] = None
    trend: Optional[str] = None  # up, down, flat
    font_color: str = COLORS.BLACK
    bg_color: str = COLORS.WHITE

    def get_formatted_value(self, currency_symbol: str = "") -> str:
        """Format value based on format_type."""
        if self.format_type == "currency":
            return f"{currency_symbol}{float(self.value):,.2f}"
        if self.format_type == "percent":
            return f"{float(self.value):.1%}"
        if self.format_type == "number":
            return f"{float(self.value):,.2f}"
        return str(self.value)


def write_kpi_cell(
    ws: Worksheet,
    row: int,
    col: int,
    kpi: KPICell,
    currency_symbol: str = "",
) -> None:
    """Write a KPI cell with styling.

    Args:
        ws: Target worksheet.
        row: Row number.
        col: Column number.
        kpi: KPICell data.
        currency_symbol: Currency symbol to use.
    """
    # Label cell
    label_cell = ws.cell(row=row, column=col, value=kpi.label)
    label_cell.font = Font(bold=True, size=9, color=COLORS.NEUTRAL)
    label_cell.alignment = Alignment(horizontal="left", vertical="center")

    # Value cell (below label)
    value_cell = ws.cell(row=row + 1, column=col)
    if kpi.format_type == "text" or isinstance(kpi.value, str):
        value_cell.value = kpi.value
    else:
        value_cell.value = float(kpi.value)
        if kpi.format_type == "currency":
            value_cell.number_format = f'{currency_symbol}#,##0.00'
        elif kpi.format_type == "percent":
            value_cell.number_format = "0.0%"
        else:
            value_cell.number_format = "#,##0.00"
    value_cell.font = Font(bold=True, size=14, color=kpi.font_color)
    value_cell.fill = PatternFill(
        start_color=kpi.bg_color,
        end_color=kpi.bg_color,
        fill_type="solid"
    )
    value_cell.alignment = Alignment(horizontal="left", vertical="center")

    # Trend indicator
    if kpi.trend:
        trend_cell = ws.cell(row=row + 1, column=col + 1)
        if kpi.trend == "up":
            trend_cell.value = "▲"
            trend_cell.font = Font(color=COLORS.SUCCESS, size=12)
        elif kpi.trend == "down":
            trend_cell.value = "▼"
            trend_cell.font = Font(color=COLORS.DANGER, size=12)
        else:
            trend_cell.value = "●"
            trend_cell.font = Font(color=COLORS.NEUTRAL, size=12)
        trend_cell.alignment = Alignment(horizontal="center", vertical="center")


# ── Table Formatter ─────────────────────────────────────────────────


class TableFormatter:
    """Utility class for formatting data tables professionally."""

    def __init__(self, ws: Worksheet) -> None:
        """Initialize with target worksheet."""
        self._ws = ws

    def format_header_row(
        self,
        row: int,
        col_start: int,
        col_end: int,
        bold: bool = True,
    ) -> None:
        """Apply header styling to a row range.

        Args:
            row: Row number.
            col_start: Starting column.
            col_end: Ending column.
            bold: Whether to bold text.
        """
        for col in range(col_start, col_end + 1):
            cell = self._ws.cell(row=row, column=col)
            cell.font = header_font(bold=bold)
            cell.fill = header_fill()
            cell.border = thick_border()
            cell.alignment = Alignment(horizontal="center", vertical="center")

    def format_data_rows(
        self,
        row_start: int,
        row_end: int,
        col_start: int,
        col_end: int,
        zebra: bool = True,
        currency_cols: Optional[List[int]] = None,
    ) -> None:
        """Apply data styling with zebra stripes.

        Args:
            row_start: First data row.
            row_end: Last data row.
            col_start: Starting column.
            col_end: Ending column.
            zebra: Enable zebra striping.
            currency_cols: Columns to format as currency (1-based).
        """
        currency_cols = currency_cols or []
        for row_idx in range(row_start, row_end + 1):
            for col in range(col_start, col_end + 1):
                cell = self._ws.cell(row=row_idx, column=col)
                cell.border = thin_border()
                if zebra:
                    cell.fill = zebra_fill(row_idx)
                if col in currency_cols:
                    cell.alignment = Alignment(horizontal="right", vertical="center")
                    cell.font = currency_font()
                else:
                    cell.alignment = Alignment(horizontal="left", vertical="center")

    def format_subtotal_row(
        self,
        row: int,
        col_start: int,
        col_end: int,
        label_col: int = 1,
        label: str = "Subtotal",
    ) -> None:
        """Format a subtotal row.

        Args:
            row: Row number.
            col_start: Starting column.
            col_end: Ending column.
            label_col: Column for label.
            label: Subtotal label text.
        """
        for col in range(col_start, col_end + 1):
            cell = self._ws.cell(row=row, column=col)
            cell.fill = subtotal_fill()
            cell.border = thin_border()
            cell.font = Font(bold=True)
            if col == label_col:
                cell.value = label

    def format_total_row(
        self,
        row: int,
        col_start: int,
        col_end: int,
        label_col: int = 1,
        label: str = "Total",
    ) -> None:
        """Format a grand total row.

        Args:
            row: Row number.
            col_start: Starting column.
            col_end: Ending column.
            label_col: Column for label.
            label: Total label text.
        """
        for col in range(col_start, col_end + 1):
            cell = self._ws.cell(row=row, column=col)
            cell.fill = total_fill()
            cell.border = thick_border()
            cell.font = Font(bold=True, size=11)
            if col == label_col:
                cell.value = label

    def add_autofilter(self, col_start: int, col_end: int, row: int) -> None:
        """Add autofilter to header row.

        Args:
            col_start: Starting column.
            col_end: Ending column.
            row: Header row number.
        """
        start_col = get_column_letter(col_start)
        end_col = get_column_letter(col_end)
        self._ws.auto_filter.ref = f"{start_col}{row}:{end_col}{row}"


# ── Named Ranges Helper ─────────────────────────────────────────────


def create_named_range(
    ws: Worksheet,
    name: str,
    col: str,
    start_row: int,
    end_row: int,
) -> str:
    """Create a named range in the worksheet.

    Args:
        ws: Target worksheet.
        name: Name for the range (must be valid Excel name).
        col: Column letter.
        start_row: Starting row.
        end_row: Ending row.

    Returns:
        The range string created.
    """
    range_str = f"'{ws.title}'!${col}${start_row}:${col}${end_row}"
    ws.parent.defined_names.add(f"{name}={range_str}")
    return range_str


# ── Print Setup Helper ──────────────────────────────────────────────


def setup_print_area(
    ws: Worksheet,
    col_end: str = "G",
    row_end: int = 100,
    landscape: bool = True,
    fit_to_width: int = 1,
    fit_to_height: int = 0,
) -> None:
    """Configure print settings for professional output.

    Args:
        ws: Target worksheet.
        col_end: Last column to include.
        row_end: Last row to include.
        landscape: Use landscape orientation.
        fit_to_width: Pages to fit width.
        fit_to_height: Pages to fit height (0 = auto).
    """
    ws.print_area = f"A1:{col_end}{row_end}"
    ws.page_setup.orientation = "landscape" if landscape else "portrait"
    ws.page_setup.fitToPage = True
    ws.page_setup.fitToWidth = fit_to_width
    ws.page_setup.fitToHeight = fit_to_height
    ws.print_title_rows = "1:1"
