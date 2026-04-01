#!/usr/bin/env python3
"""Customer Importer — parses customer spreadsheets via Python."""

import logging
import sys
from typing import Any, Dict, List, Optional

import pandas as pd

from base_importer import BaseImporter, normalise_header, safe_get

logger: logging.Logger = logging.getLogger(__name__)


class CustomerImporter(BaseImporter):
    """Import customer records from spreadsheet data.

    Uses dynamic header detection to map spreadsheet columns to
    the Customer model's fillable fields.
    """

    EXPECTED_HEADERS: List[str] = [
        "name",
        "email",
        "contact",
        "billing_name",
        "billing_country",
        "billing_state",
        "billing_city",
        "billing_phone",
        "billing_zip",
        "billing_address",
        "shipping_name",
        "shipping_country",
        "shipping_state",
        "shipping_city",
        "shipping_phone",
        "shipping_zip",
        "shipping_address",
    ]

    def __init__(self) -> None:
        """Initialise the CustomerImporter."""
        super().__init__("CustomerImporter")

    def get_expected_headers(self) -> List[str]:
        """Return the expected customer column headers."""
        return self.EXPECTED_HEADERS

    def validate_row(
        self, row: Dict[str, Any], row_idx: int
    ) -> Optional[Dict[str, Any]]:
        """Validate a single customer row.

        Args:
            row:     Dict mapping header → value.
            row_idx: 0-based row index.

        Returns:
            Cleaned dict, or None if skipped.
        """
        name: Any = self.clean_cell(row.get("name"))
        if name is None:
            self.add_error(row_idx, "missing customer name")
            return None
        cleaned: Dict[str, Any] = {}
        for header in self.EXPECTED_HEADERS:
            val: Any = self.clean_cell(row.get(header))
            if val is not None:
                cleaned[header] = val
        if "name" not in cleaned:
            cleaned["name"] = name
        return cleaned

    def process_data(self) -> List[Dict[str, Any]]:
        """Parse and validate all customer rows.

        Returns:
            List of validated customer dicts.
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
        """Read stdin, process customer data, write result to stdout."""
        try:
            self.read_stdin()
            rows: List[Dict[str, Any]] = self.process_data()
            self.write_result(rows)
        except Exception as exc:
            logger.error("%s: fatal — %s", self._name, exc)
            self._errors.append(f"Fatal: {exc}")
            self.write_result([])


if __name__ == "__main__":
    CustomerImporter().run()
