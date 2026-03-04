# \_inc/utils/ — Global / Infrastructure Utilities

Pertains to **infrastructure-level** observations and tools that affect the web server, cloud provider, containers, or the OS.

## Structure

```
scripts/
  sh/       — Shell scripts (system ops, Docker, nginx, MySQL admin, crons)
  py/       — Python scripts (analysis, comparison, data processing)
  php/      — PHP CLI scripts (global checks, not Laravel-specific)
prompts/    — Structured prompts (XML/YAML) for agent workflows
regexes/    — Reusable regex/grep patterns for CLI and CI
logs/       — Runtime logs from infra scripts (gitignored via *.log)
```

## Scope

- Docker / Dockerfile / docker-compose operations
- Nginx config generation and testing
- MySQL administrative commands (user management, backup, restore)
- OS-level cron, supervisor, and systemd operations
- Cloud provider deployment scripts
- Cross-project analysis (comparing repos, dependencies)
- Infrastructure-level monitoring and alerting patterns
