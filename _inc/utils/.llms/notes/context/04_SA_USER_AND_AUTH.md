# 04 — SA USER AND AUTH

> **Last updated:** 2026-02-28 — SA user changed after DB rebuild

## Super admin user

| Field         | Value                                                     |
| ------------- | --------------------------------------------------------- |
| `id`          | `a3e8f4b2-7c1d-4f5a-9b0c-82d6e1f3a5b7`                   |
| `name`        | `Suporte Prestech`                                        |
| `email`       | `suporte@prestech.com.br`                                 |
| `password`    | `test1234` (bcrypt hashed in DB)                          |
| `type`        | `super admin` (= `PMC::SA`)                               |
| `lang`        | `pt-br`                                                   |
| `created_by`  | `a3e8f4b2-7c1d-4f5a-9b0c-82d6e1f3a5b7` (self-referential)|
| `ownerId()`   | returns own `id` (SA owns itself)                         |
| `creatorId()` | returns own `id`                                          |

> ⚠️ **History note:** Previous SA user was `lucia@prestech.com.br` / `2f8f895d-e048-41f7-9b8d-85d25ebdfd19`.
> That user no longer exists in the DB. All references in code and seeders should use the current UUID.

## UUID primary keys — THE BIGGEST GOTCHA

All models use `UsesUuids` trait. Primary keys are UUID strings like
`2f8f895d-e048-41f7-9b8d-85d25ebdfd19`.

```php
// ❌ BREAKS EVERYTHING — UUID cast to int = 0
$userId = (int)($user->id ?? 0);  // Always 0!
// Then: find_in_set(0, assigned_to) → matches NOTHING

// ✅ Correct: keep as string
$userId = $user->id;
// Or for numeric checks: is_numeric($id) ? (int)$id : null
```

## Auth flow

1. Login form POST → `AuthenticatedSessionController@store`
2. On success → redirect to `route('dashboard')` = `/home`
3. `DashboardController@index` checks user type and renders dashboard
4. Guest middleware on `/login`, `/register`, `/forgot-password`
5. Session driver = `file` (stored in `storage/framework/sessions/`)

## Locale flow

1. Login page: `SetGuestLocale` middleware reads cookie `guest_locale`
2. On login: user's `lang` column sets `App::setLocale()`
3. If user has no `lang`, falls back to `config('app.locale')` = `en`
4. The `languages` table stores available languages with `created_by` = SA UUID
5. **Arabic showing**: If `created_by` on languages doesn't match SA UUID,
   the locale picker shows the first alphabetical language = Arabic (`ar`)
6. Fix: ensure `languages.created_by` = SA UUID for all rows

## Settings table

The `settings` table stores key-value pairs:

- `default_language` → should be `pt-br`
- `company_name` → `Nova Prestech`
- `site_currency` → `BRL`
- etc.

⚠️ If settings table is empty, the app falls back to hardcoded defaults,
which causes locale/currency/formatting issues.

## Password hashing

```php
// The SA password 'test1234' must be hashed with bcrypt:
use Illuminate\Support\Facades\Hash;
Hash::make('test1234');
```
