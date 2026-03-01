# LLM Agent Context Transfer — ERP Nova Prestech

> **Last updated:** 2026-02-28 by Copilot (Claude Opus 4.6)
> **Branch:** `agent` (HEAD: `3cc44472`)

## How to use these files

Pass the files from this directory as context attachments when starting a new
agentic session. They are numbered for reading order. Read **all** of them
before touching any code.

| File                            | Purpose                                                              |
| ------------------------------- | -------------------------------------------------------------------- |
| `01_CRITICAL_RULES.md`          | **Non-negotiable constraints** — read first or you WILL break things |
| `02_PROJECT_OVERVIEW.md`        | Tech stack, paths, env vars, DB config                               |
| `03_CONSTANTS_AND_ALIASES.md`   | Constant classes, aliases, import patterns                           |
| `04_SA_USER_AND_AUTH.md`        | Super admin user details, auth flow, UUID gotchas                    |
| `05_DATABASE_STATE.md`          | Current DB state, seeder history, what data exists                   |
| `06_GIT_HISTORY.md`             | All batches, what was fixed, commit hashes                           |
| `07_KNOWN_BUGS_AND_PATTERNS.md` | Recurring bug patterns, SA type check issues                         |
| `08_VIEWS_AND_ROUTES.md`        | Key route names, view paths, AJAX endpoints                          |
| `09_ACTIVE_STASH.md`            | **ARCHIVED** — stash dropped, kept for history                       |
| `10_CURRENT_STATUS.md`          | What is working, what is broken right now                            |

## Critical warnings for any agent

```
⛔ NEVER run `php artisan test` — it wipes the production database
⛔ NEVER run `php artisan migrate:fresh` — same effect
⛔ NEVER replace the SA user with a fake UUID
⛔ NEVER change the `languages` table `created_by` to a non-SA UUID
⛔ UUID primary keys — (int)$user->id === 0, breaks find_in_set()
⛔ Always check `$userType !== PMC::SA` alongside `$userType !== PMC::CPN`
```
