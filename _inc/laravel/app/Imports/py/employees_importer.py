#!/usr/bin/env python3
"""Employees Importer — parses employee spreadsheets via Python."""

import logging
from typing import Any, Dict, List, Optional


from base_importer import BaseImporter

logger: logging.Logger = logging.getLogger(__name__)


class EmployeesImporter(BaseImporter):
    """Import employee records from spreadsheet data.

    Requires at least name, email, and employee_id columns.
    """

    EXPECTED_HEADERS: List[str] = [
        "name",
        "email",
        "employee_id",
        "phone",
        "dob",
        "gender",
        "address",
        "branch_id",
        "department_id",
        "designation_id",
        "company_doj",
        "account_holder_name",
        "account_number",
        "bank_name",
        "bank_identifier_code",
        "branch_location",
        "tax_payer_id",
        "salary_type",
        "salary",
    ]

    REQUIRED_FIELDS: List[str] = ["name", "email", "employee_id"]

    def __init__(self) -> None:
        """Initialise the EmployeesImporter."""
        super().__init__("EmployeesImporter")

    def get_expected_headers(self) -> List[str]:
        """Return expected employee column headers."""
        return self.EXPECTED_HEADERS

    def validate_row(
        self, row: Dict[str, Any], row_idx: int
    ) -> Optional[Dict[str, Any]]:
        """Validate a single employee row.

        Args:
            row:     Dict mapping header → value.
            row_idx: 0-based row index.

        Returns:
            Cleaned dict, or None if skipped.
        """
        for field in self.REQUIRED_FIELDS:
            val: Any = self.clean_cell(row.get(field))
            if val is None:
                self.add_error(row_idx, f"missing required field: {field}")
                return None
        cleaned: Dict[str, Any] = {}
        for header in self.EXPECTED_HEADERS:
            val = self.clean_cell(row.get(header))
            if val is not None:
                cleaned[header] = val
        return cleaned

    def process_data(self) -> List[Dict[str, Any]]:
        """Parse and validate all employee rows.

        Returns:
            List of validated employee dicts.
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
        """Read stdin, process employee data, write result to stdout."""
        try:
            self.read_stdin()
            rows: List[Dict[str, Any]] = self.process_data()
            self.write_result(rows)
        except Exception as exc:
            logger.error("%s: fatal — %s", self._name, exc)
            self._errors.append(f"Fatal: {exc}")
            self.write_result([])


if __name__ == "__main__":
    EmployeesImporter().run()
