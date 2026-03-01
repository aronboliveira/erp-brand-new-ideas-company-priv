# 06 — GIT HISTORY

## Branch: `agent`

All work is on this branch. Each "batch" = one logical set of fixes.

| Batch  | Commit         | Summary                                                                                                                         |
| ------ | -------------- | ------------------------------------------------------------------------------------------------------------------------------- |
| 1      | `2336c2c0`     | Guest locale persistence via cookie + sync on login                                                                             |
| 2      | `931ee7fb`     | Footer CTA section + CSS on landing layout                                                                                      |
| 3      | `d71b08b4`     | Rewrite checkMounted.js without ES2022 private class fields                                                                     |
| 4      | `396ac8fa`     | pt-br Register translation fix                                                                                                  |
| 5      | `fec6e30b`     | Silence ERPGuard console errors, restore logo onerror, fix login locale                                                         |
| 6      | `dee8ae01`     | Fix custompage header, restore guest middleware, fix login locale var, silence JS errors (39 files), add AccountStatementSeeder |
| 7      | `4d401f0a`     | Fix void() JS syntax errors (38 files), fix account-statement 5xx, add DashboardSeeder                                          |
| 8      | `15219475`     | Stabilise all 26 main parameterless routes (35 fixes)                                                                           |
| 9      | `b9f5062a`     | Restore dashboard/report mock data, fix locale picker, fix all 18 report routes                                                 |
| 10     | `bb705fa3`     | Fix locale ordering, dashboard cards, goals, log noise, DataTables                                                              |
| 11     | `8b9b69cd`     | Strip ~57 noise middleware calls + fix 3 route bugs                                                                             |
| 12     | `9d21015d`     | Purge excessive logging & add exception dedup                                                                                   |
| 13     | `4e22ff7b`     | Seed projects/tasks for SA + more log cleanup                                                                                   |
| **14** | **`290c76cc`** | **Fix task-boards redirect loop, empty taskboard, loading spinners**                                                            |
| 15-16  | `various`      | Session batch 15/16 — misc fixes                                                                                                |
| 17     | `various`      | Finance seeders                                                                                                                 |
| 18-19  | `various`      | Infrastructure / migration fixes                                                                                                |
| 20     | `various`      | Proposals views fixes                                                                                                           |
| 21     | `various`      | HRM guard fixes                                                                                                                 |
| 22     | `00b9a795`     | Fix 9 broken HRM/commerce routes + saturation_deductions blade                                                                  |
| 22docs | `bf67b28e`     | Batch 22 docs — HRM/commerce route fixes documentation                                                                          |
| **23** | **`9f519c91`** | **Fix report sub-routes, exports, PDF generation, Python export wiring (12 files)**                                             |
| 24     | various        | Security fixes (RCE, XSS, IPN, eval, SQL injection, rich-text purifier) + DB rebuild                                           |
| 25     | various        | PHPUnit Feature + Playwright failure fixes, ProjectStagesTest, LoginRequest HTTPS                                               |
| 26     | various        | 28 mock HTML+JS route test pages (1532 routes), Jest 376 tests, Playwright spec                                                 |
| 27     | `d19468a3`     | Comprehensive route smoke test framework                                                                                        |
| 28     | `3d67d687`     | Fix budgets/create 500 and debit_notes SQL error                                                                                |
| 29     | `6ea8febc`     | PHP 8 nested ternary in Blade views, ContractNotes class collision, finance-render selector                                     |
| 30     | `9e7d78eb`     | Resolve all pre-existing PHPUnit Feature + Playwright failures                                                                  |
| 31     | `6f4e49f1`     | i18n locale bugs + 204 translation tests (Playwright 69 + Jest 135)                                                            |
| 32     | `aaef3f39`     | Resolve expense create page failures (3 bugs)                                                                                   |
| 33     | `4a3b7ca4`     | Add missing public consts + fix snake_case methods + update routes to use constants                                             |
| 34     | `faec8ce3`     | Resolve all 9 PHPStan level-5 errors                                                                                           |
| 35     | `407b1bb6`     | DealController ModelNotFoundException → 500 fixed                                                                               |
| 36     | `c10f971d`     | 3 orphan ProjectController routes + 35 missing consts + 96 const refs + PurchaseController 12×                                  |
| 37     | `557aa8ae`     | Singleton bootstrap architecture + refactor 27 route files                                                                      |
| **38** | **`3cc44472`** | **ESLint flat config + zero-error pass on core & route JS (HEAD)**                                                              |

### Batch 23 details

Files changed:

1. `ReportController.php` — warehouse import, int|string type hint, 8x null-safe json_decode
2. `routes/web.php` — reorder exports before resource(), remove 3 duplicate routes
3. `InvoiceExport.php` — 'invoices' → 'rows' key for Python export
4. `InvoiceController.php` — BinaryFileResponse import, Python export mode, return type widening
5. `template1.blade.php` — DNS2D instance instead of broken static facade
6. `EmployeeController.php` — 8 methods int→int|string for UUID acceptance
7. `ExtendingLayoutsConstants.php` — CTC missing dot separator
8. `contract_header.blade.php` — $meta_title/$meta_desc null defaults
9. `Bill.php` — items() HasMany relationship
10. `Utility.php` — billItemStats() method + nullable creatorId
11. `ViewsConstants.php` — BIL_TMP trailing dot
12. `PayslipController.php` — month fallback + view name snake_case fix

## Stashed changes

No active stashes. The previous stash `other-agent-changes-b14-review` was
dropped after all valid SA type fixes were incorporated into batches 15–38.
See `09_ACTIVE_STASH.md` for historical reference.

## Committing convention

```bash
cd /path/to/erp_prestech
git add _inc/laravel/path/to/file1 _inc/laravel/path/to/file2
git commit -m "Batch N — short summary

- bullet point details
- more details"
```
