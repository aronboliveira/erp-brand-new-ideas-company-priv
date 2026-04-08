# _inc/laravel/.notes/.llms/ — App-Specific LLM Guidelines & History

> Application-scoped guidelines and session artefacts for the Laravel workspace.
> Canonical filesystem map → [`where-to-update-and-read.yml`](../../../../where-to-update-and-read.yml)

## Purpose

Contains guidelines specific to the main application (security roleplay, testing
patterns), plus historical reports and fix summaries scoped to `_inc/laravel/`.
For architecture-wide guidelines see `.notes/.llms/.guidelines/` at the repo root;
for per-language coding conventions see `_inc/utils/prompts/.guidelines/`.

## Directory structure

```
.llms/
├── README.md                           ← You are here
├── .guidelines/
│   ├── security-roleplay-guidelines.md Security roleplay testing framework guide
│   ├── security-roleplay-profiles.xml  Role profiles (black-hat, green-hat, white-hat, CISO, backend-dev, QA)
│   └── security-test-map.xml           Mapping of roles → test suites → scripts
├── .history/
│   └── reports/
│       └── route_verify_*.txt          Route verification run logs
├── AGENT_BRANCH_MERGE_LOG.md           Agent branch merge history
├── OVERLAPPING_FILES_PHPSTAN_VS_AGENT.txt
├── PHPSTAN_FIX_SUMMARY.md              PHPStan remediation summary
├── route-view-testing-pattern.md       Pattern for route→view smoke tests
├── security-testing-audit.md           Security test coverage audit
├── test-fixes-20260313.md              Test-fix session notes
└── typescript-migration.md             TS migration plan and status
```

## Guidelines checklist (paths agents & developers must read)

| What | Where |
|---|---|
| This app-specific context | `_inc/laravel/.notes/.llms/` (here) |
| Primary architecture guidelines | `.notes/.llms/.guidelines/` |
| Coding-style guides (per-language) | `_inc/utils/prompts/.guidelines/` |
| LLM session context & tooling | `_inc/utils/.llms/` |
| Agent behaviour config | `.agent.md`, `.instructions.md`, `AGENTS.md`, `copilot-instructions.md` |
| Full filesystem map | `where-to-update-and-read.yml` |
