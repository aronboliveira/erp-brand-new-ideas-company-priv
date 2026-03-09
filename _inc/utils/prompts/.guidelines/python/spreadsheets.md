# Spreadsheet Processing Guidelines

## Dependencies

Use **pandas** for all dataframing operations and **openpyxl** as the Excel engine. These two libraries form the foundation of all spreadsheet processing in this codebase.

```python
import pandas as pd
from openpyxl import load_workbook
from openpyxl.styles import Font, PatternFill, Border, Side, Alignment
```

## Formatting

### Headers

- Headers must be **bold** using `openpyxl.styles.Font(bold=True)`.
- The header row must be **frozen** (freeze panes) so it remains visible during scrolling.
- The header row background must use a **"cold" colour** — one with a low R (red) value. Examples: steel blue (`4472C4`), slate (`5B6F8A`), teal (`2E86AB`).

```python
from openpyxl.styles import Font, PatternFill

header_font = Font(bold=True, color="FFFFFF")
header_fill = PatternFill(start_color="2E5A88", end_color="2E5A88", fill_type="solid")
ws.freeze_panes = "A2"
```

### Row Colours

Rows (non-header) should have **opaque** background colours for readability. Use alternating fills for even/odd rows to enhance scanning.

### Borders

| Border Type   | Style                                                         |
|---------------|---------------------------------------------------------------|
| Horizontal    | Near-transparent — thin, light grey. Subtle row separation.   |
| Vertical      | Opaque grey for data rows, deep grey/black for header columns.|

```python
from openpyxl.styles import Border, Side

thin_horizontal = Side(style="hair", color="D0D0D0")
opaque_vertical = Side(style="thin", color="808080")
header_vertical = Side(style="thin", color="333333")

data_border = Border(
    top=thin_horizontal,
    bottom=thin_horizontal,
    left=opaque_vertical,
    right=opaque_vertical
)
```

## NaN Handling

All `NaN` and `nan` values must be treated as **empty values**. When writing to Excel, convert them to empty strings or leave cells blank. When reading, use `pd.isna()` to detect them uniformly.

```python
df = df.fillna("")  # Before writing to xlsx
```

## Header Detection

When parsing spreadsheets with unknown structure, detect headers by recognizing **typical form fields**:

- Personal: name, first_name, last_name, age, gender, date_of_birth
- Organizational: role, title, department, position, employee_id
- Temporal: created_at, updated_at, date, timestamp, start_date, end_date

A row is considered a header candidate if it contains **at least two filled cells** that match known field patterns. This heuristic handles sheets where the header is not in row 1.

## Multi-Table Extraction

A single worksheet may contain **multiple data tables** separated by empty rows or columns. The extraction logic must:

1. Scan for distinct header rows (using the detection heuristic above).
2. Determine the bounding box of each table (start row, end row, start col, end col).
3. Extract each table as an independent DataFrame.
4. Apply **correction factors for indexes** — when working with multiple subtables, row/column indexes from the original sheet must be offset to produce correct zero-based DataFrame indexes.

## Derivative Cells (Formulas)

Cells that are derived from other data must be assigned as **Excel formula expressions**, not computed Python values:

```python
# Correct — writes an Excel formula
ws["E2"] = "=SUM(B2:D2)"
ws["F2"] = '=VLOOKUP(A2,Sheet2!A:B,2,FALSE)'

# Incorrect — writes a static value
ws["E2"] = sum([ws["B2"].value, ws["C2"].value, ws["D2"].value])
```

Supported formula types: `SUM`, `VLOOKUP`, `HLOOKUP`, `INDEX/MATCH`, `COUNTIF`, `SUMIF`, `AVERAGE`, and array functions (`FILTER`, `SORT`, `UNIQUE` for modern Excel).

## Output Options

After processing, always **offer to save results as `.json`** in addition to `.xlsx`. Use `df.to_json(orient="records", indent=2)` for readable output. This enables downstream systems to consume the data without Excel dependencies.
