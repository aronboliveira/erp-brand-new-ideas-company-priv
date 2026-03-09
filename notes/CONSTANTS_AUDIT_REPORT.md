# Constants vs Migrations Audit Report

**Scope**: `BC`, `BKC`, `AC`, `SC`, `UC`, `DC`, `PMC` + secondary aliases  
**Source of Truth**: `database/migrations/` (203 files)  
**Target Dirs**: `app/{Http/Controllers,Models,Traits,Services}`, `Modules/LandingPage/{Http/Controllers,Entities}`

---

## 1 CRITICAL — Alias Collisions ⚠️

The **same alias** is used for **different** Constants classes in different files.
This is a ticking time bomb — a wrong import can silently compile but reference the wrong constant.

| Alias   | Class A                                                    | Class B                                                 | Files affected             |
| ------- | ---------------------------------------------------------- | ------------------------------------------------------- | -------------------------- |
| **MC**  | `MessagesConstants` (most places)                          | `MiddlewaresConstants` (LandingPage `Routes/web.php`)   | 13 files                   |
| **PC**  | `ProjectsConstants` (2 files)                              | `PermissionsConstants` (5 files)                        | 7 files                    |
| **SC**  | `SettingsConstants` (57 files)                             | `SupportsConstants` (6 files)                           | 63 files                   |
| **LPC** | `LandingPageConstants` (other files)                       | `SettingsConstants` (7 LandingPage views/seeder/entity) | 7+ files                   |
| **CTC** | `ChartsConstants` (1 — `Utility.php`)                      | Canonical `CHTC` everywhere else                        | 1 file                     |
| **BLC** | `BillsConstants` (1 — `BankAccount.php` + some migrations) | Canonical `BC` everywhere else (90 files)               | 1 target file + migrations |
| **VCN** | `ViewClassNamesConstants` (rare)                           | Canonical `VC` everywhere else                          | few                        |

### Recommended canonical aliases

| Constants class         | Canonical alias | Should NOT be  |
| ----------------------- | --------------- | -------------- |
| BillsConstants          | **BC**          | BLC            |
| ChartsConstants         | **CHTC**        | CTC            |
| CompaniesConstants      | **CPC**         | CC             |
| MessagesConstants       | **MC**          | —              |
| MiddlewaresConstants    | **MWC**         | MC             |
| PermissionsConstants    | **PMC**         | PC             |
| ProjectsConstants       | **PJC**         | PC             |
| SettingsConstants       | **SC**          | LPC, LPSC, LSC |
| SupportsConstants       | **SPC**         | SC             |
| LandingPageConstants    | **LPC**         | —              |
| ViewClassNamesConstants | **VC**          | VCN            |

---

## 2 DatabaseConstants — Table Name Issues

### 2.1 Duplicate TABLE\_ values (same string, multiple constants)

| String value  | Constants                          |
| ------------- | ---------------------------------- |
| `form_fields` | `TABLE_FORM_FIELDS`, `TABLE_FM_FD` |
| `warehouses`  | `TABLE_WHS`, `TABLE_WRH`           |
| `products`    | `TABLE_PRODUCTS`, `TABLE_PRD`      |
| `templates`   | `TABLE_TEMPLATES`, `TABLE_TMP`     |
| `payslips`    | `TABLE_PAY_SLP`, `TABLE_PSLP`      |

**Action**: Remove one from each pair. The migration-referenced one should be kept:

- `TABLE_FM_FD` ✅ (used in migrations) → remove `TABLE_FORM_FIELDS`
- `TABLE_WRH` ✅ (used in migrations) → remove `TABLE_WHS`
- `TABLE_PRD` ✅ (used in migrations) → remove `TABLE_PRODUCTS`
- `TABLE_TMP` ✅ (used in migrations) → remove `TABLE_TEMPLATES`
- `TABLE_PAY_SLP` ✅ (used in migrations) → remove `TABLE_PSLP`

### 2.2 DC::TABLE\_ constants NOT referenced in any migration (6)

| Constant            | Value          | Reason                                         |
| ------------------- | -------------- | ---------------------------------------------- |
| `TABLE_FORM_FIELDS` | `form_fields`  | Duplicate of `TABLE_FM_FD`                     |
| `TABLE_ROLES`       | `roles`        | Permissions migration defines its own constant |
| `TABLE_TEMPLATES`   | `templates`    | Duplicate of `TABLE_TMP`                       |
| `TABLE_PSLP`        | `payslips`     | Duplicate of `TABLE_PAY_SLP`                   |
| `TABLE_CLT_DLS`     | `client_deals` | Migration uses raw string `'client_deals'`     |
| `TABLE_USR_DLS`     | `user_deals`   | Migration uses raw string `'user_deals'`       |

### 2.3 Migration tables using raw strings (missing from DC)

