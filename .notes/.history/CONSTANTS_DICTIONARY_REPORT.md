# Constants Dictionary System — Comprehensive Audit Report

**Project**: erp_prestech (Laravel ERP Fork)  
**Base Path**: `_inc/laravel/`  
**Date**: Auto-generated

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [All Constants Files — Inventory](#2-all-constants-files--inventory)
3. [Alias Registration System (BladeImportsServiceProvider)](#3-alias-registration-system-bladeimportsserviceprovider)
4. [Constants NOT Aliased](#4-constants-not-aliased)
5. [Blade View Adoption Statistics](#5-blade-view-adoption-statistics)
6. [Route File Analysis](#6-route-file-analysis)
7. [View Sampling — Detailed Analysis](#7-view-sampling--detailed-analysis)
8. [Two Aliasing Mechanisms](#8-two-aliasing-mechanisms)
9. [Inconsistencies & Issues](#9-inconsistencies--issues)
10. [Recommendations](#10-recommendations)

---

## 1. Executive Summary

The project has a robust constants dictionary system spanning **29 PHP class files** (34,618 total lines) in `app/Config/Constants/`. These constants centralize CSS class names, view/route names, layout paths, stack names, section names, permissions, settings keys, database columns, and i18n strings.

A central `BladeImportsServiceProvider` (in `app/Providers/`) registers global `class_alias()` calls so that short aliases like `VC`, `VW`, `EL`, `YD`, `ST`, `PMC` etc. are available in all Blade `@php` blocks without `use` statements.

**Key Statistics:**

- **29** constants files, **34,618** total lines of code
- **716** total blade view files
- **401** (56%) reference at least one constant alias
- **315** (44%) reference none — but after excluding vendor/fragments/partials, only **~38** application-level views lack any constant references
- **636** VW::-based route names in `web.php` vs **75** raw string `->name()` calls
- **13** of 29 constants classes have **no short alias** in BladeImportsServiceProvider

---

## 2. All Constants Files — Inventory

| #   | File                            | Lines  | Namespace              | Class                       | Purpose                                                                                                                                                                    |
| --- | ------------------------------- | ------ | ---------------------- | --------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | `ActivitiesConstants.php`       | 218    | `App\Config\Constants` | `ActivitiesConstants`       | Activity/log table column constants (`COL_USR_ID`, `COL_ACT_TP`, `COL_ACT_DATA`, etc.)                                                                                     |
| 2   | `BanksConstants.php`            | 39     | `App\Config\Constants` | `BanksConstants`            | Bank-related column constants (`COL_BNK_NM`, `COL_ACC_NM`, etc.)                                                                                                           |
| 3   | `BaseRoutesConstants.php`       | 9      | `App\Config\Constants` | `BaseRoutesConstants`       | Base route segment strings (`DSB_HM = 'home'`, `ACC_AST = 'account_assets'`)                                                                                               |
| 4   | `BillsConstants.php`            | 290    | `App\Config\Constants` | `BillsConstants`            | Billing/invoicing column constants (`COL_BIL_DT`, `COL_DUE_DT`, `COL_SEND_DT`, etc.)                                                                                       |
| 5   | `ChartsConstants.php`           | 1,459  | `App\Config\Constants` | `ChartsConstants`           | Chart of account types/subtypes, UUIDs, and `COA_TPS`/`COA_SBTPS` arrays                                                                                                   |
| 6   | `CompaniesConstants.php`        | 55     | `App\Config\Constants` | `CompaniesConstants`        | Company/branch/department column constants                                                                                                                                 |
| 7   | `DatabaseConstants.php`         | 279    | `App\Config\Constants` | `DatabaseConstants`         | Table names (`TABLE_USERS`, `TABLE_PLANS`, `TABLE_PROJECTS`, etc.), column constants, default UUIDs                                                                        |
| 8   | `EmailsConstants.php`           | 53     | `App\Config\Constants` | `EmailsConstants`           | Email-related columns and `STATUS_MAP` array                                                                                                                               |
| 9   | `ExtendingLayoutsConstants.php` | 13     | `App\Config\Constants` | `ExtendingLayoutsConstants` | Blade `@extends()` layout paths (`ADM = 'layouts.admin'`, `AUTH = 'layouts.auth'`, `CTC`, `SPJ`, `CKC`)                                                                    |
| 10  | `FormsConstants.php`            | 147    | `App\Config\Constants` | `FormsConstants`            | Form builder column constants, `PERMISSIONS` array                                                                                                                         |
| 11  | `LandingPageConstants.php`      | 38     | `App\Config\Constants` | `LandingPageConstants`      | Landing page setting key constants                                                                                                                                         |
| 12  | `LangsConstants.php`            | 26,031 | `App\Config\Constants` | `LangsConstants`            | Massive i18n dictionary — error messages in 15+ languages                                                                                                                  |
| 13  | `MessagesConstants.php`         | 18     | `App\Config\Constants` | `MessagesConstants`         | Message-related column constants                                                                                                                                           |
| 14  | `MiddlewaresConstants.php`      | 18     | `App\Config\Constants` | `MiddlewaresConstants`      | Middleware name strings (`AUTH = 'auth'`, `WEB = 'web'`, `XSS`, `REV`)                                                                                                     |
| 15  | `NotificationsConstants.php`    | 16     | `App\Config\Constants` | `NotificationsConstants`    | Notification template columns                                                                                                                                              |
| 16  | `PermissionsConstants.php`      | 715    | `App\Config\Constants` | `PermissionsConstants`      | Permission string constants for Spatie roles/permissions (`SA = 'super admin'`, `SHW_ACC_DSB`, `MNG_BIL`, etc.)                                                            |
| 17  | `PlansConstants.php`            | 23     | `App\Config\Constants` | `PlansConstants`            | Plan model column names                                                                                                                                                    |
| 18  | `ProjectsConstants.php`         | 180    | `App\Config\Constants` | `ProjectsConstants`         | Project/task/contract columns (`COL_PRJ_NM`, `COL_STR_DT`, `COL_TSK_ID`, etc.)                                                                                             |
| 19  | `RoutesKeysConstants.php`       | 8      | `App\Config\Constants` | `RoutesKeysConstants`       | Just `API_KEY = 'api'`                                                                                                                                                     |
| 20  | `SeedersTemplating.php`         | 3,314  | `App\Config\Constants` | `SeedersTemplating`         | Permission seeder arrays using `PMC::` references                                                                                                                          |
| 21  | `ServicesConstants.php`         | 8      | `App\Config\Constants` | `ServicesConstants`         | Just `CRM = 'crm'`                                                                                                                                                         |
| 22  | `SettingsConstants.php`         | 439    | `App\Config\Constants` | `SettingsConstants`         | Settings key strings (`CST_DRK`, `THM_CLR`, `ENB_SGU`, S3/Wasabi storage keys, template prefixes)                                                                          |
| 23  | `StacksConstants.php`           | 17     | `App\Config\Constants` | `StacksConstants`           | Blade `@push()` stack name strings (`ADM_SCR_PG`, `ADM_CSS`, `AUTH_CST_SCR`, `CST_DRK`)                                                                                    |
| 24  | `SupportsConstants.php`         | 24     | `App\Config\Constants` | `SupportsConstants`         | Support ticket columns                                                                                                                                                     |
| 25  | `TemplatesConstants.php`        | 14     | `App\Config\Constants` | `TemplatesConstants`        | Template columns                                                                                                                                                           |
| 26  | `UsersConstants.php`            | 75     | `App\Config\Constants` | `UsersConstants`            | User model column constants (`COL_NM`, `COL_EM`, `COL_TP`, `COL_PSW`, etc.)                                                                                                |
| 27  | `ViewClassNamesConstants.php`   | 911    | `App\Config\Constants` | `ViewClassNamesConstants`   | CSS class string constants — single (`BT`, `CD`, `RW`, `DFL`) and composite (`BT_SM_PM`, `DFL_AIC`, `CLMS4`), icon classes (`TI_EYE`, `TI_TRS`), form groups, modals, etc. |
| 28  | `ViewsConstants.php`            | 186    | `App\Config\Constants` | `ViewsConstants`            | View/route name strings (~170 constants: `EMP`, `INV`, `BIL`, `PRJ`, `DSB`, `DSB_HM`, `CTC`, `SET`, composites like `PRJ_TSK`, `ML`)                                       |
| 29  | `YieldingConstants.php`         | 21     | `App\Config\Constants` | `YieldingConstants`         | Blade `@section()`/`@yield()` name strings (`ADM_PG_TTL`, `ADM_ACT_BTN`, `ADM_BDC`, `ADM_CTT`, `AUTH_PG_TTL`, `AUTH_CTT`, `CTC_CTT`)                                       |

---

## 3. Alias Registration System (BladeImportsServiceProvider)

**File**: `app/Providers/BladeImportsServiceProvider.php`

The `boot()` method runs `class_alias()` for each entry, guarded by:

```php
if (!\class_exists($alias, false) && \class_exists($fqcn)) {
    \class_alias($fqcn, $alias);
}
```

### Complete Alias Table (Constants)

| Short Alias(es)                                   | Fully Qualified Class                                                      |
| ------------------------------------------------- | -------------------------------------------------------------------------- |
| `AC`, `ActivitiesConstants`                       | `App\Config\Constants\ActivitiesConstants`                                 |
| `BillsConstants`                                  | `App\Config\Constants\BillsConstants`                                      |
| `DBC`, `DC`, `DatabaseConstants`                  | `App\Config\Constants\DatabaseConstants`                                   |
| `EL`, `ELC`, `ExtendingLayoutsConstants`          | `App\Config\Constants\ExtendingLayoutsConstants`                           |
| `LangsConstants`                                  | `App\Config\Constants\LangsConstants`                                      |
| `PC`, `PERM`, `PM`, `PMC`, `PermissionsConstants` | `App\Config\Constants\PermissionsConstants`                                |
| `PJ`, `ProjectsConstants`                         | `App\Config\Constants\ProjectsConstants`                                   |
| `PL`, `PLC`, `PlansConstants`                     | `App\Config\Constants\PlansConstants`                                      |
| `SC`, `STG`, `SettingsConstants`                  | `App\Config\Constants\SettingsConstants`                                   |
| `ST`, `StacksConstants`                           | `App\Config\Constants\StacksConstants`                                     |
| `SupportsConstants`                               | `App\Config\Constants\SupportsConstants`                                   |
| `UC`, `UCN`, `UsersConstants`                     | `App\Config\Constants\UsersConstants`                                      |
| `C`, `VC`, `VCN`, `ViewClassNamesConstants`       | `App\Config\Constants\ViewClassNamesConstants`                             |
| `VW`, `ViewsConstants`, `ViewsConstans` _(typo)_  | `App\Config\Constants\ViewsConstants`                                      |
| `YC`, `YD`, `YW`, `YieldingConstants`             | `App\Config\Constants\YieldingConstants`                                   |
| `LPC`, `LandingPageConstants`                     | `App\Config\Constants\LandingPageConstants`                                |
| `E`                                               | `Modules\LandingPage\Config\Constants\ExtendingLandingPageLayoutConstants` |
| `R`, `RRC`, `RoutesResourcesConstants`            | `Modules\LandingPage\Config\Constants\RoutesResourcesConstants`            |
| `LPSC`, `LandingPageSettingsConstants`            | `Modules\LandingPage\Config\Constants\SettingsConstants`                   |

### Model Aliases (also in BladeImportsServiceProvider)

The provider also aliases 30+ models (Utility, Employee, Plan, Invoice, Bill, Project, DealTask, etc.), the `Log` facade, custom log facades (`BcLog`, `AiLog`, `SpLog`, etc.), and third-party facades (`Form`, `Role`, `Purifier`, `DNS2D`).

---

## 4. Constants NOT Aliased

The following 13 constants classes have **NO short alias** in `BladeImportsServiceProvider`, meaning they can only be used via full namespace `use` imports in `@php` blocks:

| Class                    | Lines | Notes                                                        |
| ------------------------ | ----- | ------------------------------------------------------------ |
| `BanksConstants`         | 39    | No alias — requires inline `use`                             |
| `BaseRoutesConstants`    | 9     | No alias — only 2 constants                                  |
| `ChartsConstants`        | 1,459 | No alias — large file, used mostly in seeders/backend        |
| `CompaniesConstants`     | 55    | No alias                                                     |
| `EmailsConstants`        | 53    | No alias                                                     |
| `FormsConstants`         | 147   | No alias                                                     |
| `MessagesConstants`      | 18    | No alias                                                     |
| `MiddlewaresConstants`   | 18    | No alias — used via `use ... as MWC` in routes, not in views |
| `NotificationsConstants` | 16    | No alias                                                     |
| `RoutesKeysConstants`    | 8     | No alias — just 1 constant                                   |
| `SeedersTemplating`      | 3,314 | No alias — used in seeders only                              |
| `ServicesConstants`      | 8     | No alias — just 1 constant                                   |
| `TemplatesConstants`     | 14    | No alias                                                     |

---

## 5. Blade View Adoption Statistics

### Overall Numbers

| Category                              | Count | %    |
| ------------------------------------- | ----- | ---- |
| **Total blade files**                 | 716   | 100% |
| Files referencing at least 1 constant | 401   | 56%  |
| Files referencing NO constants        | 315   | 44%  |

### Breakdown of Non-Constant Files

| Category                            | Count   | Notes                                                                                      |
| ----------------------------------- | ------- | ------------------------------------------------------------------------------------------ |
| Vendor (3rd-party) files            | 22      | `resources/views/vendor/` — not owned, expected to lack constants                          |
| Fragment files                      | 6       | `resources/views/fragments/` — small HTML snippets (favicon, OG tags, etc.)                |
| Partial/helper files                | 6       | `resources/views/partials/` — utility snippets                                             |
| **Application views w/o constants** | **~38** | Main application views that should be migrated                                             |
| Remaining (layouts, emails, etc.)   | ~243    | Probably modal bodies, script partials, payment callbacks, email templates, sub-components |

### What the "No Constants" Views Look Like

Most non-constant application views fall into these categories:

- **Modal body partials** (e.g., `indicators/show.blade.php`, `trainers/show.blade.php`, `deals/tasks_show.blade.php`) — small modal views that use raw CSS classes like `'col-md-6'`, `'text-sm'`, `'badge badge-pill'`
- **Legacy form views** (e.g., `revenues/create.blade.php`) — use `Collective\Html\FormFacade` with raw class strings like `'form-control'`, `'form-label'`, `'btn btn-primary'`
- **Payment callback/script views** (e.g., `plans/paytr_payment.blade.php`, `invoices/script.blade.php`) — minimal views with inline scripts
- **Vendor detail partials** (e.g., `bills/vendor_detail.blade.php`) — data display views with raw CSS

---

## 6. Route File Analysis

### `routes/web.php` (1,985 lines)

**Imports at top:**

```php
use App\Config\Constants\{
    BaseRoutesConstants as BRC,
    DatabaseConstants as DBC,
    MiddlewaresConstants as MWC,
    PermissionsConstants as PMC,
    ViewsConstants as VW
};
```

| Metric                           | Count                                                         |
| -------------------------------- | ------------------------------------------------------------- |
| `VW::` references                | 757                                                           |
| `VW::`-based `->name()` calls    | 636                                                           |
| Raw string `->name('...')` calls | **75**                                                        |
| `MWC::` references               | 578                                                           |
| `DBC::` references               | 18                                                            |
| `PMC::` references               | 0 (permissions checked in controllers/middleware, not routes) |

### All 75 Raw String Route Names in `web.php`

These route names are defined with raw strings instead of `VW::` constants:

```
attendance.file.import    attendance.import         benefit.callback
business.setting          cache.settings.store      cashfree.payment.success
change.mode               chatify.download.safe     client.dashboard.view
cookie-consent            credit.note               crm.dashboard
custom_landing_page.index dashboard                 dashboard.view
debit.note                error.invoice.show        error.plan.show
exp.download.doc          exp.download.pdf          experience_certificate.update
filter.project.view       filter.user.view          generate
generate.keywords         generate.response         grammar
grammar.response          hrm.dashboard             iyzipay.invoicepayment.callback
iyzipay.payment.callback  iyzipay.payment.init      joining_letter.download.doc
joining_letter.download.pdf  joining_letter.update   language.disable
last_login                lead_stages.order          name.search.products
noc.download.doc          noc.download.pdf           noc.update
notifications.seen        offer_letter.download.doc  offer_letter.download.pdf
offer_letter.update       pay.aamarpay.success       pay.paytr.success
payfast.payment           payfast.payment.success    print.setting
receivables.export        register                   remove.user.from.project
search.json               search.products            settings
settings.recaptcha.store  share.project              slack.settings
stages.json               stages.order               stop.tracker
stripe                    stripe.post                telegram.settings
time.tracker              transactions.index         trial.balance.print
twilio.setting            two-factor.secret-key.safe update.profile
user.profile              warehouse-empty-cart       zoom.settings
```

### Other Route Files (ALL raw strings — no VW:: usage)

| File                  | Lines | VW:: | Raw `->name()`                                         |
| --------------------- | ----- | ---- | ------------------------------------------------------ |
| `routes/api.php`      | 33    | 0    | 3 (`photos.upload`, `trackers.store`, `trackers.stop`) |
| `routes/auth.php`     | 97    | 0    | 13 (standard Laravel auth routes)                      |
| `routes/fortify.php`  | 171   | 0    | 25 (standard Fortify routes)                           |
| `routes/channels.php` | 18    | 0    | 0                                                      |
| `routes/console.php`  | 19    | 0    | 0                                                      |

---

## 7. View Sampling — Detailed Analysis

### Views Using Constants Properly (Global Aliases)

| View                                        | Constants Used                                                                                                                                  | Pattern                         |
| ------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------- |
| `leaves/index.blade.php` (469 lines)        | `EL::ADM`, `YD::ADM_PG_TTL`, `UsersConstants::COL_TP`                                                                                           | Global aliases, no inline `use` |
| `plans/index.blade.php` (312 lines)         | `EL::ADM`, `YD::ADM_PG_TTL`, `YD::ADM_BDC`, `YD::ADM_ACT_BTN`, `VC::FEND`                                                                       | Global aliases, no inline `use` |
| `zoom_meetings/index.blade.php` (242 lines) | `ExtendingLayoutsConstants::ADM`, `YieldingConstants::*`, `VC::BT_SM_PM`, `VC::TI_CLD`, `VW::ZMM`                                               | Full-name global aliases        |
| `project_tasks/index.blade.php` (827 lines) | `ExtendingLayoutsConstants::ADM`, `YieldingConstants::*`, `StacksConstants::*`, `ViewsConstants::PRJ`                                           | Full-name global aliases        |
| `invoices/index.blade.php` (591 lines)      | `ExtendingLayoutsConstants::ADM`, `YieldingConstants::*`, `VC::BT_SM_PM`, `VC::TI_EXP`, `ViewsConstants::INV`                                   | Mixed full-name + short aliases |
| `bills/index.blade.php` (425 lines)         | `ExtendingLayoutsConstants::ADM`, `YieldingConstants::*`, `StacksConstants::*`, `VC::FEND`, `VC::BT_SM_PM`, `VC::TI_EXP`, `ViewsConstants::BIL` | Mixed full-name + short aliases |
| `leads/index.blade.php` (381 lines)         | `ExtendingLayoutsConstants::ADM`, `YieldingConstants::*`, `StacksConstants::*`, `VC::FEND`, `VC::BT_SM`, `VC::FM_CT_SL`, `VW::DL`               | Well-adopted                    |

### Views Using Redundant Inline Imports

Some views include explicit `use ... as` statements within `@php` blocks **on top of** the global aliases. This is redundant but harmless:

```php
@php
    use App\Config\Constants\ViewClassNamesConstants as VC;
    use App\Config\Constants\ViewsConstants as VW;
    // These are already globally available via BladeImportsServiceProvider!
@endphp
```

Files observed with this pattern: `employees/edit.blade.php`, `contracts/edit.blade.php`, `warehouse/index.blade.php`

### Views with NO Constants (Raw Strings Only)

| View                                      | Raw CSS Classes Found                                                                   | Notes                                    |
| ----------------------------------------- | --------------------------------------------------------------------------------------- | ---------------------------------------- |
| `trainers/show.blade.php`                 | `col-12`, `text-dark`, `table table-striped table-hover toggle-circle`                  | Modal body, no layout directives         |
| `revenues/create.blade.php`               | `form-control`, `form-label`, `btn btn-light`, `btn btn-primary`, `form-control select` | Legacy `Collective\Html\FormFacade` form |
| `indicators/show.blade.php`               | `col-md-12`, `col-md-6`, `text-sm`, `row py-4`                                          | Modal body with raw CSS                  |
| `deals/tasks_show.blade.php`              | `col-md-4`, `text-sm`, `badge badge-pill badge-success`, `badge-warning`                | Modal body                               |
| `form_builders/response_detail.blade.php` | `col-12`, `text-xs`, `text-sm`, `text-center text-muted`                                | Small modal                              |
| `bills/vendor_detail.blade.php`           | (none in snippets — pure data display)                                                  | Vendor address display                   |

---

## 8. Two Aliasing Mechanisms

### Mechanism 1: Global `class_alias()` (BladeImportsServiceProvider)

- **Registered in**: `app/Providers/BladeImportsServiceProvider.php`
- **How**: Uses `class_alias($fqcn, $shortName)` in `boot()` method
- **Effect**: Short aliases like `VC`, `VW`, `EL`, `YD`, `ST`, `PMC` are available **globally** in all PHP contexts (including Blade `@php` blocks) without any import
- **Coverage**: 16 of 29 constants classes aliased (plus module constants)

### Mechanism 2: Inline `use ... as` in Blade `@php` Blocks

- **Found in**: Some older/migrated views (`employees/edit`, `contracts/edit`, `warehouse/index`, etc.)
- **Pattern**:
  ```php
  @php
      use App\Config\Constants\ViewClassNamesConstants as VC;
      use App\Config\Constants\ViewsConstants as VW;
  @endphp
  ```
- **Status**: **Redundant** — the ServiceProvider already registers these same aliases globally
- **Impact**: No harm (PHP ignores duplicate `use` in the same scope), but adds noise

---

## 9. Inconsistencies & Issues

### 9.1 Typo Alias

`ViewsConstans` (missing 't') is registered as an alias for `ViewsConstants`. This masks a potential typo bug — if a developer types `ViewsConstans::` it silently works instead of throwing an error.

### 9.2 Inconsistent Alias Naming Style

Views use a mix of alias styles for the same classes:

- **Short**: `EL::ADM`, `YD::ADM_PG_TTL`, `ST::ADM_SCR_PG`, `VC::BT_SM_PM`
- **Full**: `ExtendingLayoutsConstants::ADM`, `YieldingConstants::ADM_PG_TTL`, `StacksConstants::ADM_SCR_PG`

No single convention is enforced. Some views use `YD::` while others use `YW::` or `YC::` — all three alias the same class.

### 9.3 Duplicate Alias Confusion

Multiple aliases point to the same class, which can confuse developers:

- `YieldingConstants`: `YC`, `YD`, `YW` (3 short aliases)
- `PermissionsConstants`: `PC`, `PERM`, `PM`, `PMC` (4 short aliases)
- `ViewClassNamesConstants`: `C`, `VC`, `VCN` (3 short aliases)
- `SettingsConstants`: `SC`, `STG` (2 short aliases)

### 9.4 Missing Aliases for 13 Classes

See [Section 4](#4-constants-not-aliased). Notable gaps:

- `MiddlewaresConstants` — used as `MWC` in `web.php` via inline `use as`, but no global blade alias
- `ChartsConstants` (1,459 lines) — large file with no global alias
- `CompaniesConstants` — likely used in views but requires inline import

### 9.5 Raw Permission Strings in Some Views

Some views use raw strings for permission checks instead of `PMC::` constants:

```blade
@can('create designation')  {{-- should be @can(PMC::CRT_DSG) or similar --}}
@can('create plan')
@can('create bill')
@can('view lead')
```

### 9.6 Raw Route String `'dashboard'` Used Everywhere

The route name `'dashboard'` is used as a raw string in breadcrumbs across many views:

```blade
Route::has('dashboard') ? route('dashboard') : '#'
```

This should use a VW constant (e.g., `VW::DSB` or `VW::DSB_HM`).

### 9.7 Raw CSS Classes in Modal/Partial Views

~38 application-level views (excluding vendor/fragments/partials) still use raw CSS class strings like `'col-md-6'`, `'btn btn-primary'`, `'form-control'` instead of `VC::CM6`, `VC::BT_PM`, `VC::FM_CT`.

### 9.8 Route Files `api.php`, `auth.php`, `fortify.php` — Zero Constants

These route files define **41 total raw-string route names** without any `VW::` constants. The `auth.php` and `fortify.php` routes are standard Laravel routes, so this may be intentional, but `api.php` could benefit from constants.

### 9.9 Duplicate `@section` in `invoices/index.blade.php`

The `invoices/index.blade.php` file has `@section(YieldingConstants::ADM_BDC)` defined **twice** (at different points). The second one will silently override the first.

---

## 10. Recommendations

### High Priority

1. **Standardize on ONE alias per class**. Recommended canonical aliases:
   - `VC` for ViewClassNamesConstants
   - `VW` for ViewsConstants
   - `EL` for ExtendingLayoutsConstants
   - `YD` for YieldingConstants
   - `ST` for StacksConstants
   - `PMC` for PermissionsConstants
   - `SC` for SettingsConstants
   - `DBC` for DatabaseConstants
   - `UC` for UsersConstants
   - `AC` for ActivitiesConstants
   - `PLC` for PlansConstants
   - `PJ` for ProjectsConstants

2. **Add missing aliases** for the 13 unaliased classes (where applicable) in `BladeImportsServiceProvider`.

3. **Replace raw permission strings** in `@can()` directives with `PMC::` constants.

4. **Replace raw `'dashboard'` route name** across all breadcrumb blocks with a `VW::` constant.

### Medium Priority

5. **Remove redundant inline `use ... as` imports** in views — the global aliases make these unnecessary.

6. **Migrate the 38 application-level views** that still use raw CSS strings to use `VC::` constants.

7. **Add `VW::` constants** for the 75 raw-string route names in `web.php` and define them in `ViewsConstants.php`.

8. **Fix duplicate `@section`** in `invoices/index.blade.php`.

### Low Priority

9. **Remove or document the typo alias** `ViewsConstans` — either remove it (and do a find/replace to fix any usage) or add a code comment explaining it exists for legacy compatibility.

10. **Consider aliasing** `api.php` and `fortify.php` route names if they're referenced from views or controllers.

---

## Appendix: Key Constant Examples

### ViewsConstants (VW::) — Route/View Name Strings

```php
VW::EMP        = 'employees'
VW::INV        = 'invoices'
VW::BIL        = 'bills'
VW::PRJ        = 'projects'
VW::DSB        = 'dashboard'
VW::CTC        = 'contracts'
VW::SET        = 'settings'
VW::USR        = 'users'
VW::PRJ_TSK    = 'project_tasks'
VW::DL         = 'deals'
VW::ZMM        = 'zoom_meetings'
```

### ViewClassNamesConstants (VC::) — CSS Class Strings

```php
VC::BT         = 'btn'
VC::BT_PM      = 'btn-primary'
VC::BT_SM_PM   = 'btn btn-sm btn-primary'
VC::CD         = 'card'
VC::CD_BD      = 'card-body'
VC::RW         = 'row'
VC::DFL        = 'd-flex'
VC::ALC        = 'align-items-center'
VC::DFL_AIC    = 'd-flex align-items-center'
VC::FM_CT      = 'form-control'
VC::TI_EYE     = 'ti ti-eye'
VC::TI_TRS     = 'ti ti-trash'
VC::TI_PLS     = 'ti ti-plus'
VC::FEND       = 'float-end'
```

### ExtendingLayoutsConstants (EL::) — Layout Paths

```php
EL::ADM  = 'layouts.admin'
EL::AUTH = 'layouts.auth'
EL::CTC  = 'layouts.contract_header'
```

### YieldingConstants (YD::) — Section/Yield Names

```php
YD::ADM_PG_TTL  = 'page-title'
YD::ADM_ACT_BTN = 'action-btn'
YD::ADM_BDC     = 'breadcumb'
YD::ADM_CTT     = 'content'
```

### StacksConstants (ST::) — Stack Names

```php
ST::ADM_SCR_PG  = 'script-page'
ST::ADM_CSS     = 'css-page'
```

### PermissionsConstants (PMC::) — Permission Strings

```php
PMC::SA          = 'super admin'
PMC::SHW_ACC_DSB = 'show account dashboard'
PMC::MNG_BIL     = 'manage bill'
PMC::CRT_INV     = 'create invoice'
```
