# _inc/laravel/.notes/.llms/ — Unified LLM Guidelines & History

> Unified tree for LLM guidelines and historical context.
> This folder merges the former root-level and app-hidden notes trees.
> Canonical filesystem map → [`where-to-update-and-read.yml`](../../../../where-to-update-and-read.yml)

## Purpose

Houses the unified **guidelines tree** (`.guidelines/`) for architecture,
backend/frontend/database conventions, security patterns, testing strategies,
agent roles, and Laravel app-specific roleplay/security material. Also stores
historical snapshots in `.history/`.

## Directory structure

```
.llms/
├── README.md                           ← You are here
├── .guidelines/                        ★ Unified guideline tree
│   ├── README.md                       Tree overview
│   ├── THE_TESTER.md                   QA agent persona
│   ├── project.yml                     Global project config
│   ├── constraints.md                  Hard rules and constraints
│   ├── backend/                        Architecture and coding conventions
│   ├── database/                       DB conventions
│   ├── frontend/                       Blade/JS/TS guidance
│   ├── infrastructure/                 Server and infra references
│   ├── modules/                        Domain module guidance
│   ├── roles/                          Agent role definitions
│   ├── security/                       Security patterns + roleplay profiles/test maps
│   └── testing/                        CI and test architecture
├── .history/                           Historical context (reference only)
├── route-view-testing-pattern.md       App route→view smoke test pattern
├── .guidelines/backend/reliability-outbox-ledger.md  Outbox/inbox + operation ledger policy
├── security-testing-audit.md           Security coverage audit
└── typescript-migration.md             TS migration notes
```

## Guidelines checklist (paths agents & developers must read)

| What | Where |
| --- | --- |
| Unified context tree | `_inc/laravel/.notes/.llms/` (here) |
| Primary + app guidelines | `_inc/laravel/.notes/.llms/.guidelines/` |
| Coding-style guides (per-language) | `_inc/laravel/utils/prompts/.guidelines/` |
| LLM session context | `_inc/laravel/utils/.llms/` |
| Agent behaviour config | `.agent.md`, `.instructions.md`, `AGENTS.md`, `copilot-instructions.md` |
| Full filesystem map | `where-to-update-and-read.yml` |
| Reliability/outbox policy | `_inc/laravel/.notes/.llms/.guidelines/backend/reliability-outbox-ledger.md` |
| Final dev-mode resilience boundary | `.tmp/codex/20260512/final-resilience-readiness-scan.md` |

## Update policy

1. Read [`.guidelines/`](.guidelines/) and [`where-to-update-and-read.yml`](../../../../where-to-update-and-read.yml) before making code or docs changes.
2. Keep durable guidance in [`.guidelines/`](.guidelines/) and this README.
3. For resilience work, read the backend reliability guideline and the final
   dev-mode readiness scan before adding wrappers or new dispatch surfaces.
4. Store historical artifacts under [`.history/`](.history/); this path is archived context and should remain gitignored.
5. If a helper script is one-off or host-specific, archive it in the nearest `.history/` scripts path instead of keeping it as reusable tooling.

## CHORES

After finishing a large task, consider clearing cache and logs, and save some data of your procedures as instructed:

### CLI:

_NOTE_: They should be written as [arbitrary label]: [full cmd, with flags with options], and always saved in a subfolder with the date they were used ($(date +%Y%m%d))

- find: \_inc/laravel/utils/find/
- grep : \_inc/laravel/utils/grep/
- [any relevant other]: \_inc/laravel/utils/cli/[cmd]/

### SCRIPTS

_NOTE_: Always saved in a subfolder path of [extension ]/[date they were created ($(date +%Y%m%d))]

- path as \_inc/laravel/utils/scripts/

### REGEX PATTERNS

_NOTE_: Always saved in a subfolder path of [date they were created ($(date +%Y%m%d))]

- path as \_inc/laravel/utils/regex/

### CASES

- If you finish the cycle of an issue (i.e. diagonisis, treatment, implemented working solution), save it to ./.history/case-study within the subfolder of your designated alias. If you forgot your alias due to context rot, you should just created a subfolder with [alias for the case]-[date(s) worked]/;
