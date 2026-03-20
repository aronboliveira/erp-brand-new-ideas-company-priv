#!/usr/bin/env python3
"""Attendance Importer — parses attendance spreadsheets via Python."""

import logging
from typing import Any, Dict, List, Optional


from base_importer import BaseImporter, parse_date

logger: logging.Logger = logging.getLogger(__name__)


class AttendanceImporter(BaseImporter):
    """Import employee attendance records from spreadsheet data.

    Expected columns: employee_id, date, status, clock_in, clock_out,
                      late, early_leaving, overtime, total_rest.
    """

    EXPECTED_HEADERS: List[str] = [
        "employee_id",
        "date",
        "status",
        "clock_in",
        "clock_out",
        "late",
        "early_leaving",
        "overtime",
        "total_rest",
    ]

    def __init__(self) -> None:
        """Initialise the AttendanceImporter."""
        super().__init__("AttendanceImporter")

    def get_expected_headers(self) -> List[str]:
        """Return the expected attendance column headers."""
        return self.EXPECTED_HEADERS

    def validate_row(
        self, row: Dict[str, Any], row_idx: int
    ) -> Optional[Dict[str, Any]]:
        """Validate a single attendance row.

        Args:
            row:     Dict mapping header → value.
            row_idx: 0-based row index for error reporting.

        Returns:
            Cleaned dict, or None if the row should be skipped.
        """
        employee_id: Any = self.clean_cell(row.get("employee_id"))
        if employee_id is None:
            self.add_error(row_idx, "missing employee_id")
            return None
        date_val: Optional[str] = parse_date(
            self.clean_cell(row.get("date")),
            strict=True
        )
        if date_val is None:
            self.add_error(row_idx, "missing or invalid date")
            return None
        return {
            "employee_id": employee_id,
            "date": date_val,
            "status": self.clean_cell(row.get("status")) or "Present",
            "clock_in": self.clean_cell(row.get("clock_in")),
            "clock_out": self.clean_cell(row.get("clock_out")),
            "late": self.clean_cell(row.get("late")) or "0",
            "early_leaving": self.clean_cell(row.get("early_leaving")) or "0",
            "overtime": self.clean_cell(row.get("overtime")) or "0",
            "total_rest": self.clean_cell(row.get("total_rest")) or "0",
        }

    def process_data(self) -> List[Dict[str, Any]]:
        """Parse and validate all attendance rows from the data payload.

        Returns:
            List of validated attendance dicts.
        """
        validated: List[Dict[str, Any]] = []
        rows: List[Any] = self._data.get("rows", [])
        for idx, row in enumerate(rows):
            if not isinstance(row, dict):
                self.add_error(idx, "row is not a dict")
                continue
            cleaned: Optional[Dict[str, Any]] = self.validate_row(row, idx)
            if cleaned is not None:
                self._imported_count += 1
                validated.append(cleaned)
        logger.info(
            "%s: processed %d/%d rows",
            self._name,
            self._imported_count,
            len(rows),
        )
        return validated

    def run(self) -> None:
        """Read stdin, process attendance data, write result to stdout."""
        try:
            self.read_stdin()
            rows: List[Dict[str, Any]] = self.process_data()
            self.write_result(rows)
        except Exception as exc:
            logger.error("%s: fatal — %s", self._name, exc)
            self._errors.append(f"Fatal: {exc}")
            self.write_result([])


if __name__ == "__main__":
    AttendanceImporter().run()
