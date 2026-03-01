# 09 — STASH (ARCHIVED)

> **Status:** ❌ ARCHIVED — stash has been dropped, this document is kept for historical reference only.
> **Last updated:** 2026-02-28

## What happened (historical)

In early February 2026, another LLM agent session made destructive changes:

1. ✅ Made SA type fixes across ~16 controller files (mostly valid)
2. ❌ Rewrote `ProjectDataFixSeeder.php` with destructive logic that wiped the database
3. Changes were stashed as `other-agent-changes-b14-review`

## Resolution

- **Stash dropped.** No stash entries remain in the repo.
- The valid SA type fixes from the stash were subsequently applied in batches 15–23+
  through proper code review and targeted controller fixes.
- The destructive seeder was never applied again.
- The database was rebuilt from scratch using `DatabaseSeeder::runMocks()` and
  `ContentValidationSeeder`.

## Lessons learned

- ⛔ NEVER run untrusted seeders without reviewing them first
- ⛔ Stashed multi-file changes should be cherry-picked file by file
- ⛔ Seeders that DELETE or TRUNCATE must be flagged immediately

## Files that were in the stash (for reference)

SA type fixes were applied to: `DealController`, `PerformanceTypeController`,
`SupportController`, `MessagesController`, `ContractTypeController`,
`IndicatorController`, `ProjectController`, `ProjectReportController`,
`ProjectTaskController`, `ResignationController`, `DashboardController`,
`FormBuilderController`, `TimesheetController`, `DocumentUploadController`.

Service files: `BugReportService`, `LeadRequestService`,
`ProjectRequestService`, `TaskRequestService`.
