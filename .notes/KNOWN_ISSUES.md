# KNOWN ISSUES

> Last updated: 2026-03-04
> Active issues only. Resolved items archived to `.notes/.llms/.history/fixes/`.
> Naming history moved to `.notes/.llms/.history/fixes/naming-history.md`.
> Coding conventions in `.notes/.llms/.guidelines/`.

## DEFERRED (monitoring only)

| ID   | Issue                                                                     | Severity | Notes                                                                         |
| ---- | ------------------------------------------------------------------------- | -------- | ----------------------------------------------------------------------------- |
| FP-1 | BankTransferPaymentController L380: "Expected array, found Collection"    | Info     | Collection implements ArrayAccess — Intelephense false positive               |
| FP-2 | ComissionControllerTest L254/282/310: "Undefined method commissionCreate" | Info     | Called on Mockery partial mock — Intelephense can't resolve                   |
| FP-3 | jest.config.cjs L17/28: "`__dirname` is not defined"                      | Info     | ESLint false positive — `__dirname` is valid in .cjs (CommonJS) files         |
| FP-4 | web.php L1783: "Undefined type PaytabsLaravelListenerApi"                 | Info     | Runtime container resolution via `app()` — works if Paytabs package installed |
| D-1  | CSP `unsafe-inline` in SecureHeaders.php                                  | Medium   | Needs nonce-based CSP implementation — major refactor, deferred               |
| D-2  | holidays/calendar.blade.php: no getData function for calendar type        | Low      | Calendar type dropdown exists but no JS implements switching for holidays     |

## RECENTLY RESOLVED

| ID  | Issue                                                | Resolution                                                                                                             |
| --- | ---------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------- |
| D-3 | PHPUnit DashboardDataTest migration/factory errors   | Fixed factories (billing\_\* cols, UUID bill_id), namespace import, createdBy(), risky tests — 26/26 pass (2026-03-04) |
| D-4 | PHPStan-level type issues                            | Added @property PHPDoc to 8 models (80 → 0 errors at L2). L3 module runner created (2026-03-04)                        |
| —   | Copilot + Codex combined audit (60+ fixes, 30 files) | Phases 1-5: structural, variables, LAR-001–008, blade/JS, route/middleware (2026-03-07)                                |
| —   | Intelephense batch (14 fixes, 12 files)              | Import aliases, static properties, case fixes, types, test bugs (2026-03-06)                                           |
| —   | Playwright Firefox flaky render-timing               | Browser-aware timeouts, `test.slow()`, separated skip vs fail logic (2026-03-05)                                       |

---
