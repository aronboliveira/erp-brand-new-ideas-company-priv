# Eloquent Model Conventions

## Getters, Mutators & Setters

Always use `$this->getAttribute('field')` and `$this->setAttribute('field', $value)` inside model scope.
This ensures casts (e.g., `$casts = ['options' => 'array']`) and `booted()` lifecycle hooks fire correctly.
Direct property access (`$this->field`) bypasses these safeguards when called internally within the model class itself.

## Defensive Null Handling

When using raw DB methods (`DB::table()->insert()`, `DB::statement()`) or the model's static `insert()`,
Eloquent casts and mutators do **not** execute. You must manually typecast and null-check every value:

```php
'price' => (float) ($data['price'] ?? 0),
'name'  => (string) ($data['name'] ?? ''),
```

## Exception Handling & Logging

Wrap all non-trivial operations in `try/catch`. Always log with the `Log` facade and include
file and line information for traceability:

```php
try {
    // ...
} catch (\Throwable $e) {
    Log::error($e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
}
```

## Eager Loading (`$with`)

- Names in `$with` must match **actual relationship method names** that correspond to real Model classes.
- Only define **one** relationship method per related table — do not create both `invoiceItems()` and `items()`
  pointing to the same `invoice_items` table.

## Fillable & Guarded

- **Never** include `'id'` or `'created_by'` in `$fillable`. These must remain guarded at all times.
- Remove any redundant `private const` arrays that were created solely to feed `$fillable`.
  Use the `$fillable` property directly.

## Traits

| Condition | Trait |
|---|---|
| Migration uses `HasNullableAuditColumns` | Apply `HasAuditFields` on the model |
| Migration has `email`, `phone`, or `zip` columns | Apply `NormalizesAddresses` |
| Migration has JSON / array columns | Apply `NormalizesArrays` |

### NormalizesAddresses Methods

`normalizeEmail`, `normalizePhone`, `normalizeZip`, `normalizeContactKey`,
`keyIsEmail`, `keyIsPhone`, `normalizeBillingCountry`, `normalizeShippingCountry`, `normalizeStateField`.

### NormalizesArrays Methods

`normalizeArrayField`, `encodeJsonValue`, `looksLikeJson`, `encodeJsonAttribute`,
`ensureJsonAttributesAreEncoded`, `normalizeStringList`.

## Business Logic Helpers

- Add aggregator methods and `$appends`-connected accessors for computed attributes.
- Cache query results and aggregations used in business logic.
- Rely on `$appends` to expose computed values in JSON serialization.

## Guard Clauses

For boolean guard clauses that lead to a single-line body, **suppress curly brackets**:

```php
if (!$this->getAttribute('is_active')) return null;
```

## Unique Slug / Field Assignment

Use a `do/while` loop with an **attempt limit** to prevent infinite loops:

```php
$attempts = 0;
do {
    $slug = Str::slug($base) . '-' . Str::random(4);
    $attempts++;
} while (static::where('slug', $slug)->exists() && $attempts < 20);
```
