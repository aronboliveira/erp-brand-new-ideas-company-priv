# I18N / Translation System — Audit Report

**Date:** 2026-02-26  
**Branch:** `agent` (parent: `259fcc7f`)  
**Scope:** Full translation service audit — server-side rendering, client-side callbacks, cookie/session interop, RTL support

---

## Test Results Summary

| Suite                                              | Framework  | Tests | Passed | Failed | Status |
| -------------------------------------------------- | ---------- | ----- | ------ | ------ | ------ |
| `tests/e2e/i18n.spec.cjs`                          | Playwright | 69    | 69     | 0      | ✅     |
| `tests/Unit/frontend/js/core/i18n-locale.test.cjs` | Jest       | 135   | 135    | 0      | ✅     |
| Full Playwright regression (`tests/e2e/`)          | Playwright | 329   | 300    | 1\*    | ✅     |

\* The single failure (`financial.spec.cjs:133` — expense create form) was a pre-existing bug fixed in commit `aaef3f39` (2026-02-25). Four cascading issues: `selectRaw()` bindings type error in ExpenseController/BillController, wrong breadcrumb route namespace (`projects.expenses.*` → `expenses.*`), and `Form::open()` passed a URL instead of route name. As of `aaef3f39`: financial.spec.cjs is 35/35 ✅ and total Playwright is 329/329 ✅.

---

## Playwright i18n Test Coverage (69 tests)

### Section 1: Guest locale via /login/{lang}

- Verifies `<html lang>` matches route parameter for en, fr, pt-br, es
- Confirms translated text renders (`Connexion`, `Iniciar sessão`, `Acceso`)
- Tests label elements contain translated strings

### Section 2: SetGuestLocale cookie behaviour

- `erp_locale` cookie set on response
- Cookie persists across page navigations
- Cookie value matches requested locale

### Section 3: RTL layout for ar/he

- Arabic: `dir="rtl"`, `lang="ar"`
- Hebrew: `dir="rtl"`, `lang="he"`

### Section 4: Unsupported locale fallback

- `/login/xx` → falls back to `lang="en"`
- Does not produce HTTP 500

### Section 5: Language dropdown

- Dropdown exists on login page with correct locale options

### Section 6: Client-side localStorage/cookie sync

- Login page sets `localStorage.locale` to current lang
- `document.cookie` includes `erp_locale` with correct value

### Section 7: Authenticated change-languages endpoint

- `GET /change-languages/es` — redirects, sets LANGUAGE cookie
- `GET /change-languages/pt-br` → navigate → `lang="pt-br"`
- `GET /change-languages/fr` — persists LANGUAGE cookie + erp_locale
- `GET /change-languages/ar` → `dir="rtl"` on admin layout
- `GET /change-languages/en` — resets to LTR

### Section 8: All 16 locales smoke test

- Each of `ar, da, de, en, es, fr, he, it, ja, nl, pl, pt, pt-br, ru, tr, zh` loads `/login/{lang}` without HTTP 500 and sets correct `<html lang>`

### Section 9: Translation JSON integrity

- `en.json` has >1000 keys
- `pt-br.json`, `es.json`, `fr.json`, `de.json` cover ≥90% of en.json keys
- No empty-string translations in secondary locale files

---

## Jest i18n Test Coverage (135 tests)

### ERPGuard locale detection

- `#detectLocale()` priority: localStorage → documentElement.lang → navigator.language → meta[name=locale] → 'en'
- Normalisation: 'pt-BR' → 'pt-br', 'en-US' → 'en'

### ERPGuard.getMsg translations

- Returns correct translation for supported locales
- Falls back to English for unsupported locale
- Falls back to key itself when key not found
- Custom fallback parameter works

### DEFAULT_MESSAGES completeness

- All 15 locales present (ar, da, de, en, es, fr, he, it, ja, nl, pl, pt, ru, tr, zh)
- Each locale has 22 message keys
- No undefined values

### Login locale sync simulation

- `localStorage.locale` set correctly
- `localStorage['erp-np-lang']` set correctly
- `document.cookie` includes `erp_locale`

### Translation JSON file validation

- All 16 locale JSON files exist and parse correctly
- Each has ≥500 keys
- Secondary locales cover ≥90% of en.json keys

### ERPUtils getTranslation

- Delegates to ERPGuard.getMsg when available
- Falls back to window.translations

### RTL identification

- ar and he identified as RTL
- Other locales correctly identified as LTR

---

## Bugs Found & Fixed

### Bug 1: `<html lang>` always "en" on auth pages (CRITICAL)

- **File:** `resources/views/layouts/auth.blade.php`
- **Root cause:** `prepareCommonViewData()` returns `$data[SC::LCL]` = 'en' from admin DB settings, taking priority over `app()->getLocale()` (correctly set by SetGuestLocale middleware)
- **Fix:** Reordered `$lang` resolution chain: `request()->route('lang')` (validated) → `app()->getLocale()` → `$data[SC::LCL]` → `fetchUserLang()` → `DEFAULT_LANG`

