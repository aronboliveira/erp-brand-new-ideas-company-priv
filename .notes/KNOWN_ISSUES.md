# KNOWN ISSUES

> Last updated: 2026-03-06
> Active issues only. Resolved items archived to `.notes/.llms/.history/fixes/`.
> Naming history moved to `.notes/.llms/.history/fixes/naming-history.md`.
> Coding conventions in `.notes/.llms/.guidelines/`.

## DEFERRED (monitoring only)

| ID   | Issue                                                                     | Severity | Notes                                                                         |
| ---- | ------------------------------------------------------------------------- | -------- | ----------------------------------------------------------------------------- |
| FP-1 | BankTransferPaymentController L380: "Expected array, found Collection"    | Info     | Collection implements ArrayAccess — Intelephense false positive               |
| FP-2 | ComissionControllerTest L254/282/310: "Undefined method commissionCreate" | Info     | Called on Mockery partial mock — Intelephense can’t resolve                   |
| FP-3 | jest.config.cjs L17/28: "`__dirname` is not defined"                      | Info     | ESLint false positive — `__dirname` is valid in .cjs (CommonJS) files         |
| FP-4 | web.php L1783: "Undefined type PaytabsLaravelListenerApi"                 | Info     | Runtime container resolution via `app()` — works if Paytabs package installed |

## RECENTLY RESOLVED

| ID  | Issue                                   | Resolution                                                                       |
| --- | --------------------------------------- | -------------------------------------------------------------------------------- |
| —   | Intelephense batch (14 fixes, 12 files) | Import aliases, static properties, case fixes, types, test bugs (2026-03-06)     |
| —   | Playwright Firefox flaky render-timing  | Browser-aware timeouts, `test.slow()`, separated skip vs fail logic (2026-03-05) |

---
