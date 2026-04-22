# \_inc/utils/ — Global / Infrastructure Utilities

Pertains to **infrastructure-level** observations and tools that affect the web server,
cloud provider, containers, or the OS.

## Structure

```
_inc/utils/
├── scripts/
│   ├── sh/         # Shell scripts (system ops, Docker, nginx, MySQL admin)
│   └── py/         # Python scripts (analysis, comparisons, diagnostics)
├── prompts/        # Prompt templates and coding guidelines
├── cli/            # Durable command logs by date
├── find/           # Durable find command notes
├── grep/           # Durable grep command notes
├── regex/          # Durable regex command notes
├── regexes/        # Reusable regex references
├── assets/         # Utility assets
├── containers/     # Container-related helpers
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
