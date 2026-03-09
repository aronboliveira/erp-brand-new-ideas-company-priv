# Batch 23 — Report Sub-Routes, Exports, PDF Generation & Python Export Wiring

**Date**: 2026-02-10
**Commit**: `9f519c91`
**Branch**: `agent`

---

## Test Methodology

### Authentication Setup

All tests were run against a local dev server (`http://127.0.0.1:8000`, PID 1879582) using `curl` with a persistent cookie jar at `/tmp/erp_cookies10.txt`. Authentication was established by:

1. **Fetching CSRF token** from login page:

   ```bash
   CSRF=$(curl -sSc /tmp/erp_cookies10.txt http://127.0.0.1:8000/login \
     | grep '_token' | head -1 | sed 's/.*value="\([^"]*\)".*/\1/')
   ```

2. **POSTing login** with SA credentials:

   ```bash
   curl -sS -b /tmp/erp_cookies10.txt -c /tmp/erp_cookies10.txt \
     -X POST http://127.0.0.1:8000/login \
     -d "_token=$CSRF&email=suporte@prestech.com.br&password=TestPass123!"
   ```

3. **All subsequent requests** used `-b /tmp/erp_cookies10.txt` for session persistence.

### Route Testing Pattern

- **GET routes**: `curl -sS -o /dev/null -w "%{http_code} %{size_download}" -b /tmp/erp_cookies10.txt <URL>`
- **POST routes** (exports): Required a fresh CSRF token per request, extracted from a GET page load, then submitted via:
  ```bash
  curl -sS -o /dev/null -w "%{http_code} %{size_download} %{content_type}" \
    -b /tmp/erp_cookies10.txt -X POST \
    -d "_token=$CSRF" <URL>
  ```
- **PDF routes**: Required `Crypt::encrypt($id)` for URL construction. IDs were obtained via:
  ```bash
  php artisan tinker --execute="echo \Illuminate\Support\Facades\Crypt::encrypt('$UUID');"
  ```
- **Content verification**: Used `file --mime-type` on downloaded files, `wc -c` for sizes, and `head -c 4` to verify XLSX magic bytes (`PK` ZIP header).

### Python Export Testing

The Python export pipeline was tested by appending `?engine=python` to the invoice export URL:

```bash
curl -sS -o /tmp/test_py_export.xlsx -w "%{http_code} %{size_download} %{content_type}" \
  -b /tmp/erp_cookies10.txt \
  "http://127.0.0.1:8000/invoices/export?engine=python"
```

Then verified the output:

```bash
file /tmp/test_py_export.xlsx
# => Microsoft Excel 2007+
python3 -c "import openpyxl; wb=openpyxl.load_workbook('/tmp/test_py_export.xlsx'); print(wb.sheetnames)"
# => ['Invoices']
```

---

## Test Results

### 1. Main Routes (9/9 → 200)

| Route                  | HTTP | Size   |
| ---------------------- | ---- | ------ |
| /trainers              | 200  | ~98KB  |
| /allowance_options     | 200  | ~93KB  |
| /training_types        | 200  | ~93KB  |
| /announcement          | 200  | ~92KB  |
| /invoices              | 200  | ~172KB |
| /deduction_options     | 200  | ~93KB  |
| /loan_options          | 200  | ~93KB  |
| /saturation_deductions | 200  | ~98KB  |
| /warehouse_transfers   | 200  | ~102KB |

### 2. Report GET Routes (6/6 → 200)

| Route                       | HTTP | Size   |
| --------------------------- | ---- | ------ |
| /reports/invoice-report     | 200  | ~129KB |
| /reports/invoice-summary    | 200  | ~105KB |
| /reports-warehouse          | 200  | ~96KB  |
| /reports-payroll            | 200  | ~97KB  |
| /reports-leave              | 200  | ~97KB  |
| /reports-monthly-attendance | 200  | ~95KB  |

### 3. GET Export Routes (5/5 → 200, XLSX)

| Route                    | HTTP | Size  | Type             |
| ------------------------ | ---- | ----- | ---------------- |
| /invoices/export         | 200  | 6685B | application/xlsx |
| /leaves/export           | 200  | 6715B | application/xlsx |
| /employees/export        | 200  | 6648B | application/xlsx |
| /product_stocks/export   | 200  | 6718B | application/xlsx |
| /reports/payrolls/export | 200  | 6812B | application/xlsx |

