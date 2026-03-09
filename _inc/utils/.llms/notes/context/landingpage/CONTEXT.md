# LandingPage Module — Subagent Context & Operations Guide

> **Purpose**: This document provides a complete operational context for an AI subagent
> working exclusively on the LandingPage module of the ERP Nova Prestech application.

---

## 1. Module Overview

| Property        | Value                                                    |
| --------------- | -------------------------------------------------------- |
| **Module name** | `LandingPage`                                            |
| **Namespace**   | `Modules\LandingPage`                                    |
| **Framework**   | Laravel 10.x + nwidart/laravel-modules                   |
| **PHP version** | 8.3.6                                                    |
| **Root path**   | `_inc/laravel/Modules/LandingPage/`                      |
| **Status**      | Enabled (`modules_statuses.json: {"LandingPage": true}`) |
| **View prefix** | `landingpage::`                                          |

---

## 2. Directory Structure

```
Modules/LandingPage/
├── Config/
│   ├── config.php
│   └── Constants/
│       ├── MiddlewaresConstants.php     # WEB, AUTH, XSS, TRT (throttle), API
│       ├── RoutesResourcesConstants.php # LP, HM, CT_PG, FT, DV, SST, PRC_PLN, FQ, TTMN, JU
│       ├── SettingsConstants.php        # PG_SLG='page_slug', MB_STT_K, MB_PG_K, SD_K, etc.
│       └── ExtendingLayoutsConstants.php
├── Database/
│   └── Migrations/
├── Entities/
│   └── LandingPageSetting.php          # Main model — settings(), landingPageSetting()
├── Http/
│   └── Controllers/
│       ├── CustomPageController.php     # Portfolio pages, custom page CRUD
│       ├── DiscoverController.php
│       ├── FaqController.php
│       ├── FeaturesController.php
│       ├── HomeController.php
│       ├── JoinUsController.php
│       ├── LandingPageController.php
│       ├── PricingPlanController.php
│       ├── ScreenshotsController.php
│       └── TestimonialsController.php
├── Providers/
│   ├── LandingPageServiceProvider.php   # Boot: config, views, translations, migrations
│   └── RouteServiceProvider.php         # map(): web + api routes
├── Resources/
│   ├── lang/
│   └── views/
│       ├── layouts/
│       │   └── buttons.blade.php        # Menubar buttons included in auth layout
│       └── partials/
│           ├── about_us.blade.php       # Static About Us page
│           ├── privacy_policy.blade.php # LGPD-compliant privacy policy
│           └── terms_and_conditions.blade.php
├── Routes/
│   ├── api.php
│   └── web.php                          # All module routes (186 lines)
└── module.json
```

---

## 3. Constants Reference

### RoutesResourcesConstants

| Constant  | Value           | Description            |
| --------- | --------------- | ---------------------- |
| `LP`      | `landingpage`   | Landing page resource  |
| `HM`      | `home_section`  | Home section resource  |
| `CT_PG`   | `custom_pages`  | Custom pages resource  |
| `FT`      | `features`      | Features resource      |
| `DV`      | `discover`      | Discover resource      |
| `SST`     | `screenshots`   | Screenshots resource   |
| `PRC_PLN` | `pricing_plans` | Pricing plans resource |
| `FQ`      | `faqs`          | FAQs resource          |
| `TTMN`    | `testimonials`  | Testimonials resource  |
| `JU`      | `join_us`       | Join Us resource       |

### MiddlewaresConstants

| Constant | Value      |
| -------- | ---------- |
| `WEB`    | `web`      |
| `AUTH`   | `auth`     |
| `XSS`    | `XSS`      |
| `TRT`    | `throttle` |
| `API`    | `api`      |

### SettingsConstants (key constants for DB lookups)

| Constant   | Value              | Usage                          |
| ---------- | ------------------ | ------------------------------ |
| `PG_SLG`   | `page_slug`        | Page slug field in pages JSON  |
| `MB_STT_K` | `menubar_status`   | Menubar on/off toggle          |
| `MB_PG_K`  | `menubar_page`     | JSON of custom menubar pages   |
| `MB_PG_NM` | `menubarPageName`  | Page name field in page object |
| `SD_K`     | `site_description` | Site description setting       |