| Raw string                   | Notes                                        |
| ---------------------------- | -------------------------------------------- |
| `ch_messages`                | Chatify package table                        |
| `deal_discussions`           | Should be `DC::TABLE_DL_DSC` or new constant |
| `app_personal_access_tokens` | Laravel Sanctum — OK to keep raw             |
| `failed_jobs`                | Laravel Queue — OK to keep raw               |
| `generate_payslip_options`   | Needs `DC::TABLE_GEN_PSL_OPT`                |
| `messages`                   | Needs `DC::TABLE_MESSAGES`                   |
| `sources`                    | Needs `DC::TABLE_SOURCES`                    |
| `webhook_settings`           | Needs `DC::TABLE_WEBHOOK_SETTINGS`           |

---

## 3 COL\_ Constants Not Found in Any Migration Column Definition (116 total)

These constants exist in the dictionaries but no `$table->type(...)` call in any migration references them (either by constant or matching raw string).

### 3.1 BC (BillsConstants) — 67 unmatched

Many of these are **billing/shipping/card/NFE** columns that may be defined via traits or were planned but never migrated:

- **Billing group (10)**: `COL_BL_NAME`, `COL_BL_EMAIL`, `COL_BL_ADR`, `COL_BL_TEL`, `COL_BL_ZIP`, `COL_BL_CTY`, `COL_BL_ST`, `COL_BL_CTR`, `COL_BL_DTL`, `COL_TX_N`
- **Shipping group (9)**: `COL_SHIP_NAME`, `COL_SHIP_EMAIL`, `COL_SHIP_ADR`, `COL_SHIP_TEL`, `COL_SHIP_ZIP`, `COL_SHIP_CTY`, `COL_SHIP_ST`, `COL_SHIP_CTR`, `COL_SHIP_DTL`
- **Card group (6)**: `COL_CD_NB`, `COL_CD_DG`, `COL_CD_EX_M`, `COL_CD_EX_Y`, `COL_CD_FLG`, `COL_CD_HNM`
- **NFE group (6)**: `COL_NFE_KEY`, `COL_NFE_NUMBER`, `COL_NFE_SERIES`, `COL_NFE_XML_PATH`, `COL_NFE_PROTOCOL`, `COL_NFE_AUTH_AT`
- **Template group (4)**: `COL_BIL_TMP`, `COL_INV_TMP`, `COL_PPS_TMP`, `COL_PRC_TMP`, `COL_POS_TMP` — used in SC::DFT_SETTINGS but not as migration columns
- **Transfer/Reconciliation (8)**: `COL_AUTORCC`, `COL_RCC_RL`, `COL_RCC_AT`, `COL_RCC_BY`, `COL_SYNC_ER`, `COL_SCHD_TRF_TS`, `COL_PPS_CD`, `COL_PPS_DS`
- **Status/misc (24)**: `COL_BILL_STATUS`, `COL_CLT_ID`, `COL_DSC_AMT`, `COL_IS_CNV`, `COL_IS_PRM`, `COL_IS_SIGN`, `COL_PAY_MTD_LB`, `COL_VW_AT`, `COL_ISS_DT`, `COL_PRC_AMT`, `COL_INTR_AMT`, `COL_CURR_N_INTR`, `COL_PRD_SV_UNT`, `COL_CMP_AT`, `COL_CNC_AT`, `COL_CNC_RS`, `COL_EXC_AT`, `COL_PD_AT`, `COL_PD_BY`, `COL_ADD_ATTACH`, `COL_RQ_SIGN`, `COL_TXS_FEE`, `COL_ACC_TTL`, `COL_MAX_BDG`, `COL_EXP_BDG`
- **Warranty/Insurance (6)**: `COL_HAS_EXT_SEC`, `COL_EXT_SEC_CST`, `COL_HAS_INS`, `COL_INS_CST`, `COL_INS_PLC`, `COL_HAS_WRT`, `COL_WRT_CST`, `COL_WRT_DYS`, `COL_WRT_PLC`

### 3.2 AC (ActivitiesConstants) — 22 unmatched

- **Call group (4)**: `COL_CL_DT`, `COL_CL_DUR`, `COL_CL_RS`, `COL_CL_TP`
- **General (18)**: `COL_CUR_SLR`, `COL_DEL_AT`, `COL_DSB`, `COL_EDT_CNT`, `COL_EXP_SLR`, `COL_FRM_ID`, `COL_HAS_CT_QT`, `COL_IS_DEL`, `COL_IS_EDT`, `COL_ITV_TIME`, `COL_NT_ID`, `COL_NXT_ITV`, `COL_PJ_NM`, `COL_RCT_RL`, `COL_RGT_TP`, `COL_RPL_CNT`, `COL_SCHD_TP`, `COL_TO_ID`

### 3.3 DC (DatabaseConstants) — 12 unmatched

