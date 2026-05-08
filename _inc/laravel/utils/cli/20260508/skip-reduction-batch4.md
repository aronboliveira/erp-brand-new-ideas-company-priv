# Skip-reduction batch 4 — 2026-05-08

Eliminates 9 more PHPUnit skips: 6 Chatify + 3 LandingPage route/ordering.

## LandingPage routes (3 sites)

### `LandingPageControllerTest::create_returns_create_form` (line 67)

`landingpage/create` was being shadowed by `landingpage/{landingpage}`
(show route from `RF::resource(...)->only(['index','show'])`) because
the resource was registered before the create route — `{landingpage}`
matched `'create'` as an id and the controller redirected with
"Setting not found" (302).

Fix (production): explicitly register the GET `landingpage/create`
route BEFORE the `RF::resource()` call in the auth/web group of
`Modules/LandingPage/Routes/web.php`.

### `DiscoverControllerTest::discover_store_without_file_appends_feature_and_redirects_back` (line 329) + `discover_store_with_file_upload_failure_redirects_back_with_error` (line 348)

Two related production bugs:

1. The POST `/discover/store/` route was bound to `DC::DCV_CRT`
   (`discoverCreate`, a GET method) instead of `DC::DCV_STR`
   (`discoverStore`, the actual POST handler).
2. The route was named `discover.store`, colliding with the
   `RF::resource()`-generated `discover.store` (POST `/discover` → `store()`,
   the global-settings handler).

Fix (production): rebind the explicit POST to `DCV_STR` and rename it
to `discover.feature.store` (path: `/discover/feature/store/`). The
resource's `store()` keeps owning `discover.store`. Both intents now
have distinct, working routes:

| Route name              | Path                  | Method                        |
|---|---|---|
| `discover.store`        | `POST /discover`      | `DiscoverController@store` (global settings) |
| `discover.feature.store`| `POST /discover/feature/store/` | `DiscoverController@discoverStore` (feature add) |

Test #2 (upload-failure path) was rewritten to seed
`local_storage_validation = 'pdf'` so the validator inside
`LandingPageSetting::uploadFile()` rejects the .png upload via the
real code path — no static-mock workaround needed.

## Chatify (6 sites in MessageControllerTest)

All 6 skips were variants of "Requires Chatify routes/views fully
configured". The actual blockers were two:

1. **lang=null on factory users** — the XSS middleware calls
   `App::setLocale($user->lang)` and the resulting null-locale chain
   produced redirects instead of 200. Fix: add `'lang' => 'en'` to
   each `User::factory()->create()` call.

2. **Wrong assertion target** — original tests asserted on
   `'Messenger'` after a stub view stripped that text. The published
   view at `resources/views/vendor/Chatify/pages/app.blade.php` does
   contain the title "Messenger" — assertion now uses that.

3. **Test isolation** — download tests used a shared `'test_attach'`
   folder under `storage/`. Replaced with per-test
   `'test_attach_' . uniqid()` directories cleaned up in `try/finally`.

| Test | Reason resolved |
|---|---|
| `test_index_displays_view_for_non_admin_users` | + `lang=en` |
| `index_displays_chat_view_for_non_admin_user`  | + `lang=en` |
| `test_download_returns_404_for_missing_file`   | unique folder + cleanup |
| `test_download_serves_existing_file`           | unique folder + cleanup |
| `download_returns_file_or_404`                 | unique folder + cleanup |
| `get_contacts_returns_html_or_empty_hint`      | + `lang=en` |

## Verification

### Targeted (LandingPage 3)

```
APP_ENV=testing php vendor/bin/phpunit \
  tests/Unit/app/Http/Controllers/LandingPage/LandingPageControllerTest.php \
  tests/Unit/app/Http/Controllers/LandingPage/DiscoverControllerTest.php \
  --no-coverage
# Before: 9F / 3S / 56A   →   After: 9F / 0S / 63A
```

### Targeted (Chatify 6)

```
APP_ENV=testing php vendor/bin/phpunit \
  tests/Unit/app/Http/Controllers/contact/MessageControllerTest.php \
  --no-coverage
# Before: 0F / 6S / 48A   →   After: 0F / 0S / 62A
```

Net: **9 skip sites eliminated**, 21 new assertions, no new failures.
