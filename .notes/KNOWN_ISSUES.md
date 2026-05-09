# Monorepo Known Issues Archive

> Merged from `./notes/KNOWN_ISSUES.md` on 2026-05-09.
> Canonical Laravel app open issues live in
> [`_inc/laravel/.notes/KNOWN_ISSUES.md`](../_inc/laravel/.notes/KNOWN_ISSUES.md).

This file preserves the old `./notes` issue list at the root notes level.
It is not the current app backlog.

## Current Open App Issues

Current open app issues are maintained in
[`_inc/laravel/.notes/KNOWN_ISSUES.md`](../_inc/laravel/.notes/KNOWN_ISSUES.md).

As of the Codex continuation after the recovered Claude token-limit handoff:

- RT-007..RT-009 — resolved 2026-05-09; full `tests/Unit` now has
  0 errors and 0 failures.
- RT-010 — 8 remaining aliasMock skips.
- RT-011 — 4 incomplete markers.
- Task G / `serve-k8s--hard` remains blocked by Docker Hub connectivity.

## Historical Issues Merged From `./notes`

### Circular Redirect Loops

Older report described about 10 routes redirecting through permission
middleware fallback chains instead of landing on explicit module-safe fallback
targets. Treat this as historical unless reproduced; current route-health
state is documented in `_inc/laravel/.notes/`.

### HRM Hidden Tables

Older report marked `/meetings` and `/award_types` tables as hidden until JS
loads data. Test impact was visibility assertions on hidden tables. Re-check
with current Playwright specs before acting.

### HTTP 500 On Non-Existent UUIDs

The old `notes/KNOWN_ISSUES.md` already marked this as resolved. Deal task
routes were changed to use `handleFailure()` and invalid UUID-like inputs
became redirects or 404 JSON instead of raw 500s.

### PHPStan Level 5 Errors

The old report marked prior PHPStan errors as resolved. The later Claude
session also cleared the stale BillProduct namespace PHPStan errors at
`1e73c3e13`.

### PHPUnit Unit Failures

The old report noted roughly 30 pre-existing model-level test failures. This
was superseded by later work. The current known PHPUnit state is maintained
in `_inc/laravel/.notes/KNOWN_ISSUES.md`.

### Migration / Naming Cleanup

The old report tracked resolved naming fixes:

- Model/file naming corrections such as `Vendor`, `GeneratedOfferLetter`,
  `ProjectStage`, `TrialBalanceExport`, and `PusherConfig`.
- Controller method naming conversion to camelCase plus route constants.
- Field spelling corrections such as `statuses`, `$saturationDeductionType`,
  and `document_uploads`.

## Where To Update Now

- Use this root file for monorepo-wide archival context only.
- Use `_inc/laravel/.notes/KNOWN_ISSUES.md` for active Laravel app work.