- `COL_C_AT` (`created_at`), `COL_U_AT` (`updated_at`) — Laravel auto-manages these via `$table->timestamps()`
- `COL_FL_PT`, `COL_FL_SZ`, `COL_MM_TP`, `COL_DL_CT`, `COL_V_NUM`, `COL_RQ_SPC`, `COL_PERM_RLS`, `COL_RTR_CT`, `COL_LST_RTR_AT`, `COL_FLD_RS`

### 3.4 UC (UsersConstants) — 4 unmatched

- `COL_C_AT`, `COL_U_AT` — duplicate of DC (Laravel timestamps)
- `COL_EM_KEY` — may be in a trait
- `COL_RT` (`remember_token`) — Laravel adds via `$table->rememberToken()`

---

## 4 Duplicate COL\_ Values Across Classes (27 collisions)

| Value                  | Constants                                                              |
| ---------------------- | ---------------------------------------------------------------------- |
| `name`                 | `BC::COL_PAY_SLP_NM`, `BC::COL_TAX_NM`, `UC::COL_NM`, `UC::COL_DSG_NM` |
| `bill_id`              | `BC::COL_BL_ID`, `BC::COL_BIL_ID`                                      |
| `other_taxes`          | `BC::COL_OT_TX`, `BC::COL_OTHER_TX`                                    |
| `accepts_credit_cards` | `BC::COL_ACP_CRD`, `BKC::COL_ACPTS_CRD_CD`                             |
| `accepts_debit_cards`  | `BC::COL_ACP_DBT`, `BKC::COL_ACPTS_DBT_CD`                             |
| `accepts_pix`          | `BC::COL_ACP_PIX`, `BKC::COL_ACPT_PIX`                                 |
| `account_id`           | `BC::COL_BACC_ID`, `BKC::COL_REL_ID`                                   |
| `rejected_at`          | `BC::COL_REJ_AT`, `AC::COL_RJC_AT`                                     |
| `rejection_reason`     | `BC::COL_REJ_RS`, `AC::COL_RJC_RS`                                     |
| `user_agent`           | `BC::COL_USR_AGT`, `DC::COL_UA`                                        |
| `user_id`              | `BKC::COL_REL_USER`, `AC::COL_U`, `UC::COL_USER_ID`                    |
| `account_number`       | `BKC::COL_ACC_N`, `UC::COL_ACC_NM`                                     |
| `bank_name`            | `BKC::COL_NM`, `UC::COL_BANK_NM`                                       |
| `transfer_date`        | `BKC::COL_TRF_DT`, `UC::COL_TRF_DT`                                    |
| `task_id`              | `AC::COL_TSK`, `AC::COL_TSK_ID`                                        |
| `log_type`             | `AC::COL_LT`, `AC::COL_LOG_TP`                                         |
| `type`                 | `AC::COL_TP`, `UC::COL_TP`                                             |
| `is_active`            | `AC::COL_IA`, `UC::COL_IA`                                             |
| `password`             | `AC::COL_PW`, `UC::COL_PW`                                             |
| `responsible_id`       | `AC::COL_RES_ID`, `UC::COL_RSP_ID`                                     |
| `salary_type`          | `AC::COL_SLR_TP`, `UC::COL_SLR_TP`                                     |
| `created_at`           | `UC::COL_C_AT`, `DC::COL_C_AT`                                         |
| `updated_at`           | `UC::COL_U_AT`, `DC::COL_U_AT`                                         |
| `messenger_color`      | `UC::COL_MC`, `UC::COL_MSG_CL`                                         |
| `delete_status`        | `UC::COL_D_ST`, `UC::COL_DEL_STT`                                      |
| `requested_plan`       | `UC::COL_RP`, `UC::COL_RQ_PLN`                                         |
| `notice_date`          | `UC::COL_TERMINATION_NDT`, `UC::COL_RESIGNATION_NDT`                   |

**Intra-class duplicates to clean up**:

- `BC::COL_BL_ID` / `BC::COL_BIL_ID` → keep `COL_BL_ID` (used 7× in migrations)
- `BC::COL_OT_TX` / `BC::COL_OTHER_TX` → keep one
- `AC::COL_TSK` / `AC::COL_TSK_ID` → keep `COL_TSK_ID` (3× in migrations)
- `AC::COL_LT` / `AC::COL_LOG_TP` → keep `COL_LOG_TP` (2× in migrations)
- `UC::COL_MC` / `UC::COL_MSG_CL` → keep `COL_MC` (used in migration)
- `UC::COL_D_ST` / `UC::COL_DEL_STT` → keep `COL_D_ST` (used in migration)
- `UC::COL_RP` / `UC::COL_RQ_PLN` → keep `COL_RP` (used in migration)

---

## 5 Raw String Columns in Migrations That Could Use Constants (20)

