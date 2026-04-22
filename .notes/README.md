# .notes/

Durable repository-level notes and guidance for developers and agents.

## Update order

1. Read [`where-to-update-and-read.yml`](../where-to-update-and-read.yml).
2. Update durable guidance in this tree and in [`.notes/.llms/`](.llms/).
3. Keep volatile or historical output in [`.notes/.history/`](.history/).

## Formats

- `.md` — Readable documentation, plans, checklists
- `.yml` — Structured configs, mappings, decision records
- `.toml` — Compact key-value references
- `.xml` — Structured prompts, templates, schemas

## Subdirectories

- [`.llms/`](.llms/) — Primary guidelines tree and LLM-specific documentation
- [`.history/`](.history/) — Historical snapshots and archived notes (gitignored)

## Git hygiene

- Keep `.history/` for local archival context only; do not re-add it to git tracking.
- Reusable scripts belong in [`_inc/utils/scripts/`](../_inc/utils/scripts/) or [`_inc/laravel/utils/scripts/`](../_inc/laravel/utils/scripts/), not in `.notes/`.
