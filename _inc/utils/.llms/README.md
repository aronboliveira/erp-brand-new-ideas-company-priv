# _inc/utils/.llms/ — LLM Session Context & Working Notes

> Developer tooling workspace context for LLM agents.
> Canonical filesystem map → [`where-to-update-and-read.yml`](../../../where-to-update-and-read.yml)

## Purpose

Stores chronological CLI logs, agent context snapshots, working session notes, and
scripts used during LLM-assisted development. This is **not** a guidelines tree —
for coding conventions see `_inc/utils/prompts/.guidelines/`, for architecture/domain
guidelines see `.notes/.llms/.guidelines/`.

## Directory structure

```
.llms/
├── README.md               ← You are here
├── ctx/
│   ├── erp_context.md      ERP project overview for agent bootstrapping
│   └── agents/
│       ├── README.md        Agent role definitions
│       ├── backend/         Backend-specific agent guides
│       ├── frontend/        Frontend-specific agent guides
│       └── infrastructure/  Infra-specific agent guides
├── cli/
│   └── {YYYYMMDD}/         One folder per date — CLI commands logged by language
│       ├── js/*.md          Node/npm/jest commands
│       ├── php/*.md         Artisan/composer/phpunit commands
│       └── sh/*.md          Shell, git, file-ops commands
├── notes/
│   └── {YYYYMMDD}/*.md     Working notes per date
│       └── archive/         Older consolidated notes / nohup output
│       └── context/         Context snapshots carried between sessions
└── scripts/                 Helper scripts used by agents
```

## Guidelines checklist (paths agents & developers must read)

| What | Where |
|---|---|
| This tooling context | `_inc/utils/.llms/` (here) |
| Primary architecture guidelines | `.notes/.llms/.guidelines/` |
| Coding-style guides (per-language) | `_inc/utils/prompts/.guidelines/` |
| App-specific guidelines | `_inc/laravel/.notes/.llms/.guidelines/` |
| Agent behaviour config | `.agent.md`, `.instructions.md`, `AGENTS.md`, `copilot-instructions.md` |
| Full filesystem map | `where-to-update-and-read.yml` |
