# Python Scripts — 2026-02-08

## migration_guard.py — Wrap Schema::create with hasTable guard

Bulk-modified 148 migration files to add idempotent guards.

### What it does

1. Scans all `database/migrations/*.php` files
2. For each `Schema::create('table_name', ...)` without a `hasTable` guard:
   - Wraps with `if (!Schema::hasTable('table_name')) { ... }`
3. Replaces `Schema::drop('table_name')` with `Schema::dropIfExists('table_name')`

### Key regex patterns

```python
# Match Schema::create('table_name', function (Blueprint $table) {
r"Schema::create\(\s*'([^']+)'"

# Guard wrapper
f"if (!Schema::hasTable('{table_name}')) {{\n{indent}    Schema::create(..."

# Drop replacement
r"Schema::drop\(\s*'([^']+)'\s*\)" → "Schema::dropIfExists('\\1')"
```

### Post-fix manual repairs needed

- Multi-line `Schema::create` with closure on next line → closure format broken
- Files affected: `create_invoice_products_table`, `create_project_invoices_table`,
  `create_admin_payment_settings_table`, `create_company_payment_settings_table`

## i18n_sync.py — Inject missing translation keys

Added 27 missing keys to 14 locale JSON files.

### Pattern

```python
import json

# For each locale file:
with open(path, 'r') as f:
    data = json.load(f)

for key, translations in MISSING_KEYS.items():
    if key not in data:
        data[key] = translations[locale_code]

with open(path, 'w') as f:
    json.dump(data, f, ensure_ascii=False, indent=4)
```
