# \_inc/laravel/.notes/

Durable repository-level notes and guidance for developers and agents.

**Cross-references:** [`KNOWN_ISSUES.md`](KNOWN_ISSUES.md) (open issues) · [`CURRENT_WORKING_ISSUES.md`](CURRENT_WORKING_ISSUES.md) (bug-fix log) · [`CURRENT_WORKING_ISSUES_WORK.md`](CURRENT_WORKING_ISSUES_WORK.md) (try/fail journal) · [`NEXT_STEPS.md`](NEXT_STEPS.md) (tasks) · [`RESOLVED_ISSUES.md`](RESOLVED_ISSUES.md) (resolved archive) · [`TODO_LATER.MD`](TODO_LATER.MD) (deferred) · [`README_INFRA.md`](README_INFRA.md) (infra map) · [`README_UTILS.md`](README_UTILS.md) (utils guide)

## Current Agent Handoff

The current Codex continuation handoff is
[`../../../.tmp/codex/20260511/handsoff.md`](../../../.tmp/codex/20260511/handsoff.md).
It covers the reliability foundation, finance/HRM outbox dispatchers, retry/
circuit breaker guards, quarantine overlays, the warehouse/products reliability
slice, CRM lead/deal and relationship-record slices, the project-planning
finalization/deletion slice, the shared heavy-I/O import/export/webhook slice,
the extended finance revenue/payment/transfer/note/journal slice, and the
timesheet/expense approval-finalization slice.

Current broad unit baseline before the CRM relationship-record slice:

```text
Tests: 10623, Assertions: 20619, Errors: 0, Failures: 0.
```

2026-05-10 reliability work added generic operation ledger, operation step,
outbox/inbox, operational event, circuit breaker, and quarantine tables plus
the service layer under `app/Services/Reliability/`. Finance payment flows and
the first HRM slice are wired. 2026-05-11 added the warehouse/products slice for
stock adjustments, decisive product/service catalog changes, warehouse
transfers, warehouse deletion guards, purchase stock commits/reversals, and POS
stock commits. CRM now covers lead/deal lifecycle decisions, deal status/stage
movement, customer/vendor/client lifecycle rows, and deal user/client/
permission relationship sub-actions. Project planning now covers final project
status, project deletion, milestone final/delete paths, task completion/final
progress, and completed/final task deletion while keeping routine project-board
activity low-overhead. Heavy I/O now covers shared Python import/export
subprocesses and configured webhook delivery while keeping routine file/HTTP
helpers low-overhead. Finance extended flows now cover revenue, generic vendor
payments, bank transfers, purchase payments, credit/debit notes, and journal
entries/items through `FinanceOperationService`. Timesheet/expense approvals
now cover timesheet create/update/delete and submit/approve/reject decisions
through planning reliability, plus expense create/update/delete and expense-line
deletion through finance reliability. Focused checks:

```text
Reliability service tests: 54 tests, 297 assertions, 0 errors, 0 failures.
Reliability service tests after finance extended flows: 57 tests, 309 assertions, 0 errors, 0 failures.
Reliability service tests after timesheet/expense approvals: 61 tests, 321 assertions, 0 errors, 0 failures.
Touched warehouse/product controller tests: 410 tests, 486 assertions, 0 errors, 0 failures.
Touched CRM relationship controller tests: 594 tests, 703 assertions, 0 errors, 0 failures.
Touched planning controller tests plus planning reliability: 354 tests, 450 assertions, 0 errors, 0 failures.
Heavy-I/O reliability tests: 4 tests, 22 assertions, 0 errors, 0 failures.
Touched finance extended-flow controller tests: 344 tests, 411 assertions, 0 errors, 0 failures.
Touched timesheet/expense controller tests: 173 tests, 203 assertions, 0 errors, 0 failures.
Python delegation trait tests: 26 tests, 30 assertions, 0 errors, 0 failures.
Webhook utility tests: 7 tests, 25 assertions, 0 errors, 0 failures.
Full Unit suite baseline before relationship-record slice: 10623 tests, 20619 assertions, 0 errors, 0 failures.
Full Unit suite attempt after relationship-record slice: 10627 tests, 20632 assertions, 1 unrelated timing failure in ContractControllerTest::test_noteStore_performance_114; isolated rerun passed.
Full Unit suite after heavy-I/O slice: 10637 tests, 20685 assertions, 0 errors, 0 failures.
Full Unit suite after finance extended-flow slice: 10640 tests, 20697 assertions, 0 errors, 0 failures.
Full Unit suite after timesheet/expense approval slice: 10644 tests, 20709 assertions, 0 errors, 0 failures.
composer phpstan: no errors.
ESLint: clean with max-warnings=50.
```

Guideline:
[`./.llms/.guidelines/backend/reliability-outbox-ledger.md`](./.llms/.guidelines/backend/reliability-outbox-ledger.md).

## Update order

1. Read [`where-to-update-and-read.yml`](../../../where-to-update-and-read.yml).
2. Update durable guidance in this tree and in [`.llms/`](.llms/).
3. For infrastructure/session-context conventions, read [`README_INFRA.md`](README_INFRA.md).
4. Keep volatile or historical output in [`.history/`](.history/).

## Formats

- `.md` — Readable documentation, plans, checklists
- `.yml` — Structured configs, mappings, decision records
- `.toml` — Compact key-value references
- `.xml` — Structured prompts, templates, schemas

## Subdirectories

- [`.llms/`](.llms/) — Primary guidelines tree and LLM-specific documentation
- [`.history/`](.history/) — Historical snapshots and archived notes (gitignored)

## Infrastructure and session context

- Tooling session context is maintained in [`_inc/laravel/utils/.llms/`](../utils/.llms/).
- Infrastructure-only guidance is consolidated in [`README_INFRA.md`](README_INFRA.md).

## Git hygiene

- Keep `.history/` for local archival context only; do not re-add it to git tracking.
- Reusable scripts belong in [`_inc/laravel/utils/scripts/`](../utils/scripts/), not in this notes tree.
