# Seeder Conventions

## Null Handling

Do **not** use `array_filter` for null removal on nullable columns when the data flows through
Eloquent (`create()`, `save()`). Eloquent handles nullable columns natively.
Use `Collection` methods or `array_values` / `array_column` only when you genuinely need
non-null filtering for raw `insert()` / DB façade calls.

## Loop Safety — Break-Out Strategies

Every `while` loop **must** have a break-out strategy:

1. **Attempt counter** — increment on every iteration, `break` when limit is reached.
2. **Clock limiter** — record `microtime(true)` at start, break when elapsed exceeds threshold.

### Clock Limiter Thresholds

| Module Type | Max Seconds |
|---|---|
| Financial / Critical | 600 |
| All others | 300 |

### HardCap Limiter

Maximum entity count per seeder run: `512 × [1–6]` based on module complexity.

- Complexity 1 (simple lookups): 512
- Complexity 6 (financial with relations): 3072

## Console Output

Create a **private** Symfony `ConsoleOutput` instance. Use `writeln()` **before** each
`save()` / `create()` call to detail the variation being created:

```php
private ConsoleOutput $output;

public function __construct()
{
    $this->output = new ConsoleOutput();
}
```

## Raw SQL for Read-Only Queries

Optimize read-only seed-preparation queries with raw SQL when Eloquent overhead is unnecessary:

```php
$ids = DB::select('SELECT id FROM users WHERE type = ?', ['admin']);
```

## Encoding for Raw Insert

When using `insert()` or raw DB methods that skip Model casts/booted hooks,
manually encode JSON fields and cast types:

```php
'options' => json_encode($data['options'] ?? []),
'amount'  => (float) ($data['amount'] ?? 0),
```

## Unique Column — do/while with EXISTS

```php
$attempts = 0;
do {
    $code = strtoupper(Str::random(8));
    $exists = DB::selectOne("SELECT 1 FROM vouchers WHERE code = ? LIMIT 1", [$code]);
    $attempts++;
} while ($exists && $attempts < 50);
```

## UUID Collision Check

```php
$attempts = 0;
do {
    $uuid = (string) Str::uuid();
    $collision = DB::selectOne("SELECT 1 FROM {$table} WHERE id = ? LIMIT 1", [$uuid]);
    $attempts++;
} while ($collision && $attempts < 30);
```

## Entity Count

Entity counts should be powers of 2: `log($count, 2) % 1 === 0`.
Ensure **at least 1** entity per possible subtype/type.

## Import Aliasing

Alias imports when a class name appears 3+ times in the file.

## Comments

Limit to **critical-only** comments — no obvious or redundant remarks.

## Cycle Logging

Use `Log::info()` to record cycle completion, including entity count and elapsed time.

## ErrorHandler

Use the `ErrorHandler` helper for centralized catching and logging:

```php
use App\Helpers\ErrorHandler;

try {
    // seed logic
} catch (\Throwable $e) {
    ErrorHandler::report($e, ['seeder' => static::class]);
}
```
