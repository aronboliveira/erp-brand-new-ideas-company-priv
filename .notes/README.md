# .notes/

Durable notes, instructions, and structured references for agents and developers.

> **Cross-references:** [`MOVED_README.md`](MOVED_README.md) (migration notice) · [`CURRENT_WORKING_ISSUES.md`](CURRENT_WORKING_ISSUES.md) (merged root archive) · [`KNOWN_ISSUES.md`](KNOWN_ISSUES.md) (merged root archive) · `_inc/laravel/.notes/` (canonical app-specific docs) · `where-to-update-and-read.yml` (filesystem map)

## Current Policy

- Active Laravel app work is documented in `_inc/laravel/.notes/`.
- Root-wide or historical monorepo context belongs here in `.notes/`.
- The legacy `./notes/` directory has been merged into this directory and
  should only contain redirect stubs.
- The current continuation handoff is `.tmp/codex/20260509/handsoff.md`;
  `.tmp/claude/20260808/HANDOFF.md` is the recovered pre-continuation stop.
- The 2026-05-10 reliability foundation pass added generic outbox/inbox,
  operation ledger, operation step, and operational event support in the
  Laravel app. Canonical guide:
  `_inc/laravel/.notes/.llms/.guidelines/backend/reliability-outbox-ledger.md`.

## Formats

- `.md` — Readable documentation, plans, checklists
- `.yml` — Structured configs, mappings, decision records
- `.toml` — Compact key-value references
- `.xml` — Structured prompts, templates, schemas

## Subdirectories

- `.history/` — Snapshot log of decisions and sessions (gitignored)
- `.llms/.guidelines/` — Legacy guideline tree (deprecated — canonical version now in `_inc/laravel/.notes/.llms/.guidelines/`)

## Root-Level Files

- `CURRENT_WORKING_ISSUES.md` — merged historical route/i18n/naming archive from `./notes/CURRENT_WORKING_ISSUES.md`.
- `KNOWN_ISSUES.md` — merged historical unresolved/resolved issue archive from `./notes/KNOWN_ISSUES.md`.
- `CODE_FLAGS.md` — grep/search markers for deferred work, production-secret mocks, and agent TODOs.