### 4. POST Export Routes (5/5 → 200, XLSX)

| Route                  | HTTP | Size  | Type             |
| ---------------------- | ---- | ----- | ---------------- |
| /payslips/export       | 200  | 6712B | application/xlsx |
| /balance-sheets/export | 200  | 7688B | application/xlsx |
| /sales/export          | 200  | 6685B | application/xlsx |
| /receivables/export    | 200  | 6798B | application/xlsx |
| /trial-balances/export | 200  | 7746B | application/xlsx |

### 5. Python Export Integration (1/1 → 200)

| Route                          | HTTP | Size  | Type             | Verified                                   |
| ------------------------------ | ---- | ----- | ---------------- | ------------------------------------------ |
| /invoices/export?engine=python | 200  | 5322B | application/xlsx | ✅ openpyxl loads, sheet name = 'Invoices' |

### 6. PDF Routes (6/7 → 200)

| Route (via Crypt::encrypt)  | Record UUID                          | HTTP | Size  |
| --------------------------- | ------------------------------------ | ---- | ----- |
| /invoices/pdf/{enc}         | 10b52a5d-5499-4aba-9eb8-1d83c61d5391 | 200  | 6.5MB |
| /bills/pdf/{enc}            | 6dad97da-a1fc-43a4-8427-25f0f61cfa65 | 200  | 3.0MB |
| /contracts/pdf/{enc}        | 0bac486e-ae94-49dc-a521-5c2b9bc381c5 | 200  | 1.4MB |
| /employees/pdf/{id}         | 34026c75-ac6b-4fb3-9a88-2071a48701ab | 200  | 95KB  |
| /payslips/payslip-pdf/{enc} | 49da9aed-3ca6-4c54-bc71-14647b7b12d6 | 200  | 703KB |
| /proposals/pdf/{enc}        | 4b713781-d2e6-4c93-9370-9d87bf4bfe6b | 200  | 52KB  |
| /expenses/pdf/{enc}         | 1358d17f-baff-421c-90fe-fde4c985b83e | ⚠️   | N/A   |

> **Expense PDF**: Data integrity issue — the expense's `bill_id` FK references a non-existent Bill record. Not a code bug.

---

## Files Modified (12 files)

1. **app/Http/Controllers/Activity/ReportController.php** — warehouse import fix, `int|string` type hint, 8x null-safe `json_decode()` for payroll
2. **routes/web.php** — reorder export routes before `resource()` calls, remove 3 duplicate registrations
3. **app/Exports/InvoiceExport.php** — `'invoices'` → `'rows'` key for Python export data array
4. **app/Http/Controllers/Bills/InvoiceController.php** — `BinaryFileResponse` import, Python-aware export via `?engine=python`, return type widening, compact variable fix
5. **resources/views/invoices/templates/template1.blade.php** — QR barcode: `new \Milon\Barcode\DNS2D()` instance instead of broken static facade
6. **app/Http/Controllers/Individuals/EmployeeController.php** — 8 methods `int $id` → `int|string $id`, `renderLetter()` empId same
7. **app/Config/Constants/ExtendingLayoutsConstants.php** — `CTC` missing dot separator fix
8. **resources/views/layouts/contract_header.blade.php** — Added `$meta_title` / `$meta_desc` null defaults
9. **app/Models/Bills/Bill.php** — Added `items()` HasMany relationship to BillProduct
10. **app/Models/utils/Utility.php** — Implemented `billItemStats()` method, `prepareCommonViewData` accepts null `$creatorId`
11. **app/Config/Constants/ViewsConstants.php** — `BIL_TMP` trailing dot fix for template path concatenation
12. **app/Http/Controllers/Bills/PayslipController.php** — month fallback from `salary_month`, view name `payslipPdf` → `payslip_pdf`

---

## Known Remaining Issues (Not Blocking)

- **Expense PDF**: Data FK integrity — expense references non-existent Bill
- **POS report routes**: `daily-pos`, `monthly-pos`, `daily-purchase`, `monthly-purchase`, `pos-vs-purchase` have route definition errors (`Route [reports.pos_vs_purchase] not defined` etc.)
- **PhpSpreadsheet styling**: `Borders::getInsideHorizontal()` undefined in `LeaveReportExport` / `ProductStockExport` — exports still succeed, just logs a style error
- **DNS2D facade**: Still globally broken (only fixed in invoice `template1` via instance workaround). Other templates using `\DNS2D::` static calls will fail similarly
