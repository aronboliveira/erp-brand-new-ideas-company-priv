# _inc/laravel/.notes/README_INFRA.md

Infrastructure and tooling context map for developers and agents.

## Purpose

This document preserves infrastructure-specific guidance that was previously
maintained in the legacy global utilities tree.

Canonical tooling session context is now under `_inc/laravel/utils/.llms/`.

## Tooling context location

- Root: [`_inc/laravel/utils/.llms/`](../utils/.llms/)
- Canonical filesystem map: [`where-to-update-and-read.yml`](../../../where-to-update-and-read.yml)

## Directory structure

```
_inc/laravel/utils/.llms/
├── README.md
├── ctx/
│   ├── erp_context.md
│   └── agents/
│       ├── README.md
│       ├── backend/
│       ├── frontend/
│       └── infrastructure/
├── cli/
│   └── {YYYYMMDD}/
│       ├── js/*.md
│       ├── php/*.md
│       └── sh/*.md
├── notes/
│   └── {YYYYMMDD}/*.md
│       ├── archive/
│       └── context/
└── scripts/
```

## Guidelines checklist

| What                               | Where                                                                    |
| ---------------------------------- | ------------------------------------------------------------------------ |
| Tooling session context            | `_inc/laravel/utils/.llms/`                                              |
| Primary architecture guidelines    | `_inc/laravel/.notes/.llms/.guidelines/`                                |
| Coding-style guides (per-language) | `_inc/laravel/utils/prompts/.guidelines/`                               |
| App-specific guidance              | `_inc/laravel/.notes/.llms/`                                             |
| Agent behavior config              | `.agent.md`, `.instructions.md`, `AGENTS.md`, `copilot-instructions.md` |
| Full filesystem map                | `where-to-update-and-read.yml`                                           |

## Update policy

1. Read `where-to-update-and-read.yml` before reorganizing tooling context files.
2. Keep durable context in `_inc/laravel/utils/.llms/ctx/` and stable docs in `_inc/laravel/utils/.llms/README.md`.
3. Save transient logs, dated notes, and one-off artifacts under the nearest `.history/` path.
4. Keep `.history/` local and gitignored; do not re-track archived files.
