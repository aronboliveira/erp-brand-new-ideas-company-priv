#!/usr/bin/env python3
"""Vendor Importer — parses vendor spreadsheets via Python."""

import logging
import sys
from typing import Any, Dict, List, Optional

import pandas as pd

from base_importer import BaseImporter, safe_get

logger: logging.Logger = logging.getLogger(__name__)


class VendorImporter(BaseImporter):
    """Import vendor records from spreadsheet data.

    Expected fields match the Vendor model's fillable columns.
    Passwords are returned as-is; PHP will handle hashing.
    """

    EXPECTED_HEADERS: List[str] = [
        "vendor_id",
        "name",
        "email",
        "password",
        "contact",
        "avatar",
        "is_active",
        "created_by",
        "email_verified_at",
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

    REQUIRED_FIELDS: List[str] = ["name", "email"]

    def __init__(self) -> None:
        """Initialise the VendorImporter."""
        super().__init__("VendorImporter")

    def get_expected_headers(self) -> List[str]:
        """Return expected vendor column headers."""
        return self.EXPECTED_HEADERS

    def validate_row(
        self, row: Dict[str, Any], row_idx: int
    ) -> Optional[Dict[str, Any]]:
        """Validate a single vendor row.

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
        """Parse and validate all vendor rows.

        Returns:
            List of validated vendor dicts.
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
        """Read stdin, process vendor data, write result to stdout."""
        try:
            self.read_stdin()
            rows: List[Dict[str, Any]] = self.process_data()
            self.write_result(rows)
        except Exception as exc:
            logger.error("%s: fatal — %s", self._name, exc)
            self._errors.append(f"Fatal: {exc}")
            self.write_result([])


if __name__ == "__main__":
    VendorImporter().run()
