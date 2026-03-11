from __future__ import annotations

import re
from pathlib import Path

from conftest import EXPORT_WRAPPER_DIR, EXPORTS_PY_DIR, IMPORT_WRAPPER_DIR, IMPORTS_PY_DIR


EXPECTED_MISSING_EXPORT_BACKENDS: set[str] = set()

EXPECTED_MISSING_IMPORT_BACKENDS: set[str] = set()


def _backend_name_from_endpoint(wrapper_file: Path) -> str:
    content = wrapper_file.read_text(encoding="utf-8")
    match = re.search(r"/api(?:/python)?/([a-z_]+)", content)
    assert match, f"Could not find backend endpoint in {wrapper_file.name}"
    endpoint_name = match.group(1)
    if endpoint_name.endswith("_export"):
        return endpoint_name.replace("_export", "_exporter.py")
    if endpoint_name.endswith("_import"):
        return endpoint_name.replace("_import", "_importer.py")
    raise AssertionError(f"Unsupported endpoint shape in {wrapper_file.name}: {endpoint_name}")


def test_all_implemented_exporters_have_php_wrappers() -> None:
    implemented = {path.name for path in EXPORTS_PY_DIR.glob("*_exporter.py") if path.name != "base_exporter.py"}
    wrappers = {_backend_name_from_endpoint(path) for path in EXPORT_WRAPPER_DIR.glob("*.php")}

    assert implemented - wrappers == set()


def test_all_implemented_importers_have_php_wrappers() -> None:
    implemented = {path.name for path in IMPORTS_PY_DIR.glob("*_importer.py") if path.name != "base_importer.py"}
    wrappers = {_backend_name_from_endpoint(path) for path in IMPORT_WRAPPER_DIR.glob("*.php")}

    assert implemented - wrappers == set()


def test_php_wrapper_backend_gap_snapshot_is_current() -> None:
    export_wrappers = {_backend_name_from_endpoint(path) for path in EXPORT_WRAPPER_DIR.glob("*.php")}
    import_wrappers = {_backend_name_from_endpoint(path) for path in IMPORT_WRAPPER_DIR.glob("*.php")}
    implemented_exports = {path.name for path in EXPORTS_PY_DIR.glob("*_exporter.py") if path.name != "base_exporter.py"}
    implemented_imports = {path.name for path in IMPORTS_PY_DIR.glob("*_importer.py") if path.name != "base_importer.py"}

    missing_exports = export_wrappers - implemented_exports
    missing_imports = import_wrappers - implemented_imports

    assert missing_exports == EXPECTED_MISSING_EXPORT_BACKENDS
    assert missing_imports == EXPECTED_MISSING_IMPORT_BACKENDS
