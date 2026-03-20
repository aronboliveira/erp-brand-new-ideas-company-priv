#!/usr/bin/env python3
"""Product/Service Importer — parses product/service spreadsheets via Python."""

import logging
from typing import Any, Dict, List, Optional


from base_importer import BaseImporter

logger: logging.Logger = logging.getLogger(__name__)


class ProductServiceImporter(BaseImporter):
    """Import product/service records from spreadsheet data.

    Performs dynamic header detection and snake_case normalisation
    to match the ProductService model's fillable fields.
    """

    EXPECTED_HEADERS: List[str] = [
        "name",
        "sku",
        "sale_price",
        "purchase_price",
        "quantity",
        "tax_id",
        "category_id",
        "unit_id",
        "type",
        "description",
    ]

    def __init__(self) -> None:
        """Initialise the ProductServiceImporter."""
        super().__init__("ProductServiceImporter")

    def get_expected_headers(self) -> List[str]:
        """Return expected product/service column headers."""
        return self.EXPECTED_HEADERS

    def validate_row(
        self, row: Dict[str, Any], row_idx: int
    ) -> Optional[Dict[str, Any]]:
        """Validate a single product/service row.

        Args:
            row:     Dict mapping header → value.
            row_idx: 0-based row index.

        Returns:
            Cleaned dict, or None if skipped.
        """
        name: Any = self.clean_cell(row.get("name"))
        if name is None:
            self.add_error(row_idx, "missing product/service name")
            return None
        cleaned: Dict[str, Any] = {"name": name}
        for header in self.EXPECTED_HEADERS:
            if header == "name":
                continue
            val: Any = self.clean_cell(row.get(header))
            if val is not None:
                cleaned[header] = val
        return cleaned

    def process_data(self) -> List[Dict[str, Any]]:
        """Parse and validate all product/service rows.

        Returns:
            List of validated product/service dicts.
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
        """Read stdin, process product/service data, write result to stdout."""
        try:
            self.read_stdin()
            rows: List[Dict[str, Any]] = self.process_data()
            self.write_result(rows)
        except Exception as exc:
            logger.error("%s: fatal — %s", self._name, exc)
            self._errors.append(f"Fatal: {exc}")
            self.write_result([])


if __name__ == "__main__":
    ProductServiceImporter().run()
