# Shell Commands — 2026-02-10 — Batch 23 Route/Export/PDF Testing

## Authentication

```bash
# Login and capture session cookies
CSRF=$(curl -sSc /tmp/erp_cookies10.txt http://127.0.0.1:8000/login \
  | grep '_token' | head -1 | sed 's/.*value="\([^"]*\)".*/\1/')

curl -sS -b /tmp/erp_cookies10.txt -c /tmp/erp_cookies10.txt \
  -X POST http://127.0.0.1:8000/login \
  -d "_token=$CSRF&email=suporte@brandnewideascompany.com&password=TestPass123!"
```

## GET Route Testing

```bash
# Test a GET route for status code and response size
curl -sS -o /dev/null -w "%{http_code} %{size_download}" \
  -b /tmp/erp_cookies10.txt http://127.0.0.1:8000/invoices

# Test multiple main routes
for route in trainers allowance_options training_types announcement invoices \
  deduction_options loan_options saturation_deductions warehouse_transfers; do
  CODE=$(curl -sS -o /dev/null -w "%{http_code}" -b /tmp/erp_cookies10.txt \
    "http://127.0.0.1:8000/$route")
  echo "$route => $CODE"
done

# Test report routes
for route in "reports/invoice-report" "reports/invoice-summary" \
  "reports-warehouse" "reports-payroll" "reports-leave" "reports-monthly-attendance"; do
  CODE=$(curl -sS -o /dev/null -w "%{http_code}" -b /tmp/erp_cookies10.txt \
    "http://127.0.0.1:8000/$route")
  echo "$route => $CODE"
done
```

## Export Route Testing (GET)

```bash
# Test GET export routes (expect 200 + XLSX content type)
for route in invoices/export leaves/export employees/export \
  product_stocks/export reports/payrolls/export; do
  curl -sS -o /tmp/test_export.xlsx -w "%{http_code} %{size_download} %{content_type}" \
    -b /tmp/erp_cookies10.txt "http://127.0.0.1:8000/$route"
  echo " => $route"
  file /tmp/test_export.xlsx
done
```

## Export Route Testing (POST with CSRF)

```bash
# For POST export routes, get CSRF first
CSRF=$(curl -sS -b /tmp/erp_cookies10.txt http://127.0.0.1:8000/payslips \
  | grep '_token' | head -1 | sed 's/.*value="\([^"]*\)".*/\1/')

# Test POST export
curl -sS -o /tmp/test_export.xlsx -w "%{http_code} %{size_download} %{content_type}" \
  -b /tmp/erp_cookies10.txt -X POST \
  -d "_token=$CSRF" http://127.0.0.1:8000/payslips/export

# POST export routes tested:
# payslips/export, balance-sheets/export, sales/export,
# receivables/export, trial-balances/export
```

## PDF Route Testing

```bash
# Encrypt an ID for PDF URLs (most PDF routes use Crypt::encrypt)
cd /workspace/erp/_inc/laravel

# Invoice PDF
ENC=$(php artisan tinker --execute="echo \Illuminate\Support\Facades\Crypt::encrypt('10b52a5d-5499-4aba-9eb8-1d83c61d5391');")
curl -sS -o /dev/null -w "%{http_code} %{size_download}" \
  -b /tmp/erp_cookies10.txt "http://127.0.0.1:8000/invoices/pdf/$ENC"

# Bill PDF
ENC=$(php artisan tinker --execute="echo \Illuminate\Support\Facades\Crypt::encrypt('6dad97da-a1fc-43a4-8427-25f0f61cfa65');")
curl -sS -o /dev/null -w "%{http_code} %{size_download}" \
  -b /tmp/erp_cookies10.txt "http://127.0.0.1:8000/bills/pdf/$ENC"

# Employee PDF (uses raw UUID, no encryption)
curl -sS -o /dev/null -w "%{http_code} %{size_download}" \
  -b /tmp/erp_cookies10.txt \
  "http://127.0.0.1:8000/employees/34026c75-ac6b-4fb3-9a88-2071a48701ab/joining-letter-pdf"
```

## Python Export Testing

```bash
# Test Python export engine for invoices
curl -sS -o /tmp/test_py_export.xlsx -w "%{http_code} %{size_download} %{content_type}" \
  -b /tmp/erp_cookies10.txt \
  "http://127.0.0.1:8000/invoices/export?engine=python"

# Verify the XLSX file is valid
file /tmp/test_py_export.xlsx
python3 -c "import openpyxl; wb=openpyxl.load_workbook('/tmp/test_py_export.xlsx'); print(wb.sheetnames)"
```

## Error Log Monitoring

```bash
# Tail latest error log for debugging
tail -50 storage/logs/error-$(date +%Y-%m-%d).log

# Watch for new errors during testing
tail -f storage/logs/error-$(date +%Y-%m-%d).log
```