| Raw string    | Appears in    | Could use                   |
| ------------- | ------------- | --------------------------- |
| `name`        | 45 migrations | `UC::COL_NM`                |
| `status`      | 32 migrations | `AC::COL_TSK_STT`           |
| `type`        | 29 migrations | `AC::COL_TP` / `UC::COL_TP` |
| `title`       | 23 migrations | `AC::COL_TT`                |
| `description` | 68 migrations | `AC::COL_DESC`              |
| `date`        | 16 migrations | `AC::COL_TSK_DATE`          |
| `email`       | 15 migrations | `UC::COL_EM`                |
| `order`       | 11 migrations | `AC::COL_OD`                |
| `phone`       | 11 migrations | `UC::COL_TEL`               |
| `address`     | 9 migrations  | `UC::COL_ADR`               |
| `module`      | 10 migrations | `AC::COL_MD`                |
| `url`         | 5 migrations  | — (no constant)             |
| `time`        | 4 migrations  | `AC::COL_TSK_TIME`          |
| `rate`        | 2 migrations  | `BC::COL_TAX_RT`            |
| `salary`      | 2 migrations  | `UC::COL_SLR`               |
| `password`    | 2 migrations  | `UC::COL_PW`                |
| `note`        | 3 migrations  | `AC::COL_NT`                |
| `avatar`      | 1 migration   | `UC::COL_AV`                |
| `complete`    | 1 migration   | `AC::COL_CPT`               |
| `user_id`     | 1 migration   | `UC::COL_USER_ID`           |

**Note**: Many of these are generic column names used across many tables. The constant names (e.g. `COL_TSK_STT` for `status`) sometimes carry table-specific semantics that don't fit a generic `status` column on an unrelated table. Bulk-replacing these requires care.

---

## 6 Usage Statistics (Target Dirs)

### Primary aliases

| Alias | Class                | Files | References |
| ----- | -------------------- | ----- | ---------- |
| DC    | DatabaseConstants    | 357   | 3 446      |
| BC    | BillsConstants       | 89    | 2 871      |
| UC    | UsersConstants       | 183   | 1 996      |
| AC    | ActivitiesConstants  | 90    | 1 160      |
| SC    | SettingsConstants    | 62    | 494        |
| PMC   | PermissionsConstants | 99    | 476        |
| BKC   | BanksConstants       | 10    | 118        |

### Secondary aliases (>10 refs)

| Alias | Class                                    | References |
| ----- | ---------------------------------------- | ---------- |
| PJC   | ProjectsConstants                        | 1 547      |
| MC    | MessagesConstants                        | 591        |
| PC    | PermissionsConstants/ProjectsConstants⚠️ | 193        |
| CPC   | CompaniesConstants                       | 165        |
| CC    | CompaniesConstants                       | 148        |
| FC    | FormsConstants                           | 133        |
| TC    | TemplatesConstants                       | 117        |
| EC    | EmailsConstants                          | 102        |
| LC    | LangsConstants                           | 89         |
| CHTC  | ChartsConstants                          | 67         |
| PLC   | PlansConstants                           | 61         |
| MWC   | MiddlewaresConstants                     | 41         |
| CTC   | ChartsConstants (alias conflict)         | 26         |
| BLC   | BillsConstants (alias conflict)          | 20         |
| NC    | NotificationsConstants                   | 16         |
| LPC   | LandingPageConstants/SettingsConstants⚠️ | 16         |

---

## 7 Summary of Action Items

| Priority    | Category                                      | Count       | Action                                                                                                 |
| ----------- | --------------------------------------------- | ----------- | ------------------------------------------------------------------------------------------------------ |
| 🔴 Critical | Alias collisions (MC, PC, SC, LPC)            | 4 conflicts | Standardize to canonical alias per class                                                               |
| 🟠 High     | Duplicate TABLE\_ constants                   | 5 pairs     | Remove unused duplicate from each pair                                                                 |
| 🟠 High     | Duplicate COL\_ values (intra-class)          | 7 pairs     | Remove one from each pair, update refs                                                                 |
| 🟡 Medium   | Missing DC::TABLE\_ for raw-string migrations | 5 tables    | Add `TABLE_SOURCES`, `TABLE_MESSAGES`, `TABLE_DEAL_DSC`, `TABLE_WEBHOOK_SETTINGS`, `TABLE_GEN_PSL_OPT` |
| 🟡 Medium   | DC::TABLE\_ unused (not in any migration)     | 6           | Remove after confirming not used elsewhere                                                             |
| 🟡 Medium   | COL\_ constants not in any migration          | 116         | Audit: add migrations or remove stale constants                                                        |
| 🟢 Low      | Cross-class value duplicates                  | 27 groups   | OK by design (same column name across tables)                                                          |
| 🟢 Low      | Raw string columns replaceable by constants   | 20          | Replace carefully where semantics match                                                                |
