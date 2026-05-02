# \_inc/laravel/utils/ — Global / Infrastructure Utilities

Pertains to **infrastructure-level** observations and tools that affect the web server,
cloud provider, containers, or the OS.

> **Cross-references:** [`../.notes/README_UTILS.md`](../.notes/README_UTILS.md) (Laravel application-layer utils) · [`../.notes/README_INFRA.md`](../.notes/README_INFRA.md) (infra context map) · [`../.notes/KNOWN_ISSUES.md`](../.notes/KNOWN_ISSUES.md) (open issues) · [`../.notes/README.md`](../.notes/README.md) (notes tree overview)

## Structure

```
_inc/laravel/utils/
├── scripts/
│   ├── sh/         # Shell scripts (system ops, Docker, nginx, MySQL admin)
│   ├── py/         # Python scripts (analysis, comparisons, diagnostics)
│   ├── js/         # JS/Node scripts (asset tooling, refactors, build helpers)
│   ├── php/        # PHP CLI scripts (route checks, model inspections)
│   └── ts-harness/ # TypeScript test harness generators
├── prompts/        # Prompt templates and coding guidelines
├── cli/            # Durable command logs by date
├── find/           # Durable find command notes
├── grep/           # Durable grep command notes
├── regex/          # Durable regex command notes
├── cmds/           # Quick-command scripts
├── regexes.txt     # Reusable regex references
├── assets/         # Utility assets
├── containers/     # Container-related helpers
├── caches/         # Cached outputs
├── logs/           # Runtime logs (gitignored)
├── .llms/          # LLM context and session artifacts
└── .history/       # Archived utility artifacts (gitignored)
```

## Script triage rules

- Reusable scripts stay in [`scripts/`](scripts/).
- One-off or host-specific scripts should be moved to [`.history/`](.history/) (for example, `.history/scripts/{lang}/YYYYMMDD-*`).
- Keep `.history/` as local archival context and do not re-track it in git.

## Scope

- Docker / Dockerfile / docker-compose operations
- Nginx config generation and testing
- MySQL administrative commands (user management, backup, restore)
- OS-level cron, supervisor, and systemd operations
- Cloud provider deployment scripts
- Cross-project analysis (comparing repos, dependencies)
- Infrastructure-level monitoring and alerting patterns
