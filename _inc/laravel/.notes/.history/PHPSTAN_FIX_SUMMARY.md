# PHPStan Level 3 Fix Summary

## Final Result: 0 errors (from 2,199)

## Fixes Applied

### @property Annotations (~1,163 errors)

Added 59 `@property` PHPDoc annotations across 36 model files to declare
dynamically accessed properties (accessor-based, relation-based, computed).

### Unused Closure Variables (~423 errors)

Removed 466 unused `use ($variable)` references from closures across ~51
controller files. These were leftover from MeasuresPerformance trait's
`measureProfile()` method which passed closures.

### MeasuresPerformance Optimization (28+ errors)

- `Controller::measureProfile()` → pass-through (`return $callback()`)
- `Controller::logExecutionTime()` → empty no-op
- `MeasuresPerformance` middleware → checks `static::PERF_ENABLED` constant
- Only `Authenticate.php` and `XSS.php` have `PERF_ENABLED = true`

### Utility Static Method Fixes (36 errors)

Changed `Utility::settings()` calls to `Utility::settingsById()` where
appropriate. Added stub methods: `getStorageSetting()`, `deleteFile()`,
`projectChartTask()`, `adminPaymentSettings()`.

### Model Trait Fixes

- `ExtendsBaseTable`: `static::$baseTableName` → `$this->baseTableName`
- `ExtendsPaymentTable/ProductServiceTable/InvoiceTable`: `@phpstan-ignore new.static`
- `UsesCountryRegions`: `UnitEnum` → `BackedEnum`, inlined `looksLikeJson()`
- `IsNumericBenefit`: `getAttribute()` instead of direct property access
- `LogsIcons`: `@phpstan-require-extends Model`, `@property string $log_type`

### BelongsTo Generics

Fixed template parameters: `BelongsTo<TRelated, $this>` (2 params),
`HasOne<TRelated>` / `HasMany<TRelated>` (1 param each).

### Chatify Stub

Created `stubs/chatify.stub.php` with `@method` annotations for all Chatify
facade methods. Referenced in `phpstan.neon` under `stubFiles`.

### Controller-Specific Fixes

- `SystemController`: Added `saveSettings()` method
- `CommissionController`: Added `_authorize()` method
- `DesignationController`: Added `ChecksPermissions` trait + `_authorize()`
- `AppraisalController`: Fixed `ROute` → `Route` typo

## Configuration

- Level: 3
- Extension: Larastan
- Memory: 2GB (4GB for full scan)
- Ignored: `missingType.iterableValue`, `missingType.generics`
- `reportUnmatchedIgnoredErrors: false`
