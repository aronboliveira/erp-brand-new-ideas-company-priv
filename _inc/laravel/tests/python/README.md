# Python exporter/importer tests

This folder exercises the Python modules that back Laravel export and import flows.

Coverage includes:

- Base utility behavior in `app/Exports/py/base_exporter.py`
- Base utility behavior in `app/Imports/py/base_importer.py`
- End-to-end workbook generation for every implemented exporter
- Validation and result handling for `attendance_importer.py`
- Inventory checks for PHP `_withPython` wrappers that still have no Python backend

## Bootstrap

```bash
cd _inc/laravel/tests/python
./bootstrap-venv.sh
source .venv/bin/activate
pytest
```

## Notes

- The repo currently does not ship `pytest`, `pandas`, or `openpyxl` in the local environment.
- `test_python_wrapper_coverage.py` snapshots the current wrapper-to-backend gap so drift is visible.
- The workbook tests prefer `output_path` over stdout so the generated `.xlsx` files can be opened and asserted directly.

