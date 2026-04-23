# 03 — CONSTANTS AND ALIASES

## Import pattern (used in every controller)

```php
use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    PermissionsConstants as PMC,
    ProjectsConstants as PJC,
    UsersConstants as UC,
    ViewsConstants as VW
};
```

## PMC — PermissionsConstants (`app/Config/Constants/PermissionsConstants.php`)

| Constant   | Value           | Description           |
| ---------- | --------------- | --------------------- |
| `PMC::SA`  | `'super admin'` | Super admin user type |
| `PMC::CPN` | `'company'`     | Company user type     |
| `PMC::CL`  | `'client'`      | Client user type      |
| `PMC::CT`  | `'customer'`    | Customer user type    |
| `PMC::VD`  | `'vendor'`      | Vendor user type      |
| `PMC::ADM` | `'admin'`       | Admin user type       |
| `PMC::ACT` | `'accountant'`  | Accountant user type  |
| `PMC::HR`  | `'hr'`          | HR user type          |

### SA type check pattern (CRITICAL)

The original ERPGo code only handled `PMC::CPN` as the "owner" type.
Our SA user (`type='super admin'`) is effectively the same as company owner.
Whenever you see:

```php
// ❌ WRONG — excludes SA user, returns empty data
if ($userType !== PMC::CPN) { ... find_in_set ... }

// ✅ CORRECT — include SA alongside CPN
if ($userType !== PMC::CPN && $userType !== PMC::SA) { ... find_in_set ... }
// or:
if (!in_array($userType, [PMC::CPN, PMC::SA], true)) { ... }
```

This pattern exists in ~30+ controller methods and needs fixing everywhere.

## VW — ViewsConstants (`app/Config/Constants/ViewsConstants.php`)

| Constant      | Value                 |
| ------------- | --------------------- |
| `VW::PRJ`     | `'projects'`          |
| `VW::PRJ_TSK` | `'project_tasks'`     |
| `VW::TSKB`    | `'taskboards'`        |
| `VW::TSK`     | `'tasks'`             |
| `VW::DSB`     | `'dashboard'`         |
| `VW::INV`     | `'invoices'`          |
| `VW::BIL`     | `'bills'`             |
| `VW::USR`     | `'users'`             |
| `VW::ADM`     | `'admin'`             |
| `VW::GOL`     | `'goals'` (if exists) |
| `VW::BUG`     | `'bugs'`              |

## DC — DatabaseConstants

| Constant                | Value                                       |
| ----------------------- | ------------------------------------------- |
| `DC::COL_TABLE_CREATOR` | `'created_by'`                              |
| `DC::TABLE_TASKS`       | `'tasks'`                                   |
| `DC::DEFAULT_LANG`      | `'lang'` (was `'default_lang'` in old code) |

## UC — UsersConstants

| Constant     | Value    |
| ------------ | -------- |
| `UC::COL_TP` | `'type'` |

## PJC — ProjectsConstants

| Constant         | Value           |
| ---------------- | --------------- |
| `PJC::COL_PJ_ID` | `'project_id'`  |
| `PJC::COL_ASGN`  | `'assigned_to'` |

## Controller class constants

Each controller defines:

```php
protected const SINGULAR = 'project_task';    // or 'project', 'goal', etc.
protected const ENTITY = 'project';
protected const REDIRECT_INDEX = VW::PRJ . '.index';  // = 'projects.index'
```

## Layout constants

```php
use App\Config\Constants\ExtendingLayoutsConstants;
// ExtendingLayoutsConstants::ADM = 'layouts.admin'  (main admin layout)
```
