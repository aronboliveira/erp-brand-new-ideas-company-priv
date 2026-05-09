# Monorepo Working Issues Archive

> Merged from `./notes/CURRENT_WORKING_ISSUES.md` on 2026-05-09.
> Canonical Laravel app issue tracking lives in
> [`_inc/laravel/.notes/CURRENT_WORKING_ISSUES.md`](../_inc/laravel/.notes/CURRENT_WORKING_ISSUES.md).

This root-level file preserves the old monorepo `notes/` storyline while
pointing current app work to `_inc/laravel/.notes/`.

## Current Pointer

The latest continuation state is documented in:

- [Codex handoff](../.tmp/codex/20260509/handsoff.md)
- [Claude handoff recovered before continuation](../.tmp/claude/20260808/HANDOFF.md)
- [Laravel current issues](../_inc/laravel/.notes/CURRENT_WORKING_ISSUES.md)
- [Laravel open issues](../_inc/laravel/.notes/KNOWN_ISSUES.md)

After Codex continued from Claude's token-limit stop point, `tests/Unit/`
has:

```text
Tests: 10575, Assertions: 20354, Errors: 0, Failures: 0,
Deprecations: 38, Skipped: 8, Incomplete: 4.
```

## Historical Content Merged From `./notes`

### Route Health Report — 2026-02-07

Original `./notes/CURRENT_WORKING_ISSUES.md` tracked the first route-health
audit:

| Crawl | HTTP 500s | Date | Notes |
|---|---:|---|---|
| v5 baseline | 46 | 2026-02-07 19:30 | Fresh `migrate:fresh --seed`, first full 832-route crawl |
| v6 | 20 | 2026-02-07 20:23 | After 6 code bug fixes |
| v8 final | 13 | 2026-02-07 21:45 | After 4 more fixes; remaining items data/protocol/config |

Fixed bug families from that audit:

- Missing Spatie permissions for training, zoom, and reports.
- Missing `resources/views/app.blade.php` for Fortify/Jetstream routes.
- `FaqController::create()` missing `$settings`.
- `TimesheetController` guard truthiness pattern.
- `TimesheetController::filterTimesheetTable()` return type mismatch.
- `HomeController::show()` accepting only `int` IDs.
- `BenefitPaymentController` route-name separator bug.
- `forgot_password.blade.php` Collection cast bug.
- `webhook` vs `webhooks` view path mismatch.
- `User::notifications()` relationship mismatch.
- `Timesheet` SoftDeletes without `deleted_at`.
- `ModelNotFoundException` becoming 500 instead of 404.

The remaining 13 v8 route failures were documented as data-dependent,
POST-only, or external-config-required, not immediate code bugs.

### Session 3 — i18n Audit (2026-02-25)

Merged summary:

- Fixed `change-language` vs `change-languages` route mismatch.
- Added unsupported-locale fallback in `SetGuestLocale`.
- Added missing invalid-locale fallback in `SetLocale`.
- Forced session save in `change-languages`.
- Added RTL direction support for Arabic/Hebrew in admin layout.
- Added 69 Playwright i18n tests and 135 Jest locale tests.

### Session 4 — Expense Form Fix (2026-02-25)

Merged summary:

- Fixed `BankAccount::selectRaw()` binding type bug in expense/bill flows.
- Replaced project-scoped expense routes with global expense routes where
  no project ID exists.
- Changed `Form::open(['route' => $storeUrl])` to URL mode for absolute URLs.
- Full Playwright suite reached 329/329 after the fix.

### Session 5 — Controller Method Naming Refactor (2026-02-26)

Merged summary:

- Added controller method constants and camelCase routes across target
  controllers.
- Updated route definitions away from raw method strings where possible.
- Left a low-priority tail of functional raw string routes for future cleanup.

## Where To Update Now

- App issues: `_inc/laravel/.notes/KNOWN_ISSUES.md`
- App work journal: `_inc/laravel/.notes/CURRENT_WORKING_ISSUES_WORK.md`
- App next tasks: `_inc/laravel/.notes/NEXT_STEPS.md`
- Monorepo notes index: `.notes/README.md`