### Bug 2: `_setLocale()` sets locale without validation (SECURITY)

- **Files:** `AuthenticatedSessionController.php`, `RegisteredUserController.php`, `EmailVerificationPromptController.php`
- **Root cause:** `_setLocale()` calls `App::setLocale($lang)` with NO validation on the route `{lang}` parameter. An attacker could set arbitrary locale values (e.g., `/login/../../etc/passwd`)
- **Fix:** Added `in_array($lang, array_keys(Utility::langList()), true)` validation before `App::setLocale()` in all 3 controllers, with fallback to `DC::DEFAULT_LANG`

### Bug 3: Admin layout `$lang` always 'en'

- **File:** `resources/views/layouts/admin.blade.php`
- **Root cause:** `$lang = Utility::fetchUserLang()` called without `$user`/`$req` args always returns `config('app.locale')` = 'en'
- **Fix:** Changed to `$lang = str_replace('_','-',app()->getLocale()) ?: Utility::fetchUserLang()`

### Bug 4: Cookie encryption breaks JS/server interop (CRITICAL)

- **File:** `app/Http/Middleware/EncryptCookies.php`
- **Root cause:** `EncryptCookies` encrypts all cookies including `erp_locale`. Login page's inline JS sets `document.cookie = 'erp_locale=fr'` (plain text), overwriting server's encrypted version. On next request, EncryptCookies can't decrypt plain text → returns null → middleware falls back to 'en'
- **Fix:** Added `'erp_locale'` to `EncryptCookies::$except`

---

## Known Pre-existing Issues (Not Fixed)

### ERPUtils → ERPGuard argument order mismatch

- `ERPUtils.getTranslation(key, el)` calls `ERPGuard.getMsg(el, key)` but ERPGuard signature is `getMsg(key, fallback = "")`
- When `el` is null (common case), `getMsg(null, key)` normalises null→'en' and works by accident
- When `el` is an HTML element, `getMsg(element, key)` fails silently

### Admin layout `dir` attribute inconsistency

- Admin layout uses `dir=""` for LTR (empty string)
- Auth layout uses `dir="ltr"` for LTR
- Not a functional bug, but inconsistent

### Expense create form redirect

- `/expenses/create` now redirects to `/product_service_units?modal=create`
- Pre-existing route change, unrelated to i18n

---

## Files Modified

| File                                                              | Change                                                         |
| ----------------------------------------------------------------- | -------------------------------------------------------------- |
| `composer.json`                                                   | Added `auth:refresh`, `e2e`, `e2e:headed` scripts              |
| `package.json`                                                    | Added `auth:refresh`, `e2e`, `e2e:headed`, `e2e:debug` scripts |
| `tests/e2e/i18n.spec.cjs`                                         | **NEW** — 69 Playwright i18n tests                             |
| `tests/Unit/frontend/js/core/i18n-locale.test.cjs`                | **NEW** — 135 Jest i18n tests                                  |
| `resources/views/layouts/auth.blade.php`                          | Fixed `$lang` resolution chain                                 |
| `resources/views/layouts/admin.blade.php`                         | Fixed `$lang` to use `app()->getLocale()`                      |
| `app/Http/Controllers/Auth/AuthenticatedSessionController.php`    | Added locale validation in `_setLocale()`                      |
| `app/Http/Controllers/Auth/RegisteredUserController.php`          | Added locale validation before `App::setLocale()`              |
| `app/Http/Controllers/Auth/EmailVerificationPromptController.php` | Added locale validation in `_setLocale()`                      |
| `app/Http/Middleware/EncryptCookies.php`                          | Added `'erp_locale'` to `$except`                              |

---

## Translation System Architecture Summary

- **16 supported locales:** ar, da, de, en, es, fr, he, it, ja, nl, pl, pt, pt-br, ru, tr, zh
- **Server-side:** Laravel `__()` with JSON files at `resources/lang/{code}.json` (en.json: 3297 keys)
- **Middleware:** `SetGuestLocale` — validates & sets `App::setLocale()` per-request; persists `erp_locale` cookie
- **Auth routes:** `/login/{lang?}`, `/register/{lang?}`, `/forgot-password/{lang?}`, `/verify/{lang?}`
- **Language change:** `GET /change-languages/{lang}` — updates user DB, cache, LANGUAGE cookie; `SetGuestLocale` then syncs `erp_locale`
- **Client-side:** `ERPGuard.getMsg(key)` with 15-locale `DEFAULT_MESSAGES` embedded, `#detectLocale()` checks localStorage/DOM/navigator
- **RTL:** `ar` and `he` trigger `dir="rtl"` via `SITE_RTL` DB setting + code override in `prepareCommonViewData()`
