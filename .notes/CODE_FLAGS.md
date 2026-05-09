# Searchable Code Flags

> Last updated: 2026-05-09. Use these strings and regexes in IDE search
> fields, `rg`, grep, and CI audits to find deferred work, temporary mocks,
> production-hardening gaps, and intentionally incomplete coverage.

## IDE Search Tips

- Start with the literal searches below in the editor magnifier field.
- Toggle case-insensitive search when available; many comments use mixed
  casing from older agents.
- Toggle regex mode for the regex blocks below.
- In JetBrains and VS Code, search the active app first:
  `_inc/laravel/{app,Modules,resources,routes,tests,utils}`.
- Exclude generated/noisy paths unless the task is explicitly about them:
  `vendor`, `node_modules`, `storage/framework`, `storage/logs`,
  `bootstrap/cache`, `.history`, `.backup`.

## Literal Searches

| Purpose | Search strings |
|---|---|
| Standard TODOs | `TODO`, `Todo`, `todo`, `@todo`, `TO-DO`, `TO_DO`, `to do`, `todo:` |
| Fix markers | `FIXME`, `Fixme`, `fixme`, `FIX ME`, `FIX-ME`, `FIX_ME`, `fix:` |
| Deferred work | `DEFERRED`, `Deferred`, `deferred`, `TODO_LATER`, `TODO LATER`, `later:`, `follow-up`, `FOLLOWUP`, `FOLLOW_UP` |
| Risky temporary work | `TEMP`, `Temporary`, `temporary`, `TEMPORARY`, `WIP`, `workaround`, `WORKAROUND`, `hack`, `HACK`, `XXX` |
| Production hardening | `before prod`, `for prod`, `prod-only`, `production`, `go-live`, `hardening`, `remove before`, `replace before` |
| Mock/test seams | `mock`, `Mock`, `MOCK`, `fake`, `Fake`, `stub`, `Stub`, `test seam`, `test-seam`, `test_seam` |
| Known broken but accepted | `KNOWN ISSUE`, `Known issue`, `known issue`, `pre-existing`, `preexisting`, `accepted for now` |
| Real production secret substituted in test/mock | `## ! MOCKING REAL PROD SECRET`, `MOCKING REAL PROD SECRET`, `prod secret`, `production secret` |
| Google Calendar temporary seam | `MockCalendarGateway`, `GoogleCalendarGateway`, `CalendarService::setGateway`, `setGateway(` |
| PHPUnit incomplete/skip guards | `markTestIncomplete`, `markTestSkipped`, `skipped`, `incomplete` |
| Mockery alias hot-load risk | `aliasMock`, `overload:`, `class already exists`, `hot-loaded` |
| Static test seams | `resetTestSeams`, `Override = null`, `?\Closure`, `Closure|null`, `static::$` |
| Storage/settings leak hotspots | `local_storage_validation`, `local_max_upload_size`, `storage_setting`, `resetSettingsCache` |
| Migration-source-of-truth notes | `migration-source-of-truth`, `Migrations are the source of truth`, `schema is canonical` |

## Regex Searches

General TODO/deferred/fix markers:

```regex
(?i)\b(todo|to[-_ ]?do|fix[-_ ]?me|hack|xxx|bug|revisit|follow[-_ ]?up|defer(?:red)?|todo[-_ ]?later)\b
```

Temporary mocks, fake gateways, stubs, and workaround seams:

```regex
(?i)\b(temp(?:orary)?|work[-_ ]?around|wip|stub|fake|mock(?:ed|ing)?|test[-_ ]?seam|fixture|fallback)\b
```

Production-hardening reminders:

```regex
(?i)\b(prod(?:uction)?|go[-_ ]?live|release|hardening)\b.{0,100}\b(todo|remove|replace|mock|fake|temporary|before|after)\b
```

Secret/mock warning comments:

```regex
(?i)##\s*!\s*mocking\s+real\s+prod\s+secret|prod(?:uction)?\s+secret|real\s+secret
```

PHPUnit skip/incomplete markers:

```regex
markTest(Skipped|Incomplete)\s*\(
```

Mockery static alias risks and reset seams:

```regex
(aliasMock|overload:|resetTestSeams|\?\s*\\Closure|Closure\|null|Override\s*=\s*null)
```

Calendar gateway temporary-mock points:

```regex
\b(Mock[A-Za-z0-9_]*Gateway|CalendarService::setGateway|setGateway\s*\(|GoogleCalendarGateway)\b
```

Settings writes likely to leak across tests:

```regex
(insertOrIgnore|updateOrInsert|DB::table)\s*\(.*(settings|landing_page_settings)
```

Storage upload validation settings:

```regex
\b(storage_setting|local_storage_validation|local_max_upload_size|resetSettingsCache)\b
```

Legacy brand/content audit:

```regex
(?i)\b(legacy brand|old brand|vendor brand|white[-_ ]?label|portfolio)\b
```

## Current Handoff Markers

- RT-007..RT-009 — resolved 2026-05-09 by Codex test fixture fixes.
- RT-010 — aliasMock skip cluster; needs real-fixture refactors.
- RT-011 — current `markTestIncomplete` placeholders; re-enumerate before editing.
- PR-* / GL-* — production cleanup and guideline follow-ups from recovered agent handoffs.
