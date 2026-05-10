# \_inc/laravel/.notes/

Durable repository-level notes and guidance for developers and agents.

**Cross-references:** [`KNOWN_ISSUES.md`](KNOWN_ISSUES.md) (open issues) · [`CURRENT_WORKING_ISSUES.md`](CURRENT_WORKING_ISSUES.md) (bug-fix log) · [`CURRENT_WORKING_ISSUES_WORK.md`](CURRENT_WORKING_ISSUES_WORK.md) (try/fail journal) · [`NEXT_STEPS.md`](NEXT_STEPS.md) (tasks) · [`RESOLVED_ISSUES.md`](RESOLVED_ISSUES.md) (resolved archive) · [`TODO_LATER.MD`](TODO_LATER.MD) (deferred) · [`README_INFRA.md`](README_INFRA.md) (infra map) · [`README_UTILS.md`](README_UTILS.md) (utils guide)

## Current Agent Handoff

The current Codex continuation handoff is
[`../../../.tmp/codex/20260509/handsoff.md`](../../../.tmp/codex/20260509/handsoff.md).
It finishes the PHPUnit work left by the recovered Claude token-limit session
at `157145304`.

Current full-unit baseline after the 2026-05-10 reliability pass:

```text
Tests: 10586, Assertions: 20432, Errors: 0, Failures: 0.
```

2026-05-10 reliability foundation work added generic operation ledger,
operation step, outbox/inbox, and operational event tables plus the service
layer under `app/Services/Reliability/`. Focused checks:

```text
Reliability service tests: 7 tests, 48 assertions, 0 errors, 0 failures.
Ledger service test: 2 tests, 2 assertions, 0 errors, 0 failures.
composer phpstan: no errors.
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
