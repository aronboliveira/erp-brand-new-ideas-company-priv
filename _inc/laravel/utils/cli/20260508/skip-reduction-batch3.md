# Skip-reduction batch 3 — 2026-05-08

Eliminates 6 more PHPUnit skips by mixing real-DB seeding (instead of
mock recipes that never matched the schema) with the registration of 3
genuinely missing module routes.

## UtilityTest (3 sites)

### `test_number_format_wrappers` (line 3409)

The original recipe tried `aliasMock('App\Models\Utility[formatNumber]')`,
which fails because `formatNumber` is private static and `Utility` is a
hot-loaded class.

Fix: seed the real `settings` table with each prefix key and call the
wrapper methods directly. They internally call `formatNumber()`, which
reads from `Utility::settings()` — no mock needed.

Side note: original test asserted `'Z-00003'` for `customerPosNumberFormat`
but the function actually delegates to `formatNumber(SC::POS_PFX, $number)`
which reads `pos_prefix` (already 'O-' in our seed). So the corrected
assertion is `'O-00003'`.

### `it_replaces_all_defined_variables_in_content` (line 7400) and `it_replaces_variables_in_email_content_correctly` (line 12858)

Both originally inserted into `settings` with `created_by => 1` (integer)
and skipped with "Settings cache interaction complex." The actual reason
the cache "didn't work" was that `settings.created_by` is a `uuid` column
— integer `1` never matched what `Utility::settings()` reads (which uses
`DatabaseConstants::DEFAULT_UUID` for global rows).

Fix: insert under `DEFAULT_UUID`, call `Utility::resetSettingsCache()`
both before and after seeding, and `Auth::logout()` so `settings()` takes
the global-fallback branch.

## FeaturesControllerTest (3 sites)

### `featuresStore`, `featuresUpdate`, `featuresDelete` (lines 187/197/207)

The methods exist on `Modules\LandingPage\Http\Controllers\FeaturesController`
along with their route-name constants (`FTRS_STR`, `FTRS_UPD`, `FTRS_DEL`,
plus `FTRS_CRT`, `FTRS_EDT`), but no routes were registered in
`Modules/LandingPage/Routes/web.php` — so `action([Controller, 'featuresStore'])`
threw at the test level.

Fix (production): register the 5 missing `features/others/...` routes
mirroring the singular `features/...` pattern:

```php
RF::get(R::FT . '/others/create/',     [FTC::class, FTC::FTRS_CRT])->name(R::FT . '.others.create');
RF::get(R::FT . '/others/edit/{key}',  [FTC::class, FTC::FTRS_EDT])->name(R::FT . '.others.edit');
RF::get(R::FT . '/others/delete/{key}',[FTC::class, FTC::FTRS_DEL])->name(R::FT . '.others.delete');
RF::post(R::FT . '/others/store/',     [FTC::class, FTC::FTRS_STR])->name(R::FT . '.others.store');
RF::post(R::FT . '/others/update/{key}',[FTC::class, FTC::FTRS_UPD])->name(R::FT . '.others.update');
```

Tests: each test acts as a super-admin user, asserts a redirect with the
expected success flash, and verifies the `landing_page_settings` row for
`name='other_features'` was written/modified as expected.

## Verification

### Targeted (6 modified tests)

```
APP_ENV=testing php vendor/bin/phpunit \
  tests/Unit/app/Models/utils/UtilityTest.php \
  tests/Unit/app/Http/Controllers/LandingPage/FeaturesControllerTest.php \
  --filter='test_number_format_wrappers|it_replaces_all_defined_variables_in_content|it_replaces_variables_in_email_content_correctly|featuresStore_adds_other_feature_and_redirects|featuresUpdate_modifies_other_feature_and_redirects|featuresDelete_removes_other_feature_and_redirects' \
  --no-coverage
# → 6/6 passed
```

### Suite-level

| Suite | Before (S/F/A) | After (S/F/A) |
|---|---|---|
| UtilityTest                                | 4 / 4 / 2231 | **1** / 4 / 2240 |
| FeaturesControllerTest                     | 3 / 4 / 51   | **0** / 4 / 61   |

Net: **6 skip sites eliminated**, 19 new assertions, no new failures.

## Out of scope

Remaining `markTestSkipped()` sites in `UtilityTest.php`:

- `test_warehouse_transfer_qty_moves_and_deletes` (line 2021):
  blocked by a real production schema bug — `warehouse_products.product_id`
  has a `unique()` constraint in `2025_06_03_233612_create_warehouse_products_table`,
  but `FinanceBillingService::warehouseTransferQty()` creates a second row
  with the same `product_id` (different `warehouse_id`) when transferring.
  The fix requires a migration, which the project conventions forbid
  modifying. Left skipped with the original reason; flagged for a follow-up
  migration ticket.
