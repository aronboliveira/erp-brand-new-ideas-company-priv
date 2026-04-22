# .notes/.llms/ — Primary LLM Guidelines & History

> Root-level guidelines tree and historical context for LLM agents and developers.
> Canonical filesystem map → [`where-to-update-and-read.yml`](../../where-to-update-and-read.yml)

## Purpose

Houses the **primary guidelines tree** (`.guidelines/`) that defines architecture
decisions, backend/frontend/DB/module conventions, security patterns, testing
strategies, and agent roles for the entire ERP Prestech monorepo. Also stores
resolved-issue logs, fix history, context snapshots, and session reports in
`.history/`.

## Directory structure

```
.llms/
├── README.md                       ← You are here
├── .guidelines/                    ★ PRIMARY guideline tree
│   ├── README.md                   Tree overview
│   ├── THE_TESTER.md               QA agent persona
│   ├── project.yml                 Global project config
│   ├── constraints.md              Hard rules and constraints
│   ├── backend/
│   │   ├── erp-architecture.md     System architecture overview
│   │   ├── naming-conventions.yml  Naming rules
│   │   ├── constants_map.json      Constants alias map (11 classes)
│   │   ├── middleware_pipeline.xml Middleware chain definition
│   │   ├── controllers/controller-patterns.md
│   │   ├── models/model-conventions.md
│   │   └── services/delegation-patterns.md
│   ├── database/
│   │   └── database-guide.md
│   ├── frontend/
│   │   ├── esm-iife-strategy.md    ESM→IIFE build strategy
│   │   ├── template-literal-testing.md
│   │   ├── blade/blade-conventions.md
│   │   ├── assets/js-singletons.md
│   │   └── typescript/typescript-guide.md
│   ├── infrastructure/
│   │   └── server.toml
│   ├── modules/
│   │   └── {accounting,billing,crm,hrm,planning,products}/*-guide.md
│   ├── roles/
│   │   └── agent-roles.md
│   ├── security/
│   │   └── security-patterns.xml
│   └── testing/
│       ├── ci.yml
│       ├── test-architecture.json
│       ├── test_suites.xml
│       └── typescript-test-harness.md
│
└── .history/                       Historical context (outdated snapshots — reference only)
    ├── RESOLVED_ISSUES.md          Resolved issue log
    ├── context/                    Session context snapshots
    │   ├── 00_READ_ME_FIRST.md
    │   ├── 02_PROJECT_OVERVIEW.md
    │   ├── 04_SA_USER_AND_AUTH.md
    │   ├── 05_DATABASE_STATE.md
    │   ├── 06_GIT_HISTORY.md
    │   ├── 09_ACTIVE_STASH.md
    │   ├── 10_CURRENT_STATUS.md
    │   └── agents-readme.md
    ├── fixes/
    │   ├── security-fixes.json
    │   ├── non-security-fixes.json
    │   ├── resolution-log.xml
    │   └── naming-history.md
    └── reports/
        ├── FULL_AUDIT_REPORT.md
        ├── I18N_AUDIT_REPORT.md
        ├── SESSION_*.md
        └── content_smoke_*.{json,txt}
```

## Related guideline trees

| Tree                    | Path                                                                    | Scope                                                |
| ----------------------- | ----------------------------------------------------------------------- | ---------------------------------------------------- |
| **This tree (primary)** | `.notes/.llms/.guidelines/`                                             | Architecture, domain, testing, security, roles       |
| Coding-style guides     | `_inc/utils/prompts/.guidelines/`                                       | Per-language rules (PHP, JS, TS, Python, CSS, React) |
| App-specific guides     | `_inc/laravel/.notes/.llms/.guidelines/`                                | Security roleplay profiles, test maps                |
| LLM session context     | `_inc/utils/.llms/`                                                     | CLI logs, agent context, working notes               |
| Agent behaviour config  | `.agent.md`, `.instructions.md`, `AGENTS.md`, `copilot-instructions.md` | Copilot/agent config                                 |

## Full map

See [`where-to-update-and-read.yml`](../../where-to-update-and-read.yml) for the comprehensive filesystem architecture map
with every path an LLM agent or developer must check.

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
