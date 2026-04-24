# \_inc/laravel/.notes/

Durable repository-level notes and guidance for developers and agents.

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
