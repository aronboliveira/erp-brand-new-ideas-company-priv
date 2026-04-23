# Batch 17 — Finance Seeder Fixes

## Date: 2026-02-10

## Commit: e1ad2ea9 (branch: agent)

### Root Cause Analysis

All finance models (Invoice, Revenue, Payment, Expense, Goal) have `created_by`
in their `$guarded` array. When seeders call `Model::create($data)`, Laravel's
mass-assignment protection silently strips the `created_by` field. The
`HasAuditFields` trait only sets `created_by` via the `creating` model event when
`auth()->check()` returns true — which is false during CLI seeding. Result: NOT
NULL constraint violation → caught by per-row try/catch → swallowed → 0 records.

### Fixes Applied

#### 1. DatabaseSeeder.php — Model::unguard()

```bash
cd _inc/laravel
# Verify the fix
grep -n "unguard\|reguard" database/seeders/DatabaseSeeder.php
```

Added `Model::unguard()` at start of `run()` and `Model::reguard()` at end.
Standard Laravel pattern for seeding guarded models.

#### 2. ExpenseSeeder.php — Add created_by

Added `DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID` and `DC::COL_TABLE_UPDATER =>
DC::DEFAULT_UUID` to the `createOneExpenseVariation()` Expense::create() payload.

#### 3. PaymentSeeder.php — Fix created_by

Changed `DC::COL_TABLE_CREATOR => $this->maybe($userIds)` (could return null) to
`DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID`. Same for `COL_TABLE_UPDATER`.

#### 4. InvoicePaymentSeeder.php — Fix created_by + break 2

- Changed `$maybe(fn() => ...)` closure to `DC::DEFAULT_UUID`
- **Critical**: Changed two `return;` statements (time limit + hard cap checks)
  to `break 2;` — the `return` was exiting the method BEFORE `DB::transaction()`
  insert block at the bottom, causing all collected `$rows` to be lost

### Verification Commands

```bash
cd _inc/laravel

# Full re-seed
php artisan migrate:fresh --seed --force

# Verify counts
php artisan tinker --execute="
\$tables = ['invoices','revenues','payments','expenses','goals','leads','bills',
  'deals','contracts','invoice_payments','journal_entries','budgets'];
foreach (\$tables as \$t) {
  \$c = \Illuminate\Support\Facades\DB::table(\$t)->count();
  echo str_pad(\$t, 25) . \$c . PHP_EOL;
}
"

# Verify SA ownership
php artisan tinker --execute="
\$sa = 'a3e8f4b2-7c1d-4f5a-9b0c-82d6e1f3a5b7';
\$tables = ['invoices','revenues','payments','expenses','goals','bills','leads','deals','contracts'];
foreach (\$tables as \$t) {
  \$total = \Illuminate\Support\Facades\DB::table(\$t)->count();
  \$owned = \Illuminate\Support\Facades\DB::table(\$t)->where('created_by', \$sa)->count();
  echo str_pad(\$t, 18) . 'total=' . str_pad(\$total, 5) . 'SA_owned=' . \$owned . PHP_EOL;
}
"

# PHPUnit (unit tests only — NEVER run full test suite, it wipes DB)
php artisan test --testsuite=Unit --filter="Enum" 2>&1 | tail -5
php artisan test --filter="VerifyCsrfTokenTest" --testsuite=Unit 2>&1 | tail -5
```

### Verified Results

| Table            | Before        | After | SA-Owned |
| ---------------- | ------------- | ----- | -------- |
| invoices         | 0             | 4     | 4        |
| revenues         | 0             | 16    | 16       |
| payments         | 0             | 15    | 15       |
| expenses         | 0             | 8     | 8        |
| goals            | TABLE_MISSING | 2     | 2        |
| invoice_payments | 0             | 2     | —        |
| leads            | 12            | 24    | 24       |
| bills            | 2             | 2     | 2        |
| deals            | 12            | 12    | 12       |
| contracts        | 9             | 9     | 9        |
| journal_entries  | —             | 11    | —        |
| budgets          | —             | 2     | —        |

### PHPUnit

- Enums: 590/590 pass
- VerifyCsrfToken (unit): 8/8 pass

### Known Remaining Issues

- `timesheets` = 0 — TimesheetSeeder has `HARD_CAP=2` with `MULTIPLE=64`;
  `roundDownToMultiple(2, 64) = 0` → target=0 (non-critical, math edge case)
- Other seeders with nullable created_by (VendorSeeder, AnnouncementSeeder, etc.)
  work because those tables allow NULL created_by
- InvoiceSeeder/RevenueSeeder already had DC::DEFAULT_UUID from previous session
