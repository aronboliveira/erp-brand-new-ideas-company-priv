# Utility Delegation + Problems Panel Cleanup — 2026-03-15

## Overview

Major refactoring session: extracted 68 methods from `Utility.php` into 6 dedicated service classes, then resolved all VS Code Problems Panel errors to 0.

| Metric                 | Before       | After         |
| ---------------------- | ------------ | ------------- |
| Utility.php lines      | 4,282        | **1,828**     |
| Service classes        | 2 (existing) | **8** (6 new) |
| Intelephense errors    | 895+         | **0**         |
| Unused imports removed | —            | **30+**       |
| PHPStan Level 3        | 0            | **0**         |

## Service Classes Created

All under `app/Services/Utility/`:

### AccountingService (12 methods)

Chart of accounts, journal entries, trial balance, balance sheet, account statements.
Key methods: `getAccountBalance()`, `journalEntryData()`, `trialBalanceData()`, `balanceSheetData()`

### FileStorageService (10 methods)

File upload/download, storage settings, S3/Wasabi integration, file management.
Key methods: `uploadFile()`, `getFile()`, `getStorageSetting()`, `deleteFile()`

### FinanceBillingService (14 methods)

Invoices, bills, taxes, payments, proposals, revenue calculations.
Key methods: `invoiceNumberFormat()`, `billNumberFormat()`, `taxData()`, `totalQuantity()`

### LocalizationService (12 methods)

Languages, currency formatting, phone number formatting, date/time utilities.
Key methods: `languages()`, `getCurrency()`, `getPhoneNumberFormat()`, `secondToTime()`

### ModelLookupService (10 methods)

Settings lookups, plan feature checks, model finders, tenant resolution.
Key methods: `settingsById()`, `getSettingById()`, `getPlan()`, `getAdminPaymentSetting()`

### NotificationService (10 methods)

Email templates, Twilio SMS, Pusher configuration, notification dispatch.
Key methods: `sendEmailTemplate()`, `sendUserEmailTemplate()`, `sendTwilioSMS()`, `getPusherSetting()`

## Import Cleanup Summary

### Utility.php — Removed imports (30+)

**Constants:** `ActivityConstants`, `BillsConstants`, `CrmPipelineConstants`, `LanguageConstants`
**Models:** `BrazilState`, `UserType`, `ErrorHandler`, `CommonEmailTemplate`, `GoogleEvent`, `ChartOfAccountType`, `Customer`, `Deal`, `DealTask`, `Employee`, `Expense`, `GeneratedOfferLetter`, `Goal`, `Invoice`, `Lead`, `Order`, `Pipeline`, `ProductService`, `Project`, `ProjectTask`, `Proposal`, `Purchase`, `StockReport`, `Vendor`, `WarehouseProduct`
**Facades:** `Artisan`, `Cache`, `File`, `Schema`, `Storage`, `Validator`
**Others:** `ModelNotFoundException`, `FilesystemAdapter`, `TwilioClient`

### Utility.php — Final imports (kept)

**Constants (8):** CTC, DC, EC, FC, PMC, PJC, SC, UC
**Models (15):** BankAccount, BillAccount, BillPayment, BillProduct, Budget, Indicator, InvoicePayment, InvoiceProduct, JournalItem, Payment, Plan, ProductService, Revenue, Tax, User
**Services (8):** AccountingService, CalendarService, FileStorageService, FinanceBillingService, LocalizationService, ModelLookupService, NotificationService, TenantSetupService
**Framework:** Carbon, CarbonPeriod, Faker, Model, BelongsTo, QueryException, RedirectResponse, Request, Collection, Str
**Facades (8):** App, Auth, Config, DB, Http, Lang, Log, Route
**Helpers:** SafeConsoleOutput, ChecksLogin trait

## Type / IDE Fixes

| File                             | Issue                                | Fix                                 |
| -------------------------------- | ------------------------------------ | ----------------------------------- |
| `LocalizationService.php`        | `str_pad()` needs string 1st arg     | `(string) rand(0, 9999999)`         |
| `LocalizationService.php`        | Comparison with int needs cast       | `(int)$areaCode`                    |
| `FinanceBillingService.php`      | Unused UC, Product, Auth             | Removed imports                     |
| `UtilityTest.php`                | Unused GoogleEvent                   | Removed import                      |
| `UtilityTest.php`                | `assertMissing` not found on Storage | Extracted to `$disk` with `@var`    |
| `ProductServiceCategoryTest.php` | Mockery method not found             | Added `@var ProductServiceCategory` |
| `ProjectTaskTest.php`            | Missing DB import                    | Added to use statement              |
| `GeneratedOfferLetterTest.php`   | Missing required arg                 | Added `$createdBy` parameter        |
| `ReportController.php`           | Missing BC alias                     | Added `BillsConstants as BC`        |
| `SetSalaryController.php`        | Missing JsonResponse                 | Added to import group               |
| `Proposal.php`                   | Wrong namespace prefix               | `\Utility::` → `Utility::`          |

## Test Infrastructure

- **ChartOfAccountType** `id` is guarded — must use `$rec = new ChartOfAccountType(); $rec->id = $id; $rec->saveQuietly();`
- **Number format prefixes**: 13+ assertions fixed (`#` → `INV-`, `BILL-`, etc.)
- **mysql-schema.sql**: 72 false-positive SQL errors suppressed via `.vscode/settings.json` file association

## Related Files Modified

- `app/Models/utils/Utility.php`
- `app/Services/Utility/AccountingService.php`
- `app/Services/Utility/FileStorageService.php`
- `app/Services/Utility/FinanceBillingService.php`
- `app/Services/Utility/LocalizationService.php`
- `app/Services/Utility/ModelLookupService.php`
- `app/Services/Utility/NotificationService.php`
- `app/Services/Utility/TenantSetupService.php`
- `tests/Unit/app/Models/utils/UtilityTest.php`
- `tests/Unit/app/Models/products/ProductServiceCategoryTest.php`
- `tests/Unit/app/Models/planning/ProjectTaskTest.php`
- `tests/Unit/app/Models/ssr/GeneratedOfferLetterTest.php`
- `app/Http/Controllers/Activity/ReportController.php`
- `app/Http/Controllers/Planning/SetSalaryController.php`
- `app/Models/Planning/Proposal.php`
- `.vscode/settings.json`
