# Migration Conventions

## Foreign Key Loops

When creating **multiple** foreign-key columns, use a `foreach` loop to keep the code DRY.
However, for a **single** FK column, write it inline — a loop of one is noise.

```php
// Multiple FKs — use foreach
$fkColumns = ['company_id', 'department_id', 'branch_id'];
foreach ($fkColumns as $col) {
    $table->foreignUuid($col)
          ->nullable()
          ->constrained()
          ->nullOnDelete();
}

// Single FK — inline
$table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
```

## Traits for Migrations

| Trait | Purpose |
|---|---|
| `IsNumericBenefit` | Provides scale calculation for monetary benefit columns |
| `HasNullableAuditColumns` | Adds nullable `created_at`, `updated_at`, `deleted_at` |
| `HasAuditFields` | Standardized audit fields (`created_by`, `updated_by`, `deleted_by`) |
| `EmployeeConnected` | Establishes employee-owner relationship logic |

## Enums

All enums below expose a `normalize()` method for consistent value handling:

- `Frequency` — billing/payment cycles
- `CalculationBase` — benefit/deduction calculation basis
- `DeductionType` — payroll deduction categories
- `DocumentKind` — document classification
- `Gender` — gender representations
- `MimeType` — accepted file MIME types
- `SalaryType` — salary structure types
- `UserType` — application user roles

## Constants Classes

Use these constants for table and column names to prevent typos and enable IDE refactoring:

| Class | Alias | Contains |
|---|---|---|
| `DatabaseConstants` | `DC` | `TABLE_*`, `COL_*` for core DB schema |
| `UsersConstants` | `UC` | User-related tables/columns |
| `CompaniesConstants` | `CC` | Company-related tables/columns |
| `ProjectsConstants` | `PC` | Project-related tables/columns |
| `BillsConstants` | `BC` | Billing-related tables/columns |

## Important Notes

1. **`created_by` must NOT use `nullOnDelete()`** — audit trails must survive user deletion.
   Use `restrictOnDelete()` or leave unconstrained.
2. **UUID primary keys** — all main entity tables use UUID PKs via `$table->uuid('id')->primary()`.
3. **Always reference constants** for column and table names:
   ```php
   $table->foreignUuid(UC::COL_USER_ID)->constrained(UC::TABLE)->cascadeOnDelete();
   ```
