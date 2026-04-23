# LandingPage Module — Fixes & Changelog (Batch 27)

## Fix 1: Double Route Registration

**File**: `Modules/LandingPage/Providers/LandingPageServiceProvider.php`

**Problem**: `boot()` called `$this->loadRoutesFrom(module_path($this->moduleName, 'Routes/web.php'))` AND
`register()` pushed `RouteServiceProvider::class` into the providers list — which ALSO loads the same
routes file. This caused every LP route to be registered twice, leading to ambiguous matching and
occasional 404s depending on middleware resolution order.

**Fix**: Removed the `$this->loadRoutesFrom()` call from `boot()`. Routes are now loaded exclusively
through `RouteServiceProvider` (registered in `register()`).

---

## Fix 2: URI Mangling in RouteServiceProvider::boot()

**File**: `Modules/LandingPage/Providers/RouteServiceProvider.php`

**Problem**: `boot()` iterated over **ALL** registered application routes (not just LP routes) and
mutated their URIs by running `Str::plural()` on every intermediate path segment. This corrupted
auth routes (e.g. `/login` → unaffected because single segment, but multi-segment routes like
`/password/reset/{token}` could be mutated). More critically, it caused route resolution conflicts
and priority issues with LP routes.

**Fix**: Replaced the entire `boot()` body with `parent::boot()` only. The URI mangling logic was
fundamentally broken — it should never have operated on ALL routes, and pluralizing path segments
is not a valid route normalization strategy.

---

## Fix 3: Static Page Fallback in CustomPageController

**File**: `Modules/LandingPage/Http/Controllers/CustomPageController.php`

**Problem**: `customPage()` loaded the `menubar_page` JSON from `landing_page_settings` DB table,
but this value was **NULL** (never seeded). The method decoded NULL as an empty array, found no
matching page for slugs like `about_us`, and called `abort(404)`. This caused all public portfolio
pages to return 404 even though:

- The routes were correctly registered
- The partial Blade views existed on disk

**Fix**: Added a `STATIC_PAGE_PARTIALS` class constant mapping known slugs to their partial views:

```php
private const STATIC_PAGE_PARTIALS = [
    'about_us'             => 'landingpage::partials.about_us',
    'privacy_policy'       => 'landingpage::partials.privacy_policy',
    'terms_and_conditions' => 'landingpage::partials.terms_and_conditions',
];
```

After the existing page-search loop fails, the method now checks if the slug exists in
`STATIC_PAGE_PARTIALS` and renders the partial view directly with the full settings array.

Additionally changed: when `menubar_page` JSON is malformed (not decodable), instead of
`abort(404)`, the method now gracefully sets `$pages = []` and continues to the fallback logic.

---

## Verification Results

| Endpoint                    | Status | Notes                                   |
| --------------------------- | ------ | --------------------------------------- |
| `GET /about_us`             | 200    | Renders `partials.about_us`             |
| `GET /privacy_policy`       | 200    | Renders `partials.privacy_policy`       |
| `GET /terms_and_conditions` | 200    | Renders `partials.terms_and_conditions` |
| `GET /pages/about_us`       | 200    | Same via slug parameter                 |
| `GET /pages/privacy_policy` | 200    | Same via slug parameter                 |
| `GET /login`                | 200    | Auth routes unaffected                  |
| `GET /register`             | 200    | Auth routes unaffected                  |
| `GET /forgot-password`      | 200    | Auth routes unaffected                  |
| `GET /landingpage`          | 302    | Correctly requires auth                 |
| `GET /testimonials`         | 302    | Correctly requires auth                 |

**PHPUnit**: 82 tests, 106 assertions — ALL PASSING
**Syntax**: 14 files checked — zero errors
