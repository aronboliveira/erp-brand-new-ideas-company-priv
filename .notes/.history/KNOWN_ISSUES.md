# KNOWN ISSUES

> Last updated: 2026-03-05
> Active issues only. Resolved items archived to `.notes/.llms/.history/fixes/`.
> Naming history moved to `.notes/.llms/.history/fixes/naming-history.md`.
> Coding conventions in `.notes/.llms/.guidelines/`.

---

## OPEN ISSUES

| ID  | Issue            | Severity | Notes                                      |
| --- | ---------------- | -------- | ------------------------------------------ |
| —   | No active issues | —        | All known issues resolved as of 2026-03-05 |

---

## DEFERRED (monitoring only)

| ID  | Issue                                  | Severity | Notes                                        |
| --- | -------------------------------------- | -------- | -------------------------------------------- |
| —   | Playwright Firefox flaky render-timing | Cosmetic | Not a code bug — intermittent browser timing |

---

## RESOLVED (summary — full details in `.notes/.llms/.history/fixes/`)

24 issues resolved across sessions 2026-03-01 through 2026-03-05:

- K1, K7–K18: Security, testing, exports, seeders — see `.notes/.llms/.history/fixes/security-fixes.json` and `.notes/.llms/.history/fixes/non-security-fixes.json`
- K2, K4, K5: PhpSpreadsheet + seeders — fixed 2026-03-04
- K16 (ContractNote): Renamed + autoload + test assertions fixed
- **K3** (show.blade.php): Created `resources/views/task_stages/show.blade.php` — 2026-03-05
- **K6** (Arabic locale): Fixed `LanguageController::storeLanguage()` to set `created_by` — 2026-03-05
- **D1** (IDOR tenant scoping): Added `where(created_by, creatorId())` to 11 findOrFail calls across 5 controllers — 2026-03-05
- **D2** (Shared-link passwords): Replaced `base64_encode/decode` with `Hash::make/check` in ProjectController + blade + test — 2026-03-05
- **D3** (jobApplyData auth): Removed `_checkLogin()` gate, made public form with input validation — 2026-03-05
- **ContentValidationSeeder**: Created idempotent seeder for ~30 catalog tables + user type bootstrap — 2026-03-05
