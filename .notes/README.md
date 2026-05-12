# .notes/

Durable notes, instructions, and structured references for agents and developers.

> **Cross-references:** [`MOVED_README.md`](MOVED_README.md) (migration notice) · [`CURRENT_WORKING_ISSUES.md`](CURRENT_WORKING_ISSUES.md) (merged root archive) · [`KNOWN_ISSUES.md`](KNOWN_ISSUES.md) (merged root archive) · `_inc/laravel/.notes/` (canonical app-specific docs) · `where-to-update-and-read.yml` (filesystem map)

## Current Policy

- Active Laravel app work is documented in `_inc/laravel/.notes/`.
- Root-wide or historical monorepo context belongs here in `.notes/`.
- The legacy `./notes/` directory has been merged into this directory and
  should only contain redirect stubs.
- The current continuation handoff is `.tmp/codex/20260512/handsoff.md`;
  `.tmp/claude/20260808/HANDOFF.md` is the recovered pre-continuation stop.
- The 2026-05-10 and 2026-05-11 reliability passes added generic outbox/inbox, operation
  ledger, operation step, operational event support, retry/circuit breaker
  builders, quarantine overlays, and monolith outbox dispatcher/compensation
  slices for finance and the first HRM procedures. Finance covers invoice/bill
  payment create/delete plus extended revenue, generic payment, bank-transfer,
  purchase-payment, credit/debit note, and journal-entry/item flows. HRM covers
  salary/payroll updates, termination
  lifecycle decisions, and leave status decisions. HRM quarantine explicitly
  treats employees without linked users as valid and reserves quarantine for
  persistent linked-user/RBAC/payroll/lifecycle corruption signals. Warehouse/
  products now covers stock adjustments, decisive product/service catalog
  changes, warehouse transfers, guarded warehouse deletion, purchase stock
  commits/reversals, and POS stock commits, with quarantine reserved for
  persistent high-impact stock/warehouse instability. CRM now covers durable
  lead/deal decisions plus customer/vendor/client relationship records and
  deal user/client/permission sub-actions, with routine CRM activity payloads
  kept low-overhead. Project planning now covers final project status, project
  deletion, milestone final/delete paths, task completion/final progress, and
  completed/final task deletion, while routine project-board activity remains
  outside durable overhead. Heavy I/O now covers shared Python import/export
  subprocesses and configured webhook delivery while ordinary file reads,
  previews, and transient notifications remain outside durable overhead.
  Timesheet/expense approval-finalization now covers timesheet create/update/
  delete and submit/approve/reject decisions through planning reliability, plus
  expense create/update/delete and expense-line deletion through finance
  reliability. Domain outbox signals now pass through inbox-backed local
  handlers before dispatch completion, giving projection/reconciliation/bridge
  signals an idempotent receive-side boundary instead of pure descriptor
  acceptance.
  Canonical guide:
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