---

## 4. Route Architecture

### Public Routes (no auth required)

```
GET  /about_us              → CustomPageController@customPage  (slug defaults: about_us)
GET  /privacy_policy        → CustomPageController@customPage  (slug defaults: privacy_policy)
GET  /terms_and_conditions  → CustomPageController@customPage  (slug defaults: terms_and_conditions)
GET  /pages/{slug}          → CustomPageController@customPage  (named: custom.page)
```

These routes use middleware `[web]` only — no auth.

### Auth-Required Routes (middleware: web, auth, throttle:100,1)

All CRUD operations require authentication + `manage_landing_page` permission:

```
GET  /landingpage           → LandingPageController@index
GET  /home_section          → HomeController@index
GET  /features              → FeaturesController@index
GET  /discover              → DiscoverController@index
GET  /faqs                  → FaqController@index
GET  /testimonials          → TestimonialsController@index
GET  /screenshots           → ScreenshotsController@index
GET  /custom_pages          → CustomPageController@index
GET  /custom_pages/create   → CustomPageController@create
GET  /custom_pages/edit/{k} → CustomPageController@edit
GET  /custom_pages/delete/{k} → CustomPageController@destroy
```

### Write Routes (middleware: web, auth, XSS, throttle:20,1)

```
POST /custom_pages/store         → CustomPageController@store
POST /custom_pages/custom-store  → CustomPageController@customStore
POST /features/store             → FeaturesController@store
POST /faqs/store                 → FaqController@store
POST /testimonials/store         → TestimonialsController@store
...etc
```

---

## 5. Provider Architecture

### LandingPageServiceProvider

- `register()`: Registers `RouteServiceProvider` as a service provider
- `boot()`: Loads translations, config, views, migrations
- **IMPORTANT**: Does NOT load routes directly (removed duplicate `$this->loadRoutesFrom()`)

### RouteServiceProvider

- `map()`: Calls `mapWebRoutes()` and `mapApiRoutes()` to register all module routes
- `boot()`: Calls `parent::boot()` only — URI mangling was disabled (it was corrupting all app routes globally)
- Web routes use namespace `Modules\LandingPage\Http\Controllers`

---

## 6. Data Model: LandingPageSetting

### Table: `landing_page_settings`

| Column      | Type    | Description                   |
| ----------- | ------- | ----------------------------- |
| `name`      | string  | Setting key                   |
| `value`     | text    | Setting value (often JSON)    |
| `query_key` | string? | UUID key for collection items |

### Key Methods

- `settings(): array` — Loads all settings with defaults from `LANDING_PAGE_SETTINGS` constant, overridden by DB values. UUID-keyed settings (features, faqs, testimonials, etc.) are loaded separately as JSON-encoded collections.
- `landingPageSetting(): array` — Cached wrapper around `settings()`.
- `uploadFile()` — Handles file uploads for site logos, banners, etc.

### Important Settings Keys

| Key                   | Type   | Example Value                                       |
| --------------------- | ------ | --------------------------------------------------- |
| `menubar_status`      | string | `'on'` / `'off'`                                    |
| `menubar_page`        | JSON   | `[{"menubarPageName":"...", "pageSlug":"...",...}]` |
| `home_status`         | string | `'on'` / `'off'`                                    |
| `feature_of_features` | JSON   | UUID-keyed collection of feature items              |
| `faqs`                | JSON   | UUID-keyed collection of FAQ items                  |
| `testimonials`        | JSON   | UUID-keyed collection of testimonial items          |
| `site_logo`           | string | Filename of uploaded logo                           |

---

## 7. CustomPageController Deep Dive

### Public Method: `customPage(string $slug)`

