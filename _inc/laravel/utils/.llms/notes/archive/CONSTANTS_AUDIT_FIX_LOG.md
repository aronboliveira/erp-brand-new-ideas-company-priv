# Constants Audit Fix — Session Log

**Date**: 2025-06-06 (continued from audit session)  
**Branch**: `agent`  
**Audit Report**: [CONSTANTS_AUDIT_REPORT.md](CONSTANTS_AUDIT_REPORT.md)

---

## Scope

Applied **Critical**, **High**, and **Medium** fixes from the audit report.  
**Low priority (cross-class value duplicates) was explicitly rejected** — different classes may share string values intentionally for independent future modification.

---

## Commits (chronological)

### Migrations

| Commit     | Priority | Files | Description                                                              |
| ---------- | -------- | ----- | ------------------------------------------------------------------------ |
| `11d5b1b3` | CRITICAL | 20    | Alias collisions: BLC→BC, PC→PJC, SC→SPC, CC→CPC                         |
| `0b19d411` | HIGH     | 8     | Deprecated duplicate refs: COL_TSK→COL_TSK_ID, TABLE_WHS→TABLE_WRH, etc. |
| `7d0544ca` | MEDIUM   | 8     | Raw-string TABLE names → DC:: constants (5 new TABLE\_ consts added)     |

### Models / Controllers / Traits / Views

| Commit     | Priority | Files | Description                                                               |
| ---------- | -------- | ----- | ------------------------------------------------------------------------- |
| `f1ba23da` | CRITICAL | 52    | All alias collisions fixed across app/ + Modules/                         |
| `6a5a05f2` | HIGH     | 12    | Deprecated const refs updated + 12 deprecated consts removed from 4 files |
| `0688d0bf` | MEDIUM   | 4     | Raw-string table names → DC:: in models/controllers                       |

---

## Canonical Alias Map (enforced)

| Alias | Class                   | Package                              |
| ----- | ----------------------- | ------------------------------------ |
| BC    | BillsConstants          | App\Config\Constants                 |
| BKC   | BanksConstants          | App\Config\Constants                 |
| AC    | ActivitiesConstants     | App\Config\Constants                 |
| SC    | SettingsConstants       | App\Config\Constants                 |
| UC    | UsersConstants          | App\Config\Constants                 |
| DC    | DatabaseConstants       | App\Config\Constants                 |
| PMC   | PermissionsConstants    | App\Config\Constants                 |
| PJC   | ProjectsConstants       | App\Config\Constants                 |
| CPC   | CompaniesConstants      | App\Config\Constants                 |
| SPC   | SupportsConstants       | App\Config\Constants                 |
| MWC   | MiddlewaresConstants    | App\Config\Constants                 |
| MC    | MessagesConstants       | App\Config\Constants                 |
| CHTC  | ChartsConstants         | App\Config\Constants                 |
| VC    | ViewClassNamesConstants | App\Config\Constants                 |
| VW    | ViewsConstants          | App\Config\Constants                 |
| EC    | EmailsConstants         | App\Config\Constants                 |
| FC    | FormsConstants          | App\Config\Constants                 |
| TC    | TemplatesConstants      | App\Config\Constants                 |
| PLC   | PlansConstants          | App\Config\Constants                 |
| NC    | NotificationsConstants  | App\Config\Constants                 |
| LC    | LangsConstants          | App\Config\Constants                 |
| LPSC  | SettingsConstants       | Modules\LandingPage\Config\Constants |

---

## Deprecated Constants Removed

| File                | Constant            | Canonical Replacement |
| ------------------- | ------------------- | --------------------- |
| BillsConstants      | `COL_BIL_ID`        | `COL_BL_ID`           |
| BillsConstants      | `COL_OTHER_TX`      | `COL_OT_TX`           |
| ActivitiesConstants | `COL_TSK`           | `COL_TSK_ID`          |
| ActivitiesConstants | `COL_LT`            | `COL_LOG_TP`          |
| UsersConstants      | `COL_MSG_CL`        | `COL_MC`              |
| UsersConstants      | `COL_DEL_STT`       | `COL_D_ST`            |
| UsersConstants      | `COL_RQ_PLN`        | `COL_RP`              |
| DatabaseConstants   | `TABLE_FORM_FIELDS` | `TABLE_FM_FD`         |
| DatabaseConstants   | `TABLE_WHS`         | `TABLE_WRH`           |
| DatabaseConstants   | `TABLE_PRODUCTS`    | `TABLE_PRD`           |
| DatabaseConstants   | `TABLE_TEMPLATES`   | `TABLE_TMP`           |
| DatabaseConstants   | `TABLE_PSLP`        | `TABLE_PAY_SLP`       |

---

## New Constants Added to DatabaseConstants

| Constant            | Value                        |
| ------------------- | ---------------------------- |
| `TABLE_SOURCES`     | `'sources'`                  |
| `TABLE_DL_DSC`      | `'deal_discussions'`         |
| `TABLE_MSG`         | `'messages'`                 |
| `TABLE_WEBHOOK_STG` | `'webhook_settings'`         |
| `TABLE_GEN_PSL_OPT` | `'generate_payslip_options'` |

---

## CLI Commands

All `sed` commands saved to:  
<<<<<<< HEAD:_inc/laravel/utils/.llms/notes/archive/CONSTANTS_AUDIT_FIX_LOG.md
`_inc/laravel/utils/.llms/cli/20260206/sh/constants_audit_fix.sh`
=======
`_inc/utils/.llms/cli/20260206/sh/constants_audit_fix.sh`
>>>>>>> 66cafc92b (fix: implement 3 orphan ProjectController routes; add 35 missing consts across 8 controllers; fix PurchaseController 12x ModelNotFoundException→404; convert 96 string literals to const refs in routes/web.php; DealController deal() visibility→protected):_inc/utils/.llms/notes/archive/CONSTANTS_AUDIT_FIX_LOG.md
