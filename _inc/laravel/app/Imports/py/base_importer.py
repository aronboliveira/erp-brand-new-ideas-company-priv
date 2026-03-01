#!/usr/bin/env python3
"""Base Importer Module — abstract base + utilities for spreadsheet imports via openpyxl/pandas."""

import json
import logging
import sys
from abc import ABC, abstractmethod
from datetime import datetime
from pathlib import Path
from typing import Any, Dict, List, Optional, Tuple, Union

import pandas as pd
from openpyxl import load_workbook
from openpyxl.worksheet.worksheet import Worksheet

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(name)s: %(message)s",
)
logger: logging.Logger = logging.getLogger(__name__)


class BaseImporter(ABC):
    """Abstract base class for all spreadsheet importers.

    Subclasses must implement:
        - ``get_expected_headers`` — return list of expected column headers.
        - ``validate_row``        — validate a single row dict, return cleaned dict or None.
        - ``process_data``        — transform the DataFrame and return list of validated dicts.
        - ``run``                 — entry-point that orchestrates the full import pipeline.
    """

    def __init__(self, importer_name: str) -> None:
        """Initialise the importer.

        Args:
            importer_name: Human-readable importer identifier for logging.
        """
        self._name: str = importer_name
        self._data: Dict[str, Any] = {}
        self._errors: List[str] = []
        self._imported_count: int = 0
        self._skipped_count: int = 0
        logger.info("BaseImporter.__init__ — %s", self._name)

    # ------------------------------------------------------------------
    # stdin / stdout helpers
    # ------------------------------------------------------------------

    def read_stdin(self) -> Dict[str, Any]:
        """Read JSON data from stdin.

        Returns:
            Parsed dictionary from the JSON payload.

        Raises:
            json.JSONDecodeError: If stdin does not contain valid JSON.
        """
        raw: str = sys.stdin.read()
        if not raw.strip():
            logger.warning("%s: empty stdin", self._name)
            return {}
        self._data = json.loads(raw)
        logger.info("%s: read %d bytes from stdin", self._name, len(raw))
        return self._data

    def write_result(self, rows: List[Dict[str, Any]]) -> None:
        """Write the import result as JSON to stdout.

        Args:
            rows: List of validated row dicts that were successfully parsed.
        """
        result: Dict[str, Any] = {
            "status": "success" if not self._errors else "partial",
            "imported": self._imported_count,
            "skipped": self._skipped_count,
            "errors": self._errors[:50],
            "rows": rows,
        }
        sys.stdout.write(json.dumps(result, default=str))
        sys.stdout.flush()
        logger.info(
            "%s: wrote result — imported=%d skipped=%d errors=%d",
            self._name,
            self._imported_count,
            self._skipped_count,
            len(self._errors),
        )

    # ------------------------------------------------------------------
    # File reading helpers
    # ------------------------------------------------------------------

    def read_spreadsheet(
        self,
        file_path: str,
        sheet_name: Optional[Union[str, int]] = 0,
        header_row: int = 0,
    ) -> pd.DataFrame:
        """Read an Excel/CSV file into a pandas DataFrame.

        Args:
            file_path:  Absolute path to the spreadsheet file.
            sheet_name: Sheet name or index (default first sheet).
            header_row: Row index containing column headers (0-based).

        Returns:
            DataFrame with the spreadsheet data.
        """
        path: Path = Path(file_path)
        if not path.exists():
            raise FileNotFoundError(f"File not found: {file_path}")

        ext: str = path.suffix.lower()
        logger.info("%s: reading %s (ext=%s)", self._name, file_path, ext)

        if ext in (".xlsx", ".xls", ".xlsm"):
            df: pd.DataFrame = pd.read_excel(
                file_path,
                sheet_name=sheet_name,
                header=header_row,
                engine="openpyxl" if ext != ".xls" else "xlrd",
            )
        elif ext == ".csv":
            df = pd.read_csv(file_path, header=header_row)
        else:
            raise ValueError(f"Unsupported file extension: {ext}")

        logger.info(
            "%s: loaded %d rows x %d cols", self._name, len(df), len(df.columns)
        )
        return df

    def read_rows_from_data(self) -> pd.DataFrame:
        """Build a DataFrame from the ``rows`` key in ``self._data``.

        Useful when PHP has already parsed the file and sends raw row arrays.

        Returns:
            DataFrame constructed from the rows payload.
        """
        rows: List[Any] = self._data.get("rows", [])
        if not rows:
            logger.warning("%s: no rows in data payload", self._name)
            return pd.DataFrame()
        df: pd.DataFrame = pd.DataFrame(rows)
        logger.info(
            "%s: built DataFrame from %d rows", self._name, len(df)
        )
        return df

    # ------------------------------------------------------------------
    # Header detection
    # ------------------------------------------------------------------

    def detect_header_row(
        self,
        ws: Worksheet,
        expected: List[str],
        max_scan: int = 20,
    ) -> Tuple[int, Dict[str, int]]:
        """Scan the first *max_scan* rows of *ws* to find one that contains
        at least two of the *expected* headers.

        Args:
            ws:       An openpyxl Worksheet.
            expected: List of expected header strings (case-insensitive).
            max_scan: Maximum rows to scan.

        Returns:
            Tuple of (row_index_0based, {header_name: col_index}).

        Raises:
            RuntimeError: If no header row is detected.
        """
        expected_lower: List[str] = [h.lower().strip() for h in expected]
        for row_idx, row in enumerate(ws.iter_rows(max_row=max_scan, values_only=True)):
            mapping: Dict[str, int] = {}
            for col_idx, cell in enumerate(row):
                if cell is None:
                    continue
                normalised: str = str(cell).lower().strip()
                if normalised in expected_lower:
                    mapping[normalised] = col_idx
            if len(mapping) >= 2:
                logger.info(
                    "%s: header detected at row %d — %s",
                    self._name,
                    row_idx,
                    list(mapping.keys()),
                )
                return row_idx, mapping
        raise RuntimeError(
            f"{self._name}: header row not found in first {max_scan} rows"
        )

    # ------------------------------------------------------------------
    # Validation helpers
    # ------------------------------------------------------------------

    @staticmethod
    def clean_cell(value: Any) -> Any:
        """Normalise a cell value — convert NaN strings and blanks to None.

        Args:
            value: Raw cell value.

        Returns:
            Cleaned value, or None if blank/NaN.
        """
        if value is None:
            return None
        if isinstance(value, float) and pd.isna(value):
            return None
        s: str = str(value).strip()
        if s == "" or s.lower() == "nan":
            return None
        return value

    def add_error(self, row_idx: int, message: str) -> None:
        """Register an import error for a specific row.

        Args:
            row_idx: 0-based row index in the source data.
            message: Human-readable error description.
        """
        entry: str = f"Row {row_idx}: {message}"
        self._errors.append(entry)
        self._skipped_count += 1
        logger.warning("%s: %s", self._name, entry)

    # ------------------------------------------------------------------
    # Abstract interface
    # ------------------------------------------------------------------

    @abstractmethod
    def get_expected_headers(self) -> List[str]:
        """Return the list of expected column header names."""
        ...

    @abstractmethod
    def validate_row(self, row: Dict[str, Any], row_idx: int) -> Optional[Dict[str, Any]]:
        """Validate and clean a single row dict.

        Args:
            row:     Dictionary mapping header names to cell values.
            row_idx: 0-based index of the row in the source data.

        Returns:
            Cleaned row dict, or None if the row should be skipped.
        """
        ...

    @abstractmethod
    def process_data(self) -> List[Dict[str, Any]]:
        """Transform the loaded data into a list of validated row dicts."""
        ...

    @abstractmethod
    def run(self) -> None:
        """Entry-point: read stdin → parse → validate → write result."""
        ...