1. Loads `LandingPageSetting::settings()`
2. Decodes `menubar_page` JSON into array of page objects
3. Iterates pages looking for matching `page_slug`
4. If found: renders `layouts.custompage` view with page data
5. **If not found**: Falls back to static partial views for known slugs:
   - `about_us` → `landingpage::partials.about_us`
   - `privacy_policy` → `landingpage::partials.privacy_policy`
   - `terms_and_conditions` → `landingpage::partials.terms_and_conditions`
6. If neither: `abort(404)`

### Page Object Structure (in menubar_page JSON)

```json
{
  "menubarPageName": "About Us",
  "menubarPageContent": "<html content>",
  "pageSlug": "about_us",
  "templateName": "page_content",
  "pageUrl": "",
  "header": "on",
  "footer": "on",
  "login": "on"
}
```

---

## 8. View Integration with Auth Layout

The auth layout (`resources/views/layouts/auth.blade.php`) includes:

```blade
@includeIf('landingpage::layouts.buttons')
```

The `buttons.blade.php` partial:

1. Loads `LandingPageSetting::settings()`
2. Checks `menubar_status === 'on'`
3. Iterates `menubar_page` pages
4. Renders `<li class="nav-item">` links for pages where `login === 'on'`
5. Supports two templates: `page_content` (internal link) and `page_url` (external link)

---

## 9. Known Issues & Current State

### DB State

- `menubar_page` setting does **not exist** in DB — all custom page lookups return empty
- `languages` table has only English — locale picker shows only one option
- `users` table is empty — cannot test authenticated operations

### Fixed Issues (Batch 27)

1. ✅ Double route registration removed from `LandingPageServiceProvider`
2. ✅ URI mangling in `RouteServiceProvider::boot()` disabled (was corrupting ALL app routes)
3. ✅ `customPage()` static partial fallback for `about_us`, `privacy_policy`, `terms_and_conditions`
4. ✅ All public LP pages now return HTTP 200

### Remaining Work

- Seed `menubar_page` data for custom pages to work from DB
- Seed additional languages for locale picker
- Create admin user for testing auth-protected LP CRUD operations
- Add integration tests for LP CRUD (store, update, delete)
- Implement proper layout wrapping for static partial pages
- Add SEO meta tags to portfolio pages

---

## 10. Testing

### Existing Test Coverage

File: `tests/Feature/AuthAndLandingPageTest.php`

- **82 tests, 106 assertions**
- Sections: auth view rendering, HTML structure, form POST, route existence, LP public pages, LP content validation, LP auth pages, view existence, duplicate route detection, response headers

### Running Tests

```bash
cd _inc/laravel
php artisan test --filter=AuthAndLandingPageTest
```

### Curl Validation

```bash
# Public pages (should all return 200)
curl -sS -o /dev/null -w "%{http_code}" http://127.0.0.1:8000/about_us
curl -sS -o /dev/null -w "%{http_code}" http://127.0.0.1:8000/privacy_policy
curl -sS -o /dev/null -w "%{http_code}" http://127.0.0.1:8000/terms_and_conditions
curl -sS -o /dev/null -w "%{http_code}" http://127.0.0.1:8000/pages/about_us

# Auth pages (should return 302 → login)
curl -sS -o /dev/null -w "%{http_code}" http://127.0.0.1:8000/landingpage
curl -sS -o /dev/null -w "%{http_code}" http://127.0.0.1:8000/testimonials
curl -sS -o /dev/null -w "%{http_code}" http://127.0.0.1:8000/faqs
```

---

## 11. File Modification Rules

When modifying LandingPage module files:

1. **Always use constants** — never hardcode route names, settings keys, or view paths
2. **Always wrap in try/catch** — all controller methods use `measureProfile()` with error handling
3. **Log at appropriate levels** — debug for flow, info for success, warning for non-critical, error for failures
4. **Check permissions** — CRUD methods require `PMC::MNG_LP` (`manage_landing_page`)
5. **Check login** — Use `self::_checkLogin()` which returns `User|RedirectResponse`
6. **Use DB transactions** — All write operations use `DB::transaction()`
7. **Run syntax check** — `php -l <file>` after any PHP file changes
8. **Run tests** — `php artisan test --filter=AuthAndLandingPageTest` after changes
