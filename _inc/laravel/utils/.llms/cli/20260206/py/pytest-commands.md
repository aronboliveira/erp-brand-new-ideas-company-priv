# Python Test Commands — Export & Import Modules

## Prerequisites

```bash
# Install test runner + timeout plugin (PEP 668 system — needs --break-system-packages)
pip3 install --break-system-packages pytest pytest-timeout

# Verify installation
python3 -m pytest --version
# pytest 9.0.2
```

## Run Full Python Suite

```bash
cd _inc/laravel
python3 -m pytest tests/python/ -v --tb=short
# 215 tests · 0.67 s
```

## Run Export Tests Only

```bash
# All export tests (base + 19 concrete)
python3 -m pytest tests/python/exports/ -v --tb=short
# 142 tests

# Base exporter unit tests
python3 -m pytest tests/python/exports/test_base_exporter.py -v
# 42 tests — ExportStyle constants, safe_get, format_date, format_currency,
#            BaseExporter init, read_stdin, create_workbook, styles, output

# Concrete exporter parametrised tests
python3 -m pytest tests/python/exports/test_exporters.py -v
# 100 tests — headings, process_data, export end-to-end, performance,
#             SalesReport customer mode, Receivable total, Payroll status
```

## Run Import Tests Only

```bash
# All import tests (base + 5 concrete)
python3 -m pytest tests/python/imports/ -v --tb=short
# 73 tests

# Base importer unit tests
python3 -m pytest tests/python/imports/test_base_importer.py -v
# 31 tests — safe_get, normalise_header, parse_date, clean_cell,
#            add_error, write_result, read_rows_from_data, end-to-end

# Concrete importer parametrised tests
python3 -m pytest tests/python/imports/test_importers.py -v
# 42 tests — headers, validate_row, process_data, run, performance,
#             non-dict row handling
```

## Useful Flags

```bash
# Run a specific test function
python3 -m pytest tests/python/exports/test_base_exporter.py::TestExportStyle::test_header_fill_color -v

# Run all performance tests only
python3 -m pytest tests/python/ -v -k "performance"

# Stop on first failure
python3 -m pytest tests/python/ -x

# Show local variables on failure
python3 -m pytest tests/python/ -v --tb=long -l

# Parallel (requires pytest-xdist)
# pip3 install --break-system-packages pytest-xdist
# python3 -m pytest tests/python/ -n auto
```

## Directory Layout

```
tests/python/
├── conftest.py               # sys.path injection for app/Exports/py + app/Imports/py
├── exports/
│   ├── __init__.py
│   ├── test_base_exporter.py  # 42 tests
│   └── test_exporters.py      # 100 tests
└── imports/
    ├── __init__.py
    ├── test_base_importer.py  # 31 tests
    └── test_importers.py      # 42 tests
```

## Key Notes

- Tests run against Python 3.12.3 (system)
- conftest.py automatically wires `app/Exports/py/` and `app/Imports/py/` via `sys.path`
- Performance tests use `pytest.mark.timeout(10)` as upper bound
- All concrete exporter/importer tests are **parametrised** — adding a new Python script
  just requires adding one entry to the `EXPORTER_REGISTRY` / `IMPORTER_REGISTRY` dict
