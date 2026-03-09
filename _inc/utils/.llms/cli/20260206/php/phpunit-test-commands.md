# PHPUnit Test Commands — Export & Import Modules

## Run All Export + Import Tests

```bash
cd _inc/laravel
php artisan test --filter="Export|Import"
```

## Run Individual Test Suites

### Export Tests — per-class files

```bash
# Specific export (e.g. BillExportTest)
php artisan test --filter="BillExportTest"

# All 19 individual export tests
php artisan test --filter="AccountStatementExportTest|BalanceSheetExportTest|BillExportTest|CustomerExportTest|EmployeeExportTest|InvoiceExportTest|LeaveReportExportTest|PayrollExportTest|PayslipExportTest|ProductServiceExportTest|ProductStockExportTest|ProfitLossExportTest|ProposalExportTest|ReceivableExportTest|SalesReportExportTest|TaskReportExportTest|TransactionExportTest|TrialBalanceExportTest|VendorExportTest"
```

### Export Supplementary Tests (unauth guards, empty-data, performance, AfterSheet checks)

```bash
php artisan test --filter="ExportSupplementaryTest"
# 56 tests · 98 assertions
```

### Import Tests — per-class files

```bash
php artisan test --filter="AttendanceImportTest|CustomerImportTest|EmployessImportTest|ProductServiceImportTest|VendorImportTest"
```

### Import Supplementary Tests (empty-input, performance, Python delegation)

```bash
php artisan test --filter="ImportSupplementaryTest"
# 13 tests · 25 assertions
```

### Both Supplementary Suites

```bash
php artisan test --filter="SupplementaryTest"
# 69 tests · 123 assertions
```

## Test Output Filtering

```bash
# Compact — pass/fail summary only
php artisan test --filter="Export|Import" 2>&1 | grep -E "PASS|FAIL|Tests:"

# Count passing tests
php artisan test --filter="Export|Import" 2>&1 | grep -c "✓"
```

## Key Notes

- All tests use `DatabaseTransactions` (NOT `RefreshDatabase`)
- Current tally: **150 tests** across 26 test files
- Time limit constants: 5 s (exports), 5 s (imports), 50 MB memory delta
- Pre-existing `EmployessImportTest.php` filename has a typo (Employess)
