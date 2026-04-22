# \_inc/laravel/.notes/.llms/ — App-Specific LLM Guidelines & History

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

| What                               | Where                                                                   |
| ---------------------------------- | ----------------------------------------------------------------------- |
| This app-specific context          | `_inc/laravel/.notes/.llms/` (here)                                     |
| Primary architecture guidelines    | `.notes/.llms/.guidelines/`                                             |
| Coding-style guides (per-language) | `_inc/utils/prompts/.guidelines/`                                       |
| LLM session context & tooling      | `_inc/utils/.llms/`                                                     |
| Agent behaviour config             | `.agent.md`, `.instructions.md`, `AGENTS.md`, `copilot-instructions.md` |
| Full filesystem map                | `where-to-update-and-read.yml`                                          |

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
