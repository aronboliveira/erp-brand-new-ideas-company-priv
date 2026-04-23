# Batch 20 — Proposals + Views + Type Case + Seeder Fixes

## Date: 2026-02-10

## Commit: bbba319b (branch: agent)

### Summary

Fixed proposal page rendering (3 separate bugs), multiple view blade issues,
controller type case mismatches, and seeder `created_by` forcings. All 20
finance+CRM routes verified via curl with non-empty grids.

---

### Bug 1: ProposalController Guard Pattern (12 occurrences)

**Root Cause:** `guard()` returns `true` (boolean) on authorization success, but
code `if ($c = $this->guard(...))` always enters the if-block since `true` is
truthy. The controller returned boolean `true` (rendered as "1") instead of
reaching the query/view logic.

**Fix:** Changed all 12 occurrences to `if (($c = $this->guard(...)) !== true)`.

**File:** `app/Http/Controllers/Planning/ProposalController.php`

---

### Bug 2: Missing `createdBy`/`updatedBy` Relationships on Lead Model

**Root Cause:** `Lead` model has `$with = ['createdBy', 'updatedBy', ...]` but
`HasAuditFields` trait only defined `creator()` and `updater()` methods.
Eager-loading `createdBy` threw `Call to undefined relationship`.

**Fix:** Added `createdBy()` and `updatedBy()` alias methods to `HasAuditFields`
trait, returning `belongsTo(User::class, DC::COL_TABLE_CREATOR)` and
`belongsTo(User::class, DC::COL_TABLE_UPDATER)` respectively.

**File:** `app/Models/Traits/HasAuditFields.php`

---

### Bug 3: Route [#] Not Defined in Proposals View

**Root Cause:** `ViewsConstants::PPS = 'proposals'` (plural) but actual Laravel
routes use SINGULAR `proposal.*` (e.g., `proposal.index`, `proposal.show`).
`Route::has('proposals.index')` returns false → fallback `$routeArray = ['#']` →
`Form::open(['route' => ['#']])` → Laravel calls `route('#')` →
`RouteNotFoundException`.

**Fix (4 Form::open blocks + 8 route resolution blocks):**

1. Added `Str::singular(ViewsConstants::PPS).'.xxx'` fallback to all 8 route
   resolution blocks (export, create, index, show×2, edit, convert, duplicate,
   destroy)
2. Changed `['#']` fallback to `null` for all 4 route arrays (index, convert,
   duplicate, destroy)
3. Wrapped all 4 `Form::open()` calls with `array_filter()` pattern:
   ```php
   Form::open(array_filter([
       'route' => $routeArray ?? null,
       'url'   => ($routeArray ?? null) ? null : '#',
       // ... other params
   ], fn($v) => $v !== null))
   ```

**File:** `resources/views/proposals/index.blade.php`

---

### Type Case Fixes

| File                    | Change                                                                       |
| ----------------------- | ---------------------------------------------------------------------------- |
| BillController.php      | `'Bill'` → `'bill'`, `'Bill Category'` → `'bill_category'` (5+2 occurrences) |
| ExpenseController.php   | `'Expense'` → `'expense'`, `'Bill'` → `'bill'` (6 occurrences)               |
| DebitNoteController.php | `'Bill'` → `'bill'` (1 occurrence)                                           |
| Bills migration         | Filter: `fn($v) => $v === TransactionType::Bill->value`                      |

These match the `TransactionType::Bill = 'bill'` and `ConsumableType::Expense = 'expense'` enum values.

---

### View Fixes

| View                    | Fix                                                                                                                       |
| ----------------------- | ------------------------------------------------------------------------------------------------------------------------- |
| invoices/index          | Fix `$invAv` check, `$resetLinkId`, move `$due` inside `@foreach`                                                         |
| goals/index             | `$list instanceof Collection` → `$list instanceof \Illuminate\Support\Collection`                                         |
| debit_notes/index       | `$bill->debitNote` → `$bill->debitNotes`, merge `@php` blocks, add fallbacks, restore `$editDebitBase`/`$deleteDebitBase` |
| expenses/index          | Add fallback defaults before try blocks                                                                                   |
| bills/index             | Use `VC::TI_COPY_WT` constant                                                                                             |
| ViewClassNamesConstants | Added `TI_COPY` and `TI_COPY_WT` constants                                                                                |

---

### Seeder Fixes

Forced `DC::DEFAULT_UUID` for `created_by`/`updated_by` in:

- `BillSeeder.php`
- `BudgetSeeder.php`
- `BankTransferSeeder.php`
- `DebitNoteSeeder.php`

---

### Verification Results (All 20 Routes)

```
revenues           TRs=17
payments           TRs=16
customers          TRs= 7
vendors            TRs= 7
orders             TRs= 3
credit-notes       TRs= 2
journal_entries    TRs= 9
leads              cards=10
deals              cards=15
contracts          cards=15
purchases          cards=15
bank_accounts      TRs= 5
invoices           TRs= 3
bills              TRs= 3
expenses           TRs= 3
goals              TRs= 3
debit_notes        TRs= 5
budgets            TRs= 3
bank_transfers     TRs= 2
proposal           TRs= 3
```

---

### Key Patterns Discovered

1. **Guard pattern bug:** `guard()` returns `true` on success. Code that does
   `if ($c = $this->guard(...))` always enters the if-block. Must use
   `if (($c = $this->guard(...)) !== true)`.

2. **Route name mismatch:** `ViewsConstants::PPS = 'proposals'` but routes are
   `proposal.*` (singular). Always add `Str::singular()` fallback.

3. **Form::open(['route' => ['#']])** crashes Laravel — use `url` fallback
   instead with `array_filter()`.

4. **Blade `@php($var = val)` inline + `@php` block:** The block `@php` after
   inline form doesn't compile. Always separate with whitespace or merge.

5. **Enum values are lowercase:** `TransactionType::Bill->value = 'bill'`,
   `ConsumableType::Expense->value = 'expense'`. DB queries must use lowercase.

### Files Modified (17 total)

```
app/Config/Constants/ViewClassNamesConstants.php
app/Http/Controllers/Bills/BillController.php
app/Http/Controllers/Bills/DebitNoteController.php
app/Http/Controllers/Bills/ExpenseController.php
app/Http/Controllers/Planning/ProposalController.php
app/Models/Traits/HasAuditFields.php
database/migrations/2025_06_03_233369_create_bills_table.php
database/seeders/BankTransferSeeder.php
database/seeders/BillSeeder.php
database/seeders/BudgetSeeder.php
database/seeders/DebitNoteSeeder.php
resources/views/bills/index.blade.php
resources/views/debit_notes/index.blade.php
resources/views/expenses/index.blade.php
resources/views/goals/index.blade.php
resources/views/invoices/index.blade.php
resources/views/proposals/index.blade.php
```