# ------------------------------------------------------------------
# Utility functions
# ------------------------------------------------------------------


def safe_get(data: Dict[str, Any], key: str, default: Any = "") -> Any:
    """Safely retrieve a value from a dictionary.

    Args:
        data:    Source dictionary.
        key:     Key to look up.
        default: Fallback value if key is missing or value is None.

    Returns:
        The value, or *default*.
    """
    val: Any = data.get(key)
    return val if val is not None else default


def normalise_header(header: str) -> str:
    """Convert a header string to snake_case.

    Args:
        header: Raw header text (e.g. ``'Employee ID'``).

    Returns:
        Snake-cased string (e.g. ``'employee_id'``).
    """
    import re

    cleaned: str = re.sub(r"[^A-Za-z0-9 ]+", "", header.strip())
    parts: List[str] = cleaned.split()
    return "_".join(p.lower() for p in parts)


def parse_date(value: Any, fmt: str = "%Y-%m-%d", strict: bool = False) -> Optional[str]:
    """Attempt to parse a date value into a formatted string.

    Args:
        value: Raw date value (string, datetime, or None).
        fmt:   Output format string.
        strict: If True, return None when parsing fails.

    Returns:
        Formatted date string. Returns None when strict mode fails parsing,
        otherwise returns the stripped original text.
    """
    if value is None:
        return None
    if isinstance(value, datetime):
        return value.strftime(fmt)
    raw: str = str(value).strip()
    if not raw:
        return None

    # Try the declared format first.
    try:
        return datetime.strptime(raw, fmt).strftime(fmt)
    except (ValueError, TypeError):
        pass

    # Try ISO-like datetime strings (e.g. 2025-01-01T12:00:00Z).
    try:
        return datetime.fromisoformat(raw.replace("Z", "+00:00")).strftime(fmt)
    except (ValueError, TypeError):
        pass

    # Try common alternate calendar formats.
    for pattern in ("%Y/%m/%d", "%d/%m/%Y", "%m/%d/%Y"):
        try:
            return datetime.strptime(raw, pattern).strftime(fmt)
        except (ValueError, TypeError):
            continue

    if strict:
        return None
    return raw
